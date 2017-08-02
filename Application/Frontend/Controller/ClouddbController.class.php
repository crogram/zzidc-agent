<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2017/1/10
 * Time: 10:08
 */

namespace Frontend\Controller;

use Common\Controller\BaseController;
use Common\Data\GiantAPIParamsData;

class ClouddbController extends BaseController
{

    /**
     * 快云数据库产品信息页面
     */
    public function ClouddbShow()
    {
        $article = new \Frontend\Model\HelpModel();
        $cpbz = $article->home_news("快云数据库");
        $czsc = $article->home_news("数据库");
        $bazn = $article->home_news("备案");
        $this->assign(['cpbz'=>$cpbz,'czsc'=>$czsc,'bazn'=>$bazn,]);
        $this->display("ClouddbShow");
    }
    /**
     * 快云数据库独享版购买页面
     */
    public function clouddbdx()
    {
        $this->display();
    }
    /**
     * 快云数据库分享版购买页面
     */
    public function clouddbgx()
    {
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
       $clouddb = new \Frontend\Model\ClouddbModel();
        $sum = $clouddb->cloudserver_stateCount(GiantAPIParamsData::PTYPE_CLOUD_DATABASE);
        $date["status"] = 'ok';
        $date["sum"] = $sum;
        return $this->ajaxReturn($date);
    }
    /**
     * 快云数据库价格
     * @data: 2016年11月25日 下午2:39:40
     * @author: Guopeng
     * @return price
     */
    public function buyPrice(){
        $data = request();
        $clouddb = new \Frontend\Model\ClouddbModel();
        $price = $clouddb->getPrice($data);
        if($price !== false){
            $date['price'] = $price;
            $date["flag"] = true;
        }else{
            $date['price'] = $clouddb->getError();
            $date["flag"] = false;
        }
        $this->ajaxReturn($date);
    }

    /**
     * 快云服务器购买
     * @author: Guopeng
     * @param: variable
     * @return json code
     */
    public function clouddb_buy(){
        if (! session('?user_id')) {
            if(IS_AJAX){
                $this->success("请先登录！",U('/Frontend/Login/login',[],false),true);
            }else{
                redirect(U('/Frontend/Login/login',[],false),0,'');
                die();
            }
        }
        $data = request();
        $clouddb = new \Frontend\Model\ClouddbModel();
        $result = $clouddb->clouddb_order_buy($data);
        if($result){
            $url = U('frontend/order/orderlist',['state' => '6'],false);
            $msg = '购买成功，您可以在[会员中心-我的订单]查看订单信息!';
            $this->success($msg,$url,3);
        }else{
            $msg = $clouddb->getError();
            $this->error($msg);
        }
    }

    /**
     * 快云服务器开通
     * @author: Guopeng
     * @param: $order_id
     */
    public function clouddb_open()
    {
        if(!session('?user_id'))
        {
            if(IS_AJAX){
                $this->success("请先登录！",U('/Frontend/Login/login', [], false),true);
            }else{
                redirect(U('/Frontend/Login/login', [], false), 0, '');
                die();
            }
        }
        $clouddb = new \Frontend\Model\ClouddbModel();
        $order = new \Frontend\Model\OrderModel();
        $data = request();
        $user_id = session("user_id");
        $order_id = $data['order_id']+0;
        if(IS_POST)
        {
            // 开通快云服务器
            $result = $clouddb->clouddb_open($order_id,$user_id,$data);
            if($result){
                $msg = "快云数据库业务正在队列开通,请到[会员中心]业务列表等待获取业务信息";
                $this->success($msg,U('frontend/clouddb/clouddblist','',false),true);
            }else{
                $message = $clouddb->getError();
                $this->error($message,U('frontend/order/orderlist',['state' => '6'],false),true);
            }
        }else{
            //显示快云数据库开通页面
            // 获取订单信息
            $order_info = $order->order_find($order_id);
            if($order_info['user_id'] != $user_id || $order_info['state'] != 6)
            {
                $this->error('订单不存在，或者订单错误',U('frontend/order/orderlist',['state' => '6'],false));
            }
            $this->assign('order_info',$order_info);
            $this->display();
        }
    }

    /**
     * 获取用户所有快云数据库业务
     * @author: Guopeng
     */
    public function clouddblist(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $clouddb = new \Frontend\Model\ClouddbModel();
        $where = $clouddb->conver_par();
        $order = I("get.order");
        $info = $clouddb->get_clouddb_business_list($where,10,$order,GiantAPIParamsData::PTYPE_CLOUD_DATABASE);
        $Not_open = $clouddb->get_not_open($info['list'],5);
        $Not_uplevel = $clouddb->get_not_open($info['list'],6);
        $this->assign([
                          "count"=>$info['count'],
                          "page"=>$info["page_show"],
                          "info" =>$info['list'],
                          "not_open"=>implode(",",$Not_open),
                          "not_uplevel"=>implode(",",$Not_uplevel)
                      ]);
        $this->display();
    }
    /**
     * 获取用户所有快云数据库业务
     * @author: Guopeng
     */
    public function clouddbrenew(){
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        $clouddb = new \Frontend\Model\ClouddbModel();
        $where = $clouddb->conver_par();
        $order = I("get.order");
        $info = $clouddb->get_clouddb_renew_list($where,10,$order,GiantAPIParamsData::PTYPE_CLOUD_DATABASE);
        $Not_open = $clouddb->get_not_open($info['list'],5);
        $Not_uplevel = $clouddb->get_not_open($info['list'],6);
        $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list'],
        ]);
        $this->display();
    }

    /**
     * 修改备注
     * @author: Guopeng
     * @param: remark，business_id
     * @return boolean
     */
    public function remark(){
        $cloudserver = new \Frontend\Model\ClouddbModel();
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
     * 获取快云数据库开通进度
     * @author : Guopeng
     * @param : $clouddb_id,保存在主站的订单编号
     * @return  boolean
     */
    public function get_open_progress()
    {
        $clouddb = new \Frontend\Model\ClouddbModel();
        $data = request();
        $clouddb_id = $data['clouddb_id'];
        if(is_null($clouddb_id) || !is_numeric($clouddb_id) || strpos($clouddb_id,".") != false){
            $msg = "业务编号为空或错误";
            $this->error($msg,"",true);
        }
        $result = $clouddb->get_business_info($clouddb_id);
        if($result){
            $msg = "业务开通成功";
            $this->success($msg,"",true);
        }elseif($result === 0){
            $this->error("",1,true);
        }else{
            $msg = $clouddb->getError();
            $this->error($msg,0,true);
        }
    }

    /**
     * 快云数据库续费业务
     * @author : Guopeng
     */
    public function clouddb_renewals()
    {
        $data = request();
        $user_id = session("user_id");
        $yw_id = $data["clouddb_id"]+0;
        $clouddb = new \Frontend\Model\ClouddbModel();
        $url = U('frontend/clouddb/clouddblist','',false);
        if(IS_POST)
        {
            if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
            {
                $message = "业务编号为空或错误";
                $this->error($message,$url,true);
            }
            /*快云数据库续费*/
            $result = $clouddb->clouddb_renewals($user_id,$yw_id,$data);
            if($result == -1)
            {
                $message = "快云数据库续费成功，您可以在[会员中心]快云数据库查看信息";
                $this->success($message,$url,true);
            }
            else
            {
                $message = business_code($result);
                $this->error($message,$url,true);
            }
        }
        else
        {
            if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
            {
                $message = "业务编号为空或错误";
                $this->error($message,$url);
            }
            /*快云数据库续费业务信息显示*/
            $result = $clouddb->clouddb_renewals($user_id,$yw_id,'','get');
            if($result <= 0)
            {
                $message = business_code($result);
                $this->error($message,$url);
            }
            $this->assign($result);
            $this->display();
        }
    }

    /**
     * 快云数据库升级业务
     * @author : Guopeng
     */
    public function clouddb_uplevel()
    {
        $data = request();
        $user_id = session("user_id");
        $yw_id = $data["clouddb_id"]+0;
        $clouddb = new \Frontend\Model\ClouddbModel();
        $url = U('frontend/clouddb/clouddblist','',false);
        if(IS_POST)
        {
            if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
            {
                $message = "业务编号为空或错误";
                $this->error($message,$url);
            }
            /*快云数据库续费*/
            $result = $clouddb->clouddb_uplevel($user_id,$yw_id,$data);
            if($result == -1)
            {
                $message = "快云数据库业务正在队列升级,请到[会员中心]业务列表等待获取业务信息";
                $this->success($message,$url);
            }
            else
            {
                $message = business_code($result);
                $this->error($message,$url);
            }
        }
        else
        {
            if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
            {
                $message = "业务编号为空或错误";
                $this->error($message,$url);
            }
            /*快云数据库续费业务信息显示*/
            $result = $clouddb->clouddb_uplevel($user_id,$yw_id,'','get');
            if($result <= 0)
            {
                $message = business_code($result);
                $this->error($message,$url);
            }
            $this->assign($result);
            $this->display();
        }
    }

    /**
     * 获取快云数据库升级状态
     * @author : Guopeng
     * @param : $clouddb_id,保存在主站的订单编号
     * @return  boolean
     */
    public function get_uplevel_progress()
    {
        $clouddb = new \Frontend\Model\ClouddbModel();
        $data = request();
        $user_id = session("user_id");
        $clouddb_id = $data['clouddb_id'];
        if(is_null($clouddb_id) || !is_numeric($clouddb_id) || strpos($clouddb_id,".") != false){
            $msg = "业务编号为空或错误";
            $this->error($msg,"",true);
        }
        $result = $clouddb->get_uplevel_progress($user_id,$clouddb_id);
        if($result){
            $msg = "业务升级成功";
            $this->success($msg,"",true);
        }elseif($result === 0){
            $this->error("",1,true);
        }else{
            $msg = $clouddb->getError();
            $this->error($msg,0,true);
        }
    }

    /**
     * 同步业务信息
     * @author: Guopeng
     * @param: $yw_id,$ptype
     * @return boolean
     */
    public function memberSync(){
        $data =request();
        $yw_id = $data['business_id']+0;
        $url = U('frontend/clouddb/clouddblist','',false);
        $clouddb = new \Frontend\Model\ClouddbModel();
        $clouddbRespository = new \Common\Respository\ClouddbBusinessRespository($clouddb);
        $result = $clouddbRespository->ClouddbBusinessSynchronizing($yw_id);
        if($result){
            $this->success("同步业务信息成功",$url,true);
        }else{
            $this->error("同步业务信息失败-".$clouddbRespository->model()->getError(),$url,true);
        }
    }
}