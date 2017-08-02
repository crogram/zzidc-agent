<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use Common\Respository\OrderRespository;

/**
 * -------------------------------------------------------
 * | 后台控制器模板
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午5:06:01
 * |@version: 1.0
 * -------------------------------------------------------
 */
class OrderController extends BackendController
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
		$this->model = D('Backend/Order');
		$this->respository = new OrderRespository($this->model);
	}
	
	/**
	 * ----------------------------------------------
	 * | 产品列表页
	 * | @时间: 2016年9月30日 下午3:30:32
	 * | @author: duanbin
	 * ----------------------------------------------
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
		
		//获取有使用的产品类型
		$catNotTrialType = D('Common/ProductType')->getCanNotTrialType();
		
// 		dump($catTrialType);die;
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
				'catNotTrialType' => $catNotTrialType,
		]);
		
		$this->display();
	}

	/**
	 * ----------------------------------------------
	 * | 更新价格页面
	 * | @时间: 2016年10月14日 下午2:46:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function update(){
	
		$id = request()['id']+0;
	
		$entity = $this->respository->firstOrFalse( $id );
	
		if (!$entity) {
			$this->error('参数有误！，没有改订单的相关信息');
			die;
		}
	
		$fill_data = $this->model->getFillData()->fillData;
// 		dump($entity);die;	
		$this->assign([
				'entity' => $entity,
				'extra' => $fill_data,
				'searchable' => $this->model->searchable,
		]);

		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新产品
	 * | @时间: 2016年10月14日 下午5:48:44
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function store(){
		//取参数
		$request = request(true);
// 		dump($request);die;
		$id = $request['id'];
		unset($request['id']);
		$data['state'] = $request['state'];
// 		dump($data);die;
		
		//更新操作
		$res = $this->respository->update($data, $id);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'更新订单失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '更新订单成功';
			$url = U('Backend/Order/index', '', false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	
	}
	
	

	/**
	 * ----------------------------------------------
	 * | 删除订单
	 * | @时间: 2016年10月17日 下午2:39:35
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function disable(){
		//取参数
		$request = request();
		$id = $request['id'];
		//修改状态为禁用
		$data['state'] = 5;
		//更新操作
		$res = M('order')->where([ 'id' => [ 'eq', $id ] ])->setField($data);
// 		echo M()->_sql();die;
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，删除订单失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '删除订单成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 启用价格
	 * | @时间: 2016年10月17日 下午2:39:41
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
// 	public function enable(){
// 		//取参数
// 		$request = request();
// 		$id = $request['id'];
// 		//修改状态为禁用
// 		$data['product_state'] = 1;
// 		//更新操作
// 		$res = M('product')->where([ 'id' => [ 'eq', $id ] ])->setField($data);
	
// 		//返回结果
// 		if ($res === false) {
// 			$msg = $this->model->getError().'，上架商品失败，请重试！';
// 			if (IS_AJAX) {
// 				$this->error( $msg, '',true);
// 			}else {
// 				$this->error($msg);
// 			}
// 		}else {
// 			$msg = '上架商品成功';
// 			if (IS_AJAX) {
// 				$this->success($msg, '', true);
// 			}else {
// 				$this->success($msg);
// 			}
// 		}
	
// 	}
	
	
	/**
	 * ----------------------------------------------
	 * | 订单详情页
	 * | @时间: 2016年10月20日 上午11:45:20
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function details(){
		
		$id = request()['id']+0;
		
		$entity = $this->respository->firstOrFalse($id);
		
		if (!$entity) {
			$this->error('参数有误！，没有改订单的相关信息');
			die;
		}
		
		$fill_data = $this->model->getFillData()->fillData;
// 		dump($this->model->searchable);die;
		$this->assign([
				'entity' => $entity,
				'extra' => $fill_data,
				'searchable' => $this->model->searchable,
		]);
		
		$this->display();
		
	}
	

	/**
	 * ----------------------------------------------
	 * | 审核业务逻辑
	 * | 仅限于虚拟主机和vps(有试用的产品)
	 * | @时间: 2016年10月24日 下午3:03:29
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function review(){
		$id = request()['id']+0;
		
		$res = $this->model->review($id);

		$message = business_code ( $res );
		//返回结果
		if ($message == "成功") {
			$msg = '审核订单'.$message;
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}else {
			$msg = $message;
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}
		
	}
	
	
	
	
	
	
	
	

}