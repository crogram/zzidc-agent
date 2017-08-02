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
class ProductModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_product';
	
	
	
	//搜索条件字段
	public $searchable = [
			'product_type_id' => [
					'display_name' => '分类',
					'html_type' => 'select',
					'data' => [],
			],
			'area_code' => [
					'display_name' => '所属区域',
					'html_type' => 'select',
					'data' => [],
			],
			'system_type' => [
					'display_name' => '系统',
					'html_type' => 'select',
					'data' => [
							'0' => 'window',
							'1' => 'linux',
					],
			],
			'product_state' => [
					'display_name' => '状态',
					'html_type' => 'select',
					'data' => [
							'0' => '下线',
							'1' => '上线',
					],
			],
			'type' => [
					'display_name' => '类型',
					'html_type' => 'select',
					'data' => [
							'0' => '标准产品',
							'1' => '增值产品',
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
			'product_type_id' => [
					'display_name' =>'分类',
					'sortable' => false,
			],
			'system_type' => [
					'display_name' => '系统',
					'sortable' => false,
			],
			'product_state' => [
					'display_name' => '状态',
					'sortable' => false,
			],
			'area_code' => [
					'display_name' => '区域',
					'sortable' => false,
			],
			'type' => [
					'display_name' => '类型',
					'sortable' => false,		
			],
			'create_time' => [
					'display_name' => '创建时间',
					'sortable' => true,
			],
			'modify_time' => [
					'display_name' => '修改时间',
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
// 		$fill_data['product_type_id'] = D('Common/ProductType')->getAll('id as k,type_name as v');
		$fill_data['product_type_id'] = (new \Common\Model\ProductTypeModel())->getProductIndexType('id as k,type_name as v');
		//区域数据
		$fill_data['area_code'] = D('Common/Region')->getAll('region_code as k,region_name as v');
		
// 		dump($fill_data);die;
		//父类里继承来的属性
		$this->fillData = $fill_data;
		return $this;
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 获取某条产品的信息
	 * | 带有产品类型的(api_ptype)
	 * | @时间: 2016年10月25日 下午2:55:26
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getProductWithProductType($id){
		
		if (is_array($id)){
			$where = $id;
		}else {
			$where['a.id'] = [ 'eq', $id ];
		}
		
		$info = $this->alias('a')
		->field("a.id,a.product_type_id,a.product_name,a.api_name,a.area_code,b.type_name,b.api_ptype")
		->join('left join '.C('DB_PREFIX').'product_type as b on a.product_type_id = b.id')
		->where( $where )->find();
		
		return $info;
	}
	
	
	

}
