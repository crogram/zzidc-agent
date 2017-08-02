<?php
namespace Common\Respository;
use Common\Respository\BaseRespository;

/**
 * -------------------------------------------------------
 * | 产品仓储类
 * | 可选择实现orderExtra()，whereExtra()方法，
 * | 为了有连表的情况出现，额外设置别名
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:50:29
 * | @version: 1.0
 * -------------------------------------------------------
 */
class ProductRespository extends BaseRespository{
	

	/**
	 * ----------------------------------------------
	 * | 额外的扩充条件，这里要增加一项
	 * | product_id值得条件
	 * | @时间: 2016年10月13日 下午6:34:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function whereExtra($where, $request){
// 		$_where = [];
// 		foreach ($where as $k => $v){
// 			$_where[$this->alias.'.'.$k] = $v;
// 		}
	
// 		$_where[$this->alias.'.product_id'] = [ 'eq' , (int)$request['product_id'] ];

		// 		dump($_where);die;
		
// 		return $_where;

		
		$where['_string'] = ' find_in_set(2,stage) ';
		return  $where;
		
	}
	
}