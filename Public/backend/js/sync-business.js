
var store = {
    index: 0
}
function sleep(ms) {
    var starttime = new Date().getTime();
    do {
    } while ((new Date().getTime() - starttime) < ms)
}

function afterBatch(data, flag, ptype){

    var bids = data.info;
    if (flag == 'diff') {
        var title = "差异同步业务";
    }else {
        var title = "同步本地所有业务";
    }
    if (bids.length == 0) {
        alertBox('没有业务可以同步啦！');
        return false;
    }
    $.gritter.add({
        title: title,
        text: '本次共同步'+bids.length+'个业务，请耐心等待一下 :)',
        class_name: 'gritter-light ',
        time: 8000,
        before_open: function(){
            if($('.gritter-item-wrapper').length >= 3){
                $.gritter.removeAll();
            }
        },
        before_close: function(e, manual_close){
            if($('.gritter-item-wrapper').length <= 0 ){
                return false;
            }
		},
    });
    var ok = fail = 0;
    var message = '';
    var result = '';
//    var index = 0;
    for (var i =  0; i < bids.length; i++) {
        sync(bids[i], function (data, bid, current) {
            (data.status == 1) ? ok++: fail++;
            if (store.index % 3 == 0) {
                message = '';
            }
            message += '业务id['+bid+']:'+data.info+';<br />';
            result += '业务id['+bid+']:'+data.info+';<br />';
            if (store.index >= (bids.length-1)) {
                suffix = '结束... :)';
            }else {
                suffix = '进行中... :)';
            }
            $.gritter.add({
                title: title+suffix,
                text: '本次共同步'+bids.length+'个业务。<br />'+'已同步成功'+ok+'条'+'已同步失败'+fail+'条。<br />共'+(bids.length-ok-fail)+'条业务正在响应中...<br />'+message,
                class_name: 'gritter-light ',
                time: 8000,
                before_open: function(){
                    if($('.gritter-item-wrapper').length >= 2){
                        $.gritter.removeAll();
                    }
                },
                before_close: function(e, manual_close){
                    if($('.gritter-item-wrapper').length <= 0){
                        return false;
                    }
                },
            });
            if (store.index >= (bids.length)) {
                alertBox('<h3>'+title+suffix+'<h3><br /><h4>--如有需要，请记录此信息--</h4><br />本次共同步'+bids.length+'个业务。<br />'+'已同步成功'+ok+'条'+'已同步失败'+fail+'条。<br />共'+(bids.length-fail-ok)+'条业务响应失败<br />'+result, function(){
                    window.location.href = window.location.href;
                });
            }
        }, store.index, ptype);
        if ( (((i+1) % 5) == 0) ) {
            sleep(2000);
        }
    }

}


function syncBatch(flag){
    $('.fa-times').parent().trigger('click');

    var business_name = $('#current-business').val();
    var url = "/Backend/"+business_name+"/syncBatch/flag/"+flag;
    var ptype = arguments[1];
    if (!!ptype) {
        url += '/type/'+ptype;
    }
    var running = null;
    $.ajax({
        type : "get",
        dataType : "json",
        url : url,
        beforeSend: function(){
            running = bootbox.dialog({
                message: '<p><i class="fa fa-spin fa-spinner"></i>正在获取同步信息...请稍等</p>',
                closeButton: false
            });
        },
        success : function(data){
            running.modal('hide');
            if (data.status == 1) {
                afterBatch(data, flag, ptype);
            }else {
                alertBox(data.info);
            }
        }
    });

}

function sync(bid){
    var business_name = $('#current-business').val();
    var url = "/Backend/"+business_name+"/sync/id/"+bid;
    var fn = ((typeof arguments[1]) == 'function') ? arguments[1]: undefined;
    var current = arguments[2];
    var ptype = arguments[3];
    if (!!ptype) {
        url += '/type/'+ptype;
    }
    var dialog = null;
    $.ajax({
        type : "get",
        dataType : "json",
        url : url,
        error: function(){
            store.index++;
        },
        beforeSend: function(){
            if (fn == undefined) {
                dialog = bootbox.dialog({
                    message: '<p style="color:#000;text-align:center;" ><i class="fa fa-spin fa-spinner"></i>同步中...</p>',
                    closeButton: false
                });
            }
        },
        success : function(data){
            if (fn !=undefined) {
                store.index++;
                fn(data, bid, current);
            }else {
                dialog.modal('hide');
                if (data.status == 1) {
                    alertBox('同步成功',function(){
                        location.reload(true)
                    });
                }else {
                    alertBox(data.info);
                }
            }
        }
    });
}
