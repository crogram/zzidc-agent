$(function () {	
	/*
	 * 菜单侧收控制
	 */
	var $_body = $("body");
	var MenuClassName = "tender-menu";
	var menuCollapseCookieName = "menuCollapsed";
	
	$("#menuControl").click(function () {
		if ($_body.hasClass(MenuClassName)) {
			$_body.removeClass(MenuClassName);
			//$.removeCookie(menuCollapseCookieName, {path: "/"});
		}
		else {
			$_body.addClass(MenuClassName);
			//$.cookie(menuCollapseCookieName, "y", {expires:180, path: "/"});
		}
	});
	// 侧收时 一级菜单不响应点击事件（加上menu-linkable类的链接除外）
	$("html").on("click", ".tender-menu .upper-menu", function () {
		if (!$(this).hasClass("menu-linkable")) {
			return false;
		}
	});
});
