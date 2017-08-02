<?php
namespace Common\Respository;
use Common\Respository\BaseRespository;

use Common\Aide\AgentAide;
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
class BusinessRespository extends BaseRespository{
	
	
	
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
			}else {
				$_where[$this->alias.'.'.$k] = $v;
			}
		}
		return $_where;
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
			$order = $this->alias . '.' . 'id DESC';
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
		$where['state'] =[ 'eq', 2 ];
		$counter['deleted'] = $this->queryBuilder($field, $where);
		
		/**
		 * ---------------------------------------------------
		 * | 已过期业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		 */
		$where['state'] =[ 'eq', 3 ];
		$counter['expired'] = $this->queryBuilder($field, $where);
		
		/**
		 * ---------------------------------------------------
		 * | 失败业务
		 * | @时间: 2016年10月26日 下午1:47:49
		 * ---------------------------------------------------
		 */
		$where['state'] =[ 'eq', 4 ];
		$counter['failed'] = $this->queryBuilder($field, $where);
		
		return $counter;
	}


	/**
	 * ----------------------------------------------
	 * | 转让业务的方法
	 * | @时间: 2016年10月28日 上午11:50:52
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function assignment($request){
		//会员Id
		$uid = $request['uid'];
		//业务id(多个)
		$bids = $request['bids'];
		
		//查询待转让的会员信息
		$where['user_id'] = [ 'eq', $uid ];
		$member  = M('member')->where($where)->find();
		
		if (empty($member)){
			$this->model()->setError('该会员不存在！');
			return false;
		}
		//处理业务id
		$bids = array_filter(explode(',', $bids), function ($v){
			return $v+0;
		});
		//转让业务
		$data['user_id'] = $uid+0;
		//如果是云空间的话，需要连表，因为此表没有login_name和product_name字段
		if (stripos(get_class($this->model()), 'Cloudspace') === false){
			$data['login_name'] = $member['login_name'];
		}
		//靠，cloudserver_business表和ssl_business表的主键字段是Id,不是id。醉了无语了。
		if (stripos(get_class($this->model()), 'CloudserverIp') === false && stripos(get_class($this->model()), 'ssl') === false){
			$update_where['id'] = [ 'in', $bids ];
		}else {
			$update_where['Id'] = [ 'in', $bids ];
		}

		$res = $this->model()->where($update_where)->save($data);
		if ($res === false){
			$this->model()->setError('服务器繁忙！');
			return false;
		}
		//如果是快云服务器的转让，那么也转让一下他对应的ip,如果有的话
		if (stripos(get_class($this->model()), 'Cloudserver') !==false){
			//获取页面传入的所有的快云服务器信息
			$cloudserver_info = $this->model()->where($update_where)->select();
			$m_cloudserver_ip = M('cloudserver_business_ip');
			foreach ($cloudserver_info as $k => $v){
				//如果有绑定ip的话，就直接把绑定的ip也一并修改给该会员
				if ( ($v['ip_state']+0) == 0 && empty($v['ip_bid'])){
					$ip_update_where['api_bid'] = [ 'eq', $v['ip_bid'] ];
					$ip_update_where['belong_server'] = [ 'eq', $v['api_bid'] ];
					$m_cloudserver_ip->where($where)->save($data);
				}
			}
		}
		//返回快云服务器的修改结果
		return $res;
	}
	

	
	/**
	 * ----------------------------------------------
	 * | 同步业务的主方法
	 * | @时间: 2016年10月31日 下午5:35:32
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function syncBusinessFromZZIDC($business_info, $ptype, $pname = '', $tid = 18){
		
		//记录日志
		$log_message = "ptype:" .$ptype . "||bid:" . $business_info ["business_id"].'||tid='.$tid;
		try {
			//引入景安api sdk;
			$agent = new AgentAide();
			$transaction = $agent->servant;
			
			//调用接口,同步业务
			//同步域名的方法。。。恶心
			if (in_array($ptype, [GiantAPIParams::PTYPE_DOMAIN])){
				$result = $transaction->getdomaininfo($business_info ["business_id"]);
			}else {
				//同步其他所有业务的方法
				$result = $transaction->syncBusinessInfo($ptype, $business_info ["business_id"],$pname);
			}
		
			api_log ( $business_info ["user_id"], $log_message, $result, '同步'.$ptype.'信息，业务编号：['.$business_info ["business_id"].']');
			//解析JSON
			$json = json_decode ( $result ,true);
		} catch ( \Exception $e ) {
			api_log ( $business_info ["user_id"], $log_message, $e->getMessage(), '同步'.$ptype.'信息，业务编号：['.$business_info ["business_id"].']失败，接口调用失败');
			return - 9;
		}
		if($json['code']!=0){
			return $json['code'];
		}
		//业务信息
		$expir_and_config = [
			GiantAPIParams::PTYPE_CLOUD_SPACE,
			GiantAPIParams::PTYPE_CLOUD_VIRTUAL,
			GiantAPIParams::PTYPE_HOST,
			GiantAPIParams::PTYPE_HK_HOST,
			GiantAPIParams::PTYPE_USA_HOST,
			GiantAPIParams::PTYPE_DEDE_HOST,
			GiantAPIParams::PTYPE_FAST_CLOUDVPS,
			GiantAPIParams::PTYPE_VPS,
		];
		//云服务器的业务id是这样的$json['info']
		if(in_array($ptype,[GiantAPIParams::PTYPE_CLOUD_SERVER,GiantAPIParams::PTYPE_DOMAIN,GiantAPIParams::PTYPE_CLOUD_DATABASE,GiantAPIParams::PTYPE_SSL]) )	{
			$bus_info=$json['info'];
			if(null==$bus_info){
				return - 10;
			}
			$return = [ 'json' => $json ];
			
		//同步(云空间、云虚拟主机)的处理
		//vps相关的  是首字母大写。。。真恶心
		}else if ( in_array($ptype, $expir_and_config) ) {
			$bus_info = !$json ['info'] ['expir'] ? $json['info']['Extir']: $json ['info'] ['expir'];
			if (null == $bus_info) {
				return - 10;
			}
			$bus_config = $json ['info'] ['config'];
			if (null == $bus_config) {
				return - 10;
			}

			//根据返回的业务信息，获取相应的产品信息;
			$product_service = new \Common\Model\ProductModel();
			$product_info=$product_service->getProductInfoByApiNameAndApiPtype($bus_info['productName'], $ptype,'',$bus_info['areaCode']);
			if($product_info==null){
				return -2;
			}
			
			//如果返回值里有Ipv6的信息，那么就相应的处理下ipv6的数据
			//快云服务器和快云服务器ip没有该项目
			if($json['info']['ipv6']!=null){
				foreach ($json['info']['ipv6'] as $key=>$value){
					$arr['business_id']=$bus_info['bid'];
					$arr['api_type']=$product_info['api_ptype'];
					$arr['IPv6']=$value;
					//查询ipv6是否已经同步
					if(!$product_service->Ipv6Exists($arr['IPv6'])){
						//不存在就写入
						$product_service->addIpv6($arr);
					}
				}
			}
			
			//接口返回增值信息
			//如果接口返回的有增值业务的信息，那么就经对应的代理平台的增值业务信息删掉，在添加
			//删除对应的增值业务信息
			$destroy_where['business_id'] = [ 'eq', $business_info ['business_id']];
			$destroy_where['product_type_id'] = [ 'eq', $product_info['product_type_id']];
			$m_common_appreciation = new \Common\Model\AppreciationModel();
			$m_common_appreciation->destroyAppreciation($destroy_where);
			//快云服务器和快云服务器ip没有该项目
			$addValue=$json['info']['addValue'];
			if($addValue!=null && count($addValue)>0){
				//添加增值业务信息
				$m_common_appreciation->enter($addValue, $product_info, $business_info);
			}

			$return = [
				'json' => $json,
				'product_info' => $product_info,
			];
		}
		return $return;

	}
	
	
	/**
	 * ----------------------------------------------
	 * | *******************批量同步业务信息*********************
	 * | @时间: 2016年11月1日 下午4:52:00
	 * | @author: duanbin
	 * | @param: $ptype 业务类型(vps,fast_vps...)  $flag(diff,all)差异同步还是全部同步
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function syncBatch($ptype, $flag, $pname, $bid_field = 'business_id'){
		if ($flag == 'diff'){
			//对虚拟主机的异步操作进行下特殊处理
			if (in_array($ptype, [GiantAPIParams::PTYPE_CLOUD_SERVER, GiantAPIParams::PTYPE_DOMAIN,GiantAPIParams::PTYPE_CLOUD_DATABASE])){
				$res = $this->syncBatchBusinessFromZZIDC($ptype, '', $pname, 'api_bid');
				
				//云主机的业务id字段是api_bid
			}else if (in_array($ptype, [ GiantAPIParams::PTYPE_CLOUD_HOST ])){
				$res = $this->syncBatchBusinessFromZZIDC($ptype, '', '', 'api_bid');
				
			}else {
				$res = $this->syncBatchBusinessFromZZIDC($ptype);
			}

		}elseif ($flag == 'all'){
			//这里注意云主机的business_id字段是api_bid。。。
			//云服务器的也是。。。。
			//还有域名。。。已阵亡
			if (in_array($ptype, [ GiantAPIParams::PTYPE_CLOUD_HOST ,GiantAPIParams::PTYPE_CLOUD_SERVER,GiantAPIParams::PTYPE_DOMAIN,GiantAPIParams::PTYPE_CLOUD_DATABASE])){
				$res = $this->getBusinessFromSelf("api_bid");
				
				//如果是主机类的
			}else if (in_array($ptype, $this->model()->internalPtype) ){
				$res = $this->getBusinessFromSelf('business_id', [ 'virtual_type' => [ 'eq', array_search($ptype, $this->model()->internalPtype) ] ]);
			}else{
				$res = $this->getBusinessFromSelf();
			}
		}
		if (is_array($res)){
			return $res;
		}else {
			//说明同步调用api出错了,将错误结果返回出去
			$this->model()->setError(business_code($res));
			return FALSE;
		}
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 批量同步业务的核心方法
	 * | 差异同步业务的主方法
	 * | @时间: 2016年11月1日 下午6:02:05
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function syncBatchBusinessFromZZIDC($ptype, $time = '', $pname = '', $bid_field = 'business_id'){
		//批量同步的tid是20
		if ($ptype == GiantAPIParams::PTYPE_DOMAIN){
			$tid = 15;
		}else {
			$tid = 20;
		}
		$log_message = "ptype:" . $ptype . '||tid='.$tid;
		try {
			//引入景安api sdk;
			$agent = new AgentAide();
			$transaction = $agent->servant;
			$flag['code'] = - 1;
			if ($ptype == GiantAPIParams::PTYPE_DOMAIN){
				//同步所有域名业务的方法
				$result = $transaction->syscAllBusinessInfo($ptype, $tid, $time, $pname);
			}else {
				//同步所有其他业务的方法
				$result = $transaction->syscAllBusinessInfo($ptype, $tid, $time, $pname);
			}
			// 记录日志
			api_log("", $log_message, $result, '同步'.$ptype.'信息');
			// 解析JSON
			$json = json_decode($result, true);
		} catch (\Exception $e) {
			api_log("", $log_message, $e->getMessage(), '同步'.$ptype.'信息失败，接口调用失败');
			return - 9;
		}

		// 业务信息
		if ($json['code'] != 0) {
			return $json['code'];
		}
		
		$bus_info = $json['info'];
		if ($bus_info == null) {
			return - 10;
		}
		//拿到业务id，如果是host、usahost、hkhost返回形式
		//需要特殊处理下
		if (in_array($ptype,$this->model()->internalPtype ) ||  in_array($ptype, [GiantAPIParams::PTYPE_CLOUD_SPACE,GiantAPIParams::PTYPE_DOMAIN]) ){
			foreach ($bus_info as $k => $v){
				$business_ids[] = $v['bh'];
			}
			//云空间不需要这个字段
			if (!in_array($ptype, [GiantAPIParams::PTYPE_CLOUD_SPACE,GiantAPIParams::PTYPE_DOMAIN])){
				//将主机类型保存到virtual_type字段中
				$parms['virtual_type'] = array_search($ptype, $this->model()->internalPtype);
			}
		}elseif($ptype == GiantAPIParams::PTYPE_SSL){
			foreach ($bus_info as $k => $v){
				$business_ids[] = $v['ywbh'];
			}
		}else {
			$business_ids = explode(',', $bus_info);
		}

		//保存需要同步的业务的id
		$data = [];
		foreach ($business_ids as $k => $business_id){
			if (!empty($business_id)){
				$business_exist = $this->firstOrFalse([ $bid_field => [ 'eq', $business_id ] ]);
				//如果业务不存在，那么就写入到数据库里
				if (empty($business_exist)) {
					//组装数据
					$parms['user_id'] = - 1;
					//如果是云空间的话，需要连表，因为此表没有login_name和product_name字段
					if (stripos(get_class($this->model()), 'Cloudspace') ===false){
						$parms['login_name'] = "待转让会员";
						$parms['product_name'] = '未同步业务';
					}
					$parms['product_id'] = '0';
					$parms['state'] = 1;
					//云主机特有的字段
					//不是云主机特有的了，云服务器也是。。。(云主机  你不孤单了！！！)
					if (in_array($ptype, [ GiantAPIParams::PTYPE_CLOUD_HOST ,GiantAPIParams::PTYPE_CLOUD_SERVER, GiantAPIParams::PTYPE_DOMAIN])){
						$parms['api_bid'] = $business_id;
					}else {						
						$parms['business_id'] = $business_id;
						$parms['sync_state'] = '0';
					}

					$res = $this->model()->add($parms);
					if ($res !== false) {
						// 保存business_id
						$data[] = $business_id;
					}
				}
			}
		}
		return $data;
	}
	/**
	* 获取异常业务信息方法
	* @date: 2016年12月21日 下午5:44:28
	* @author: Lyubo
	* @param: $order_info,$ptype
	* @return: boolean
	*/
	public function syncOrderFromZZIDC($order_info,$ptype){
	    //通过订单ID获取业务信息的tid为7
	    $log_message = "ptype:" . $ptype . '||tid='.GiantAPIParams::TID_GET_BUSINESS_ID_ORDER_ID;
	    try {
	        //引入景安api sdk;
	        $agent = new AgentAide();
	        $transaction = $agent->servant;
	        $flag['code'] = - 1;
	        //同步所有其他业务的方法
	        $result = $transaction->get_business_id_order_id($ptype, $order_info['api_id']);
	        // 记录日志
	        api_log("", $log_message, $result, '获取订单业务'.$ptype.'信息');
	        // 解析JSON
	        $json = json_decode($result, true);
	    } catch (\Exception $e) {
	        api_log("", $log_message, $e->getMessage(), '获取订单业务'.$ptype.'信息失败，接口调用失败');
	        return - 9;
	    }
	    if ($json ['code'] != 0) {
	        return $json ['code'];
	    }
	    $bus_info=$json['info']['expir'];
	    //根据返回的业务信息，获取相应的产品信息;
	    $product_service = new \Common\Model\ProductModel();
	    $product_info=$product_service->getProductInfoByApiNameAndApiPtype($bus_info['productName'], $ptype);
	    if($product_info==null){
	        return -2;
	    }
	    $return = [
	        'json' => $json,
	        'product_info' => $product_info,
	    ];
	    return $return;
	}
	/**
	 * ----------------------------------------------
	 * | 返回本地某项业务的所有business_id值
	 * | 同步本地所有业务时使用
	 * | 供批量同步业务调用的方法
	 * | @时间: 2016年11月1日 下午6:10:04
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function getBusinessFromSelf($field = 'business_id', $where = []) {
		if (empty($where)){
			$res = $this->model()->field($field)->select();
		}else {
			$res = $this->model()->field($field)->where($where)->select();
		}
		$business_ids = [];
		foreach ($res as $k => $v){
			$business_ids[] = $v[$field];
		}
		return $business_ids;
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 删除业务的  主要逻辑
	 * | @时间: 2016年11月3日 下午3:14:01
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function erase($business_info, $ptype, $pname = ''){
		//先解决主键不一致的问题。。。— —|| 真尴尬。
		$pk = in_array($pname, [ 'cloudserver.ip','ssl']) ? 'Id': 'id';
// 		dump($business_info);
		//如果你传入的是id值那么就获取信息，如果你传入的是一条业务记录(数组),那么就不用再去取了
		if (is_numeric($business_info) && !is_array($business_info)){
			//先获取本地业务信息
			$business_info = $this->firstOrFalse([ $pk => [ 'eq',$business_info ] ]);
		}
		$id = $business_info['id'];
// 		//云主机相关
// 		if ( in_array($ptype, [ GiantAPIParams::PTYPE_CLOUD_HOST ]) ){
// 			//调用api同步业务信息
// 			$result = $this->CloudhostBusinessSynchronizing($business_info, $ptype);
	
// 			//vps相关
// 		}elseif ( in_array($ptype, [ GiantAPIParams::PTYPE_VPS, GiantAPIParams::PTYPE_FAST_CLOUDVPS ])){
// 			$result = $this->VpsBusinessSynchronizing($business_info, $ptype);
			
// 			//虚拟主机相关(因为虚拟主机有4个，所以这里要判断下)
// 			//还是因为有4个，所以这里同步的时候就不需要再传ptype了，同步方法内部处理好了
// 		}elseif ( in_array($ptype, $this->model()->internalPtype) ){
// 			$result = $this->VirtualhostBusinessSynchronizing($business_info, $ptype);
			
// 			//云空间相关
// 		}elseif ( in_array($ptype, [ GiantAPIParams::PTYPE_CLOUD_SPACE ]) ){
// 			$result = $this->CloudspaceBusinessSynchronizing($business_info);
			
// 			//快云服务器或者是快云服务器ip
// 		}elseif ( in_array($ptype, [ GiantAPIParams::PTYPE_CLOUD_SERVER]) ){
// 			//快云服务器
// 			if ($pname == 'cloudserver.ip'){
// 				$result = $this->CloudserverIpBusinessSynchronizing($business_info);
// 				//快云服务器ip
// 			}elseif ($pname == 'cloudserver.server'){
// 				$result = $this->CloudserverBusinessSynchronizing($business_info);
// 			}
			
// 			//域名业务
// 		}elseif ( in_array($ptype, [ GiantAPIParams::PTYPE_DOMAIN]) ){
// 			$result = $this->DomainBusinessSynchronizing($business_info);
			
// 			//ssl业务
// 		}elseif ( in_array($ptype, [ GiantAPIParams::PTYPE_SSL]) ){
// 			$result = $this->SslBusinessSynchronizing($business_info);
// 		}

// 		if ($result !=1012 && !$result){
// 			//$this->model()->setError('服务器繁忙，请稍后再试');
// 			return false;
// 		}
		//同步信息后，获取当前业务记录信息，
		$business_info = $this->firstOrFalse([ $pk => [ 'eq',$id ] ]);
// 		dump([ $pk => [ 'eq',$id ] ]);die;
		//比较过期时间，与当前时间，合理则删除;
		$overdue_time = new \DateTime($business_info['overdue_time']);
		$product_name = $business_info['product_name'];
		$login_name = $business_info['login_name'];
		$user_id = $business_info['user_id'];
		$new = new \DateTime('now');
    		if($overdue_time < $new){
    			$delete_res = $this->delete([ $pk => [ 'eq',$id ] ]);
    			if ($delete_res === false){
    				$this->model()->setError('服务器繁忙，请稍后再试');
    				return false;
    			}else {
    				return true;
    			}
    		}else{
    		    //如果是未同步业务也可以删除
    		    if($product_name == '未同步业务' || $login_name == '待转让会员' || $user_id == '-1'){
    		        $delete_res = $this->delete([ $pk => [ 'eq',$id ] ]);
    		        if ($delete_res === false){
    		            $this->model()->setError('服务器繁忙，请稍后再试');
    		            return false;
    		        }else {
    		            return true;
    		        }
    		    }else{
        			$this->model()->setError('服务时间未到期，不能删除！！');
        			return false;
    		    }
    		}
	
	}
	
	/**
	 * ----------------------------------------------
	 * | 批量删除业务
	 * | @时间: 2017年1月3日 下午2:52:07
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function batchErase($request, $pk = 'id'){
		
		$ids = explode(',', $request['ids']);
		$ids = array_filter($ids);
		
		$business_info = $this->model()->where([ $pk => [ 'in', $ids ] ])->select();
// 		dump($business_info);die;
		$error = $ok = $not_expired = 0;
		foreach ($business_info as $k => $v){
			//比较过期时间，与当前时间，合理则删除;
			$overdue_time = new \DateTime($v['overdue_time']);
			$new = new \DateTime('now');
			if($overdue_time < $new){
				$delete_res = $this->delete([ $pk => [ 'eq', $v[$pk] ] ]);
				if ($delete_res === false){
					$error++;
				}else {
					$ok++;
				}
			}else{
				$not_expired++;
			}
		}
		$this->model()->setError('本次共删除'.($error+$ok+$not_expired).'条记录，有'.$ok.'条记录删除成功，'.$error.'条删除失败，'.$not_expired.'条记录尚未过期，未删除。');
		return false;
		
	}
	
	
}