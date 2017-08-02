<?php
namespace Common\Model;
use Common\Model\BaseModel;



/**
 * -------------------------------------------------------
 * | 公共的交易模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class DealModel extends BaseModel{
	
	//关联的表名
	protected $trueTableName = "agent_member_transactions";
	
	
	
	
	
	
	
	
	
	/**
	 * ----------------------------------------------
	 * | 公共的添加交易记录的方法
	 * | 平台上所有的交易记录都要留在这里一份
	 * | 所有与钱相关的都要在这张表里写条记录
	 * | @时间: 2016年10月27日 上午10:06:26
	 * | @author: duanbin
	 * | @param: $type '交易类型0充值 1消费 2取款' 
	 * | @param: $recharge '0:手动录入1：在线支付'
	 * | @param: $remark '录款备注'
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function addRecord($user_id, $transactions_money, $note_appended, $type, $recharge=1, $product_id = null, $order_id=null, $remark=null){
		$params ["user_id"] = $user_id;
		$params ["account_money"] = $transactions_money;
		$params ["note_appended"] = $note_appended;
		$params ["type"] = $type;
		$params ["remark"] = $remark;
		$params ['recharge_type']=$recharge;
		
		$login_name = M('member')->field('login_name')->where([ 'user_id' => [ 'eq', $user_id ] ])->find();
		$params ["login_name"] = $login_name['login_name'];
		
		$params ["create_time"] = date('Y-m-d H:i:s');
		if (! empty ( $product_id )) {
			$params ["product_id"] = $product_id;
		}
		if (! empty ( $order_id )) {
			$params ["order_id"] = $order_id;
		}
		
		$res = $this->add($params);
		return $res;
	}
	

	
	/**
	 * ----------------------------------------------
	 * | 获取某个会员的交易信息
	 * | @时间: 2016年10月27日 下午2:04:00
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getDealInfoByUser($user_id, $limit = 5, $fields = "a.*,b.*"){
		$where['a.user_id'] = [ 'eq', $user_id ];
		return $this->alias('a')->field($fields)
		->join('left join '.C('DB_PREFIX').'product as b on a.product_id = b.id')
		->where($where)->order('a.create_time DESC')->limit($limit)->select();
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 计算某个用户一共有个多少比交易在当前平台
	 * | @时间: 2016年10月27日 下午2:43:10
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function countDealByUser($user_id){
		$where['user_id'] = [ 'eq', $user_id ];
		return ($this->where($where)->count());
	}
	
	

}
