//滑动插件
;(function($) {
    $.fn.sliderDate = function(setting) {
        var defaults = {
                callback: false //默认回调函数为false
            }
        //如果setting为空，就取default的值
        var setting = $.extend(defaults, setting);
        this.each(function() {
            //插件实现代码 
            //var $sliderDate = $(".slider-date");
            var $sliderDate = $(this);
			var $sliderBar = $sliderDate.find(".slider-bar");
			var $sliderBtn = $sliderDate.find(".slider-bar-btn");
			var liWid = 42+1; //单个li的宽度

			//滚动指定的位置
			var sliderToDes = function(index){

				//最大不能超过11
				if(index > 11){
					index = 11;
				}

				//最小不能小于 0 
				if(index < 0){
					index = 0;
				}

				//背景动画
				$sliderBar.animate({
					"width" : liWid*(index+1)
				},500);

				//执行回调
				if(setting.callback){
					setting.callback(index);
				}

			};

			//点击 - 滚动到指定位置
			$sliderDate.on('click', "li", function(e) {
				//执行滚动方法
				sliderToDes($(this).index());
			});

			//拖动 - 滚动到指定位置
			$sliderBtn.on('mousedown', function(e) {
				var $this = $(this);
				var pointX = e.pageX - $this.parent().width();
				var wid = null;

				//拖动事件
				$(document).on('mousemove',function(ev){
					wid = ev.pageX - pointX
					if(wid > 20 && wid < 570){
						$sliderBar.css("width", wid);
					}
				}).on('mouseup',function(e){
					$(this).off('mousemove mouseup');
					var index = Math.ceil(wid/liWid) - 1;
					sliderToDes(index);
				});
			});
        });
    }
})(jQuery);

$(function(){
	function a(index){
		console.log(index+1);
	}
	$("#slider-date-1").sliderDate({callback:a});

	function b(index){
		console.log(index+1);
	}
	$("#slider-date-2").sliderDate({callback:b});

	function c(index){
		console.log(index+1);
	}
	$("#slider-date-3").sliderDate({callback:c});
});