<?php
namespace Frontend\Behavior;
/**
 * -------------------------------------------------------
 * | 判断设备是否是移动端，来决定采用什么页面
 * |@author: duanbin
 * |@时间: 2016年9月29日 下午5:25:06
 * |@version: 1.0
 * -------------------------------------------------------
 */
class DetectDeviceBehavior {
	// 行为扩展的执行入口必须是run
	public function run(&$params){
		/**
		 * ---------------------------------------------------
		 * |如果访问的是前台，并且是移动端，那么就返回相应的移动端的页面
		 * |待开发
		 * | TODO:
		 * |@时间: 2016年9月29日 下午5:34:55
		 * ---------------------------------------------------
		 */
	if (MODULE_NAME == 'Frontend' && isMobile() == 'phone'){
	       header("Content-type: text/html; charset=utf-8");
 			C('DEFAULT_V_LAYER', 'Mobile');
 			C('TMPL_ACTION_MOBILE_ERROR', THINK_PATH . 'Tpl/dispatch_mobile_jump.tpl'); // 默认错误跳转对应的模板文件
 			C('TMPL_PARSE_STRING', [
 			    //前台手机站静态资源
 			    '__FRONTEND_MOBILE_CSS__'      => PUBLIC_PATH.'frontend/mobile/css/',
 			    '__FRONTEND_MOBILE_JS__'       => PUBLIC_PATH.'frontend/mobile/js/',
 			    '__FRONTEND_MOBILE_IMAGES__'   => PUBLIC_PATH.'frontend/mobile/images/',
 			]);
 		}
		return $params;
	}
}