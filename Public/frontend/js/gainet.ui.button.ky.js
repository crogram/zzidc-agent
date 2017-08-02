﻿(function(g){
	var _g = g;

	//radiobuttons
	_g.fn.radiobuttons = function(options){
		var element = this , opts = _g.extend({},_g.defaultopts,options);
		var liArr = element.find("li");
		liArr.click(function(){
			var index = _g(this).index();
			selectRadioButtons(index,liArr);
			_g.callFunc(this,opts.callback,index);
		}).each(function(i){
			_g(this).addClass("libg");
		});
		selectRadioButtons(opts.index,liArr);
		return this;
	};

	function selectRadioButtons(index,liArr){
		liArr.eq(index).removeClass("libg").find("a").addClass("liselect")
			.end().siblings().addClass("libg").find("a").removeClass("liselect");
	}
	// 推荐配置
	_g.fn.radiobuttons2 = function(options){
		var element = this , opts = _g.extend({},_g.defaultopts,options);
		var liArr = element.find(".ul-item > li");
		var tipsArr = element.find(".itemtips");
		if(opts.index>=liArr.length){
			opts.index = 0;
		}
		selectRadioLi(opts.index,liArr);
		selectRadioButtons2Tips(opts.index,tipsArr);
		liArr.click(function(){
			var index = _g(this).index();
			selectRadioLi(index,liArr);
			selectRadioButtons2Tips(index,tipsArr);
			_g.callFunc(this,opts.callback,index);
		});
		_g.callFunc(liArr.eq(opts.index),opts.callback,opts.index);
		return this;
	};
	function selectRadioLi(index,liArr){
		liArr.eq(index).removeClass("libg");
		liArr.eq(index).find("a").addClass("liselect");
		liArr.eq(index).siblings().addClass("libg").find("a").removeClass("liselect");
	}
	// 推荐配置提示信息对应显示
	function selectRadioButtons2Tips(index,tipsArr){
		if(index<tipsArr.length){
			tipsArr.hide().eq(index).show();
		}else{
			tipsArr.hide();
		}
	}
	// 数据盘配置
	_g.fn.radiobuttonsDisk = function(options){
		var element = this, opts = _g.extend( {}, _g.defaultopts, options), isMousedown = false;
		if (_g.isEmptyObject(opts)) {
			_g.error("block null");
			return;
		}
		var liArr = element.find(".ul-item li");
		var tipsArr = element.find(".itemtips");
		liArr.eq(opts.index).find("a").addClass("current");
		liArr.filter("[selectid]").click(function(){
			_g(this).find("a").addClass("current").end().siblings().removeClass("current");
			var index = _g(this).index();
			var selectid = _g(this).attr("selectid");
			_g.callFunc(this,opts.callback,opts.ary[index]);
		});
		return this;
	};
	// 数据盘大小
	_g.fn.changSize = function(options) {
		var element = this, opts = _g.extend( {}, _g.defaultopts, options), isMousedown = false;
		if (_g.isEmptyObject(opts)) {
			_g.error("block null");
			return;
		}
		var InputDisk = element.find(".bk-disk-input");
		element.setBarValue = function(val){
			InputDisk.val(val);
			_g.callFunc(element,opts.callback,InputDisk.val());
		}
		InputDisk.blur(function(){
			var diskSize = InputDisk.val();
		 	if(diskSize <= 0 ||diskSize =="" || isNaN(diskSize)){
				diskSize = 10;
			}else if(diskSize >= 1000){
				diskSize = 1000;
			}else if(diskSize%10 != 0){
				diskSize = (parseInt(diskSize/10)+1)*10;
			}
			element.setBarValue(diskSize);
		});
		
		 InputDisk.keypress(function(event) { 
            var keyCode = event.which; 
            if (keyCode >= 48 && keyCode <=57) 
                return true; 
            else 
                return false; 
        }).focus(function() { 
            this.style.imeMode='disabled'; 
        });
		 // 加减
		 var addsubtract = element.find(".shuju-box30").find("span");
		 addsubtract.click(function(){
			 var index = _g(this).index();
			 var diskSize = parseInt(InputDisk.val());
			 if((diskSize <= 40 && index==1 ) || isNaN(diskSize)){
				 diskSize = 40;
			 }else if(index==0){
				 diskSize+=10;
			 }else if(index==1){
				 diskSize-=10;
			 }
			 if(diskSize > 1000){
				 diskSize = 1000;
			 }else if(diskSize%10 != 0){
				 diskSize = (parseInt(diskSize/10)+1)*10;
			 }
			 element.setBarValue(diskSize);
		 });
		_g.callFunc(element,opts.callback,InputDisk.val());
		return this;
	}
	
})(gainet);