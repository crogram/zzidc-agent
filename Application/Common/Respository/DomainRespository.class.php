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
class DomainRespository extends BusinessRespository{
	
	
	
	/**
	 * ----------------------------------------------
	 * | 扩充查询字段，在连表的情况下有别名使用
	 * | @时间: 2016年10月14日 上午10:19:58
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function fieldsExtra($fields){
		if (is_string($fields)){
			$fields = explode(',', $fields);
		}
		foreach ($fields as $k => $filed){
			if ($filed == 'product_name'){
				$fields[$k] = 'b.'.$filed;
			}elseif ($filed == 'login_name'){
				$fields[$k] = 'c.'.$filed;					
			}elseif ($filed == 'dwmc'){
				$fields[$k]= 'd.'.$filed;
			}else{
				$fields[$k] = $this->alias.'.'.$filed;
			}
		}
		return $fields;
	}
	
	/**
	 * ----------------------------------------------
	 * | 返回一个join数组
	 * | 连表查询时，请实现此方法
	 * | @时间: 2016年10月13日 下午6:14:18
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function joinsExtra(){
		return [
				'left join '.$this->table_prefix.'product as b on '.$this->alias.'.product_id = b.id',
				'left join '.$this->table_prefix.'member as c on '.$this->alias.'.user_id = c.user_id',
				'left join '.$this->table_prefix.'domain_contact as d on '.$this->alias.'.id = d.domain_business_id',
		];
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 额外的扩充条件，这里要增加一项
	 * | product_id值得条件
	 * | @时间: 2016年10月13日 下午6:34:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function whereExtra($where, $request){
		$_where = [];
		foreach ($where as $k => $v){
			if ($k == 'product_name') {
				$_where['b.product_name'] = $v;
			}elseif ($k == 'login_name'){
				$_where['c.login_name'] = $v;
			}elseif ($k == 'dwmc'){
				$_where['d.dwmc'] = $v;
			}else {
				$_where[$this->alias.'.'.$k] = $v;
			}
		}
		$where = $_where;
		return $where;
	}

	/**
	 * ----------------------------------------------
	 * | 额外的扩充排序，这里要增加一项
	 * | product_id值得条件
	 * | @时间: 2016年10月13日 下午6:34:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function orderExtra($order, $request){
		//增加默认排序规则
		if (empty($order)){
			//到期时间倒序
			$order = $this->alias . '.' . 'overdue_time DESC';
		}else {
			$order = $this->alias . '.' . $order;
		}
		return $order;
	}

	/**
	 * ----------------------------------------------
	 * | 业务统计方法，统计出各个分类的总数等
	 * | @时间: 2016年10月26日 上午11:59:06
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function counter($field){
		$counter = [];
	
		/**
		 * ---------------------------------------------------
		 * | 总记录数
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		*/
		$counter['total'] = $this->queryBuilder($field, []);
		/**
		 * ---------------------------------------------------
		 * | 成功业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		*/
		$where['state'] =[ 'eq', 1 ];
		$counter['successful'] = $this->queryBuilder($field, $where);
	
		/**
		 * ---------------------------------------------------
		 * | 已删除业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		*/
		$where['state'] =[ 'eq', 3 ];
		$counter['deleted'] = $this->queryBuilder($field, $where);
	
		/**
		 * ---------------------------------------------------
		 * | 已过期业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		*/
		$where['state'] =[ 'eq', 2 ];
		$counter['expired'] = $this->queryBuilder($field, $where);
	
		/**
		 * ---------------------------------------------------
		 * | 失败业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		*/
		$where['state'] =[ 'eq', 0 ];
		$counter['failed'] = $this->queryBuilder($field, $where);
	
		return $counter;
	}







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
	public function DomainBusinessSynchronizing($business_info, $attr = 'api_bid'){
		//如果你传入的是id值那么就获取信息，如果你传入的是一条业务记录(数组),那么就不用再去取了
		if (is_numeric($business_info) && !is_array($business_info)){
			//先获取本地业务信息
			$business_info_exists =$this->model()->where([ 'api_bid' => [ 'eq',$business_info ] ])->find();
			//如果不存在这条记录，那么就写进去
			if (empty($business_info_exists)){
				list($business_info, $temp) = [ [], $business_info];
				$business_info[$attr] = $temp;
				$business_info['user_id'] = -1;
				$add_id = $this->model()->add($business_info);
				$business_info['id'] = $add_id;
			}else {
				$business_info = $business_info_exists;
			}
		}
		$business_info['business_id'] = $business_info[$attr];
		$id = $business_info['id'];
		
		//调用api同步业务信息
		$result = $this->syncBusinessFromZZIDC($business_info, GiantAPIParams::PTYPE_DOMAIN, 'getbusinfo', 15);
// 				dump(business_code($result));die;
		if(is_array($result) && !empty($result['json'])){
			$bus_info = $result['json'] ['info'] ;
			//这里处理下产品id问题
			$product_name=$bus_info['product_name'];
			//拿到产品类型id
			$product_type=$bus_info['ymlx'];
			if($product_type==1){
				$product_type=10;	//中文域名
			}else{
				$product_type=9;		//英文域名
			}
			//获取产品信息
			$m_common_product = new \Common\Model\ProductModel();
			$product_info = $m_common_product->getProductByProductNameAndProductTypeId($product_name, $product_type);

			$parms['product_id']=$product_info['id'];
			$parms['create_time']=$bus_info['createDate'];
			$parms['overdue_time']=$bus_info['overDate'];
			$parms['api_bid']=$bus_info['api_bid'];
			$parms['product_name']=$bus_info['product_name'];
			$parms['domain_name']=$bus_info['domain_name'];
			$date=date('Y-m-d H:i:s');
			if($bus_info['overDate'] < $date){
				$parms['state']='0';
			}else{
				$parms['state']='1';
			}
			if($bus_info['mm']==null){
			$parms['mm']=null;
			}else{
				$parms['mm']=$bus_info['mm'];
			}
			if($bus_info['api_type']==100004){
				$parms['provider']='新网互联';
				$parms['api_type']=6;//3：万网4：新网5：中国数据6：新网互联7:ResellerClub
			}else if($bus_info['api_type']==100006){
				$parms['provider']='ResellerClub';
				$parms['api_type']=7;//3：万网4：新网5：中国数据6：新网互联7:ResellerClub
			}else{
				$parms['api_type']=$bus_info['api_type'];
				if($bus_info['api_type']==3){
					$parms['provider']='万网';
				}else if($bus_info['api_type']==4){
					$parms['provider']='新网';
				}else if($bus_info['api_type']==5){
					$parms['provider']='中国数据';
				}
			}
			$parms['fdnsym']=$bus_info['fdnsym'];
			$parms['zdnsym']=$bus_info['zdnsym'];
			//更新域名信息
			$update_res = $this->model()->where([ 'id' => [ 'eq', $id ] ])->save($parms);
			
			//这里额外处理下域名联系人信息
			$m_common_domain_contect = new \Common\Model\DomainContactModel();
			$contect_exist = $m_common_domain_contect->firstOrfail($id, 'domain_business_id');
			//准备处理的数据
			$parms_contect['domain_business_id'] = $id;
			$parms_contect['zcrSF']=$bus_info['zcrSF'];
			$parms_contect['zcrGJ']=$bus_info['zcrGJ'];
			$parms_contect['zcrCS']=$bus_info['zcrCS'];
			$parms_contect['dwmc']=$bus_info['dwmc'];
			$parms_contect['zcrX']=$bus_info['zcrX'];
			$parms_contect['zcrYX']=$bus_info['zcrYX'];
			$parms_contect['zcrM']=$bus_info['zcrM'];
			$parms_contect['zcrDZ']=$bus_info['zcrDZ'];
			$parms_contect['zcrCZ']=$bus_info['zcrCZ'];
			$parms_contect['zcrDZ2']=$bus_info['zcrDZ2'];
			$parms_contect['zcrDH']=$bus_info['zcrDH'];
			$parms_contect['zcrYZBM']=$bus_info['zcrYZBM'];
			$parms_contect['dwmcYW']=$bus_info['dwmcYW'];
			$parms_contect['create_time']=$bus_info['createDate'];
			if (empty($contect_exist)){
				$parms_contect['user_id']='-1';
				$contect_res = $m_common_domain_contect->add($parms_contect);
			}else {
				$contect_res = $m_common_domain_contect->where([ 'id' => [ 'eq', $contect_exist['id'] ] ])->save($parms_contect);
			}
			
			//同步域名或域名联系人的结果
			if($update_res === false || $contect_res === false){
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
	public function eraseDomainBusiness($id){
		return $this->erase( $this->firstOrFalse( [ 'id' => [ 'eq', $id] ] ), GiantAPIParams::PTYPE_DOMAIN);
	}








}