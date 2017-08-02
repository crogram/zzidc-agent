<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

/**
 * -------------------------------------------------------
 * | 产品模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class WhiteModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_white';

	//搜索条件字段
	public $searchable = [
			'create_time' => [
					'display_name' => '创建时间',
					'html_type' => 'date',
					'start_name' => '_start',
					'end_name' => '_end',
			],
    	    'key' => [
    	        'domain' => '域名',
    	    ],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'编号',
					'sortable' => true,
			],
			'domain' => [
					'display_name' =>'域名',
					'sortable' => false,
			],
			'ip_address' => [
					'display_name' =>'IP地址',
					'sortable' => false,
			],
			'create_time' => [
					'display_name' => '创建时间',
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
		//$fill_data['product_type'] = D('Common/ProductType')->getAll('id as k,type_name as v');
		
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
		/**
		 * ---------------------------------------------------
		 * | 不允许修改
		 * | @时间: 2016年12月22日 下午1:53:18
		 * ---------------------------------------------------
		 */
		return false;
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

		/**
		 * ---------------------------------------------------
		 * | 不允许修改
		 * | @时间: 2016年12月22日 下午1:53:18
		 * ---------------------------------------------------
		 */
		return false;
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
	
	/**
	 * ----------------------------------------------
	 * | 删除数据前的回调方法
	 * | @时间: 2016年11月13日 下午2:44:15
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _before_delete($options){
		
	}

	/**
	 * ----------------------------------------------
	 * | 删除成功后的回调方法
	 * | @时间: 2016年11月13日 下午2:44:25
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _after_delete($data, $options){

	}
	
	


}
