<?php
namespace Frontend\Model;
use Frontend\Model\BusinessModel;
use Common\Data\GiantAPIParamsData;
use Common\Aide\AgentAide;

class IpModel extends BusinessModel{
    protected $trueTableName = 'agent_cloudserver_business_ip';
    
    public function conver_par(){
        $date =request();
        $where["bs.user_id"] = array("eq",session("user_id"));
        if(!empty($date['select'])){
            $select = clearXSS($date['select']);
            $map['bs.api_bid'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map['bs.ipaddress'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map["bs.beizhu"] = array("like" ,"%$select%");
            $where['_complex'] = $map;
        }
        return $where;
    }
    
    /**
     * ----------------------------------------------
     * | 获取用户的快云服务器ip
     * | @时间: 2016年12月19日 下午5:39:05
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function get_user_ips($where = []) {
    	if (empty($where)){
    		$where['user_id'] = [ 'eq', session('user_id') ];
    	}
    	
    	return $this->where($where)->select();
    }
    /**
    * IP续费展示
    * @date: 2017年1月4日 上午11:47:35
    * @author: Lyubo
    * @param: variable
    * @return: $ip_info
    */
    public function show_ip_renewals($member_id,$ip_id){
        $order = new \Frontend\Model\OrderModel();
        $product= new \Frontend\Model\ProductModel();
        // 获取IP业务信息
        $cloudserver_ip_where['Id'] = ["eq",$ip_id];
        $cloudserverip_info = $this->where($cloudserver_ip_where)->find();
        if (! $cloudserverip_info || $cloudserverip_info ['user_id'] != $member_id) {
            return - 101; // 快云服务器业务信息获取失败
        }
        if ($cloudserverip_info ['state'] != \Common\Data\StateData::SUCCESS && $cloudserverip_info ['state'] != \Common\Data\StateData::OVERDUE) {
            return - 10; // 业务失效
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product ( $cloudserverip_info ['product_id'] );
        if (! $product_info) {
            return - 2; // 产品信息获取失败
        }
        $cloudserverip_info['type'] = GiantAPIParamsData::TID_RENEWALS;
        $cloudserverip_info['dqbh'] = $cloudserverip_info['area_code'];
        $cloudserverip_info['gmqx'] = 1;
        $cloud_price_one  = cloudserverPrice($cloudserverip_info);
        $cloudserverip_info['gmqx'] = 12;
        $cloud_price_Twelve = cloudserverPrice($cloudserverip_info);
        //获取续费信息
        $renewals_info[0]=array('product_name'=>$cloudserverip_info['product_name'],'id'=>$cloudserverip_info['Id'],'month'=>1,'product_price'=>$cloud_price_one);
        $renewals_info[1]=array('product_name'=>$cloudserverip_info['product_name'],'id'=>$cloudserverip_info['Id'],'month'=>12,'product_price'=>$cloud_price_Twelve);
        $total_price[0]=array('price'=>$cloud_price_one,'month'=>1);
        $total_price[1]=array('price'=>$cloud_price_Twelve,'month'=>12);
        return array_merge($cloudserverip_info,array('renewals_info'=>$renewals_info,'total_price'=>$total_price,'renewals_price_list'=>$renewals_info)); // 返回该业务详细信息和续费金额给确认续费页面
    }
    /**
    * IP升级、临时增值显示
    * @date: 2017年1月6日 上午10:05:34
    * @author: Lyubo
    * @param: variable
    * @return: $ip_info
    */
    public function show_ip_upgrade($member_id,$ip_id){
        $product= new \Frontend\Model\ProductModel();
        // 获取IP业务信息
        $cloudserver_ip_where['Id'] = ["eq",$ip_id];
        $cloudserverip_info = $this->where($cloudserver_ip_where)->find();
        if (! $cloudserverip_info || $cloudserverip_info ['user_id'] != $member_id) {
            return - 101; // 快云服务器业务信息获取失败
        }
        if ($cloudserverip_info ['state'] != \Common\Data\StateData::SUCCESS && $cloudserverip_info ['state'] != \Common\Data\StateData::OVERDUE) {
            return - 10; // 业务失效
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product ( $cloudserverip_info ['product_id'] );
        if (! $product_info) {
            return - 2; // 产品信息获取失败
        }
        return $cloudserverip_info;
    }
   /**
   * 计算IP升级费用
   * @date: 2017年1月9日 下午4:41:58
   * @author: Lyubo
   * @param: variable
   * @return:
   */
    public function ipPrice($data){
        $product = new \Frontend\Model\ProductModel();
        if($data['dqbh'] == '4001'){//机房：4001:郑州机房，4002:香港机房
            /*获取带宽*/
            $price_bandwidth=$product->get_cloud_price_list("购买带宽大于5M");
            $price_bandwidth_price=$product->get_cloud_price_list("购买带宽");
            if($data['bandwidth']>5){
                $cloud_bandwidth_price=$price_bandwidth['product_price'] * ($data['bandwidth']-5)+$price_bandwidth_price['product_price'] *5;
            }else{
                $cloud_bandwidth_price = $price_bandwidth_price['product_price'] * ($data['bandwidth']);
            }
        }elseif($data['dqbh'] == '4002'){
            /*获取香港带宽价格*/
            $price_hkbandwidth=$product->get_cloud_price_list("购买香港带宽大于5M");
            $price_hkbandwidth2=$product->get_cloud_price_list("购买香港带宽");
            if($data['bandwidth']>5){
                $cloud_bandwidth_price=$price_hkbandwidth['product_price'] * ($data['bandwidth']-5)+$price_hkbandwidth2['product_price'] *5;
            }else{
                $cloud_bandwidth_price = $price_hkbandwidth2['product_price'] * ($data['bandwidth']);
            }
        }
        $ip_where["api_bid"] = ["eq",$data["ipbh"]];
        $ip_where["user_id"] = ["eq",session("user_id")];
        $ipinfo = $this->where($ip_where)->find();
        $ipinfo ["overdue_month"] = app_month($ipinfo ["overdue_time"]);
        if($ipinfo['bandwidth'] >5){//计算原有配置价格
            $ip_bandwidth_price=$price_bandwidth['product_price'] * ($ipinfo['bandwidth']-5)+$price_bandwidth_price['product_price'] *5;
            }else{
            $ip_bandwidth_price = $price_bandwidth_price['product_price'] * ($ipinfo['bandwidth']);
       }
        $data['count'] ='1';
        return $cloud_price=(($cloud_bandwidth_price - $ip_bandwidth_price)*$ipinfo['overdue_month'])*$data['count'];
    }
    /**
    * IP续费
    * @date: 2017年1月4日 下午3:33:12
    * @author: Lyubo
    * @param: $ip_id,$member_id,$renewalstime
    * @return: code
    */
     public function ip_renewals($ip_id,$member_id,$renewalstime){
        $order = new \Frontend\Model\OrderModel();
        $product= new \Frontend\Model\ProductModel();
        // 获取IP业务信息
        $cloudserver_ip_where['Id'] = ["eq",$ip_id];
        $cloudserverip_info = $this->where($cloudserver_ip_where)->find();
        if (! $cloudserverip_info || $cloudserverip_info ['user_id'] != $member_id) {
            return - 101; // 快云服务器业务信息获取失败
        }
        if ($cloudserverip_info ['state'] != \Common\Data\StateData::SUCCESS && $cloudserverip_info ['state'] != \Common\Data\StateData::OVERDUE) {
            return - 10; // 业务失效
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product ( $cloudserverip_info ['product_id'] );
        if (! $product_info) {
            return - 2; // 产品信息获取失败
        }
        $cloudserverip_info['type'] = GiantAPIParamsData::TID_RENEWALS;
        $cloudserverip_info['dqbh'] = $cloudserverip_info['area_code'];
        $cloudserverip_info['gmqx'] = 1;
        $cloud_price_one  = cloudserverPrice($cloudserverip_info);
        $cloudserverip_info['gmqx'] = 12;
        $cloud_price_Twelve = cloudserverPrice($cloudserverip_info);
        // 封装订单表数据
        $dataarray = array (
            "user_id" => $member_id,
            "order_type" => \Common\Data\StateData::RENEWALS_ORDER, // 续费
            "state" => \Common\Data\StateData::FAILURE_ORDER,
            "ip_address" => $cloudserverip_info ['ip_address'],
            "product_type" => $product_info ['product_type_id'],
            "product_id" => $product_info ['id'],
            "product_name" => $product_info ['product_name'],
            "free_trial" => 0, // 不是试用
            "order_time" => $renewalstime, // 续费12个月
            "order_quantity" => 1,
            "business_id" => $cloudserverip_info ['id'],
            "create_time" => current_date (),
            "area_code" => $cloudserverip_info['area_code'], // 4001国内4002香港
            "login_name" => $cloudserverip_info ['login_name'],
            "complete_time" => current_date (),
        );
        $cloudserverip_info['member_id'] = $member_id;
        $order_id = $order->cloud_order_do_entry ( $dataarray, $cloudserverip_info, \Common\Data\StateData::RENEWALS_ORDER);
        if ($order_id <= 0) {
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order->order_find ( $order_id );
        // 调用api接口续费
        $format_id = 2; // 1返回json字符串2返回xml字符串 默认为1
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->cloudserverip_renewals (GiantAPIParamsData::PTYPE_CLOUD_SERVER ,GiantAPIParamsData::PNAME_CLOUDSERVER_IP, $cloudserverip_info ['api_bid'],$renewalstime );
            api_log ( $member_id, "ptype:" . $product_info ['api_ptype'] . "--pname:".GiantAPIParamsData::PNAME_CLOUDSERVER_IP."--bid:" . $dataarray ['business_id'] . "--tid:" . GiantAPIParamsData::TID_RENEWALS, $result, "会员" . $member_id . "续费" . $product_info ['product_name'] );
        } catch ( \Exception $e ) {
            $dataarray = array (
                "state" => \Common\Data\StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date (),
                "api_id" => $cloudserverip_info ['api_bid'],
                "order_log" => $order_info ['order_log'] . "--接口调用失败"
            );
            $order->order_edit ( $order_id,$dataarray);
            return - 9;
        }
       $xstr = json_decode($result, true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            // 续费成功，修改业务表信息
            $overdue_time = $cloudserverip_info ['overdue_time'];
            $dataarray = array (
                "overdue_time" => $xstr['info']['overdate'],
                'buy_time' => $cloudserverip_info ['buy_time'] + $renewalstime
            );
            $up_cloudserverip_where['id'] = ["eq",$ip_id];
            if ($this->where($up_cloudserverip_where)->save($dataarray) !== false) {
                // 业务表修改成功，修改订单表
                $dataarray = array (
                    "state" => \Common\Data\StateData::SUCCESS_ORDER,
                    "complete_time" => current_date (),
                    "api_id" => $cloudserverip_info ['id'],
                    "order_log" => $order_info ['order_log'] . "--续费成功"
                );
                 $order->order_edit ( $order_id,$dataarray);
                return - 1; // 续费成功
            } else {
                // 业务表修改失败，修改订单表
                $dataarray = array (
                    "state" => \Common\Data\StateData::SUCCESS_ORDER,
                    "complete_time" => current_date (),
                    "api_id" => $cloudserverip_info ['api_id'],
                    "order_log" => $order_info ['order_log'] . "--续费成功,业务表修改失败"
                );
               $order->order_edit ( $order_id,$dataarray);
                return - 12; // 续费成功，业务表修改失败
            }
        } else {
            $dataarray = array (
                "state" =>  \Common\Data\StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date (),
                "api_id" => $cloudserverip_info ['id'],
                "order_log" => $order_info ['order_log'] . "--接口调用失败--" . api_code ( $xstr['code'] )
            );
            $order->order_edit ( $order_id,$dataarray);
            return $xstr['code'];
        }
    } 
    /**
    * IP升级带宽
    * @date: 2017年1月10日 上午11:32:01
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function ip_upgrade($ip_id, $member_id ,$daikuan){
        $order = new \Frontend\Model\OrderModel();
        $product= new \Frontend\Model\ProductModel();
        // 获取IP业务信息
        $cloudserver_ip_where['Id'] = ["eq",$ip_id];
        $cloudserverip_info = $this->where($cloudserver_ip_where)->find();
        if (! $cloudserverip_info || $cloudserverip_info ['user_id'] != $member_id) {
            return - 101; // 快云服务器业务信息获取失败
        }
        if ($cloudserverip_info ['state'] != \Common\Data\StateData::SUCCESS && $cloudserverip_info ['state'] != \Common\Data\StateData::OVERDUE) {
            return - 10; // 业务失效
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product ( $cloudserverip_info ['product_id'] );
        if (! $product_info) {
            return - 2; // 产品信息获取失败
        }
        // 封装订单表数据
        $dataarray = array (
            "user_id" => $member_id,
            "order_type" => \Common\Data\StateData::CHANGE_ORDER, // 变更方案
            "state" => \Common\Data\StateData::FAILURE_ORDER,
            "ip_address" => $cloudserverip_info ['ip_address'],
            "product_type" => $product_info ['product_type_id'],
            "product_id" => $product_info ['id'],
            "product_name" => $product_info ['product_name'],
            "free_trial" => 0, // 不是试用
            "order_time" => app_month($cloudserverip_info ["overdue_time"]), // 续费12个月
            "order_quantity" => 1,
            "business_id" => $cloudserverip_info ['id'],
            "create_time" => current_date (),
            "area_code" => $cloudserverip_info['area_code'], // 4001国内4002香港
            "login_name" => $cloudserverip_info ['login_name'],
            "complete_time" => current_date (),
        );
        $cloudserverip_info['member_id'] = $member_id;
        $order_id = $order->cloud_order_do_entry ( $dataarray, $cloudserverip_info, \Common\Data\StateData::CHANGE_ORDER);
        if ($order_id <= 0) {
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order->order_find ( $order_id );
        $system = WebSiteConfig();
        $usetype = $system["site_buy_cloud"];
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->cloudserverip_upgrade (GiantAPIParamsData::PTYPE_CLOUD_SERVER ,GiantAPIParamsData::PNAME_CLOUDSERVER_IP, $cloudserverip_info ['api_bid'],$daikuan,$usetype);
            api_log ( $member_id, "ptype:" . $product_info ['api_ptype'] . "--pname:".GiantAPIParamsData::PNAME_CLOUDSERVER_IP."--bid:" . $cloudserverip_info ['api_bid'] . "--tid:" . GiantAPIParamsData::TID_UPGRADE, $result, "会员" . $member_id . "升级" . $product_info ['product_name'] );
        } catch ( \Exception $e ) {
            $dataarray = array (
                "state" => \Common\Data\StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date (),
                "api_id" => $cloudserverip_info ['api_bid'],
                "order_log" => $order_info ['order_log'] . "--接口调用失败"
            );
            $order->order_edit ( $order_id,$dataarray);
            return - 9;
        }
        $xstr = json_decode($result, true);
        if ($xstr['code'] == 0 && $xstr['code'] !== null) {
            // 续费成功，修改业务表信息
            $bandwidth = $cloudserverip_info ['bandwidth'];
            $dataarray = array (
                'bandwidth' => $cloudserverip_info ['bandwidth'] + $bandwidth
            );
            $up_cloudserverip_where['id'] = ["eq",$ip_id];
            if ($this->where($up_cloudserverip_where)->save($dataarray) !== false) {
                // 业务表修改成功，修改订单表
                $dataarray = array (
                    "state" => \Common\Data\StateData::SUCCESS_ORDER,
                    "complete_time" => current_date (),
                    "api_id" => $cloudserverip_info ['id'],
                    "order_log" => $order_info ['order_log'] . "--升级成功"
                );
                $order->order_edit ( $order_id,$dataarray);
                return - 1; // 续费成功
            } else {
                // 业务表修改失败，修改订单表
                $dataarray = array (
                    "state" => \Common\Data\StateData::SUCCESS_ORDER,
                    "complete_time" => current_date (),
                    "api_id" => $cloudserverip_info ['api_id'],
                    "order_log" => $order_info ['order_log'] . "--升级成功,业务表修改失败"
                );
                $order->order_edit ( $order_id,$dataarray);
                return - 12; // 续费成功，业务表修改失败
            }
        } else {
            $dataarray = array (
                "state" =>  \Common\Data\StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date (),
                "api_id" => $cloudserverip_info ['id'],
                "order_log" => $order_info ['order_log'] . "--接口调用失败--" . api_code ( $xstr['code'] )
            );
            $order->order_edit ( $order_id,$dataarray);
            return $xstr['code'];
        }
    }
}