$(function() {
	var i, n, o = $("#floatMenu .menu-main>ul"),
		a = $("#floatMenu .menu-child"),
		s = 0,
		l = $("#floatMenu .menu-main"),
		d = $("#floatMenu .menu-child>div"),
		c = window.product.menubar,
		r = [],
		u = $(".float-box").offset().top,
		p = !1;
	$.each(c, function(e, t) {
		var n, a = "";		
		$.each(["li1", "li2"], function(n, o) {
			t[o] && (a += '<ul class="y-left">', $.each(t[o], function(n, o) {
				window.product.code != o.key || p ? a += "<li>" : "" == h ? (void 0 == i ? i = e : "", a += '<li class="action">', p = !0) : h == t.key ? (void 0 == i ? i = e : "", a += '<li class="action">', p = !0) : a += "<li>", a += '<a data-spm-click="gostr=/aliyun;locaid=' + o.spm + '" href="' + o.link + '" ' + (o.or ? 'target="_blank"' : "") + " ><h2>" + o.h2 + "</h2><p>" + o.p + "</p></a></li>"
			}), a += "</ul>", t[o].length > s && (s = t[o].length))
		});
		i == e ? (o.attr("length", t.li2 ? 2 : 1), n = $('<li length="' + (t.li2 ? 2 : 1) + '" index="' + e + '" class="action" key="' + t.key + '">' + t.text + '<i class="icon-arrow-right y-right"></i></li>')) : n = $('<li length="' + (t.li2 ? 2 : 1) + '" index="' + e + '" key="' + t.key + '">' + t.text + '<i class="icon-arrow-right y-right"></i></li>'), n.appendTo(o), n.data("chlidHtml", a)
		}); 
	2 * c.length / 3 > s && (s = 2 * c.length / 3); 
	i || (i = 0);
	
	d.html($(".menu-main li:nth-child(" + ++i + ")").data("chlidHtml"));
	$(".product-menu").mouseenter(function() {
		n && clearTimeout(n), n = setTimeout(function() {
			l.width(200), "none" == o.css("display") && o.find("li:nth-child(" + i + ")").trigger("mouseenter");
			var e = function() {
					var e = $.Deferred();
					return l.css("box-shadow", "1px 3px 4px rgba(0,0,0,.2)").animate({
						height: 60 * s + 20 + "px"
					}, 0, function() {
						e.resolve()
					}), e.promise()
				};
				
			$.when(e()).then(function() {
				o.css("display", "inline-block"), a.css({
					display: "inline-block",
					height: 60 * s + 20
				})
			})
		}, 50)
	}).mouseleave(function() {
		n && clearTimeout(n), n = setTimeout(function() {
			o.css("display", "none"), a.css({
				display: "none",
				height: o.height() + 45
			}), l.css("box-shadow", "none").animate({
				height: 0
			}, 0)
		}, 0), $(".menu-main ul>li").removeClass("hover")
	});
	$(".menu-main ul>li").mouseenter(function() {
		var e = this;
		$(this).addClass("hover").siblings().removeClass("hover");
		var t = parseInt($(e).attr("length"));
		d.empty();
		var i = function() {
				var e = $.Deferred();
				return l.animate({					
					width: 200 * t 
				}, 0, function() {
					e.resolve()
				}), e.promise()
			};
		$.when(i()).then(function() {
			d.html($(e).data("chlidHtml"))
		})
	});
	$(".gainet-product-title").each(function(e) {
		var t = $(this).offset().top - 120;
		r.push(t), $(".menu-box li:nth-child(" + (e + 1) + ")").data("top", t)
	});
	$(".menu-box li").click(function() {
		$("html, body").animate({
			scrollTop: $(this).data("top") + 20 + "px"
		}, "500")
	});
	$(window).scroll(function() {
		var e = $(document).scrollTop();
		e >= u ? ($(".float-box").addClass("fixed"), $("#product-btn,#floatMenu").show()) : ($(".float-box").removeClass("fixed"), $("#product-btn,#floatMenu").hide()), $.each(r, function(t, i) {
			if (r[t + 1]) {
				if (e >= i && e < r[t + 1]) return $(".menu-box li:nth-child(" + (t + 1) + ")").addClass("action").siblings().removeClass("action"), !1
			} else e < r[0] ? $(".menu-box li").removeClass("action") : $(".menu-box li:last-child").addClass("action").siblings().removeClass("action")
		})
	})
	// 适应场景切换
	$(".scene-item").mouseenter(function () {
		var $_self = $(this);
		var activeClass = "scene--active";
		if (!$_self.hasClass(activeClass)) {
			$_self.addClass(activeClass).siblings().removeClass(activeClass);
		}
	});
})
