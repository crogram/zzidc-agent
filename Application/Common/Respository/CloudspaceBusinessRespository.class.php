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
class CloudspaceBusinessRespository extends BusinessRespository{
	
	
	/**
	 * ----------------------------------------------
	 * | 同步云空间的业务逻辑
	 * | 方法内部已经对ptype进行了处理，会自动找出对应的ptype，不需要再传入了
	 * | @时间: 2016年10月31日 下午5:05:36
	 * | @author: duanbin
	 * | @param: $business_info  一条业务记录或者是业务表的id值  建议传入一条业务记录
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function CloudspaceBusinessSynchronizing($business_info){
		//如果你传入的是id值那么就获取信息，如果你传入的是一条业务记录(数组),那么就不用再去取了
		if (is_numeric($business_info) && !is_array($business_info)){
			//先获取本地业务信息
			$business_info_exists =$this->model()->where([ 'business_id' => [ 'eq',$business_info ] ])->find();
			//如果不存在这条记录，那么就写进去
			if (empty($business_info_exists)){
				$business_info['business_id'] = $business_info;
				$business_info['user_id'] = -1;
				$add_id = $this->model()->add($business_info);
				$business_info['id'] = $add_id;
			}else {
				$business_info = $business_info_exists;
			}
		}
		$id = $business_info['id'];
		
		//调用api同步业务信息
		$result = $this->syncBusinessFromZZIDC($business_info, GiantAPIParams::PTYPE_CLOUD_SPACE);
// 		dump($result);die;
	
		if(is_array($result) && !empty($result['json'])){
			//说明通用的单次同步业务逻辑未出错；
			//拿取api返回的信息
			$product_info = $result['product_info'];
			$bus_info = $result['json'] ['info'] ['expir'];
			$bus_config = $result['json'] ['info'] ['config'];
			//处理相应的信息，拼凑要更新到本地的数据
			// 业务信息
			$parms ['overdue_time'] = $bus_info ['overDate']; // 到期时间
			$parms ['create_time'] = $bus_info ['createDate']; // 创建时间
			$parms ['open_time'] = $bus_info ['createDate']; // 创建时间
			//根据到期时间和开通时间来判断服务期限
			if($bus_info['payTerm'] == '2天'){
				$parms['free_trial']=1;
				$parms ['service_time'] = 2;
			}else{
				$parms['free_trial']=0;
				$parms ['service_time'] = $bus_info['payTerm'];
			}
			$parms ['ip_address'] = $bus_config ['ipAddress']; // ip地址
			$parms ['space_capacity'] = $bus_config ['spaceSize']; // 空间总大小
			$parms ['database_capacity'] = $bus_config ['dbSize']; // 数据库空间总大小
			$parms ['flow_capacity'] = $bus_config ['flow']; // 总流量大小
			$parms ['database_quantity'] = $bus_config ['dbNumber']; // 数据库总个数
			if($bus_config ['spaceUsed'] != null && count($bus_config ['spaceUsed'])>0){
				$parms ['use_space_capacity'] = $bus_config ['spaceUsed']; // 已使用空间大小
			}else{
				$parms['use_space_capacity'] =0;
			}
			if($bus_config ['flowUsed'] != null && count ( $bus_config ['flowUsed'] ) > 0){
				$parms ['use_flow'] = $bus_config ['flowUsed']; // 已使用流量
			}else{
				$parms ['use_flow'] = 0;
			}
			$parms['state'] = $bus_info['status'];
			//云空间  没有产品名称
// 			$parms['product_name']=$product_info['product_name'];
			$parms['product_id']=$product_info['id'];
			//这里更新的是cloudspace业务表的信息
			$cloudspace_update_res = $this->model()->where([ 'id' => [ 'eq', $id ] ])->save($parms);
			
			//这里云空间站点配置表的信息
			// 站点配置信息
			$siteConfig = $result['json']['info']['siteConfig'];
			if ($siteConfig != null && count ( $siteConfig ) > 0) {
				// 获取当前数据库站点列表
				$m_cloudspace_site = M('cloudspace_site');
				$cloudspace_site_update_res = [];
				foreach ($siteConfig as $k => $config){
					//拼凑站点信息数据
					$config_data['site_capacity'] = $config['spaceSize'];
					$config_data['site_flow'] = $config['flow'];
					$config_data['ip_address'] = $config['ipAddress'];
					$config_data['site_name'] = $config['domain'];
					$config_data['business_id'] = $business_info ["business_id"];
					$config_data['ftp_password'] = $config['password'];
					$config_data['ftp_user'] = $config['ftpUser'];
					$config_data['create_time'] = $config['createDate'];
					$config_data['overdue_time'] = $config['overtime'];
					/**
					 * ---------------------------------------------------
					 * | 状态
					 * | @时间: 2017年1月18日 上午11:03:12
					 * ---------------------------------------------------
					 */
					$config_data['use_state'] = $config['zt'];
					$config_data['state'] = $config['yxzt'];
					//查看是否有该条站点信息
					$site_info = $this->model()->getSiteInfoByDomainAndId($config['domain'], $id);
					//站点信息，有就更新，没有就添加;
					if (empty($site_info)){
						$config_data['cloudspace_id'] = $id;
						$cloudspace_site_update_res[] = $m_cloudspace_site->add($config_data);
					}else {
						$where['site_name'] = [ 'eq', $config['domain'] ];
						$where['cloudspace_id'] = [ 'eq', $id ];
						$cloudspace_site_update_res[] = $m_cloudspace_site->where($where)->save($config_data);
					}
				}
				//如果有某条站点信息记录更新失败了
				if (!empty($cloudspace_site_update_res) && array_search(false, $cloudspace_site_update_res, true) !== false){
					$this->model()->setError('服务器繁忙，同步失败，请重试');
					return false;
				}
				
			}
			
			if ($cloudspace_update_res === false){
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
	 * | 删除云空间的方法
	 * | @时间: 2016年11月4日 下午2:32:16
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function eraseCloudspaceBusiness($id){
		return $this->erase( $this->firstOrFalse( [ 'id' => [ 'eq', $id] ] ), GiantAPIParams::PTYPE_CLOUD_SPACE );
	}
	
	
}