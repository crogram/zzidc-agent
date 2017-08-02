<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use Common\Respository\CloudhostBusinessRespository;
use Common\Data\GiantAPIParamsData as GiantAPIParams;
/**
 * -------------------------------------------------------
 * | 会员管理
 * | @author: duanbin
 * | @时间: 2016年10月21日 下午3:35:10
 * | @version: 1.0
 * -------------------------------------------------------
 */
class CloudhostController extends BackendController{
	
	
	
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
		
		$this->error('当前访问页面不存在');
		
		parent::_initialize();
		$this->model = new \Backend\Model\CloudhostModel();
		$this->respository = new CloudhostBusinessRespository($this->model);
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
		//获取统计数据
		$counter = $this->respository->counter($field);
		
		// 		dump($data);die;
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
				'counter' => $counter,
		]);
		if (IS_AJAX){
			return $this->fetch();
		}else {

			$this->display();
		}
	}

	
	/**
	 * ----------------------------------------------
	 * | 录入cloudhost业务页面
	 * | @时间: 2016年10月28日 上午9:40:28
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
// 	public function create(){

// 		$extra['area_code'] = (new \Common\Model\RegionModel())->getArea();
// 		$this->assign([
// 				'state' => $this->model->searchable['state']['data'],
// 				'free_trial' => [	//业务状态
// 						1 => '试用',
// 						0 => '购买',
// 				],
// 				'extra' => $extra,
// 		]);
// 		$this->display();
// 	}
	
	/**
	 * ----------------------------------------------
	 * | 新增录入cloudhost业务
	 * | @时间: 2016年11月2日 下午6:26:58
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
// 	public function recording(){
		
// 		$request = request(true);
		
// 		$res = $this->respository->create($request);
// 		// 	dump($res);die;
// 		//返回结果
// 		if ($res === false) {
// 			$msg = $this->model->getError().'录入业务信息失败，请重试！';
// 			if (IS_AJAX) {
// 				$this->error( $msg, '',true);
// 			}else {
// 				$this->error($msg);
// 			}
// 		}else {
// 			$msg = '录入业务信息成功';
// 			$url = U('Backend/cloudhost/index', [  ], false);
// 			if (IS_AJAX) {
// 				$this->success($msg, $url, true);
// 			}else {
// 				$this->success($msg, $url);
// 			}
// 		}
// 	}
	
	/**
	 * ----------------------------------------------
	 * | 更新cloudhost业务页面
	 * | @时间: 2016年10月14日 下午2:46:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function update(){
	
		$id = request()['id']+0;
	
		$entity = $this->respository->firstOrFalse( [ 'id' => [ 'eq', $id ] ] );
	
		if (!$entity) {
			$this->error('参数有误！，没有该云主机业务的相关信息');
			die;
		}
	
// 		dump($entity);die;
		$this->assign([
				'entity' => $entity,
				'state' => $this->model->searchable['state']['data'],
				'free_trial' => [	//业务状态
						1 => '试用',
						0 => '购买',
				],
		]);
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新cloudhost业务信息
	 * | @时间: 2016年10月14日 下午5:48:44
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function store(){
		//取参数
		$request = request(true);
		//dump($request);
		$id = $request['id'];
		unset($request['id']);
		$data = $request;
		//更新操作
		$res = $this->respository->update($data, $id, 'id');
// 	dump($res);die;
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'更新业务信息失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '更新业务信息成功';
			$url = U('Backend/Cloudhost/index', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	
	}
	
	/**
	 * ----------------------------------------------
	 * | 删除cloudhost业务
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
		$res = $this->respository->eraseCloudhostBusiness($id, GiantAPIParams::PTYPE_CLOUD_HOST);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '删除业务成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	}
	
	
	/**
	 * ----------------------------------------------
	 * | cloudhost业务详情页面
	 * | @时间: 2016年10月27日 上午11:26:01
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function details() {
		//获取业务id
		$id = request()['id']+0;
		
		//获取业务信息
		$entity = $this->respository->firstOrFalse( [ 'id' => [ 'eq', $id ] ] );
		if (!$entity) {
			$this->error('参数有误！，没有该业务的相关信息');
			die;
		}
		
		//获取业务的产品配置相关信息
		$config = (new \Common\Model\ProductConfigModel())->getConfigByPId($entity['product_id']);

		$area_code = (new \Common\Model\RegionModel())->getArea();
	
// 		dump($entity);die;
		$this->assign([
				'entity' => $entity,
				'free_trial' => [	//账户状态
						1 => '试用',
						0 => '购买',
				],
				'state' => $this->model->searchable['state']['data'],
				'id' => $id,
				'area_code' => $area_code,
				'config' => $config,
		]);
		
		$this->display();
		
	}

	
	
	/**
	 * ----------------------------------------------
	 * | 转让cloudhost业务
	 * | @时间: 2016年10月28日 上午11:24:21
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function assignment(){
		
		//取参数
		$request = request();
		//转让cloudhost操作
		$res = $this->respository->assignment($request);
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '操作成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	}
	
	
	
	/**
	 * ----------------------------------------------
	 * | 同步cloudhost业务
	 * | @时间: 2016年10月28日 上午11:24:21
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function sync(){
		
		//取参数
		$request = request();
		$id = $request['id'];
		//同步cloudhost操作
		$res = $this->respository->CloudhostBusinessSynchronizing($id, GiantAPIParams::PTYPE_CLOUD_HOST);
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '操作成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	}

	/**
	 * ----------------------------------------------
	 * | 批量更新同步业务信息
	 * | @时间: 2016年11月1日 下午4:51:23
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function syncBatch(){
		//取参数
		$request = request();
		$flag = $request['flag'];
		//批量同步cloudhost操作
		$res = $this->respository->syncBatch(GiantAPIParams::PTYPE_CLOUD_HOST, $flag);
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = $res;
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
	 * | @时间: 2017年1月3日 下午2:50:31
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function batcherase(){
		//取参数
		$request = request();
		//转让vps操作
		$res = $this->respository->batchErase($request);
	
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
