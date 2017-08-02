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
class CloudserverBusinessRespository extends BusinessRespository{

	
	
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
			}elseif ($filed == 'ipaddress'){
				$fields[$k] = 'd.'.$filed;
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
				'left join '.$this->table_prefix.'cloudserver_business_ip as d on '.$this->alias.'.api_bid = d.belong_server',
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
			}elseif ($k == 'ipaddress'){
				$_where['d.ipaddress'] = $v;
			}else {
				$_where[$this->alias.'.'.$k] = $v;
			}
		}
		return $_where;
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
		$where['a.state'] =[ 'eq', 1 ];
		$counter['successful'] = $this->queryBuilder($field, $where);
	
		/**
		 * ---------------------------------------------------
		 * | 已删除业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		*/
		$where['a.state'] =[ 'eq', 2 ];
		$counter['deleted'] = $this->queryBuilder($field, $where);
	
		/**
		 * ---------------------------------------------------
		 * | 已过期业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		*/
		$where['a.state'] =[ 'eq', 3 ];
		$counter['expired'] = $this->queryBuilder($field, $where);
	
		/**
		 * ---------------------------------------------------
		 * | 失败业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		*/
		$where['a.state'] =[ 'eq', 4 ];
		$counter['failed'] = $this->queryBuilder($field, $where);
	
		return $counter;
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 同步云服务器的业务逻辑
	 * | 方法内部已经对ptype进行了处理，会自动找出对应的ptype，不需要再传入了
	 * | @时间: 2016年10月31日 下午5:05:36
	 * | @author: duanbin
	 * | @param: $business_info  一条业务记录或者是业务表的id值  建议传入一条业务记录
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function CloudserverBusinessSynchronizing($business_info, $attr = 'api_bid'){
		//如果你传入的是id值那么就获取信息，如果你传入的是一条业务记录(数组),那么就不用再去取了
		if (is_numeric($business_info) && !is_array($business_info)){
			//先获取本地业务信息
			$business_info_exists =$this->model()->where([ $attr => [ 'eq',$business_info ] ])->find();

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
// 		dump($business_info);die;
		//调用api同步业务信息
		$result = $this->syncBusinessFromZZIDC($business_info, GiantAPIParams::PTYPE_CLOUD_SERVER, GiantAPIParams::PNAME_CLOUDSERVER_SERVER);
// 		dump($result);die;
		if(is_array($result) && !empty($result['json'])){
			//说明通用的单次同步业务逻辑未出错；
			//拿取api返回的信息
			$product_info = $result['product_info'];
			$bus_info = $result['json'] ['info'] ;

			//处理相应的信息，拼凑要更新到本地的数据
			// 业务信息
			$parms['create_time'] = $bus_info['createDate']; // 创建时间
			$parms['overdue_time'] = $bus_info['overDate']; // 到期时间
			$parms['cloudserver_user'] = $bus_info['username'];
			$parms['cloudhost_password'] = $bus_info['password'];
			$parms['input_name'] = $bus_info['input_name'];
			$parms['cpu'] = $bus_info['cpucount'];
			$parms['memory'] = $bus_info['memory'];
			$parms['disk'] = $bus_info['ssddisk'];
			$parms['api_bid'] = $bus_info['ywbh'];
			$parms['buy_time'] = $bus_info['gmqx'];
			$parms['nw_ip'] = $bus_info['nwip'];
			$parms['area_code'] = $bus_info['server_areacode'];
			$parms['os_type'] = $bus_info['sysTem'];
			//这里在顺便同步一下快云服务器的ip，如果有的话
			if($bus_info['ip_ywbh'] != 'excep' && $bus_info['ip_ywbh'] != null){
				$parms ['ip_bid'] = $bus_info['ip_ywbh'];
				$parms['ip_state'] = '0';
				//这里处理下ip的同步
				$cloudserber_ip_business = new \Common\Respository\CloudserverIpBusinessRespository(new \Backend\Model\CloudserverIpModel());
				$ip_sync_res = $cloudserber_ip_business->CloudserverIpBusinessSynchronizing($bus_info['ip_ywbh'], $business_info['business_id']);
				if ($ip_sync_res === false){
					$this->model()->setError('服务器繁忙，ip地址同步失败，请重试');
					return false;
				}
			}else{
				$parms['ip_state'] = '2';
			}
			$parms['sync_state'] ='1';
			$parms['product_name'] = '快云服务器';
			$parms['product_id'] = '214';
			$parms['user_id'] = !$business_info['user_id'] ? '-1': $business_info['user_id'];
			$parms['login_name'] = !$business_info['login_name'] ? '待转让会员': $business_info['login_name'];
			//这里更新的是cloudspace业务表的信息
			$cloudspace_update_res = $this->model()->where([ 'id' => [ 'eq', $id ] ])->save($parms);

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
	 * | 删除云服务器的方法
	 * | @时间: 2016年11月4日 下午2:32:16
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function eraseCloudserverBusiness($id){
		return $this->erase( $this->firstOrFalse( [ 'id' => [ 'eq', $id] ]), GiantAPIParams::PTYPE_CLOUD_SERVER, 'cloudserver.server'  );
	}
	

	
}