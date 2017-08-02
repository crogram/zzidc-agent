<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;

class CloudspaceController extends FrontendController{
    public function _initialize()
    {
        parent::_initialize();
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
    }
    /**
     * 获取用户所有cloudspace业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     */
    public function cloudspacelist(){
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $where = $cloudspace->conver_par();
        $order = I("get.order");
        $info = $cloudspace->get_cloudspace_business_list($where,10,$order);
        $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list']
        ]);
        $this->display();
    }
    /**
     * 获取用户所有cloudspace待续费业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     */
    public function cloudspacerenew(){
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $where = $cloudspace->conver_par();
        $order = I("get.order");
        $info = $cloudspace->get_cloudspace_renew_list($where,10,$order);
        $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list']
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
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $sum = $cloudspace->stateCount();
        $date["status"] = 'ok';
        $date["sum"] = $sum;
        return $this->ajaxReturn($date);
    }
    /**
    * 查看该业务的子站点信息
    * @date: 2017年1月17日 下午2:34:30
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function cloudspace_site_info(){
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $info = $cloudspace->get_cloudpace_site_info();
        $this->assign([
            "info" =>$info
        ]);
        $this->display();
    }
    /**
     * 修改备注
     * @date: 2016年10月27日 上午11:23:22
     * @author: Lyubo
     * @param: remark，business_id
     * @return boolean
     */
    public function remark(){
        $cs = new \Frontend\Model\CloudspaceModel();
        $result = $cs->remark_edit();
        if($result){
            $data = "ok";
            $this->ajaxReturn($data,'json');
        }else{
            $data = "fail";
            $this->ajaxReturn($data,'json');
        }
    }
/******************************业务操作**************************************/
    /**
    * 云空间开通
    * @date: 2016年11月29日 下午5:18:18
    * @author: Lyubo
    * @param: $order_id
    */
    public function cloudspace_open(){
        $data =request();
        $member_id = session("user_id");
        $order_number = $data['order_id'];
        $order =  new \Frontend\Model\OrderModel();
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        if(IS_POST){
            $result = $cloudspace->cloudspace_open ( $order_number, $member_id );
            $message = business_code ( $result );
            if ($result == - 1) {
                $message = "云空间开通成功！您可以在[会员中心-云空间列表]查看虚拟主机信息";
                $url = U('frontend/cloudspace/cloudspacelist','',false);
                $this->success($message,$url);
            } else {
                $url = U('frontend/order/orderlist',['state'=>'6'],false);
                $this->error($message,$url);
            }
        }else{
            $order_info = $order->order_find ( $order_number );
            if ($order_info ['user_id'] != $member_id || $order_info ['state'] != 6) {
                $message = '订单不存在，或者订单错误';
                $url = U('frontend/order/orderlist',['state'=>'6'],false);
                 $this->error($message,$url);
            }
            $this->assign('order_info',$order_info);
            $this->display();
        }
    }
    /**
    * 云空间转正
    * @date: 2016年11月30日 下午3:31:36
    * @author: Lyubo
    * @param: $cloudspace_id,$service_time
    * @return business_code
    */
    public function cloudspace_onformal(){
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $data =request();
        $cloudspace_id = $data['cloudspace_id']+0;
        $member_id = session("user_id");
        $url = U('frontend/cloudspace/cloudspacelist' ,'',false);
        if(IS_POST){
           $service_time = $data['service_time']+0; // 转正时限
           $result = $cloudspace->cloudspace_onformal($cloudspace_id , $member_id , $service_time);
           $message = business_code ( $result );
           if($result == -1){
               $message = "云空间转正成功！您可以在[会员中心-云空间]查看云空间信息";
               $this->success($message,$url);
           }else{
               $this->error($message,$url);
           }
        }else{
            $cloudspace_info = $cloudspace->show_cloudspace_onformal($cloudspace_id , $member_id);
            if ($cloudspace_info < 0) {
                $message = business_code ( $cloudspace_info );
                $this->error($message,$url);
            }else{
                $this->assign("cloudspace_info",$cloudspace_info);
                $this->display();
            }
        }
    }
    /**
    * 云空间续费
    * @date: 2016年11月30日 下午4:18:24
    * @author: Lyubo
    * @param: $cloudspace_id,$member_id,$renewalstime
    */
    public function cloudspace_renewals(){
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $data =request();
        $url = U('frontend/cloudspace/cloudspacelist','',false);
        $cloudspace_id = $data['cloudspace_id'];
        $member_id = session("user_id");
        if(IS_POST){
            $renewalstime = $data ['renewalstime'];
            $result = $cloudspace->cloudspacd_renewals($cloudspace_id,$member_id,$renewalstime);
            $message = business_code ( $result );
            if($result == -1){
                $message = "云空间续费成功！您可以在[会员中心-云空间]查看云空间信息";
                $this->success($message,$url);
            }else{
                $this->error($message,$url);
            }
        }else{
            $cloudspace_info = $cloudspace->show_cloudspacd_renewals($cloudspace_id,$member_id);
            if ($cloudspace_info < 0) {
                $message = business_code ($cloudspace_info);
                $this->error($message,$url);
            }else{
                $this->assign($cloudspace_info);
                $this->display();
            }
        }
    }
    /**
    * 云空间增值
    * @date: 2016年11月30日 下午5:03:55
    * @author: Lyubo
    * @param: $cloudspace_id,$app_name.$app_size
    */
    public function cloudspace_appreciation(){
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $data =request();
        $url = U('frontend/cloudspace/cloudspacelist','',false);
        $member_id = session("user_id");
        if(IS_POST){
            $cloudspace_id = I('post.cloudspace_id');
            $app_name = $data['app_name'];
            $app_size = $data['app_size'];
            if (is_null ( $app_name )) {
                $message = "增值类型错误！";
                $this->error($message,$url);
            }
            $result = $cloudspace->cloudspace_apperciation($cloudspace_id,$member_id,$app_name,$app_size);
            $message = business_code($result);
            if ($result == - 1) {
                $message = "增值成功！您可以在[会员中心-云空间]查看云空间信息";
                $this->success($message,$url);
            } else {
                $this->error($message, $url);
            }
        }else{
            $cloudspace_id = I('get.cloudspace_id');
            $cloudspace_info = $cloudspace->show_cloudspace_apperciation ( $cloudspace_id, $member_id );
            if ($cloudspace_info < 0) {
                $message = business_code($cloudspace_info);
                $this->error($message,$url);
            }else{
                $this->assign($cloudspace_info);
                $this->display();
            }
        }
    }
    /**
    * 云空间升级
    * @date: 2016年12月1日 下午5:09:09
    * @author: Lyubo
    * @param: $cloudspace_id,$up_product_id
    */
    public function cloudspace_uplevel(){
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $data = request();
        $url = U('frontend/cloudspace/cloudspacelist','',false);
        $cloudspace_id = $data['cloudspace_id'];
        $member_id = session("user_id");
        if(IS_POST){
            $up_product_id = $data['up_product_id'];
            $result = $cloudspace->cloudspace_upgrade($cloudspace_id,$member_id,$up_product_id);
            $message = business_code($result);
            if($result == -1){
                $message = "升级成功！您可以在[会员中心-云空间]查看云空间信息";
                $this->success($message,$url);
            }else{
                $this->error($message,$url);
            }
        }
        else{
            $cloudspace_upgrage_info = $cloudspace->show_cloudspace_upgrage_info($cloudspace_id,$member_id);
            if($cloudspace_upgrage_info < 0){
                $message = business_code($cloudspace_upgrage_info);
                $this->error($message,$url);
            }else{
                $this->assign($cloudspace_upgrage_info);
                $this->display();
            }
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
        $ptype = $data['ptype'];
        $url = U('frontend/cloudspace/cloudspacelist','',false);
        $cloudspace = new \Frontend\Model\CloudspaceModel();
        $cloudspaceRespository = new \Common\Respository\CloudspaceBusinessRespository($cloudspace);
        $result = $cloudspaceRespository->CloudspaceBusinessSynchronizing($yw_id,$ptype);
        if($result){
            $this->success("同步业务信息成功",$url,true);
        }else{
            $this->error("同步业务信息失败-".$cloudspaceRespository->model()->getError(),$url,true);
        }
    }
/******************************业务操作**************************************/
}