<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

/**
 * -------------------------------------------------------
 * | 后台模板模型
 * | @author: duanbin
 * | @时间: 2016年10月9日 上午10:37:46
 * | @version: 1.0
 * -------------------------------------------------------
 */
class TestModel extends BackendModel{


	//搜索条件字段
	public $searchable = [
			'product_type_id' => [
					'dispaly_name' => '分类',
					'html_type' => 'select',
					'data' => [],
			],
			'product_state' => [
					'dispaly_name' => '状态',
					'html_type' => 'select',
					'data' => [
							'0' => '下线',
							'1' => '上线',
					],
			],
			'key' => [
					'product_name' => '产品名称',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'编号',
					'sortable' => true,
			],
			'product_name' => [
					'display_name' =>'名称',
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
		$fill_data['product_type_id'] = D('Common/ProductType')->getAll('id as k,type_name as v');
		//区域数据
		$fill_data['area_code'] = D('Common/Region')->getAll('region_code as k,region_name as v');
	
		//父类里继承来的属性
		$this->fillData = $fill_data;
		return $this;
	}
	

}
