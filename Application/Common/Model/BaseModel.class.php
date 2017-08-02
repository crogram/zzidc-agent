<?php
namespace Common\Model;
use Think\Model;

/**
 * -------------------------------------------------------
 * | 全局基础模型类
 * |@author: duanbin
 * |@时间: 2016年9月30日 上午10:58:27
 * |@version: 1.0
 * -------------------------------------------------------
 */
class BaseModel extends Model{

	
	

	

	
	
	
	
	/**
	 * ----------------------------------------------
	 * | 返回所有的数据表记录
	 * | ******************慎用******************
	 * | @时间: 2016年10月11日 下午3:37:31
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getAll( $field = '*', $where = [], $order = '' ){
		return $this->field($field)->where($where)->order($order)->select();
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 设置错误信息
	 * | @时间: 2016年11月4日 下午6:22:19
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function setError($error) {
		$this->error = $error;
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取属性的get方法
	 * | @时间: 2016年11月5日 下午3:41:41
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function __get($name){
		return property_exists($this, $name) ? $this->$name: null;
	}
	
}
