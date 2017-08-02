/**
 * kuaiyun 
 * update by icesummer
 */
(function(g) {
	var _g = g;
	_g.fn.combo = function(options) {
		var element = this, opts = _g.extend( {
			"block" : [],
			"defaultval" : 0,
			"label" : ""
		}, _g.defaultopts, options), isMousedown = false;

		if (_g.isEmptyObject(opts.block)) {
			_g.error("block null");
			return;
		}
		element.eq(0).html(makeHtmlCPU(opts));
		var btnArr = element.eq(0).find(".right-btn");
		btnArr.click(function(){
			_g(this).removeClass("right-btn").addClass("right-btn-select").siblings().removeClass("right-btn-select").addClass("right-btn");
			var index = _g(this).index();
			var cur = opts.block[index];
			// 当前内存大小
			var memoryValue = element.eq(1).find(".right-btn-select").attr("value");
			element.eq(1).html(makeHtmlMemory(cur.memory));
			if(memoryValue && cur.memory.num[0]<=parseInt(memoryValue) && cur.memory.num[cur.memory.num.length-1]>=parseInt(memoryValue)){
				element.eq(1).find(".right-btn[value="+memoryValue+"]").click();
			}
			else{
				element.eq(1).find(".right-btn:eq(0)").click();
			}
		});
		
		element.eq(1).delegate(".right-btn","click",function(){
			_g(this).removeClass("right-btn").addClass("right-btn-select").siblings().removeClass("right-btn-select").addClass("right-btn");
			_g.applyFunc(element,opts.callback,[element.eq(0).find(".right-btn-select").attr("value"),element.eq(1).find(".right-btn-select").attr("value")]);
		});
		
		if(btnArr.length>0){
			btnArr[0].click();
		}
		element.setBarValue = function(cpu,mem){
			element.eq(0).find(".right-btn[value="+cpu+"]").click();
			element.eq(1).find(".right-btn[value="+mem+"]").click();
			_g.applyFunc(element,opts.callback,[element.eq(0).find(".right-btn-select").attr("value"),element.eq(1).find(".right-btn-select").attr("value")]);
		}
		return this;
	}
	
	function makeHtmlCPU(opts) {
		var html = '';
		_g.each(opts.block, function(i, n) {
			if(opts.block.length==i+1){
				html += '<button class="right-btn no-margin-right" value="' + n.cpu+ '">' + n.cpu + opts.label+'</button>';
			}else{
				html += '<button class="right-btn" value="' + n.cpu+ '">' + n.cpu + opts.label+'</button>';
			}
		});
		return html;
	}
	function makeHtmlMemory(cur){
		var html = '';
		_g.each(cur.num, function(i, n) {
			if(cur.num.length==i+1){
				html += '<button class="right-btn no-margin-right" value="' + n+ '">' + n + cur.label+'</button>';
			}else{
				html += '<button class="right-btn" value="' + n+ '">' + n + cur.label+'</button>';
			}
		});
		return html;
	}
})(gainet);