
//bootbox.setLocale('zh_CN');

function confirmBox(msg,fn){
    bootbox.confirm({
        size: "small",
        message: msg,
        backdrop: true,
        buttons: {
          confirm: {
             label: "确定",
             className: "btn-primary btn-sm",
          },
          cancel: {
             label: "取消",
             className: "btn-sm",
          }
        },
        callback: function(result) {
            if(result){
                fn();
            }
        }
      }
    );
}

function alertBox(msg){
    var fn = !!arguments[1] ? arguments[1]: '';
    bootbox.alert({
        size: "small",
        message: msg,
        backdrop: true,
        buttons: {
            ok: {
                label: '确定',
                className: 'btn-danger'
            }
        },
        callback: function(){
            if (fn != '') {
                fn();
            }
        }
    });
}
