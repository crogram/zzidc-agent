<?php
namespace Frontend\Model;
use Common\Aide\AgentAide;
use Common\Data\GiantAPIParamsData;
use Common\Data\StateData;
use Frontend\Model\BusinessModel;

class FastvpsModel extends BusinessModel{
    protected $trueTableName = 'agent_fast_vps_business';
   
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
     * 购买/试用fastvps
     * @date: 2016年11月18日
     * @author: Guopeng
     * @param: $buy_info，购买参数
     * @param: $product_info,产品信息
     * @return:
     */
    public function fastvps_buy($buy_info, $product_info, $product_type_info)
    {
        // 操作系统0:windows2003;1:linux;2:windows2008
        if ($buy_info['system_type'] == 'Windows2003') {
            $buy_info['system_type'] = 0;
        } else if ($buy_info['system_type'] == 'CentOS 6.7') {
                $buy_info['system_type'] = 1;
        } else if($buy_info['system_type'] == 'Windows2008'){
                $buy_info['system_type'] = 2;
            }
        // 封装订单参数信息
        $dataarray = array(
            "product_id" => $buy_info['product_id'],
            "user_id" => $buy_info['member_id'],
            "order_quantity" => 1,
            "free_trial" => $buy_info['free_trial'], // 购买或者试用
            "order_time" => $buy_info['free_trial'] == 1 ? 0 : $buy_info['order_time'],
            "create_time" => date('Y-m-d H:i:s'),
            "complete_time" => date('Y-m-d H:i:s'), // 完成时间
            "system_type" => $buy_info['system_type']//操作系统
        );
        //执行录入订单、购买方法
        $order = new \Frontend\Model\OrderModel();
        $order_id = $order->order_do_entry($dataarray,$buy_info ['member_id'],StateData::NEW_ORDER,$product_info,$product_type_info);
        if($order_id < 0)
        {//小于0就是错误返回信息
            return $order_id;
        }
        return $this->api_fastvps_buy($order_id,$product_info);
    }
    /**
     * 快云vps试用/购买接口调用
     * @date: 2016年11月18日
     * @author: Lyubo
     * @param: $order_id,$product_info
     * @return: business_code
     */
    public function api_fastvps_buy($order_id,$product_info)
    {
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        $system = WebSiteConfig();
        $usetype = $system["site_buy_vps"];
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $czxt = $order_info['system_type'];
            $areacode = $product_info["area_code"];
            if ($order_info['free_trial'] == StateData::STATE_BUY) { // 购买
                if (strpos($product_info['api_ptype'], GiantAPIParamsData::PTYPE_FAST_CLOUDVPS) !== false) {
                    if (strpos($product_info['api_name'], 'fast') !== false) {
                        // 购买快云VPS
                        $result = $transaction->buy(GiantAPIParamsData::PTYPE_FAST_CLOUDVPS, $product_info['api_name'], $order_info['order_time'], $areacode, $czxt,$usetype);
                        api_log($order_info['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_FAST_CLOUDVPS . "--pname:" . $product_info['api_name'] . "--操作系统:" . $czxt . "--yid:" . $order_info['order_time'] . "--tid:" . GiantAPIParamsData::TID_BUY, $result,$order_info["order_log"]);
                    } else {
                        return - 2; // 产品信息获取失败
                    }
                }
            } else { // 试用
                if (strpos($product_info['api_ptype'], GiantAPIParamsData::PTYPE_FAST_CLOUDVPS) !== false) {
                    if (strpos($product_info['api_name'], 'fast') !== false) {
                        // 试用快云VPS
                        $result = $transaction->_try(GiantAPIParamsData::PTYPE_FAST_CLOUDVPS, $product_info['api_name'], $areacode, $czxt);
                        api_log($order_info['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_FAST_CLOUDVPS . "--pname:" . $product_info['api_name'] . "--操作系统:" . $czxt . "--yid:" . $order_info['order_time'] . "--tid:" . GiantAPIParamsData::TID_TRY, $result,$order_info["order_log"]);
                    } else {
                        return - 2; // 产品信息获取失败
                    }
                }
            }
        } catch (\Exception $e) {
            // 调用接口试用失败修改订单表
            $dataarray = array(
                "complete_time" => date('Y-m-d H:i:s'),
                "order_log" => $order_info['order_log'] . "-接口调用错误",
                "area_code" => $areacode,
                "state" => StateData::FAILURE_ORDER // 订单状态失败
            );
            api_log($order_info['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_FAST_CLOUDVPS . "--pname:" . $product_info['api_name'] . "--操作系统:" . $czxt . "--yid:" . $order_info['order_time'] . "--tid:" . GiantAPIParamsData::TID_BUY,$e->getMessage(),$order_info["order_log"]."--接口调用失败");
            $order->order_edit($order_id, $dataarray);
            return - 9; // 接口调用失败
        }
        $xstr = json_decode($result, true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            // 调用接口购买/试用成功
            $zzidc_id = $xstr['info']['did'];
            if($order_info["free_trial"] == 0)
            {
                $dataarray = array(
                    "api_id" => $zzidc_id,
                    "complete_time" => current_date(),
                    "area_code" => $areacode,
                    "system_type" => $czxt,
                    "state" => StateData::PAYMENT_ORDER  // 订单状态已付款
                );
            }
            else
            {
                $dataarray = array(
                    "api_id" => $zzidc_id,
                    "complete_time" => current_date(),
                    "area_code" => $areacode,
                    "system_type" => $czxt,
                    "state" => StateData::EXAMINE_ORDER  // 订单状态审核中
                );
            }
            if (! $order->order_edit($order_id, $dataarray)) {
                return - 7; // 订单表修改失败
            } else {
                return - 1; // 购买成功
            }
        } else {
            // 调用接口购买/试用失败
            // 调用接口试用失败修改订单表
            $dataarray = array(
                "complete_time" => date('Y-m-d H:i:s'),
                "order_log" => $order_info['order_log'] . "-接口调用错误",
                "area_code" => $areacode,
                "state" => StateData::FAILURE_ORDER // 订单状态失败
            );
            $order->order_edit($order_id, $dataarray);
            return $xstr['code'];
        }
    }

    /**
     * 快云VPS订单开通
     * @date: 2016年11月19日 下午5:23:30
     * 
     * @author : Lyubo
     * @param : $order_id,$member_id            
     * @return :
     */
    public function fastvps_open($order_id,$member_id)
    {
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
            "create_time" => date('Y-m-d H:i:s'),
            "service_time" => $order_info['order_time'],
            "state" => \Common\Data\StateData::FAILURE,
            "free_trial" => $order_info['free_trial'],
            "login_name" => $order_info['login_name'],
            "area_code" => $order_info['area_code'],
            "note_appended" => "会员编号:" . $member_id . "开通" . $product_info['product_name'] . "订单编号：" . $order_id
        );
        if (! $this->add($business_info)) {
            $result_inf['code'] = - 103; // 业务表插入失败
            return $result_inf;
        } else {
            $business_id = $this->getLastInsID(); // 获取业务ID
        }
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try {
            $result = $transaction->open($product_info['api_ptype'], $order_info['api_id']);
            // 插入日志记录
            api_log($member_id, "ptype:" . $product_info['api_ptype'] . "--did:" . $order_info['api_id'] . "--tid:" . GiantAPIParamsData::TID_OPEN, $result, $business_info['note_appended']);
        } catch (\Exception $e) {
            $del_where['id'] = [
                'eq',
                $business_id
            ];
            $this->where($del_where)->delete();
            api_log($member_id, "ptype:" . $product_info['api_ptype'] . "--did:" . $order_info['api_id'] . "--tid:" . GiantAPIParamsData::TID_OPEN, $e->getMessage(), $business_info['note_appended']);
            $result_inf['code'] = - 9;
            return $result_inf; // 调用接口失败
        }
        $xstr = json_decode($result, true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) { // 开通成功
            $arr = array(
                "state" => \Common\Data\StateData::SUCCESS_ORDER,
                "note_appended" => "会员编号:" . $member_id . "开通" . $product_info['product_name'] . "订单编号：" . $order_id . "执行成功",
                "create_time" => date('Y-m-d H:i:s'),
                "business_id" => $xstr['info']['bid'],
                "ip_address" => $xstr['info']['ip'],
                "system_user" => $xstr['info']['user'],
                "system_password" => $xstr['info']['pwd'],
                "open_time" => $xstr['info']['createDate'],
                "overdue_time" => $xstr['info']['overDate'],
                "remoteport" => $xstr['info']['remoteport'],
                "area_code" => $xstr['info']['areaCode']
            );
            $up_where['id'] = array(
                "eq",
                $business_id
            );
            if ($this->where($up_where)->save($arr)) { // 修改vps业务表
                                                       // 修改订单表订单标识，业务编号
                $arr = array(
                    "state" => \Common\Data\StateData::SUCCESS_ORDER,
                    "business_id" => $xstr['info']['bid'],
                    "ip_address" => $xstr['info']['ip'],
                    "complete_time" => date('Y-m-d H:i:s')
                );
                if ($order->order_edit($order_id, $arr)) {
                    $result_inf['code'] = -1;
                    $result_inf['ip_address'] = $xstr['info']['ip'];
                    $result_inf['system_user'] = $xstr['info']['user'];
                    $result_inf['order_id'] = $order_id;
                    $result_inf['system_password'] = $xstr['info']['pwd'];
                    $result_inf['remoteport'] = $xstr['info']['remoteport'];
                    $result_inf['createDate'] = $xstr['info']['createDate'];
                    $result_inf['overDate'] = $xstr['info']['overDate'];
                    $result_inf['product_name'] = $product_info['product_name'];
                    return $result_inf;
                } else {
                    $arr['note_appended'] = $arr['note_appended'] . "业务开通成功，订单状态修改失败";
                    $up_where['id'] = array(
                        "eq",
                        $business_id
                    );
                    $this->where($up_where)->save($arr);
                    $result_inf['code'] = - 7;
                    return $result_inf;
                }
            } else {
                $arr = array(
                    "state" => \Common\Data\StateData::FAILURE_ORDER,
                    'order_log' => "业务开通成功，业务表修改失败"
                );
                $order->order_edit($order_id, $arr);
                $result_inf['code'] = - 12;
                return $result_inf;
            }
        } else {
            $del_where['id'] = array(
                "eq",
                $business_id
            );
            $this->where($del_where)->delete();
            $result_inf['code'] = $xstr['code'];
            return $result_inf;
        }
    }

    /**
     * 快云VPS业务详情
     * @author: Guopeng
     * @param $id
     * @return mixed
     */
    public function fastvps_info($id)
    {
        $field =
            "od.system_type,p.system_type,vb.id,vb.user_id,vb.login_name,vb.business_id,p.product_name,
            vb.product_id,vb.ip_address,vb.system_user,vb.system_password,vb.open_time,vb.overdue_time,
            vb.state,vb.service_time,vb.free_trial,vb.note_appended,vb.area_code,vb.remoteport,
            vb.system_type as osType,p.api_name,p.product_type_id";
        $where["vb.id"] = $id;
        $join_od = "LEFT JOIN ".C('DB_PREFIX')."order as od on od.business_id = vb.business_id AND od.product_id=vb.product_id";
        $join_p = "LEFT JOIN ".C('DB_PREFIX')."product as p on p.id=vb.product_id";
        $fastvps_info = $this->alias('vb')->where($where)->field($field)->join($join_od)->join($join_p)->find();
        return $fastvps_info;
    }

    /**
     * FastVPS续费
     * @author: Guopeng
     * @param: $user_id 用户id
     * @param: $yw_id 业务id
     * @param: $order_time 续费时间(月)
     * @param: null $method 显示/业务
     * @return int
     */
    public function fastvps_renewals($user_id,$yw_id,$order_time,$method = null)
    {
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        // 获取fastvps业务信息
        $fastvps_info = $this->fastvps_info($yw_id);
        if(!$fastvps_info || $fastvps_info['user_id'] != $user_id)
        {
            return -101; // fastvps业务信息获取失败
        }
        if($fastvps_info ['state'] != StateData::SUCCESS && $fastvps_info ['state'] != 3)
        {
            return -10; // 业务失效
        }
        if($fastvps_info ['free_trial'] != 0)
        {
            return -100; // 试用业务不能续费
        }
        // 根据产品编号获取产品信息
        $product_info = $product_service->get_product($fastvps_info['product_id']);
        $product_type_info = $product_service->get_product_type_info($product_info['product_type_id']);
        if(!$product_info)
        {
            return -2; // 产品信息获取失败
        }
        // 计算续费价格（续费一年）
        if(!is_null($method) && strcmp($method,'get') == 0)
        {
            //获取续费信息
            return $order_service->get_renewals_info($fastvps_info);
        }
        // 生成订单表
        // 封装订单表数据
        $dataarray = array(
            "user_id" => $user_id,
            "order_type" => StateData::RENEWALS_ORDER, // 续费
            "state" => StateData::FAILURE_ORDER,
            "ip_address" => $fastvps_info ['ip_address'],
            "product_type" => $product_info ['product_type_id'],
            "product_id" => $product_info ['id'],
            "product_name" => $product_info ['product_name'],
            "free_trial" => $fastvps_info['free_trial'], // 不是试用
            "order_time" => $order_time, // 续费12个月
            "order_quantity" => 1,
            "business_id" => $fastvps_info ['business_id'],
            "create_time" => current_date(),
            "area_code" => $fastvps_info["area_code"], // 4001国内4002香港
            "login_name" => $fastvps_info ['login_name'],
            "complete_time" => current_date(),
            "order_log" => "会员".$user_id."续费".$product_info['product_name'],
            "system_type"=>$fastvps_info["system_type"]
        );
        $order_id = $order_service->order_do_entry($dataarray,$user_id,$dataarray["order_type"],$product_info,$product_type_info);
        if($order_id <= 0)
        {
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order_service->order_find($order_id);
        $system = WebSiteConfig();
        $usetype = $system["site_buy_vps"];
        try
        {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->renewals($product_info['api_ptype'],$fastvps_info['business_id'],$order_time,$usetype);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$fastvps_info['business_id']."--tid:".GiantAPIParamsData::TID_RENEWALS,$result,$order_info["order_log"]);
        }catch(\Exception $e)
        {
            $datarray = array(
                "state" => StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date(),
                "order_log" => $order_info ['order_log']."--接口调用失败"
            );
            $order_service->order_edit($order_id,$datarray);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$fastvps_info['business_id']."--tid:".GiantAPIParamsData::TID_RENEWALS,$e->getMessage(),$order_info["order_log"]."--接口调用失败");
            return -9;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null)
        {
            // 续费成功，修改业务表信息
            $overdue_time = $fastvps_info['overdue_time'];
            if(add_dates($overdue_time,$order_time) > $fastvps_info['create_time'])
            {
                $datarray = array (
                    "state" => StateData::SUCCESS,
                    'note_appended' => $fastvps_info['note_appended'].'||'.current_date().'成功续费'.$order_time.'个月',
                    "overdue_time" => add_dates ( $overdue_time, $order_time ),
                    'service_time' => $fastvps_info['service_time'] + $order_time
                );
            }
            else
            {
                $datarray = array (
                    'note_appended' => $fastvps_info['note_appended'].'||'.current_date().'成功续费'.$order_time.'个月',
                    "overdue_time" => add_dates ( $overdue_time, $order_time ),
                    'service_time' => $fastvps_info['service_time'] + $order_time
                );
            }
            if($this->business_edit($fastvps_info['id'],$datarray))
            {
                // 业务表修改成功，修改订单表
                $datarray = array(
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date(),
                    'note_appended' => $fastvps_info['note_appended'].'|'.current_date().'成功续费'.$order_time.'个月',
                    "order_log" => $order_info['order_log']."--续费成功"
                );
                $order_service->order_edit($order_id,$datarray);
                return -1; // 续费成功
            }
            else
            {
                // 业务表修改失败，修改订单表
                $datarray = array(
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date(),
                    "order_log" => $order_info ['order_log']."--续费成功,业务表修改失败"
                );
                $order_service->order_edit($order_id,$datarray);
                return -12; // 续费成功，业务表修改失败
            }
        }
        else
        {
            $datarray = array(
                "state" => StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date(),
                "order_log" => $order_info ['order_log']."--业务续费失败--接口调用错误"
            );
            $order_service->order_edit($order_id,$datarray);
            return $xstr['code'];
        }
    }

    /**
     * FastVPS升级
     * @author: Guopeng
     * @param: $user_id 会员编号
     * @param: $business_id 业务编号
     * @param: $upgrade_name 升级后名称
     * @param: null $method
     * @return int
     */
    public function fastvps_upgrade($user_id,$business_id,$up_product_id,$method = null)
    {
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        // 获取fastvps业务信息
        $fastvps_info = $this->fastvps_info($business_id);
        if(!$fastvps_info || $fastvps_info ['user_id'] != $user_id)
        {
            return -101;// 业务不存在或业务不属于该会员
        }
        if($fastvps_info ['state'] != StateData::SUCCESS && $fastvps_info ['state'] != 3)
        {
            return -10; // 业务失效
        }
        if($fastvps_info ['free_trial'] != 0)
        {
            return -100; // 试用业务不能升级
        }
        if(!is_null($method) && strcmp($method,'get') == 0)
        {
            //获取升级信息
            $fastvps_info["overdue_month"] = app_month($fastvps_info["overdue_time"]);
            $fastvps_info["month"] = $fastvps_info["service_time"];
            $params = $product_service->up_product_config_gap($fastvps_info,$fastvps_info['overdue_month']);
            $params["fastvps_info"] = $fastvps_info;
            return $params;
        }
        $product_info = $product_service->get_product($up_product_id);
        if(!$product_info)
        {
            return -2; // 升级产品信息获取失败
        }
        // 封装订单表参数
        $dataarray = array('user_id' => $user_id,
            'order_type' => StateData::CHANGE_ORDER, // 定单类型：0新增、1增值、2续费,3变更方案,转正
            'state' => StateData::FAILURE_ORDER,
            'ip_address' => $fastvps_info ['ip_address'],
            'product_type' => $product_info ['product_type_id'],
            'product_id' => $product_info ['id'],
            'product_name' => $product_info ['product_name'],
            'free_trial' => $fastvps_info['free_trial'],
            'order_time' => app_month($fastvps_info['overdue_time']),
            'order_quantity' => 1,
            'business_id' => $fastvps_info ['business_id'],
            'create_time' => current_date(),
            "area_code" => $fastvps_info["area_code"], // 4001国内4002香港
            'login_name' => $fastvps_info ['login_name'],
            'order_log' => '会员',$user_id."升级".$fastvps_info['product_name'],
            "system_type"=>$fastvps_info["system_type"]
        );
        $product_type_info["old_product_id"] = $fastvps_info["product_id"];
        $order_id = $order_service->order_do_entry($dataarray,$user_id,$dataarray['order_type'],$product_info,$product_type_info);
        if($order_id <= 0)
        {
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order_service->order_find($order_id);
        try
        {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->upgrade($product_info['api_ptype'],$product_info['api_name'],$fastvps_info['business_id']);
            // 插入日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$fastvps_info['business_id']."--tid:".GiantAPIParamsData::TID_UPGRADE,$result,$order_info['order_log']);
        }catch(\Exception $e)
        {
            // 升级失败
            $datarray = array('state' => StateData::FAILURE_ORDER,'order_log' => $order_info ['order_log'].'业务升级失败,接口连接失败','complete_time' => current_date());
            $order_service->order_edit($order_id,$datarray);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$fastvps_info['business_id']."--tid:".GiantAPIParamsData::TID_UPGRADE,$e->getMessage(),$dataarray["order_log"]."--接口调用失败");
            return -9;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null)
        {
            // 升级成功
            // 修改业务表
            $datarray = array(
                'product_id' => $product_info['id'],
                'product_name' => $product_info['product_name'],
                'note_appended' => $fastvps_info ['note_appended'].'||'.current_date().'成功升级到'.$product_info ['product_name']
            );
            if($this->business_edit($fastvps_info ['id'],$datarray))
            {
                $datarray = array(
                    'state' => StateData::SUCCESS_ORDER,
                    'note_appended' => '会员'.$user_id.'|'.current_date().'成功升级到'.$product_info['product_name'],
                    'order_log' => $order_info ['order_log'].'--业务升级成功',
                    'complete_time' => current_date()
                );
                $order_service->order_edit($order_id,$datarray);
                return -1;
            }
            else
            {
                $datarray = array(
                    'state' => StateData::SUCCESS_ORDER,
                    'order_log' => $order_info ['order_log'].'--业务升级成功,业务信息修改失败',
                    'complete_time' => current_date()
                );
                $order_service->order_edit($order_id,$datarray);
                return -102;
            }
        }
        else
        {
            // 升级失败
            $datarray = array(
                'state' => StateData::FAILURE_ORDER,
                'order_log' => $order_info ['order_log'].'--业务升级失败--接口调用错误',
                'complete_time' => current_date()
            );
            $order_service->order_edit($order_id,$datarray);
            return $xstr['code'];
        }
    }

    /**
     * FastVPS增值
     * @author: Guopeng
     * @param: $user_id 用户编号
     * @param: $yw_id fastvps业务ID
     * @param: $appreciation_name fastvps增值名称
     * @param: int $appreciation_size 增值大小
     * @param: null $method get显示/null业务
     * @return int
     */
    public function fastvps_appreciation($user_id,$yw_id,$appreciation_name,$appreciation_size = 1,$method = null)
    {
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        // 获取业务信息
        $fastvps_info = $this->fastvps_info($yw_id);
        if(!$fastvps_info || $fastvps_info ['user_id'] != $user_id)
        {
            return -101; // 业务不存在或不属于该会员
        }
        if($fastvps_info['free_trial'] != 0)
        {
            return -100; // 试用业务不能增值
        }
        if($fastvps_info ['state'] != StateData::SUCCESS && $fastvps_info ['state'] != 3)
        {
            return -10; // 业务已经过期
        }
        if(!is_null($method) && $method == 'get')
        {
            // 获取增值信息
            // 产品信息
            $product_info = $product_service->get_product($fastvps_info ['product_id']);
            if(!$product_info)
            {
                return -2;
            }
            // 业务剩余时间
            $price_data["month"] = $fastvps_info['service_time'];
            $surplus_time = app_month($fastvps_info ['overdue_time']);
            $product_price = $product_service->get_product_price_buy_time($fastvps_info['product_id'],$price_data);
            // 获取已经增值的产品信息列表
            $apperction_info = $order_service->get_apperction_list($fastvps_info['business_id'],$product_info['product_type_id'],'',true);
            // 获取增值产品信息
            $apperction_product = $product_service->app_product($product_info ['product_type_id']);
            for($i = 0;$i < count($apperction_product);$i++)
            {
                $price_data["month"] = 1;
                $app_price = $product_service->get_product_price_buy_time($apperction_product[$i]['id'],$price_data);
                $apperction_product [$i] ['app_price'] = $surplus_time * $app_price['product_price'];
            }
            $fastvps_info ['surplus_time'] = $surplus_time;
            $fastvps_info ['product_price'] = $product_price;
            $fastvps_info ['apperction_info'] = $apperction_info;
            $fastvps_info ['apperction_product'] = $apperction_product;
            $fastvps_info ['overdue_day'] = overdue_day($fastvps_info ['overdue_time']);
            return $fastvps_info;
        }
        $p_info = $product_service->get_product($fastvps_info['product_id']);
        $product_info = $product_service->get_appreciation_product($appreciation_name,$p_info['product_type_id']);
        if(!$product_info)
        {
            return -2; // 增值产品信息获取失败
        }
        // 获取增值配置信息表
        $product_config_info = $product_service->get_app_product_config($product_info ['id']);
        if(!$product_config_info)
        {
            return -16; // 增值配置信息获取失败
        }
        if($appreciation_name == GiantAPIParamsData::PNAME_FAST_CLOUDVPS_ZENGZHI_IP)
        {
            $quantity = 1;
        }elseif($appreciation_name == GiantAPIParamsData::PNAME_FAST_CLOUDVPS_ZENGZHI_NEICUN)
        {
            $quantity = $appreciation_size * $product_config_info ['config_value'];
            if($quantity % $product_config_info ['config_value'] != 0)
            {
                return -116; // 增值内存容量必须是128的倍数
            }
        }else if($appreciation_name == GiantAPIParamsData::PNAME_FAST_CLOUDVPS_ZENGZHI_YINGPAN)
        {
            $quantity = $appreciation_size * $product_config_info ['config_value'];
            if($quantity % $product_config_info ['config_value'] != 0)
            {
                return -116; // 增值硬盘必须是5的倍数
            }
        }else
        {
            return -110; // 增值类型错误
        }
        // 订单表参数
        $dataarray = array(
            "user_id" => $user_id,
            "product_id" => $product_info ['id'],
            "order_type" => StateData::APP_ORDER,
            "state" => StateData::FAILURE_ORDER,
            "ip_address" => $fastvps_info ['ip_address'],
            "product_type" => $product_info ['product_type_id'],
            "product_name" => $product_info ['product_name'],
            "free_trial" => $fastvps_info['free_trial'],
            "order_time" => app_month($fastvps_info ['overdue_time']),
            "order_quantity" => $appreciation_size,
            "business_id" => $fastvps_info ['business_id'],
            "create_time" => current_date(),
            "area_code" => $fastvps_info["area_code"], // 4001国内4002香港
            "login_name" => $fastvps_info ['login_name'],
            "order_log" => "会员".$user_id."增值".$fastvps_info['product_name'].'-'.$product_info['product_name'].app_month($fastvps_info['overdue_time'])."个月",
            "system_type"=>$fastvps_info["system_type"]
        );
        // 插入订单表
        $product_type_info["old_product_id"] = $fastvps_info["product_id"];
        $order_id = $order_service->order_do_entry($dataarray,$user_id,$dataarray['order_type'],$product_info,$product_type_info);
        if($order_id <= 0)
        {
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order_service->order_find($order_id);
        try
        {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->appreciation($product_info['api_ptype'],$product_info['api_name'],$fastvps_info['business_id'],$quantity);
            // 日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--pname:".$product_info['api_name']."--bid:".$fastvps_info['business_id']."--tid:".GiantAPIParamsData::TID_APPRECIATION."--size:".$quantity,$result,$order_info['order_log']);
        }catch(\Exception $e)
        {
            $order_service->order_edit($order_id,array(
                "state" => StateData::FAILURE_ORDER,
                "order_log" => $order_info['order_log']."--接口调用失败",
                "complete_time" => current_date()
            ));
            api_log($user_id,"ptype:".$product_info['api_ptype']."--pname:".$product_info['api_name']."--bid:".$fastvps_info['business_id']."--tid:".GiantAPIParamsData::TID_APPRECIATION."--size:".$quantity,$e->getMessage(),$order_info['order_log']."--接口调用失败");
            return -9;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null)
        {
            $ip_address = $xstr['info'];
            if($ip_address)
            {
                $ip_address = '无';
            }
            // 增值信息表插入记录
            $result = add_appreciations($fastvps_info['business_id'],$product_info['id'],$quantity,$product_info['product_type_id'],$ip_address);
            if(!$result)
            {
                $datarray = array(
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date(),
                    "order_log" => $order_info['order_log']."--增值信息表插入记录失败"
                );
                $order_service->order_edit($order_id,$datarray);
                return -111; // 增值记录表记录失败
            }
            $datarray = array(
                "state" => StateData::SUCCESS_ORDER,
                "complete_time" => current_date(),
                "note_appended" => "会员".$user_id."成功增值".$fastvps_info['product_name'].'-'.$product_info['product_name'].app_month($fastvps_info['overdue_time'])."个月",
                "order_log" => $order_info['order_log']."--增值成功"
            );
            $order_service->order_edit($order_id,$datarray);
            if($ip_address != '无')
            {
                $datarray = array(
                    "ip_address" => $ip_address,
                    "note_appended" => $fastvps_info['note_appended'].'||'.current_date()."成功增值".$fastvps_info['product_name'].'-'.$product_info['product_name'].app_month($fastvps_info['overdue_time'])."个月"
                );
                if(!$this->business_edit($fastvps_info['id'],$datarray))
                {
                    return -12;
                };
            }
            return -1;
        }
        else
        {
            $datarray = array(
                "state" => StateData::FAILURE_ORDER,
                "complete_time" => current_date(),
                "order_log" => $order_info["order_log"]."--增值失败-接口调用错误"
            );
            // 增值失败
            $order_service->order_edit($order_id,$datarray);
            return $xstr['code'];
        }
    }
    /**
     * 快云VPS转正
     * @author: Guopeng
     * @param: $user_id 会员编号
     * @param: $yw_id 业务编号
     * @param: null $service_time 转正时间
     * @param: null $method 是否是get提交
     * @return int|\SimpleXMLElement[]
     */
    public function fastvps_onformal($user_id, $yw_id, $service_time = null, $method = null)
    {
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        $fastvps_info = $this->fastvps_zhuanzheng_info($yw_id);
        if(!$fastvps_info || $fastvps_info['state'] != StateData::SUCCESS)
        {
            return -10;
        }
        if($fastvps_info['user_id'] != $user_id)
        {
            return -101;
        }
        if($fastvps_info['free_trial'] != 1)
        {
            return -114; // 不是试用业务不能转正
        }
        if(!is_null($method) && $method == 'get')
        {
            return $fastvps_info;
        }
        $product_info = $product_service->get_product($fastvps_info['product_id']);
        if(!$product_info)
        {
            return -2; // 增值产品信息获取失败
        }
        // 订单参数
        $arr = array(
            'user_id' => $user_id,
            'order_type' => StateData::ONFORMAL_ORDER, // 转正
            'state' => StateData::FAILURE_ORDER,
            'ip_address' => $fastvps_info['ip_address'],
            'product_type' => $product_info['product_type_id'],
            'product_id' => $product_info['id'],
            'product_name' => $product_info['product_name'],
            'free_trial' => $fastvps_info['free_trial'],
            'order_time' => $service_time,
            'order_quantity' => 1,
            'business_id' => $fastvps_info['id'],
            'create_time' => current_date(),
            "area_code" => $fastvps_info["area_code"], // 4001国内4002香港
            'login_name' => $fastvps_info['login_name'],
            "system_type"=>$fastvps_info["system_type"]
        );
        // 插入订单
        $order_id = $order_service->order_do_entry($arr,$user_id,$arr['order_type'],$product_info,$fastvps_info["product_id"]);
        if($order_id <= 0)
        {
            return $order_id;
        }
        $order_info = $order_service->order_find($order_id);
        // 调用api接口增值
        try
        {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->onformal($product_info['api_ptype'],$fastvps_info['business_id'],$service_time);
            // 日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$fastvps_info['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$result,$order_info['order_log']);
        }catch(\Exception $e)
        {
            $arr = array(
                "complete_time" => current_date(),
                "order_log"=>$order_info['order_log']."--接口调用失败",
                "state" => StateData::FAILURE_ORDER // 订单状态失败
            );
            $order_service->order_edit($order_id,$arr);
            // 日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$fastvps_info['business_id']."--tid:".GiantAPIParamsData::TID_POSITIVE."--yid:".$service_time,$e->getMessage(),$order_info['order_log']."-接口调用失败");
            return -9;
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
     * 获取快云VPS详细信息
     * @author:Guopeng
     * @param: $id 业务编号
     * @return int|mixed
     */
    public function fastvps_zhuanzheng_info($id)
    {
        $product_service = new \Frontend\Model\ProductModel();
        // 获取业务信息
        $fastvps_info = $this->fastvps_info($id);
        // 是否为前台用户且为删除状态
        if($fastvps_info['state'] == 2)
        {
            return -10;
        }
        $product_info = $product_service->get_product($fastvps_info['product_id']);
        // 获取产品配置信息
        $product_config = $product_service->get_product_config($fastvps_info['product_id']);
        $price_list = $product_service->get_product_price_list($fastvps_info['product_id'],StateData::STATE_BUY);
        $p_config = array();
        // 获取该业务的所有增值信息
        $appreciation_list = $product_service->business_appreciation_info($fastvps_info['id'],$product_info['product_type_id']);
        if(!$appreciation_list)
        {
            // 如果不存在增值信息
            // 将多维数组转换为一维数组，$product_config的en_name为新数组的key$product_config的config_value和联合组成新数组的value
            for($i = 0;$i < count($product_config);$i++)
            {
                $key = $product_config[$i]['en_name'];
                $p_config["$key"] = $product_config[$i]['config_value'];
            }
        }
        else
        {
            // 如果存在
            for($i = 0; $i < count ( $appreciation_list ); $i ++) {
                // 获取增值产品编号
                $product_id = $appreciation_list [$i] ['app_product_id'];
                // 获取增值产品信息
                $appreciation_info = $product_service->get_app_product_config ( $product_id );
                // 将多维数组转换为一维数组，$product_config的en_name为新数组的key$product_config的config_value和联合组成新数组的value
                if ($appreciation_info ['app_name'] == GiantAPIParamsData::PNAME_FAST_CLOUDVPS_ZENGZHI_YINGPAN) {
                    for($j = 0; $j < count ( $product_config ); $j ++) {
                        $key = $product_config [$j] ['en_name'];
                        if ($key == 'disk_config') {//硬盘配置
                            $p_config ["$key"] = $product_config [$j] ['config_value'] += $appreciation_list [$i] ['quanity'];
                        } else {
                            $key = $product_config [$i] ['en_name'];
                            $p_config ["$key"] = $product_config [$i] ['config_value'];
                        }
                    }
                } elseif ($appreciation_info ['app_name'] == GiantAPIParamsData::PNAME_FAST_CLOUDVPS_ZENGZHI_NEICUN) {
                    for($j = 0; $j < count ( $product_config ); $j ++) {
                        $key = $product_config [$j] ['en_name'];
                        if ($key == 'memory_config') {//内存类型
                            $p_config ["$key"] = $product_config [$j] ['config_value'] += $appreciation_list [$i] ['quanity'];
                        } else {
                            $key = $product_config [$j] ['en_name'];
                            $p_config ["$key"] = $product_config [$j] ['config_value'];
                        }
                    }
                } else if ($appreciation_info ['app_name'] == GiantAPIParamsData::PNAME_FAST_CLOUDVPS_ZENGZHI_IP) {
                    for($j = 0; $j < count ( $product_config ); $j ++) {
                        $key = $product_config [$j] ['en_name'];
                        if ($key == 'ip') {//IP地址
                            $p_config ["$key"] = $product_config [$j] ['config_value'] += 1;
                        } else {
                            $key = $product_config [$j] ['en_name'];
                            $p_config ["$key"] = $product_config [$j] ['config_value'];
                        }
                    }
                }else if ($appreciation_info ['app_name'] == 'cpu') {
                    for($j = 0; $j < count ( $product_config ); $j ++) {
                        $key = $product_config [$j] ['en_name'];
                        if ($key == 'cpu_info') {//CPU
                            $p_config ["$key"] = $product_config [$j] ['config_value'] += 1;
                        } else {
                            $key = $product_config [$j] ['en_name'];
                            $p_config ["$key"] = $product_config [$j] ['config_value'];
                        }
                    }
                }
            }
        }
        foreach($p_config as $p_key=>$p_v)
        {
            $fastvps_info[$p_key] = $p_v;
        }
        $fastvps_info['api_ptype'] = $product_info['api_ptype'];
        $fastvps_info['price_list'] = $price_list;
        return $fastvps_info;
    }
}