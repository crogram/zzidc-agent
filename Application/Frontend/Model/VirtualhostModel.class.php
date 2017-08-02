<?php
namespace Frontend\Model;
use Frontend\Model\BusinessModel;
use Common\Data\StateData;
use Common\Data\GiantAPIParamsData;
use Common\Aide\AgentAide;
class VirtualhostModel extends BusinessModel{
    protected $trueTableName = 'agent_virtualhost_business';

     public  $internalPtype = [
			0 => GiantAPIParamsData::PTYPE_HOST,
			1 => GiantAPIParamsData::PTYPE_HK_HOST,
			2 => GiantAPIParamsData::PTYPE_USA_HOST,
			3 => GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL,
			4 => GiantAPIParamsData::PTYPE_DEDE_HOST,
	];
    public function conver_par(){
        $date =request();
        //获取主机类型
        if(empty($date["virtual_type"])){
            $where["bs.virtual_type"] = array("in","0,4");
        }else{
            $where["bs.virtual_type"] = array("eq",$date["virtual_type"]);
        }
        $where["bs.user_id"] = array("eq",session("user_id"));
        if(!empty($date['select'])){
            $select = clearXSS($date['select']);
            $map['bs.business_id'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map['bs.ip_address'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map["bs.beizhu"] = array("like" ,"%$select%");
            $map['_logic'] = 'OR';
            $map["bs.domain_name"] = array("like" ,"%$select%");
            $map['_logic'] = 'OR';
            $map["bs.bindDomain"] = array("like" ,"%$select%");
            $map['_logic'] = 'OR';
            $map["bs.product_name"] = array("like" ,"%$select%");
            $where['_complex'] = $map;
        }
        return $where;
    }
    /**
     * 购买/试用虚拟主机
     * @date: 2016年11月10日 下午6:33:53
     * @author: Lyubo
     * @param: $buy_info，购买参数
     * @param: $product_info,产品信息
     * @return:
     */
    public function  virtualhost_buy($buy_info,$product_info,$product_type_info){
        // 封装订单参数信息
        $datarray = array (
            "product_id" => $buy_info ['product_id'],
            "user_id" => $buy_info ['member_id'],
            "order_quantity" => 1,
            "free_trial" => $buy_info ['free_trial'], // 购买或者试用
            "order_time" => $buy_info ['free_trial'] == 1 ? 0 : $buy_info ['order_time'],
            "create_time" => current_date(),
            "complete_time" => current_date(), // 完成时间
            "system_type"=>$buy_info['system_type']//操作系统
        );
        //执行录入订单、购买方法
        $order =  new \Frontend\Model\OrderModel();
        $order_id = $order->order_do_entry ( $datarray, $buy_info ['member_id'], StateData::NEW_ORDER , $product_info ,$product_type_info);
        if ($order_id < 0) {//小于0就是错误返回信息
            return $order_id;
        }
        return $this->api_virtualhost_buy ( $order_id ,$product_info );
    }
    /**
     * 虚拟主机试用/购买接口调用
     * @date: 2016年11月11日 下午2:54:24
     * @author: Lyubo
     * @param: $order_id,$product_info
     * @return: business_code
     */
    public function api_virtualhost_buy($order_id ,$product_info){
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $order_info['system_type'] == '1' ? $czxt='windows' : $czxt='linux';
            if($order_info['free_trial'] == StateData::STATE_BUY){//购买
                if(strpos ( $product_info['api_ptype'], GiantAPIParamsData::PTYPE_DEDE_HOST ) !==false){
                    if (strpos ( $product_info ['api_name'], 'hk' ) !== false) {
                        // 购买香港织梦主机
                        $areacode = 4002;
                        $result = $transaction->buy ( GiantAPIParamsData::PTYPE_DEDE_HOST, $product_info ['api_name'], $order_info ['order_time']);
                        api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_DEDE_HOST . "--pname:" . $product_info ['api_name'] . "--yid:" . $order_info ['order_time'] . "--tid:" . GiantAPIParamsData::TID_BUY, $result );
                    } elseif (strpos ( $product_info ['api_name'], 'cn' ) !== false) {
                        // 购买国内织梦主机
                        $areacode = 4001;
                        $result = $transaction->buy ( GiantAPIParamsData::PTYPE_DEDE_HOST, $product_info ['api_name'], $order_info ['order_time'] );
                        api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_DEDE_HOST . "--pname:" . $product_info ['api_name'] . "--yid:" . $order_info ['order_time'] . "--tid:" . GiantAPIParamsData::TID_BUY, $result );
                    } else {
                        return - 2; // 产品信息获取失败
                    }
                }else if(strpos ( $product_info['api_ptype'], GiantAPIParamsData::PTYPE_USA_HOST ) !==false){
                    //购买美国主机
                    $areacode = 4005;
                    $result = $transaction->buy ( GiantAPIParamsData::PTYPE_USA_HOST, $product_info ['api_name'], $order_info ['order_time'],'',$order_info['system_type'] );
                    api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_USA_HOST . "--pname:" . $product_info ['api_name'] ."--操作系统:" .$czxt."--yid:" . $order_info ['order_time'] . "--tid:" . GiantAPIParamsData::TID_BUY, $result );
                }else if(strpos ( $product_info['api_ptype'], GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL ) !==false){
                    //购买云虚拟主机
                    $areacode = 4008;
                    $result = $transaction->buy ( GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL , $product_info ['api_name'], $order_info ['order_time'],$areacode,$order_info['system_type'] );
                    api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL . "--pname:" . $product_info ['api_name']."--操作系统:" .$czxt . "--yid:" . $order_info ['order_time'] . "--tid:" . GiantAPIParamsData::TID_BUY, $result );
                }else{
                    if (strpos ( $product_info ['api_name'], 'hk' ) !== false) {
                        // 购买香港虚拟主机
                        $areacode = 4002;
                        $result = $transaction->buy ( GiantAPIParamsData::PTYPE_HK_HOST, $product_info ['api_name'], $order_info ['order_time'], $areacode,$order_info['system_type']);
                        api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_HK_HOST . "--pname:" . $product_info ['api_name'] . "--yid:" ."--操作系统:" .$czxt  . $order_info ['order_time'] . "--tid:" . GiantAPIParamsData::TID_BUY, $result );
                    } elseif (strpos ( $product_info ['api_name'], 'hk' ) == 0) {
                        // 购买国内虚拟主机
                        $areacode = 4001;
                        $result = $transaction->buy ( GiantAPIParamsData::PTYPE_HOST, $product_info ['api_name'], $order_info ['order_time'],$areacode,$order_info['system_type'] );
                        api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_HOST . "--pname:" . $product_info ['api_name'] ."--操作系统:" .$czxt  . "--yid:" . $order_info ['order_time'] . "--tid:" . GiantAPIParamsData::TID_BUY, $result );
                    } else {
                        return - 2; // 产品信息获取失败
                    }
                }
            }else{//试用
                if(strpos ( $product_info['api_ptype'], GiantAPIParamsData::PTYPE_DEDE_HOST ) !==false){
                    if (strpos ( $product_info ['api_name'], 'hk' ) !== false) {
                        // 试用香港织梦主机
                        $areacode = 4002;
                        $result = $transaction->_try ( GiantAPIParamsData::PTYPE_DEDE_HOST, $product_info ['api_name'] );
                        api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_DEDE_HOST . "--pname:" . $product_info ['api_name'] . "--tid:" . GiantAPIParamsData::TID_TRY, $result );
                    } elseif (strpos ( $product_info ['api_name'], 'cn' ) !== false) {
                        // 试用国内织梦主机
                        $areacode = 4001;
                        $result = $transaction->_try ( GiantAPIParamsData::PTYPE_DEDE_HOST, $product_info ['api_name']);
                        api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_DEDE_HOST . "--pname:" . $product_info ['api_name'] . "--tid:" . GiantAPIParamsData::TID_TRY, $result );
                    } else {
                        return - 2; // 产品信息获取失败
                    }
                }else if(strpos ( $product_info['api_ptype'], GiantAPIParamsData::PTYPE_USA_HOST ) !==false){
                    //试用美国主机
                    $areacode = 4005;
                    $result = $transaction->_try ( GiantAPIParamsData::PTYPE_USA_HOST, $product_info ['api_name'],$areacode,$order_info['system_type']);
                    api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_USA_HOST . "--pname:" . $product_info ['api_name'] . "--tid:" . GiantAPIParamsData::TID_TRY, $result );
                }else if(strpos ( $product_info['api_ptype'], GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL ) !==false){
                    //云虚拟主机
                    $areacode = 4008;
                    $result = $transaction->_try ( GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL, $product_info ['api_name'],$areacode,$order_info['system_type']);
                    api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL . "--pname:" . $product_info ['api_name'] . "--tid:" . GiantAPIParamsData::TID_TRY, $result );
                }else{
                    if (strpos ( $product_info ['api_name'], 'hk' ) !== false) {
                        // 试用香港虚拟主机
                        $areacode = 4002;
                        $result = $transaction->_try ( GiantAPIParamsData::PTYPE_HK_HOST, $product_info ['api_name'], $areacode,$order_info['system_type']);
                        api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_HK_HOST . "--pname:" . $product_info ['api_name'] . "--tid:" . GiantAPIParamsData::TID_TRY, $result );
                    } elseif (strpos ( $product_info ['api_name'], 'hk' ) == 0) {
                        // 试用国内虚拟主机
                        $areacode = 4001;
                        $result = $transaction->_try ( GiantAPIParamsData::PTYPE_HOST, $product_info ['api_name'],$areacode,$order_info['system_type'] );
                        api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_HOST . "--pname:" . $product_info ['api_name'] . "--tid:" . GiantAPIParamsData::TID_TRY, $result );
                    } else {
                        return - 2; // 产品信息获取失败
                    }
                }
            }
        } catch (\Exception $e) {
            // 调用接口试用失败修改订单表
            $dataarray = array (
                "complete_time" =>  current_date(),
                "order_log" => $order_info ['order_log'] . "-接口调用错误",
                "area_code" => $areacode,
                "state" => StateData::FAILURE_ORDER  // 订单状态失败
            );
            $order->order_edit ( $order_id, $dataarray );
            api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_HOST . "--pname:" . $product_info ['api_name'] . "--tid:" . GiantAPIParamsData::TID_TRY,$e->getMessage());
            return - 9;//接口调用失败
        }
        $xstr = json_decode($result, true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            // 调用接口购买/试用成功
            $zzidc_id = $xstr['info']['did'];
            $czxt = $xstr['info']['czxt'];
            if($order_info["free_trial"] == 0)
            {
                $dataarray = array (
                    "api_id" => $zzidc_id,
                    "complete_time" => current_date(),
                    "area_code" => $areacode,
                    "system_type" => $czxt,
                    "state" => StateData::PAYMENT_ORDER  // 订单状态已付款
                );
            }
            else
            {
                //这里通过site_reviewed判断试用主机是否需要审核
                $site_config = WebSiteConfig();//获取网站配置
                if($site_config['site_reviewed'] == 'no'){
                    $dataarray = array (
                        "api_id" => $zzidc_id,
                        "complete_time" => current_date(),
                        "area_code" => $areacode,
                        "system_type" => $czxt,
                        "state" => StateData::PAYMENT_ORDER  // 订单状态已付款
                    );
                }else{
                    $dataarray = array (
                        "api_id" => $zzidc_id,
                        "complete_time" => current_date(),
                        "area_code" => $areacode,
                        "system_type" => $czxt,
                        "state" => StateData::EXAMINE_ORDER  // 订单状态审核中
                    );
                }
            }
            if (! $order->order_edit ( $order_id, $dataarray )) {
                return - 7; // 订单表修改失败
            } else {
                return - 1; // 购买成功
            }
        } else {
            // 调用接口购买/试用失败
            // 调用接口试用失败修改订单表
            $dataarray = array (
                "complete_time" =>  current_date(),
                "order_log" => $order_info ['order_log'] . "-接口调用错误",
                "area_code" => $areacode,
                "state" => StateData::FAILURE_ORDER  // 订单状态失败
            );
            $order->order_edit ( $order_id, $dataarray );
            return $xstr['code'];
        }
    }
    /**
     * 虚拟主机开通
     * @date: 2016年11月18日 下午3:57:57
     * @author: Lyubo
     * @param: $order_id,$member_id
     * @return: business_code
     */
    public function virtualhost_open($order_id,$member_id,$type){
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
        $result_inf = [];
        $order_info = $order->order_find($order_id);
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
        if ($order_info['user_id'] != $member_id) {
            $result_inf['code'] = - 101; // 业务不存在或不属于该会员
            return $result_inf;
        }
        $product_info = $product->get_product($order_info['product_id']); // 获取订单产品信息
        if (! $product_info) {
            $result_inf['code'] = - 2; // 产品获取失败
            return $result_inf;
        }
        $business_info = array(
            "user_id" => $member_id,
            "product_id" => $product_info['id'],
            "product_name" => $product_info['product_name'],
            "create_time" => current_date(),
            "service_time" => $order_info['order_time'],
            "state" => StateData::FAILURE,
            "free_trial" => $order_info['free_trial'],
            "login_name" => $order_info['login_name'],
            "area_code" => $order_info['area_code'],
            "note_appended" => "会员编号:" . $member_id . "开通" . $product_info['product_name'] . "订单编号：" . $order_id,
            "virtual_type" => $type,
            "system_type" => $order_info['system_type']
        );
        if (! $this->add($business_info)) {
            $result_inf['code'] = - 103; // 业务表插入失败
            return $result_inf;
        } else {
            $business_id = $this->getLastInsID(); // 获取业务ID
        }
        $agent = new AgentAide();
        $transaction = $agent->servant;
        if (strpos($product_info['api_name'], 'host.mf.I') !== false || strpos($product_info['api_name'], 'host.qiye.windows.I') !== false || strpos($product_info['api_name'], 'host.qiye.linux.I') !== false) {
            try { // 免费主机
                $result = $transaction->open_qy($product_info['api_ptype'], $order_info['api_id']);
                api_log($member_id, "ptype:" . $product_info['api_ptype'] . "--did:" . $order_info['api_id'] . "--tid:" . GiantAPIParamsData::TID_OPEN, $result, $business_info['note_appended']);
            } catch (\Exception $e) {
                $del_where['id'] = array(
                    'eq',
                    $business_id
                );
                $this->where($del_where)->delete(); // 开通失败删除失败业务表记录
                api_log($member_id, "ptype:" . $product_info['api_ptype'] . "--did:" . $order_info['api_id'] . "--tid:" . GiantAPIParamsData::TID_OPEN, $e->getMessage(), $business_info['note_appended']);
                $result_inf['code'] = - 9;
                return $result_inf;
            }
        } else {
            try {
                $result = $transaction->open($product_info['api_ptype'], $order_info['api_id'], $order_info['system_type']);
                api_log($member_id, "ptype:" . $product_info['api_ptype'] . "--did:" . $order_info['api_id'] . "--tid:" . GiantAPIParamsData::TID_OPEN, $result, $business_info['note_appended']);
            } catch (\Exception $e) {
                $del_where['id'] = array(
                    'eq',
                    $business_id
                );
                $this->where($del_where)->delete(); // 开通失败删除失败业务表记录
                api_log($member_id, "ptype:" . $product_info['api_ptype'] . "--did:" . $order_info['api_id'] . "--tid:" . GiantAPIParamsData::TID_OPEN, $e->getMessage(), $business_info['note_appended']);
                $result_inf['code'] = - 9;
                return $result_inf;
            }
        }
        $xstr = json_decode($result, true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) { // 开通成功
            $arr = array(
                "state" => StateData::SUCCESS_ORDER,
                "note_appended" => "会员编号:" . $member_id . "开通" . $product_info['product_name'] . "订单编号：" . $order_id . "执行成功",
                "business_id" => $xstr['info']['bid'],
                "domain_name" => $xstr['info']['identify'],
                "ftp_password" => $this->GetFtpPwd($xstr['info']['bid']),
                "open_time" => $xstr['info']['createDate'],
                "overdue_time" => $xstr['info']['overDate'],
                'ip_address' => $xstr['info']['ip'],
                'system_type' => $order_info['system_type']
            );
            $up_where['id'] = array("eq",$business_id);
            if ($this->where($up_where)->save($arr)) { // 修改业务表
                // 修改订单表订单标识，业务编号
                $arr = array(
                    "business_id" => $xstr['info']['bid'],
                    "complete_time" => current_date(),
                    "state" => StateData::SUCCESS_ORDER,
                    'ip_address' => $xstr['info']['ip'],
                    'system_type' => $order_info['system_type']
                );
                //此位置放同步
                if ($order->order_edit($order_id, $arr)) {
                    $result_inf['code'] = "-1"; // return - 1; // 成功
                    $result_inf['order_id'] = $order_id; // return - 1; // 成功
                    $result_inf['domain_name'] = $xstr['info']['identify'];
                    $result_inf['ip_address'] = $xstr['info']['ip'];
                    $result_inf['ftp_password'] = $this->GetFtpPwd($xstr['info']['bid']);
                    $result_inf['open_time'] = $xstr['info']['createDate'];
                    $result_inf['overdue_time'] = $xstr['info']['overDate'];
                    $result_inf['threedomain'] = $xstr['info']['threedomain'];
                    $result_inf['product_name'] = $product_info['product_name'];
                    $result_inf['ftp_address'] = $xstr['info']['ftp_address'];
                    $result_inf['product_name'] = $product_info['product_name'];
                    return $result_inf;
                } else {
                    $result_inf['code'] = - 104; // 订单表修改失败
                    return $result_inf;
                }
            }
        }elseif($xstr['code'] == 3050){
            // 主机开通异常
            $order->order_edit ( $order_id, array (
                "state" => StateData::HANDLE_ORDER,
                "order_log" => "业务开通异常，1--3分钟后到处理订单列表中获取业务信息。"
            ) );
            $del_where['id'] = array('eq' , $business_id);
            $this->where($del_where)->delete();
            $result_inf['code'] = $xstr['code'];
            return $result_inf;
        } else {
            $del_where['id'] = array('eq' , $business_id);
            $this->where($del_where)->delete();
            $result_inf['code'] = $xstr['code'];
            return $result_inf;
        }
    }

    /**
     * 获取主机详细信息
     * @author: Guopeng
     * @param: $id 业务ID
     * @return mixed
     */
    public function virtualhost_info($id)
    {
        $field =
            "vb.id,vb.user_id,vb.business_id,vb.product_id,p.product_type_id,p.product_name,
            vb.ip_address,vb.domain_name,vb.bindDomain,vb.ftp_password,vb.create_time,vb.open_time,
            vb.overdue_time,vb.state,vb.service_time,vb.free_trial,vb.area_code,vb.login_name,
            vb.virtual_type,vb.system_type,vb.note_appended,p.api_name";
        $where = array("vb.id" => $id);
        return $this->alias('vb')->field($field)->where($where)
                    ->join('JOIN '.C('DB_PREFIX').'product as p on vb.product_id=p.id')->find();
    }

    /**
     * 主机续费业务
     * @author: Guopeng
     * @param: $user_id 用户id
     * @param: $yw_id 业务id
     * @param: $order_time 续费时间(月)
     * @param: null $method 显示/业务
     * @return array|int
     */
    public function virtualhost_renewals($user_id,$yw_id,$order_time,$method = null)
    {
        $product = new \Frontend\Model\ProductModel();
        $order = new \Frontend\Model\OrderModel();
        // 获取业务信息
        $virtualhost_info = $this->virtualhost_info($yw_id);
        if($virtualhost_info ['user_id'] != $user_id)
        {
            return -101; // 业务不属于该会员或不存在
        }
        if(!$virtualhost_info || $virtualhost_info ['state'] != 1 && $virtualhost_info ['state'] != 3)
        {
            return -10; // 获取不到业务，或业务状态错误
        }
        if($virtualhost_info ['free_trial'] != 0)
        {
            return -100; // 试用业务不能执行此操作
        }
        if($virtualhost_info['api_name'] == 'host.mf.I')
        {
            //根据到期时间和开通时间来判断服务期限
            $now_time = strtotime(current_date());
            $nowtime = $virtualhost_info['overdue_time'];
            $over_time = strtotime("$nowtime-1 year");
            //到期时间减去一年大于当前时间不能续费
            if($over_time > $now_time)
            {
                return -3046;
            }
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product($virtualhost_info ['product_id']);
        $product_type_info = $product->get_product_type_info($product_info['product_type_id']);
        if(!$product_info)
        {
            return -2; // 产品信息获取失败
        }
        if(!is_null($method) && $method == 'get')
        {
            // 获取续费信息
            return $order->get_renewals_info($virtualhost_info);
        }
        $order_where["order_type"] = 2;
        $order_where["business_id"] = $yw_id;
        $ole_order_info = $order->where($order_where)->order("create_time desc")->find();
        if($ole_order_info){
            if(strtotime($ole_order_info["create_time"]) > strtotime("-5 minute")){
                return -121; // 续费限制5分钟之内不得重复续费
            }
        }
        // 生成订单表
        // 封装订单表数据
        $dataarray = array (
            "user_id" => $user_id,
            "order_type" => StateData::RENEWALS_ORDER, // 续费
            "state" => StateData::FAILURE_ORDER,
            "ip_address" => $virtualhost_info ['ip_address'],
            "product_type" => $product_info ['product_type_id'],
            "product_id" => $product_info ['id'],
            "product_name" => $product_info ['product_name'],
            "free_trial" => $virtualhost_info['free_trial'], // 不是试用
            "order_time" => $order_time, // 续费12个月
            "order_quantity" => 1,
            "business_id" => $virtualhost_info ['id'],
            "create_time" => current_date(),
            "area_code" => $virtualhost_info["area_code"], // 4001国内4002香港
            "login_name" => $virtualhost_info ['login_name'],
            "complete_time" => current_date(),
            "system_type"=>$virtualhost_info["system_type"],
            'order_log' => '会员'.$user_id."续费".$virtualhost_info['product_name'].'主机'
        );
        $order_id = $order->order_do_entry($dataarray,$user_id,$dataarray["order_type"],$product_info,$product_type_info);
        if($order_id <= 0)
        {
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order->order_find($order_id);
        // 调用zzidc接口
        try
        {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->renewals($product_info ['api_ptype'],$virtualhost_info ['business_id'],$order_time);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$virtualhost_info['business_id']."--time:".$order_time,$result,$order_info['order_log']);
        }catch(\Exception $e)
        {
            // 调用接口试用失败修改订单表
            $dataarray = array(
                "complete_time" => current_date(),
                "order_log" => $order_info['order_log']."--接口调用失败",
                "state" => StateData::FAILURE_ORDER); // 订单状态失败
            $order->order_edit($order_id,$dataarray);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$virtualhost_info['business_id']."--time:".$order_time,$e->getMessage(),$order_info['order_log']."--接口调用失败");
            return -9;//接口调用失败
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null)
        {
            // 续费成功，修改业务表信息
            $overdue_time = $virtualhost_info['overdue_time'];
            if(add_dates($overdue_time,$order_time) > $virtualhost_info['create_time'])
            {
                $datarray = array (
                    "state" => StateData::SUCCESS,
                    'note_appended' => $virtualhost_info['note_appended'].'||'.current_date().'成功续费'.$order_time.'个月',
                    "overdue_time" => add_dates ( $overdue_time, $order_time ),
                    'service_time' => $virtualhost_info ['service_time'] + $order_time
                );
            }
            else
            {
                $datarray = array (
                    'note_appended' => $virtualhost_info['note_appended'].'||'.current_date().'成功续费'.$order_time.'个月',
                    "overdue_time" => add_dates ( $overdue_time, $order_time ),
                    'service_time' => $virtualhost_info ['service_time'] + $order_time
                );
            }
            if($this->business_edit($virtualhost_info['id'],$datarray))
            {
                // 业务表修改成功，修改订单表
                $datarray = array (
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date(),
                    'note_appended' => '会员'.$user_id.'|'.current_date().'成功续费'.$order_time.'个月',
                    "order_log" => $order_info['order_log'] . "--续费成功"
                );
                $order->order_edit($order_id,$datarray);
                return -1; // 续费成功
            }
            else
            {
                // 业务表修改失败，修改订单表
                $datarray = array (
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date(),
                    "order_log" => $order_info ['order_log'] . "--续费成功,业务表修改失败"
                );
                $order->order_edit($order_id,$datarray);
                return -102; // 续费成功，业务表修改失败
            }
        }
        else
        {
            $datarray = array (
                "state" => StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date(),
                "order_log" => $order_info['order_log'].'--业务续费失败--接口调用错误'
            );
            $order->order_edit($order_id,$datarray);
            return $xstr['code'];
        }
    }

    /**
     * 虚拟主机升级
     * @author: Guopeng
     * @param: $user_id: 会员编号
     * @param: $yw_id: 业务编号
     * @param: $up_product_id 要升级到的产品id
     * @param: $type 业务类型
     * @param: null $method 显示/业务
     * @return int
     */
    public function virtualhost_uplevel($user_id,$yw_id,$up_product_id,$type,$method = null)
    {
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        $virtualhost_info = $this->virtualhost_info($yw_id);
        if(!$virtualhost_info || $virtualhost_info['state'] != StateData::SUCCESS)
        {
            return -10; // 业务信息不存在或业务状态错误
        }
        if ($virtualhost_info ['free_trial'] != 0) {
            return - 100; // 试用业务不能升级
        }
        if($virtualhost_info['user_id'] != $user_id)
        {
            return -101; // 当前业务不属于该会员
        }
        if(!is_null($method) && strcmp($method,'get') == 0)
        {
            $virtualhost_info ["overdue_month"] = app_month($virtualhost_info ["overdue_time"]);
            $virtualhost_info ["month"] = $virtualhost_info ["service_time"];
            $params = $product_service->up_product_config_gap($virtualhost_info,$virtualhost_info['overdue_month']);
            $params ["virtualhost_info"] = $virtualhost_info;
            return $params;
        }
        $product_info = $product_service->get_product($up_product_id);
        $product_type_info = $product_service->get_product_type_info($product_info['product_type_id']);
        if(!$product_info)
        {
            return -37; // 升级产品信息获取失败
        }else if($type == 0 && $product_info ["api_ptype"] == GiantAPIParamsData::PTYPE_HOST)
        {}else if($type == 1 && $product_info ["api_ptype"] == GiantAPIParamsData::PTYPE_HK_HOST)
        {}else if($type == 2 && $product_info ["api_ptype"] == GiantAPIParamsData::PTYPE_USA_HOST)
        {}else if($type == 3 && $product_info ["api_ptype"] == GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL)
        {}else if($type == 0 && $product_info ["api_ptype"] == GiantAPIParamsData::PTYPE_DEDE_HOST)
        {}else
        {
            return -38;
        }
        // 封装订单表参数
        $dataarray = array (
            'user_id' => $user_id,
            'order_type' => StateData::CHANGE_ORDER, // 变更方案即升级
            'state' => StateData::FAILURE_ORDER,
            'ip_address' => $virtualhost_info ['ip_address'],
            'product_type' => $product_info ['product_type_id'],
            'product_id' => $product_info ['id'],
            'product_name' => $product_info ['product_name'],
            'free_trial' => $virtualhost_info['free_trial'],
            'order_time' => app_month ( $virtualhost_info ['overdue_time'] ),
            'order_quantity' => 1,
            'charge' => $product_info ['price'] * app_month ( $virtualhost_info ['overdue_time'] ),
            'business_id' => $virtualhost_info ['id'],
            'create_time' => current_date(),
            'area_code' => $virtualhost_info ['area_code'],
            'login_name' => $virtualhost_info ['login_name'],
            "system_type"=>$virtualhost_info["system_type"],
            'order_log' => '会员'.$user_id."升级".$virtualhost_info['product_name'].'主机'
        );
        $product_type_info["old_product_id"] = $virtualhost_info["product_id"];
        $order_id = $order_service->order_do_entry($dataarray,$user_id,$dataarray['order_type'],$product_info,$product_type_info);
        if($order_id <= 0)
        {
            return $order_id;
        }
        $order_info = $order_service->order_find($order_id);
        // 调用zzidc接口
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->upgrade($product_info['api_ptype'],$product_info['api_name'],$virtualhost_info['business_id'],$virtualhost_info ['system_type']);
            // 插入日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$virtualhost_info['business_id']."--tid:".GiantAPIParamsData::TID_UPGRADE,$result,$order_info['order_log']);
        }catch(\Exception $e)
        {
            // 调用接口试用失败修改订单表
            $datarray = array(
                "complete_time"=>current_date(),
                "order_log"=>$order_info['order_log']."--接口调用失败",
                "state" => StateData::FAILURE_ORDER); // 订单状态失败
            $order_service->order_edit($order_id,$datarray);
            // 插入日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$virtualhost_info['business_id']."--tid:".GiantAPIParamsData::TID_UPGRADE,$e->getMessage(),$order_info['order_log']."--接口调用失败");
            return -9;//接口调用失败
        }
        $xstr = json_decode($result,true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            // 升级成功
            // 修改主机业务表
            $datarray = array (
                'product_id' => $product_info['id'],
                'product_name' => $product_info['product_name'],
                'note_appended' => $virtualhost_info['note_appended'].'||'.current_date().'成功升级到'.$product_info['product_name'].'主机',
            );
            if ($this->business_edit($virtualhost_info['id'],$datarray)){
                $datarray = array (
                    'state' =>StateData::SUCCESS_ORDER,
                    'note_appended' => '会员'.$user_id.'|'.current_date().'成功升级到'.$product_info['product_name'],
                    'order_log' => $order_info['order_log'].'--业务升级成功',
                    'complete_time' => current_date(),
                );
                $order_service->order_edit ($order_id,$datarray );
                return - 1;
            } else {
                $datarray = array (
                    'state' => StateData::SUCCESS_ORDER,
                    'order_log' => $order_info['order_log'] . '--业务升级成功,业务信息修改失败',
                    'complete_time' =>current_date()
                );
                $order_service->order_edit($order_id,$datarray );
                return - 102;
            }
        } else {
            // 升级失败
            $datarray = array (
                'state' =>StateData::FAILURE_ORDER,
                'order_log' => $order_info['order_log'].'--业务升级失败--接口调用错误',
                'complete_time' =>current_date()
            );
            $order_service->order_edit($order_id,$datarray);
            return $xstr['code'];
        }
    }

    /**
     * 主机增值业务
     * @author: Guopeng
     * @param $user_id: 用户编号
     * @param $yw_id: 主机业务编号
     * @param $appreciation_name: 增值名称
     * @param $appreciation_size: 增值大小
     * @param null $method get显示/null业务
     * @return int
     */
    public function virtualhost_appreciation($user_id,$yw_id,$appreciation_name,$appreciation_size=1,$method = null) {
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        $virtualhost_info = $this->virtualhost_info ($yw_id);
        if (! $virtualhost_info || $virtualhost_info ['user_id'] != $user_id) {
            return - 101;
        }
        if ($virtualhost_info ['state'] != StateData::SUCCESS) {
            return - 10;
        }
        if ($virtualhost_info ['free_trial'] != 0) {
            return - 100;
        }
        if(!is_null($method) && $method == 'get')
        {
            // 获取增值信息
            // 产品信息
            $product_info = $product_service->get_product( $virtualhost_info ['product_id'] );
            if (! $product_info) {
                return - 2;
            }
            // 业务剩余时间
            $time=$time1=strtotime($virtualhost_info['overdue_time'])-$time2=strtotime($virtualhost_info['open_time']);
            $surplus_time = floor($time/3600/24/30);
            $price_data["month"] = $virtualhost_info['service_time'];
            $product_price = $product_service->get_product_price_buy_time($virtualhost_info['product_id'],$price_data["month"]);
            // 获取已经增值的产品信息列表
            $apperction_info = $order_service->get_apperction_list($virtualhost_info['business_id'],$product_info['product_type_id']);
            // 获取增值产品信息
            $apperction_product = $product_service->app_product ( $product_info ['product_type_id'], $product_info ['system_type']);
            for($i = 0; $i < count ( $apperction_product ); $i ++) {
                $price_data["month"] = 1;
                $app_price = $product_service->get_product_price_buy_time($apperction_product [$i]['id'],$price_data["month"]);
                $apperction_product [$i] ['app_price'] = $surplus_time * $app_price ['product_price'];
            }
            foreach ($apperction_product as $key => $val) {
                if ($virtualhost_info['system_type'] == '0' && $val['api_name'] == 'zengzhi.beifen') {
                    $position = strripos($product_info['api_name'], '.');
                    $ptype = substr($product_info['api_name'], 0, $position);
                    unset($apperction_product[$key]);
                }elseif($virtualhost_info['system_type'] == '1' && $val['api_name'] == 'zengzhi.liuliang'){
                    $position = strripos($product_info['api_name'], '.');
                    $ptype = substr($product_info['api_name'], 0, $position);
                    unset($apperction_product[$key]);
                }
            }
            $virtualhost_info['surplus_time'] = $surplus_time;
            $virtualhost_info['product_price'] = $product_price;
            $virtualhost_info['apperction_info'] = $apperction_info;
            $virtualhost_info['apperction_product'] = $apperction_product;
            $virtualhost_info['overdue_day'] = overdue_day($virtualhost_info['overdue_time']);
            $virtualhost_info['product_info']=$product_info;
            return $virtualhost_info;
        }
        // 增值产品信息
        $product_info = $product_service->get_appreciation_product($appreciation_name, $virtualhost_info ['product_type_id'] );
        if (! $product_info) {
            return - 2;
        }
        // 获取增值配置信息表
        $product_config_info = $product_service->get_app_product_config ( $product_info ['id'] );
        if (! $product_config_info) {
            return - 2; // 增值配置信息获取失败
        }
        $quantity = $appreciation_size * $product_config_info ['config_value'];
        if($appreciation_name == 'zengzhi.kongjian'){
            // 增值空间
            if ($quantity % $product_config_info ['config_value'] != 0 || $quantity < 0) {
                return - 105;
            }
        }elseif ($appreciation_name == 'zengzhi.liuliang'){
            // 增加流量
            if ($quantity % $product_config_info ['config_value'] != 0 || $quantity < 0) {
                return - 107;
            }
        }elseif ($appreciation_name == 'zengzhi.ip'){
            // 增加IP
            if ($quantity < 0 || ! is_numeric ( $quantity ) || $quantity % $product_config_info ['config_value'] != 0) {
                return - 106;
            }
        } elseif ($appreciation_name == 'zengzhi.subsite') {
            // 增加子站点个数
            if ($quantity < 0 || !is_numeric($quantity) || $quantity % $product_config_info ['config_value'] != 0) {
                return - 108;
            } else {
                $quantity = $appreciation_size;
            }
        } elseif ($appreciation_name == 'zengzhi.beifen') {
            // 增加定时备份个数
            if ($quantity < 0 || !is_numeric($quantity) || $quantity % $product_config_info ['config_value'] != 0) {
                return - 116;
            } else {
                $quantity = $appreciation_size;
            }
        } else {
            return - 110;
        }
        // 订单表参数
        $dataarray = array (
            "user_id" => $user_id,
            "product_id" => $product_info ['id'],
            "state" => StateData::FAILURE_ORDER,
            'order_type' => StateData::APP_ORDER, // 增值
            "product_type" => $product_info ['product_type_id'],
            "product_name" => $product_info ['product_name'],
            "free_trial" => $virtualhost_info['free_trial'],
            "order_time" => app_month ( $virtualhost_info ['overdue_time'] ),
            "order_quantity" => $appreciation_size,
            "business_id" => $virtualhost_info ['id'],
            "create_time" => current_date(),
            "area_code" => $virtualhost_info["area_code"], // 4001国内4002香港
            "login_name" => $virtualhost_info ['login_name'],
            "system_type"=>$virtualhost_info["system_type"],
            "order_log" => "会员".$user_id."增值".$virtualhost_info['product_name'].'-'.$product_info['product_name'].app_month($virtualhost_info['overdue_time'])."个月",
            'ip_address' => $virtualhost_info['ip_address']
        );
        // 插入订单表
        $order_id = $order_service->order_do_entry($dataarray,$user_id,$dataarray['order_type'],$product_info,$virtualhost_info["product_id"]);
        if ($order_id <= 0) {
            return $order_id;
        }
        $order_info = $order_service->order_find( $order_id );
        // 调用zzidc接口
        try {            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->appreciation ( $product_info ['api_ptype'], $product_info ['api_name'], $virtualhost_info ['business_id'], $quantity );
            // 日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--pname:".$product_info['api_name']."--bid:".$virtualhost_info['business_id']."--tid:".GiantAPIParamsData::TID_APPRECIATION."--size:".$appreciation_size,$result,$order_info['order_log']);
        } catch (\Exception $e) {
            // 调用接口试用失败修改订单表
            $dataarray = array(
                "complete_time" => current_date(),
                "order_log"=>$order_info['order_log']."--接口调用失败",
                "state" => StateData::FAILURE_ORDER // 订单状态失败
            );
            $order_service->order_edit($order_id,$dataarray);
            // 插入日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--pname:".$product_info['api_name']."--bid:".$virtualhost_info['business_id']."--tid:".GiantAPIParamsData::TID_APPRECIATION."--size:".$appreciation_size,$e->getMessage(),$order_info['order_log']."--接口调用失败");
            return -9;//接口调用失败
        }
        $xstr = json_decode($result,true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null)
        {
            $ip_address = $xstr['info'];
            if($ip_address)
            {
                $ip_address = '无';
            }
            $result = add_appreciations($virtualhost_info['business_id'],$product_info['id'],$quantity,$product_info['product_type_id'],$ip_address);
            if (! $result) {
                $datarray = array(
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date(),
                    "order_log" => $order_info['order_log']."--增值信息表插入记录失败"
                );
                $order_service->order_edit($order_id,$datarray);
                return - 111; // 增值记录表记录失败
            }
            // 增值成功
            $datarray = array(
                "complete_time" => current_date(),
                "state" => StateData::SUCCESS_ORDER,
                "note_appended" => "会员".$user_id."成功增值".$virtualhost_info['product_name'].'-'.$product_info['product_name'].app_month($virtualhost_info['overdue_time'])."个月",
                "order_log"=>$order_info["order_log"]."--增值成功"
            );
            $order_service->order_edit($order_id,$datarray);
            if($ip_address == '无')
            {
                $datarray = array(
                    "ip_address"=> $ip_address,
                    "note_appended" => $virtualhost_info['note_appended'].'||'.current_date()."成功增值".$virtualhost_info['product_name'].'-'.$product_info['product_name'].app_month($virtualhost_info['overdue_time'])."个月",
                );
                if(!$this->business_edit($virtualhost_info['id'],$datarray)){
                    return - 12;
                };
            }
            return - 1;
        } else {
            // 增值失败
            $datarray = array(
                "complete_time" => current_date(),
                "state" => StateData::FAILURE_ORDER,
                "order_log"=>$order_info["order_log"]."--增值失败-接口调用错误"
            );
            $order_service->order_edit($order_id,$datarray);
            return $xstr['code'];
        }
    }

    /**
     * 虚拟主机转正
     * @author: Guopeng
     * @param: $user_id 会员编号
     * @param: $yw_id 业务编号
     * @param: null $service_time 转正时间
     * @param: null $method 是否是get提交
     * @return int|\SimpleXMLElement[]
     */
    public function virtualhost_onformal($user_id, $yw_id, $service_time = null, $method = null)
    {
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        $virtualhost_info = $this->virtualhost_zhuanzheng_info($yw_id);
        if(!$virtualhost_info || $virtualhost_info['state'] != StateData::SUCCESS)
        {
            return -10;
        }
        if($virtualhost_info ['user_id'] != $user_id)
        {
            return -101;
        }
        if($virtualhost_info['free_trial'] != 1)
        {
            return -114; // 不是试用业务不能转正
        }
        if(!is_null($method) && $method == 'get')
        {
            return $virtualhost_info;
        }
        $product_info = $product_service->get_product($virtualhost_info['product_id']);
        // 订单参数
        $arr = array(
            'user_id' => $user_id,
            'order_type' => StateData::ONFORMAL_ORDER, // 转正
            'state' => StateData::FAILURE_ORDER,
            'ip_address' => $virtualhost_info['ip_address'],
            'product_type' => $product_info['product_type_id'],
            'product_id' => $product_info['id'],
            'product_name' => $product_info['product_name'],
            'free_trial' => 0,
            'order_time' => $service_time,
            'order_quantity' => 1,
            'business_id' => $virtualhost_info['id'],
            'create_time' => current_date(),
            "area_code" =>$virtualhost_info["area_code"], // 4001国内4002香港
            'login_name' =>$virtualhost_info['login_name'],
            "system_type"=>$virtualhost_info["system_type"]
        );
        // 插入订单
        $order_id = $order_service->order_do_entry($arr,$user_id,$arr['order_type'],$product_info,$virtualhost_info["product_id"]);
        if($order_id <= 0)
        {
            return $order_id;
        }
        $order_info = $order_service->order_find($order_id);
        // 调用api接口增值
        $agent = new AgentAide();
        $transaction = $agent->servant;
        if($virtualhost_info['virtual_type'] == 0)
        {
            try
            {
                $result = $transaction->onformal($product_info['api_ptype'],$virtualhost_info['business_id'],$service_time);
                // 日志记录
                api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$virtualhost_info['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$result,$order_info['order_log']);
            }catch(\Exception $e)
            {
                $arr = array(
                    "complete_time" => current_date(),
                    "order_log"=>$order_info['order_log']."--接口调用失败",
                    "state" => StateData::FAILURE_ORDER // 订单状态失败
                );
                $order_service->order_edit($order_id,$arr);
                // 日志记录
                api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$virtualhost_info['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$e->getMessage(),$order_info['order_log']."-接口调用失败");
                return -9;
            }
        }
        elseif($virtualhost_info['virtual_type'] == 1)
        {
            try
            {
                $result = $transaction->onformal($product_info['api_ptype'],$virtualhost_info['business_id'],$service_time);
                // 日志记录
                api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$virtualhost_info['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$result,$order_info['order_log']);
            }catch(\Exception $e)
            {
                $arr = array(
                    "complete_time" => current_date(),
                    "order_log"=>$order_info['order_log']."--接口调用失败",
                    "state" => StateData::FAILURE_ORDER // 订单状态失败
                );
                $order_service->order_edit($order_id,$arr);
                // 日志记录
                api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$virtualhost_info['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$e->getMessage(),$order_info['order_log']."-接口调用失败");
                return -9;
            }
        }
        elseif($virtualhost_info ['virtual_type'] == 2)
        {
            try
            {
                $result = $transaction->onformal($product_info ['api_ptype'],$virtualhost_info ['business_id'],$service_time);
                // 日志记录
                api_log($user_id,"ptype:".$product_info ['api_ptype']."--bid:".$virtualhost_info ['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$result,$order_info ['order_log']."-接口调用失败");
            }catch(\Exception $e)
            {
                $arr = array(
                    "complete_time" => current_date(),
                    "order_log"=>$order_info['order_log']."--接口调用失败",
                    "state" => StateData::FAILURE_ORDER // 订单状态失败
                );
                $order_service->order_edit($order_id,$arr);
                // 日志记录
                api_log($user_id,"ptype:".$product_info ['api_ptype']."--bid:".$virtualhost_info ['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$e->getMessage(),$order_info ['order_log']."-接口调用失败");
                return -9;
            }
        }
        elseif ($virtualhost_info['virtual_type'] == 3)
        {
            try
            {
                $result = $transaction->onformal($product_info ['api_ptype'],$virtualhost_info ['business_id'],$service_time);
                // 日志记录
                api_log($user_id,"ptype:".$product_info ['api_ptype']."--bid:".$virtualhost_info ['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$result,$order_info ['order_log']."-接口调用失败");
            }catch(\Exception $e)
            {
                $arr = array(
                    "complete_time" => current_date(),
                    "order_log"=>$order_info['order_log']."--接口调用失败",
                    "state" => StateData::FAILURE_ORDER // 订单状态失败
                );
                $order_service->order_edit($order_id,$arr);
                // 日志记录
                api_log($user_id,"ptype:".$product_info ['api_ptype']."--bid:".$virtualhost_info ['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$e->getMessage(),$order_info ['order_log']."-接口调用失败");
                return -9;
            }
        }elseif ($virtualhost_info['virtual_type'] == 4)
        {
            try
            {
                $result = $transaction->onformal($product_info ['api_ptype'],$virtualhost_info ['business_id'],$service_time);
                // 日志记录
                api_log($user_id,"ptype:".$product_info ['api_ptype']."--bid:".$virtualhost_info ['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$result,$order_info ['order_log']."-接口调用失败");
            }catch(\Exception $e)
            {
                $arr = array(
                    "complete_time" => current_date(),
                    "order_log"=>$order_info['order_log']."--接口调用失败",
                    "state" => StateData::FAILURE_ORDER // 订单状态失败
                );
                $order_service->order_edit($order_id,$arr);
                // 日志记录
                api_log($user_id,"ptype:".$product_info ['api_ptype']."--bid:".$virtualhost_info ['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$e->getMessage(),$order_info ['order_log']."-接口调用失败");
                return -9;
            }
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null)
        {
            // 修改虚拟主机业务信息表
            $arr = array(
                "state" => StateData::SUCCESS,
                'note_appended' => '||'.current_date().'成功转正'.$service_time.'个月',
                'open_time' => $xstr['info']['createDate'],
                'overdue_time' => $xstr['info']['overDate'],
                'free_trial' => 0,
                'service_time' => $service_time
            );
            if(!$this->business_edit($yw_id,$arr))
            {
                return -102;
            }
            // 修改订单表
            $arr = array(
                "state" => StateData::SUCCESS_ORDER,
                "complete_time" => current_date(),
                'note_appended' => '会员'.$user_id.'|'.current_date().'成功转正'.$service_time.'个月',
                "order_log" => $order_info['order_log']."--转正成功"
            );
            if(!$order_service->order_edit($order_id,$arr))
            {
                return -104;
            }
            return -1;
        }
        else
        {
            // 修改订单表
            $arr = array(
                "state" => StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date(),
                "order_log" => $order_info['order_log'].'--业务转正失败--接口调用错误'
            );
            if(!$order_service->order_edit($order_id,$arr))
            {
                return -104;
            }
            return $xstr['code'];
        }
    }
    /**
     * 获取虚拟主机详细信息
     * @author:Guopeng
     * @param: $id 业务编号
     * @return int|mixed
     */
    public function virtualhost_zhuanzheng_info($id)
    {
        $product_service = new \Frontend\Model\ProductModel();

        // 获取业务信息
        $virtualhost_info = $this->virtualhost_info($id);
        // 是否为前台用户且为删除状态
        if($virtualhost_info['state'] == 2)
        {
            return -10;
        }
        $product_info = $product_service->get_product($virtualhost_info ['product_id']);
        // 获取产品配置信息
        $product_config = $product_service->get_product_config($virtualhost_info['product_id']);
        $price_list = $product_service->get_product_price_list($virtualhost_info['product_id'],StateData::STATE_BUY);
        $p_config = array();
        // 获取该业务的所有增值信息
        $appreciation_list = $product_service->business_appreciation_info($virtualhost_info['id'],$product_info['product_type_id']);
        if(!$appreciation_list)
        {
            // 如果不存在增值信息
            // 将多维数组转换为一维数组，$product_config的config_key为新数组的key$product_config的config_value和联合组成新数组的value
            for($i = 0;$i < count($product_config);$i++)
            {
                $key = $product_config[$i]['en_name'];
                $p_config ["$key"] = $product_config[$i]['config_value'];
            }
        }
        else
        {
            for($i = 0;$i < count($appreciation_list);$i++)
            {
                // 获取增值产品编号
                $product_id = $appreciation_list[$i]['app_product_id'];
                // 获取增值产品信息
                $appreciation_info = $product_service->get_app_product_config($product_id);
                if($appreciation_info['app_name'] == 'zengzhi.kongjian')
                {
                    // 增值虚拟主机空间容量
                    // 将多维数组转换为一维数组，$product_config的config_key为新数组的key$product_config的config_value和联合组成新数组的value
                    for($j = 0;$j < count($product_config);$j++)
                    {
                        $key = $product_config [$j] ['en_name'];
                        if($key == 'space_capacity')
                        {
                            $p_config ["$key"] = ($product_config [$j] ['config_value'] += $appreciation_list [$i] ['quanity']);
                        }
                        else
                        {
                            $p_config ["$key"] = $product_config [$j] ['config_value'];
                        }
                    }
                }
                else if($appreciation_info ['app_name'] == 'zengzhi.liuliang')
                {
                    for($j = 0;$j < count($product_config);$j++)
                    {
                        $key = $product_config [$j] ['en_name'];
                        if($key == 'month_flow_rate')
                        {
                            $month_flow_rate = ceil($appreciation_list [$i] ['quanity'] / 1024);
                            $p_config ["$key"] = ($product_config [$j] ['config_value'] += $month_flow_rate);
                        }
                        else
                        {
                            $p_config ["$key"] = $product_config [$j] ['config_value'];
                        }
                    }
                }
            }
        }
        foreach($p_config as $p_key=>$p_v)
        {
            $vps_info[$p_key] = $p_v;
        }
        $virtualhost_info['api_ptype'] = $product_info['api_ptype'];
        $virtualhost_info['price_list'] = $price_list;
        return $virtualhost_info;
    }
    /**
    * 验证域名是否备案
    * @date: 2017年1月11日 下午3:32:17
    * @author: Lyubo
    * @param: $GLOBALS
    * @return:
    */
    public function proving($up_product_id,$yw_id){
        //先验证是否是国内主机
        $product_service = new \Frontend\Model\ProductModel();
        $product_info = $product_service->get_product($up_product_id);
        $where["id"] = ["eq",$yw_id];
        $where["user_id"] = ["eq",session("user_id")];
        $virtualhost_info = $this->where($where)->find();
        $api_name = $product_info['api_name'];
        if(strcmp($api_name, 'host.gr.B') == 0 || strcmp($api_name, 'host.qy.B') == 0) {
            try {
                $agent = new AgentAide();
                $transaction = $agent->servant;
                $result = $transaction->proving(GiantAPIParamsData::PTYPE_SELF,$virtualhost_info['business_id']);
                // 日志记录
                api_log(session("user_id"), "ptype:" . GiantAPIParamsData::PTYPE_SELF . "--bid:" . $virtualhost_info['business_id'] . "--tid:" . GiantAPIParamsData::TID_PROVING_TYPE , $result, "验证域名备案成功");
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                api_log(session("user_id"),  "ptype:" . GiantAPIParamsData::PTYPE_SELF ."--bid:" . $virtualhost_info['business_id'] . "--tid:" . GiantAPIParamsData::TID_PROVING_TYPE , $result, $msg);
                return -9;
            }
            $xstr = json_decode($result,true);
            if($xstr['code'] == 0 && $xstr['code'] !== null){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
    
    /**
    * 虚拟主机获取FTP密码
    * @date: 2017年3月3日 上午9:25:17
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function GetFtpPwd($business_id){
        $agent = new AgentAide();
        $transaction = $agent->servant;
        $result = $transaction->GetFtpPwd($business_id);
        $xstr = json_decode($result,true);
        api_log(session("user_id"),  "ptype:" . GiantAPIParamsData::PTYPE_SELF ."--bid:" . $business_id . "--tid:" . GiantAPIParamsData::TID_VHOST_FTPPWD , $result, '获取主机密码');
        if($xstr['code'] == '0'){
            return $xstr['info']['ftpPassword'];
        }else{
            return '请联系管理员并提供业务编号获取FTP密码';
        }
    }
}