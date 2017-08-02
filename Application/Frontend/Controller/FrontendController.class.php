<?php
namespace Frontend\Controller;
use Common\Controller\BaseController;

/**
 * -------------------------------------------------------
 * | 前台的基础控制器
 * |@author: duanbin
 * |@时间: 2016年9月30日 上午10:34:42
 * |@version: 1.0
 * -------------------------------------------------------
 */
class FrontendController extends BaseController{
	
	/**
	 * ----------------------------------------------
	 * |初始化函数
	 * |@时间: 2016年9月30日 上午10:35:01
	 * |@author: duanbin
	 * |@params: variable
	 * |@return:
	 * ----------------------------------------------
	 */
	public function _initialize(){
		parent::_initialize();
		//获取控制器名称和数据库比较输出模板关键词
		//记录http中Referer参数，登陆之后的跳转链接判断这个地方
	   session("backward",$_SERVER['HTTP_REFERER']);
	}
	


}