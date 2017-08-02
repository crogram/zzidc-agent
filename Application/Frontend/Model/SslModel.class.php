<?php
namespace Frontend\Model;

use Common\Aide\AgentAide;
use Common\Data\GiantAPIParamsData;
use Common\Data\StateData;
use Frontend\Model\BusinessModel;

class SslModel extends BusinessModel
{
    protected $trueTableName = 'agent_ssl_business';

    public function conver_par()
    {
        $date = request();
        $where["bs.user_id"] = array("eq",session("user_id"));
        $select = clearXSS(I('post.select'));
        if(!empty($select))
        {
            $map['bs.business_id'] = array("like","%$select%");
            $map['_logic'] = 'OR';
            $map['bs.domain_name'] = array("like","%$select%");
            $map['_logic'] = 'OR';
            $map['bs.product_name'] = array("like","%$select%");
            $map['_logic'] = 'OR';
            $map["bs.beizhu"] = array("like","%$select%");
            $where['_complex'] = $map;
        }
        return $where;
    }

    /**
     * ----------------------------------------------
     * | 计算ssl证书的购买的价格
     * | @时间: 2016年12月27日 下午6:20:17
     * | @author: duanbin
     * | @param $buy_info
     * | @param $product_info
     * | @param $ssl_extra
     * | @return int
     * ----------------------------------------------
     */
    /**
     * @param $buy_info
     * @param $product_info
     * @param $ssl_extra
     * @return int
     */
    public function computeTotalPrice($buy_info, $product_info, $ssl_extra){
    	//总价
    	$aggregation_price = 0;
    	//需要的参数
    	$product_id = $buy_info['product_id'];
    	$current_price = $buy_info['price_info']['product_price'];
    	$current_years = $buy_info['price_info']['month']/12;
    	$current_mutil_server = $ssl_extra['mutil_server'];
    	$current_global_domain = $ssl_extra['global_domain'];
    	$current_mutil_domain = $ssl_extra['mutil_domain'];
    	//多域名价格的单价
    	$mutil_domain_step_id = $ssl_extra['mutil_domain_step_id'];
    	$m_product_price = M('product_price');
    	$mutil_domain_step = $m_product_price->where([ 'product_id' => [ 'eq', $mutil_domain_step_id ], 'month' => [ 'eq', 12 ] ])->find();
    	$step = $mutil_domain_step['product_price'];
    	//计算总价格
    	if ($current_mutil_server > 0) {
    		$aggregation_price = ( $current_price + $step * $current_mutil_domain * $current_years ) * ($current_mutil_server + 1);
    	}else if ($current_global_domain > 0) {
    		$aggregation_price = $current_price * $current_global_domain * 3 - 6 + $step * $current_mutil_domain * $current_years;
    	}else if ($current_mutil_domain >=1){
    		$aggregation_price = $current_price + $step * $current_mutil_domain * $current_years;
    	}else {
    		$aggregation_price = $current_price;
    	}
    	return $aggregation_price;
    }
    
    /**
     * 购买/试用ssl
     * @date: 2016年11月18日
     * @author: Guopeng
     * @param: $buy_info，购买参数
     * @param: $product_info,产品信息
     * @param $product_type_info
     * @param $ssl_extra
     * @return int
     */
    public function  ssl_buy($buy_info,$product_info,$product_type_info, $ssl_extra)
    {
        if($ssl_extra['mutil_domain'] > 0 && $ssl_extra['global_domain'] > 0){
            return -2;
        }
        // 封装订单参数信息
        $price = $this->computeTotalPrice($buy_info, $product_info, $ssl_extra);
        $dataarray = array(
            "product_id" => $buy_info['product_id'],
            "user_id" => $buy_info['member_id'],
            "order_quantity" => 1,
            "free_trial" => $buy_info['free_trial'], // 购买或者试用
            "order_time" => $buy_info['free_trial'] == 1 ? 0:$buy_info['order_time'],
            "create_time" => current_date(),
            "complete_time" => current_date(), // 完成时间
            "charge" => $price,
            "ip_address" => $ssl_extra['mutil_domain'].','.$ssl_extra['global_domain'].','.$ssl_extra['mutil_server']
            //SSL购买参数以逗号分隔，第一位代表多域名，第二位代表通配证书，第三位代表多服务器
        );
        //执行录入订单、购买方法
        $order = new \Frontend\Model\OrderModel();
        $order_id = $order->order_do_entry($dataarray,$buy_info['member_id'],StateData::NEW_ORDER,$product_info,$product_type_info,null,"ssl");
        if($order_id < 0)
        {//小于0就是错误返回信息
            return $order_id;
        }
        return $this->api_ssl_buy($order_id, $product_info,$product_type_info, $ssl_extra);
    }

    /**
     * SSL试用/购买接口调用
     * @date: 2016年11月18日
     * @author: Lyubo
     * @param $order_id
     * @param $product_info
     * @param $product_type_info
     * @param $ssl_extra
     * @return int
     */
    public function api_ssl_buy($order_id,$product_info,$product_type_info,$ssl_extra){
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        $agent = new AgentAide();
        $transaction = $agent->servant;
        $order_time = $order_info['order_time'];
        $areacode = $product_info["area_code"];
        if(strpos($product_type_info['api_ptype'],GiantAPIParamsData::PTYPE_SSL) === false){
            return -2;
        }
        try{
            if($order_info['free_trial'] == StateData::STATE_BUY)
            { // 购买
                $result = $transaction->ssl_buy(GiantAPIParamsData::PTYPE_SSL,$product_info['api_name'],$ssl_extra['mutil_domain'],$ssl_extra['mutil_server'],$ssl_extra['global_domain'],$order_time);
                api_log($order_info['user_id'],"ptype:".GiantAPIParamsData::PTYPE_SSL."--pname:".$product_info['api_name']."--多域名:".$ssl_extra['mutil_domain']."---多服务器".$ssl_extra['mutil_server']."---通配型".$ssl_extra['global_domain']."--yid:".$order_info['order_time']."--tid:".GiantAPIParamsData::TID_BUY,$result,"SSL证书购买");
            }else
            { // 试用
                $result = $transaction->_try(GiantAPIParamsData::PTYPE_SSL,$product_info['api_name']);
                api_log($order_info['user_id'],"ptype:".GiantAPIParamsData::PTYPE_VPS."--pname:".$product_info['api_name']."--多域名:".$ssl_extra['mutil_domain']."---多服务器".$ssl_extra['mutil_server']."---通配型".$ssl_extra['global_domain']."--yid:".$order_info['order_time']."--tid:".GiantAPIParamsData::TID_BUY,$result,"SSL证书试用");
            }
        }catch(\Exception $e){
            // 调用接口试用失败修改订单表
            $dataarray = array(
                "complete_time" => current_date(),
                "order_log" => $order_info['order_log']."-接口调用错误",
                "area_code" => $areacode,
                "state" => StateData::FAILURE_ORDER  // 订单状态失败
            );
            $order->order_edit($order_id,$dataarray);
            api_log($order_info['user_id'],"ptype:".GiantAPIParamsData::PTYPE_SSL."--pname:".$product_info['api_name']."--多域名:".$ssl_extra['mutil_domain']."---多服务器".$ssl_extra['mutil_server']."---通配型".$ssl_extra['global_domain']."--yid:".$order_info['order_time']."--tid:".GiantAPIParamsData::TID_BUY,$e->getMessage(),"SSL证书购买失败");
            return -9; // 接口调用失败
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            // 调用接口购买/试用成功
            $ddh = $xstr['info']['ddh'];
            $ywbh = $xstr['info']['ywbh'];
            if($order_info["free_trial"] == 0){
                $dataarray = array(
                    "api_id" => $ddh,
                    "business_id" => $ywbh,
                    "complete_time" => current_date(),
                    "area_code" => $areacode,
                    "state" => StateData::PAYMENT_ORDER  // 订单状态已付款
                );
            }else{
                $dataarray = array(
                    "api_id" => $ddh,
                    "business_id" => $ywbh,
                    "complete_time" => current_date(),
                    "area_code" => $areacode,
                    "state" => StateData::EXAMINE_ORDER  // 订单状态审核中
                );
            }
            if(!$order->order_edit($order_id,$dataarray)){
                return -7; // 订单表修改失败
            }else{
                return -1; // 购买成功
            }
        }else{
            // 调用接口购买/试用失败
            // 调用接口试用失败修改订单表
            $dataarray = array(
                "complete_time" => current_date(),
                "order_log" => $order_info['order_log']."-接口调用错误",
                "state" => StateData::FAILURE_ORDER // 订单状态失败
            );
            $order->order_edit($order_id,$dataarray);
            return $xstr['code'];
        }
    }
















    /************************************************************************************/
    /*
	 * 获取业务的状态
     * @author: Guopeng
     * @param $where
     * @return mixed
     */
    function get_ssl_state($where){
        $fields = "bs.Id as yid,bs.user_id,bs.login_name,bs.business_id,bs.product_id,bs.product_name,bs.create_time,bs.overdue_time,bs.buy_time,bs.state,bs.free_trial,bs.mail_state,bs.domain_name,bs.root_domain,bs.encrypt,bs.registrant,bs.mobile,bs.mail,bs.fill,bs.images,bs.validates,bs.multi_domain,bs.params,bs.open_step,p.product_type_id,pt.api_ptype,p.api_name";
        $ssl = $this->queryBuilder($where,$fields,"DESC",GiantAPIParamsData::PTYPE_SSL)->find();
        return $ssl;
    }
    /**
     * SSL开通
     * @date: 2017年3月9日 上午10:21:07
     * @author: Guopeng
     * @param: $order_id,$member_id
     * @return array
     */
    public function ssl_open($order_id,$member_id)
    {
        $order = new \Frontend\Model\OrderModel();
        $product = new \Frontend\Model\ProductModel();
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
        if($order_info['user_id'] != $member_id){
            return -101; // 业务不存在或不属于该会员
        }
        $product_info = $product->get_product($order_info['product_id']); // 获取订单产品信息
        if(!$product_info){
            return -2; // 产品获取失败
        }
        $business_info = array(
            "user_id" => $member_id,
            "business_id"=>$order_info['business_id'],
            "login_name" => $order_info['login_name'],
            "product_id" => $product_info['id'],
            "product_name" => $product_info['product_name'],
            "create_time" => current_date(),
            "buy_time" => $order_info['order_time'],
            "state" => 11,
            "free_trial" => $order_info['free_trial'],
            "open_step"=>1,
            "params"=>$order_info['ip_address'],//SSL购买参数以逗号分隔，第一位代表多域名，第二位代表通配证书，第三位代表多服务器
            "note_appended" => "会员编号:".$member_id."开通".$product_info['product_name']."订单编号：".$order_id
        );
        if(!$this->add($business_info)){
            return -103; // 业务表插入失败
        }
        $yw_id = $this->getLastInsID(); // 获取业务ID
        $arr = array(
            "state" => StateData::WAIT_ORDER,
//            "business_id"=> $yw_id,
            'note_appended' => "业务开通中！",
            );
        $order->order_edit($order_id,$arr);
        return $yw_id;
    }

    /**
     * 查询域名是否绑定
     * @author: Guopeng
     * @param $domain
     * @return mixed
     */
    public function checkDomain($domain){
        $where["domain_name"] = array("like","%$domain%");
        $ssl_info = $this->where($where)->field("domain_name")->find();
        return $ssl_info;
    }

    /**
     * ssl开通第一步-绑定域名
     * @author: Guopeng
     * @return bool
     */
    public function ssl_open_bind()
    {
        $open_info = request();
        $order_id = $open_info["order_id"]+0;
        $order = new \Frontend\Model\OrderModel();
        $ssl = new \Frontend\Model\SslModel();
        $order_info = $order->order_find($order_id);
        $yw_id = $open_info['yw_id']+0;
        $user_id = session("user_id");
        $where["bs.user_id"] = $user_id;
        $where["bs.Id"] = $yw_id;
        $ssl_info = $ssl->get_ssl_state($where);
        if(!$order_info && !$ssl_info){
            $this->setError("订单业务信息错误！");
            return false;
        }
        if(empty($open_info["domain"])){
            $this->setError('请输入您要绑定的域名，然后进行下一步！');
            return false;
        }
        $ptype = $ssl_info['api_ptype'];
        $did = $order_info["api_id"];
        $domain = implode(",",$open_info["domain"]);
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try {
            $result = $transaction->bdDomain($ptype,$did,$progress=1,$domain);
            api_log("user_id".$ssl_info['user_id'],"ptype:".$ptype."--did:".$did."--domain:".$domain,$result,"OV/EV开通第一步：绑定域名");
        }catch(\Exception $e){
            api_log("user_id".$ssl_info['user_id'],"ptype:".$ptype."--did:".$did."--domain:".$domain,$e->getMessage(),"OV/EV开通第一步：绑定域名-接口调用失败");
            $this->setError(-9);
            return false;
        }
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            if($xstr['info']['status'] == 0){
                $dataarray = array(
                    "domain_name"=>$domain,
                    "open_step"=>2,
                );
                $where1["Id"] = $yw_id;
                if ($this->where($where1)->save($dataarray)){
                    return true; //成功
                } else {
                    $this->setError("绑定域名失败！");
                    return false; //失败
                }
            }else{
                $this->setError($xstr['info']['error']);
                return false; //失败
            }
        } else {
            // 调用接口获取根域名失败
            $this->setError($xstr["code"]);
            return false;
        }
    }

    /**
     * ssl开通第二步-填写资料
     * @author: Guopeng
     * @return bool
     */
    public function ssl_fill_info()
    {
        $fill_info = request();
        $order_id = $fill_info["order_id"]+0;
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        $yw_id = $fill_info["yw_id"]+0;
        $user_id = session("user_id");
        $where["bs.user_id"] = $user_id;
        $where["bs.Id"] = $yw_id;
        $ssl_info = $this->get_ssl_state($where);
        if(!$order_info && !$ssl_info){
            $this->setError("订单业务信息错误！");
            return false;
        }
        $ptype = $ssl_info["api_ptype"];
        $did = $order_info["api_id"];
        $progress = 2;
        $data["applyName"] = clearXSS($fill_info["p_name"]);
        $data["email"] = clearXSS($fill_info["p_email"]);
        $data["mobile"] = $fill_info["p_phone"]+0;
        $data["QQ"] = empty($fill_info["p_qq"])? $fill_info["p_qq"]:$fill_info["p_qq"]+0;
        $data["compType"] = $fill_info["c_type"]+0;
        $data["compName"] = clearXSS($fill_info["c_name"]);
        $data["compEmail"] = clearXSS($fill_info["c_email"]);
        $data["compPhone"] = $fill_info["c_phone"];
        $preg_email = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/';
        $preg_phone = '/^1((3[0-9])|(4[57])|(5[0-35-9])|(7[0135678])|(8[0-9]))\d{8}$/';
        $preg_name = '/^[\x{4e00}-\x{9fa5}]{2,4}$/u';
        if(!preg_match($preg_email,$data["email"])){
            $this->setError("email格式错误！");
            return false;
        }
        if(!preg_match($preg_phone,$data["mobile"])){
            $this->setError("手机号码格式错误！");
            return false;
        }
        if(!preg_match($preg_name,$data["applyName"])){
            $this->setError("姓名格式错误！");
            return false;
        }
        $agent = new AgentAide();
        $transaction = $agent->servant;
        $input = "ptype:".$ptype."-did:".$did."-progress".$progress;
        try {
            $result = $transaction->fillinfo($ptype,$did,$progress,$data);
            api_log($ssl_info['user_id'],$input,$result,"OV/EV开通第二步：填写资料");
        }catch(\Exception $e){
            api_log($ssl_info['user_id'],$input,$e->getMessage(),"OV/EV开通第二步：填写资料-接口调用失败");
            $this->setError(-9);
            return false;
        }
        $fill = json_encode($data);
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            if($xstr['info']['status'] == 0){
                $dataarray = array(
                    "open_step"=>3,
                    "registrant"=>$data["applyName"],
                    "mail"=>$data["email"],
                    "mobile"=>$data["mobile"],
                    "fill"=>$fill,
                );
                $where1["Id"] = $yw_id;
                if ($this->where($where1)->save($dataarray) !== false){
                    return true; //成功
                } else {
                    $this->setError(-102);
                    return false;
                }
            }else{
                $this->setError($xstr['info']['error']);
                return false; //失败
            }
        } else {
            // 调用接口获取根域名失败
            $this->setError($xstr["code"]);
            return false;
        }
    }

    /**
     * ssl DV开通
     * @author: Guopeng
     * @return bool
     */
    public function ssl_dv_open(){
        $fill_info = request();
        $order_id = $fill_info["order_id"]+0;
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        $yw_id = $fill_info["yw_id"]+0;
        $user_id = session("user_id");
        $where["bs.user_id"] = $user_id;
        $where["bs.Id"] = $yw_id;
        $ssl_info = $this->get_ssl_state($where);
        if(!$order_info || !$ssl_info){
            $this->setError("订单信息错误！");
            return false;
        }
        if(empty($fill_info["domain"])){
            $this->setError('未绑定域名');
            return false;
        }
        $domains = implode(",",$fill_info["domain"]);
        $ptype = $ssl_info["api_ptype"];
        $did = $order_info["api_id"];
        $progress = 1;
        $data["applyName"] = $applyName = clearXSS($fill_info["p_name"]);
        $data["email"] = $email = clearXSS($fill_info["p_email"]);
        $data["mobile"] = $mobile = $fill_info["p_phone"]+0;
//        $keytype = $fill_info["p_keytype"]+0;
        $preg_email = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/';
        $preg_phone = '/^1((3[0-9])|(4[57])|(5[0-35-9])|(7[0135678])|(8[0-9]))\d{8}$/';
        $preg_name = '/^[\x{4e00}-\x{9fa5}]{2,4}$/u';
        if(!preg_match($preg_email,$email)){
            $this->setError("email格式错误！");
            return false;
        }
        if(!preg_match($preg_phone,$mobile)){
            $this->setError("手机号码格式错误！");
            return false;
        }
        if(!preg_match($preg_name,$applyName)){
            $this->setError("姓名格式错误！");
            return false;
        }
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try {
            $result = $transaction->bdDomain($ptype,$did,$progress,$domains,$applyName,$email,$mobile);
            api_log($ssl_info['user_id'],"ptype:".$ptype."--did:".$did."--ymbd:".$domains."--name:".$applyName."--mail:".$email."--tel:".$mobile,$result,"DV开通");
        }catch(\Exception $e){
            api_log($ssl_info['user_id'],"ptype:".$ptype."--did:".$did."--ymbd:".$domains."--name:".$applyName."--mail:".$email."--tel:".$mobile,$e->getMessage(),"DV开通-接口调用失败");
            $this->setError(-9);
            return false;
        }
        $fill = json_encode($data);
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            if($xstr['info']['status'] == 0){
                $dataarray = array(
                    "state"=>6,
                    "open_step" => 6,
                    "domain_name"=>$domains,
                    "registrant"=>$applyName,
                    "mail" => $email,
                    "mobile" => $mobile,
//                    "encrypt" => $keytype,
                    "fill"=>$fill,
                );
                $where1["Id"] = $yw_id;
                if ($this->where($where1)->save($dataarray)){
                    return true; //成功
                } else {
                    $this->setError(-102);
                    return false;
                }
            }else{
                $this->setError($xstr['info']['error']);
                return false; //失败
            }
        } else {
            // 调用接口获取根域名失败
            $this->setError($xstr["code"]);
            return false;
        }
    }

    /**
     * ssl开通第三步-上传资料
     * @author: Guopeng
     * @return bool
     */
    public function ssl_upload_info(){
        $upload_info = request();
        $order_id = $upload_info["order_id"]+0;
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        $yw_id = $upload_info["yw_id"]+0;
        $user_id = session("user_id");
        $where["bs.user_id"] = $user_id;
        $where["bs.Id"] = $yw_id;
        $ssl_info = $this->get_ssl_state($where);
        if(!$order_info && !$ssl_info){
            $this->setError("订单业务信息错误！");
            return false;
        }
        $ptype = $ssl_info["api_ptype"];
        $did = $order_info["api_id"];
        $progress = 3;
        //取得上传的图片信息
        if(empty($_FILES["letterImg"]["tmp_name"])){
            $this->setError("OV,EV授权书扫描件未上传");
            return false;
        }elseif(empty($_FILES["applyIdImg"]["tmp_name"])){
            $this->setError("身份证扫描件未上传");
            return false;
        }
        if(strpos($order_info['api_name'],'ev') !== false){
            if(empty($_FILES["busiLicenceImg"]["tmp_name"])){
                $this->setError("营业执照扫描件未上传");
                return false;
            }elseif(empty($_FILES["billImg"]["tmp_name"])){
                $this->setError("发票扫描件未上传");
                return false;
            }
        }
        if($_FILES["letterImg"]["size"] > 300*1024){ // 设置图片上传大小
            $this->setError("OV,EV授权书扫描件不能大于300KB");
            return false;
        }elseif($_FILES["applyIdImg"]["tmp_name"] > 300*1024){
            $this->setError("身份证扫描件不能大于300KB");
            return false;
        }
        if(strpos($order_info['api_name'],'ev') !== false){
            if($_FILES["busiLicenceImg"]["tmp_name"] > 300 * 1024){
                $this->setError("营业执照扫描件不能大于300KB");
                return false;
            }elseif($_FILES["billImg"]["tmp_name"] > 300 * 1024){
                $this->setError("发票扫描件不能大于300KB");
                return false;
            }
        }
        $img_type = array('jpg', 'gif', 'png', 'jpeg');
        foreach($_FILES as $fkey=>$fvalue){
            if (!in_array(explode("/",$fvalue["type"])[1], $img_type)) {
                $this->setError("非法图像文件！");
                return false;
            }
        }
        $base64_img = [];
        foreach($_FILES as $fkey=>$fvalue){
            $img = $fvalue["tmp_name"];
            $image_data = fread(fopen($img, 'r'), filesize($img));
            $base64_img[$fkey] = base64_encode($image_data);
        }
        if(empty($base64_img)){
            $this->setError("未获取到上传扫描件");
            return false;
        }
        // 上传文件
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     300*1024 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ""; // 设置附件上传（子）目录
        $images   =   $upload->upload();
        if(!$images) {// 上传错误提示错误信息
            $this->setError("上传错误");
            return false;
        }
        if(!isset($images["letterImg"])){
            $this->setError("OV,EV授权书扫描件上传错误");
            return false;
        }elseif(!isset($images["applyIdImg"])){
            $this->setError("身份证扫描件上传错误");
            return false;
        }
        if(strpos($order_info['api_name'],'ev') !== false){
            if(!isset($images["busiLicenceImg"])){
                $this->setError("营业执照扫描件上传错误");
                return false;
            }elseif(!isset($images["billImg"])){
                $this->setError("发票扫描件上传错误");
                return false;
            }
        }
        $image = new \Think\Image();
        $data = [];
        foreach($images as $ikey => $ivalue){
            $info='Uploads/'.$ivalue['savepath'].$ivalue['savename'];
            //添加图片水印
            $image->open('./'.$info)->water('./Public/frontend/images/apply-logo.png',\Think\Image::IMAGE_WATER_NORTHWEST,0)->save('./'.$info);
            $data[$ikey]= $info;
        }
        if(!isset($images["letterImg"]) || !isset($images["applyIdImg"])){
            $this->setError("非法图像文件！");
            return false;
        }
        if(strpos($order_info['api_name'],'ev') !== false){
            if(!isset($images["busiLicenceImg"]) || !isset($images["billImg"])){
                $this->setError("非法图像文件！");
                return false;
            }
        }
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try {
            $result = $transaction->uploadinfo($ptype,$did,$progress,$base64_img);
            api_log($ssl_info['user_id'],"ptype:".$ptype."--bid:".$did."--progress:".$progress,$result,"OV/EV开通第三步：上传资料");
        }catch(\Exception $e){
            api_log($ssl_info['user_id'],"ptype:".$ptype."--bid:".$did."--progress:".$progress,$e->getMessage(),"OV/EV开通第三步：上传资料-接口调用失败");
            $this->setError(-9);
            return false;
        }
        $imgs = json_encode($data);
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            if($xstr['info']['status'] == 0){
                $where1["Id"] = $yw_id;
                $dataarray = array(
                    "open_step"=>4,
                    "images"=>$imgs
                );
                if ($this->where($where1)->save($dataarray)){
                    return true;
                } else {
                    $this->setError(-102);
                    return false;
                }
            }else{
                $this->setError($xstr['info']['error']);
                return false; //失败
            }
        } else {
            // 调用接口失败
            $this->setError($xstr["code"]);
            return false;
        }
    }
    /**
     * ssl开通第四步-验证资料
     * @author: Guopeng
     * @return bool
     */
    public function ssl_user_validate(){
        $validate_info = request(true);
        $order_id = $validate_info["order_id"]+0;
        $order = new \Frontend\Model\OrderModel();
        $order_info = $order->order_find($order_id);
        $user_id = session("user_id");
        $yw_id = $validate_info["yw_id"]+0;
        $where["bs.Id"] = $yw_id;
        $where["bs.user_id"] = $user_id;
        $ssl_info = $this->get_ssl_state($where);
        if(!$order_info && !$ssl_info){
            $this->setError("订单业务信息错误！");
            return false;
        }
        $ptype = $ssl_info["api_ptype"];
        $did = $order_info["api_id"];
        $progress = 4;
        $data["compName"] = clearXSS($validate_info["compName"]);
        $data["applyName"] = clearXSS($validate_info["applyName"]);
        $data["idnum"] = $validate_info["idnum"];
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try {
            $result = $transaction->userValidate($ptype,$did,$progress,$data);
            api_log($ssl_info['user_id'],"ptype:".$ptype."--bid:".$did."--progress".$progress,$result,"OV/EV开通第四步：用户验证");
        }catch(\Exception $e){
            api_log($ssl_info['user_id'],"ptype:".$ptype."--bid:".$did."--progress".$progress,$e->getMessage(),"OV/EV开通第四步：用户验证-接口调用失败");
            $this->setError(-9);
            return false;
        }
        $validate = json_encode($data);
        $xstr = json_decode($result,true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            if($xstr['info']['status'] == 1){
                $dataarray = array(
                    "validates"=>$validate,
                    "open_step"=>5
                );
                $where1["Id"] = $yw_id;
                if ($this->where($where1)->save($dataarray)){
                    return true; //成功
                } else {
                    $this->setError(-102);
                    return false;
                }
            }else{
                $this->setError($xstr['info']['error']);
                return false; //失败
            }
        } else {
            // 调用接口获取根域名失败
            $this->setError($xstr["code"]);
            return false;
        }
    }

    /**
     * 获取未开通业务
     * @author: Guopeng
     * @param: $ssl_info
     * @return: array
     */
    public function get_not_open($ssl_info,$state){
        $business_info = [];
        $i = 0;
        foreach($ssl_info as $key=>$val){
            if($val['state'] == $state){
                $business_info[$i++] = $val['yid'];
            }
        }
        return $business_info;
    }
    /**
     * 获取ssl订单开通状态
     * @author: Guopeng
     * @param $ssl_id
     * @return int
     */
    public function get_business_info($ssl_id){
        $order =  new \Frontend\Model\OrderModel();
        $member_id = session("user_id");
        $where['Id'] = ['eq',$ssl_id];
        $where['user_id'] = ['eq',$member_id];
        $ssl_info = $this->where($where)->find();
        if(empty($ssl_info)){
            $this->setError(-10);
            return false;
        }
        $order_where['business_id'] = ['eq',$ssl_info['business_id']];
        $order_where['user_id'] = ['eq',$member_id];
        $order_info = $order->where($order_where)->find();//通过api_bid获取订单信息
        if(empty($order_info)){
            $this->setError(-13);
            return false;
        }
        $ptype = GiantAPIParamsData::PTYPE_SSL;
        $did = $order_info["api_id"];
        $tid = GiantAPIParamsData::TID_GET_SSL_BUSINESS;
        $agent = new AgentAide();
        $transaction = $agent->servant;
        try{
            $result = $transaction->ssl_getinfo($ptype,$did,$tid);
            api_log($member_id,"ptype:".$ptype."--did:".$did."--tid:".$tid,$result,"会员".$member_id."获取SSL开通进度");
        } catch ( \Exception $e ) {
            // 记录操作
            api_log($member_id,"ptype:".$ptype."--did:".$did."--tid:".$tid,$e->getMessage(),"会员".$member_id."获取SSL开通进度失败");
            $this->setError(-9);
            return false;
        }
        $xstr = json_decode($result, true);
        if($xstr['code'] == 0 && $xstr['code'] !== null){
            if(strpos($ssl_info['product_name'],'dv') !== false){
                if($xstr['info']['progress'] != 3){
                    $this->setError("业务正在开通中");
                    return 0; //失败
                }
            }else{
                if($xstr['info']['progress'] != 6){
                    $this->setError("业务正在开通中");
                    return 0; //失败
                }
            }
            $ssl_business_arr = [
                'create_time'=>$xstr['info']['createDate'],//创建时间
                'overdue_time'=>$xstr['info']['overDate'],//结束时间
                'state'=>1,//默认为1，正常状态
            ];
            $up_ssl['Id'] = ["eq",$ssl_id];
            $ssl_edit = $this->where($up_ssl)->save($ssl_business_arr);
            if($ssl_edit === false){
                $ordet_data['order_log'] = $order_info['order_log']."ssl开通成功，业务表修改失败";
                $ordet_data['note_appended'] = "业务开通成功,业务表修改失败！";
                $order->order_edit($order_info['order_id'], $ordet_data);
                $this->setError(-103);
                return false;
            }
            $ordet_data['state'] = StateData::STATE_ONLINE_TRAN_SUCCESS;
            $ordet_data['order_log'] = "业务开通成功";
            $ordet_data['note_appended'] = "业务开通成功！";
            $app = $order->order_edit($order_info['id'], $ordet_data);
            return true;
        }else{
            $this->setError($xstr['code']);
            return false;
        }
    }
}