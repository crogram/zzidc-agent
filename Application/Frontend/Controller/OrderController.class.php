<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;


class OrderController extends FrontendController{
public function _initialize()
    {
        parent::_initialize();
        if (! session('?user_id')) {
            if(IS_AJAX){
                $this->success("请先登录！",U('/Frontend/Login/login', [], false),true);
            }else{
                redirect(U('/Frontend/Login/login', [], false), 0, '');
                die();
            }
        }
    }
    /**
    * 获取订单列表
    * @date: 2016年10月29日 下午4:40:47
    * @author: Lyubo
    * @return:$list,$page
    */
    public function orderlist(){
        $page = request()["page"] + 0;
        $order = new \Frontend\Model\OrderModel();
        $where = $order->conver_par();
        $order_by = I("get.order");
        $info = $order->get_order_list($where,10,"*",$order_by);
        if($page > 0 && isMobile() == 'phone'){
            if(empty($info['list'])){
                echo json_encode("last");die();
            } else {
                echo json_encode($info['list']);die();
            }
        }
         $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list']
        ]);     
        $this->display();
    }
    /**
    * 订单详情
    * @date: 2016年11月19日 下午2:41:41
    * @author: Lyubo
    * @return:
    */
    public function orderinfo(){
        $order = new \Frontend\Model\OrderModel();
        $where = $order->conver_par();
        $info = $order->get_order_info($where);
        $this->assign([
            "order_info" =>$info
        ]);
        $this->display();
    }
    /**
    * 订单顶部条件总数获取
    * @date: 2016年11月1日 上午10:03:49
    * @author: Lyubo
    * @param: $count
    * @return:
    */
    public function state_count(){
        $order = new \Frontend\Model\OrderModel();
        $sum = $order->order_stateCount();
        $date["status"] = 'ok';
        $date["sum"] = $sum;
        return $this->ajaxReturn($date);
    }
    /**
    * 会员购买确认商品页面
    * @date: 2016年11月9日 下午2:24:55
    * @author: Lyubo
    * @param: product_id，price_id , system_type
    * @return:
    */
    public function order_buy(){
        $date =request();
        $order = new \Frontend\Model\OrderModel();
        $result = $order->order_buy();
        if($result === -1){
            if($date["free_trial"] == 0){
                $this->success('购买成功，您可以在[会员中心-我的订单]查看订单信息!',U('frontend/order/orderlist',array("state"=>6),false),true);
            }else{
                $this->success('试用成功，请等待审核，您可以在[会员中心-我的订单]查看订单信息!',U('frontend/order/orderlist',array("state"=>6),false),true);
            }
        }else{
            if($result == '-5'){
                $this->error('账户余额不足，即将跳转充值页面',U('frontend/financial/member_recharge','',false),true);
            }else{
                $this->error(business_code($result),'',true);
            }
        }
    }
    /**
    * 快云服务器购买
    * @date: 2016年11月26日 下午3:47:26
    * @author: Lyubo
    * @param: variable
    * @return: $business_code
    */
    public function cloud_order_buy(){
        $data =request();
        $order = new \Frontend\Model\OrderModel();
        if(empty($data['price']) || $data['price'] == '0'){
            $msg = "价格异常，即将刷新页面！";
            $this->error($msg);
        }elseif (($data['os'] +0) == 0){
            $msg = "操作系统参数异常,即将刷新页面！";
            $this->error($msg);
        }
        $result = $order->cloud_order_buy($data);
        if ($result === - 1) {
            if ($data['type'] == 'mobile') {
                $msg = "购买成功，您可以在[会员中心-我的订单]查看订单信息!";
                $this->success($msg, U('frontend/order/orderlist', [], false), true);
            } else {
                $this->success('购买成功，您可以在[会员中心-我的订单]查看订单信息!', 'orderlist/state/6', 3);
            }
        } else {
            $this->error(business_code($result));
        }
    }
    /**
    * 开通异常业务获取
    * @date: 2016年11月19日 下午2:56:38
    * @author: Lyubo
    * @param: $order_id
    * @return: business_info
    */
    public function order_getinfo(){
        $data = request();
        $order = new \Frontend\Model\OrderModel();
        $vhostRespository = new \Common\Respository\VirtualhostBusinessRespository($order);
        $result = $vhostRespository->VirtualhostOrderSynchronizing($data['order_id']);
        if($result  === false){
            if($vhostRespository->model()->getError() == '业务已存在'){
                //处理业务已经存在时候
                $order_where['id'] = ['eq',$data['order_id']];
                $ordet_data['state'] = \Common\Data\StateData::SUCCESS_ORDER;
                $order->order_edit($data['order_id'],$ordet_data);
                $this->error($vhostRespository->model()->getError(),'',true);
            }else{
                //处理异常错误
                $this->error($vhostRespository->model()->getError(),'',true);
            }
        }else{
            //业务获取成功
            $order_where['id'] = ['eq',$data['order_id']];
            $ordet_data['state'] = \Common\Data\StateData::SUCCESS_ORDER;
            $order->order_edit($data['order_id'],$ordet_data);
            $this->success("业务信息获取成功！请到业务列表查看",U("frontend/member/index",'',false),true);
        }
    }
    /**
    * 订单开通业务
    * @date: 2016年11月17日 下午3:42:06
    * @author: Lyubo
    * @return:
    */
    public function order_open(){
        $date = request();
        $order = new \Frontend\Model\OrderModel();
        $result = $order->order_open($date);
        if($result < 0){
            $this->error(business_code($result));
        }else{
            $order_id = $result['order_id'];
            redirect(U('/frontend/'.$result['c'].'/'.$result['a'],['order_id'=>"$order_id"],false));
        }
    }
}