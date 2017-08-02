<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use \Common\Respository\ApiLogRespository;



class ApilogController extends BackendController
{


	/**
	 * ----------------------------------------------
	 * | 初始化仓储和模型
	 * | @时间: 2016年10月9日 下午4:49:54
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function _initialize(){
		parent::_initialize();
		$this->model = new \Backend\Model\ApiLogModel();
		$this->respository = new ApiLogRespository($this->model);
		
	}
	
	
	/**
	 * 用户列表
	 */
	public function index(){
	
		//表头，排序相关数据
		$th = $this->model->getSortable();
		//获取表头里面真正有排序的字段
		$sorted = $this->model->getSortableField($th);
		//未加排序前缀的表头
		$th_raw = array_keys($this->model->sortable);
	
		//获取筛选字段的需要的数据,筛选所需要的字段
		$search = $this->model->getFillData()->getSearchable();
	
		//获取需要展示的字段，列表的表头
		$field = implode(',', array_keys($this->model->sortable));
		//获取所有的数据
		$data = $this->respository->finder($field);

		//处理分页参数问题
		$current_page = empty(request()['p']) ? 1: request()['p'];
	
		$this->assign([
				'search' => $search,
				'th' => $th,
				'th_count' => count($th),	//表格列数
				'th_raw' => $th_raw,
				'sorted' => $sorted,	//真正可以排序的字段
				'data' => $data['data'],
				'sum_pages' => $data['sumPages'],
				'total' => $data['total'],
				'current_page' => $current_page,
				'per_page' => $data['perPage'],
				'pages' => $data['show'],
				'request' => request(),
		]);
		if (IS_AJAX){
			return $this->fetch();
		}else {

			$this->display();
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 会员详情页面
	 * | @时间: 2016年10月27日 上午11:26:01
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
// 	public function details() {
// 		//获取会员id
// 		$id = request()['id']+0;
	
// 		//获取会员信息
// 		$entity = $this->respository->firstOrFalse( [ 'id' => [ 'eq', $id ] ] );

// 		if (!$entity) {
// 			$this->error('参数有误！，没有改图片的相关信息');
// 			die;
// 		}
		
// // 		dump($product_type);die;
// 		$this->assign([
// 				'entity' => $entity,
// 				'id' => $id,
// 				'location' => $this->model->searchable['location']['data'],
// 				'is_show' => $this->model->searchable['is_show']['data'],
// 		]);
	
// 		$this->display();
	
// 	}
	
	/**
	 * ----------------------------------------------
	 * | 删除vps业务
	 * | @时间: 2016年10月17日 下午2:39:35
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function erase(){
		//取参数
		$request = request();
		$id = $request['id'];
		//删除业务操作
		$res = $this->respository->delete([ 'id' => [ 'eq', $id ] ]);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '删除成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 批量删除
	 * | @时间: 2016年12月22日 下午2:51:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function batchErase(){
		//取参数
		$request = request();
		$ids = $request['ids'];
		$ids = explode(',', $ids);
		foreach ($ids as $k => $v){
			$ids[$k] = $v +0;
		}
		$ids = implode(',', $ids);
		//删除业务操作
		$res = $this->respository->delete([ 'id' => [ 'in', $ids ] ]);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '删除成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	}

	
	
	
	
}