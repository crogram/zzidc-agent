<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2017/1/10
 * Time: 16:22
 */

namespace Frontend\Model;

use Common\Data\StateData;
use Frontend\Model\BusinessModel;
use Common\Aide\AgentAide;
use Common\Data\GiantAPIParamsData;

class ClouddbModel extends BusinessModel
{
    protected $trueTableName = 'agent_clouddb_business';

    public function conver_par(){
        $date =request();
        $where["bs.user_id"] = array("eq",session("user_id"));
        if(!empty($date['select'])){
            $select = clearXSS($date['select']);
            $map['bs.api_bid'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map['bs.ywbs'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map["bs.beizhu"] = array("like" ,"%$select%");
            $where['_complex'] = $map;
        }
        return $where;
    }

    /**
     * 计算快云数据库价格
     * @author: Guopeng
     * @param: $data
     * @return: $price
     */
    public function getPrice($data){
        $product = new \Frontend\Model\ProductModel();
        $where["type"] = $data['istry'];
        $where["area_code"] = $data['dqbh'];//机房：4001:郑州机房，4002:香港机房
        $memsize = $data['memsize']+0;
        if(empty($memsize)){
            $this->setError("内存空间大小错误！");
            return false;
        }
        if(isset($data['old_memsize'])){
            $old_memsize = $data['old_memsize']+0;
            if($memsize >= $old_memsize || !empty($old_memsize)){
                $memsize = $memsize - $old_memsize;
            }else{
                $this->setError("内存空间大小错误！");
                return false;
            }
        }
        $disk = $data['spacesize']+0;
        if(empty($disk)){
            $this->setError("存储空间大小错误！");
            return false;
        }
        if(isset($data['old_spacesize'])){
            $old_disk = $data['old_spacesize']+0;
            if($disk >= $old_disk || !empty($old_disk)){
                $disk = $disk - $old_disk;
            }else{
                $this->setError("存储空间大小错误！");
                return false;
            }
        }
        if($data["config"] == "dx"){
            $where["product_id"] = 306;
            /*获取内存价格*/
            $where['note_appended'] = ['eq',"clouddb.dx.nc.price"];
            $price_mem = $product->get_database_price($where);
            if(!$price_mem){
                $this->setError("获取内存价格错误！");
                return false;
            }
            $database_mem_price = $price_mem['product_price'] * $memsize / 10;
            $where['note_appended'] = ['eq',"clouddb.dx.yp.price"];
        }elseif($data["config"] == "gx"){
            $where["product_id"] = 305;
            $database_mem_price = 0;
            $where['note_appended'] = ['eq',"clouddb.gx.yp.price"];
        }else{
            return false;
        }
        /*获取数据盘价格*/
        $price_disk = $product->get_database_price($where);
        if(!$price_disk)
        {
            $this->setError("获取硬盘价格错误！");
            return false;
        }
        $database_disk_price = $price_disk['product_price'] * $disk;
        if ($data['gmqx'] == 12) {
            $data['gmqx'] = 10;
        } elseif ($data['gmqx'] == 24) {
            $data['gmqx'] = 20;
        } elseif ($data['gmqx'] == 36) {
            $data['gmqx'] = 30;
        }
        $data['count'] ='1';
        $gmqx = $data['gmqx']+0;
        $cloud_price = ($database_disk_price + $database_mem_price) * $gmqx;
        return $cloud_price;
    }

    /**
     * 会员购买快云数据库
     * @date: 2016年11月26日 下午4:04:36
     * @author: Lyubo
     * @return:
     */
    public function clouddb_order_buy($data){
        $price = $data['price']+0;
        if(empty($price)){
            $this->setError("价格异常，请刷新页面重新购买！");
            return false;
        }
        $user_id = session("user_id");
        $login_name = session("login_name");
        $free_trial = $data["istry"]+0;
        $order_time = $data['gmqx']+0;
        $area_code = $data["dqbh"]+0;
        $api_ptype = clearXSS($data['api_ptype']);
        $version = clearXSS($data['version']);
        if($data["config"] == "dx"){
            $product_id = 306;
        }elseif($data["config"] == "gx"){
            $product_id = 305;
        }else{
            $this->setError(0);
            return false;
        }
        if (strcmp($api_ptype,GiantAPIParamsData::PTYPE_CLOUD_DATABASE) == 0)
        {
            // 封装订单参数信息
            $datarray = array (
                "user_id" => $user_id,
                "login_name"=> $login_name,
                'product_type' => "22",
                'product_id' => $product_id,
                'product_name' => "快云数据库",
                "free_trial" => $free_trial, // 购买或者试用
                "order_time" => $order_time,
                "order_type" => StateData::NEW_ORDER,
                "create_time" => date('Y-m-d H:i:s'),
                "complete_time" => date('Y-m-d H:i:s'), // 完成时间
                "state" => StateData::FAILURE_ORDER,  // 订单状态   成功
                "area_code"=>$area_code,//地区编号
                "order_quantity" => 1,
                "system_type" => $version,
            );
            //执行录入订单、购买方法
            $order =  new \Frontend\Model\OrderModel();
            $order_id = $order->clouddb_order_do_entry($user_id,$datarray,$data,StateData::NEW_ORDER);
            if ($order_id < 0) {//小于0就是错误返回信息
                $this->setError($order_id);
                return false;
            }
            return $this->api_clouddb_buy($order_id ,$data);
        }else{
            $this->setError(-2);
            return false;
        }
    }
    /**
     * 快云数据库购买接口调用
     * @author: Guopeng
     * @param $order_id
     * @param $buy_info
     * @return int
     */
    public function api_clouddb_buy($order_id,$buy_info){
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        $system = WebSiteConfig();
        $usetype = $system["site_buy_cloud"];
        try {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            // 购买
            $result = $transaction->clouddb_buy(GiantAPIParamsData::PTYPE_CLOUD_DATABASE,$order_info['order_time'],$order_info['area_code'],$buy_info,$usetype);
            api_log($order_info['user_id'],"ptype:".GiantAPIParamsData::PTYPE_CLOUD_DATABASE."--tid:".GiantAPIParamsData::TID_BUY."--area_code:".$order_info['area_code'],$result,$order_info['order_log']);
        } catch ( \Exception $e ) {
            // 调用接口试用失败修改订单表
            $dataarray = array (
                "complete_time" => date('Y-m-d H:i:s'),
                "order_log" => $order_info ['order_log'] ."-接口调用错误",
                "state" => StateData::FAILURE_ORDER
            );
            $order->order_edit ( $order_id, $dataarray );
            api_log($order_info ['user_id'],"ptype:".GiantAPIParamsData::PTYPE_CLOUD_DATABASE."--tid:".GiantAPIParamsData::TID_BUY."--area_code:".$order_info['area_code'],$e->getMessage(),$order_info ['order_log']);
            $this->setError(-9);
            return false;//接口调用失败
        }
        $xstr = json_decode($result, true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            $dataarray = array (
                "api_id" => $xstr['info']['did'],
                "complete_time" => date('Y-m-d H:i:s'),
                "state" => StateData::PAYMENT_ORDER
            );
            // 修改订单表
            if ($order->order_edit($order_id,$dataarray )){
                return true;
            } else {
                $dataarray = array (
                    "api_id" => $xstr['info']['did'],
                    "complete_time" => date('Y-m-d H:i:s'),
                    "order_log" => $order_info ['order_log'] . "-接口调用成功，订单表修改失败",
                    "state" => StateData::FAILURE_ORDER
                );
                $order->order_edit($order_id,$dataarray );
                $this->setError(-7);
                return false; // 订单表修改失败
            }
        }else{
            // 调用接口试用失败修改订单表
            $dataarray = array (
                "complete_time" => date('Y-m-d H:i:s'),
                "order_log" => $order_info ['order_log'] . "-接口调用错误",
                "state" => StateData::FAILURE_ORDER
            );
            $order->order_edit($order_id,$dataarray);
            // 修改订单表
            $this->setError($xstr['code']);
            return false;
        }
    }
    /**
     * 快云数据库开通
     * @author: Guopeng
     * @param $order_id
     * @param $user_id
     * @param $data
     * @return array|string
     */
    public function clouddb_open($order_id, $user_id, $data ){
        $product_id = $data["product_id"]+0;
        $open_info["name"] = clearXSS($data['db_name']);
        $open_info["user"] = clearXSS($data['db_user']);
        $open_info["password"] = clearXSS($data['db_psd']);
        $open_info["confirm_password"] = clearXSS($data['db_rpsd']);
        $open_info["type"] = clearXSS($data['db_type']);
        if(!preg_match("/^[a-z]{1}\w{0,63}$/",$open_info["name"]) || empty($open_info["name"]) || is_null($open_info["name"])){
            $this->setError("数据库名格式不正确（由小写字母、数字、下划线组成，字母开头，字母或数字结尾，最长64个字符）");
            return false;
        }elseif(!preg_match("/^[a-z]{1}\w{0,13}$/",$open_info["user"]) || empty($open_info["user"]) || is_null($open_info["user"])){
            $this->setError("用户名格式不正确（由小写字母、数字、下划线组成，字母开头，字母或数字结尾，最长14个字符）");
            return false;
        }elseif(!preg_match("/^\w{6,32}$/",$open_info["password"]) || empty($open_info["password"]) || is_null($open_info["password"])){
            $this->setError("密码格式不正确（密码必须是字母、数字或下划线组成，长度6~32位）");
            return false;
        }elseif(strpos($open_info["password"],$open_info["confirm_password"]) !== 0){
            $this->setError("两次输入密码不一致");
            return false;
        }
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
        $order_info = $order->order_find($order_id);
        if (! $order_info) {
            $this->setError(-13);
            return false;
        }
        if($order_info['state'] != StateData::PAYMENT_ORDER){
            if($order_info['state'] == StateData::SUCCESS_ORDER){
                $this->setError(-124);
                return false;
            }
            if($order_info['state'] == StateData::FAILURE_ORDER){
                $this->setError(-125);
                return false;
            }
        }
        if ($order_info['user_id'] != $user_id){
            $this->setError(-101);
            return false;
        }
        // 获取订单产品信息
        $product_info = $product->get_product($product_id);//快云数据库默认214
        if (!$product_info){
            $this->setError(-2);
            return false;
        }
        //快云数据库业务表
        $clouddb = array (
            "user_id" => $user_id,
            "login_name" => $order_info['login_name'],
            "product_id" => $product_info['id'],
            "product_name" => $product_info['product_name'],
            "create_time" => current_date(),
            "buy_time" => $order_info['order_time'],
            "overdue_time" => add_dates(current_date(),$order_info['order_time']),
            "free_trial" => $order_info['free_trial'],
            "des"=>"快云数据库描述",
            "state" => StateData::NOTGET,//快云数据库开通成功，未获取业务信息状态：5
            "api_bid"=>$order_info['api_id'],
            "mail_state"=>"0",
            "version" => $order_info['system_type'],//快云数据库产品名称
            "area_code"=>$order_info['area_code'],
            'note_appended'=>"会员编号:".$user_id."-开通-".$product_info['product_name']."-订单编号：".$order_id
        );
        $business_id = $this->add($clouddb);
        if (!$business_id){
            $this->setError(-103);
            return false;
        }
        // 调用zzidc接口
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try {
            $result = $transaction->clouddb_open($product_info['api_ptype'],$order_info['api_id'],$open_info);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--did:".$order_info['api_id']."--tid:".GiantAPIParamsData::TID_OPEN,$result,$clouddb['note_appended']);
        } catch ( \Exception $e ) {
            $del_where['id'] = $business_id;
            $this->where($del_where)->delete();
            api_log($user_id,"ptype:".$product_info['api_ptype']."--did:".$order_info['api_id']."--tid:".GiantAPIParamsData::TID_OPEN,$e->getMessage(),$clouddb['note_appended']);
            $this->setError(-9);
            return false;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            // 修改订单表订单标识，业务编号
            $arr = array (
                "business_id" => $business_id,
                "state" => StateData::HANDLE_ORDER,
                "complete_time" => current_date(),
                "order_log" => "业务正在队列开通,请到业务列表等待获取业务信息"
            );
            if ($order->order_edit($order_id,$arr) !== false){
                return true;
            } else {
                // 修改订单表状态
                $order->order_edit($order_id,array(
                    "state" => StateData::HANDLE_ORDER,
                    "order_log" => "业务开通成功，订单表修改失败。"
                ));
                $this->setError(-102);
                return false; // 业务执行成功，业务表修改失败
            }
        }else{
            $del_where['id'] = $business_id;
            $this->where($del_where)->delete();
            $this->setError($xstr['code']);
            return false;
        }
    }
    /**
     * 获取未开通业务
     * @author: Guopeng
     * @param: $clouddb_info
     * @return: array
     */
    public function get_not_open($clouddb_info,$state){
        $business_info = [];
        $i = 0;
        foreach($clouddb_info as $key=>$val){
            if($val['state'] == $state){
                $business_info[$i++] = $val['yid'];
            }
        }
        return $business_info;
    }
    /**
     * 获取快云数据库开通进度
     * @author: Guopeng
     * @param $clouddb_id
     * @return int
     */
    public function get_business_info($clouddb_id){
        $order =  new \Frontend\Model\OrderModel();
        $member_id = session("user_id");
        $where['id'] = ['eq',$clouddb_id];
        $where['user_id'] = ['eq',$member_id];
        $clouddb_info = $this->where($where)->find();
        $api_did = $clouddb_info['api_bid'];//保存在主站的订单编号
        $order_where['api_id'] = ['eq',$api_did];
        $order_where['user_id'] = ['eq',$member_id];
        $order_info = $order->where($order_where)->find();//通过api_bid获取订单信息
        try{
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->clouddb_getinfo(GiantAPIParamsData::PTYPE_CLOUD_DATABASE,$api_did,GiantAPIParamsData::TID_GET_BUSINESS);
            api_log($member_id,"ptype:".GiantAPIParamsData::PTYPE_CLOUD_DATABASE."--did:".$api_did."--tid:".GiantAPIParamsData::TID_GET_BUSINESS,$result,"会员".$member_id."获取快云数据库开通进度");
        } catch ( \Exception $e ) {
            // 记录操作
            api_log($member_id,"ptype:".GiantAPIParamsData::PTYPE_CLOUD_DATABASE."--did:".$api_did."--tid:".GiantAPIParamsData::TID_GET_BUSINESS,$e->getMessage(),"会员".$member_id."获取快云数据库开通进度失败");
            $this->setError(-9);
            return false;
        }
        $xstr = json_decode($result, true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            $mem = $xstr['info']['memory'];
            if($mem == 240){
                $conn = 60;$iops = 320;
            }elseif($mem == 600){
                $conn = 150;$iops = 640;
            }elseif($mem == 1200){
                $conn = 300;$iops = 1300;
            }elseif($mem == 2400){
                $conn = 600;$iops = 3500;
            }elseif($mem == 6000){
                $conn = 1500;$iops = 7000;
            }elseif($mem == 12000){
                $conn = 2000;$iops = 12000;
            }
            $clouddb_business_arr = [
                'api_bid'=>$xstr['info']['ywbh'],//业务编号
                'create_time'=>$xstr['info']['createDate'],//创建时间
                'overdue_time'=>$xstr['info']['overDate'],//结束时间
                'state'=>1,//默认为1，正常状态
                'ywbs'=>$xstr['info']['slmc'],//业务标识
                'wwdz'=>$xstr['info']['atlas_wan1'],//外网地址
                'nwdz'=>$xstr['info']['atlas1vps'],//内网地址
                'memory' =>$xstr['info']['memory'],//内存
                'disk'=>$xstr['info']['disk'],//硬盘
                'conn'=>$conn,//最大连接数
                'iops'=>$iops,//IOPS
            ];
            $up_clouddb['id'] = ["eq",$clouddb_id];
            $clouddb_edit = $this->where($up_clouddb)->save($clouddb_business_arr);
            if($clouddb_edit === false){
                $ordet_data['order_log'] = $order_info['order_log']."数据库业务开通成功，业务表修改失败";
                $order->order_edit($order_info['order_id'], $ordet_data);
                $this->setError(-103);
                return false;
            }
            $ordet_data['state'] = StateData::STATE_ONLINE_TRAN_SUCCESS;
            $ordet_data['order_log'] = "业务开通成功";
            $ordet_data['business_id'] = $xstr['info']['ywbh'];
            $app = $order->order_edit($order_info['id'], $ordet_data);
            return true;
        }elseif($xstr['code'] == 5021){
            return 0;
        }else{
            $this->setError($xstr['code']);
            return false;
        }
    }

    /**
     * 获取快云数据库详细信息
     * @author: Guopeng
     * @param: $id 业务ID
     * @return mixed
     */
    public function clouddb_info($id)
    {
        $field =
            "bs.id as yid,bs.user_id,bs.login_name,bs.ywbs,bs.product_id,bs.product_name,
            bs.create_time,bs.overdue_time,bs.buy_time,bs.free_trial,bs.wwdz,bs.nwdz,bs.version,
            bs.memory,bs.disk,bs.iops,bs.conn,bs.state,bs.api_bid,bs.mail_state,bs.beizhu,
            bs.area_code,p.product_type_id,pt.api_ptype,p.api_name";
        $where = array("bs.id" => $id);
        return $this->alias('bs')->field($field)->where($where)
            ->join('left join '.C('DB_PREFIX').'product as p on p.id = bs.product_id')
            ->join('inner join '.C('DB_PREFIX').'product_type as pt on p.product_type_id= pt.id ')
            ->find();
    }

    /**
     * 快云数据库续费业务
     * @author: Guopeng
     * @param: $user_id 用户id
     * @param: $yw_id 业务id
     * @param: $order_time 续费时间(月)
     * @param: null $method 显示/业务
     * @return array|int
     */
    public function clouddb_renewals($user_id,$yw_id,$data_info,$method = null)
    {
        $product = new \Frontend\Model\ProductModel();
        $order = new \Frontend\Model\OrderModel();
        // 获取业务信息
        $clouddb_info = $this->clouddb_info($yw_id);
        if($clouddb_info ['user_id'] != $user_id)
        {
            return -101; // 业务不属于该会员或不存在
        }
        if(!$clouddb_info || $clouddb_info ['state'] != 1 && $clouddb_info['state'] != 3)
        {
            return -10; // 获取不到业务，或业务状态错误
        }
        if($clouddb_info['free_trial'] != 0)
        {
            return -100; // 试用业务不能执行此操作
        }
        // 根据产品编号获取产品信息
        $product_info = $product->get_product($clouddb_info['product_id']);
        $product_type_info = $product->get_product_type_info($product_info['product_type_id']);
        if(!$product_info)
        {
            return -2; // 产品信息获取失败
        }
        $clouddb_info["month"] = app_month($clouddb_info["overdue_time"]);
        if($method == 'get')
        {
            // 获取续费信息
            return $clouddb_info;
        }
        $order_time = $data_info["gmqx"];
        // 封装订单表数据
        $dataarray = array (
            "user_id" => $user_id,
            "order_type" => StateData::RENEWALS_ORDER, // 续费
            "state" => StateData::FAILURE_ORDER,
            "product_type" => $product_info['product_type_id'],
            "product_id" => $product_info['id'],
            "product_name" => $product_info['product_name'],
            "free_trial" => $clouddb_info['free_trial'], // 不是试用
            "order_time" => $order_time, // 续费12个月
            "order_quantity" => 1,
            "business_id" => $clouddb_info['yid'],
            "create_time" => current_date(),
            "area_code" => $clouddb_info["area_code"], // 4001国内4002香港
            "login_name" => $clouddb_info['login_name'],
            "complete_time" => current_date(),
            "system_type"=>$clouddb_info["system_type"],
            'order_log' => '会员'.$user_id."续费".$clouddb_info['product_name']
        );
        // 生成订单表
        $order_id = $order->clouddb_order_do_entry($user_id,$dataarray,$data_info,$dataarray["order_type"]);
        if($order_id <= 0){
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order->order_find($order_id);
        $system = WebSiteConfig();
        $usetype = $system["site_buy_cloud"];
        // 调用zzidc接口
        try
        {
            $agent = new AgentAide();
            $transaction = $agent->servant;
            $result = $transaction->renewals($product_info['api_ptype'],$clouddb_info['api_bid'],$order_time,$usetype);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$clouddb_info['api_bid']."--time:".$order_time,$result,$order_info['order_log']);
        }catch(\Exception $e){
            // 调用接口试用失败修改订单表
            $dataarray = array(
                "complete_time" => current_date(),
                "order_log" => $order_info['order_log']."--接口调用失败",
                "state" => StateData::FAILURE_ORDER); // 订单状态失败
            $order->order_edit($order_id,$dataarray);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$clouddb_info['api_bid']."--time:".$order_time,$e->getMessage(),$order_info['order_log']."--接口调用失败");
            return -9;//接口调用失败
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null)
        {
            // 续费成功，修改业务表信息
            if($xstr["info"]["overDate"] > $clouddb_info['create_time'])
            {
                $datarray = array (
                    "state" => StateData::SUCCESS_ORDER,
                    'note_appended' => $clouddb_info['note_appended'].'||'.current_date().'成功续费'.$order_time.'个月',
                    "overdue_time" => $xstr["info"]["overDate"],
                    'buy_time' => $clouddb_info ['buy_time'] + $order_time
                );
            }else{
                $datarray = array (
                    'note_appended' => $clouddb_info['note_appended'].'||'.current_date().'成功续费'.$order_time.'个月',
                    "overdue_time" => $xstr["info"]["overDate"],
                    'buy_time' => $clouddb_info ['buy_time'] + $order_time
                );
            }
            if($this->business_edit($clouddb_info['yid'],$datarray))
            {
                // 业务表修改成功，修改订单表
                $datarray = array (
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date(),
                    'note_appended' => '会员'.$user_id.'||'.current_date().'成功续费'.$order_time.'个月',
                    "order_log" => $order_info['order_log'] . "--续费成功"
                );
                $order->order_edit($order_id,$datarray);
                return -1; // 续费成功
            }else{
                // 业务表修改失败，修改订单表
                $datarray = array (
                    "state" => StateData::SUCCESS_ORDER,
                    "complete_time" => current_date(),
                    "order_log" => $order_info ['order_log'] . "--续费成功,业务表修改失败"
                );
                $order->order_edit($order_id,$datarray);
                return -102; // 续费成功，业务表修改失败
            }
        }else{
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
     * 快云数据库升级
     * @author: Guopeng
     * @param: $user_id 会员编号
     * @param: $yw_id 业务编号
     * @param: $up_product_id 升级后的产品id
     * @param: null $method 显示/业务
     * @return int
     */
    public function clouddb_uplevel($user_id,$yw_id,$up_info,$method = null)
    {
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        // 获取快云数据库业务信息
        $clouddb_info = $this->clouddb_info($yw_id);
        if(!$clouddb_info || $clouddb_info['user_id'] != $user_id)
        {
            return -101;// 业务不存在或业务不属于该会员
        }
        if($clouddb_info['state'] != StateData::SUCCESS && $clouddb_info['state'] != 3)
        {
            return -10; // 业务失效
        }
        if($clouddb_info['free_trial'] != 0)
        {
            return -100; // 试用业务不能续费
        }
        $clouddb_info["month"] = app_month($clouddb_info["overdue_time"]);
        if(strcmp($method,'get') == 0)
        {
            //获取升级信息
            return $clouddb_info;
        }
        $product_info = $product_service->get_product($clouddb_info['product_id']);
        if(!$product_info)
        {
            return -2; // 升级产品信息获取失败
        }
        // 封装订单表参数
        $dataarray = array(
            'user_id' => $user_id,
            'order_type' => StateData::CHANGE_ORDER, // 定单类型：0新增、1增值、2续费,3变更方案,转正
            'state' => StateData::FAILURE_ORDER,
            'product_type' => $product_info['product_type_id'],
            'product_id' => $product_info['id'],
            'product_name' => $product_info['product_name'],
            'free_trial' => $clouddb_info['free_trial'],
            'order_time' => $clouddb_info['month'],
            'order_quantity' => 1,
            'business_id' => $clouddb_info['yid'],
            "api_id" => $clouddb_info["api_bid"],
            'create_time' => current_date(),
            "complete_time" => current_date(), // 完成时间
            "area_code" => $clouddb_info["area_code"], // 4001国内4002香港
            'login_name' => $clouddb_info['login_name'],
            'order_log' => '会员'.$user_id."升级".$clouddb_info['product_name'],
            "system_type" => $clouddb_info["version"]
        );
        $product_type_info["old_product_id"] = $clouddb_info["product_id"];
        $order_id = $order_service->clouddb_order_do_entry($user_id,$dataarray,$up_info,$dataarray['order_type']);
        if($order_id <= 0)
        {
            return $order_id;
        }
        // 获取订单详情
        $order_info = $order_service->order_find($order_id);
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try
        {
            $data["memory"] = $up_info["memsize"];
            $data["disk"] = $up_info["spacesize"];
            $result = $transaction->clouddb_upgrade($product_info['api_ptype'],$clouddb_info['api_bid'],$data);
            // 插入日志记录
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$clouddb_info['api_bid']."--tid:".GiantAPIParamsData::TID_UPGRADE."--memory:".$up_info["memsize"]."--disk:".$up_info["spacesize"],$result,$order_info['order_log']);
        }catch(\Exception $e){
            // 升级失败
            $datarray = array(
                'state' => StateData::FAILURE_ORDER,
                'order_log' => $order_info['order_log'].'业务升级失败,接口连接失败',
                'complete_time' => current_date()
            );
            $order_service->order_edit($order_id,$datarray);
            api_log($user_id,"ptype:".$product_info['api_ptype']."--bid:".$clouddb_info['api_bid']."--tid:".GiantAPIParamsData::TID_UPGRADE."--memory:".$up_info["memsize"]."--disk:".$up_info["spacesize"],$e->getMessage(),$order_info["order_log"]."--接口调用失败");
            return -9;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null)
        {
            // 修改快云数据库业务表
            $dataarray = array('state' => 6);
            if($this->business_edit($clouddb_info['yid'],$dataarray))
            {
                // 加入升级队列成功
                $datarray = array(
                    'state' => StateData::HANDLE_ORDER,
                    'order_log' => $order_info['order_log'].'--业务正在升级',
                    'complete_time' => current_date()
                );
                $order_service->order_edit($order_id,$datarray);
                return -1;
            }
        }else{
            // 升级失败
            $datarray = array(
                'state' => StateData::FAILURE_ORDER,
                'order_log' => $order_info['order_log'].'--业务升级失败--接口调用错误',
                'complete_time' => current_date()
            );
            $order_service->order_edit($order_id,$datarray);
            return $xstr['code'];
        }
    }

    public function get_uplevel_progress($user_id,$yw_id)
    {
        //查看升级状态
        $order_service = new \Frontend\Model\OrderModel();
        $product_service = new \Frontend\Model\ProductModel();
        // 获取快云数据库业务信息
        $clouddb_info = $this->clouddb_info($yw_id);
        if(!$clouddb_info || $clouddb_info['user_id'] != $user_id)
        {
            $this->setError(-101);
            return false;// 业务不存在或业务不属于该会员
        }
        $clouddb_info["month"] = app_month($clouddb_info["overdue_time"]);
        $product_info = $product_service->get_product($clouddb_info['product_id']);
        if(!$product_info)
        {
            $this->setError(-2);
            return false; // 升级产品信息获取失败
        }
        $where["business_id"] = $yw_id;
        $where["user_id"] = $user_id;
        $where["api_id"] = $clouddb_info["api_bid"];
        $where["state"] = StateData::HANDLE_ORDER;
        $order_info = $order_service->queryBuilder($where,"")->find();
        if(!$order_info){
            $this->setError(-2);
            return false; // 升级产品信息获取失败
        }
        $order_id = $order_info["id"];
        $des = '会员:'.$user_id."-".$clouddb_info['product_name']."-业务编号:".$clouddb_info["api_bid"]."-获取升级进度";
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try
        {
            $result = $transaction->clouddb_getupinfo(GiantAPIParamsData::PTYPE_CLOUD_DATABASE,$clouddb_info['api_bid'],GiantAPIParamsData::TID_CLOUDDB_UPLEVEL);
            // 插入日志记录
            api_log($user_id,"ptype:".GiantAPIParamsData::PTYPE_CLOUD_DATABASE."--bid:".$clouddb_info['api_bid']."--tid:".GiantAPIParamsData::TID_CLOUDDB_UPLEVEL,$result,$des);
        }catch(\Exception $e){
            // 查看升级状态失败
            api_log($user_id,"ptype:".GiantAPIParamsData::PTYPE_CLOUD_DATABASE."--bid:".$clouddb_info['api_bid']."--tid:".GiantAPIParamsData::TID_CLOUDDB_UPLEVEL,$e->getMessage(),$des."--接口调用失败");
            $this->setError("获取升级进度信息失败！");
            return false;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            // 修改快云数据库业务表
            $dataarray = array(
                "state" => StateData::SUCCESS,
                'version'=>$xstr['info']['version'],//创建时间
                'memory' =>$xstr['info']['memory'],//内存
                'disk'=>$xstr['info']['disk'],//硬盘
                'conn'=>$xstr['info']['conn'],//最大连接数
                'iops'=>$xstr['info']['iops'],//iops
                'product_id' => $product_info['id'],
                'product_name' => $product_info['product_name'],
                'note_appended' => $clouddb_info['note_appended'].'||'.current_date().'成功升级到'.$product_info['product_name']
            );
            if($this->business_edit($clouddb_info['yid'],$dataarray))
            {
                $datarray = array(
                    'state' => StateData::SUCCESS_ORDER,
                    'note_appended' => '会员'.$user_id.'|'.current_date().'成功升级到'.$product_info['product_name'],
                    'order_log' => $order_info['order_log'].'--业务升级成功',
                    'complete_time' => current_date()
                );
                $order_service->order_edit($order_id,$datarray);
                return true;
            }else{
                $datarray = array(
                    'order_log' => $order_info['order_log'].'--业务升级成功,业务信息修改失败',
                    'complete_time' => current_date()
                );
                $order_service->order_edit($order_id,$datarray);
                $this->setError(-102);
                return false;
            }
        }elseif($xstr['code'] == 5055){
            return 0;
        }else{
            $this->setError($xstr['code']);
            return false;
        }
    }
}