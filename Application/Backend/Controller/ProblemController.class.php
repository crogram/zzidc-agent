<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use Common\Respository\ProblemRespository;



class ProblemController extends BackendController
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
		$this->model = new \Backend\Model\CommonProblemModel();
		$this->respository = new ProblemRespository($this->model);
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
	 * | 录入域名业务页面
	 * | @时间: 2016年10月28日 上午9:40:28
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function create(){

		//产品分类数据
		$product_type = (new \Common\Model\ProductTypeModel())->getAll('id as k,type_name as v');
	
		$this->assign([
			'product_type' => $product_type,
			'state' => [	//账号状态
					1 => '显示',
					0 => '不显示',
			],
		]);
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 新增录入域名业务
	 * | @时间: 2016年11月2日 下午6:26:58
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function recording(){
		$request = request();
		$request['problem_content'] = $_POST['problem_content'];
		$res = $this->respository->create($request);
		// 	dump($res);die;
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'添加失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '添加成功';
			$url = U('Backend/Problem/index', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	}	
	
	
	/**
	 * ----------------------------------------------
	 * | 更新会员页面
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
			$this->error('参数有误！，没有改问题的相关信息');
			die;
		}
		//产品分类数据
		$product_type = (new \Common\Model\ProductTypeModel())->getAll('id as k,type_name as v');
		
	
		// 		dump($entity);die;
		$this->assign([
				'entity' => $entity,
				'product_type' => $product_type,
				'state' => [	//账号状态
						1 => '显示',
						0 => '不显示',
				],
		]);
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新会员信息
	 * | @时间: 2016年10月14日 下午5:48:44
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function store(){
		//取参数
		$request = request();
		$id = $request['id'];
		unset($request['id']);
		$data = $request;
		$data['problem_content'] = $_POST['problem_content'];
		//更新操作
		$res = $this->respository->update($data, $id, 'id');
// 		echo $this->model->_sql();
// 			dump($res);die;
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'更新问题失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '更新问题成功';
			$url = U('Backend/Problem/index', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	
	}
	
	/**
	 * ----------------------------------------------
	 * | 禁用会员
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
		$data['state'] = 0;
		//更新操作
		$res = M('common_problem')->where([ 'id' => [ 'eq', $id ] ])->setField($data);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，不显示问题失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '不显示问题成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 启用会员
	 * | @时间: 2016年10月17日 下午2:39:41
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function enable(){
		//取参数
		$request = request();
		$id = $request['id'];
		//修改状态为禁用
		$data['state'] = 1;
		//更新操作
		$res = M('common_problem')->where([ 'id' => [ 'eq', $id ] ])->setField($data);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，显示问题失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '显示问题成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
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
	public function details() {
		//获取会员id
		$id = request()['id']+0;
	
		//获取会员信息
		$entity = $this->respository->firstOrFalse( [ 'id' => [ 'eq', $id ] ] );

		if (!$entity) {
			$this->error('参数有误！，没有改会员的相关信息');
			die;
		}
		
		//产品分类数据
		$product_type = (new \Common\Model\ProductTypeModel())->getAll('id as k,type_name as v');
		
// 		dump($product_type);die;
		$this->assign([
				'entity' => $entity,
				'id' => $id,
				'state' => [	//账号状态
						1 => '显示',
						0 => '不显示',
				],
				'product_type' => $product_type,
				'state' => $this->model->searchable['state']['data'],
		]);
	
		$this->display();
	
	}
	
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
	
	

	
	
	
	
}