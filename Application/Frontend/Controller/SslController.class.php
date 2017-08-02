<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/12/6
 * Time: 9:46
 */

namespace Frontend\Controller;

use Common\Data\GiantAPIParamsData;
use Frontend\Controller\FrontendController;
use Frontend\Model\SslModel;

class SslController extends FrontendController
{
    /**
     * 获取用户所有ssl证书
     * @author: Guopeng
     */
    public function ssllist()
    {
        $ssl = new \Frontend\Model\SslModel();
        $where = $ssl->conver_par();
        $order = I("get.order");
        $info = $ssl->get_ssl_business_list($where,10,$order,GiantAPIParamsData::PTYPE_SSL);
        $Not_open = $ssl->get_not_open($info['list'],6);
        $this->assign([
              "count" => $info['count'],
              "page" => $info["page_show"],
              "info" => $info['list'],
              "not_open" => implode(",",$Not_open)
          ]);
        $this->display();
    }
    /**
     * 获取用户所有ssl证书
     * @author: Guopeng
     */
    public function sslrenew()
    {
        $ssl = new \Frontend\Model\SslModel();
        $where = $ssl->conver_par();
        $order = I("get.order");
        $info = $ssl->get_ssl_business_renew($where,10,$order,GiantAPIParamsData::PTYPE_SSL);
        $Not_open = $ssl->get_not_open($info['list'],6);
        $this->assign([
            "count" => $info['count'],
            "page" => $info["page_show"],
            "info" => $info['list'],
            "not_open" => implode(",",$Not_open)
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
        $ssl = new \Frontend\Model\SslModel();
        $sum = $ssl->cloudserver_stateCount(GiantAPIParamsData::PTYPE_SSL);
        $date["status"] = 'ok';
        $date["sum"] = $sum;
        return $this->ajaxReturn($date);
    }
    /**
     * 修改备注
     * @author: Guopeng
     */
    public function remark()
    {
        $ssl = new \Frontend\Model\SslModel();
        $result = $ssl->remark_edit($state = true);
        if($result)
        {
            $data = "ok";
            $this->ajaxReturn($data,'json');
        }
        else
        {
            $data = "fail";
            $this->ajaxReturn($data,'json');
        }
    }

    /**
     * SSL证书开通
     * @author: Guopeng
     */
    function ssl_open()
    {
        $user_id = session('user_id');
        $open_info = request();
        $order_id = $open_info['order_id'];
        $os = new \Frontend\Model\OrderModel();
        $ssl = new \Frontend\Model\SslModel();
        $order_info = $os->order_find($order_id);
        if($order_info ['user_id'] != $user_id || ($order_info['state'] != 6 && $order_info['state'] != 2)){
            $message = '订单不存在，或者订单错误';
            $url = U('frontend/order/orderlist',['state' => '6'],false);
            $this->error($message,$url);
        }
        $where["bs.business_id"] = $order_info["business_id"];
        $where["bs.user_id"] = $user_id;
        $where["bs.product_id"] = $order_info["product_id"];
        $ssl_binfo = $ssl->get_ssl_state($where);
        //通过business_id查询业务是否正在开通中
        if($ssl_binfo){
            $os->order_edit($order_id,["state"=>2]);
            $data["yw_id"] = $ssl_binfo["id"];
            $data["order_id"] = $order_id;
            $this->redirect(U('frontend/ssl/ssl_business_open',$data,false));
        }
        $result = $ssl->ssl_open($order_id,$user_id);
        if($result <= 0){
            $message = business_code($result);
            $url = U('frontend/order/orderlist',['state' => '6'],false);
            $this->error($message,$url);
        }else{
            $data["yw_id"] = $result;
            $data["order_id"] = $order_id;
            $this->redirect(U('frontend/ssl/ssl_business_open',$data,false));
        }
    }

    /**
     * SSL证书分步开通
     * @author: Guopeng
     */
    function ssl_business_open(){
        $user_id = session('user_id');
        $open_info = request();
        $order_id = $open_info['order_id']+0;
        $os = new \Frontend\Model\OrderModel();
        $ssl = new \Frontend\Model\SslModel();
        $order_info = $os->order_find($order_id);
        if($order_info ['user_id'] != $user_id || $order_info['state'] != 2)
        {
            $message = '订单不存在，或者订单错误';
            $url = U('frontend/order/orderlist',[],false);
            $this->error($message,$url);
        }
        $where["bs.business_id"] = $order_info['business_id'];
        $where["bs.user_id"] = $user_id;
        $where["bs.product_id"] = $order_info["product_id"];
        $ssl_binfo = $ssl->get_ssl_state($where);
        //通过business_id查询业务是否正在开通中
        if(!$ssl_binfo){
            $message = '业务不存在';
            $url = U('frontend/order/orderlist',[],false);
            $this->error($message,$url);
        }
        if(!is_null($ssl_binfo["domain_name"]) && !empty($ssl_binfo["domain_name"])){
            $ssl_binfo["domain_name"] = explode(",",$ssl_binfo["domain_name"]);
        }
        if(!is_null($ssl_binfo["params"]) && !empty($ssl_binfo["params"])){
            $ssl_binfo["params"] = explode(",",$ssl_binfo["params"]);
        }
        $fill = $images = $validate = "";
        if(isset($ssl_binfo["fill"]) && !empty($ssl_binfo["fill"])){
            $fill = json_decode($ssl_binfo["fill"],true);
        }
        if(isset($ssl_binfo["images"]) && !empty($ssl_binfo["images"])){
            $images = json_decode($ssl_binfo["images"],true);
        }
        if(isset($ssl_binfo["validates"]) && !empty($ssl_binfo["validates"])){
            $validate = json_decode($ssl_binfo["validates"],true);
        }
        $this->assign([
              'ssl_binfo'=>$ssl_binfo,
              "order_id"=>$order_id,
              'fill'=>$fill,
              'images'=>$images,
              'validates'=>$validate,
          ]);
        if(strpos($ssl_binfo["api_name"],"dv") !== false){
            $this->display('ssl_dv_open');
        }else{
            $this->display('ssl_business_open');
        }
    }

    /**
     * 域名验证
     * @author: Guopeng
     */
    function check_domain(){
        $ymbd = I('post.ymbd');
        $ssl = new \Frontend\Model\SslModel();
        $result = $ssl->checkDomain($ymbd);
        if($result){
            echo json_encode(array('status'=>1));
        }else{
            echo json_encode(array('status'=>0));
        }
    }

    public function ssl_dv_open()
    {
        $ssl = new \Frontend\Model\SslModel();
        $result = $ssl->ssl_dv_open();
        $message = $ssl->getError();
        if($result){
            $message = "资料保存成功！";
            $this->success($message,'',true);
        }else{
            $this->error($message,'',true);
        }
    }

    /**
     * SSL证书开通域名绑定
     * @author: Guopeng
     */
    public function ssl_open_bind()
    {
        $ssl = new \Frontend\Model\SslModel();
        $result = $ssl->ssl_open_bind();
        if($result){
            $message = "绑定域名成功！";
            $this->success($message,'',true);
        }else{
            $message = "绑定域名失败！".$ssl->getError();
            $this->error($message,'',true);
        }
    }

    /**
     * SSL证书资料
     * @author: Guopeng
     */
    public function ssl_fill_info()
    {
        $ssl = new \Frontend\Model\SslModel();
        $result = $ssl->ssl_fill_info();
        $message = $ssl->getError();
        if($result){
            $message = "保存资料成功！";
            $this->success($message,'',true);
        }else{
            $this->error($message,'',true);
        }
    }

    public function ssl_upload_info(){
        $ssl = new SslModel();
        $result = $ssl->ssl_upload_info();
        if($result){
            $message = "上传成功！";
            $array = ['status'=>$result['status'],'info'=>$message];
            echo json_encode($array);
            exit();
        }else{
            $message = $ssl->getError();
            $array = ['status'=>$result['status'],'info'=>$message];
            echo json_encode($array);
            exit();
        }
//        if($result){
//            $message = "上传成功！";
//            $this->success($message,'',true);
//        }else{
//            $message = $ssl->getError();
//            $this->error($message,'',true);
//        }
    }

    /*
     * ssl
     */
    public function ssl_user_validate(){
        $ssl = new SslModel();
        $result = $ssl->ssl_user_validate();
        if($result){
            $message = "验证成功！";
            $this->success($message,'',true);
        }else{
            $message = $ssl->getError();
            $this->error($message,'',true);
        }
    }

    /**
     * 获取SSL开通进度
     * @author : Guopeng
     * @param : $ssl_id,保存在主站的订单编号
     * @return  boolean
     */
    public function get_open_progress()
    {
        $ssl = new SslModel();
        $data = request();
        $ssl_id = $data['ssl_id'];
        if(is_null($ssl_id) || !is_numeric($ssl_id) || strpos($ssl_id,".") != false){
            $msg = "业务编号为空或错误";
            $this->error($msg,"",true);
        }
        $result = $ssl->get_business_info($ssl_id);
        if($result){
            $msg = "业务开通成功";
            $this->success($msg,"",true);
        }elseif($result === 0){
            $this->error("",1,true);
        }else{
            $msg = $ssl->getError();
            $this->error($msg,0,true);
        }
    }
    public function ssl_validate(){
        $data = request();
        $ssl = new SslModel();
        $user_id = session("user_id");
        $yw_id = $data['yw_id']+0;
        $where["Id"] = $yw_id;
        $where["user_id"] = $user_id;
        $dataarray["open_step"] = 6;
        $dataarray["state"] = 6;
        $save = $ssl->where($where)->save($dataarray);
        if($save !== false){
            $msg = "验证成功";
            $this->success($msg,"",true);
        }else{
            $msg = "验证失败";
            $this->error($msg,0,true);
        }
    }

    /**
     * 同步业务信息
     * @date: 2016年11月29日 下午2:53:15
     * @author: Lyubo
     * @param: $yw_id,$ptype
     * @return boolean
     */
    public function memberSync(){
        $data =request();
        $yw_id = $data['business_id']+0;
        $url = U('frontend/ssl/ssllist','',false);
        $ssl = new \Frontend\Model\SslModel();
        $sslRespository = new \Common\Respository\SslRespository($ssl);
        $result = $sslRespository->SslBusinessSynchronizing($yw_id);
        if($result){
            $this->success("同步业务信息成功",$url,true);
        }else{
            $this->error("同步业务信息失败-".$sslRespository->model()->getError(),$url,true);
        }
    }
}