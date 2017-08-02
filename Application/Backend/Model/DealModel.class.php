<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

/**
 * -------------------------------------------------------
 * | 交易模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class DealModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = "agent_member_transactions";
	
	
	
	//搜索条件字段
	public $searchable = [
			'type' => [
					'display_name' => '交易类型',
					'html_type' => 'select',
					'data' => [
							'0' => '充值',
							'1' => '消费',
							'2' => '提现',
					],
			],
			'create_time' => [
					'display_name' => '交易时间',
					'html_type' => 'date',
					'start_name' => '_start',
					'end_name' => '_end',
			],
			'key' => [
				'order_id' => '订单编号',
				'product_name' => '产品名称',
				'note_appended' => '交易说明',
				'login_name' => '登录名',
				'remark' => '录款备注',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'ID',
					'sortable' => true,
			],
			'login_name' => [
					'display_name' =>'登录名',
					'sortable' => false,
			],
			'user_id' => [
					'display_name' =>'用户id',
					'sortable' => false,
			],
			'order_id' => [
					'display_name' =>'订单编号',
					'sortable' => true,
			],
			'account_money' => [
					'display_name' =>'交易金额',
					'sortable' => true,
			],
			'type' => [
					'display_name' => '交易类型',
					'sortable' => true,
			],
			'product_name' => [
					'display_name' => '产品名称',
					'sortable' => false,
			],
			'note_appended' => [
					'display_name' => '交易说明',
					'sortable' => false,
			],
			'remark' => [
					'display_name' => '录款备注',
					'sortable' => false,
			],
			'create_time' => [
					'display_name' => '交易时间',
					'sortable' => true,
			],
	];
	
	
	/**
	 * ----------------------------------------------
	 * | 获取搜索条件所需要的数据
	 * | 可以实现getFillData方法，
	 * | 将一些需要的数据复给$this->fill_data
	 * | 例如本类的getFillData()方法
	 * | @时间: 2016年10月11日 下午3:03:09
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getSearchable($fill_data = []){
		if (empty($fill_data)) {
			return $this->fillSearchable($this->fillData);
		}else {
			return $this->fillSearchable($fill_data);
		}
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 获取搜索条件需要的数据
	 * | @时间: 2016年10月12日 下午2:54:05
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: array
	 * ----------------------------------------------
	 */
	public function getFillData(){
		
		/**
		 * ---------------------------------------------------
		 * | 这里注意返回的一定是一个数组，
		 * | 并且数组的key一定是与searchable数组的key相同 
		 * | @时间: 2016年10月12日 下午2:54:52
		 * ---------------------------------------------------
		 */
		
		//产品分类数据
// 		$fill_data['product_type'] = D('Common/ProductType')->getAll('id as k,type_name as v');
		//区域数据
// 		$fill_data['area_code'] = D('Common/Region')->getAll('region_code as k,region_name as v');
		
		//父类里继承来的属性
// 		$this->fillData = $fill_data;
		return $this;
	}
	
	
	
	
	
	
	
	
	/**
	 * ----------------------------------------------
	 * | 计算首页要用的数据
	 * | 统计出前12个月的交易数据
	 * | @时间: 2016年11月25日 下午4:35:08
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getDealCount(){
		
		$field = "DATE_FORMAT(t.create_time, '%Y-%m') month,sum(t.account_money) money";
		$where['type'] = [ 'eq', 1 ];
		$where['_string'] = "DATE_FORMAT(create_time, '%Y-%m') > DATE_FORMAT(date_sub(curdate(), INTERVAL 12 month),'%Y-%m')";
		$_res = $this->alias('t')->field($field)->where($where)->group('month')->select();
		$res =[];
		$month = [];
		$i = 1;
		foreach ($_res as $k => $v){
			$res[] = [ $i, $v['money'] ];
			$month[] = [ $i, $v['month'] ];
			$i++;
		}

		if (!$res){
			$this->error = '暂无交易记录';
			return false;
		}else {
			$return['money'] = $res;
			$return['month'] = $month;
			return $return;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

}
