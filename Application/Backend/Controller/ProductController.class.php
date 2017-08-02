<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use Common\Respository\ProductRespository;

/**
 * -------------------------------------------------------
 * | 后台控制器模板
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午5:06:01
 * |@version: 1.0
 * -------------------------------------------------------
 */
class ProductController extends BackendController
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
		$this->model = new \Backend\Model\ProductModel();
		$this->respository = new ProductRespository($this->model);
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
		
		$this->display();
	}

	/**
	 * ----------------------------------------------
	 * | 更新产品页面
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
			$this->error('参数有误！，没有改产品的价格相关信息');
			die;
		}
	
		$fill_data = $this->model->getFillData()->fillData;
// 		dump($entity);die;	
		$this->assign([
				'entity' => $entity,
				'extra' => $fill_data,
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
		$request = request();
		$id = $request['id'];
		unset($request['id']);
		$data = $request;
		
		//更新操作
		$res = $this->respository->update($data, $id);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'更新产品失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '更新产品成功';
			$url = U('Backend/Product/index', '', false);
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
		$data['product_state'] = 0;
		//更新操作
		$res = M('product')->where([ 'id' => [ 'eq', $id ] ])->setField($data);
// 		echo M()->_sql();die;
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，下架商品失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '下架商品成功';
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
		$data['product_state'] = 1;
		//更新操作
		$res = M('product')->where([ 'id' => [ 'eq', $id ] ])->setField($data);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，上架商品失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '上架商品成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 产品的配置页面
	 * | @时间: 2016年10月20日 上午11:45:20
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function config(){
		
		$id = request()['product_id']+0;
		
		$entities = D('product_config')->where([ 'product_id' => [ 'eq', $id ] ])->select();
		
		if (!$entities) {
			$this->error('参数有误！，没有改产品的价格相关信息');
			die;
		}
		
		$fill_data = $this->model->getFillData()->fillData;
// 		dump($entities);die;
		$this->assign([
				'entities' => $entities,
				'extra' => $fill_data,
		]);
		
		$this->display();
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新产品的配置
	 * | @时间: 2016年10月20日 上午11:45:34
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function configure(){
		
		//取参数
		$request = request();
		$id = $request['product_id'];
		unset($request['product_id']);
		$data = $request;
		
		//更新操作
		$res = D('product_config')->where([ 'product_id' => [ 'eq', $id ] ])->save($data);
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'更新产品失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '更新产品成功';
			$url = U('Backend/Product/index', '', false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
		
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 录入业务页面
	 * | @时间: 2017年2月22日 下午13:38:28
	 * | @author: Guopeng
	 * ----------------------------------------------
	 */
	public function create(){
		$this->display();
	}

	/**
	 * ----------------------------------------------
	 * | 根据录入业务编号同步获取业务信息并新增录入VPS业务
	 * | @时间: 2017年2月22日 下午1:58:58
	 * | @author: Guopeng
	 * ----------------------------------------------
	 */
	public function recording(){
		$request = request(true);
		$business_id = $request["business_id"]+0;
		$ptype = $request["type"];
		$url = U('Backend/Product/create',[],false);
        if($ptype == "vps"){
            $where = "business_id";
            $business = new \Backend\Model\VpsModel();
            $business_info = $business->where(['business_id'=>['eq',$business_id]])->find();
			$url = U('Backend/vps/index',[],false);
        }elseif($ptype == "fastcloudvps"){
            $where = "business_id";
            $business = new \Backend\Model\FastVpsModel();
            $business_info = $business->where(['business_id'=>['eq',$business_id]])->find();
			$url = U('Backend/fast_vps/index',[],false);
		}elseif($ptype == "host" || $ptype == "dedehost" || $ptype == "hkhost" || $ptype == "usahost" || $ptype == "cloudVirtual"){
            $business = new \Backend\Model\VirtualhostModel();
            $where = "business_id";
            $business_info = $business->where(['business_id'=>['eq',$business_id]])->find();
			$url = U('Backend/virtualhost/index',[],false);
        }elseif($ptype == "domain"){
            $where = "api_bid";
            $business = new \Backend\Model\DomainModel();
            $business_info = $business->where(['api_bid'=>['eq',$business_id]])->find();
			$url = U('Backend/domain/index',[],false);
        }elseif($ptype == "cloudspace"){
            $where = "api_bid";
            $business = new \Backend\Model\CloudspaceModel();
            $business_info = $business->where(['api_bid'=>['eq',$business_id]])->find();
			$url = U('Backend/cloudspace/index',[],false);
        }elseif($ptype == "cloudserver"){
            $where = "api_bid";
            $business = new \Backend\Model\CloudserverModel();
            $business_info = $business->where(['api_bid'=>['eq',$business_id]])->find();
			$url = U('Backend/cloudserver/index',[],false);
        }elseif($ptype == "cloudserverIP"){
            $where = "api_bid";
            $business = new \Backend\Model\CloudserverIpModel();
            $business_info = $business->where(['api_bid'=>['eq',$business_id]])->find();
			$url = U('Backend/cloudserver_ip/index',[],false);
        }elseif($ptype == "clouddb"){
            $where = "api_bid";
            $business = new \Backend\Model\ClouddbModel();
            $business_info = $business->where(['api_bid'=>['eq',$business_id]])->find();
			$url = U('Backend/clouddb/index',[],false);
        }else{
            $msg = '产品类型错误！';
            if(IS_AJAX){
                $this->error($msg,'',true);
            }else{
                $this->error($msg);
            }
        }
        //如果存在这条记录，那么就返回错误信息
        if ($business_info){
            $msg = '该业务已存在！';
            if (IS_AJAX) {
                $this->error( $msg, '',true);
            }else {
                $this->error($msg);
            }
        }
        if($ptype == "vps" || $ptype == "fastcloudvps"){
            $BusinessRespository = new \Common\Respository\VpsBusinessRespository($business);
            $res = $BusinessRespository->VpsBusinessSynchronizing($business_id,$ptype);
        }elseif($ptype == "host" || $ptype == "dedehost" || $ptype == "hkhost" || $ptype == "usahost" || $ptype == "cloudVirtual"){
            $BusinessRespository = new \Common\Respository\VirtualhostBusinessRespository($business);
            $res = $BusinessRespository->VirtualhostBusinessSynchronizing($business_id,$ptype);
        }elseif($ptype == "domain"){
            $BusinessRespository = new \Common\Respository\DomainRespository($business);
            $res = $BusinessRespository->DomainBusinessSynchronizing($business_id,"api_bid");
        }elseif($ptype == "cloudspace"){
            $BusinessRespository = new \Common\Respository\CloudspaceBusinessRespository($business);
            $res = $BusinessRespository->CloudspaceBusinessSynchronizing($business_id);
        }elseif($ptype == "cloudserver"){
            $BusinessRespository = new \Common\Respository\CloudserverBusinessRespository($business);
            $res = $BusinessRespository->CloudserverBusinessSynchronizing($business_id);
        }elseif($ptype == "cloudserverIP"){
            $BusinessRespository = new \Common\Respository\CloudserverIpBusinessRespository($business);
            $res = $BusinessRespository->CloudserverIpBusinessSynchronizing($business_id);
        }elseif($ptype == "clouddb"){
            $BusinessRespository = new \Common\Respository\ClouddbBusinessRespository($business);
            $res = $BusinessRespository->ClouddbBusinessSynchronizing($business_id);
        }
		//返回结果
		if ($res == false) {
            $delete = $business->where([$where=>['eq',$business_id]])->delete();
			$msg = '录入业务信息失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			if(!empty($request["beizhu"])){
				$data["beizhu"] = $request["beizhu"];
				if(!empty($request["login_name"]) && !empty($request["user_id"])){
					$data["login_name"] = $request["login_name"];
					$data["user_id"] = $request["user_id"];
				}
				$binfo = $business->where([$where=>['eq',$business_id]])->save($data);
			}elseif(!empty($request["login_name"]) && !empty($request["user_id"])){
                $data["login_name"] = $request["login_name"];
                $data["user_id"] = $request["user_id"];
                $binfo = $business->where([$where=>['eq',$business_id]])->save($data);
            }
			$msg = '录入业务信息成功';
			if (IS_AJAX) {
				$this->success($msg,$url,true);
			}else {
				$this->success($msg,$url);
			}
		}
	}
	
	
	
	
	
	

}