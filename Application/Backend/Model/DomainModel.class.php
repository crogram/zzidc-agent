<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

/**
 * -------------------------------------------------------
 * | 会员模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class DomainModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_domain_business';
	
	/**
	 * ---------------------------------------------------
	 * | 页面展示用
	 * | @时间: 2016年11月4日 下午5:26:17
	 * ---------------------------------------------------
	 */
	private $configDisplay = [
			0 => '不支持',
			1 => '支持',
	];
	
	//搜索条件字段
	public $searchable = [
			'state' => [
					'display_name' => '状态',
					'html_type' => 'select',
					'data' => [
							0 => '无效',
							1 => '有效',
							2 => '已过期',
							3 => '已删除',
					],
			],
			'api_type' => [
					'display_name' => '域名注册商',
					'html_type' => 'select',
					'data' => [
							3 =>'万网',
							4 => '新网',
							5 => '中国数据',
							6 => '新网互联',
							7 => 'ResellerClub',
					],
			],
			'overdue_time' => [
					'display_name' => '到期时间',
					'html_type' => 'date',
					'start_name' => '_start',
					'end_name' => '_end',
			],
			'key' => [
				'product_name' => '产品名称',
				'login_name' => '登陆名',
				'domain_name' => '域名名称',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'编号',
					'sortable' => true,
			],
			'domain_name' => [
					'display_name' => '域名名称',
					'sortable' => false,
			],
			'login_name' => [
					'display_name' =>'登陆名',
					'sortable' => false,
			],
			'user_id' => [
					'display_name' =>'用户id',
					'sortable' => false,
			],
			'provider' => [
					'display_name' => '域名注册商',
					'sortable' => false,
			],
			'dwmc' => [
					'display_name' => '注册单位名称',
					'sortable' => false,
			],
			'api_bid' => [
					'display_name' =>'业务id',
					'sortable' => true,
			],
			'product_name' => [
					'display_name' =>'产品名称',
					'sortable' => false,
			],
			'create_time' => [
					'display_name' => '创建时间',
					'sortable' => true,
			],
			'overdue_time' => [
					'display_name' => '到期时间',
					'sortable' => true,
			],
			'state' => [
					'display_name' => '状态',
					'sortable' => false,
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
// 		$fill_data['product_type_id'] = D('Common/ProductType')->getAll('id as k,type_name as v');
		//区域数据
// 		$fill_data['area_code'] = D('Common/Region')->getAll('region_code as k,region_name as v');
		
		//父类里继承来的属性
// 		$this->fillData = $fill_data;
		return $this;
	}
	
	
	/**********************************新增更新回调/开始***********************************/
	
	/**
	 * ----------------------------------------------
	 * | 插入数据之前的回调
	 * | @时间: 2016年10月17日 上午11:28:21
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _before_insert(&$data, $options){

	}
	
	/**
	 * ----------------------------------------------
	 * | 插入成功后的回调
	 * | @时间: 2016年10月17日 上午11:28:50
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _after_insert($data, $options){
	
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新数据之前做的操作
	 * | @时间: 2016年10月17日 上午11:26:36
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _before_update(&$data, $options){

	}
	
	/**
	 * ----------------------------------------------
	 * | 更新之后做的操作
	 * | @时间: 2016年10月17日 上午11:27:09
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _after_update($data, $options){
	
	}
	
	/**********************************新增更新回调/结束***********************************/
		

	
	

}
