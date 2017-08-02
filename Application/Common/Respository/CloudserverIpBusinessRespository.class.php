<?php
namespace Common\Respository;
use Common\Respository\BusinessRespository;

use Common\Data\GiantAPIParamsData as GiantAPIParams;


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
class CloudserverIpBusinessRespository extends BusinessRespository{
	
	/**
	 * ----------------------------------------------
	 * | 同步快云服务器ip的方法
	 * | @时间: 2016年11月5日 下午6:17:01
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function CloudserverIpBusinessSynchronizing($business_info, $belong_server = '', $attr = "api_bid"){
		//如果你传入的是id值那么就获取信息，如果你传入的是一条业务记录(数组),那么就不用再去取了
		$m_cloudserver_ip = M('cloudserver_business_ip');
		if (is_numeric($business_info) && !is_array($business_info)){
			//先获取本地业务信息
			$business_info_exists =$m_cloudserver_ip->where([ $attr => [ 'eq',$business_info ] ])->find();
			//如果不存在这条ip记录，那么就写进去
			if (empty($business_info_exists)){
				list($business_info, $temp) = [ [], $business_info];
				$business_info[$attr] = $temp;
				$business_info['user_id'] = -1;
				$add_id = $m_cloudserver_ip->add($business_info);
				$business_info['id'] = $add_id;
				$business_info['business_id'] = $business_info[$attr];
			}else {
				$business_info = $business_info_exists;
				$business_info['business_id'] = $business_info_exists[$attr];
			}
		}else {
				$business_info['business_id'] = $business_info[$attr];
		}
		$id = $business_info['id'];
	
// 		dump($business_info);die;
		//调用api同步业务信息
		$result = $this->syncBusinessFromZZIDC($business_info, GiantAPIParams::PTYPE_CLOUD_SERVER, 'cloudserver.ip');
		if(is_array($result) && !empty($result['json'])){
			// 业务信息
			$bus_info_ip = $result['json']['info'];

			$ipparms['product_name'] = '快云服务器IP';
			$ipparms['product_id'] = '227';
			$ipparms['api_bid'] = $bus_info_ip['ipywbh'];
			$ipparms['ipaddress'] = $bus_info_ip['ipdz'];
			$ipparms['bandwidth'] = $bus_info_ip['wldk'];
			$ipparms['create_time'] = $bus_info_ip['gmsj'];
			$ipparms['overdue_time'] = $bus_info_ip['dqsj'];
			$ipparms['buy_time'] = $bus_info_ip['gmqx'];
			$ipparms['area_code'] = $bus_info_ip['ip_areacode'];
			if($bus_info_ip['dqsj']>$bus_info_ip['gmsj']){
				$ipparms['state']=1;
			}else {
				$ipparms['state']=2;
			}
			$ipparms['belong_server']=$belong_server;
			/**
			 * ---------------------------------------------------
			 * | 这里去查询下快云服务器的信息，
			 * | 查看下里面是否有用户信息。
			 * | @时间: 2017年1月13日 下午4:20:49
			 * ---------------------------------------------------
			 */
			if (!empty($belong_server)){
				$m_cloudserver = M('cloudserver_business');
				$cloudeserver_info = $m_cloudserver->where([ 'api_bid' => [ 'eq', $belong_server ] ])->find();
				$ipparms['user_id'] = !$cloudeserver_info['user_id'] ? '-1': $cloudeserver_info['user_id'];
				$ipparms['login_name'] = !$cloudeserver_info['login_name'] ? '待转让会员': $cloudeserver_info['login_name'];
			}else {				
				$ipparms['user_id'] = !$business_info['user_id'] ? '-1': $business_info['user_id'];
				$ipparms['login_name'] = !$business_info['login_name'] ? '待转让会员': $business_info['login_name'];
			}
			
			$update_res = $m_cloudserver_ip->where([ 'Id' => [ 'eq', $id ] ])->save($ipparms);
			if($update_res === false){
				$this->model()->setError('服务器繁忙，同步失败，请重试');
				return false;
			}else {
				return true;
			}
		}else {
			
			//说明同步调用api出错了,将错误结果返回出去
			$this->model()->setError(business_code($result));
			return FALSE;
		}
				
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 删除云服务器IP的方法
	 * | @时间: 2016年11月4日 下午2:32:16
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function eraseCloudserverIpBusiness($id){
		return $this->erase( $this->firstOrFalse( [ 'Id' => [ 'eq', $id] ]), GiantAPIParams::PTYPE_CLOUD_SERVER, 'cloudserver.ip'  );
	}
	
	
}