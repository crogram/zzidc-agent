(function(g){
	var _g = g;
	var flag = false;
	//divselect
	//@params index 默认第几个下标
	//@params ary 列表数组
//	_g.fn.divselect = function(options){
	_g.fn.divselect = function(options){
		var element = this , opts = _g.extend({"ary":[]},_g.defaultopts,options);
		if(opts.ary.length == 0){
			return this;
		}
		element.html(makeHtml(opts.index,opts.ary));
		var cite =element.find("cite"),
		ul = element.find("ul"),
		liArr = element.find("ul > li");
		cite.click(function(event){
			ul.slideToggle("fast");
			//阻止事件冒泡
			event.stopPropagation();
		});
		liArr.click(function(){
			var thisObj = _g(this);
			cite.attr("value",_g.trim(thisObj.attr("selectid"))).text(_g.trim(thisObj.text()));
			liArr.removeClass("bei");
			thisObj.addClass("bei");
			_g.callFunc(this,opts.callback,opts.ary[thisObj.index()]);
		});
		_g(document).click(function(){
			ul.slideUp("fast");
		});
		_g.callFunc(liArr[opts.index],opts.callback,opts.ary[opts.index]);
		return this;
	};
	//系统类型
	_g.fn.divselect1 = function(options){
		var element = this , opts = _g.extend({"ary":[]},_g.defaultopts,options);
		if(opts.ary.length == 0){
			return this;
		}
		element.html(makeHtmlBox1(opts.index,opts.ary));
		var cite =element.find("cite"),
			ul = element.find("ul"),
			liArr = element.find("ul > li");
		cite.css({"background-color":"#4cb5ff","color":"white"});
		cite.addClass("cite-bg")
		cite.click(function(event){
			ul.slideToggle("fast");
			//阻止事件冒泡
			event.stopPropagation();
		});
		liArr.click(function(){
			var thisObj = _g(this);
			//cite.append("<i class='xxin-con' style='background:url("+opts.ary[thisObj.index()+1].path+") no-repeat;' ></i>")
			cite.attr("value",_g.trim(thisObj.attr("selectid"))).text(_g.trim(thisObj.text()));
			cite.html("<i class='xxin-con' style='background:url("+opts.ary[thisObj.index()+1].path+") no-repeat;' ></i>"+cite.html())
			//cite.css({"background-color":"#4cb5ff","color":"white"});
			//cite.addClass("cite-bg");
			liArr.removeClass("bei");
			thisObj.addClass("bei");
			if(opts.ary[thisObj.index()+1].id>0){
				flag = true;
			}
			_g.callFunc(this,opts.callback,opts.ary[thisObj.index()+1]);
		});
		_g(document).click(function(){
			ul.slideUp("fast");
		});
		_g.callFunc(liArr[opts.index],opts.callback,opts.ary[opts.index]);
		return this;
	};
	//具体系统名称
	_g.fn.divselect2 = function(options){
		var element = this , opts = _g.extend({"ary":[]},_g.defaultopts,options);
		if(opts.ary.length == 0){
			return this;
		}
		element.html(makeHtmlBox2(opts.index,opts.ary));
		var cite =element.find("cite"),
		ul = element.find("ul"),
		liArr = element.find("ul > li");
		if(flag){
			cite.click(function(event){
				ul.slideToggle("fast");
				//阻止事件冒泡
				event.stopPropagation();
			});
		}else{
			cite.css({"background":"url() 0px 0px no-repeat"});
		}
		
		liArr.click(function(){
			var thisObj = _g(this);
			cite.attr("value",_g.trim(thisObj.attr("selectid"))).text(_g.trim(thisObj.text()));
			cite.css({"background-color":"#4cb5ff","color":"white"});
			cite.addClass("cite-bg2");
			liArr.removeClass("bei");
			thisObj.addClass("bei");
			_g.callFunc(this,opts.callback,opts.ary[thisObj.index()+1]);
		});
		_g(document).click(function(){
			ul.slideUp("fast");
		});
		_g.callFunc(liArr[opts.index],opts.callback,opts.ary[opts.index]);
		return this;
	};
		//硬盘类型
	_g.fn.divselect3 = function(options){
		var element = this , opts = _g.extend({"ary":[]},_g.defaultopts,options);
		if(opts.ary.length == 0){
			return this;
		}
		element.html(makeHtmlBox1(opts.index,opts.ary));
		var cite =element.find("cite"),
			ul = element.find("ul"),
			liArr = element.find("ul > li");
		cite.css({"background-color":"#4cb5ff","color":"white","line-height":"33px"});
		cite.addClass("cite-bg")
		cite.click(function(event){
			ul.slideToggle("fast");
			//阻止事件冒泡
			event.stopPropagation();
		});
		liArr.click(function(){
			var thisObj = _g(this);
			//cite.append("<i class='xxin-con' style='background:url("+opts.ary[thisObj.index()+1].path+") no-repeat;' ></i>")
			cite.attr("value",_g.trim(thisObj.attr("selectid"))).text(_g.trim(thisObj.text()));
			cite.html("<i class='xxin-con' style='background:url("+opts.ary[thisObj.index()+1].path+") no-repeat;' ></i>"+cite.html())
			//cite.css({"background-color":"#4cb5ff","color":"white"});
			//cite.addClass("cite-bg");
			liArr.removeClass("bei");
			thisObj.addClass("bei");
			if(opts.ary[thisObj.index()+1].id>0){
				flag = true;
			}
			_g.callFunc(this,opts.callback,opts.ary[thisObj.index()+1]);
		});
		_g(document).click(function(){
			ul.slideUp("fast");
		});
		_g.callFunc(liArr[opts.index],opts.callback,opts.ary[opts.index]);
		return this;
	};
	
	
	
	//构造，生成div select框html
	//@params index 默认第几个下标
	//@params ary 列表数组
	function makeHtml(index,ary){
		var html = "<ul type=\"sysos\" style=\"z-index:99\">";
		_g.each(ary,function(i,v){
			if(i === index){
				html = "<cite value=\""+v.id+"\" type=\"sysos\">"+v.name+"</cite>"+html;
				html += "<li selectid=\""+v.id+"\" class=\"bei\"><a href=\"javascript:void(0);\">"+v.name+"</a></li>";
			}
			else{
				html += "<li selectid=\""+v.id+"\"><a href=\"javascript:void(0);\">"+v.name+"</a></li>";
			}
		});
		
		html += "</ul>";
		return html;
	}
	//系统类型
	function makeHtmlBox1(index,ary){
		var html = "<ul class='ul1'>";//style='width:230px;' 
		var cite = "";
		_g.each(ary,function(i,v){
			
			if(i === index){
				cite ="<cite class='cite' value='"+v.id+"' type='sysos'>"+v.name+"</cite>"; //style='width:220px;'
			}
			//html += "";
			if(v.id != 0){
				html +="<li selectid='"+v.id+"' ><a href='javascript:void(0);'><i class='xxin-con' style='background:url("+v.path+") no-repeat;' ></i>"+v.name+"</a></li>"
			}
		});
		html =cite + html + "</ul>";
		
		return html;
	}
	//系统名称
	function makeHtmlBox2(index,ary){
		var html = "<ul class='ul2' >"; //style='text-indent:10px;width:403px;' 
		var cite ="";
		_g.each(ary,function(i,v){
			if(i === index){
				cite="<cite class='cite2'  value='"+v.id+"' type='sysos'>"+v.name+"</cite>"; //style='width:394px;'
			}
			if(v.id != 0){
				html +="<li selectid='"+v.id+"' ><a href='javascript:void(0);'>"+v.name+"</a></li>"
			}
		});
		html = cite+html+ "</ul>";
		return html;
	}
	_g.fn.divselectLi = function(options){
		var element = this , opts = _g.extend({"ary":[]},_g.defaultopts,options);
		if(opts.ary.length == 0){
			// 此时需要标签下已经有下拉选项；
		}else{
			// 组装下拉选项；
			if(opts.makeHtml){
				// 自定义组装
				element.html(opts.makeHtml.call(element,opts.ary));
			}else{
				element.html(makeHtmlVps(opts.index,opts));
			}
		}
		var cite =element.find("cite"),
		ul = element.find("ul"),
		liArr = element.find("ul > li");
		// 控制长度不溢出
		var data_length = cite.attr("data-length");
		cite.click(function(event){
			var flag_=1;
			if (opts.beforecall && _g.isFunction(opts.beforecall)) {
				var beforecall =opts.beforecall;
				flag_ = beforecall.call(this);
			}
			if(flag_!=1){
				ul.slideUp("fast");
			}else{
				ul.slideToggle("fast");
			}
			//阻止事件冒泡
			event.stopPropagation();
		});
		if(opts.defLi){
			///默认选择
			var txt = _g.trim(opts.defLi.text);
			if(data_length&&txt.length>data_length){
				txt = txt.substr(0,data_length)+"..";
			}
			cite.attr("value",opts.defLi.id).text(txt);
			if(opts.defLi.attr_p){
				cite.attr("attr_p",opts.defLi.attr_p);
			}
		}
		element.mouseleave(function(){ul.slideUp("fast")});
		liArr.click(function(){
			var thisObj = _g(this);
			var selectid = _g.trim(thisObj.attr("selectid"));
			var attr_p =_g.trim(thisObj.attr("attr_p"));
			if(!attr_p){
				attr_p = 0;
			}
			var liVal = {"selectid":selectid,"attr_p":attr_p,"index":opts.index};
			var flag_=1;
			if (opts.beforecall && _g.isFunction(opts.beforecall)) {
				var beforecall =opts.beforecall;
				flag_ = beforecall.call(this, liVal);
			}
			if(flag_!=1){
				if(flag_!=0&&opts.checkfunc &&_g.isFunction(opts.checkfunc)){
					ul.slideUp(1,opts.checkfunc(flag_));
				}else{
					ul.slideUp("fast");
				}
			}else{
				var txt = _g.trim(thisObj.text());
				if(data_length&&txt.length>data_length){
					txt = txt.substr(0,data_length)+"..";
				}
				cite.attr("value",selectid).text(txt);
				if(attr_p){
					cite.attr("attr_p",attr_p);
				}
				opts.callback.call(this, opts.ary[thisObj.index()],liVal);
				//_g.callFunc(this,opts.callback,opts.ary[thisObj.index()],liattr);
			}
		});
		_g(document).click(function(){
			ul.slideUp("fast");
		});
		return this;
	};
	function makeHtmlVps(index,opts){
		/**
		<cite inputid='upsolution2'>请选择升级方案</cite>
        <ul>
           <s:iterator value="detailM.upgradeInfoList" var="v">
           	<li selectid="${v.bh}"><a href="javascript:;">${vpsYwb.cpmc}->${v.mc}</a></li>
        	</s:iterator>
       </ul>
       */
		var html = "<cite inputid='upsolution2'>请选择升级方案</cite>";
		html += "<ul>";
		_g.each(opts.ary,function(i,v){
			html += "<li selectid=\""+v.bh+"\"><a href=\"javascript:void(0);\">"+opts.cpmc+"->"+v.mc+"</a></li>";
		});
		html += "</ul>";
		return html;
	}
})(gainet);