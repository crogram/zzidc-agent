<?php
namespace Common\Respository;
use Common\Respository\BaseRespository;

/**
 * -------------------------------------------------------
 * | 交易仓储类
 * | 可选择实现orderExtra()，whereExtra()，fieldsExtra()方法，
 * | 为了有连表的情况出现，额外设置别名
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:50:29
 * | @version: 1.0
 * -------------------------------------------------------
 */
class DealRespository extends BaseRespository{
	
	
	
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
			if ($filed == 'product_name'){
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
				'left join '.$this->table_prefix.'product as b on '.$this->alias.'.product_id = b.id',
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
			if ($k == 'product_name') {
				$_where['b.product_name'] = $v;
			}else {
				$_where[$this->alias.'.'.$k] = $v;
			}
		}
		/**
		 * ---------------------------------------------------
		 * | 这里是为了会员详情页面跳转增加的参数
		 * | @时间: 2016年10月27日 下午2:40:26
		 * ---------------------------------------------------
		 */
		if (!empty($request['user_id'])){
			$_where[$this->alias.'.user_id'] = $request['user_id'];
		}
		
// 		dump($_where);die;
		return $_where;
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
	
	/**
	 * ----------------------------------------------
	 * | 业务统计方法，统计出各个分类的总数等
	 * | @时间: 2016年10月26日 上午11:59:06
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function counter($field){
		$counter = [];
		
		/**
		 * ---------------------------------------------------
		 * | 总记录数
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		 */
		$counter['total'] = $this->queryBuilder($field, []);
		/**
		 * ---------------------------------------------------
		 * | 充值总数
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		 */
		$where['a.type'] =[ 'eq', 0 ];
		$counter['recharge'] = $this->queryBuilder($field, $where);
		
		/**
		 * ---------------------------------------------------
		 * | 消费总数
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		 */
		$where['a.type'] =[ 'eq', 1 ];
		$counter['spending'] = $this->queryBuilder($field, $where);
		
		/**
		 * ---------------------------------------------------
		 * | 提现总数
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		 */
		$where['a.type'] =[ 'eq', 2 ];
		$counter['withdraw'] = $this->queryBuilder($field, $where);
		
		/**
		 * ---------------------------------------------------
		 * | 财务统计(消费额总计)
		 * | @时间: 2016年10月26日 下午2:47:11
		 * ---------------------------------------------------
		 */
		$sum_where['type'] =[ 'in', '1,0' ];
		$counter['sum_money'] = $this->model()
		->where ($sum_where)->sum('account_money');
		
// 		dump($counter);die;
		return $counter;
	}


	
	
	
	
	
	
	
	
	
	
	
	
	
}