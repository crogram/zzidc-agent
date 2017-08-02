<?php
namespace Api\Controller;
use Api\Controller\ApiController;

/**
 * -------------------------------------------------------
 * | api基础控制器
 * |@author: duanbin
 * |@时间: 2016年9月30日 上午10:34:42
 * |@version: 1.0
 * -------------------------------------------------------
 */
class UeditorController extends ApiController{

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

	}

	/**
	 * ----------------------------------------------
	 * | 富文本编辑器的图片上传
	 * | @时间: 2016年11月9日 上午11:59:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function ueditor(){
		$data = new \Common\Aide\UeditorAide();
		echo $data->output();
	}


}