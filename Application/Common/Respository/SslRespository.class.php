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
class SslRespository extends BusinessRespository{

	/**
	 * ----------------------------------------------
	 * | 同步域名业务的方法
	 * | 
	 * | @时间: 2016年11月7日 下午6:30:36
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
 	public function SslBusinessSynchronizing($business_info){
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
 		$result = $this->syncBusinessFromZZIDC($business_info, GiantAPIParams::PTYPE_SSL);
 		if(is_array($result) && !empty($result['json'])){
			$bus_info = $result['json'] ['info'] ;
            //根据返回的业务信息，获取相应的产品信息;
            $product_service = new \Common\Model\ProductModel();
            $product_info=$product_service->getProductInfoByApiNameAndApiPtype($bus_info['productName'], GiantAPIParams::PTYPE_SSL,'',4001);
            if($product_info==null){
                $this->model()->setError(business_code(-2));
                return FALSE;
            }
			$parms['product_id']=$product_info['id'];
			$parms['product_name']=$product_info['product_name'];
			$parms['business_id']=$bus_info['bid'];
			$parms['create_time']=$bus_info['ktsj'];
			$parms['overdue_time']=$bus_info['dqsj'];
			$parms['buy_time']=$bus_info['gmqx'];
			$parms['state']=$bus_info['ywzt'];
			$parms['free_trial']=0;
			$parms['domain_name']=$bus_info['ymbd'];
			$parms['registrant']=$bus_info['applyName'];
			$parms['mobile']=$bus_info['applyPhone'];
			$parms['mail']=$bus_info['applyEmail'];
			$parms['params']=$bus_info['ymgs'].','.$bus_info['tpxgs'].','.$bus_info['fwqgs'];
 			//TODO:
			
 			//更新域名信息
 			$update_res = $this->model()->where([ 'Id' => [ 'eq', $id ] ])->save($parms);
	
 			//同步域名或域名联系人的结果
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
	 * | 删除域名业务的方法
	 * | @时间: 2016年11月7日 下午6:31:38
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
 	public function eraseSslBusiness($id){
 		return $this->erase( $this->firstOrFalse( [ 'Id' => [ 'eq', $id] ] ), GiantAPIParams::PTYPE_SSL,"ssl");
 	}








}