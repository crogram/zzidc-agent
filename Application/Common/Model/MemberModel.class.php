<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * -------------------------------------------------------
 * | apilog模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class MemberModel extends BaseModel{
	
	//关联的表名

	

	

	/**
	 * ----------------------------------------------
	 * | 查看前台用户是否存在，通过user_id
	 * | @时间: 2016年10月27日 上午10:29:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function memberExistByUserId($user_id) {
		$exist = $this->getMemberInfo([ 'user_id' => [ 'eq', $user_id ] ]);
		return !$exist ? false: $exist;
	}
	
	
	
	/**
	 * ----------------------------------------------
	 * | 通过一个where条件获取一条用户信息
	 * | @时间: 2016年10月27日 上午10:31:19
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getMemberInfo($where, $field = '*'){
		return $this->field($field)->where($where)->find();
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 通过user_id 获取用户的账户信息
	 * | @时间: 2016年10月27日 下午1:57:06
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getMemberAccountInfoByUserId($user_id){
		return M('member_account')->where([ 'user_id' => [ 'eq', $user_id ] ])->find();
	}
	
	
	
	

}
