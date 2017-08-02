<?php
namespace Common\Model;
use Common\Model\BaseModel;
//use Common\Data\StateData;

/**
 * -------------------------------------------------------
 * | 快云vps业务模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
 
class CloudhostBusinessModel extends BaseModel{
	
	//关联的表名

	
	
	/**
	 * json数据添加云主机信息
	 * @param $order_info
	 * @param $product_info
	 * @param $json
	 */
	public function json_add_cloudhost($order_info,$product_info,$json,$free_trial=0){
		//封装云主机业务信息
		$params = array ();
		$params ["user_id"] = $order_info ["user_id"];
		$params ["login_name"] = $order_info ["login_name"];
		$params ["buy_term"] = $order_info ["order_time"];
		$params ["product_id"] = $product_info ["id"];
		$params ["free_trial"] = $product_info ["free_trial"];
		$params ["product_name"] = $product_info ["product_name"];
		$params ["ip"] = $json->info->ip;
		$params ["create_time"] = $json->info->createDate;
		$params ["overdue_time"] = $json->info->overDate;
		$params ["cloudhost_user"] = $json->info->user;
		$params ["cloudhost_password"] = $json->info->pwd;
		$params ["free_trial"]=$free_trial;
		$params["api_bid"]= $json->info->bid;
		$params ["des"] = "";
		$params ["state"] = 1;
		//添加云主机业务信息
		$res = $this->add($params);
		if($res !==false){
			return $res;
		}
		return -1;
	}
	
	
	
	
	
	
	
	
	
	
	

}
