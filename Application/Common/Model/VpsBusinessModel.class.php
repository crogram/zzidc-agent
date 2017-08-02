<?php
namespace Common\Model;
use Common\Model\BaseModel;
use Common\Data\StateData;

/**
 * -------------------------------------------------------
 * | 快云vps业务模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
 
class VpsBusinessModel extends BaseModel{
	
	//关联的表名

	
	
	/**
	 * 添加业务信息到vps业务表
	 * 
	 * @param
	 *        	$order_info
	 * @param
	 *        	$product_info
	 * @param
	 *        	$json
	 */
	public function json_vps_add($order_info, $product_info, $json) {
		$arr = array (
				'user_id' => $order_info ['user_id'],
				'business_id' => $json->info->bid,
				'product_id' => $product_info ['id'],
				'product_name' => $product_info ['product_name'],
				'ip_address' => $json->info->ip,
				'system_user' => $json->info->user,
				'system_password' => $json->info->pwd,
				'open_time' => $json->info->createDate,
				'overdue_time' => $json->info->overDate,
		        'remoteport' => $json->info->remoteport,
				'create_time' => date ( 'Y-m-d H:i:s' ),
				'state' => StateData::SUCCESS,
				'service_time' => $order_info ['order_time'],
				'free_trial' => $order_info ['free_trial'],
				'login_name' => $order_info ['login_name'],
				'area_code' => $order_info ['area_code'] 
		);
		$res = $this->add($arr);
		if($res !==false){
			return $res;
		}
		return -1;
	}
	
	
	
	
	
	
	
	
	
	
	

}
