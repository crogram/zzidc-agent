<?php
namespace Common\Respository;
use Common\Respository\BaseRespository;

/**
 * -------------------------------------------------------
 * | 产品价格仓储类
 * | 可选择实现orderExtra()，whereExtra()，fieldsExtra()方法，
 * | 为了有连表的情况出现，额外设置别名
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:50:29
 * | @version: 1.0
 * -------------------------------------------------------
 */
class AdRespository extends BaseRespository{
	
	
	
	/**
	 * ----------------------------------------------
	 * | 扩充查询字段，在连表的情况下有别名使用
	 * | @时间: 2016年10月14日 上午10:19:58
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function fieldsExtra($fields){

		return $fields;
	}
	
	/**
	 * ----------------------------------------------
	 * | 返回一个join数组
	 * | 连表查询时，请实现此方法
	 * | @时间: 2016年10月13日 下午6:14:18
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function joinsExtra(){
		return [];
	}
	
	
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

		return $where;
	}

	/**
	 * ----------------------------------------------
	 * | 额外的扩充排序，这里要增加一项
	 * | product_id值得条件
	 * | @时间: 2016年10月13日 下午6:34:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function orderExtra($order, $request){
		//增加默认排序规则
		if (empty($order)){
			//到期时间倒序
			$order = 'create_at DESC';
		}
		
		return $order;
	}
	
	
	
	
}