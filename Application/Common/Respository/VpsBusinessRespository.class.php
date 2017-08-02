<?php
namespace Common\Respository;
use Common\Respository\BusinessRespository;

use \Common\Data\GiantAPIParamsData as GiantAPIParams;

/**
 * -------------------------------------------------------
 * | 交易仓储类
 * | 可选择实现orderExtra()，whereExtra()，fieldsExtra()方法，
 * | 为了有连表的情况出现，额外设置别名
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:50:29
 * | @version: 1.0
 * -------------------------------------------------------
 */
class VpsBusinessRespository extends BusinessRespository{
	
	/**
	 * ----------------------------------------------
	 * | 同步VPS业务逻辑（VPS，快云VPS）
	 * | @时间: 2016年10月31日 下午5:05:36
	 * | @author: duanbin
	 * | @param: $business_info  一条业务记录或者是业务表的id值  建议传入一条业务记录
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function VpsBusinessSynchronizing($business_info, $ptype){
		//如果你传入的是id值那么就获取信息，如果你传入的是一条业务记录(数组),那么就不用再去取了
		if (is_numeric($business_info) && !is_array($business_info)){
			//先获取本地业务信息
			$business_info_exists =$this->model()->where([ 'business_id' => [ 'eq',$business_info ] ])->find();
			//如果不存在这条记录，那么就写进去
			if (empty($business_info_exists)){
				list($business_info, $temp) = [ [], $business_info];
				$business_info['business_id'] = $temp;
				$business_info['user_id'] = -1;
				$add_id = $this->model()->add($business_info);
				$business_info['id'] = $add_id;
			}else {
				$business_info = $business_info_exists;
			}
		}
		$id = $business_info['id'];
		//调用api同步业务信息
		$result = $this->syncBusinessFromZZIDC($business_info, $ptype);
		if(is_array($result) && !empty($result['json'])){
			//说明通用的单次同步业务逻辑未出错；
			//拿取api返回的信息
			$bus_info=$result['json']['info']['Extir'];
			$product_info = $result['product_info'];
			//处理相应的信息，拼凑要更新到本地的数据
			$parms['ip_address']=$bus_info['ip'];
			$parms['system_type']=$bus_info['osType'];
			$parms['product_id']=$product_info['id'];
			$parms['business_id']=$bus_info['bid'];
			$parms['open_time']=$bus_info['createDate'];
			$parms['overdue_time']=$bus_info['overDate'];
			$parms['create_time']=$bus_info['createDate'];
			$parms['remoteport']=$bus_info['remoteport'];
			$parms['area_code']=$bus_info['areaCode'];
			$parms['product_name']=$product_info['product_name'];
			$parms['system_user']=$bus_info['userName'];
			//快云VPS的跟VPS的不一样
			if ($ptype == GiantAPIParams::PTYPE_FAST_CLOUDVPS){
				$parms['system_password']=!$bus_info['modifyPass']? $bus_info['createPass']: $bus_info['modifyPass'];				
			}else {				
				$parms['system_password']=$bus_info['password'];
			}
			//根据到期时间和开通时间来判断服务期限
			$parms['service_time']=$bus_info['serviceTime'];
			/***************如果服务期限为2天，那代表是试用***************/
			if($bus_info['serviceTime'] == '2天'){
				$parms['free_trial']=1;
				$parms['service_time'] = 2;
			}else{
				$parms['free_trial']=0;
				$parms['service_time'] = $bus_info['serviceTime'];
			}
			//到期时间和当前时间比较，断定状态是正常还是过期
			$overdue_time = new \DateTime($parms['overdue_time']);
			$new = new \DateTime('now');
			if($overdue_time > $new){
				$parms['state']=1;
			}else{
				$parms['state']=$bus_info['status'];
			}
	
			//这里更新vps_business表的信息
			$update_res = $this->model()->where([ 'id' => [ 'eq', $id ] ])->save($parms);
			if ($update_res === false){
				$this->model()->setError('服务器繁忙，同步失败，请重试');
				return false;
			}else {
				return true;
			}
		}else{
			//说明同步调用api出错了,将错误结果返回出去
			$this->model()->setError(business_code($result));
			return FALSE;
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 删除VPS业务逻辑（VPS，快云VPS）
	 * | @时间: 2016年10月31日 下午5:26:59
	 * | @author: duanbin
	 * | @param: $id 代理平台业务id
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function eraseVpsBusiness($id, $ptype){
		return $this->erase($id, $ptype);
	}
	
}