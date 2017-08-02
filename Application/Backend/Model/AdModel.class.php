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
class AdModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_ad';
	//新增是允许写入的字段
	protected $insertFields = [
			'title', 'is_show','img_url','create_at','update_at','location','link_url,description'
	];
	//更新是允许更新的字段
	protected $updateFields = [
			'title', 'is_show','img_url','update_at','location','link_url,description'
	];
	
	
	
	//搜索条件字段
	public $searchable = [
			'location' => [
					'display_name' => '所属分类',
					'html_type' => 'select',
					'data' => [
							1 => '首页',
							2 => '虚拟主机列表页',
							3 => 'VPS列表页',
							4 => 'SSL证书产品页',
					],
			],
			'is_show' => [
					'display_name' => '是否展示',
					'html_type' => 'select',
					'data' => [
							'2' => '不显示',
							'1' => '显示',
					],
			],
			'create_at' => [
					'display_name' => '创建时间',
					'html_type' => 'date',
					'start_name' => '_start',
					'end_name' => '_end',
			],
			'key' => [
				'title' => '图片标题',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'编号',
					'sortable' => true,
			],
			'title' => [
					'display_name' =>'图片标题',
					'sortable' => false,
			],
			'link_url' => [
					'display_name' =>'跳转链接',
					'sortable' => false,
			],
			'img_url' => [
					'display_name' =>'图片',
					'sortable' => false,
			],
			'location' => [
					'display_name' => '广告位置',
					'sortable' => false,
			],
			'order_number' => [
					'display_name' => '排序',
					'sortable' => true,
			],
			'is_show' => [
					'display_name' => '是否显示',
					'sortable' => false,
			],
			'create_at' => [
					'display_name' => '创建时间',
					'sortable' => true,
			],
			'update_at' => [
					'display_name' => '更新时间',
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
		
		//处理上传图片的问题
		$input_name = 'img_url';
		if(isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0){
			$img_uploader = new \Common\Aide\ImagesAide();
			$res_info = $img_uploader->ImgUploader($input_name, 'ad');
			//上传失败，返回错误信息
			if (!$res_info['ok']){
				$this->error = $res_info['error'];
				return false;
			}else {
				//上传图片成功，那么就保存图片路径到数据库
				$data[$input_name] = $res_info['file'][0];
			}
		}else {
			$this->error = '您没有添加图片哦！';
			return false;
		}
		//添加创建时间默认值
		$data['create_at'] = date('Y-m-d H:i:s', time());
		$data['update_at'] = date('Y-m-d H:i:s', time());
		
		//处理问题内容的编码，统一转换成utf-8的编码，然后查看字数
		$title = mb_convert_encoding($data['title'], 'utf-8');
		if ( mb_strlen($title, 'utf-8') >= 50*3) {
			$this->error = '标题最多只有50个字';
			return false;
		}
		$description = mb_convert_encoding($data['description'], 'utf-8');
		if ( mb_strlen($description, 'utf-8') >= 500*3) {
			$this->error = '描述最多只有500个字';
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

		//处理上传图片的问题
		$input_name = 'img_url';
		if(isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0){
			$img_uploader = new \Common\Aide\ImagesAide();
			$res_info = $img_uploader->ImgUploader($input_name, 'ad');
			//上传失败，返回错误信息
			if (!$res_info['ok']){
				$this->error = $res_info['error'];
				return false;
			}else {
				//上传图片成功，那么就保存图片路径到数据库
				$data[$input_name] = $res_info['file'][0];
			}
		}

		$data['update_at'] = date('Y-m-d H:i:s', time());
		//处理问题内容的编码，统一转换成utf-8的编码，然后查看字数
			$title = mb_convert_encoding($data['title'], 'utf-8');
		if ( mb_strlen($title, 'utf-8') >= 50*3) {
			$this->error = '标题最多只有50个字';
			return false;
		}
		$description = mb_convert_encoding($data['description'], 'utf-8');
		if ( mb_strlen($description, 'utf-8') >= 500*3) {
			$this->error = '描述最多只有500个字';
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

		$info = $this->where($options['where'])->find();
		$path = '.'.$info['img_url'];
		//如果图片存在，就删除掉；
		if (is_file($path)){		
			if (!remove_file($path)){
				$this->error = 'oups,删除图片失败！可能已经不存在改图片了。';
				return false;
			}
		}
		
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
