<?php
namespace Frontend\Model;
use Frontend\Model\BusinessModel;
use Common\Aide\AgentAide;
use Common\Data\GiantAPIParamsData;

class CloudserverModel extends BusinessModel{
    protected $trueTableName = 'agent_cloudserver_business';
   
    public function conver_par(){
        $date =request();
        $where["bs.user_id"] = array("eq",session("user_id"));
        if(!empty($date['select'])){
                $select = clearXSS($date['select']);
                $map['bs.api_bid'] = array("like" , "%$select%");
                $map['_logic'] = 'OR';
                $map['bs.nw_ip'] = array("like" , "%$select%");
                $map['_logic'] = 'OR';
                $map["bs.beizhu"] = array("like" ,"%$select%");
                $where['_complex'] = $map;
        }
        return $where;
    }
    /**
    * 快云服务器OsType
    * @date: 2016年11月25日 下午2:52:55
    * @author: Lyubo
    * @return:
    */
    public function OsTypeInfo(){
        $agent = new AgentAide();
        $transaction = $agent->servant;
        $ptype = GiantAPIParamsData::PTYPE_CLOUD_SERVER;
        $pname = GiantAPIParamsData::PNAME_CLOUDSERVER_OS;
        $tid = GiantAPIParamsData::TID_SYSTEM_TYPE;
        $area_code = 4002;
        $log_message = "ptype:".$ptype."||tid=".$tid."pname:".$pname;
        try {
            $result = $transaction->CloudserverOsType($ptype,$pname,$tid,$area_code);
            api_log("-1",$log_message,$result,'获取香港机房服务器操作系统');
            $hk_json = json_decode($result, true);
        } catch ( \Exception $e) {
            $result = $e->getMessage();
            api_log("-1",$log_message,$result,'获取香港机房服务器操作系统失败');
            return - 9;
        }
        $pname = "cloudserver.getKVMsystem";
        $tid = 56;
        $area_code = 4001;
        $log_message = "ptype:".$ptype."||tid=".$tid."pname:".$pname;
        try {
            $result = $transaction->CloudserverOsType($ptype,$pname,$tid,$area_code);
            api_log("-1",$log_message,$result,'获取郑州机房服务器操作系统');
            $zz_json = json_decode($result, true);
        } catch ( \Exception $e) {
            $result = $e->getMessage();
            api_log("-1",$log_message,$result,'获取郑州机房服务器操作系统失败');
            return - 9;
        }
        return $json=["hk"=>$hk_json,"zz"=>$zz_json];
    }
    /**
    * 计算快云服务器价格
    * @date: 2016年11月25日 下午2:44:35
    * @author: Lyubo
    * @param: $data
    * @return: $price
    */
    public function CalculatedPrice($data){
        $product = new \Frontend\Model\ProductModel();
        $config = ["1"=>["disk"=>"80","cpu"=>"1","mem"=>"1","bandwidth"=>"2"],"2"=>["disk"=>"120","cpu"=>"2","mem"=>"2","bandwidth"=>"3"],"3"=>["disk"=>"200","cpu"=>"4","mem"=>"4","bandwidth"=>"5"],"4"=>["disk"=>"300","cpu"=>"8","mem"=>"8","bandwidth"=>"7"],"5"=>["disk"=>"500","cpu"=>"16","mem"=>"16","bandwidth"=>"10"]];
        if (MODULE_NAME == 'Frontend' && isMobile() == 'phone'){
            for ($i = 1;$i<count($config);$i++){
                    if($data['pzbh'] == $i){
                        $data['disk'] = $config[$i]['disk'];
                        $data['cpu'] = $config[$i]['cpu'];
                        $data['mem'] = $config[$i]['mem'];
                        $data['bandwidth'] = $config[$i]['bandwidth'];
                     }
            }
        }
        if($data['dqbh'] == '4001'){//机房：4001:郑州机房，4002:香港机房
            /*获取CPU价格*/
            $price_cpu=$product->get_cloud_price_list("购买CPU");
            $cloud_cpu_price = ($price_cpu['product_price'] * $data['cpu']);
            /*获取数据盘价格*/
    	    $disk=$data['disk'] / 10;
    	    $price_disk=$product->get_cloud_price_list("购买硬盘");
    	    $cloud_disk_price = ($price_disk['product_price'] * $disk);
    	    /*获取带宽*/
    	    $price_bandwidth=$product->get_cloud_price_list("购买带宽大于5M");
    	    $price_bandwidth_price=$product->get_cloud_price_list("购买带宽");
    	    if($data['bandwidth']>5){
    	        $cloud_bandwidth_price=$price_bandwidth['product_price'] * ($data['bandwidth']-5)+$price_bandwidth_price['product_price'] *5;
    	    }else{
    	        $cloud_bandwidth_price = $price_bandwidth_price['product_price'] * ($data['bandwidth']);
    	    }
        }elseif($data['dqbh'] == '4002'){
            
            /*获取香港CPU价格*/
            $price_hkcpu=$product->get_cloud_price_list("购买香港CPU");
            $cloud_cpu_price = $price_hkcpu['product_price'] * ($data['cpu']);
            /*获取香港硬盘价格*/
            $disk=$data['disk'] / 10;
            $price_hkdisk=$product->get_cloud_price_list("购买香港硬盘");
            $cloud_disk_price = $price_hkdisk['product_price'] * ($disk);
            /*获取香港带宽价格*/
            $price_hkbandwidth=$product->get_cloud_price_list("购买香港带宽大于5M");
            $price_hkbandwidth2=$product->get_cloud_price_list("购买香港带宽");
            if($data['bandwidth']>5){
                $cloud_bandwidth_price=$price_hkbandwidth['product_price'] * ($data['bandwidth']-5)+$price_hkbandwidth2['product_price'] *5;
            }else{
                $cloud_bandwidth_price = $price_hkbandwidth2['product_price'] * ($data['bandwidth']);
            }
        }
        /*获取内存价格*/
        $price_mem=$product->get_cloud_price_list("购买内存");
        $cloud_mem_price = $price_mem['product_price'] * ($data['mem']);
        if($data['type'] == GiantAPIParamsData::TID_RENEWALS){
            //续费没有优惠 
        }else{
            if ($data['gmqx'] == 12) {
                $data['gmqx'] = 10;
            } elseif ($data['gmqx'] == 24) {
                $data['gmqx'] = 20;
            } elseif ($data['gmqx'] == 36) {
                $data['gmqx'] = 30;
            }
        }
        $data['count'] ='1';
        return $cloud_price=(($cloud_cpu_price+$cloud_mem_price+$cloud_disk_price+$cloud_bandwidth_price)*$data['gmqx'])*$data['count'];
    }
/**********************************************快云服务器购买******************************************/
    /**
    * 购买快云服务器
    * @date: 2016年11月26日 下午4:27:21
    * @author: Lyubo
    * @return: $business_code
    */
    public function cloudserver_buy($buy_info){
        // 封装订单参数信息
        $datarray = array (
            "user_id" => $buy_info ['member_id'],
            "order_quantity" => 1,
            "free_trial" => $buy_info ['free_trial'], // 购买或者试用
            "order_time" => $buy_info ['gmqx'],
            "create_time" => date('Y-m-d H:i:s'),
            "complete_time" => date('Y-m-d H:i:s'), // 完成时间
            "state" => \Common\Data\StateData::SUCCESS_ORDER,  // 订单状态成功
            "system_type"=>$buy_info['os'],//操作系统
            "area_code"=>$buy_info['dqbh']//地区编号
        );
        //执行录入订单、购买方法
        $order =  new \Frontend\Model\OrderModel();
        $order_id = $order->cloud_order_do_entry ( $datarray, $buy_info, \Common\Data\StateData::NEW_ORDER );
        if ($order_id < 0) {//小于0就是错误返回信息
            return $order_id;
        }
        return $this->api_cloudserver_buy ( $order_id ,$buy_info );
    }
    /**
    * 快云服务器购买接口调用
    * @date: 2016年11月26日 下午5:43:30
    * @author: Lyubo
    * @return: business_code
    */
    public function api_cloudserver_buy($order_id,$buy_info){
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        $system = WebSiteConfig();
        $usetype = $system["site_buy_cloud"];
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
                // 购买
                $result = $transaction->cloud_buy ( GiantAPIParamsData::PTYPE_CLOUD_SERVER, $order_info['order_time'],$order_info['area_code'],$buy_info,$usetype);
                api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER .  "--tid:" . GiantAPIParamsData::TID_BUY."--area_code".$order_info['area_code'], $result, $order_info ['order_log'] );
        } catch ( \Exception $e ) {
           // 调用接口试用失败修改订单表
            $dataarray = array (
                "complete_time" =>  date('Y-m-d H:i:s'),
                "order_log" => $order_info ['order_log'] . "-接口调用错误",
                "area_code" => $order_info['area_code'],
                "state" => \Common\Data\StateData::FAILURE_ORDER  // 订单状态失败
            );
            $order->order_edit ( $order_id, $dataarray );
            api_log ( $order_info ['user_id'], "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER .  "--tid:" . GiantAPIParamsData::TID_BUY."--area_code".$order_info['area_code'], $result, $order_info ['order_log'] );
            return - 9;//接口调用失败
        }
        $xstr = json_decode($result, true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            $dataarray = array (
                "api_id" => $xstr['info']['ywddh'],
                "api_ip_id" => $xstr['info']['ipddh'],
                "complete_time" => date('Y-m-d H:i:s')
            );
            // 修改订单表
            if ($order->order_edit ( $order_id, $dataarray )) {
                return - 1;
            } else {
                $dataarray = array (
                    "api_id" => $xstr['info']['ywddh'],
                    "complete_time" => date('Y-m-d H:i:s'),
                    "order_log" => $order_info ['order_log'] . "-接口调用成功，订单表修改失败"
                );
                $order->order_edit ( $order_id, $dataarray );
                return - 7; // 订单表修改失败
            }
        }else{
            // 调用接口试用失败修改订单表
            $dataarray = array (
                "complete_time" => date('Y-m-d H:i:s'),
                "order_log" => $order_info ['order_log'] . "-接口调用错误",
                "state" => \Common\Data\StateData::FAILURE_ORDER
            );
            $order->order_edit ( $result, $dataarray );
            // 修改订单表
            return $xstr['code'];
        }
    }
    
    /**
    * 快云服务器开通
    * @date: 2016年12月1日 上午10:06:39
    * @author: Lyubo
    * @param: $order_id,$member_id,$password
    * @return:
    */
    public function cloudserver_open($order_id, $member_id, $password ){
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
        $result_inf = array();
        $order_info = $order->order_find ( $order_id );
        if (! $order_info) {
            $result_inf['code'] = - 13; // 订单不存在或订单状态错误
            return $result_inf;
        }
        if($order_info['state'] != \Common\Data\StateData::PAYMENT_ORDER){
            if($order_info['state'] == \Common\Data\StateData::SUCCESS_ORDER){
                $result_inf['code'] = - 124; // 该订单已经开通
                return $result_inf;
            }
            if($order_info['state'] == \Common\Data\StateData::FAILURE_ORDER){
                $result_inf['code'] = - 125; // 订单开通失败
                return $result_inf;
            }
        }
        if ($order_info ['user_id'] != $member_id) {
            $result_inf['code'] = -101;
            return $result_inf;
        }
        // 获取订单产品信息
        $product_info = $product->get_product ( '214' );//快云服务器默认214
        if (! $product_info) {
            $result_inf['code'] = -2;
            return $result_inf;
        }
        /* if($order_info['api_ip_id'] == '0'){//快云服务器订单
            $order_info['api_ip_id'] = $order_info['api_bid'];
        } */
        //快云服务器业务表
        $cloudserver = array (
            "user_id" => $member_id,
            "login_name" => $order_info ['login_name'],
            "product_id" => $product_info ['id'],
            "product_name" => $product_info ['product_name'],
            "create_time" => current_date (),
            "buy_time" => $order_info ['order_time'],
            "free_trial" => $order_info ['free_trial'],
            "cloudserver_user"=>"快云服务器用户名",
            "cloudhost_password"=>$password,
            "des"=>"快云服务器描述",
            "state" => \Common\Data\StateData::NOTGET,//快云服务器开通成功，未获取业务信息状态：5
            "api_bid"=>$order_info['api_id'],
            "mail_state"=>"0",
            "area_code"=>$order_info['area_code'],
            "beizhu"=>null,
        );
        if (! $this->add ( $cloudserver )) {
            return -103;
        }else{
                $business_id = $this->getLastInsID(); // 获取最后插入ID
        }
        // 调用zzidc接口
        $format_id = 2; // 1返回json字符串2返回xml字符串 默认为1
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->cloudserver_open ( $product_info ['api_ptype'], $order_info ['api_id'],$password );
            api_log ( $member_id, "ptype:" . $product_info ['api_ptype'] . "--did:" . $order_info ['api_id'] . "--tid:" . GiantAPIParamsData::TID_OPEN . "--password" . $password, $result, $cloudserver ['note_appended'] );
        } catch ( \Exception $e ) {
            $del_where['id'] = $business_id;
            $this->where($del_where)->delete();
            $result_inf['code'] = -9;
            return $result_inf;
        }
        $xstr = json_decode($result, true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            // 修改订单表订单标识，业务编号
            $arr = array (
                "business_id" => $business_id,
                "complete_time" => current_date (),
                "state" => \Common\Data\StateData::HANDLE_ORDER,
                "order_log" => "业务正在队列开通,请到业务列表等待获取业务信息"
            );
            if ($order->order_edit ( $order_id, $arr ) !== false) {
                             $result_inf['code'] = "-1";
                            $result_inf['api_did'] = $order_info ['api_id'];
                            return $result_inf;
            } else {
                // 修改订单表状态
                $order->order_edit ( $order_id, array (
                				"state" => \Common\Data\StateData::HANDLE_ORDER,
                				"order_log" => "业务开通成功，业务表修改失败。"
                ) );
                return $result_inf['code'] = "-102"; // 业务执行成功，业务表修改失败
            }
        }else{
            $del_where['id'] = $business_id;
            $this->where($del_where)->delete();
            $result_inf['code'] = $xstr['code'];
            $result_inf['api_did'] = $order_info ['api_id'];
            return $result_inf;
        }
    }
    /**
    * 快云服务器续费
    * @date: 2016年12月4日 下午4:31:06
    * @author: Lyubo
    * @param: $cloudserver_id,$member_id,$renewalstime
    * @return: boolean
    */
    public function cloudserver_renewals($cloudserver_id,$member_id,$renewalstime){
        $order = new \Frontend\Model\OrderModel();
        $product= new \Frontend\Model\ProductModel();
        // 获取快云服务器业务信息
        $clouserver_server_where['id'] = ["eq",$cloudserver_id];
        $cloudserver_info = $this->where($clouserver_server_where)->find();
        if($cloudserver_info['ip_state'] == 0 && $cloudserver_info['ip_bid'] !=null){
            $ip = M("cloudserver_business_ip");
            $ip_where["api_bid"] = ["eq",$cloudserver_info['ip_bid']];
            $cloudserverip_info = $ip->where($ip_where)->find();
            $cloudserver_info['ipaddress'] = $cloudserverip_info['ipaddress'];
        }
        if (! $cloudserver_info || $cloudserver_info ['user_id'] != $member_id) {
            return - 101; // 快云服务器业务信息获取失败
        }
        if ($cloudserver_info ['state'] != \Common\Data\StateData::SUCCESS && $cloudserver_info ['state'] != \Common\Data\StateData::OVERDUE) {
            return - 10; // 业务失效
        }
        if ($cloudserver_info ['free_trial'] != 0) {
            return - 100; // 试用业务不能续费
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product( $cloudserver_info ['product_id']);
        if (! $product_info) {
            return - 2; // 产品信息获取失败
        }
        $cloudserver_info['mem']=$cloudserver_info['memory']/1024;
        $cloudserver_info['bandwidth'] = $cloudserverip_info['bandwidth'];
        $cloudserver_info['type'] = GiantAPIParamsData::TID_RENEWALS;
        $cloudserver_info['dqbh'] = $cloudserver_info['area_code'];
        $cloudserver_info['gmqx'] = 1;
        $cloud_price_one  = $this->CalculatedPrice($cloudserver_info);
        $cloudserver_info['gmqx'] = 12;
        $cloud_price_Twelve = $this->CalculatedPrice($cloudserver_info);
        // 生成订单表
        // 封装订单表数据
        $dataarray = array (
            "user_id" => $member_id,
            "order_type" => \Common\Data\StateData::RENEWALS_ORDER, // 续费
            "state" => \Common\Data\StateData::FAILURE_ORDER,
            "ip_address" => $cloudserver_info ['ipaddress'],
            "product_type" => $product_info ['product_type_id'],
            "product_id" => $product_info ['id'],
            "product_name" => $product_info ['product_name'],
            "free_trial" => 0, // 不是试用
            "order_time" => $renewalstime, // 续费12个月
            "order_quantity" => 1,
            "business_id" => $cloudserver_info ['id'],
            "create_time" => current_date (),
            "area_code" => $cloudserver_info['area_code'], // 4001国内4002香港
            "login_name" => $cloudserver_info ['login_name'],
            "complete_time" => current_date ()
        );
        $cloudserver_info['member_id'] = $member_id;
        $order_id = $order->cloud_order_do_entry ( $dataarray, $cloudserver_info, \Common\Data\StateData::RENEWALS_ORDER);
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
            $result = $transaction->cloudserver_renewals ( $product_info ['api_ptype'],GiantAPIParamsData::PNAME_CLOUDSERVER_SERVER, $cloudserver_info ['api_bid'],$renewalstime,$usetype);
            api_log ( $member_id, "ptype:" . $product_info ['api_ptype'] . "--bid:" . $cloudserver_info ['business_id'] . "--tid:" . GiantAPIParamsData::TID_RENEWALS, $result, "会员" . $member_id . "续费" . $product_info ['product_name'] );
        } catch ( \Exception $e ) {
            $dataarray = array (
                "state" => \Common\Data\StateData::FAILURE_ORDER, // 订单状态失败
                "complete_time" => current_date (),
                "api_id" => $cloudserver_info ['api_bid'],
                "order_log" => $order_info ['order_log'] . "--接口调用失败"
            );
            $order->order_edit ( $order_id,$dataarray);
            return - 9;
        }
        $xstr = json_decode($result, true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            // 续费成功，修改业务表信息
            $overdue_time = $cloudserver_info ['overdue_time'];
            $dataarray = array (
                "overdue_time" => $xstr['info']['overtime'],
                'buy_time' => $cloudserver_info ['buy_time'] + $renewalstime
            );
            $up_cloudserver_where['id'] = ["eq",$cloudserver_id];
            if ($this->where($up_cloudserver_where)->save($dataarray) !== false ) {
                // 业务表修改成功，修改订单表
                $dataarray = array (
                    "state" => \Common\Data\StateData::SUCCESS_ORDER,
                    "complete_time" => current_date (),
                    "api_id" => $cloudserver_info ['id'],
                    "order_log" => $order_info ['order_log'] . "--续费成功"
                );
                $order->order_edit ( $order_id,$dataarray);
                if($cloudserverip_info !=null){
                    $arr = array(
                        "overdue_time" => $xstr['info']['ipovertime'],
                        'buy_time' => $cloudserverip_info ['buy_time'] + $renewalstime
                    );
                    $up_ip_where['id'] = ["eq",$cloudserverip_info['id']];
                    if($ip->where($up_ip_where)->save($arr) === false){
                        api_log ( $member_id, "ptype:" . $product_info ['api_ptype'] . "--bid:" . $cloudserverip_info ['api_bid'] . "--tid:" . GiantAPIParamsData::TID_RENEWALS, $result, "会员" . $member_id . "续费快云服务器绑定IP失败"  );
                        return - 12;
                    }
                }
                return - 1; // 续费成功
            }
        }else{
            return $xstr['code'];
        }
    }
    /**
    * 获取快云服务器开通进度
    * @date: 2016年12月2日 上午11:58:05
    * @author: Lyubo
    * @param: $cloudserver_id
    * @return: business_code
    */
    public function get_business_info($cloudserver_id){
        $order =  new \Frontend\Model\OrderModel();
        $member_id = session("user_id");
        $where['id'] = ['eq',$cloudserver_id];
        $where['user_id'] = ['eq',$member_id];
        $cloudserver_info = $this->where($where)->find();
        $api_did = $cloudserver_info['api_bid'];//保存在主站的订单编号
        $order_where['api_id'] = ['eq',$api_did];
        $order_where['user_id'] = ['eq',$member_id];
        $order_info = $order->where($order_where)->find();//通过api_bid获取订单信息
        try{
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->cloudserver_getinfo ( GiantAPIParamsData::PTYPE_CLOUD_SERVER, GiantAPIParamsData::PNAME_CLOUDSERVER_SERVER,$api_did,GiantAPIParamsData::TID_GET_BUSINESS);
            api_log ( $member_id, "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER . "pname:" . GiantAPIParamsData::PNAME_CLOUDSERVER_SERVER . "--did:" . $api_did . "--tid:" . GiantAPIParamsData::TID_GET_BUSINESS, $result, "会员" . $member_id . "获取快云服务器开通进度"  );
        } catch ( \Exception $e ) {
            $msg = $e->getMessage();
            // 记录操作
            api_log ( $member_id, "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER . "pname:" . GiantAPIParamsData::PNAME_CLOUDSERVER_SERVER . "--did:" . $api_did . "--tid:" . GiantAPIParamsData::TID_GET_BUSINESS, $msg, "会员" . $member_id . "获取快云服务器开通进度失败"  );
            return - 9;
        }
        $xstr = json_decode($result, true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            if($xstr['info']['disk'] == "0"){
                $disk = $xstr['info']['ssddisk'];
            }else{
                $disk = $xstr['info']['disk'];
            }
            $cloudserver_business_arr = [
                'api_bid'=>$xstr['info']['ywbh'],//业务编号
                'free_trial'=>'0',
                'create_time'=>$xstr['info']['createDate'],//创建时间
                'overdue_time'=>$xstr['info']['overDate'],//结束时间
                'cloudserver_user'=>$xstr['info']['username'],//快云服务器账号
                'cloudhost_password'=>$xstr['info']['password'],//快云服务器密码
                'state'=>1,//默认为1，正常状态
                'nw_ip'=>$xstr['info']['nwip'],//内网IP
                'cpu'=>$xstr['info']['cpucount'],//CPU
                'memory' =>$xstr['info']['memory'],//内存
                'disk'=>$disk,//硬盘
                'area_code'=>$xstr['info']['server_areacode'],//地区编号
                'input_name'=>$xstr['info']['input_name'],//存放操作系统编号
                'os_type'=>$xstr['info']['ostype'],//存放操作系统名称
            ];
            //判断ip_ywbh是否存在，excep是不存在的标识
                if($xstr['info']['ip_ywbh'] != "excep"){
                    $cloudserver_business_ip_arr = [
                        'user_id'=>session("user_id"),//用户ID
                        'login_name'=>session("login_name"),//用户名称
                        'product_id'=>'227',
                        'free_trial'=>'0',
                        'state'=>1,//默认为1，正常状态
                        'api_bid'=>$xstr['info']['ip_ywbh'],//IP业务编号
                        'product_name'=>"快云服务器IP",//默认"快云服务器IP"
                        'create_time'=>$xstr['info']['createDate'],//创建时间
                        'overdue_time'=>$xstr['info']['overDate'],//结束时间
                        'bandwidth'=>$xstr['info']['bandwidth'],//带宽
                        'belong_server'=>$xstr['info']['ywbh'],//绑定服务器编号
                        'area_code'=>$xstr['info']['server_areacode'],//IP地区编号
                        'ipaddress'=>$xstr['info']['ipdz'],//IP地区编号
                    ];
                    $cloudserver_business_arr['ip_bid'] = $xstr['info']['ip_ywbh'];
                 $ip =M("cloudserver_business_ip");
               $cloudserver_ip_edit = $ip->add($cloudserver_business_ip_arr);
            }else{
                //IP状态：0为绑定成功，1为已绑定未成功，2，未绑定
                $cloudserver_business_arr['ip_state'] = '2';
                $cloudserver_business_arr['ip_bid'] = '0';
            }
            $up_cloudserver['id'] =  ["eq",$cloudserver_id];
            $cloudserver_edit = $this->where($up_cloudserver)->save($cloudserver_business_arr);
            if($cloudserver_edit === false){
                $ordet_data['order_log'] = $order_info['order_log']."服务器业务开通成功，业务表修改失败";
                $order->order_edit($order_info['order_id'], $ordet_data);
                return - 103;
            }
            if(!$cloudserver_ip_edit){
                $ordet_data['order_log'] = $order_info['order_log']."ip业务开通成功，业务表修改失败";
                $order->order_edit($order_info['order_id'], $ordet_data);
                return - 103;
            }
            $ordet_data['state'] = \Common\Data\StateData::STATE_ONLINE_TRAN_SUCCESS;
            $ordet_data['order_log'] = "业务开通成功";
            $ordet_data['business_id'] = $xstr['info']['ywbh'];
            $app = $order->order_edit($order_info['id'], $ordet_data);
            return -1;
        }else{
            return $xstr['code'];
        }
    }
    /**
    * 快云服务器解绑IP
    * @date: 2016年12月3日 下午5:58:04
    * @author: Lyubo
    * @param: $cloudserver_id
    * @return: $cloudserver_info
    */
    public function show_cloudserver_info($cloudserver_id){
        return $this->cloudserver_info($cloudserver_id);
    }
    /**
    * 快云服务器续费展示
    * @date: 2016年12月4日 下午2:32:00
    * @author: Lyubo
    * @param: $member_id,$cloudserver_id
    * @return:
    */
    public function show_cloudserver_renewals($member_id,$clouserver_id){
        $order = new \Frontend\Model\OrderModel();
        $product= new \Frontend\Model\ProductModel();
        // 获取快云服务器业务信息
        $clouserver_server_where['id'] = ["eq",$clouserver_id];
        $cloudserver_info = $this->where($clouserver_server_where)->find();
        if($cloudserver_info['ip_state'] == 0 && $cloudserver_info['ip_bid'] !=null){
            $ip = M("cloudserver_business_ip");
            $ip_where["api_bid"] = ["eq",$cloudserver_info['ip_bid']];
            $cloudserverip_info = $ip->where($ip_where)->find();
            $cloudserver_info['ipaddress'] = $cloudserverip_info['ipaddress'];
        }
        if (! $cloudserver_info || $cloudserver_info ['user_id'] != $member_id) {
            return - 101; // 快云服务器业务信息获取失败
        }
        if ($cloudserver_info ['state'] != \Common\Data\StateData::SUCCESS && $cloudserver_info ['state'] != \Common\Data\StateData::OVERDUE) {
            return - 10; // 业务失效
        }
        if ($cloudserver_info ['free_trial'] != 0) {
            return - 100; // 试用业务不能续费
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product( $cloudserver_info ['product_id']);
        if (! $product_info) {
            return - 2; // 产品信息获取失败
        }
        $cloudserver_info['mem']=$cloudserver_info['memory']/1024;
        $cloudserver_info['bandwidth'] = $cloudserverip_info['bandwidth'];
        $cloudserver_info['type'] = GiantAPIParamsData::TID_RENEWALS;
        $cloudserver_info['dqbh'] = $cloudserver_info['area_code'];
        $cloudserver_info['gmqx'] = 1;
        $cloud_price_one  = $this->CalculatedPrice($cloudserver_info);
        $cloudserver_info['gmqx'] = 12;
        $cloud_price_Twelve = $this->CalculatedPrice($cloudserver_info);
        /*获取购买期限*/
        //获取续费信息
        $renewals_info[0]=array('product_name'=>$cloudserver_info['product_name'],'id'=>$cloudserver_info['id'],'month'=>1,'product_price'=>$cloud_price_one);
        $renewals_info[1]=array('product_name'=>$cloudserver_info['product_name'],'id'=>$cloudserver_info['id'],'month'=>12,'product_price'=>$cloud_price_Twelve);
        $total_price[0]=array('price'=>$cloud_price_one,'month'=>1);
        $total_price[1]=array('price'=>$cloud_price_Twelve,'month'=>12);
        return array_merge($cloudserver_info,array('renewals_info'=>$renewals_info,'total_price'=>$total_price,'renewals_price_list'=>$renewals_info)); // 返回该业务详细信息和续费金额给确认续费页面
    }
    /**
    * 解绑快云服务器IP
    * @date: 2016年12月3日 下午7:14:35
    * @author: Lyubo
    * @param: $cloudserver_id,$ip_adderss,$cloudserver_api_bid,$cloudserver_ip_bid
    * @return: boolean
    */
    public function cloudserver_relieve($cloudserver_id,$cloudserver_ip_id,$ip_address,$cloudserver_api_bid,$cloudserver_ip_bid){
        $cloudserver_info = $this->show_cloudserver_info($cloudserver_id);
        if($cloudserver_info['ipaddress'] != $ip_address){
            return '5016';
        }
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->cloudserver_relieve (GiantAPIParamsData::PTYPE_CLOUD_SERVER , $cloudserver_api_bid,$cloudserver_ip_bid);
            api_log ( session("user_id"), "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER . "--bid:" . $cloudserver_api_bid . "ipdid:".$cloudserver_ip_bid."--tid:".GiantAPIParamsData::TID_IP_RELIEVE."--", $result, "快云服务器解绑IP" );
        } catch ( \Exception $e ) {
            api_log ( session("user_id"), "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER . "--bid:" . $cloudserver_api_bid . "ipdid:".$cloudserver_ip_bid."--tid:".GiantAPIParamsData::TID_IP_RELIEVE."--", $result, "快云服务器解绑IP" );
            return -9;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            $dataarray = array (
                "belong_server" =>'0'
            );
            $arr = array (
                "ip_state" => '2',
                "ip_bid" =>'0'
            );
            $up_where["id"] = ["eq",$cloudserver_id];
            if ($this->where($up_where)->save( $arr) !== false) {
                $ip = M("cloudserver_business_ip");
                $ip_where['id'] = ["eq",$cloudserver_ip_id];
                if ($ip->where($ip_where)->save($dataarray) === false){
                    return - 102;
                }
            }else{
                     //表修改失败
                    return - 102;
            }
            return  -1;
        }else{
            return $xstr['code'];
        }
    }
    /**
    * 快云服务器绑定IP
    * @date: 2016年12月4日 上午11:01:44
    * @author: Lyubo
    * @param: $cloudserver_id,$cloudserver_api_bid,$ip_address
    * @return: boolean
    */
    public function cloudserver_bound($cloudserver_id,$cloudserver_api_bid,$ip_address){
        $cloudserver_ip  = M("cloudserver_business_ip");
        $ip_where['ipaddress'] = ["eq",$ip_address];
        $cloudserver_ip_info = $cloudserver_ip->where($ip_where)->find();
        if(!$cloudserver_ip_info){
            return '5016';//快云服务器，ip不存在
        }
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->cloudserver_bound (GiantAPIParamsData::PTYPE_CLOUD_SERVER, $cloudserver_api_bid,$cloudserver_ip_info['api_bid']);
            api_log ( session("user_id"), "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER . "--bid:" . $cloudserver_api_bid . "ipdid:".$cloudserver_ip_info['api_bid']."--tid:.".GiantAPIParamsData::TID_IP_BOUND."--", $result, "快云服务器绑定IP" );
        } catch ( \Exception $e ) {
            $message = $e->getMessage();
            api_log ( session("user_id"), "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER . "--bid:" . $cloudserver_api_bid . "ipdid:".$cloudserver_ip_info['api_bid']."--tid:".GiantAPIParamsData::TID_IP_BOUND."--", $message, "快云服务器绑定IP失败" );
            return -9;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == '0'){
            $dataarray = array (
                "ip_state" => '1',
                "ip_bid"=> $cloudserver_ip_info['api_bid'],
            );
            $where["id"] = $cloudserver_id;
            if ($this->where($where)->save($dataarray) !== false) {
                //修改服务器表成功
                return -1;
            }
        }else{
            return $xstr['code'];
        }
    }
    /**
    * 获取IP绑定进度
    * @date: 2016年12月4日 下午12:06:49
    * @author: Lyubo
    * @param: $cloudserver_id,$ip_bid
    * @return:
    */
    public function ip_progress($cloudserver_id,$ip_bid){
        $ip = M("cloudserver_business_ip");
        $ip_where["api_bid"] = ["eq",$ip_bid];
        $cloudserver_where['id'] = ["eq",$cloudserver_id];
        $cloudserver_ip_info = $ip->where($ip_where)->find();
        $cloudserver_info = $this->where($cloudserver_where)->find();
        if(empty($cloudserver_ip_info)){
            return '5016';//快云服务器，ip不存在
        }
        try {
             $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->cloudserver_getip (  GiantAPIParamsData::PTYPE_CLOUD_SERVER, $cloudserver_info['api_bid'],$ip_bid);
            api_log ( session("user_id"), "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER . "--bid:" . $cloudserver_info['api_bid'] . "ipdid:".$ip_bid."--tid:".GiantAPIParamsData::TID_GET_BUSINESS, $result, "获取IP绑定进度" );
        } catch ( \Exception $e ) {
            $message = $e->getMessage();
            api_log ( session("user_id"), "ptype:" . GiantAPIParamsData::PTYPE_CLOUD_SERVER . "--bid:" .  $cloudserver_info['api_bid'] . "ipdid:".$ip_bid."--tid:".GiantAPIParamsData::TID_GET_BUSINESS, $message, "获取IP绑定进度" );
            return -9;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == '0'){
            $dataarray = array (
                "ip_state" => '0'
            );
            $arr = array (
                "belong_server" => $cloudserver_info["api_bid"]
            );
            $up_cloudserver_where['id'] = ["eq",$cloudserver_id];
            $up_cloudserver_ip_where["api_bid"] = ["eq",$ip_bid];
            if ($this->where($up_cloudserver_where)->save($dataarray) !== false) {
                if($ip->where($up_cloudserver_ip_where)->save($arr) === false){
                    //IP表修改失败
                    return - 102;
                }
            }else{
                //服务器表修改失败
                return - 102;
            }
            return -1;
        }else{
            return $xstr['code'];
        }
    }
    /**
    * 获取未开通业务
    * @date: 2016年12月6日 下午5:54:37
    * @author: Lyubo
    * @param: $cloudserver_info
    * @return: array
    */
    public function get_not_open($cloudserver_info){
        $business_info = [];
        $i = 0;
       foreach($cloudserver_info as $key=>$val){
           if($val['state'] == 5){
               $business_info[$i++] = $val['yid'];
           }
       }
       return $business_info;
    }
}