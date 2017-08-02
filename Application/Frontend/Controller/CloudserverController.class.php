<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;
use Common\Data\GiantAPIParamsData;
class CloudserverController extends FrontendController{
    public function _initialize()
    {
        parent::_initialize();
    }
    /**
    * 快云服务器首页
    * @date: 2016年11月24日 下午2:48:39
    * @author: Lyubo
    */
    public function cloudserverShow(){
        $article = new \Frontend\Model\HelpModel();
        $cpbz = $article->home_news("快云服务器");
        $czsc = $article->home_news("服务器");
        $bazn = $article->home_news("备案");
        $this->assign(['cpbz'=>$cpbz,'czsc'=>$czsc,'bazn'=>$bazn,]);
        $this->display("Cloudserver/cloudserverShow");
    }
    /**
    * 快云服务器购买页面
    * @date: 2016年11月24日 下午4:50:55
    * @author: Lyubo
    */
    public function cloudserverBuy(){
        $this->display("Cloudserver/cloudserverBuy");
    }
    /**
     * 获取用户所有cloudserver业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     */
    public function cloudserverlist(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $where = $cloudserver->conver_par();
        $order = I("get.order");
        $info = $cloudserver->get_cloudserver_business_list($where,10,$order,GiantAPIParamsData::PTYPE_CLOUD_SERVER);
        $Not_open = $cloudserver->get_not_open($info['list']);
        $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list'],
            "business_info"=>implode(",", $Not_open)
        ]);
        $this->display();
    }
    /**
     * 获取用户所有cloudserver业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     */
    public function cloudserverrenew(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $where = $cloudserver->conver_par();
        $order = I("get.order");
        $info = $cloudserver->get_cloudserver_renew_list($where,10,$order,GiantAPIParamsData::PTYPE_CLOUD_SERVER);
        $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list'],
        ]);
        $this->display();
    }
    /**
     * 获取列表数量和待续费业务数量
     * @date: 2017年4月11日 下午4:47:38
     * @author: Lyubo
     * @param: variable
     * @return:
     */
    public function state_count(){
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $sum = $cloudserver->cloudserver_stateCount(GiantAPIParamsData::PTYPE_CLOUD_SERVER);
        $date["status"] = 'ok';
        $date["sum"] = $sum;
        return $this->ajaxReturn($date);
    }
    /**
     * 修改备注
     * @author: Lyubo
     * @param: remark，business_id
     * @return boolean
     */
    public function remark(){
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $result = $cloudserver->remark_edit();
        if($result){
            $data = "ok";
            $this->ajaxReturn($data,'json');
        }else{
            $data = "fail";
            $this->ajaxReturn($data,'json');
        }
    }
    /**
    * 快云服务器价格
    * @data: 2016年11月25日 下午2:39:40
    * @author: Lyubo
    * @param: disk:数据盘
    * @param: dqbh:地区编号
    * @param: gmqx:购买期限
    * @param: disktype:数据盘类型
    * @param: serverbh:服务器编号
    * @return price
    */
    public function buyPrice(){
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $data =request();
        $price = $cloudserver->CalculatedPrice($data);
        $date['price'] = $price;
        $this->ajaxReturn($date);
    }
    /**
    * 快云服务器操作系统
    * @date: 2016年11月25日 下午2:48:10
    * @author: Lyubo
    * @return json $os_type
    */
    public function OsType(){
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $os_type = $cloudserver->OsTypeInfo();
        if ($os_type) {
                echo json_encode($os_type);
                exit();
            } else {
                echo json_encode($os_type);
                exit();
            }
    }
    /**
    * 快云服务器开通
    * @date: 2016年12月1日 上午9:33:23
    * @author: Lyubo
    * @param: $order_id
    * @return business_code
    */
    public function cloudserver_open(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $order = new \Frontend\Model\OrderModel();
        $data =request();
        $member_id = session("user_id");
        $order_id = $data['order_id'];
        if(IS_POST){
            $password = $data['password'];
            if(! preg_match("/^(?=^.{9,20}$)(?=(?:.*?\\d){3})(?=(?:.*?[a-z]){3})(?=(?:.*?[A-Z]){3})[0-9a-zA-Z].*$/",$password)){
                $message = "密码格式不正确";
                $this->error($message,U('frontend/order/orderlist',['state'=>'6'],false));
            }
            if (is_null ( $order_id ) || ! is_numeric ( $order_id ) || strpos ( $order_id, "." ) != false || $order_id <= 0) {
                $message = "订单编号错误";
                $this->error($message,U('frontend/order/orderlist',['state'=>'6'],false));
            } elseif (is_null ( $password ) || ! reg_user_pass ( $password ) || strpos ( $password, '@' ) != false || strpos ( $password, '#' ) != false || strpos ( $password, '%' ) != false) {
                $message = '密码必须包含字母，数字，和除#%&以外的特殊字符，长度最小8位';
                $this->error($message,U('frontend/order/orderlist',['state'=>'6'],false));
            }else {
                // 获取订单信息
                $order_info = $order->order_find($order_id);
                // 开通快云服务器
                $result = $cloudserver->cloudserver_open ( $order_id, $member_id, $password );
                $message = business_code (  $result['code'] );
                if ($result['code'] == -1) {
                    $this->success("快云服务器开通成功，您可以在[会员中心]查看订单信息和快云服务器信息"."服务器密码为:".$password,U('frontend/cloudserver/cloudserverlist' ,'',false));
                } else{
                    $this->error($message,U('frontend/order/orderlist',['state'=>'6'],false));
                }
            }
        }else{
            //显示快云服务器开通页面
            $order_info = $order->order_find ( $order_id );
             if($order_info ['user_id'] != $member_id || $order_info ['state'] != 6)
            {
                if($order_info['state'] == '1'){
                    $message = '订单状态失败，请联系客服';
                }elseif($order_info['state'] == '1'){
                    $message = '订单已开通,请到业务列表查看业务信息';
                }else{
                    $message = '订单不存在，或者订单错误';
                }
                $url = U('frontend/order/orderlist',['state' => '6'],false);
                $this->error($message,$url);
            }
            $this->assign('order_info',$order_info);
            $this->display();
        }
    }
    /**
    * 快云服务器续费
    * @date: 2016年12月4日 下午1:53:14
    * @author: Lyubo
    * @param: $cloudserver_id
    */
    public function cloudserver_renewals(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $data = request();
        $cloudserver_id = $data['cloudserver_id'];
        $member_id  = session("user_id");
        if(IS_POST){
            $renewalstime = $data['renewalstime'];
            if (is_null ( $cloudserver_id ) || ! is_numeric ( $cloudserver_id ) || strpos ( $cloudserver_id, "." ) != false) {
                 $this->error("业务编号为空或错误",U('frontend/cloudserver/cloudserverlist' ,'',false));
            } else {
                $result = $cloudserver->cloudserver_renewals ( $cloudserver_id, $member_id ,$renewalstime);
                $message = business_code ( $result );
                if($result==-1){
                    $this->success("快云服务器续费成功",U('frontend/cloudserver/cloudserverlist' ,'',false));
                }else{
                    $this->error($message,U('frontend/cloudserver/cloudserverlist' ,'',false));
                }
            }
        }else{
            //显示续费信息
            if (is_null ( $cloudserver_id ) || ! is_numeric ( $cloudserver_id ) || strpos ( $cloudserver_id, "." ) != false || $cloudserver_id <= 0) {
                $this->error("业务编号为空或错误",U('frontend/cloudserver/cloudserverlist' ,'',false));
            } else {
                // 业务信息
                $cloudserver_info = $cloudserver->show_cloudserver_renewals ( $member_id, $cloudserver_id);
                if ($cloudserver_info <= 0) {
                    $msg = business_code($cloudserver_info);
                    $this->error($msg,U('frontend/cloudserver/cloudserverlist' ,'',false));
                }else{
                    $this->assign($cloudserver_info);
                    $this->display();
                }
            }
        }
    }
    /**
     * **************************服务器操作*******************************
     */
    /**
     * 获取快云服务器开通进度
     * @date: 2016年12月2日 上午11:34:20
     * @author : Lyubo
     * @param : $cloudserver_id,保存在主站的订单编号            
     * @return  boolean
     */
    public function get_progress()
    {
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $data = request();
        $cloudserver_id = $data['cloudserver_id'];
        if (is_null($cloudserver_id) || ! is_numeric($cloudserver_id) || strpos($cloudserver_id, ".") != false) {
            $message = "业务编号为空或错误";
            $ajaxdata['status'] = 'no';
            $ajaxdata['message'] = $message;
            $this->ajaxReturn($ajaxdata);
        } else {
            $result = $cloudserver->get_business_info($cloudserver_id);
            $message = business_code($result);
            if ($result == -1) {
                $ajaxdata['status'] = 'y';
                $ajaxdata['message'] = "业务开通成功";
                $this->ajaxReturn($ajaxdata);
            } else {
                $ajaxdata['status'] = 'n';
                $ajaxdata['message'] = $message;
                $this->ajaxReturn($ajaxdata);
            }
        }
    }
    /**
    * 获取IP绑定进度
    * @date: 2016年12月4日 上午11:53:41
    * @author: Lyubo
    * @param: $ip_api_bid
    * @return boolean
    */
    public function IpProgress(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $data = request();
        $result = $cloudserver->ip_progress($data['cloudserver_id'],$data['ip']);
        $message = business_code($result);
        if ($result == -1) {
            $ajaxdata['status'] = 'y';
            $ajaxdata['message'] = "IP绑定成功";
            $this->ajaxReturn($ajaxdata);
        } elseif($result == '5033') {
            $ajaxdata['status'] = 'n';
            $ajaxdata['message'] = $message;
            $this->ajaxReturn($ajaxdata);
        }else{
            $ajaxdata['status'] = 'no';
            $ajaxdata['message'] = $message;
            $this->ajaxReturn($ajaxdata);
        }
    }
    /**
    * 快云服务器解绑IP
    * @date: 2016年12月3日 下午5:45:53
    * @author: Lyubo
    * @param: $cloudserver_id,$ip_id,$ip_adderss,$cloudserver_api_bid,$cloudserver_ip_bid
    * @return boolean
    */
    public function cloudserver_relieve(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $data = request();
        $cloudserver_id = $data['cloudserver_id'];
        $cloudserver = new \Frontend\Model\CloudserverModel();
        if(IS_POST){
            $ip_address = $data["ipdz"];
            $ip_bid = $data["cloudserver_ip_bid"];
            $api_bid = $data["cloudserver_api_bid"];
            $cloudserver_ip_id = $data['cloudserver_ip_id'];
            $result = $cloudserver->cloudserver_relieve($cloudserver_id,$cloudserver_ip_id,$ip_address,$api_bid,$ip_bid);
            $msg = business_code($result);
            if($result == -1){
                $this->success("解绑IP：".$ip_address."成功",U('frontend/cloudserver/cloudserverlist' ,'',false));
            }else{
                $this->error($msg,U('frontend/cloudserver/cloudserverlist' ,'',false));
            }
        }else{
            $cloudserver_info = $cloudserver->show_cloudserver_info($cloudserver_id);
            $this->assign($cloudserver_info);
            $this->display();
        }
    }
    /**
    * 快云服务器绑定IP
    * @date: 2016年12月3日 下午7:52:49
    * @author: Lyubo
    * @param: $cloudserver_id
     */
    public function cloudserver_bound(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $data = request();
        $cloudserver_id = $data['cloudserver_id'];
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $m_ip = new \Frontend\Model\IpModel();
        if(IS_POST){
            if (!filter_var($data['ipdz'], FILTER_VALIDATE_IP)){
                $this->error("IP地址格式错误！");
            }
            $cloudserverip_id  = $data['cloudserver_ip_id'];
            $cloudserver_api_bid = $data["cloudserver_api_bid"];
            $ip_address  = $data['ipdz'];
            $result = $cloudserver->cloudserver_bound($cloudserver_id,$cloudserver_api_bid,$ip_address);
            $message = business_code($result);
            if($result== -1){
                $this->success("IP：".$ip_address."正在绑定，请稍后查看业务信息请在当页面耐心等候1--2分钟",U('frontend/cloudserver/cloudserverlist' ,'',false));
            }else{
                $this->error($message,U('frontend/cloudserver/cloudserverlist' ,'',false));
            }
        }else{
            $where["id"] = ["eq",$cloudserver_id];
            if($data['type'] == 'get'){
                $cloudserver_info = $cloudserver->where($where)->find();
            }else{
                $cloudserver_info = $cloudserver->where($where)->find();
            }
            //获取所有的ip处于未绑定状态的ip地址的信息
           	$ip_info = $m_ip->get_user_ips([
           			'user_id' => [ 'eq', session('user_id') ],
           			'belong_server' => 0,
           	]);
//             dump($ip_info);die;
            $this->assign($cloudserver_info);
            $this->assign(['ip_info' => $ip_info]);
            $this->display();
        }
    }
    /**
     * 同步业务信息
     * @date: 2016年11月29日 下午2:53:15
     * @author: Lyubo
     * @param: $business_id,$ptype
     * @return boolean
     */
    public function memberSync(){
        $data =request();
        $yw_id = $data['business_id']+0;
        $cloudserver = new \Frontend\Model\CloudserverModel();
        $url = U('frontend/cloudserver/cloudserverlist','',false);
        $cloudserverRespository = new \Common\Respository\CloudserverBusinessRespository($cloudserver);
        $result = $cloudserverRespository->CloudserverBusinessSynchronizing($yw_id);
        if($result){
            $this->success("同步业务信息成功",$url,true);
        }else{
            $this->error("同步业务信息失败-".$cloudserverRespository->model()->getError(),$url,true);
        }
    }
/****************************服务器操作********************************/
}