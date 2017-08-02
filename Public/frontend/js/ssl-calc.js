
$(function(){
    $('.extra-section').hide();
    $('.xt-radio').click(function(e){
        var $this = $(this);
        var $data_flag = $this.attr('data-flag');
        var $data_extra = $this.attr('data-extra');
        var $data_p_id = $this.attr('data-p-id');
        $('.'+$data_flag).removeClass('checked');
        $this.addClass('checked');
        $this.find('input').prop("checked", function( i, val ) {
            return !val;
        });
        if ($this.find('input').val() == 1) {
            $('.'+$data_extra+'-'+$data_p_id).find('input').val(1);
            $('.'+$data_extra+'-'+$data_p_id).show();
            if($data_extra == "for-multi-domain"){
                $('#current-global-domain-'+$data_p_id).val(0);
                $('#is_global_'+$data_p_id).hide();
                $('#is_global_text_'+$data_p_id).show();
            }else if($data_extra == "for-global-domain"){
                $('#current-mutil-domain-'+$data_p_id).val(0);
                $('#multi_domain_'+$data_p_id).hide();
                $('#multi_domain_text_'+$data_p_id).show();
            }
        }else {
            $('.'+$data_extra+'-'+$data_p_id).hide();
            $('.'+$data_extra+'-'+$data_p_id).find('input').val(0);
            if($data_extra == "for-multi-domain") {
                $('#is_global_'+$data_p_id).show();
                $('#is_global_text_'+$data_p_id).hide();
            }else if($data_extra == "for-global-domain"){
                $('#multi_domain_'+$data_p_id).show();
                $('#multi_domain_text_'+$data_p_id).hide();
            }
        }
        var $current_price = $('#current-price-'+$data_p_id);
        var price_id = $current_price.val();
        var result = compute($data_p_id);
        $('#price-year-'+$data_p_id).html('');
        $('#price-year-'+$data_p_id).html(result+'元');

    });

    $('.sslsizeinp').on('keyup',function(){
        var tmptxt=$(this).val();
        $(this).val(tmptxt.replace(/[^0-9.]/g,''));
        var temtxt = $(this).val();
        if (temtxt.match(/\./g)) {
            // if(point == 1){
            //     $(this).val(temtxt.substr(0,temtxt.indexOf('.')+4));
            // }else{
                $(this).val(temtxt.substr(0,temtxt.indexOf('.')));
            // }
        }
        if (!temtxt == true) {
            $(this).val(1);
        }
    }).on('paste',function(){
        var tmptxt=$(this).val();
        $(this).val(tmptxt.replace(/[^0-9.]/g,''));
        var temtxt = $(this).val();
        if (temtxt.match(/\./g)) {
            // if(point == 1){
            //     $(this).val(temtxt.substr(0,temtxt.indexOf('.')+4));
            // }else{
                $(this).val(temtxt.substr(0,temtxt.indexOf('.')));
            // }
        }
        if (!temtxt == true) {
            $(this).val(1);
        }
    }).css("ime-mode", "disabled");
});

function selectYear(obj, id){
    var $this = $(obj);
    var $data_p_id = $this.attr('data-p-id');
    var yes = '<i class="binggo"></i>';

    $('#current-price-'+$data_p_id).val(id);
    $('#current-price-'+$data_p_id).attr('data-current-price', $this.attr('data-price'));
    $('#current-price-'+$data_p_id).attr('data-current-years', $this.attr('data-years'));

    $('.year-'+$data_p_id).removeClass('orange');
    $this.parent('div').find('i').remove();
    $this.parent('div').find('a').css({ color: '#009de0', background: '#fff' });
    $this.append(yes);
    $this.addClass('orange');
    $this.css({ color: '#fff', background: '#009de0' });

    var result = compute($data_p_id);

    $('#price-year-'+$data_p_id).html('');
    $('#price-year-'+$data_p_id).html(result+'元');

}

function revaluation(obj, product_id){
    var $this = $(obj);
    var oprator = $this.attr('data-operator');
    var data_flag = $this.attr('data-flag');
    var $input = $('#'+data_flag+'-'+product_id);
    var current_number = $input.val()-0;
    if (oprator == "+") {
        current_number += 1;
    }else if (oprator == "-") {
        current_number -= 1;
    }
    if (current_number < 1) {
        if (data_flag == 'current-mutil-domain') {
            current_number = 1;
        }else {
            current_number = 0;
        }
    }
    $input.val(current_number);
    var $current_price = $('#current-price-'+product_id);
    var price_id = $current_price.val();

    var result = compute(product_id);

    $('#price-year-'+product_id).html('');
    $('#price-year-'+product_id).html(result+'元');
}

function compute(product_id){
    var result = 0;
    var current_mutil_domain = $('#current-mutil-domain-'+product_id).val() ? $('#current-mutil-domain-'+product_id).val(): 1;
    var current_mutil_server = $('#current-mutil-server-'+product_id).val() ? $('#current-mutil-server-'+product_id).val(): 0;
    var current_global_domain = $('#current-global-domain-'+product_id).val() ? $('#current-global-domain-'+product_id).val(): 0;
    if(current_global_domain > 1){
        $('#current-global-domain-'+product_id).val(1);
        current_global_domain = 1;
    }
    var $current_price = $('#current-price-'+product_id);
    var current_price = $current_price.attr('data-current-price');
    var step = $current_price.attr('data-current-mutil-domain-step');
    var current_years = $current_price.attr('data-current-years');

    current_price = current_price - 0  ;
    //current_mutil_domain -= 1;
    if (current_mutil_server > 0) {
        result = ( current_price + step * current_mutil_domain * current_years ) * (current_mutil_server*1 + 1)
    }else if (current_global_domain > 0) {
        result = current_price * current_global_domain * 3 - 6 + step * current_mutil_domain * current_years
    }else if (current_mutil_domain >=1){
        result = current_price + step * current_mutil_domain * current_years
    }else {
        result = current_price;
    }

    return result;
}
