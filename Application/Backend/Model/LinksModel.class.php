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
class LinksModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_links';
	//新增是允许写入的字段
	protected $insertFields = [
			'link_name', 'link_url','link_explain','state'
	];
	//更新是允许更新的字段
	protected $updateFields = [
			'link_name', 'link_url','link_explain','state'
	];
	
	
	
	//搜索条件字段
	public $searchable = [
			'state' => [
					'display_name' => '状态',
					'html_type' => 'select',
					'data' => [
							0=> '前台不显示',
							1=> '前台显示',
					],
			],
			'key' => [
				'link_name' => '友链名称',
				'link_explain' => '说明',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'编号',
					'sortable' => true,
			],
			'link_name' => [
					'display_name' =>'友链名称',
					'sortable' => false,
			],
			'link_url' => [
					'display_name' =>'友链地址',
					'sortable' => false,
			],
			'link_explain' => [
					'display_name' =>'说明',
					'sortable' => false,
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
		
		//处理问题内容的编码，统一转换成utf-8的编码，然后查看字数
		if (!empty($data['link_name'])){		
			$link_name = mb_convert_encoding($data['link_name'], 'utf-8');
			if ( mb_strlen($link_name, 'utf-8') >= 50*3) {
				$this->error = '友链名称最多只有50个字';
				return false;
			}
		}
		if (!empty($data['link_url'])){		
			$link_url = mb_convert_encoding($data['link_url'], 'utf-8');
			if ( mb_strlen($link_url, 'utf-8') >= 100*3) {
				$this->error = '友链地址最多只有100个字';
				return false;
			}
		}
		if (!empty($data['link_explain'])){
			$link_explain = mb_convert_encoding($data['link_explain'], 'utf-8');
			if ( mb_strlen($link_explain, 'utf-8') >= 1000*3) {
				$this->error = '说明最多只有1000个字';
				return false;
			}
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

		//处理问题内容的编码，统一转换成utf-8的编码，然后查看字数
		if (!empty($data['link_name'])){		
			$link_name = mb_convert_encoding($data['link_name'], 'utf-8');
			if ( mb_strlen($link_name, 'utf-8') >= 50*3) {
				$this->error = '友链名称最多只有50个字';
				return false;
			}
		}
		if (!empty($data['link_url'])){		
			$link_url = mb_convert_encoding($data['link_url'], 'utf-8');
			if ( mb_strlen($link_url, 'utf-8') >= 100*3) {
				$this->error = '友链地址最多只有100个字';
				return false;
			}
		}
		if (!empty($data['link_explain'])){
			$link_explain = mb_convert_encoding($data['link_explain'], 'utf-8');
			if ( mb_strlen($link_explain, 'utf-8') >= 1000*3) {
				$this->error = '说明最多只有1000个字';
				return false;
			}
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
