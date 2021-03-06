(function($) {
	$.fn.smartFloat = function() {
		var position = function(element) {
			var top = element.position().top, pos = element.css("position");
			$(window).scroll(function() {
				var scrolls = $(this).scrollTop();
				if (scrolls > top) {
					if (window.XMLHttpRequest) {
						element.css( {
							position : "fixed",
							top : 0,
							width : "100%",
							background : "#2dca60"
						});
					} else {
						element.css( {
							top : scrolls
						});
					}
				} else {
					element.css( {
						position : pos,
						top : top
					});
				}
			});
		};
		return $(this).each(function() {
			position($(this));
		});
	};
})(jQuery);