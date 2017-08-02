<?php
namespace Frontend\Model;
use Frontend\Model\BusinessModel;
use Common\Aide\AgentAide;
use Common\Data\GiantAPIParamsData;
class DomainModel extends BusinessModel{
    protected $trueTableName = 'agent_domain_business';
    
 public function conver_par(){
        $date =request();
        $where["bs.user_id"] = array("eq",session("user_id"));
        if(!empty($date['select'])){
            $select = clearXSS($date['select']);
            $map['bs.api_bid'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map['bs.domain_name'] = array("like" , "%$select%");
            $map['_logic'] = 'OR';
            $map["bs.beizhu"] = array("like" ,"%$select%");
            $where['_complex'] = $map;
        }
        return $where;
    }
    /**
    * 跳转中国数据控制面板
    * @date: 2016年10月29日 下午2:52:54
    * @author: Lyubo
    * @return: boolean
    */
    public function domain_panel(){
         $data =request();
         $api_bid = $data["business_id"];
         $user_id = session("user_id");
         $info = $this->get_api_bid_one($api_bid);
         if($info){
             try {
                $agent = new AgentAide();
                 $transaction = $agent->servant;
                 $result = $transaction->domainskip($api_bid);
                 $log_message = "ptype:" .GiantAPIParamsData::PTYPE_DOMAIN .'||tid=15';
                 api_log($user_id,$log_message,$result,"跳转中国数据控制面板");
                 $json = json_decode ( $result ,true);
             } catch (\Exception $e) {
                 $log_message = "ptype:" .GiantAPIParamsData::PTYPE_DOMAIN .'||tid=15';
                 if(empty($result)){
                     $result = $e->getMessage();
                 }
                 api_log($user_id,$log_message,$result,"跳转中国数据控制面板失败");
                 return  - 9;
             }
             return $json;
         }
    }
    /**
    * 域名产品信息
    * @date: 2016年11月20日 下午1:54:22
    * @author: Lyubo
    * @return: array
    */
    public function domain_product(){
        $product = new \Frontend\Model\ProductModel();
        $product_type_list = $product->get_type_list('4');//4为域名的顶级分类
         foreach ( $product_type_list as $ptl ) {
           $product_list[$ptl['api_ptype']]= $product->domain_info($ptl ['id']);
        }
	  return $product_list;
    }
    /**
    * 默认选中域名
    * @date: 2016年11月20日 下午4:52:42
    * @author: Lyubo
    * @return: array
    */
    /**
     * 获取默认域名信息
     */
    public function get_default_domain() {
            $product = new \Frontend\Model\ProductModel();
            // 获取信息
            $result = WebSiteConfig ( \Common\Data\StateData::SYS_DEFAULT_DOMAIN );
            // 以逗号分隔
            $value = explode ( ",", $result  ["default_domain"] );
            foreach ($value as $key=>$val){
                $default_domain[$key] = $product->get_product($val);
            }
        return $default_domain;
    }
    /**
    * 域名查询
    * @date: 2016年11月21日 上午11:46:38
    * @author: Lyubo
    * @param: $domain_list,$domain_name
    * @return:
    */
    public function doamin_query($domain_list,$domain,$check_suffix){
        $product = new \Frontend\Model\ProductModel();
        $ids = "";
        for ($i=0;$i<count($domain_list);$i++){
            if($i<count($domain_list)-1){
                $ids.=$domain_list[$i].",";
            }else{
                $ids.=$domain_list[$i];
            }
        }
        $proiduct_list=$product->product_by_ids($ids);
         //域名后缀
		$suffix=array();
		for ($i=0;$i<count($proiduct_list);$i++){
			$pro=$proiduct_list[$i];
			if(strcmp(GiantAPIParamsData::PTYPE_DOMAIN,$pro["api_name"])!=0){
                return - 2;
            }
            $suffix[$i] = $pro["product_name"];
        }
        try {
            $format_id = 2; 
            $agent = new AgentAide();
            $transaction = new \Agent\Transaction($format_id , $agent->config);
            $result = $transaction->new_domain_quary($domain, $suffix);
		} catch ( \Exception $e ) {
		    return -9;
		}
		$xstr=simplexml_load_string ( $result );
        if($xstr->code!=0){
			return $xstr->code;
		}
		//可以注册列表
		$yesList=array();
		//查询失败列表
		$failList=array();
		//不可注册列表
		$noList=array();
		$yes=(array)$xstr->info->yesList;
		$fail=(array)$xstr->info->failList;
		$no=(array)$xstr->info->noList;
		if (count($yes['node']) == 1) {
            $yesList[0] = $yes['node'];
        } else {
            for ($i = 0; $i < count($yes['node']); $i ++) {
                $yesList[$i] = $yes['node'][$i];
            }
        }
        if (count($no['node']) == 1) {
            $noList[0] = $no['node'];
        } else {
            for ($i = 0; $i < count($no['node']); $i ++) {
                $noList[$i] = $no['node'][$i];
            }
        }
        if (count($fail['node']) == 0) {
            $failList[0] = $fail['node'];
        } else {
            for ($i = 0; $i < count($fail['node']); $i ++) {
                $failList[$i] = $fail['node'][$i];
            }
        }
        //修改状态
        for ($i=0;$i<count($proiduct_list);$i++){
            unset($proiduct_list[$i]['config']);
            $product_name=$proiduct_list[$i]["product_name"];
            //可以注册
            if(in_array($product_name,$yesList)){
                $proiduct_list[$i]["is_reg"]=1;
            }
            //查询失败
            if(in_array($product_name,$failList)){
                $proiduct_list[$i]["is_reg"]=0;
            }
            //不可以注册
            if(in_array($product_name,$noList)){
                $proiduct_list[$i]["is_reg"]=2;
            }
            $proiduct_list[$i]["product_name"]=$domain.$product_name;
        }
        return $proiduct_list;
    }
    /**
    * 查询所有域名
    * @date: 2016年11月22日 下午5:43:51
    * @author: Lyubo
    * @param: $default_domain
    * @return: array
    */
    public function domain_query_all($default_domain,$all_domain){
        $parms = request();
        $domain  = $parms['domain'];
        $domain_type = $parms['domain_type'];
        $default_domain_id = [];
        $all_domain_id = [];
        $query_id = '0';
        $product = new \Frontend\Model\ProductModel();
       //循环默认域名
        foreach ($default_domain as $def_key=>$def_val){
            foreach($all_domain as $all_key=>$all_val){
                if($all_val['id'] == $def_val['id']){
                    unset($all_domain[$all_key]);
                }
            }
        }
        //循环全部域名
        foreach ($all_domain as $key=>$val){
            $all_domain_id[$query_id++] = $val['id'];
        }
        $ids = "";//全部域名和默认域名的ID匹配去重
        for ($i=0;$i<count($all_domain_id);$i++){
            if($i<count($all_domain_id)-1){
                $ids.=$all_domain_id[$i].",";
            }else{
                $ids.=$all_domain_id[$i];
            }
        }
        //查询产品数据
        $search_all=$product->product_by_ids($ids,$domain_type);
         //域名后缀
        $suffix=array();
        for ($i=0;$i<count($search_all);$i++){
            $pro=$search_all[$i];
            if(strcmp(GiantAPIParamsData::PTYPE_DOMAIN,$pro["api_name"])!=0){
                return - 2;
            }
            $suffix[$i] = $pro["product_name"];
        }
        try {
            $format_id = 2; 
            $agent = new AgentAide();
            $transaction = new \Agent\Transaction($format_id , $agent->config);
            $result = $transaction->new_domain_quary($domain, $suffix);
        } catch ( \Exception $e ) {
            return -9;
        }
        $xstr=simplexml_load_string ( $result );
        if($xstr->code!=0){
            return $xstr->code;
        }
        //可以注册列表
        $yesList=array();
        //查询失败列表
        $failList=array();
        //不可注册列表
        $noList=array();
        $yes=(array)$xstr->info->yesList;
        $fail=(array)$xstr->info->failList;
        $no=(array)$xstr->info->noList;
        if (count($yes['node']) == 1) {
            $yesList[0] = $yes['node'];
        } else {
            for ($i = 0; $i < count($yes['node']); $i ++) {
                $yesList[$i] = $yes['node'][$i];
            }
        }
        if (count($no['node']) == 1) {
            $noList[0] = $no['node'];
        } else {
            for ($i = 0; $i < count($no['node']); $i ++) {
                $noList[$i] = $no['node'][$i];
            }
        }
        if (count($fail['node']) == 0) {
            $failList[0] = $fail['node'];
        } else {
            for ($i = 0; $i < count($fail['node']); $i ++) {
                $failList[$i] = $fail['node'][$i];
            }
        }
        //修改状态
        for ($i=0;$i<count($search_all);$i++){
            unset($search_all[$i]['config']);
            $product_name=$search_all[$i]["product_name"];
            //可以注册
            if(in_array($product_name,$yesList)){
                $search_all[$i]["is_reg"]=1;
            }
            //查询失败
            if(in_array($product_name,$failList)){
                $search_all[$i]["is_reg"]=0;
            }
            //不可以注册
            if(in_array($product_name,$noList)){
                $search_all[$i]["is_reg"]=2;
            }
            $search_all[$i]["product_name"]=$domain.$product_name;
        }
        return $search_all;
  }
    /**
    * 显示域名注册
    * @date: 2016年11月27日 上午11:11:48
    * @author: Lyubo
    * @param: $memeber_id,$product_id,$domain
    */
  public function show_domain_register($member_id,$product_id,$domain){
      $product = new \Frontend\Model\ProductModel();
      //获取最近一条联系人信息
      $contact=$this->get_recently_contact ( $member_id );
      //获取域名产品信息
      $info=$product->get_product($product_id);
      if(strcmp(GiantAPIParamsData::PTYPE_DOMAIN,$info["api_name"])!=0){
          return -2;
      }
      //查询域名是否注册
      $q[0]=$info["id"];
      $query=$this->doamin_query($q,$domain);
      if(!is_array($query)){
          return $query;
      }
      //无法注册
      if($query[0]["is_reg"]==2){
          return -42;
      }
      //查询失败
      if($query[0]["is_reg"]==0){
          return -43;
      }
      //封装信息
      $params["contact"]=$contact;
      $params["info"]=$info;
      $params["reg_domain"]=$domain;
      return $params;
  }
  /**
  * 获取最近1条联系人信息
  * @date: 2016年11月27日 上午11:16:07
  * @author: Lyubo
  * @param: memeber_id
  */
  public function get_recently_contact($member_id){
      $domain_contact = M("domain_contact");
      $where['user_id'] = ["eq",$member_id];
      return $domain_contact->field("id,domain_business_id,user_id,create_time,dwmc,dwmcYW,zcrX,zcrM,zcrGJ,zcrSF,zcrCS,zcrDZ,zcrDZ2,zcrYZBM,zcrYX,zcrDH,zcrCZ,lxrX,lxrM,lxrGJ,lxrSF,lxrCS,lxrDZ,lxrDZ2,lxrYZBM,lxrYX,lxrDH,lxrCZ,zjhm")
      ->where($where)
      ->order("create_time DESC")
      ->find();
  }
  /**
  * 域名注册调用接口
  * @date: 2016年11月27日 下午1:48:21
  * @author: Lyubo
  * @param: $data
  * @return: code
  */
  public function domain_register($product_id, $domain,$member_id, $buy_time, $buy_num, $contact, $api_type){
      $product = new \Frontend\Model\ProductModel();
      $order = new \Frontend\Model\OrderModel();
      //添加联系人表
      $contact ["user_id"] = $member_id;
      $contact ["create_time"] = date('Y-m-d H:i:s');
      $domain_contact = M("domain_contact");
      $add_contact = $domain_contact->add( $contact );
      if (! $add_contact) {
          return - 17;//添加联系人信息失败
      }
      $contact_id = $domain_contact->getLastInsID();
      $params ["user_id"] = $member_id;
      $params ["product_id"] = $product_id;
      $params ["free_trial"] = 0;
      $params ["order_time"] = $buy_time;
      $params ["order_quantity"] = $buy_num;
      // 获取产品信息
      $product_info = $product->get_product($product_id);
      if (! $product_info || $product_info ["product_state"] == 0) {
          return - 2;//产品信息获取失败
      }
      //拼接域名
      $domain = $domain . $product_info ["product_name"];
      //录入订单
      $order_id = $order->order_do_entry ( $params, $member_id, \Common\Data\StateData::NEW_ORDER,$product_info,null,$domain,$api_type );
      if ($order_id <= 0) {
          return $order_id;
      }
      try {
          $agent = new AgentAide();
          $transaction = $agent->servant;
          //处理联系人信息为API参数
          $api_contact = $this->api_contact ( $contact );
          /**
           * 调用接口，域名注册
          */
          $result = $transaction->domainregistration ( $domain, $api_contact, $buy_time, $api_type );
          $json = json_decode ( $result );
          	
          //修改订单失败，默认失败，添加业务表信息成功时再修改成功
          $order_data['state'] =\Common\Data\StateData::FAILURE_ORDER;
          $order->order_edit ( $order_id, $order_data );
          $log_message = '域名注册，域名：[' . $domain . ']';
          api_log ( $member_id, "ptype:".GiantAPIParamsData::PTYPE_DOMAIN."--pname:".GiantAPIParamsData::PNAME_DOMAIN_REGISTRATION."--tid:".GiantAPIParamsData::TID_DOMAIN, $result, $log_message );
      } catch ( \Exception $e ) {
          $log_message = '域名注册，域名：[' . $domain . ']接口调用失败失败';
          api_log ( $member_id, "ptype:".GiantAPIParamsData::PTYPE_DOMAIN."--pname:".GiantAPIParamsData::PNAME_DOMAIN_REGISTRATION."--tid:".GiantAPIParamsData::TID_DOMAIN, $e->getMessage(), $log_message );
          return -9;
      }
      $xstr = json_decode($result, true);
      if($xstr['code'] != 0){
          return $xstr['code'];
      }
      try {
          /**
           * 调用接口，获取业务信息
           */
          $result = $transaction->getdomainlist ();
          $log_message = '获取域名业务信息成功';
          api_log ( $member_id, "ptype:".GiantAPIParamsData::PTYPE_DOMAIN."--pname:".GiantAPIParamsData::PNAME_DOMAIN_LIST."--tid:".GiantAPIParamsData::TID_DOMAIN, $result, $log_message );
          $json = json_decode ( $result , true);
          if ($json['code'] != 0) {
              return $json['code'];
          }
          //域名业务列表
          $domain_list = $json['info'];
          //域名业务编号
          $domain_business_id = null;
          //遍历域名信息
          for($i = 0; $i < count ( $domain_list ); $i ++) {
              if (strcmp($domain,$domain_list [$i]['ymmc'])== 0) {
                  $api_bid = $domain_list [$i]['ymmc'];
                  //封装域名业务信息
                  array_splice($params,2,3);//删除params数组总free_trial和order_time和order_quantiyy字段
                  $params ["domain_name"] = $domain_list [$i]['ymmc'];
                  $params ["create_time"] = $domain_list [$i]['gmsj'];
                  $params ["overdue_time"] = $domain_list [$i]['dqsj'];
                  $params ["user_id"] = $member_id;
                  $params ["api_bid"] = $domain_list [$i]['ywbh'];
                  $params ["provider"] = $domain_list [$i]['ymzcs'];
                  $params ["state"] = $domain_list [$i]['zt'];
                  $params ["product_id"] = $product_info ["id"];
                  $params ["product_name"] = $product_info ["product_name"];
                  //add by xuanyd 添加字段参数，区分是万网/中国数据域名
                  $params ["api_type"] = $api_type;
                  //add end
                  //添加域名业务信息
                  $add_domain = $this->add( $params );
                  if (! $add_domain) {
                      return - 18;
                  }
                  $domain_business_id = $this->getLastInsID();
              }
          }
          if ($domain_business_id != null) {
              //修改订单成功
              $order_data['state'] = \Common\Data\StateData::SUCCESS_ORDER;
              $order->order_edit ( $order_id, $order_data );
              //修改域名业务ID信息
              $upd_contact['domain_business_id'] = $domain_business_id;
              $contact_where['id'] = ["eq",$contact_id];
              $this->update_domain_contact($contact_where,$upd_contact);
              //删除没有域名业务ID的联系人信息
              $del_contact['user_id'] = ['eq',$member_id];
              $del_contact['domain_business_id'] = ['exp','is null'];
              $this->delete_domain_contact ($del_contact);
              return - 1;
          }
          return -15;
      } catch ( \Exception $e ) {
          $log_message = '域名注册成功,添加域名业务信息失败';
          api_log ( $member_id, "ptype:".GiantAPIParamsData::PTYPE_DOMAIN."--pname:".GiantAPIParamsData::PNAME_DOMAIN_LIST."--tid:".GiantAPIParamsData::TID_DOMAIN, $e->getMessage(), $log_message );
          return - 122;
      }
  }
  /**
  * 处理联系人为API参数
  * @date: 2016年11月27日 下午2:31:39
  * @author: Lyubo
  * @param: $contact
  * @return: $api_contact
  */
  private function api_contact($contact){
          $domainparams = "";
          $domainparams .= "dwmc:" . $contact ["dwmc"];
          $domainparams .= "-dwmcYW:" . $contact ["dwmcYW"];
          $domainparams .= "-zcrX:" . $contact ["zcrX"];
          $domainparams .= "-zcrM:" . $contact ["zcrM"];
          $domainparams .= "-zcrGJ:" . $contact ["zcrGJ"];
          $domainparams .= "-zcrSF:" . $contact ["zcrSF"];
          $domainparams .= "-zcrCS:" . $contact ["zcrCS"];
          $domainparams .= "-zcrDZ:" . $contact ["zcrDZ"];
          $domainparams .= "-zcrDZ2:" . $contact ["zcrDZ2"];
          $domainparams .= "-zcrYZBM:" . $contact ["zcrYZBM"];
          $domainparams .= "-zcrYX:" . $contact ["zcrYX"];
          $domainparams .= "-zjhm:" . $contact ["zjhm"];
          //-号替换为+号
          $domainparams .= "-zcrDH:" . strtr ( $contact ["zcrDH"], "-", "+" );
          //-号替换为+号
          $domainparams .= "-isPersonal:" . $contact ["isPersonal"];
          $domainparams .= "-zcrName:" . $contact ["zcrName"];
          $domainparams .= "-zcrNameEn:" . $contact ["zcrNameEn"];
          $domainparams .= "-zcrYX:" . $contact ["zcrYX"];
          $domainparams .= "-isPersonal:" . $contact ["isPersonal"];
          $domainparams .= "-zcrName:" . $contact ["zcrName"];
          $domainparams .= "-zcrNameEn:" . $contact ["zcrNameEn"];
          $domainparams .= "-zcrYX:" . $contact ["zcrYX"];
          return $domainparams;
  }
  /**
  * 域名续费展示
  * @date: 2016年12月3日 上午11:41:25
  * @author: Lyubo
  * @param: $domain_id,$member_id
  * @return: $domain_info
  */
  public function domain_renewals_info($domain_id,$member_id){
      $where["db.id"] = ["eq",$domain_id];
      $where["db.user_id"] = ["eq",$member_id];
      return $this->alias(' db ')
      ->field("db.id,domain_name,db.user_id,db.create_time,db.overdue_time,api_bid,provider,state,product_id,db.product_name, api_type")
      ->join("left join ". C('DB_PREFIX')."product p on (p.id=db.product_id)")
      ->where($where)->find();
  }
  /**
  * 域名续费
  * @date: 2016年12月3日 下午3:19:20
  * @author: Lyubo
  * @param: $domain_id,$member_id,$renewals_time
  * @return: boolean
  */
  public function doamin_renewals($domain_id,$member_id,$renewals_time){
      $product = new \Frontend\Model\ProductModel();
      $order = new \Frontend\Model\OrderModel();
      $where['id'] = ["eq",$domain_id];
      $where["user_id"] = ["eq",$member_id];
      $domain_info = $this->where($where)->find();
      //获取不到业务信息或业务状态错误
      if (! $domain_info || $domain_info ["state"] != 1) {
          return - 10;
      }
      //计算过期时间
      $over_day=overdue_day($domain_info["overdue_time"]);
      if($over_day<-31){
          return -36;
      }
      //获取不到接口业务编号
      if (! $domain_info ["api_bid"]) {
          return - 11;
      }
      // 获取产品信息
      $product_info = $product->get_product ( $domain_info ["product_id"] );
      if (! $product_info || $product_info ["product_state"] == 0) {
          return - 2;
      }
      $params ["product_id"] = $domain_info ["product_id"];
      $params ["order_time"] =$renewals_time;
      $params ["order_quantity"] =1;
      $params["business_id"]=$domain_id;
      //录入订单
      $product_type_info['business_id'] = $domain_info['id'];
      $order_id = $order->order_do_entry ( $params, $domain_info ["user_id"], \Common\Data\StateData::RENEWALS_ORDER,$product_info,null,null,'5',$domain_info['id'] );
      if ($order_id <= 0) {
          return $order_id;
      }
      $json=null;
      try {
          $agent = new AgentAide();
          $transaction = $agent->servant;
          /**
           * 调用接口，域名续费
          */
          $result=$transaction->domainrenewals ( $domain_info ["api_bid"], $renewals_time);
          $json = json_decode ( $result ,true);
          //修改订单失败，默认失败，续费成功时再修改成功
          $order_data["state"] = \Common\Data\StateData::FAILURE_ORDER;
          $order->order_edit ( $order_id, $order_data);
          api_log($member_id,"ptype:".GiantAPIParamsData::PTYPE_DOMAIN."--pname:".GiantAPIParamsData::PNAME_DOMAIN_RENEWALS."--tid:".GiantAPIParamsData::TID_DOMAIN, $result,"域名续费");
      } catch ( \Exception $e ) {
          api_log($member_id,"ptype:".GiantAPIParamsData::PTYPE_DOMAIN."--pname:".GiantAPIParamsData::PNAME_DOMAIN_RENEWALS."--tid:".GiantAPIParamsData::TID_DOMAIN,$e->getMessage(),"域名续费失败—接口调用失败");
          return - 9;
      }
      if ($json == null) {
          return - 9;
      }
      //返回API code
      if ($json['code'] != 0) {
          return $json['code'];
      }
      /**
       * 域名业务续费时间
       */
      $ren=$this->domain_renewals($domain_id,$renewals_time);
      if(!$ren){
          return 0;
      }
      //修改订单成功
      $order_data["state"] = \Common\Data\StateData::SUCCESS;
      $order->order_edit ( $order_id, $order_data);
      return -1;
  }
  /**
   * 域名续费
   * @param $business_id
   */
  public function domain_renewals($domain_id,$month){
      $where['id'] = $domain_id;
      $params["overdue_time"]="DATE_ADD(overdue_time, INTERVAL ".$month." MONTH)";
      return $this->where($where)->save($params);
  }
  /**
  * 修改域名联系人表business_id
  * @date: 2016年11月27日 下午3:21:29
  * @author: Lyubo
  * @param: $contact_id，$upd_contact
  * @return: boolean
  */
  public function update_domain_contact($contact_where,$upd_contact){
      $domain_contact = M("domain_contact");
      return $domain_contact->where($contact_where)->save($upd_contact);
  }
  /**
  * 删除没有域名业务ID的联系人信息
  * @date: 2016年11月27日 下午3:35:30
  * @author: Lyubo
  * @param: $member_id，$del_contact
  * @return: boolean
  */
  public function delete_domain_contact($del_contact){
      $domain_contact = M("domain_contact");
      return $domain_contact->where($del_contact)->delete();
  }
}