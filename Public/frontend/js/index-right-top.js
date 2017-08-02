//返回顶部
$(document).ready(function(){
	$("#leftsead a").hover(function(){
		if($(this).prop("className")=="youhui"){
			$(this).children("img.hides").show();
		}else{
			$(this).children("img.hides").show();
			
			$(this).children("img.hides").animate({},'fast'); 
		}
	},function(){ 
		$(this).children("img.hides").animate({},'fast',function(){$(this).hide();$(this).next("img.shows").show();});
		
	});
});
//返回顶部
$(function(){
    // 给 window 对象绑定 scroll 事件
    $(window).bind("scroll", function(){

        var scrollTopNum = $(document).scrollTop(), // 获取网页文档对象滚动条的垂直偏移
            winHeight = $(window).height(), // 获取浏览器当前窗口的高度
            returnTop = $("div.returnTop");

        // 滚动条的垂直偏移大于 0 时显示，反之隐藏
        (scrollTopNum > 0) ? returnTop.fadeIn("fast") : returnTop.fadeOut("fast");

        // 给 IE6 定位
        if (!-[1,]&&!window.XMLHttpRequest) {
            returnTop.css("top", scrollTopNum + winHeight - 200);    
        } 

    });

    // 点击按钮后，滚动条的垂直方向的值逐渐变为0，也就是滑动向上的效果
    $("div.returnTop").click(function() {
        $("html, body").animate({ scrollTop: 0 }, 100);
    });
});
 //cart tab
 			function setView5(n){
				for(var i=1;i<=5;i++){
					if(i == n){
						$('#tab5_0'+i).removeClass("undis");
						$('#tab5_0'+i).addClass("dis");
						$('#tab5_'+i).removeClass("nonav");
						$('#tab5_'+i).addClass("nav_on6");
					}else{
						$('#tab5_'+i).removeClass("nav_on6");
						$('#tab5_'+i).addClass("nonav");
						$('#tab5_0'+i).removeClass("dis");
						$('#tab5_0'+i).addClass("undis");
					}
				}
			} 

$(".os_btn_1").click(function() {
	$(".os_btn_2").removeClass('v-selected');
	$(".os_btn_1").addClass('v-selected');
	$("#czxt").val(1);
});
$(".os_btn_2").click(function() {
	$(".os_btn_1").removeClass('v-selected');
	$(".os_btn_2").addClass('v-selected');
	$("#czxt").val(0);
});
