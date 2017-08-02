<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

use Common\Aide\AgentAide;
use Common\Data\GiantAPIParamsData;

/**
 * -------------------------------------------------------
 * | 产品模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class OrderModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = "agent_order";
	
	
	
	//搜索条件字段
	public $searchable = [
			'product_type' => [
					'display_name' => '产品类型',
					'html_type' => 'select',
					'data' => [],
			],
			'area_code' => [
					'display_name' => '所属区域',
					'html_type' => 'select',
					'data' => [],
			],
			'order_type' => [
					'display_name' => '类型',
					'html_type' => 'select',
					'data' => [
							'0' => '新增',
							'1' => '增值',
							'2' => '续费',
							'3' => '变更方案',
							'4' => '转正',
					],
			],
			'state' => [
					'display_name' => '状态',
					'html_type' => 'select',
					'data' => [
							'0' => '失败',
							'1' => '成功',
							'2' => '待处理',
							'3' => '处理中',
							'4' => '审核中',
							'5' => '已删除',
							'6' => '已付款',
					],
			],
			'free_trial' => [
					'display_name' => '购买/试用',
					'html_type' => 'select',
					'data' => [
							'0' => '购买',
							'1' => '试用',
					],
			],
			'key' => [
				'id' => '订单编号',
				'product_name' => '产品名称',
				'note_appended' => '备注',
				'login_name' => '登录名',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'订单号',
					'sortable' => true,
			],
			'login_name' => [
					'display_name' =>'登录名',
					'sortable' => false,
			],
			'user_id' => [
					'display_name' =>'用户id',
					'sortable' => false,
			],
			'order_type' => [
					'display_name' =>'订单类型',
					'sortable' => false,
			],
			'state' => [
					'display_name' =>'状态',
					'sortable' => false,
			],
			'product_type' => [
					'display_name' => '产品类型',
					'sortable' => false,
			],
			'product_name' => [
					'display_name' => '产品名称',
					'sortable' => false,
			],
			'free_trial' => [
					'display_name' => '是否试用',
					'sortable' => false,
			],
			'order_time' => [
					'display_name' => '订购时间(月)',
					'sortable' => true,
			],
			'charge' => [
					'display_name' => '金额',
					'sortable' => true,
			],
			'note_appended' => [
					'display_name' => '备注',
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
		$fill_data['product_type'] = (new \Common\Model\ProductTypeModel())->getOrderIndexType('id as k,type_name as v');
		//区域数据
		$fill_data['area_code'] = D('Common/Region')->getAll('region_code as k,region_name as v');
		
		//父类里继承来的属性
		$this->fillData = $fill_data;
		return $this;
	}
	
	
	
	
	
	
	
	
	
	/**
	 * ----------------------------------------------
	 * | 某些(试用)业务生效时，发送邮件通知用户的方法
	 * | @时间: 2016年10月25日 下午4:21:21
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function mailNotice($order_info){
		//获取会员的信息
		$member_respository = new \Common\Respository\MemberRespository(M('Member'));
		$member_info = $member_respository->firstOrFalse([ 'user_id' => [ 'eq', $order_info['user_id'] ] ]);
		//如果会员没有邮箱，则不发送邮件；
		if (empty($member_info['user_mail'])) {
			$this->error = '该会员没有邮箱，无法发送邮件通知';
			return false;
		}
		
		//组装邮件内容
		$order_info['business_name'] = $order_info['product_name'];
		$mail_content = HTMLContentForEmail(3, $order_info, $member_info);
		//发送邮件
		$res = postOffice($member_info['user_mail'], $mail_content['subject'], $mail_content['body']);

		return $res;
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 审核订单的业务逻辑
	 * | 用于vps和虚拟主机的试用
	 * | @时间: 2016年10月24日 下午3:06:57
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function review($id){
		//订单id
		$id+=0;
		
		//先查看该订单是否是试用订单
		$order_info = $this->find($id);
		if (!$order_info['free_trial'] || $order_info ["state"] != 4){
			return - 13;
		}
		//检查产品相关信息
		$product_info = D('Backend/Product')->getProductWithProductType( $order_info['product_id'] );
		if (!$product_info){
			return - 2;
		}
// 		dump($product_info);die;
		//引入景安api sdk;
		$agent = new AgentAide();
		/*************************虚拟主机试用业务逻辑***************************/
		//如果是虚拟主机，那么可以并且是符合使用原则的
		//，那么就直接将订单状态改为6(已付款)，
		if ($product_info ['api_ptype'] == 'host' || $product_info ['api_ptype'] == 'hkhost'
				|| $product_info ['api_ptype'] == GiantAPIParamsData::PTYPE_CLOUD_SPACE
				|| $product_info ['api_ptype'] == GiantAPIParamsData::PTYPE_USA_HOST
				|| $product_info ['api_ptype'] == GiantAPIParamsData::PTYPE_DEDE_HOST
				|| $product_info ['api_ptype'] == GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL
		) {
			//将该订单的状态改为6，已付款；
			$res = $this->where([ 'id' => [ 'eq', $id ] ])->setField('state', 6);
			if ($res === false){
				$this->error = '服务器繁忙，操作失败，请重试';
				return 0;
			}else {
				/**
				 * ---------------------------------------------------
				 * | 这里说明审核成功，发送邮件通知用户
				 * | @时间: 2016年10月25日 下午4:28:01
				 * ---------------------------------------------------
				 */
				$res = $this->mailNotice($order_info);
				return -1;
			}
		}

		/***********************快云vps试用的审核逻辑************************/
		if($product_info['api_ptype']==GiantAPIParamsData::PTYPE_FAST_CLOUDVPS){
			$json = null;
			try {
				/**
				 * ---------------------------------------------------
				 * | 调用api接口，去主站获得相应的业务id
				 * | @时间: 2016年10月25日 上午11:12:28
				 * ---------------------------------------------------
				 */
				$transaction = $agent->servant;
				$result = $transaction->get_business_info_order_id ( $product_info ["api_ptype"], $order_info ["api_id"] );
				api_log (session('user_id'), "ptype:".$product_info ['api_ptype']."--tid:".GiantAPIParamsData::TID_GET_BUSINESS_INFO."--did:".$order_info ["api_id"],$result, '获取审核状态');
				$json = json_decode ( $result );
			} catch ( \Exception $e ) {
				return - 9;
			}
			if ($json == null) {
				return - 40;
			}
			if ($json->code != 0) {
				return $json->code;
			}
			/**
			 * ---------------------------------------------------
			 * | 实例化快云vps业务模型类
			 * | @时间: 2016年10月25日 上午11:09:02
			 * ---------------------------------------------------
			 */
			$m_fast_vps = new  \Common\Model\FastVpsBusinessModel();
			$add = $m_fast_vps->json_fast_vps_add ( $order_info, $product_info, $json );
			if ( $add==-1) {
				return - 15;
			}
			$this->where( [ 'id' => [ 'eq', $id ] ] )->save([ 
					'state'=>1,
					'business_id'=>$add,
					'ip_address'=>$json->info->ip,
			]);
			/**
			 * ---------------------------------------------------
			 * | 这里说明审核成功，发送邮件通知用户
			 * | @时间: 2016年10月25日 下午4:28:01
			 * ---------------------------------------------------
			 */
			$res = $this->mailNotice($order_info);
			return -1;
		}
		
		
		/**
		 * ---------------------------------------------------
		 * | 访问景安api，先获取业务编号
		 * | @时间: 2016年10月25日 上午11:30:25
		 * ---------------------------------------------------
		 */
		$json = null;
		// 获取业务编号
		$business_id = null;
		try {
			$transaction = $agent->servant;
			$result = $transaction->get_business_id_order_id ( $product_info ["api_ptype"], $order_info ["api_id"] );
			$json = json_decode ( $result );
		} catch ( \Exception $e ) {

			return - 9;
		}
		if ($json == null) {
			return - 40;
		}
		if ($json->code != 0) {
			return $json->code;
		}
		$business_id = $json->info->bid;
		if (empty ( $business_id )) {
			return - 41;
		}
		
		/**
		 * ---------------------------------------------------
		 * | 在获取通过业务编号，获取业务信息
		 * | @时间: 2016年10月25日 上午11:30:56
		 * ---------------------------------------------------
		 */
		$json = null;
		try {
			$result = $transaction->get_business_info ( $product_info ["api_ptype"], $business_id );
			$json = json_decode ( $result );
		} catch ( \Exception $e ) {
		}
		if ($json == null) {
			return - 40;
		}
		if ($json->code != 0) {
			return $json->code;
		}
		/**
		 * ---------------------------------------------------
		 * | 根据上面的业务信息，执行相应的业务
		 * | @时间: 2016年10月25日 上午11:31:30
		 * ---------------------------------------------------
		 */
		// 执行相应业务
		switch ($product_info ["api_ptype"]) {
			case GiantAPIParamsData::PTYPE_CLOUD_HOST :
				$m_cloudhost = new \Common\Model\CloudhostBusinessModel();
				// 添加云主机业务信息
				$add = $m_cloudhost->json_add_cloudhost ( $order_info, $product_info, $json, 1 );
				if ($add==-1) {
					return - 15;
				}
				break;
			case GiantAPIParamsData::PTYPE_VPS :
				$m_vps = new \Common\Model\VpsBusinessModel();
				$add = $m_vps->json_vps_add ( $order_info, $product_info, $json );
				if ( $add==-1) {
					return - 15;
				}
				break;
		}
		// 更改订单状态
		$this->where( [ 'id' => [ 'eq', $id ] ] )->save([
				'state'=>1,
				'business_id'=>$add,
				'ip_address'=>$json->info->ip,
		]);
		/**
		 * ---------------------------------------------------
		 * | 这里说明审核成功，发送邮件通知用户
		 * | @时间: 2016年10月25日 下午4:28:01
		 * ---------------------------------------------------
		 */
		$res = $this->mailNotice($order_info);
		return - 1;

	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

}
