<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * -------------------------------------------------------
 * | 产品模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class RegionModel extends BaseModel{
	
	//关联的表名

	
	/**
	 * ----------------------------------------------
	 * | 返回一组key为数字,val为汉字的区域数组
	 * | @时间: 2016年10月28日 下午4:22:56
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getArea() {
		$res = $this->select();
		$_res = [];
		foreach ($res as $k => $v){
			$_res[$v['region_code']] = $v['region_name'];
		}
		return $_res;
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 获取所有的区域
	 * | @时间: 2016年11月9日 上午10:08:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getAll($field = '*', $where = [], $order = '') {
		$res = parent::getAll( $field, $where, $order);
		$_res = [];
		foreach ($res as $k => $v) {
			$_res[$v['k']] = $v['v'];
		}
		// 		dump($res);die;
		return $_res;
	}

}
