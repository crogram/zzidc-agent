<?php
namespace Api\Controller;

/**
 * -------------------------------------------------------
 * | api基础控制器
 * |@时间: 2016年9月30日 上午10:34:42
 * |@version: 1.0
 * -------------------------------------------------------
 */
class ShengpayController extends ApiController{
	
	protected $aide = null;

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
		
		$this->aide = new \Common\Aide\ShengpayAide();
	}
	
	/**
	 * ----------------------------------------------
	 * | 盛付通异步通知回调接口
	 * | @时间: 2016年12月8日 上午10:07:40
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function notifyurl(){
		$this->aide->notifyurl();
	}
	
	/**
	 * ----------------------------------------------
	 * | 盛付通页面跳转通知回调接口
	 * | @时间: 2016年12月8日 上午10:08:51
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function returnurl(){
		$this->aide->returnurl();
	}


}