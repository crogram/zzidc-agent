<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

/**
 * -------------------------------------------------------
 * | 会员模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class MemberModel extends BackendModel{
	
	//关联的表名
	
	//搜索条件字段
	public $searchable = [
			'user_state' => [
					'display_name' => '用户状态',
					'html_type' => 'select',
					'data' => [
							'0' => '禁用',
							'1' => '正常',
					],
			],
			'create_time' => [
					'display_name' => '创建时间',
					'html_type' => 'date',
					'start_name' => '_start',
					'end_name' => '_end',
			],
			'key' => [
				'login_name' => '登陆名',
				'user_name' => '用户名',
				'user_mail' => '邮箱',
				'beizhu' => '备注',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'user_id' => [
					'display_name' =>'用户ID',
					'sortable' => true,
			],
			'login_name' => [
					'display_name' =>'登陆名',
					'sortable' => false,
			],
			'user_name' => [
					'display_name' =>'用户名',
					'sortable' => false,
			],
			'user_mail' => [
					'display_name' => '邮箱',
					'sortable' => false,
			],
			'user_state' => [
					'display_name' => '用户状态',
					'sortable' => false,
			],
			'balance' => [
					'display_name' => '余额',
					'sortable' => false,
			],
			'purchases' => [
					'display_name' => '消费总额',
					'sortable' => false,		
			],
			'create_time' => [
					'display_name' => '创建时间',
					'sortable' => true,
			],
			'beizhu' => [
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
// 		$fill_data['product_type_id'] = D('Common/ProductType')->getAll('id as k,type_name as v');
		//区域数据
// 		$fill_data['area_code'] = D('Common/Region')->getAll('region_code as k,region_name as v');
		
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
		
		//不能为空的字段
		if ( empty($data['user_name']) ){
			$this->error = '会员名称不能为空';
			return false;			
		}
		
		if ( !empty($data['user_code']) ){
			if ( !checkIdCard($data['user_code']) ){
				$this->error = '请输入正确的身份证号码';
				return false;
			}
		}
		
		if (!empty( $data['user_mobile'] )){
    		if ( !is_tel($data['user_mobile']) ){
    			$this->error = '手机号码格式不对';
    			return false;
    		}
		}
		
		if (!empty( $data['user_mail'] )){
    		if ( !filter_var($data['user_mail'], FILTER_VALIDATE_EMAIL) ){
    			$this->error = '请输入正确的邮箱格式';
    			return false;
    		}
		}
		
		
		if (!empty( $data['user_telphone'] )){
			if ( !is_tel($data['user_telphone']) ){
				$this->error = '固话格式不对';
				return false;
			}
		}
		
		if ( !empty( $data['user_postal'] ) ){
			if ( !postalCodesValidator($data['user_postal']) ){
				$this->error = '请填写正确的邮政编码';
				return false;
			}
		}
		
		if ( !empty( $data['user_qq'] ) ){
			if ( !is_numeric($data['user_qq']) ){
				$this->error = '请填写正确的qq号码';
				return false;
			}
		}

		//处理下备注说明的编码，统一转换成utf-8的编码，然后查看字数
		$beizhu = mb_convert_encoding($data['beizhu'], 'utf-8');
		if ( mb_strlen($beizhu, 'utf-8') >= 100*3) {
			$this->error = '备注说明最多只有100个字';
			return false;
		}
		
		//过滤xss脚本攻击
		$data['user_province'] = clearXSS($data['user_province']);
		$data['user_city'] = clearXSS($data['user_city']);
		$data['user_address'] = clearXSS($data['user_address']);
		$data['beizhu'] = clearXSS($data['beizhu']);
		$data['user_name'] = clearXSS($data['user_name']);
		
	
		//添加更新时间默认值
// 		$data['update_time'] = date( 'Y-m-d H:i:s', time() );
	
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
	
	/**********************************新增更新回调/结束***********************************/
		
	/**
	 * ----------------------------------------------
	 * | 更新用户余额
	 * | @时间: 2016年10月27日 上午9:32:30
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function updateAccountBalance($user_id, $operation, $amount, $remark,$recharge_type = 0){
	    
		//1.检查该用户是否成功注册
		$m_common_member = new \Common\Model\MemberModel();
		$user_info = $m_common_member->memberExistByUserId($user_id);
		if (!$user_info){
			$this->error = '该用户不存在！操作失败';
			return false;
		}
		
		//2.检查金额是否是合法数字
		if ($amount<=0){
			$this->error = '录入金额错误';
			return false;
		}
		
		//3.更新余额的准备条件
		$field = 'balance';
		$where['user_id'] = [ 'eq', $user_id ];
		$m_member_account = M('member_account');
		//3.1获取用户现有余额
		$balance = $m_member_account->field($field)->where($where)->find();
		$balance = $balance[$field];
		if ($operation == 1){
			
			//录入(增加)
			$res = $m_member_account->where($where)->setInc($field, $amount+0);
			$msg = '用户：'.$user_info['login_name'].'录款'.$amount.'元，余额：'.($balance+$amount);
			//如果更新记录成功，提现或录款成功，那么就写一条交易记录
			if ($res !== false){
				add_transactions($user_id, $amount, $msg, 0, $recharge_type, null, null, $remark);
			}
			
		}elseif ($operation == 2){
			
			//提现(减少)
			if ($balance < $amount){
				$this->error = '提现数额大于该用户余额，提现失败！';
				return false;
			}else {
				$res = $m_member_account->where($where)->setDec($field, $amount+0);
				$msg = '用户：'.$user_info['login_name'].'提现'.$amount.'元，余额：'.($balance-$amount);
				//如果更新记录成功，提现或录款成功，那么就写一条交易记录
				if ($res !== false){
					add_transactions($user_id, $amount, $msg, 2,$recharge_type, null, null, $remark);
				}
			}
			
		}
		return $res;
	}
	
	
	

}
