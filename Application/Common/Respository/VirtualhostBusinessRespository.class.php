<?php
namespace Common\Respository;
use Common\Respository\BusinessRespository;
date_default_timezone_set('prc');


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
class VirtualhostBusinessRespository extends BusinessRespository{

	
	/**
	 * ----------------------------------------------
	 * | 同步虚拟主机的业务逻辑
	 * | 方法内部已经对ptype进行了处理，会自动找出对应的ptype，不需要再传入了
	 * | @时间: 2016年10月31日 下午5:05:36
	 * | @author: duanbin
	 * | @param: $business_info  一条业务记录或者是业务表的id值  建议传入一条业务记录
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function VirtualhostBusinessSynchronizing($business_info, $ptype = ''){
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
		
		//对于虚拟主机的业务，需要特殊处理下。。。。
		//由于是批量传入，所以此处处理下ptype类型的合法性
		if (!$business_info['product_id'] && $ptype == ''){
			$m_product = new \Common\Model\ProductTypeModel();
			$ptype = $m_product->getApiPtypeByProductId($business_info['product_id']);
		}
		if (!in_array($ptype, $this->model()->internalPtype)){
			$this->model()->setError('ptype参数类型不对！');
			return false;
		}
		//调用api同步业务信息
		$result = $this->syncBusinessFromZZIDC($business_info, $ptype);
		if(is_array($result) && !empty($result['json'])){
			//说明通用的单次同步业务逻辑未出错；
			//拿取api返回的信息
			$bus_info=$result['json']['info']['expir'];
			$bindDomain=$result['json']['info']['config']['bindDomain'];
			$product_info = $result['product_info'];
			//处理相应的信息，拼凑要更新到本地的数据
			$parms['bindDomain']=$bindDomain;
			$parms['ip_address']=$bus_info['ip'];
			// 业务信息
			$parms ['ftp_password'] = $bus_info ['pwd'];
			$parms ['create_time'] = $bus_info ['createDate'];
			$parms ['overdue_time'] = $bus_info ['overDate']; // 创建时间
			$parms ['open_time'] = $bus_info ['createDate']; // 到期时间
			$parms['virtual_type'] = array_search($ptype, $this->model()->internalPtype);

			//根据到期时间和开通时间来判断服务期限
			$parms ['service_time'] = $bus_info['payTerm'];
			if($bus_info['payTerm'] == '2天'){
				$parms['free_trial']=1;
				$parms ['service_time'] = 2;
			}else{
				$parms['free_trial']=0;
				$parms ['service_time'] = $bus_info['payTerm'];
			}
			$new_data = date('y-m-d h:i:s',time());
			$parms ['domain_name'] =$bus_info['domain'];
			if($parms['overdue_time']>$parms['open_time']){
				$parms['state']=1;
			}elseif($parms['overdue_time']>$new_data){
			    $parms['state']=3;
			}else{
				$parms['state']=$bus_info['status'];
			}
			$parms ['system_type']=$bus_info['czxt'];
			$parms['product_name']=$product_info['product_name'];
			$parms['product_id']=$product_info['id'];
			$parms['area_code']=$product_info['area_code'];
			$parms['sync_state']=1;
			//这里更新virtualhost_business表的信息
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
	* 通过订单获取业务信息
	* @date: 2016年12月21日 下午5:30:54
	* @author: Lyubo
	* @param: $order_id
	* @return: business_info
	*/
	public function VirtualhostOrderSynchronizing($order_id){
	   $order_info =  $this->model()->order_find($order_id);
	    if(!is_array($order_info)){
	        $this->model()->setError('参数类型不对！');
	        return false;
	    }
	    $m_product = new \Common\Model\ProductTypeModel();
	    $ptype = $m_product->getApiPtypeByProductId($order_info['product_id']);
	    //调用api同步业务信息
	    $result = $this->syncOrderFromZZIDC($order_info,$ptype);
	    if(is_array($result) && !empty($result['json'])){
	        //说明通用的单次同步业务逻辑未出错；
	        //拿取api返回的信息
	        $bus_info=$result['json']['info']['expir'];
	        $bindDomain=$result['json']['info']['config']['bindDomain'];
	        $config =$result['json']['info']['config'];
	        $product_info = $bus_info['product_info'];
	        $parms['user_id']=$order_info['user_id'];
	        $parms['login_name']=$order_info['login_name'];
	        $parms['business_id']=$bus_info['bid'];
	        $parms['state']= '1';
	        $parms['product_id'] = $order_info['product_id'];
	        $parms['product_name'] = $order_info['product_name'];
	        $parms['ip_address'] = $config['ipAddress'];
	        $parms['domain_name'] = $bus_info['domain'];
	        $parms ['create_time'] = $bus_info ['createDate'];
			$parms ['overdue_time'] = $bus_info ['overDate']; // 创建时间
			$parms ['open_time'] = $bus_info ['createDate']; // 到期时间
			//根据到期时间和开通时间来判断服务期限
			$parms ['service_time'] = $bus_info['payTerm'];
			if($bus_info['payTerm'] == '2天'){
			    $parms['free_trial']=1;
			    $parms ['service_time'] = 2;
			}else{
			    $parms['free_trial']=0;
			    $parms ['service_time'] = $bus_info['payTerm'];
			}
			$parms['area_code']=$order_info['area_code'];
			$parms['sync_state']=1;
			if($ptype == 'host'){
			    $virtual_type = '0';
			}elseif($ptype == 'hkhost'){
			   $virtual_type = '1';
			}elseif ($ptype == 'usahost'){
			    $virtual_type = '2';
			}elseif($ptype == 'cloudVirtual'){
			    $virtual_type = '3';
			}elseif ($ptype == 'dedehost'){
			    $virtual_type = '4';
			}
			$parms['virtual_type'] = $virtual_type;
			$parms['note_appended']='通过订单获取业务成功';
			$parms['bindDomain']=$bindDomain;
			$parms ['system_type']=$bus_info['czxt'];
	        if($order_info['product_type'] == '7' || $order_info['product_type'] == '8' || $order_info['product_type'] == '15' || $order_info['product_type'] == '16' || $order_info['product_type'] == '17'){
	               $vh = new \Frontend\Model\VirtualhostModel();
	               $vh_where['business_id'] = $bus_info['bid'];
	               $vhost_info = $vh->where($vh_where)->find();
	               if($vhost_info == null && empty($vhost_info)){
	                   $insert = $vh->add($parms);
	                   if($insert){
	                       return true;
	                   }
	               }else{
	                   $this->model()->setError('业务已存在');
	                   return FALSE;
	               }
	        }
	    }else{
	            //说明同步调用api出错了,将错误结果返回出去
	            $this->model()->setError(business_code($result));
	            return FALSE;
	    }
	}
	
	/**
	 * ----------------------------------------------
	 * | 删除虚拟主机的方法
	 * | @时间: 2016年11月4日 下午2:32:16
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function eraseVirtualhostBusiness($id, $ptype){
		return $this->erase( $this->firstOrFalse( [ 'id' => [ 'eq', $id] ]), $ptype );
	}

}