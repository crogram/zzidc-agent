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
class ClouddbModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_clouddb_business';
	
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
				1 => '正常',
				2 => '已删除',
				3 => '已过期',
				4 => '失败',
			],
		],
		'area_code' => [
			'display_name' => '地区',
			'html_type' => 'select',
			'data' => [],
		],
		'overdue_time' => [
			'display_name' => '到期时间',
			'html_type' => 'date',
			'start_name' => '_start',
			'end_name' => '_end',
		],
		'key' => [
			'api_bid' => '业务id',
			'login_name' => '登陆名',
			'wwdz' => '外网IP',
			'nwdz' => '内网IP',
		],
	];


	//排序字段
	public $sortable = [
		'id' => [
			'display_name' =>'编号',
			'sortable' => true,
		],
		'wwdz' => [
			'display_name' => 'IP',
			'sortable' => false,
		],
		'nwdz' => [
			'display_name' => 'IP',
			'sortable' => false,
		],
		'api_bid' => [
			'display_name' =>'业务id',
			'sortable' => true,
		],
		'login_name' => [
			'display_name' =>'登陆名',
			'sortable' => false,
		],
		'user_id' => [
			'display_name' =>'用户id',
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
		'buy_time' => [
			'display_name' => '购买时长(月)',
			'sortable' => true,
		],
		'version' => [
			'display_name' => '产品配置',
			'sortable' => false,
		],
		'memory' => [
			'display_name' => '内存',
			'sortable' => false,
		],
		'disk' => [
			'display_name' => '数据盘',
			'sortable' => false,
		],
		'area_code' => [
			'display_name' => '地区',
			'sortable' => false,
		],
		'state' => [
			'display_name' => '状态',
			'sortable' => false,
		],
		'iops' => [
			'display_name' => 'IOPS',
			'sortable' => false,
		],
        'conn' => [
            'display_name' => '最大连接数',
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
		$fill_data['area_code'] = D('Common/Region')->getAll('region_code as k,region_name as v');
		
		//父类里继承来的属性
		$this->fillData = $fill_data;
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
