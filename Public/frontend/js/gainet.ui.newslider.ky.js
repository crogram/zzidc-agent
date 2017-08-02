﻿﻿;(function(g){
	var _g = g;
	_g.fn.slider = function(options){
		var opts = [];
		var element = this , 
			isMousedown = false ,
			totalWidth = 0;
		opts = _g.extend({"block":[],"end":{"max":0,"text":""},"default_":0,"label":"","isnumber":true},_g.defaultopts,options) ;
		if(!opts.isdiy){
			if(_g.isEmptyObject(opts.block)){
				_g.error("block is null");
				return;
			}
			makeHtmlBg();
		}else{
			var flagtmp = true;
			opts.block=[];
			element.find(".slider-bar li").each(function(i,v){
				var width_=$(this).attr("width_");
				var widthw =parseInt(width_?width_:$(this).width());
				var skips =parseInt($(this).attr("skip"));
				var minm =parseInt($(this).attr("min"));
				if(!widthw||!skips||(!minm&&minm<0)){
					flagtmp = false;
					return ;
				}
				if(!flagtmp){
					_g.error("block is null ,cause by the wrong property!");
					return;
				}
				opts.block.push({"width":widthw,"skip":skips,"min":minm});
			});
		}
		var doc = _g(document) ,
			sliderBar = element.find(".slider-bar") ,
			//bar = element.find(".slider-bar") ,
			sliderBtn = element.find(".slider-bar-btn"),
			cut = element.find(".bk-number-arrow2"),
			plus = element.find(".bk-number-arrow"),
			number = element.find(".bk-disk-input");
		if(cut){
			cut.off("click");
			plus.off("click");
			number.off("keydown");
			number.off("focusout");
			number.off("keypress");
		}
		cut.click(function(){
			var num = parseInt(number.val());
			if(num<=opts.block[0].min)
				num=opts.block[0].min;
			else{
				for(var j=0;j<opts.block.length;j++){
					var maxn=opts.block[j+1]?opts.block[j+1].min:opts.end.max;
					if(num>opts.block[j].min&&num<=maxn){
						var ccstep = Math.round((num-opts.block[j].min)/opts.block[j].skip);
						num = num-opts.block[j].skip;
						num = num<opts.block[0].min?opts.block[0].min:num;
						number.val(num);
						break;
					}
				}
			}
			element.setBarValue();
		});

		plus.click(function(){
			var num = parseInt(number.val());
			for(var j=0;j<opts.block.length;j++){
				var maxn=opts.block[j+1]?opts.block[j+1].min:opts.end.max;
				if(num>=opts.block[j].min&&num<maxn){
					num = num+opts.block[j].skip;
					num = num>opts.end.max?opts.end.max:num;
					number.val(num);
					break;
				}
			}
			element.setBarValue();
		});
		
		number.keydown(function(event){
			if(13 == event.keyCode){
				element.setBarValue();
			}
		});
		number.focusout(function(){  
			element.setBarValue();
		});
		number.keypress(function(event) { 
            var keyCode = event.which; 
            if (keyCode >= 48 && keyCode <=57) 
                return true; 
            else 
                return false; 
        }).focus(function() { 
            this.style.imeMode='disabled'; 
        });
		_g.each(opts.block,function(i,n){
			totalWidth += n.width;
		});
		//点击鼠标
		//sliderBar.mousedown(function(event){
			//event.preventDefault();
			//gainet.js.stopDefault(event);
			//isMousedown = true;
		//});
		element.on('click', "li", function(e) {
			//执行滚动方法
			//sliderToDes($(this).index());
			var barLeft = sliderBar.offset().left;
			//var btnLeft = sliderBtn.offset().left;
			var pointX = e.pageX;
			var wid = pointX - barLeft;
			//var barpoint = getBarPoint(e);
			if(wid>=0){
				wid += opts.end.startwidth;
				var result = calculate(wid);
				setBarPoint(result);
			}
		});
		//释放鼠标
		//doc.mouseup(function(event){
			//if(isMousedown){
				//isMousedown = false;
				//var barpoint = getBarPoint(event);
				//var result = calculate(barpoint);
			//}
		//});
		//拖动 - 滚动到指定位置
		sliderBtn.on('mousedown', function(e) {
			_g.js.stopDefault(e);
			var $this = $(this);
			var pointX = sliderBar.offset().left;
			//var pointX = e.pageX - $this.parent().width();
			var wid = null;
			isMousedown = true;
			//拖动事件
			$(document).on('mousemove',function(ev){
				wid = ev.pageX - pointX;
				if(wid > 2 && wid < 580){
					if(isMousedown){
						sliderBar.css("width", wid);
						var result = calculate(wid);
						// 不执行回调
						result.notcall=true;
						setBarPoint(result);
						e.stopPropagation();
					}
				}
			});
			$(document).on('mouseup',function(e){
				//$(this).off('mousemove mouseup');
				//var index = Math.ceil(wid/liWid) - 1;
				//sliderToDes(index);
				if(isMousedown){
					//var barpoint = getBarPoint(e);
					var result = calculate(wid);
					setBarPoint(result);
					isMousedown=false;
					
					e.stopPropagation();
				}
			});
		});
		//鼠标滑动效果

		//设置按钮位置
		function setBarPoint(result){
			if(result.val >= opts.block[0].min && result.val <= opts.end.max){
				number.val(result.val);
				sliderBar.stop().animate({'width' : result.width}, 200);
				//显示被遮盖的数字
				if(!result.notcall){
					_g.callFunc(element,opts.callback,result.val);
				}
			}
		}

		//获取按钮坐标
		function getBarPoint(event){
			var offset = sliderBar.offset();
			//计算页面鼠标坐标
			var pageMouseX = doc.scrollLeft() + event.clientX;
			//计算bar的left （bar相对于 list-diskui div的left）
			barLeft = pageMouseX - sliderBar.offset().left;
			//barLeft 相当于 进度条的拖拉长度
			return barLeft;
		}

		function calculateDiff(i,n){
			var diff = 0;
			//算出每一刻度的段数
			if(opts.block.length === i + 1){
				diff = parseInt( (opts.end.max - n.min) / n.skip );
			}
			else{
				diff = parseInt( (opts.block[i+1].min - n.min) / n.skip );
			}
			return diff;
		}

		
		function setNumberValue(val){
			var num = val || parseFloat(_g.trim(number.val()));
			if(_g.trim(num) == "" || isNaN(parseInt(num)) || parseInt(num) == undefined ||num<opts.block[0].min){
				number.val(opts.default_?opts.default_:opts.block[0].min);
			}else if(parseInt(number.val())> opts.end.max){
				number.val(opts.end.max);
			}else{
				var num = parseInt(num);
				for(var j=0;j<opts.block.length;j++){
					var maxn=opts.block[j+1]?opts.block[j+1].min:opts.end.max;
					if(num>opts.block[j].min&&num<=maxn){
						var ccstep = Math.round((num-opts.block[j].min)/opts.block[j].skip);
						num = opts.block[j].min+opts.block[j].skip*(ccstep);
						number.val(num);
						break;
					}
				}
			}
		}
		
		function makeHtmlBg(){
			var sliderul = element.find(".slider-bg").empty();
			_g.each(opts.block,function(i,n){
				sliderul.eq(0).append('<li style="width:'+n.width+'px">'+n.text+'</li>');
			});
			_g.each(opts.block,function(i,n){
				sliderul.eq(1).append('<li style="width:'+n.width+'px">'+n.text+'<span>'+n.text+'</span></li>');
			});
		}
		//计算拖拉条值和按钮位置
		function calculate(width){
			//width=opts.end.startwidth+width;
			var tempWidth = opts.end.startwidth?opts.end.startwidth:0;
			var val = 0;
			_g.each(opts.block,function(i,n){
				tempWidth += n.width;
				if(width <= tempWidth){
					var diff = calculateDiff(i,n);
					//算出刻度的每一段的宽度
					var cellWidth =  n.width / diff;
					if(i === 0  && width < cellWidth / 2){
						tempWidth = opts.end.startwidth?opts.end.startwidth:0;
						val = n.min;
						return false;
					}

					//算出bar在这一刻度的哪一段 TODO 
					for(var j=1; j<=diff; j++){
						if(width+opts.end.startwidth >= (tempWidth - cellWidth * j)){
							if(width - (tempWidth - cellWidth * j) < cellWidth / 2){
								val = n.min + (diff - j) * n.skip;
								tempWidth = tempWidth - cellWidth * j;
								if(val > opts.end.max){
									val = val - n.skip;
								}
							}
							else{
								val = n.min + (diff - j + 1) * n.skip;
								tempWidth = tempWidth - cellWidth * (j - 1);
								if(val > opts.end.max){
									val = val - n.skip;
								}
							}
							return false;
						}
					}
				}else if(width > tempWidth){
					val = opts.end.max;
				}
			});
			return {"width":tempWidth,"val":val};
		}
		// 根据数值设置进度的长度
		element.setBarValue = function(val){
			setNumberValue(val);
			var num = parseFloat(_g.trim(number.val()));
			var tempWidth = opts.end.startwidth?opts.end.startwidth:0;
			if(num > opts.block[0].min){
				_g.each(opts.block,function(i,n){
					tempWidth += n.width;
					var diff = calculateDiff(i,n);
					for(var j=1; j<=diff; j++){
						if(num === n.min + n.skip * j){
							//算出刻度的每一段的宽度
							var cellWidth =  n.width / diff;
							tempWidth = tempWidth - cellWidth * (diff - j);
							return false;
						}
					}
				});
				setBarPoint({"width":tempWidth,"val":num});
			}else if(num === opts.block[0].min){
				var tempWidth = opts.end.startwidth?opts.end.startwidth:0;
				setBarPoint({"width":tempWidth,"val":num});
			}
		}
		element.setBarValue();
		return this;
	};

})(gainet);