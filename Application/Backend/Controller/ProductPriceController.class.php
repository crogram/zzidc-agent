<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use Common\Respository\ProductPriceRespository;

/**
 * -------------------------------------------------------
 * | 后台控制器模板
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午5:06:01
 * |@version: 1.0
 * -------------------------------------------------------
 */
class ProductPriceController extends BackendController
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
		$this->model = D('Backend/ProductPrice');
		$this->respository = new ProductPriceRespository($this->model);
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
		$field = array_keys($this->model->sortable);
// 		dump($field);die;
		//获取所有的数据
		$data = $this->respository->finder($field);
		
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
				'product_id' => request()['product_id'],
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
			$this->error('参数有误！，没有改产品的价格相关信息');
			die;
		}
		$m_backend_product = new \Backend\Model\ProductModel();
		$product_name = $m_backend_product->field('product_name')->where([ 'id' => [ 'eq', $entity['product_id'] ] ])->find();

		$this->assign([
				'entity' => $entity,
				'product_name' => $product_name['product_name'],
				'product_id' => $entity['product_id'],
		]);
// 		dump($product_name);die;
		$this->display();
	}

	/**
	 * ----------------------------------------------
	 * | 更新产品价格
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
		//判断是否低于最低价格
		if($request['product_price'] < $request['min_price']){
			$msg = '价格不能低于限制价格！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}
		//更新操作
		$res = $this->respository->update($data, $id);

		//获取产品id，给成功时跳转使用
		$product_id = $this->respository->firstOrFalse( $id , 'product_id');
		 
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'更新价格失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '更新价格成功';
			$url = U('Backend/ProductPrice/index', [ 'product_id' => $product_id['product_id'], ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 禁用价格
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
		$res = M('product_price')->where([ 'id' => [ 'eq', $id ] ])->setField($data);
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，禁用价格失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '禁用价格成功';
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
	public function enable(){
		//取参数
		$request = request();
		$id = $request['id'];
		//修改状态为禁用
		$data['state'] = 1;
		//更新操作
		$res = M('product_price')->where([ 'id' => [ 'eq', $id ] ])->setField($data);
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，启用价格失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '启用价格成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
		
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 批量修改价格页面
	 * | @时间: 2016年10月20日 下午3:37:58
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function updateBatchPrice(){
		
		
		//1.获取产品类型
		$m_common_product_type = new \Common\Model\ProductTypeModel();
		$_types = $m_common_product_type->getAll('id as k,type_name as v');
		$types = [];
		//处理下产品类型
		foreach ( $_types as $k => $v ){
			if (in_array($k, [ '1', '7', '8', '12', '13', '14', '15', '16', '17'])) {
				$types[$k] = $v;
			}
		}
	
		$first_type = reset($types);
		$first_type = key($types);
		
		$m_backend_product = new \Common\Model\ProductModel();
		$products = $m_backend_product->where([ 
				'product_type_id' => [ 'eq', $first_type ], 
				'_string' => ' find_in_set(2,stage) ',
 				'type' => [ 'eq', 0],
		])->select();
		
// 		echo D('Backend/Product')->_sql();
		
// 		dump($first_type);
// 		dump($types);
// 		die;
		
		$this->assign([
				'types' => $types,
				'products' => $products,
		]);
		
		$this->display('update-batch-price');
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 批量修改商品价格
	 * | @时间: 2016年10月20日 下午3:38:43
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function storeBatchPrice(){

		//批量更新价格
		$res = $this->model->updateBatchPrice();
		
		//返回结果
		$msg = $this->model->getError();
		$url = U('Backend/ProductPrice/updateBatchPrice', [ ], false);
		if (IS_AJAX){
			$this->success($msg, $url, true);
		}else {
			$this->success($msg, $url);
		}

	}
	
	/**
	 * ----------------------------------------------
	 * | 通过商品类型获得相应的商品
	 * | @时间: 2016年10月21日 上午10:30:49
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function ajaxGetProducts() {
		$request = request();
		
		$type_id = $request['type_id'];
		
		//默认条件
		$where['_string'] = ' find_in_set(2,stage) ';
		
		//产品类型条件
		if (empty($type_id)){
			$this->error('未知参数错误');
		}		
		$where['product_type_id'] = [ 'eq', $type_id];
		
		//订购续费条件
		$type = $request['type'];
		if ( in_array($type, [ 0,1 ], true)){
			$where['type'] = [ 'eq', $type];
		}
// 		dump($type);
		//获取产品信息
		$products = D('Backend/Product')->where($where)->select();
		
// 		dump($products);die;
		$this->assign([
				'products' => $products,
		]);
		$res = $this->fetch('products');
		
		//返回结果
		if (empty($res)) {
			$return = [
					'code' => -1,
					'msg' => '未知错误',
			];
		}else {
			$return = [
					'code' => 1,
					'msg' => '成功',
					'data' => $res,
			];
		}
		
		$this->ajaxReturn($return, 'json');
		
	}

	/**
	 * 快云服务器产品价格列表页
	 * @时间: 2017年4月10日 下午3:30:32
	 * @author: Guopeng
	 */
	public function cloudserver_price(){
		$cloudserver_price = $this->model->get_cloudserver_price();
		$data = request();
		$type = $data['type'] + 0;
		if($type == 1){
			$type = 1;
		}else{
			$type = 0;
		}
		$this->assign(["data"=>$cloudserver_price,'type'=>$type]);
		$this->display();
	}

    /**
     * 快云服务器产品价格列表页修改操作
     * @时间: 2017年4月10日 下午3:30:32
     * @author: Guopeng
     */
    public function cloudserver_update(){
        $data = request();
		if($data['jf'] == "hk"){
			$type = 1;
		}else{
			$type = 0;
		}
		$res = $this->model->cloudserver_price_update();
		//返回结果
		if ($res) {;
			$return = ['code' => 1,'msg' => '快云服务器产品价格修改成功！','type'=>$type];
		}else {
			$return = ['code' => -1,'msg' => $this->model->getError(),'type'=>$type];
		}
		$this->ajaxReturn($return, 'json');
    }


	/**
	 * 快云数据库产品价格列表页
	 * @时间: 2017年4月10日 下午3:30:32
	 * @author: Guopeng
	 */
	public function clouddb_price(){
		$clouddb_price = $this->model->get_clouddb_price();
		$this->assign(["data"=>$clouddb_price]);
		$this->display();
	}

	/**
	 * 快云数据库产品价格列表页修改操作
	 * @时间: 2017年4月10日 下午3:30:32
	 * @author: Guopeng
	 */
	public function clouddb_update(){
		$res = $this->model->clouddb_price_update();
		//返回结果
		if ($res) {;
			$return = ['code' => 1,'msg' => '快云数据库产品价格修改成功！',];
		}else {
			$return = ['code' => -1,'msg' => $this->model->getError(),];
		}
		$this->ajaxReturn($return, 'json');
	}


    /**
     * SSL证书价格列表页
     * @时间: 2017年4月10日 下午3:30:32
     * @author: Guopeng
     */
    public function ssl_price(){
        $ssl_price = $this->model->get_ssl_price();
        $this->assign(["data"=>$ssl_price]);
        $this->display();
    }

    /**
     * SSL证书价格列表页修改操作
     * @时间: 2017年4月10日 下午3:30:32
     * @author: Guopeng
     */
    public function ssl_update(){
        $res = $this->model->ssl_price_update();
        //返回结果
        if ($res) {;
            $return = ['code' => 1,'msg' => '快云数据库产品价格修改成功！',];
        }else {
            $return = ['code' => -1,'msg' => $this->model->getError(),];
        }
        $this->ajaxReturn($return, 'json');
    }

}