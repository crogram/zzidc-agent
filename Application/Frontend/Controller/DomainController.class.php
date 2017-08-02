<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;
use Common\Data\GiantAPIParamsData;
/**
* 域名
* @date: 2016年10月25日 下午3:51:53
* @author: Lyubo
*/
class DomainController extends FrontendController
{
    public function _initialize()
    {
        parent::_initialize();
    }
    /**
    * 获取该用户所有域名业务
    * @date: 2016年10月25日 下午4:38:44
    * @author: Lyubo
    * @return: $list
    */
   public function domainlist(){
       if (! session('?user_id')) {
           redirect(U('/Frontend/Login/login', [], false), 0, '');
           die();
       }
       $dm = new \Frontend\Model\DomainModel();
        $where = $dm->conver_par();
        $order = I("get.order");
        $info = $dm->get_domain_business_list($where,10,$order,GiantAPIParamsData::PTYPE_DOMAIN);
        $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list']
        ]);
        $this->display();
   }
   /**
    * 修改备注
    * @date: 2016年10月27日 上午11:23:22
    * @author: Lyubo
    * @param: remark，business_id
    * @return: boolean
    */
   public function remark(){
       if (! session('?user_id')) {
         redirect(U('/Frontend/Login/login', [], false), 0, '');
           die();
       }
       $dm = new \Frontend\Model\DomainModel();
       $result = $dm->remark_edit();
       if($result){
           $data = "ok";
           $this->ajaxReturn($data,'json');
       }else{
           $data = "fail";
           $this->ajaxReturn($data,'json');
       }
   }
   /**
   * 跳转中国数据控制面板
   * @date: 2016年10月29日 下午2:50:07
   * @author: Lyubo
   * @param: $business_id
   * @return:
   */
    public function panel(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $dm = new \Frontend\Model\DomainModel();
        $result = $dm->domain_panel();
        if($result["code"] == '0'){
            $data["status"] = "ok";
            $data["url"] = $result['info']["ym"];
            $this->ajaxReturn($data,'json');
        }else{
            $data = "fail";
            $this->ajaxReturn($data,'json');
        }
    }
    /**
    * 域名页面
    * @date: 2016年11月20日 下午3:16:40
    * @author: Lyubo
    * @return:
    */
    public function domain(){
        $dm = new \Frontend\Model\DomainModel();
        $product = $dm->domain_product();
        $default_domain = $dm->get_default_domain();
        $this->assign([
            'endomain' =>$product['endomain'],
            'cndomain' =>$product['cndomain'],
            'domian_list'=>array_merge($product['endomain'],$product['cndomain']),
            'default_domain'=>$default_domain,
           ]);
        $this->display();
    }
    /**
     * 域名查询
     * @date: 2016年11月20日 下午1:41:42
     * @author: Lyubo
     * @param: $domain,$suffix   域名参数，域名后缀
     * @return:  array
     */
    public function domain_query()
    {
        $dm = new \Frontend\Model\DomainModel();
        $date = request();
        $domain_name = $date['domain'];
        $suffix = $date['suffix'];
        $domain_list = $date['domain_list'];
        if (empty($domain_name)) {
            $this->error('请输入要查询的域名', U('frontend/domain/domain', '', false));
        }
        if (empty($suffix)) {
            $this->error('请选择域名后缀', U('frontend/domain/domain', '', false));
        }
        if (strpos($suffix, "(中文域名)") != false) {
            if (! checkCnDomainName($domain_name)) {
                $msg = '中文域名"中只能包含中文、字母、数字和"-"字符，不能以"-"字符开头或结束，至少需要含有一个中文文字，最多不超过20个字符!';
                $url = U('frontend/domain/domain', '', false);
                $this->error($msg,$url);
            }
            $cn_domain = str_replace("(中文域名)", '', $suffix);
        } else 
            if (strpos($suffix, "(英文域名)") != false) {
                if (! checkEnDomainName($domain_name)) {
                    $msg = '"英文域名"中只能包含字母、数字和"-"字符！并且不能以"-"字符开头、结尾！';
                    $url = U('frontend/domain/domain', '', false);
                    $this->error($msg,$url);
                }
                $en_domain = str_replace("(英文域名)", '', $suffix);
            }
        if (empty($cn_domain)) {
            $domain = $en_domain;
            $domain_type = "endomain";
        }else{
            if(empty($en_domain)){
                $domain = $cn_domain;
                $domain_type = "cndomain";
            }
        }
        $proiduct_list = $dm->doamin_query($domain_list, $domain_name, $domain);
        if (! is_array($proiduct_list)) {
            $messge = business_code($proiduct_list);
            $url = U('frontend/domain/domain', '', false);
            $this->error($messge,$url);
        } else {
             $default_domain = $dm->get_default_domain();
            $this->assign([
                "proiduct_list" => $proiduct_list,
                "domain_name" => $domain_name,
                "api_ptype" => $domain_type,
                "default_domain" =>$default_domain,
            ]);
            $this->display();
        }
             
    }
    /**
    * 域名查询默认之外
    * @date: 2016年11月22日 下午5:39:05
    * @author: Lyubo
    * @param: state
    * @return:
    */
    public function domain_query_all(){
		$domain = I("post.domain");
        $dm = new \Frontend\Model\DomainModel();
        $default_domain = $dm->get_default_domain();
        $domain_product = $dm->domain_product();
        $all_domain = array_merge($domain_product['endomain'],$domain_product['cndomain']);
        $domain_query_all = $dm->domain_query_all($default_domain,$all_domain);
        $this->assign([
            "search_all"=>$domain_query_all,
			"domain"=>$domain
        ]);
        $data['status']  = 'ok';
        $data['result'] = $this->fetch("Domain/search_all");
        $this->ajaxReturn($data);
    }
    /**
    * 域名注册
    * @date: 2016年11月24日 下午1:41:38
    * @author: Lyubo
    * @param: $product_id,$domain,$suffix
    * @return: boolean
    */
    public function domainRegister(){
        $data =request(true);
        if (! session('?user_id')) {
            session('backward',__SELF__);
           redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $dm = new \Frontend\Model\DomainModel();
        $product = new \Frontend\Model\ProductModel();
        if(IS_POST){
            //封装联系人参数
            $contact ["dwmc"] = $data ["dwmc"];
            $contact ["dwmcYW"] = trim($data ["dwmcYW"]);
            $contact ["zjhm"] = $data ["zjhm"];
            $contact ["zcrX"] = $data ["zcrX"];
            $contact ["zcrM"] = $data ["zcrM"];
            $contact ["zcrGJ"] = $data ["zcrGJ"];
            $contact ["zcrSF"] = $data ["zcrSF"];
            $contact ["zcrCS"] = $data ["zcrCS"];
            $contact ["zcrDZ"] = trimall($data ["zcrDZ"]);
            $contact ["zcrDZ2"] = trimall($data ["zcrDZ2"]);
            $contact ["zcrYZBM"] = $data ["zcrYZBM"];
            $contact ["zcrYX"] = $data ["zcrYX"];
            $contact ["zcrDH"] = $data ["zcrDH"];
            $contact ['zcrCZ'] = $data ['zcrDH'];
                //验证联系人信息
              $var_code = $this->domain_var_contact($contact);
              if ($var_code != - 1) {
                  $message = business_code ( $var_code );
                  $this->error($message,U('frontend/domain/domainRegister', ['product_id'=>$data['product_id'],'domain'=>$data['domain']], false));
              }
              $api_type = $product->getApiType($_POST ["price_id"]);
              $result = $dm->domain_register($data["product_id"],$data['domain'],session("user_id"),$api_type["month"],'1',$contact,$api_type["api_type"]);
              $message = business_code ( $result );
              if ($result == - 1) {
                  $this->success("域名注册成功，你可以在[会员中心]查看订单信息和域名信息",U('frontend/domain/domainlist' ,false));
              }else{
                 $this->error($message,U('frontend/domain/domainRegister', ['product_id'=>$data['product_id'],'domain'=>$data['domain']], false));
              }
        }else{
            $api_type = '5';
            $price_list = $product->get_product_price_list($data['product_id'],\Common\Data\StateData::STATE_BUY,$api_type);
            $result = $dm->show_domain_register ( session("user_id"), $data ["product_id"], $data ["domain"] );
            $result['product_price_list'] = $price_list;
            if (! is_array ( $result )) {
                $msg = business_code ( $result );
                $this->error($msg,U('frontend/domain/domain', '', false));
            }
            $this->assign("result",$result);
            $this->display("Domain/domainRegister");
        }
    }
    /**
    * 验证联系人信息
    * @date: 2016年11月27日 上午11:54:11
    * @author: Lyubo
    * @param: $contact
    * @return: business_code
    */
    public function domain_var_contact($contact){
        //中文正则表达式
        $regZW = "/^[\x{4e00}-\x{9fa5}]{1,20}$/u";
        // 数字正则表达式
        $regSZ = "/[0-9]+/";
        // 手机正则表达式
        $regDH = "/1[0-9]{10}/";
        // 通讯地址英文正则表达式
        $regDZ2 = "/[a-zA-Z0-9]{6,64}/";
        // 邮箱正则表达式
        $regYX = "/[A-Za-z0-9]+@([_A-Za-z0-9]+\\.)+[A-Za-z0-9]{2,3}/";
        // 单位名称只能为中文字符，且长度<2
        if (strlen ( $contact ["dwmc"] ) < 2 || ! preg_match ( $regZW, str_replace ( " ", "", $contact ["dwmc"] ) )) {
            return - 18;
        }
        // 英文单位名称只能为字母数字,且长度为3-200
        if (! preg_match ( "/[a-zA-Z0-9]{3,200}/", $contact ["dwmcYW"] )) {
            return - 19;
        }
        // 注册人姓只能为中文
        if (! preg_match ( $regZW, $contact ["zcrX"] )) {
            return - 20;
        }
        // 注册人名只能为中文
        if (! preg_match ( $regZW, $contact ["zcrM"] )) {
            return - 21;
        }
        // 注册人国家只能为中国
        if ($contact ["zcrGJ"] != "中国") {
            return - 22;
        }
        //证件号码为1-30位
        if(strlen($contact ["zjhm"])==0||strlen($contact ["zjhm"])>30){
            return -45;
        }
        // 注册人地址英文只能为数字字母，且长度6-64
        if (! preg_match ( $regDZ2, $contact ["zcrDZ2"] )) {
            return - 23;
        }
        // 注册人邮政编码只能为数字，长度为6
        if (! preg_match ( $regSZ, $contact ["zcrYZBM"] ) || strlen ( $contact ["zcrYZBM"] ) != 6) {
            return - 24;
        }
        // 注册人电话(手机号码)
        if (! preg_match ( $regDH, $contact ["zcrDH"] )) {
            return - 25;
        }
        // 注册人邮箱错误
        if (! preg_match ( $regYX, $contact ["zcrYX"] )) {
            return - 27;
        }
        return - 1;
    }
    /**
    * 域名续费
    * @date: 2016年12月3日 上午11:24:55
    * @author: Lyubo
    * @param: domain_id
    * @return: business_code
    */
    public function domain_renewals(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $domain = new \Frontend\Model\DomainModel();
        $product = new \Frontend\Model\ProductModel();
        $data =request();
        $domain_id = $data['domain_id'];
        $member_id = session("user_id");
        if(IS_POST){
            $renewals_time = $data['renewalstime'];
            /**
             * 域名续费
             */
            $code = $domain->doamin_renewals ( $domain_id, $member_id, $renewals_time );
            $message = business_code ( $code );
            if($code==-1){
                $this->success("域名续费成功，你可以在[会员中心]查看域名信息",U('frontend/domain/domainlist' ,false));
                //TODO 邮件发送
            }else{
                $this->error($message,U('frontend/domain/domainlist', '', false));
            }
        }else{
            if (empty ( $domain_id ) && ! is_numeric ( $domain_id )) {
                $msg = "编号不存在，或编号错误";
                $this->error($msg,U('frontend/domain/domainlist', '', false));
            }
            $domain_info = $domain->domain_renewals_info($domain_id , $member_id);
            $product_price_list = null;
            if(!empty($domain_info)){
                $condition['type'] = ["eq" , \Common\Data\StateData::STATE_RENEWALS];
                $condition['api_type'] = ["eq" , "5"];
                $product_price_list =$product->get_product_price_buy_time($domain_info ["product_id"],$condition);
                $domain_info['product_price_list'] = $product_price_list;
            }
            $this->assign($domain_info);
            $this->display();
        }
    }
    /**
    * 域名同步
    * @date: 2017年1月12日 下午5:02:48
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function memberSync(){
        $data =request();
        $yw_id = $data['business_id']+0;
        $ptype = $data['ptype'];
        $url = U('frontend/domain/domainlist','',false);
        $dm = new \Frontend\Model\DomainModel();
        $dmRespository = new \Common\Respository\DomainRespository($dm);
        $result = $dmRespository->DomainBusinessSynchronizing($yw_id);
        if($result){
            $this->success("同步业务信息成功",$url,true);
        }else{
            $this->error("同步业务信息失败-".$dmRespository->model()->getError(),$url,true);
        }
    }
}