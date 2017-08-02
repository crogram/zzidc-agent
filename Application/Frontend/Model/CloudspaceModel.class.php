<?php
namespace Frontend\Model;
use Common\Data\StateData;
use Frontend\Model\BusinessModel;
use Common\Data\GiantAPIParamsData;
use Common\Aide\AgentAide;
class CloudspaceModel extends BusinessModel{
    protected $trueTableName = 'agent_cloudspace_business';
   
    public function conver_par(){
        $date =request();
        $where["bs.user_id"] = array("eq",session("user_id"));
        if(!empty($date['select'])){
            $select = clearXSS($date['select']);
            $map['bs.business_id'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map['bs.ip_address'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map["bs.beizhu"] = array("like" ,"%$select%");
            $where['_complex'] = $map;
        }
        return $where;
    }
    /**
     * 获取云空间子站点信息
     * @date: 2017年1月17日 下午2:35:56
     * @author: Lyubo
     * @param: variable
     * @return:
     */
    function get_cloudpace_site_info(){
        $cloudspace_id = I("get.cloudspace_id")+0;
        $where["cs.business_id"] = ['eq',$cloudspace_id];
        $cloudspace_site = M("cloudspace_site");
        $site_info = $cloudspace_site->alias(' cs ')->where($where)
            ->field("cs.id,cs.business_id,cs.site_capacity,cs.ip_address,cs.overdue_time,cs.create_time,cs.site_flow,cs.state,p.api_name,pt.api_ptype")
            ->join('inner join '.C('DB_PREFIX').'cloudspace_business as cb on cb.id=cs.cloudspace_id')
            ->join('inner join '.C('DB_PREFIX').'product as p on cb.product_id=p.id')
            ->join('inner join '.C('DB_PREFIX').'product_type as pt on p.product_type_id= pt.id ')
            ->select();
        return $site_info;
    }
  /**
  * 购买/试用云空间
  * @date: 2016年11月28日 下午5:08:34
  * @author: Lyubo
  * @param: $buy_info，购买参数
  * @param: $product_info,产品信息
  * @return:
  */
    public function cloudspace_buy($buy_info,$product_info,$product_type_info){
        $order = new \Frontend\Model\OrderModel();
        $datarray = array (
            "product_id" => $buy_info ['product_id'],
            "user_id" => $buy_info ['member_id'],
            "order_quantity" => 1,
            "free_trial" => $buy_info ['free_trial'], // 购买或者试用
            "order_time" => $buy_info ['free_trial'] == 1 ? 0 : $buy_info ['order_time'],
            "create_time" => date('Y-m-d H:i:s'),
            "complete_time" => date('Y-m-d H:i:s'), // 完成时间
            "system_type"=> $buy_info['system_type'] //操作类型
        );
        if ($datarray ['free_trial'] == 0) {
            // 写入订单
            $result = $order->order_do_entry ( $datarray, $datarray ['user_id'],StateData::NEW_ORDER ,$product_info,$product_type_info);
            if ($result < 0) {
                return $result;
            }
            return $this->api_cloudspace_buy ( $result, $datarray ['order_time'] ,$product_info);
        } else {
            $result = $order->order_do_entry ( $datarray, $datarray ['user_id'],StateData::NEW_ORDER,$product_info,$product_type_info );
            if ($result < 0) {
                return $result;
            }
            return $this->api_cloudspace_buy ( $result ,null,$product_info);
        }
    }
    /**
    * 调用接口购买云空间
    * @date: 2016年11月29日 上午9:55:11
    * @author: Lyubo
    * @param: $order_id,$order_time,$product_info
    * @return: business_id
    */
    public function api_cloudspace_buy($order_id,$order_time = null,$product_info){
        $product = new \Frontend\Model\ProductModel();
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        try {
            $agent = new AgentAide();
             $transaction = $agent->servant;
            if (is_null ( $order_time )) {
                // 试用
                $result = $transaction->_try ( $product_info ['api_ptype'], $product_info ['api_name'] );
                // 插入日志记录
                api_log($order_info ['user_id'],"ptype:".$product_info ['api_ptype']."--pname:".$product_info ['api_name']."--tid:".GiantAPIParamsData::TID_TRY,$result,$order_info ['order_log']);
            } else {
                // 购买
                $result = $transaction->buy($product_info ['api_ptype'],$product_info ['api_name'],$order_time);
                // 插入日志记录
                api_log($order_info ['user_id'],"ptype:".$product_info ['api_ptype']."--pname:".$product_info ['api_name']."--tid:".GiantAPIParamsData::TID_BUY,$result,$order_info ['order_log']);
            }
        } catch ( \Exception $e ) {
            // 调用接口试用失败修改订单表
            $dataarray = array (
                "complete_time" => current_date (),
                "order_log" => $order_info ['order_log'] . "-接口调用失败",
                "state" => StateData::FAILURE_ORDER
            );
            $order->order_edit ( $order_id, $dataarray );
            api_log($order_info ['user_id'],"ptype:".$product_info ['api_ptype']."--pname:".$product_info ['api_name']."--tid:".GiantAPIParamsData::TID_TRY,$e->getMessage(),$order_info ['order_log']);
            return - 9;
        }
        $xstr = json_decode($result, true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            $zzidc_id = $xstr['info']['did'];
            if($order_info["free_trial"] == 0)
            {
                $dataarray = array (
                    "api_id" => $zzidc_id,
                    "complete_time" => current_date (),
                    "order_log" => $order_info ['order_log'] . "-成功",
                    "state" => StateData::PAYMENT_ORDER  // 订单状态已付款
                );
            }
            else
            {
                $dataarray = array (
                    "api_id" => $zzidc_id,
                    "complete_time" => current_date (),
                    "order_log" => $order_info ['order_log'] . "-成功",
                    "state" => StateData::EXAMINE_ORDER  // 订单状态审核中
                );
            }
            // 修改订单表
            if ($order->order_edit ( $order_id, $dataarray )) {
                return - 1;
            } else {
                $dataarray = array (
                    "api_id" => $zzidc_id,
                    "complete_time" => current_date (),
                    "order_log" => $order_info ['order_log'] . "-接口调用成功，订单表修改失败"
                );
                $order->order_edit ( $order_id, $dataarray );
                echo $order->getLastSql();
                return - 7; // 订单表修改失败
            }
        } else {
			// 调用接口试用失败修改订单表
			$dataarray = array (
					"complete_time" => current_date (),
					"order_log" => $order_info ['order_log'] . "-接口调用错误",
					"state" => StateData::FAILURE_ORDER
			);
			$order->order_edit ( $order_id, $dataarray );
			// 修改订单表
			return $xstr['code'];
		}
    }
    /**
    * 云空间开通
    * @date: 2016年11月29日 下午5:38:05
    * @author: Lyubo
    * @param: $order_id,$member_id
    * @return: business_code
    */
    public function cloudspace_open($order_id,$member_id){
        $order =  new \Frontend\Model\OrderModel();
        $product = $product = new \Frontend\Model\ProductModel();
        $order_info = $order->order_find($order_id);
        if (! $order_info || $member_id != $order_info ['user_id']) {
            return - 113; // 该订单不存在或不属于该会员
        }
        if (! $order_info) {
            $result_inf['code'] = - 13; // 订单不存在或订单状态错误
            return $result_inf;
        }
        if($order_info['state'] != StateData::PAYMENT_ORDER){
            if($order_info['state'] == StateData::SUCCESS_ORDER){
                $result_inf['code'] = - 124; // 该订单已经开通
                return $result_inf;
            }
            if($order_info['state'] == StateData::FAILURE_ORDER){
                $result_inf['code'] = - 125; // 订单开通失败
                return $result_inf;
            }
        }
        // 获取订单产品信息
        $product_info = $product->get_product ( $order_info ['product_id'] );
        if (! $product_info) {
            return - 2; // 产品获取失败
        }
        // 获取产品配置信息
        $product_config = $product->get_product_config ( $order_info ["product_id"] );
        if (is_null ( $product_config )) {
            return - 2;
        }
        $params = [];
        for($i = 0; $i < count ( $product_config ); $i ++) {
            $config_name = $product_config [$i] ['en_name'];
            $config_value = $product_config [$i] ['config_value'];
            $params ["$config_name"] = $config_value;
        }
        $arr = array (
            "user_id" => $member_id,
            "product_id" => $product_info ['id'],
            "create_time" => current_date (),
            "service_time" => $order_info ['order_time'],
            "state" => StateData::FAILURE,
            "free_trial" => $order_info ['free_trial'],
            "overdue_time" => current_date (),
            "open_time" => current_date (),
            "note_appended" => "会员编号:" . $member_id . "开通" . $product_info ['product_name'] . "订单编号：" . $order_id
        );
        $arr = array_merge ( $params, $arr );
        if (! $this->add($arr)) {
            return - 103; // 业务表插入失败
        } else {
            $cloudspace_id = $this->getLastInsID();
        }
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->open ( $product_info ['api_ptype'], $order_info ['api_id'] );
            // 插入日志记录
            api_log ( $member_id, "ptype:" . $product_info ['api_ptype'] . "--did:" . $order_info ['api_id'] . "--tid:" . GiantAPIParamsData::TID_OPEN, $result, $arr ['note_appended'] );
        } catch ( \Exception $e ) {
            $del_where['id'] =['eq',$cloudspace_id];
            $this->where($del_where)->delete();
            return - 9;
        }
        $xstr = json_decode($result, true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            $arr = array (
                "state" => StateData::SUCCESS_ORDER,
                "note_appended" => "会员编号:" . $member_id . "开通" . $product_info ['product_name'] . "订单编号：" . $order_id . "执行成功",
                "create_time" => current_date (),
                "business_id" => $xstr['info']['bid'],
                "ip_address" => $xstr['info']['ip'],
                "open_time" => $xstr['info']['createDate'],
                "overdue_time" => $xstr['info']['overDate']
            );
            $up_where['id'] = array(
                "eq",
                $cloudspace_id
            );
            // 修改云空间业务表
            if ($this->where($up_where)->save($arr)) {
                // 修改订单表订单标识，业务编号
                $arr = array (
                    "state" => StateData::SUCCESS_ORDER,
                    "business_id" => $xstr['info']['bid'],
                    "ip_address" => $xstr['info']['ip'],
                    "complete_time" => current_date ()
                );
                if ($order->order_edit ( $order_id, $arr )) {
                    return - 1; // 成功
                } else {
                    $up_where['id'] = ['eq',$cloudspace_id];
                    $arr ['note_appended'] = $arr ['note_appended'] . "业务开通成功，订单状态修改失败";
                    $this->where($up_where)->save($arr);
                    return - 7;
                }
            } else {
                $arr = array (
                    "state" => StateData::FAILURE_ORDER,
                    'order_log' => "业务开通成功，业务表修改失败"
                );
                $order->order_edit ( $order_id, $arr );
                return - 12; // 业务执行成功，业务表修改失败
            }
        }else{
            $del_where['id'] = ['eq',$cloudspace_id];
            $this->where($del_where)->delete();
            return $xstr['code'];
        }
    }
    /**
    * 转正展示
    * @date: 2016年11月30日 下午3:45:17
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id
    * @return: $cloudspace_info
    */
    public function show_cloudspace_onformal($cloudspace_id , $member_id){
        $product_service = new \Frontend\Model\ProductModel();
        $cloudspace_info = $this->cloudspace_info($cloudspace_id);
        $price_list = $product_service->get_product_price_list ( $cloudspace_info ['product_id'],StateData::STATE_BUY );
        $cloudspace_info ['price_list'] = $price_list;
        if(strtotime($cloudspace_info["overdue_time"]) < time()){
            return -126;
        }
        if (! $cloudspace_info || $cloudspace_info ['state'] != StateData::SUCCESS) {
            return - 11; // 业务不存在或已经过期
        }
        if ($cloudspace_info ['user_id'] != $member_id) {
            return - 12; // 业务不属于该会员
        }
        if ($cloudspace_info ['free_trial'] == 0) {
            return - 114; // 不是试用业务不能转正
        }
        return $cloudspace_info;
    }
    /**
    * 续费展示
    * @date: 2016年11月30日 下午4:25:29
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id
    * @return: $cloudspace_info
    */
    public function show_cloudspacd_renewals($cloudspace_id , $member_id){
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
        $where['id'] = ["eq",$cloudspace_id];
        $cloudspace_info = $this->where($where)->find();
        if (! $cloudspace_info || $cloudspace_info ['user_id'] != $member_id) {
            return - 101; // 云空间 业务信息获取失败
        }
        if ($cloudspace_info ['state'] != StateData::SUCCESS && $cloudspace_info ['state'] !=StateData::OVERDUE) {
            return - 10; // 业务失效
        }
        if ($cloudspace_info ['free_trial'] != 0) {
            return - 100; // 试用业务不能续费
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product ( $cloudspace_info ['product_id'] );
        if (! $product_info) {
            return - 2; // 产品信息获取失败
        }
        $cloudspace_info['product_name'] = $product_info['product_name'];
        // 计算续费价格（续费一年）
       return $order->get_renewals_info ( $cloudspace_info );
    }
    /**
    * 增值展示
    * @date: 2016年11月30日 下午5:13:11
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id
    * @return:
    */
    public function show_cloudspace_apperciation($cloudspace_id,$member_id){
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
       $cloudspace_info = $this->cloudspace_info($cloudspace_id);
        if(strtotime($cloudspace_info["overdue_time"]) < time()){
            return -126;
        }
        if (! $cloudspace_info || $cloudspace_info ['state'] != StateData::SUCCESS) {
            return - 10;
        }
        if ($cloudspace_info ['user_id'] != $member_id) {
            return - 101;
        }
        if ($cloudspace_info ['free_trial'] != 0) {
            return - 100;
        }
        // 产品信息
        $product_info = $product->get_product ( $cloudspace_info ['product_id'] );
        if (! $product_info) {
            return - 2;
        }
        // 业务剩余时间
        $surplus_time = app_month ( $cloudspace_info ['overdue_time'] );
        $where["month"] = $cloudspace_info ['service_time'];
        $where['type'] = StateData::STATE_BUY;
        $product_price = $product->get_product_price_buy_time ( $cloudspace_info ['product_id'],$where);
        
        // 获取已经增值的产品信息列表
        $apperction_info = $order->get_apperction_list ( $cloudspace_id, $product_info ['product_type_id'], '', true );
        // 获取增值产品信息
        $apperction_product = $product->app_product ( $product_info ['product_type_id'],$product_info ['system_type'] );
        for($i = 0; $i < count ( $apperction_product ); $i ++) {
            $where["month"] = 1;
            $where['type'] = StateData::STATE_BUY;
            $app_price = $product->get_product_price_buy_time( $apperction_product [$i] ['id'],$where);
            $apperction_product [$i] ['app_price'] = $surplus_time * $app_price ['product_price'];
        }
        $cloudspace_info ['surplus_time'] = $surplus_time;
        $cloudspace_info ['product_price'] = $product_price;
        $cloudspace_info ['apperction_info'] = $apperction_info;
        $cloudspace_info ['apperction_product'] = $apperction_product;
        $cloudspace_info ['overdue_day'] = overdue_day ( $cloudspace_info ['overdue_time'] );
        $cloudspace_info['product_name'] = $product_info['product_name'];
        return $cloudspace_info;
    }
    /**
    * 升级展示
    * @date: 2016年12月1日 下午5:27:07
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id
    * @return:
    */
    public function show_cloudspace_upgrage_info($cloudspace_id,$member_id){
        $product = new \Frontend\Model\ProductModel();
        $cloudspace_info = $this->cloudspace_info($cloudspace_id);
        if(strtotime($cloudspace_info["overdue_time"]) < time()){
            return -126;
        }
        if (! $cloudspace_info || $cloudspace_info ['state'] != StateData::SUCCESS) {
            return - 10;
        }
        if ($cloudspace_info ['user_id'] != $member_id) {
            return - 101;
        }
        if ($cloudspace_info ['free_trial'] != 0) {
            return - 100;
        }
        $cloudspace_info ["overdue_month"] = app_month ( $cloudspace_info ["overdue_time"] );
        $cloudspace_info ['month'] = $cloudspace_info ['service_time'];
        $params = $product->up_product_config_gap ( $cloudspace_info, $cloudspace_info ['overdue_month'] );
        $params ["cloudspace_info"] = $cloudspace_info;
        return $params;
    }
    /**
    * 云空间转正
    * @date: 2016年11月30日 下午3:49:38
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id,$service_time
    * @return: $business_code
    */
    public function cloudspace_onformal($cloudspace_id,$member_id,$service_time){
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
        $where['id'] = array("eq",$cloudspace_id);
        $cloudspace_info = $this->where($where)->find();
        if (! $cloudspace_info || $cloudspace_info ['state'] != StateData::SUCCESS) {
            return - 10; // 业务不存在或已经过期
        }
        if ($cloudspace_info ['user_id'] != $member_id) {
            return - 12; // 业务不属于该会员
        }
        if ($cloudspace_info ['free_trial'] == 0) {
            return - 114; // 不是试用业务不能转正
        }
        $product_info = $product->get_product ( $cloudspace_info ['product_id'] );
        if (! $product_info) {
            return - 2;
        }
        // 订单参数
        $arr = array (
            'user_id' => $member_id,
            'order_type' => StateData::ONFORMAL_ORDER, // 转正
            'ip_address' => $cloudspace_info ['ip_address'],
            'product_type' => $product_info ['product_type_id'],
            'product_id' => $product_info ['id'],
            'product_name' => $product_info ['product_name'],
            'free_trial' => 0,
            'order_time' => $service_time,
            'order_quantity' => 1,
            'business_id' => $cloudspace_info ['id']
        );
        // 插入订单
        $order_id = $order->order_do_entry ( $arr, $member_id, StateData::ONFORMAL_ORDER,$product_info,"");
        if ($order_id <= 0) {
            return $order_id;
        }
        $order_info = $order->order_find ( $order_id );
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->onformal ( GiantAPIParamsData::PTYPE_CLOUD_SPACE, $cloudspace_info ['business_id'], $service_time );
            // 日志记录
            api_log($member_id,"ptype:".$product_info['api_ptype']."--bid:".$cloudspace_info['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$result,$order_info['order_log']);
        } catch ( \Exception $e ) {
            // 修改订单表
            $arr = array (
                'complete_time' => current_date (),
                'state' => StateData::FAILURE_ORDER
            );
            $order->order_edit ( $order_id, $arr );
            api_log($member_id,"ptype:".$product_info['api_ptype']."--bid:".$cloudspace_info['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$e->getMessage(),$order_info['order_log']."接口调用失败");
            return - 9;
        }
        $xstr = json_decode($result, true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            // 修改云空间业务信息表
            $arr = array (
                'open_time' => current_date (),
                'overdue_time' => $xstr['info']['overDate'],
                'free_trial' => 0,
                'service_time' => $service_time
            );
            $up_where['id'] = array(
                "eq",
                $cloudspace_id
            );
            if ($this->where($up_where)->save($arr) === false) {
                return - 102;
            }
            // 修改订单表
            $arr = array (
                'complete_time' => current_date (),
                'state' => 1
            );
            if ($order->order_edit ( $order_id, $arr ) === false) {
                return - 104;
            }
            return - 1;
        } else {
			// 修改订单表
			$arr = array (
					'complete_time' => current_date (),
					'state' =>  StateData::FAILURE_ORDER
			);
			if ($order->order_edit ( $order_id, $arr ) === false) {
				return - 104;
			}
			return $xstr['code'];
		}
    }
    /**
    * 云空间续费
    * @date: 2016年11月30日 下午4:52:39
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id,$renewalstime
    * @return:
    */
    public function cloudspacd_renewals($cloudspace_id,$member_id,$renewalstime){
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
        $where['business_id'] = ["eq",$cloudspace_id];
        $cloudspace_info = $this->cloudspace_info($cloudspace_id);;
        if (! $cloudspace_info || $cloudspace_info ['user_id'] != $member_id) {
            return - 101; // 云空间 业务信息获取失败
        }
        if ($cloudspace_info ['state'] != StateData::SUCCESS && $cloudspace_info ['state'] !=StateData::OVERDUE) {
            return - 10; // 业务失效
        }
        if ($cloudspace_info ['free_trial'] != 0) {
            return - 100; // 试用业务不能续费
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product ( $cloudspace_info ['product_id'] );
        if (! $product_info) {
            return - 2; // 产品信息获取失败
        }
        // 封装订单表数据,生成订单
        $dataarray = array (
            "user_id" => $member_id,
            "order_type" => StateData::RENEWALS_ORDER, // 续费
            "state" => StateData::FAILURE_ORDER,
            "ip_address" => $cloudspace_info ['ip_address'],
            "product_type" => $product_info ['product_type_id'],
            "product_id" => $product_info ['id'],
            "product_name" => $product_info ['product_name'],
            "free_trial" => 0, // 不是试用
            "order_time" => $renewalstime, // 续费12个月
            "order_quantity" => 1,
            "business_id" => $cloudspace_info ['id'],
            "create_time" => current_date (),
            "login_name" => $cloudspace_info ['login_name'],
            "complete_time" => current_date ()
        );
        $order_id = $order->order_do_entry ( $dataarray, $member_id,StateData::RENEWALS_ORDER , $product_info,null );
        if ($order_id <= 0) {
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order->order_find($order_id );
        try {
             $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->renewals ( $product_info ['api_ptype'], $cloudspace_info ['business_id'], $renewalstime );
            api_log($member_id,"ptype:".$product_info['api_ptype']."--bid:".$cloudspace_info['business_id']."--tid:".GiantAPIParamsData::TID_RENEWALS."--yid:".$renewalstime,$result,"会员".$member_id."续费".$product_info['product_name']);
        } catch ( \Exception $e ) {
            $dataarray = array (
                "state" => StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date (),
                "business_id" => $cloudspace_info ['id'],
                "order_log" => $order_info ['order_log'] . "--接口调用失败"
            );
            $order->order_edit ( $order_id, $dataarray );
            api_log($member_id,"ptype:".$product_info['api_ptype']."--bid:".$cloudspace_info['business_id']."--tid:".GiantAPIParamsData::TID_RENEWALS."--yid:".$renewalstime,$e->getMessage(),"会员".$member_id."续费".$product_info['product_name']."接口调用失败");
            return - 9;
        }
        $xstr = json_decode($result,true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            // 续费成功，修改业务表信息
            $overdue_time = $cloudspace_info ['overdue_time'];
            $dataarray = array (
                "overdue_time" => add_dates($overdue_time,$renewalstime),
                'service_time' => $cloudspace_info ['service_time'] + $renewalstime
            );
            $up_where['id'] = ["eq",$cloudspace_info['id']];
            if ($this->where($up_where)->save($dataarray) !==false) {
                // 业务表修改成功，修改订单表
                $dataarray = array (
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date (),
                    "business_id" => $cloudspace_info ['id'],
                    "order_log" => $order_info ['order_log'] . "--续费成功"
                );
                $order->order_edit ( $order_id, $dataarray );
                return - 1; // 续费成功
            } else {
                // 业务表修改失败，修改订单表
                $dataarray = array (
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date (),
                    "business_id" => $cloudspace_info ['id'],
                    "order_log" => $order_info ['order_log'] . "--续费成功,业务表修改失败"
                );
                $order->order_edit ( $order_id, $dataarray );
                return - 12; // 续费成功，业务表修改失败
            }
        }else{
            // 获取订单详情
            $order_info = $order->order_find ( $order_id );
            $dataarray = array (
                "state" => StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date (),
                "business_id" => $cloudspace_info ['id'],
                "order_log" => $order_info ['order_log'] . "--接口调用失败--" . api_code ( $xstr['code'] )
            );
            $order->order_edit ( $order_id, $dataarray );
            return $xstr['code'];
        }
    }
    /**
    * 云空间增值
    * @date: 2016年11月30日 下午5:33:19
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id,$app_name,$app_size
    * @return: $business_code
    */
    public function cloudspace_apperciation($cloudspace_id,$memeber_id,$app_name,$app_size){
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
        $cloudspace_info =$this->cloudspace_info($cloudspace_id);
       if (! $cloudspace_info || $cloudspace_info ['user_id'] != $memeber_id) {
			return - 101; // 云空间 业务信息获取失败
		}
		if ($cloudspace_info ['state'] != StateData::SUCCESS) {
			return - 10; // 业务失效
		}
		if ($cloudspace_info ['free_trial'] != 0) {
			return - 100; // 试用业务不能续费
		}
		$product_info = $product->get_appreciation_product ( $app_name,$cloudspace_info['product_type_id'] );
		if (! $product_info) {
			return - 2; // 增值产品信息获取失败
		}
		// 获取增值配置信息表
		$product_config_info = $product->get_app_product_config ( $product_info ['id'] );
		if (! $product_config_info) {
			return - 16; // 增值配置信息获取失败
		}
		$app_num = $app_size;
		if (strcmp ( $app_name, GiantAPIParamsData::PNAME_CLOUD_SPACE_ZENG_ZHI_KONG_JIAN ) == 0) {
		    // 增值空间容量
		    $app_size = $app_size * $product_config_info ['config_value'];
		    if ($app_size < 500 || $app_size % 500 != 0) {
		        return - 116; // 增值空间容量必须是500的倍数
		    }
		} elseif (strcmp ( $app_name,GiantAPIParamsData::PNAME_CLOUD_SPACE_ZENG_ZHI_ZHAN_DIAN ) == 0) {
		    $app_size = $app_size * $product_config_info ['config_value'];
		    // 增加子站点个数
		    if ($app_size < 0 || ! is_numeric ( $app_size ) || $app_size % $product_config_info ['config_value'] != 0) {
		        return - 108;
		    } else {
		        $quantity = $app_size;
		    }
		} else {
		    return - 110;
		}
		// 订单表参数
		$dataarray = array (
		    "user_id" => $memeber_id,
		    "product_id" => $product_info ['id'],
		    "order_type" => StateData::APP_ORDER,
		    "state" => StateData::FAILURE_ORDER,
		    "ip_address" => $cloudspace_info ['ip_address'],
		    "product_type" => $product_info ['product_type_id'],
		    "product_name" => $product_info ['product_name'],
		    "free_trial" => 0,
		    "order_time" => app_month ( $cloudspace_info ['overdue_time'] ),
		    "order_quantity" => $app_num,
		    "note_appended" => "会员" . $memeber_id . "增值" . $cloudspace_info ['product_name'] . $product_info ['product_name'] . app_month ( $cloudspace_info ['overdue_time'] ) . "个月",
		    "business_id" => $cloudspace_info ['id'],
		    "create_time" => current_date (),
		    "login_name" => $cloudspace_info ['login_name']
		);
		// 插入订单表
		$order_id = $order->order_do_entry ( $dataarray, $memeber_id, StateData::APP_ORDER , $product_info,null);
		if ($order_id <= 0) {
		    return $order_id;
		}
		try {
		    $agent = new AgentAide();
            $transaction = $agent->servant;
		    $result = $transaction->appreciation ( $product_info ['api_ptype'], $product_info ['api_name'], $cloudspace_info ['business_id'], $app_size );
		    // 日志记录
		    api_log($memeber_id,"ptype:".$product_info['api_ptype']."--pname:".$product_info['api_name']."--bid:".$cloudspace_info['business_id']."--tid:".GiantAPIParamsData::TID_APPRECIATION."--size:".$app_size,$result,$dataarray["note_appended"]);
		}catch(\Exception $e) {
		    $order->order_edit ( $order_id, array (
		        "state" => StateData::FAILURE_ORDER,
		        "complete_time" => current_date ()
		    ) );
            api_log($memeber_id,"ptype:".$product_info['api_ptype']."--pname:".$product_info['api_name']."--bid:".$cloudspace_info['business_id']."--tid:".GiantAPIParamsData::TID_APPRECIATION."--size:".$app_size,$e->getMessage(),$dataarray["note_appended"]."接口调用失败");
            return - 9;
		}
		$xstr = json_decode($result,true);
		if($xstr['code'] == 0 && $xstr['code'] !== null){
		    // 增值信息表插入记录
		    $result = add_appreciations ( $cloudspace_id, $product_info ['id'], $app_size, $product_info ['product_type_id'],$cloudspace_info ['ip_address']);
		    if (! $result) {
		        return - 111; // 增值记录表记录失败
		    }
		    switch ($app_name) {
		        case GiantAPIParamsData::PNAME_CLOUD_SPACE_ZENG_ZHI_KONG_JIAN :
		            $kj['site_quantity'] = $cloudspace_info ['space_capacity'] + $app_size;
		            $up_where['business_id'] = ["eq",$cloudspace_id];
		            $this->where($up_where)->save($kj);
		            break;
		        case GiantAPIParamsData::PNAME_CLOUD_SPACE_ZENG_ZHI_SUB_SITE :
		            $sub['site_quantity'] =  $cloudspace_info ['site_quantity'] +  $app_size;
		            $up_where['business_id'] = ["eq",$cloudspace_id];
		            $this->where($up_where)->save($sub);
		    }
		    // 增值成功
		    $order->order_edit ( $order_id, array (
		        "state" => StateData::SUCCESS_ORDER,
		        "complete_time" => current_date ()
		    ) );
		    return - 1;
		}else{
		    // 增值失败
		    $order->order_edit ( $order_id, array (
		        "state" => StateData::FAILURE_ORDER,
		        "complete_time" => current_date ()
		    ) );
		    return $xstr['code'];
		}
    }
    /**
    * 云空间升级
    * @date: 2016年12月1日 下午5:32:44
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id,$up_product_id
    * @return: boolean
    */
    public function cloudspace_upgrade($cloudspace_id,$member_id,$up_product_id){
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
         $cloudspace_info = $this->cloudspace_info($cloudspace_id);
        if (! $cloudspace_info || $cloudspace_info ['user_id'] != $member_id) {
            return - 101; // 云空间 业务信息获取失败
        }
        if ($cloudspace_info ['state'] != StateData::SUCCESS) {
            return - 10; // 业务失效
        }
        if ($cloudspace_info ['free_trial'] != 0) {
            return - 100; // 试用业务不能升级
        }
        $product_info = $product->get_product ( $up_product_id );
        $product_type_info = $product->get_product_type_info($product_info['product_type_id']);
        if (! $product_info) {
            return - 2;
        }
        // 封装订单表参数
        $dataarray = array (
            'user_id' => $member_id,
            'product_id' => $product_info ['id'],
            'order_type' => StateData::CHANGE_ORDER, // 定单类型：0新增、1增值、2续费,3变更方案,转正
            'state' => StateData::FAILURE_ORDER,
            'ip_address' => $cloudspace_info ['ip_address'],
            'product_type' => $product_info ['product_type_id'],
            'product_name' => $product_info ['product_name'],
            'free_trial' => StateData::STATE_BUY,
            'order_time' => app_month ( $cloudspace_info ['overdue_time'] ),
            'order_quantity' => 1,
            'business_id' => $cloudspace_info ['id'],
            'create_time' => current_date (),
            'login_name' => $cloudspace_info ['login_name'],
            'order_log' => '会员' . $member_id . $cloudspace_info ['product_name'] . '主机',
            "note_appended" => "会员".$member_id."升级".$cloudspace_info['product_name']."至".$product_info ['product_name']
        );
        $product_type_info["old_product_id"] = $cloudspace_info["product_id"];
        $order_id = $order->order_do_entry ( $dataarray, $member_id, $dataarray ['order_type'], $product_info, $product_type_info);
        if ($order_id <= 0) {
            return $order_id;
        }
        $order_info = $order->order_find ( $order_id );
        try {
             $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->upgrade ( $product_info ['api_ptype'], $product_info ['api_name'], $cloudspace_info ['business_id'],null );
            // 插入日志记录
            api_log($member_id,"ptype:".$product_info ['api_ptype']."--bid:".$cloudspace_info ['business_id']."--tid:".GiantAPIParamsData::TID_UPGRADE,$result,$dataarray['note_appended']);
        } catch ( \Exception $e ) {
            // 升级失败
            $datarray = array (
                'state' => StateData::FAILURE_ORDER,
                'order_log' => $order_info ['order_log'] . '业务升级失败,接口连接失败',
                'complete_time' => current_date ()
            );
            $order->order_edit ( $order_id, $datarray );
            api_log($member_id,"ptype:".$product_info ['api_ptype']."--bid:".$cloudspace_info ['business_id']."--tid:".GiantAPIParamsData::TID_UPGRADE,$e->getMessage(),$dataarray['note_appended']."接口调用失败");
            return - 9;
        }
        $xstr = json_decode($result,true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            // 升级成功修改业务表
            $datarray = array (
                'product_id' => $product_info ['id'],
                'note_appended' => $cloudspace_info ['note_appended'] . '||' . current_date () . '成功升级到' . $product_info ['product_name']
            );
            // 获取新主机产品信息
            $product_config = $product->get_product_config( $up_product_id );
            // 获取原主机产品配置信息
            $old_product_config = $product->get_product_config ( $cloudspace_info ['product_id'] );
            // 计算两种产品配置差
            $poor_arr = array ();
            for($i = 0; $i < count ( $product_config ); $i ++) {
                $col_name = $product_config [$i] ["en_name"];
                for($j = 0; $j < count ( $old_product_config ); $j ++) {
                    if (strcmp ( $col_name, $old_product_config [$j] ['en_name'] ) == 0) {
                        $poor_arr ["$col_name"] = $product_config [$i] ['config_value'] - $old_product_config [$j] ['config_value'];
                    }
                }
            }
            // 更新新产品配置信息到业务表
            for($i = 0; $i < count ( $product_config ); $i ++) {
                $col_name = $product_config [$i] ["en_name"];
                // 计算升级后产品配置信息(原业务表配置记录+新老产品配置差)
                $datarray ["$col_name"] = $cloudspace_info ["$col_name"] + $poor_arr ["$col_name"];
            }
            $up_where['id'] = $cloudspace_info['id'];
            if ($this->where($up_where)->save($datarray)) {
                $datarray = array (
                    'state' => StateData::SUCCESS_ORDER,
                    'order_log' => $order_info ['order_log'] . '业务升级成功',
                    'complete_time' => current_date ()
                );
                $order->order_edit ( $order_id, $datarray );
                return - 1;
            } else {
                $datarray = array (
                    'state' => StateData::SUCCESS_ORDER,
                    'order_log' => $order_info ['order_log'] . '业务升级成功,业务信息修改失败',
                    'complete_time' => current_date ()
                );
                $order->order_edit ( $order_id, $datarray );
                return - 102;
            }
        }else{
            // 升级失败
            $datarray = array (
                'state' => StateData::FAILURE_ORDER,
                'order_log' => $order_info ['order_log'] . '业务升级失败,错误描述' . business_code ( $xstr['code'] ),
                'complete_time' => current_date ()
            );
            $order->order_edit ( $order_id, $datarray );
            return - 112;
        }
    }
}