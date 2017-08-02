<?php
namespace Frontend\Model;
use Common\Data\StateData;
use Think\Model;
/* use \Frontend\Model\ProductModel;
use \Frontend\Controller\VirtualhostController;
use \Common\Aide\AgentAide; */
use Common\Data\GiantAPIParamsData;
class OrderModel extends Model{
    protected $trueTableName = 'agent_order';
   
    public function conver_par(){
        $date =request();
        $where["user_id"] = array("eq",session("user_id"));
        $date["order_type"] = is_numeric($date["order_type"])? $date["order_type"]+0: $date["order_type"];
        if($date["product_type"] !==null && $date["product_type"] !== ""){//判断如果在全部列表下没有product_type参数直接跳出
            $product_type = explode(".",$date['product_type']);
            $where["product_type"] = array("in",$product_type);
        }
        if(!empty($date)){
            if($date["state"] !==null && $date["state"] !== ""){//判断如果在全部列表下没有state参数直接跳出
             $where["state"] = array("eq",$date['state']);
             }
             if($date["order_type"] === 0 || !empty($date["order_type"])){
                 $where["order_type"] = array("eq",$date['order_type']);
             }
             if(!empty($date["select"])){
                $where["product_name"] = array("like","%".$date['select']."%");
             }
        }
        return $where;
    }
    
    /**
     * 获取条件全部订单
     * @date: 2016年10月29日 下午5:30:57
     * @author: Lyubo
     * @param $where
     * @param int $per_page
     * @param string $fields
     * @param $order_by
     * @return array|bool
     */
    public function get_order_list($where,$per_page=5,$fields="*",$order_by){
        if(empty($order_by)){
            $order_by = 'DESC';
        }
        $info = [];
        if($where){
            $sum = $this->paging($where,$per_page,$fields);
            $data = $this->queryBuilder($where,$fields,$order_by)->limit($sum['page']->firstRow.','.$sum['page']->listRows)->select();
            $info['count'] = $sum['count'];
            $info['show'] = $sum['page']->show();
            //对自带thinkphp分页进行替换
            $show = str_replace("<div>", "", $info["show"]);
            $show = str_replace("</div>", "", $show);
            $show = str_replace("span", "a", $show);
            $info['page_show'] = $show;
            $info['list'] = $data;
            return $info;
        }else{
            $this->error="查询条件为空";
            return false;
        }
    }
    /**
     * 获取带条件的总记录数
     * @date: 2016年10月29日 下午5:32:44
     * @author: Lyubo
     * @return:
     */
    public  function queryBuilder($where,$field,$order_by="DESC"){
        return $this->field($field)->where( $where )->order("create_time ".$order_by);//返回带条件的总记录数;
    }
    /**
     * 分页
     * @date: 2016年10月29日 下午5:53:25
     * @author: Lyubo
     * @param: $GLOBALS
     * @return:
     */
    public  function paging($where,$per_page =5,$fields,$order_by){
        $sumpage= [];
        $count =  $this->queryBuilder($where,$fields,$order_by)->count();
        $Page   = new \Think\Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig("first", "首页");
        $Page->setConfig("last", "尾页");
        $request = request();
        //分页跳转的时候保证查询条件
        foreach($request as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        $sumpage['page'] = $Page;
        $sumpage['count'] = $count;
        return $sumpage;
    }
    /**
    * 获取订单条件总数
    * @date: 2016年11月1日 上午9:52:32
    * @author: Lyubo
    * @return:
    */
    public function order_stateCount($where){
            $date =request();
            $where["user_id"] = array("eq",session("user_id"));
            if($date["state"] !== '*'){//统计全部参数为*直接跳过
            $where["state"] = array("eq",$date['state']);
            }
           return $this->queryBuilder($where)->count();
    }
    /**
    * 订单详情
    * @date: 2016年11月19日 下午2:44:52
    * @author: Lyubo
    * @param: $order_id
    * @return: array
    */
    public function get_order_info($where){
        $order_id = I('get.order_id');
        $where['id'] = ['eq' , $order_id];
        return $this->where($where)->find();
    }
    /**
    * 查找一条订单信息
    * @date: 2016年11月11日 下午3:04:56
    * @author: Lyubo
    * @param: $order_id
    * @return: $array
    */
    public function order_find($order_id){
        $where["id"] = array("eq" , $order_id);
        return $this->where($where)->find();
    }
/*****************************会员购买、下单start*******************************/
    /**
    * 会员购买确认信息页面
    * @date: 2016年11月9日 下午3:09:36
    * @author: Lyubo
    * @return: boolean
    */
    public function order_buy(){
        $site_config = WebSiteConfig();//获取网站配置
        $date =request();
        $member_id = session("user_id");
        $product_id = $date['product_id'];
        $system_type = $date['system_type'];
        $price_id = $date['price_id'];
        $free_trial = $date['free_trial'];
        /**
         * ---------------------------------------------------
         * | ssl购买需要的参数
         * | @时间: 2016年12月27日 下午5:50:44
         * ---------------------------------------------------
         */
        $ssl_extra['mutil_domain'] = $date['mutil_domain'];
        $ssl_extra['mutil_server'] = $date['mutil_server'];
        $ssl_extra['global_domain'] = $date['global_domain'];
        $ssl_extra['price_id'] = $date['price_id'];
        $ssl_extra['mutil_domain_step_id'] = $date['mutil_domain_step_id'];	//多域名价格的id
        
        if(!is_int($price_id) || is_null($product_id)){
            return 4002;//产品编号错误
        }
        if($free_trial !='0'){//$free_trial：0是购买，其他值试用，判断试用次数
            $trial = $this->get_test_sum($product_id , $member_id ,$free_trial);
            if($trial >= $site_config['site_trial_times']){
                return -115;//已经到达最多试用次数，不能再免费使用
            }
        }
        if($product_id == '244'){
            if($free_trial =='0'){//限制免费主机购买测试
                $trial = $this->get_test_sum('244' , $member_id ,$free_trial);
                if($trial >= $site_config['site_buy_times']){
                    return -120;//已经到达最多试用次数，不能再免费使用
                }
            }
        }
        return $this->user_order_buy($member_id , $product_id , $price_id , $free_trial , $system_type, $ssl_extra);
    }
    /**
    * 会员购买快云服务器
    * @date: 2016年11月26日 下午4:04:36
    * @author: Lyubo
    * @return:
    */
    public function cloud_order_buy($data){
        $member_id = session("user_id");
        //手机站配置填充
        if($data['type'] == 'mobile' && MODULE_NAME == 'Frontend' && isMobile() == 'phone'){
            $config = ["1"=>["disk"=>"80","cpu"=>"1","mem"=>"1","bandwidth"=>"2"],"2"=>["disk"=>"120","cpu"=>"2","mem"=>"2","bandwidth"=>"3"],"3"=>["disk"=>"200","cpu"=>"4","mem"=>"4","bandwidth"=>"5"],"4"=>["disk"=>"300","cpu"=>"8","mem"=>"8","bandwidth"=>"7"],"5"=>["disk"=>"500","cpu"=>"16","mem"=>"16","bandwidth"=>"10"]];
                for ($i = 1;$i<count($config);$i++){
                    if($data['pzbh'] == $i){
                        $data['disk'] = $config[$i]['disk'];
                        $data['cpu'] = $config[$i]['cpu'];
                        $data['mem'] = $config[$i]['mem'];
                        $data['bandwidth'] = $config[$i]['bandwidth'];
                    }
                }
        }
        $parms = array (
            'member_id' => $member_id,
            'os' => $data['os'],
            'cpu'=>$data['cpu'],
            'mem'=>$data['mem'],
            'disk'=>$data['disk'],
            'bandwidth'=>$data['bandwidth'],
            'count'=>$data['count'],
            'gmqx'=>$data['time'],
            'dqbh'=>$data['region'],
            "serverbh" =>$data['serverbh'],
            "free_trial"=>"0",
            "pzbh"=>$data['pzbh'] //手机站配置编号
        );
        $api_ptype = $data['api_ptype'];
        if (strcmp ( $api_ptype, GiantAPIParamsData::PTYPE_CLOUD_SERVER ) == 0) {
            $cloudserver = new \Frontend\Model\CloudserverModel();
            return  $cloudserver->cloudserver_buy($parms);
        }else{
			return - 2;
		}
    }
    /**
    * 会员购买
    * @date: 2016年11月10日 下午6:12:46
    * @author: Lyubo
    * @param: $product_id 产品编号
	* @param: $order_time 购买时间
	* @param: $free_trial 是否为试用
    * @return: 
    */
    public function user_order_buy($member_id , $product_id , $price_id , $free_trial , $system_type, $ssl_extra = []){
        $product = new \Frontend\Model\ProductModel();
        $price_info = $product->get_price($price_id);//获取价格信息
        $product_info = $product->get_product($product_id);//获取产品信息
        $product_type_info = $product->get_product_type_info ( $product_info['product_type_id']);
        $api_ptype = $product_type_info ['api_ptype'];
        $buy_info = [
            'member_id' => $member_id,
            'product_id' => $product_id,
            'order_time' => $price_info['month'],
            'free_trial' => $free_trial,
            'system_type'=>$system_type,
        		/**
        		 * ---------------------------------------------------
        		 * | 产品价格信息
        		 * | @时间: 2016年12月27日 下午6:37:51
        		 * ---------------------------------------------------
        		 */
        	'price_info' => $price_info,
        ];
        if (strcmp($api_ptype, GiantAPIParamsData::PTYPE_HOST) == 0 
            || strcmp($api_ptype, GiantAPIParamsData::PTYPE_HK_HOST) == 0 
            || strcmp($api_ptype, GiantAPIParamsData::PTYPE_DEDE_HOST) == 0 
            || strcmp($api_ptype, GiantAPIParamsData::PTYPE_USA_HOST) == 0
            || strcmp($api_ptype, GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL) == 0) {
            $virtualhost = new \Frontend\Model\VirtualhostModel();
           return  $virtualhost->virtualhost_buy($buy_info,$product_info,$product_type_info);
        }
        elseif(strcmp($api_ptype, GiantAPIParamsData::PTYPE_VPS) == 0)
        {
            $vps = new \Frontend\Model\VpsModel();
            return  $vps->vps_buy($buy_info,$product_info,$product_type_info);
        }elseif(strcmp($api_ptype, GiantAPIParamsData::PTYPE_FAST_CLOUDVPS) == 0)
        {
            $vps = new \Frontend\Model\FastvpsModel();
            return  $vps->fastvps_buy($buy_info,$product_info,$product_type_info);
        } elseif(strcmp($api_ptype, GiantAPIParamsData::PTYPE_CLOUD_SPACE) == 0)
        {
            $cloudspace = new \Frontend\Model\CloudspaceModel();
            return  $cloudspace->cloudspace_buy($buy_info,$product_info,$product_type_info);
        }elseif (strcmp($api_ptype, GiantAPIParamsData::PTYPE_SSL) == 0){
        	$ssl = new \Frontend\Model\SslModel();
        	return  $ssl->ssl_buy($buy_info,$product_info,$product_type_info, $ssl_extra);
        }
        else {
            return - 2;
        }
    }

    /**
     * *******************************快云服务器订单*********************************
     */
    /**
     * 快云服务器执行购买方法
     * @date: 2016年11月26日 下午4:35:43
     * 
     * @author : Lyubo
     * @param : $datarray            
     * @return :成功返回订单号 0失败 -2产品信息获取失败 -3会员信息获取失败 -4会员账号信息获取失败 -5账号余额不足
     *         -6会员账户异常 -7交易成功，订单表修改失败 -8交易成功，交易记录生成失败
     */
    public function cloud_order_do_entry($datarray, $buy_info, $order_type)
    {
        // 获取会员信息
        $member = new \Frontend\Model\MemberModel();
        $where["user_id"] = array(
            "eq",
            $buy_info['member_id']
        );
        $member_info = $member->get_member_info($where);
        if (! $member_info) {
            return - 3; // 会员信息获取失败
        }
        // 封装订单参数信息
        $datarray['order_type'] = $order_type;
        $datarray['product_type'] = "18";
        $datarray['product_name'] = "快云服务器";
        $datarray['member_id'] = $buy_info['member_id'];
        $datarray['login_name'] = $member_info['login_name'];
        $datarray['create_time'] = date('Y-m-d H:i:s');
        $datarray['complete_time'] = date('Y-m-d H:i:s');
        $datarray['state'] = \Common\Data\StateData::FAILURE_ORDER;
        $datarray['charge'] = 0;
        // 订单表插入数据
        $insert_order = $this->add_order($datarray);
        // 订单编号
        $order_id = $this->getLastInsID();
        if (! $insert_order) {
            return 0;
        }
        return $this->cloud_order_transaction ( $order_id, $order_type, $datarray,$member_info,$buy_info );
    }
        /**
        * 订单交易
        * @date: 2016年11月26日 下午4:44:05
        * @author: Lyubo
        * @return: $order_id
        */
        public function cloud_order_transaction ( $order_id, $order_type, $datarray,$member_info,$buy_info ){
            $totalprice = 0;//总价格
            $order_log = "";//订单信息
            $order_error_data = [];//订单错误信息
            $price_data = []; //获取价格参数
            //获取会员账户信息
            $financial = new \Frontend\Model\FinancialModel();
            $product = new \Frontend\Model\ProductModel();
            $member_where['user_id'] = ["eq",$member_info['user_id']];
            $member_account = $financial->account_info ( $member_where );
            if (! $member_account) {
                $order_log = $order_log . " 交易失败-会员账号信息获取失败";
                return - 4;//会员账号信息获取失败
            }
            switch ($order_type){
                case 0://新增
                   if($datarray['free_trial'] == 0){
                       $price_data['month'] = $datarray['order_time'];
                       $price_data['type'] = \Common\Data\StateData::STATE_BUY;
                       $product_price = cloudserverPrice($buy_info);
                       if(empty($product_price)){
                           return -2;//产品价格获取失败
                       }
                       $totalprice = $product_price*$datarray ['order_quantity'];
                       $totalprice = round ( $totalprice, 2 );
                       $order_log = "购买产品：[快云服务器][数量" . $datarray ['order_quantity'] . "][购买时限" . $datarray ['order_time'] . "个月][消费金额" . $totalprice . "元]";
                   }
                   break;
                case 2://续费
    				$buy_info['gmqx'] = $datarray ['order_time'];
    				$totalprice = cloudserverPrice($buy_info);
    				$totalprice = round ( $totalprice, 2 );
    				// 日志
    				$order_log = "续费产品：[快云服务器][续费时限".$datarray['order_time']."个月][消费金额".$totalprice . "元]";
            }
            // 计算价格错误
            if ($totalprice < 0) {
                return - 39;//扣费失败！
            }
            // 扣费后余额
            $balance = bcsub ( $member_account ['balance'], $totalprice, 2 );
            if ($balance < 0) {
                $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
                $order_error_data["order_log"] = $order_log . " 交易失败-会员账号余额不足";
                $this->order_edit($order_id,$order_error_data);
                return - 5;
            }
            // 总消费金额
            $purchases = $member_account ['purchases'] + $totalprice;
            //发送扣费邮件
            $acct_type="";
            if($order_type ==0){
                if($datarray ['free_trial'] > 0)
                    $acct_type="试用";
                $acct_type="购买";
            } else if($order_type ==1){
                $acct_type="增值";
            } else if($order_type ==2){
                $acct_type="续费";
            } else if($order_type ==3){
                $acct_type="升级";
            } else if($order_type ==4){
                $acct_type="转正";
            }
            $acct_info = array(
                'totalprice'=>$totalprice,
                'balance'=>$balance,
                'product_name'=>'快云服务器',
                'product_des'=>'',
                'acct_type'=>$acct_type);
            //发送扣费邮件
             if(!empty($member_info['user_mail'])){
                $content = HTMLContentForEmail("7",$acct_info,$member_info);
                postOffice($member_info['user_mail'],$content['subject'],$content['body']);
            } 
            // 修改会员账户表
            if ($financial->edit_member_account ( $member_info ['user_id'], $balance, $purchases ) === false) {
                $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
                $order_error_data["order_log"] = $order_log . " 交易失败-会员账号异常，扣费失败";
                $this->order_edit($order_id,$order_error_data);
                return - 6;
            }
            $charge = $this->order_edit ( $order_id, array (
                "charge" => $totalprice,
                "order_log" => $order_log ,
                "complete_time"=>date('Y-m-d H:i:s')
            ) );
            if (! $charge) {
                $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
                $order_error_data["order_log"] = $order_log . " 交易成功-订单表修改失败";
                $this->order_edit ( $order_id, $order_error_data);
                return - 7;
            }
            $order_log = $order_log . "会员账户余额：[" . $balance . "]";
            if (add_transactions ( $member_info ['user_id'], $totalprice, $order_log, \Common\Data\StateData::CONSUM,  1 ,"214", $order_id)) {
                $order_log = $order_log . " 订单交易成功";
                // 前台为处理中
                $order_error_data["state"] = \Common\Data\StateData::PAYMENT_ORDER;
                $order_error_data['order_log'] = $order_log;
                $this->order_edit ( $order_id, $order_error_data );
                // 成功返回订单ID
                return $order_id;
            } else {
                $order_error_data["state"] = \Common\Data\StateData::SUCCESS_ORDER;
                $order_error_data["order_log"] = $order_log . " 交易成功-交易记录生成失败";
                $this->order_edit ( $order_id, $order_error_data);
                return - 8;//交易成功，交易记录生成失败
            }
        }
/*********************************快云服务器订单 end**********************************/
    /**
    * 执行录入订单、购买方法
    * @date: 2016年11月11日 上午10:14:53
    * @author: Lyubo
    * @param: $datarray
    * @param: $member_id
    * @param: $order_type 订单类型 0新增、1增值、2续费,3变更方案,4转正
    * @param: $product_info 产品信息
    * @param: $product_type_info 类型信息
	* @param: $domain 域名生成订单参数
	* @param: $api_type 域名生成订单参数
    * @return: 成功返回订单号 0失败 -2产品信息获取失败 -3会员信息获取失败 -4会员账号信息获取失败 -5账号余额不足
	*  -6会员账户异常 -7交易成功，订单表修改失败 -8交易成功，交易记录生成失败
    */
    public function order_do_entry($datarray,$member_id,$order_type , $product_info ,$product_type_info,$domain=null,$api_type=null,$business_id =null){
        //判断产品是否正常
        if (! $product_info || $product_info ["product_state"] == 0) {
            return - 2;//产品信息获取失败
        }
        //获取会员信息
        $member = new \Frontend\Model\MemberModel();
        $where["user_id"] = array("eq" , $member_id);
        $member_info = $member->get_member_info($where);
        if (! $member_info) {
            return - 3;//会员信息获取失败
        }
        // 封装订单参数信息
        $datarray ['order_type'] = $order_type;
        $datarray ['product_type'] = $product_info ["product_type_id"];
        $datarray ['product_name'] = $product_info ['product_name'];
        if(!is_null($domain)){
            $datarray ['product_name'] = $domain;
        }
        $datarray ['member_id'] = $member_id;
        $datarray ['login_name'] = $member_info ['login_name'];
        $datarray ['create_time'] = date('Y-m-d H:i:s');
        $datarray ['complete_time'] = date('Y-m-d H:i:s');
        $datarray ['state'] = StateData::FAILURE_ORDER;
        $datarray ['area_code'] = $product_info['area_code'];
        $datarray ['charge'] = isset($datarray ['charge'])? $datarray ['charge']:0;
        // 订单表插入数据
        $insert_order = $this->add_order($datarray);
        // 订单编号
        $order_id = $this->getLastInsID();
        if (! $insert_order) {
            return 0;
        }
        // 增加域名，区分万网/中国数据
        $product_info['api_type'] = $api_type;
        return $this->order_transaction ( $order_id, $order_type, $datarray, $product_info, $member_info , $product_type_info,$domain,$api_type);
    }
    /**
    * 订单交易
    * @date: 2016年11月11日 上午10:52:24
    * @author: Lyubo
    * @param: $order_id 订单ID
	* @param: $order_type 订单类型
	* @param: $datarray 订单信息
	* @param: $product_info 产品信息
    * @param: $member_info 用户信息
    * @param: $product_type_info 产品类型信息
    * @param: $domain 域名订单参数
    * @param: $api_type 域名订单参数
    * @return $order_id
    */
    public function order_transaction($order_id, $order_type, $datarray, $product_info, $member_info , $product_type_info,$domain,$api_type){
        $totalprice = 0;//总价格
        $order_log = "";//订单信息
        $order_error_data = [];//订单错误信息
        $price_data = []; //获取价格参数
        //获取会员账户信息
        $financial = new \Frontend\Model\FinancialModel();
        $product = new \Frontend\Model\ProductModel();
        $member_where['user_id'] = ["eq",$member_info['user_id']];
        $member_account = $financial->account_info ( $member_where );
        if (! $member_account) {
            $order_log = $order_log . " 交易失败-会员账号信息获取失败";
            return - 4;//会员账号信息获取失败
        }
        switch ($order_type) {
            case 0 : //新增
                if ($datarray ['free_trial'] > 0) {//试用
                    $order_log = "试用产品：".$datarray ['product_name']."会员账户余额：[".$member_account ['balance']."]";
                    if(add_transactions($member_info['user_id'] , 0 , $order_log , \Common\Data\StateData::CONSUM , 1 , $product_info['id'] , $order_id  ) ){
                        // 修改订单表，增加消费字段值
                        $charge = $this->order_edit ( $order_id, array (
                            "charge" => 0,
                            "order_log" => $order_log."-交易成功"
                        ) );
                        if ($charge===false) {
                            $order_log = $order_log . "-交易成功-订单表修改失败";
                            $order_error_data['order_type'] =  \Common\Data\StateData::FAILURE_ORDER;
                            $order_error_data['order_log'] = $order_log;
                            $this->order_edit ( $order_id, $order_error_data);
                            return - 7;//交易成功，订单表修改失败
                        }
                        // 成功返回订单ID
                        return $order_id;
                    }else{
                        $order_log = $order_log . "-交易成功-交易记录生成失败";
                        $order_error_data['order_type'] = \Common\Data\StateData::EXAMINE_ORDER;
                        $order_error_data['note_appended'] = $datarray ['note_appended'] . "交易记录生成失败" ;
                        $this->order_edit ( $order_id, $order_error_data);
                        return - 8;//交易成功，交易记录生成失败
                    }
                }else{//购买
                    $price_data['month'] = $datarray['order_time'];
                    $price_data['type'] = StateData::STATE_BUY;
                    if($product_info['api_name'] == 'domain'){
                        $price_data['api_type'] = $api_type;
                        $product_price = $product->get_product_price_buy_time($product_info['id'],$price_data);
                    } else {
                        $product_price = $product->get_product_price_buy_time($product_info['id'],$price_data);
                    }
                    if(empty($product_price)){
                        return -123;//产品价格获取失败
                    }
                    if($api_type == "ssl"){
                        $buy_info['product_price'] = $product_price['product_price'];
                        $buy_info['product_type_id'] = $product_info['product_type_id'];
                        $buy_info["product_des"] = $product_info["api_name"];
                        $buy_info["multi"] = explode(",",$datarray["ip_address"]);
                        $buy_info["order_time"] = ceil($datarray["order_time"]/12);
                        $product_price['product_price'] = $product->get_ssl_price($buy_info);
                        if($product_price['product_price'] <= 0){
                            return -123;
                        }
                    }
                    $totalprice = $product_price['product_price']*$datarray ['order_quantity'];
                    $totalprice = round ( $totalprice, 2 );
                    $order_log = "购买产品：[" . $product_info ['product_name'] . "][数量" . $datarray ['order_quantity'] . "][购买时限" . $datarray ['order_time'] . "个月][消费金额" . $totalprice . "元]";
                }
                break;
            case 1 :
                //增值
                $price_data["month"] = 1;
                $price_data["type"] = 0;
                $product_price = $product->get_product_price_buy_time($product_info['id'],$price_data);
                if(empty($product_price)){
                    return -2;
                }
                //消费总额=产品价格*购买数量
                $totalprice = $product_price['product_price'] * $datarray ['order_time'] * $datarray ['order_quantity'];
                // 价格
                $totalprice = round ( $totalprice, 2 );
                $order_log = "购买增值产品：[".$product_info['product_name']."]，增值大小：".($datarray['order_quantity']*$product_info['size']).$product_info['unit']."，[消费金额" . $totalprice . "元]";
                break;
            case 2 :
                // 续费
                    $business_id = $datarray['business_id'];
                    if (is_null ($business_id)) {
                        return - 10;
                    }
                    $price_data["month"] = $datarray['order_time'];
                    $price_data["type"] = 1;
                    if($product_info['api_name'] == 'domain'){
                        $price_data['api_type'] = $api_type;
                        $product_price = $product->get_product_price_buy_time($product_info['id'],$price_data);
                    } else {
                        $product_price = $product->get_product_price_buy_time($product_info['id'],$price_data);
                    }
                    if(empty($product_price)){
                        return -2;
                    }
                    //续费总金额=续费价格
                    $totalprice = $product_price['product_price'];
                    $totalprice = round($totalprice,2);
                    // 业务增值产品信息，计算续费价格
                    $rap = $this->renewals_appreciation_price($business_id,$datarray ['order_time'],$product_info['product_type_id']);
                    $totalprice += $rap ["price"];
                // 日志
                $order_log = "续费产品：[".$product_info['product_name']."][续费时限".$datarray['order_time']."个月][消费金额".$totalprice . "元]";
                break;
            case 3 :
                //升级
                // 获取原产品信息
                $source_product = $product->get_product($product_type_info["old_product_id"]);
                if (! $source_product) {
                    return - 2;
                }
                if($source_product['product_type_id'] == 1 || $source_product['product_type_id'] == 13 || $source_product['product_type_id'] == 14){
                    if($datarray ['order_time']==1){
                        $price_data["month"] = 1;
                        $price_data["type"] = 0;
                        $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                        $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        //新产品金钱
                        $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                        $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                    }elseif($datarray ['order_time']>1 && $datarray ['order_time']<=3){
                        $price_data["month"] = 3;
                        $price_data["type"] = 0;
                        $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                        $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        //新产品金钱
                        $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                        $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        if(empty($source_product_price_info) || empty($product_price_info)){
                            //如果没有3个月的价格就获取6个月的价格来计算
                            $price_data["month"] = 6;
                            $price_data["type"] = 0;
                            $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                            $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            //新产品金钱
                            $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                            $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            if(empty($source_product_price_info) || empty($product_price_info)){
                                //如果没有6个月的价格就获取12个月的价格来计算
                                $price_data["month"] = 12;
                                $price_data["type"] = 0;
                                $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                                $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                                //新产品金钱
                                $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                                $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            }
                        }
                    }elseif($datarray ['order_time']>3 && $datarray ['order_time']<=6){
                        $price_data["month"] = 6;
                        $price_data["type"] = 0;
                        $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                        $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        //新产品金钱
                        $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                        $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        if(empty($source_product_price_info) || empty($product_price_info)){
                            //如果没有6个月的价格就获取12个月的价格来计算
                            $price_data["month"] = 12;
                            $price_data["type"] = 0;
                            $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                            $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            //新产品金钱
                            $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                            $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        }
                    }elseif($datarray ['order_time']>6 && $datarray ['order_time']<=12){
                        $price_data["month"] = 12;
                        $price_data["type"] = 0;
                        $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                        $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        //新产品金钱
                        $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                        $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                    }elseif($datarray ['order_time']>12 && $datarray ['order_time']<=24){
                        $price_data["month"] = 24;
                        $price_data["type"] = 0;
                        $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                        $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        //新产品金钱
                        $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                        $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        if(empty($source_product_price_info) || empty($product_price_info)){
                            //如果没有24个月的价格就获取12个月的价格来计算
                            $price_data["month"] = 12;
                            $price_data["type"] = 0;
                            $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                            $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            //新产品金钱
                            $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                            $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        }
                    }elseif($datarray ['order_time']>24 && $datarray ['order_time']<=36){
                        $price_data["month"] = 36;
                        $price_data["type"] = 0;
                        $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                        $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        //新产品金钱
                        $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                        $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        if(empty($source_product_price_info) || empty($product_price_info)){
                            //如果没有36个月的价格就获取24个月的价格来计算
                            $price_data["month"] = 24;
                            $price_data["type"] = 0;
                            $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                            $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            //新产品金钱
                            $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                            $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            if(empty($source_product_price_info) || empty($product_price_info)){
                                //如果没有24个月的价格就获取12个月的价格来计算
                                $price_data["month"] = 12;
                                $price_data["type"] = 0;
                                $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                                $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                                //新产品金钱
                                $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                                $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            }
                        }
                    }elseif($datarray ['order_time']>= 36){
                        $price_data["month"] = 60;
                        $price_data["type"] = 0;
                        $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                        $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        //新产品金钱
                        $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                        $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                        if(empty($source_product_price_info) || empty($product_price_info)){
                            //如果没有60个月的价格就获取36个月的价格来计算
                            $price_data["month"] = 36;
                            $price_data["type"] = 0;
                            $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                            $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            //新产品金钱
                            $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                            $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                            if(empty($source_product_price_info) || empty($product_price_info)){
                                //如果没有36个月的价格就获取24个月的价格来计算
                                $price_data["month"] = 24;
                                $price_data["type"] = 0;
                                $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                                $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                                //新产品金钱
                                $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                                $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                                if(empty($source_product_price_info) || empty($product_price_info)){
                                    //如果没有24个月的价格就获取12个月的价格来计算
                                    $price_data["month"] = 12;
                                    $price_data["type"] = 0;
                                    $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                                    $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                                    //新产品金钱
                                    $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                                    $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                                }
                            }
                        }
                    }
                }else{
                    //原产品剩余金钱
                    $price_data["month"] = 12;
                    $price_data["type"] = 0;
                    $source_product_price_info = $product->get_product_price_buy_time($source_product['id'],$price_data);
                    $source_product_price=round($source_product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2);
                    //新产品金钱
                    $product_price_info = $product->get_product_price_buy_time($product_info['id'],$price_data);
                    $product_price=round($product_price_info['product_price']*$datarray ['order_time']/$price_data['month'],2); 
                }
                if(empty($product_price) || empty($source_product_price)){
                    return -2;
                }
                $totalprice = round($product_price-$source_product_price,2);
                $order_log = "产品：".$source_product['product_name']."升级到产品：[".$product_info['product_name']."][升级时限".$datarray['order_time']."个月][消费金额".$totalprice."元]";
                break;
            case 4 :
                //转正
                $price_data["month"] = $datarray['order_time'];
                $price_data["type"] = 0;
                $product_price = $product->get_product_price_buy_time($product_info['id'],$price_data);
                if(empty($product_price)){
                    return -2;
                }
                //消费总额=产品价格
                $totalprice = $product_price['product_price'];
                $totalprice = round ( $totalprice, 2 );
                $order_log = '会员：'.$member_info['user_id']."转正产品".$product_info['product_name']."[转正时限".$datarray['order_time'] . "个月][消费金额" . $totalprice . "元]";
                break;
        }
        // 计算价格错误
        if ($totalprice < 0) {
            return - 39;//扣费失败！
        }
        // 扣费后余额
        $balance = bcsub ( $member_account ['balance'], $totalprice, 2 );
        if ($balance < 0) {
            $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
            $order_error_data['order_log'] = $order_log . " 交易失败-会员账号余额不足";
            $this->order_edit($order_id,$order_error_data);
            return - 5;
        }
        // 总消费金额
        $purchases = $member_account ['purchases'] + $totalprice;
        //添加扣费后邮件提醒
        $acct_type="";
        if($order_type ==0){
            $acct_type="购买";
            if($datarray ['free_trial'] > 0)
                $acct_type="试用";
        } else if($order_type ==1){
            $acct_type="增值";
        } else if($order_type ==2){
            $acct_type="续费";
        } else if($order_type ==3){
            $acct_type="升级";
        } else if($order_type ==4){
            $acct_type="转正";
        }
        $acct_info = array('totalprice'=>$totalprice, 'balance'=>$balance,'product_name'=>$product_info['product_name'],
            'product_des'=>$product_info['product_des'],'acct_type'=>$acct_type);
        //发送扣费邮件
          if (!empty($member_info['user_mail'])){
            $content = HTMLContentForEmail("7",$acct_info,$member_info);
            postOffice($member_info['user_mail'],$content['subject'],$content['body']);
        }
        // 修改会员账户表
        if ($financial->edit_member_account($member_info['user_id'],$balance,$purchases) === false) {
            $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
            $order_error_data["order_log"] = $order_log = $order_log . " 交易失败-会员账号异常，扣费失败";
            $this->order_edit($order_id,$order_error_data);
            return - 6;
        }
        $charge = $this->order_edit ( $order_id, array (
            "charge" => $totalprice,
            "order_log" => $order_log ,
            "complete_time"=>date('Y-m-d H:i:s')
        ) );
        if (! $charge) {
            $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
            $order_error_data["order_log"] = $order_log . " 交易成功-订单表修改失败";
            $this->order_edit ( $order_id, $order_error_data);
            return - 7;
        }
        if ($order_type == 0) {// 购买时，修改产品表增加销售额和销售量
            $product_sales = $product_info ['product_sales'] + $totalprice; // 销售额
            $product_volume = $product_info ['product_volume'] + $datarray ['order_quantity']; // 销售量
            if (! $product->product_sales_volume ( $datarray ['product_id'], $product_sales, $product_volume )) {
                $order_log = $order_log . " 交易成功-产品销售量，销售额记录失败";
            }
        }
        $order_log = $order_log . "会员账户余额：[" . $balance . "]";
        if (add_transactions ( $member_info ['user_id'], $totalprice, $order_log, \Common\Data\StateData::CONSUM,  1 ,$product_info ['id'], $order_id)) {
            $order_log = $order_log . " 订单交易扣费成功";
              // 前台为处理中
//             $order_error_data["state"] = \Common\Data\StateData::PAYMENT_ORDER;
             $order_error_data['order_log'] = $order_log;
             $this->order_edit ( $order_id, $order_error_data );
            // 成功返回订单ID
            return $order_id;
        } else {
            $order_error_data["state"] = \Common\Data\StateData::FAILURE_ORDER;
            $order_error_data["order_log"] = $order_log . " 交易成功-交易记录生成失败";
            $this->order_edit ( $order_id, $order_error_data);
            return - 8;//交易成功，交易记录生成失败
        }
    }
    /**
    * 试用次数验证
    * @date: 2016年11月10日 下午5:46:28
    * @author: Lyubo
    * @param: product_id , member_id
    * @return: boolean
    */
    public function get_test_sum($product_id , $member_id , $free_trial){
        $where["product_id"] = array("eq" , $product_id);
        $where["user_id"] = array("eq" ,$member_id);
        $where["free_trial"] = array("eq" ,$free_trial);
        return $this->where($where)->count('id');
    }
    /**
    * 添加订单
    * @date: 2016年11月11日 上午10:43:56
    * @author: Lyubo
    * @param: $order_info 
    * @return: boolean
    */
    public function add_order($order_info){
        return $this->add($order_info);
    }
    /**
    * 修改订单信息
    * @date: 2016年11月11日 上午11:26:52
    * @author: Lyubo
    * @param: $order_id
    * @param: $ordet_data
    * @return:boolean
    */
    public function order_edit($order_id,$ordet_data){
        $where["id"] = array("eq" , $order_id);
        return $this->where($where)->save($ordet_data);
    }
/*****************************会员购买、下单end*******************************/
/*****************************会员开通*******************************/
    /**
    * 订单开通业务
    * @date: 2016年11月17日 下午3:49:10
    * @author: Lyubo
    * @return:
    */
    public function order_open($data){
        $product = new \Frontend\Model\ProductModel();
        $member_id = session('user_id');
        $order_info = $this->order_find($data['order_id']);
        if ($member_id != $order_info['user_id']) {
            return - 113;
        }
        $product_type_info = $product->get_product_type_info($order_info['product_type']);
        $api_ptype = $product_type_info['api_ptype'];
        if (strcmp($api_ptype, GiantAPIParamsData::PTYPE_HOST) == 0
            || strcmp($api_ptype, GiantAPIParamsData::PTYPE_HK_HOST) == 0
            || strcmp($api_ptype, GiantAPIParamsData::PTYPE_DEDE_HOST) == 0
            || strcmp($api_ptype, GiantAPIParamsData::PTYPE_USA_HOST) == 0
            || strcmp($api_ptype, GiantAPIParamsData::PTYPE_CLOUD_VIRTUAL) == 0) {
            return ['c'=>'virtualhost','a'=>'virtualhost_open','order_id'=>$data['order_id']];
        }else if(strcmp($api_ptype, GiantAPIParamsData::PTYPE_VPS) == 0){
            return ['c'=>'vps','a'=>'vps_open','order_id'=>$data['order_id']];
        }else if(strcmp($api_ptype, GiantAPIParamsData::PTYPE_CLOUD_SPACE) == 0){
            return ['c'=>'cloudspace','a'=>'cloudspace_open','order_id'=>$data['order_id']];
        }else if(strcmp($api_ptype, GiantAPIParamsData::PTYPE_FAST_CLOUDVPS) == 0){
            return ['c'=>'fastvps','a'=>'fastvps_open','order_id'=>$data['order_id']];
        }else if(strcmp($api_ptype, GiantAPIParamsData::PTYPE_CLOUD_SERVER) == 0){
            return ['c'=>'cloudserver','a'=>'cloudserver_open','order_id'=>$data['order_id']];
        }else if(strcmp($api_ptype, GiantAPIParamsData::PTYPE_SSL) == 0){
            return ['c'=>'ssl','a'=>'ssl_open','order_id'=>$data['order_id']];
        }else if(strcmp($api_ptype, GiantAPIParamsData::PTYPE_CLOUD_DATABASE) == 0){
            return ['c'=>'clouddb','a'=>'clouddb_open','order_id'=>$data['order_id']];
        }else{
            return - 2;
        }
    }
    
 /*****************************会员开通结束*******************************/


    /**
     * 业务续费，获取业务续费信息
     * @author:Guopeng
     * @param $tran_info: 业务信息
     * @return array|int 返回该业务详细信息和续费金额给确认续费页面
     */
    public function get_renewals_info($tran_info)
    {
        $tran_info["overdue_month"] = app_month($tran_info["overdue_time"]);
        $product_service = new \Frontend\Model\ProductModel();
        $renewals_info = array();
        $total_price = array();
        $product_info = $product_service->get_product($tran_info['product_id']);
        if(!$product_info)
        {
            return -2;
        }
        $renewals_price_list = $product_service->get_product_price_list($tran_info['product_id'],StateData::STATE_RENEWALS);
        if(!$renewals_price_list)
        {
            return -2;
        }
        for($i = 0;$i < count($renewals_price_list);$i++)
        {
            $price = $renewals_price_list[$i]['product_price'];
            $total_price[$i]['price'] = $price;
            $total_price[$i]['month'] = $renewals_price_list[$i]['month'];
            $renewals_info[$i]['appreciation_info'] = $this->get_apperction_list($tran_info['business_id'],$product_info['product_type_id'],$renewals_price_list[$i]['month']);
            if(!$renewals_info[$i]['appreciation_info'])
            {
                $renewals_info[$i]['appreciation_info'] = array();
            }
            $renewals_info[$i]['month'] = $renewals_price_list[$i]['month'];
        }
        return array_merge($tran_info,array('renewals_info' => $renewals_info,'total_price' => $total_price,'renewals_price_list' => $renewals_price_list));
    }

    /**
     * 业务增值产品信息，计算续费价格，公共方法
     * @author:Guopeng
     * @param: $business_id 业务ID
     * @param: $time 购买期限
     * @param: $product_type_id 产品类型
     * @param: bool $app 是否是增值
     * @return array
     */
    public function renewals_appreciation_price($business_id,$time,$product_type_id,$app = false)
    {
        $product_service = new \Frontend\Model\ProductModel();
        $price = 0;
        // 获取增值信息
        $appreciation_info = $product_service->business_appreciation_info($business_id,$product_type_id);
        for($i = 0;$i < count($appreciation_info);$i++)
        {
            $totals = 0;
            $product_info = $product_service->get_product($appreciation_info[$i]['app_product_id']);
            if($app == false)
            {
                //续费价格
                $price_data["month"] = 1;
                $price_data["type"] = StateData::STATE_RENEWALS;
                $product_price = $product_service->get_product_price_buy_time($appreciation_info[$i]['app_product_id'],$price_data);
                // 合计价格=增值大小/递增大小*产品价格*续费时间
                $totals = $appreciation_info[$i]["quanity"]/($product_info["size"] == 0 ? 1:$product_info["size"])*$product_price["product_price"]*$time;
            }
            else
            {
                $price_data["month"] = 1;
                $price_data["type"] = StateData::STATE_BUY;
                $product_price = $product_service->get_product_price_buy_time($appreciation_info[$i]['app_product_id'],$price_data);
                // 合计价格=增值大小/递增大小*产品价格*续费时间
                $totals = $appreciation_info[$i]["quanity"]/($product_info["size"] == 0 ? 1:$product_info["size"])*$product_price["product_price"]*$time;
            }
            $totals = round($totals,2);
            $appreciation_info[$i]["total"] = $totals;
            $price += $totals;
        }
        return array("appreciation_info" => $appreciation_info,"price" => $price);
    }

    /**
     * 根据业务编号和产品类别编号获取增值信息列表
     * @author:Guopeng
     * @param: $business_id 业务编号
     * @param: $product_type_id 产品类别编号
     * @param: int $time 购买期限
     * @param: bool $is_app 是否是增值
     * @return array|bool
     */
    public function get_apperction_list($business_id, $product_type_id, $time = 12, $is_app = false) {
        $product_service = new \Frontend\Model\ProductModel();
        $apperction_list = $product_service->business_appreciation_info($business_id, $product_type_id );
        if (! $apperction_list) {
            return false;
        }
        $apperction_info = array ();
        for($i = 0; $i < count($apperction_list); $i++) {
            $price_data["month"] = 1;
            $product_info = $product_service->get_product($apperction_list[$i]['app_product_id']);
            $buy_price = $product_service->get_product_price_buy_time($apperction_list [$i] ['app_product_id'],$price_data);
            $renewals_price = $product_service->get_product_price_buy_time($apperction_list [$i] ['app_product_id'],$price_data);
            $price = $is_app ? $buy_price['product_price'] : $renewals_price['product_price']*($apperction_list[$i]['quanity']/($product_info ['size'] == 0 ? 1 : $product_info ['size'])) * $time;
            $size = $apperction_list [$i] ['quanity'] . $product_info ['unit'];
            $apperction_info [$i] = array (
                'app_type' => $product_info ['product_name'],
                'app_time' => $apperction_list[$i]['create_time'],
                'size' => $size,
                'charge' => $buy_price['product_price'].'元/'.$product_info['size'].$product_info['unit'].'/月',
                'total_price' => $price . "元/" . $time . "个月",
                'ip_address'=>$apperction_list[$i]['ip_address'],
                'price'=>$price
            );
        }
        return $apperction_info;
    }
    /**
     * 错误信息设置
     * @date: 2016年12月1日 下午4:31:29
     * @author: Lyubo
     * @param: $result
     * @return: $business_code
     */
    public function setError($code){
        if(is_numeric($code))
        {
            $this->error = business_code($code);
        }else{
            $this->error = $code;
        }
    }

    /**********************************快云数据库订单**********************************/
    /**
     * 快云数据库执行购买方法
     * @author: Guopeng
     * @param: $datarray
     * @param $buy_info
     * @param $user_id
     * @return int
     * 成功返回订单号
     * 0失败 -2产品信息获取失败 -3会员信息获取失败 -4会员账号信息获取失败 -5账号余额不足
     * -6会员账户异常 -7交易成功，订单表修改失败 -8交易成功，交易记录生成失败
     */
    public function clouddb_order_do_entry($user_id,$datarray,$buy_info,$order_type)
    {
        // 获取会员信息
        $member = new \Frontend\Model\MemberModel();
        $where["user_id"] = array("eq",$user_id);
        $member_info = $member->get_member_info($where);
        if (! $member_info) {
            return - 3; // 会员信息获取失败
        }
        // 订单表插入数据
        $insert_order = $this->add_order($datarray);
        if(!$insert_order)
        {
            return 0;
        }
        // 订单编号
        $order_id = $this->getLastInsID();
        return $this->clouddb_order_transaction($order_id,$member_info,$datarray,$buy_info,$order_type);
    }
    /**
     * 快云数据库订单交易
     * @author: Guopeng
     * @return: $order_id
     */
    public function clouddb_order_transaction($order_id,$member_info,$datarray,$buy_info,$order_type){
        $totalprice = 0;//总价格
        $order_log = "";//订单信息
        $order_error_data = [];//订单错误信息
        $price_data = []; //获取价格参数
        $product = new \Frontend\Model\ProductModel();
        $clouddb = new \Frontend\Model\ClouddbModel();
        $product_info = $product->get_product($datarray["product_id"]);
        if (!$product_info) {
            return - 2;
        }
        //获取会员账户信息
        $financial = new \Frontend\Model\FinancialModel();
        $member_where['user_id'] = ["eq",$member_info["user_id"]];
        $member_account = $financial->account_info($member_where);
        if (! $member_account) {
            $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
            $order_error_data["order_log"] = $order_log."交易失败-会员账号信息获取失败";
            $this->order_edit($order_id,$order_error_data);
            return -4;//会员账号信息获取失败
        }
        switch ($order_type){
            case 0://新增
                if($datarray['free_trial'] == 0){
                    $price_data['month'] = $datarray['order_time'];
                    $price_data['type'] = StateData::STATE_BUY;
                    $product_price = $clouddb->getPrice($buy_info);
                    if($product_price === false){
                        return -2;//产品价格获取失败
                    }
                    $totalprice = $product_price * $datarray['order_quantity'];
                    $totalprice = round($totalprice,2);
                    $order_log = "购买产品：[快云数据库][数量".$datarray['order_quantity']."][购买时限".$datarray ['order_time']."个月][消费金额".$totalprice."元]";
                }
                break;
            case 2://续费
                $totalprice = $clouddb->getPrice($buy_info);
                if($totalprice === false){
                    return -2;//产品价格获取失败
                }
                $totalprice = round($totalprice,2);
                // 日志
                $order_log = "续费产品：[快云数据库][续费时限".$datarray['order_time']."个月][消费金额".$totalprice ."元]";
                break;
            case 3 :
                //升级
                $totalprice = round($clouddb->getPrice($buy_info));
                $order_log = "升级产品：".$product_info['product_name']."[升级时限".$datarray['order_time']."个月][消费金额".$totalprice."元]";
                break;
        }
        // 计算价格错误
        if ($totalprice < 0) {
            return - 39;//扣费失败！
        }
        // 扣费后余额
        $balance = bcsub($member_account['balance'],$totalprice,2);
        if ($balance < 0) {
            $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
            $order_error_data["order_log"] = $order_log . " 交易失败-会员账号余额不足";
            $this->order_edit($order_id,$order_error_data);
            return - 5;
        }
        // 总消费金额
        $purchases = $member_account['purchases'] + $totalprice;
        //发送扣费邮件
        $acct_type="";
        if($order_type ==0){
            $acct_type="购买";
            if($datarray ['free_trial'] > 0)
                $acct_type="试用";
        } else if($order_type ==1){
            $acct_type="增值";
        } else if($order_type ==2){
            $acct_type="续费";
        } else if($order_type ==3){
            $acct_type="升级";
        } else if($order_type ==4){
            $acct_type="转正";
        }
        $acct_info = array(
            'totalprice'=>$totalprice,
            'balance'=>$balance,
            'product_name'=>$product_info["product_name"],
            'product_des'=>$product_info["product_des"],
            'acct_type'=>$acct_type);
        //发送扣费邮件
        if(!empty($member_info['user_mail'])){
            $content = HTMLContentForEmail("7",$acct_info,$member_info);
            postOffice($member_info['user_mail'],$content['subject'],$content['body']);
        }
        // 修改会员账户表
        if ($financial->edit_member_account ( $member_info ['user_id'], $balance, $purchases ) === false) {
            $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
            $order_error_data["order_log"] = $order_log . " 交易失败-会员账号异常，扣费失败";
            $this->order_edit($order_id,$order_error_data);
            return - 6;
        }
        $charge = $this->order_edit ( $order_id, array (
            "charge" => $totalprice,
            "order_log" => $order_log ,
            "complete_time"=>date('Y-m-d H:i:s')
        ) );
        if (!$charge){
            $order_error_data['state'] = 5;//状态：0失败、1成功、2待处理3处理中4审核中5已删除6已付款
            $order_error_data["order_log"] = $order_log . " 交易成功-订单表修改失败";
            $this->order_edit ( $order_id, $order_error_data);
            return - 7;
        }
        $order_log = $order_log . "会员账户余额：[" . $balance . "]";
        if (add_transactions($member_info['user_id'],$totalprice,$order_log,StateData::CONSUM,1,$datarray["product_id"],$order_id)){
            $order_log = $order_log." 订单交易成功";
            // 前台为处理中
            $order_error_data["state"] = StateData::PAYMENT_ORDER;
            $order_error_data['order_log'] = $order_log;
            $this->order_edit ( $order_id, $order_error_data );
            // 成功返回订单ID
            return $order_id;
        } else {
            $order_error_data["state"] = StateData::SUCCESS_ORDER;
            $order_error_data["order_log"] = $order_log . " 交易成功-交易记录生成失败";
            $this->order_edit ( $order_id, $order_error_data);
            return - 8;//交易成功，交易记录生成失败
        }
    }
    /*********************************快云数据库订单 end**********************************/
}