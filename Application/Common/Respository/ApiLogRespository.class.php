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
class ApiLogRespository extends BaseRespository{
	
	
	
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
		if (is_string($fields)){
			$fields = explode(',', $fields);
		}
		foreach ($fields as $k => $filed){
			if ($filed == 'login_name'){
				$fields[$k] = 'b.'.$filed;
			}else{
				$fields[$k] = $this->alias.'.'.$filed;
			}
		}
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
		return [
				'left join '.$this->table_prefix.'member as b on '.$this->alias.'.user_id = b.user_id',
		];
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
		$_where = [];
		foreach ($where as $k => $v){
			if ($k == 'login_name'){
				$_where['b.login_name'] = $v;
			}else {
				$_where[$this->alias.'.'.$k] = $v;
			}
		}
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
			$order = $this->alias . '.' . 'create_time DESC';
		}else {
			$order = $this->alias . '.' . $order;
		}
		
		return $order;
	}
	
	
	
	
}