<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use Common\Respository\MemberRespository;

/**
 * -------------------------------------------------------
 * | 会员管理
 * | @author: duanbin
 * | @时间: 2016年10月21日 下午3:35:10
 * | @version: 1.0
 * -------------------------------------------------------
 */
class MemberController extends BackendController{
	
	
	
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
		$this->model = new \Backend\Model\MemberModel();
		$this->respository = new MemberRespository($this->model);
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
		]);
		if (IS_AJAX){
			return $this->fetch();
		}else {

			$this->display();
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
	
		$entity = $this->respository->firstOrFalse( [ 'user_id' => [ 'eq', $id ] ] );
	
		if (!$entity) {
			$this->error('参数有误！，没有改会员的相关信息');
			die;
		}
	
	
// 		dump($entity);die;
		$this->assign([
				'entity' => $entity,
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
		$request = request(true);
		//dump($request);
		$id = $request['id'];
		unset($request['id']);
		$data = $request;
		//更新操作
		$res = $this->respository->update($data, $id, 'user_id');
// 	dump($res);die;
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'更新会员信息失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '更新会员信息成功';
			$url = U('Backend/Member/index', [  ], false);
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
		$data['user_state'] = 0;
		//更新操作
		$res = M('member')->where([ 'user_id' => [ 'eq', $id ] ])->setField($data);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，禁用会员失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '禁用会员成功';
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
		$data['user_state'] = 1;
		//更新操作
		$res = M('member')->where([ 'user_id' => [ 'eq', $id ] ])->setField($data);
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'，启用会员失败，请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '启用会员成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
	
	}
	
	/**
	 * ----------------------------------------------
	 * | 会员提款录款操作
	 * | @时间: 2016年10月26日 下午3:33:34
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function teller(){
		
		//取参数
		$request = request();
		//更新操作
		$res = $this->model->updateAccountBalance($request['id'], $request['operation'], $request['amount'], $request['remark']);
		
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
		$entity = $this->respository->firstOrFalse( [ 'user_id' => [ 'eq', $id ] ] );
		if (!$entity) {
			$this->error('参数有误！，没有改会员的相关信息');
			die;
		}
		
		//获取账户信息
		$account_info = (new \Common\Model\MemberModel())->getMemberAccountInfoByUserId($id);
		
		//获取交易相关信息
		$m_common_deal = new \Common\Model\DealModel();
		//获取交易记录
		$deal_info = $m_common_deal->getDealInfoByUser($id);
		//获取交易总记录数
		$countor = $m_common_deal->countDealByUser($id);
	
// 		dump($countor);die;
		$this->assign([
				'entity' => $entity,
				'account_info' => $account_info,
				'state' => [	//账户状态
						1 => '正常',
						0 => '不可用',
				],
				'deal_info' => $deal_info,
				'type' => [
						0 => '充值',
						1 => '消费',
						2 => '提现',
				],
				'id' => $id,
				'user_state' => [	//账号状态
						1 => '正常',
						0 => '禁用',
				],
				'countor' => $countor,
		]);
		
		$this->display();
		
	}

	
	/**
	 * ----------------------------------------------
	 * | 重置用户密码为12345678
	 * | @时间: 2016年10月27日 下午3:14:52
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function changePwd(){
		//取参数
		$request = request();
		$id = $request['id'];
		//获取会员相关信息
		$member = $this->respository->firstOrFalse( [ 'user_id' => [ 'eq', $id ] ] );
		if (!$member) {
			$this->error('参数有误！，没有改会员的相关信息');
			die;
		}
		//重置密码为12345678
		//并更改数据库
		$raw_pwd = 12345678;
		$data['user_pass'] = pwd($member['login_name'], md5($raw_pwd));
		$res = M('member')->where([ 'user_id' => [ 'eq', $id ] ])->setField($data);
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError().'请重试！';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			//发送邮件通知会员已修改密码
			$member['user_pass'] = $raw_pwd;
			$mail_content = HTMLContentForEmail(5, '', $member);
			//发送邮件
			$res = postOffice($member['user_mail'], $mail_content['subject'], $mail_content['body']);
			
			$msg = '重置密码成功';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg);
			}
		}
		
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 跳转登录前台页面
	 * | @时间: 2016年10月27日 下午5:09:18
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function loginMember(){
		//取参数
		$request = request();
		$id = $request['id'];
		//获取会员相关信息
		$member = $this->respository->firstOrFalse( [ 'user_id' => [ 'eq', $id ] ] );
		if (!$member) {
			$this->error('参数有误！，没有改会员的相关信息');
			die;
		}
		//将会员信息存入session，方便前台判断
		rememberMember($member);
		//跳转前台会员中心
		$this->redirect(U('frontend/member/index', [], false));

	}
	
	
	/**
	 * ----------------------------------------------
	 * | 选择会员列表的单选页面
	 * | @时间: 2016年10月28日 上午10:54:24
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function radio(){
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
		]);
		if (IS_AJAX){
			return $this->fetch();
		}else {
		
			$this->display('');
		}
		
	}
	
	
	
	
	
	


}
