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
class CommonProblemModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_common_problem';
	//新增是允许写入的字段
	protected $insertFields = [
			'product_type', 'problem_title', 'state','problem_content','create_time','update_at'
	];
	//更新是允许更新的字段
	protected $updateFields = [
			'product_type', 'problem_title', 'state','problem_content','update_at'
	];
	
	
	
	//搜索条件字段
	public $searchable = [
			'product_type' => [
					'display_name' => '所属分类',
					'html_type' => 'select',
					'data' => [],
			],
			'state' => [
					'display_name' => '是否展示',
					'html_type' => 'select',
					'data' => [
							'0' => '不显示',
							'1' => '显示',
					],
			],
			'create_time' => [
					'display_name' => '创建时间',
					'html_type' => 'date',
					'start_name' => '_start',
					'end_name' => '_end',
			],
			'key' => [
				'problem_title' => '问题标题',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'编号',
					'sortable' => true,
			],
			'problem_title' => [
					'display_name' =>'标题',
					'sortable' => false,
			],
			'product_type' => [
					'display_name' => '问题分类',
					'sortable' => false,
			],
			'state' => [
					'display_name' => '是否显示',
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
		$fill_data['product_type'] = D('Common/ProductType')->getAll('id as k,type_name as v');
		
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
		//添加创建时间默认值
		$data['create_time'] = date('Y-m-d H:i:s', time());
		$data['update_at'] = date('Y-m-d H:i:s', time());
		
		//处理问题内容的编码，统一转换成utf-8的编码，然后查看字数
		$problem_content = mb_convert_encoding($data['problem_content'], 'utf-8');
		if ( mb_strlen($problem_content, 'utf-8') >= 10000*3) {
			$this->error = '内容最多只有10000个字';
			return false;
		}
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

		$data['update_at'] = date('Y-m-d H:i:s', time());
		//处理问题内容的编码，统一转换成utf-8的编码，然后查看字数
		$problem_content = mb_convert_encoding($data['problem_content'], 'utf-8');
		if ( mb_strlen($problem_content, 'utf-8') >= 10000*3) {
			$this->error = '内容最多只有10000个字';
			return false;								
		}

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
	

	


}
