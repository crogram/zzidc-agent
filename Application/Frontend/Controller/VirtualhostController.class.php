<?php
namespace Frontend\Controller;

use Frontend\Controller\FrontendController;
class VirtualhostController extends FrontendController
{
    public function _initialize()
    {
        parent::_initialize();
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
    }
    /******************会员中心start****************/
    /**
     * 获取用户所有virtualhost业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     * @return: $list
     */
    public function virtualhostlist()
    {
        $vh = new \Frontend\Model\VirtualhostModel();
        $where = $vh->conver_par();
        $order = I("get.order");
        $info = $vh->get_host_business_list($where,10,$order);
        $info = Get_bind_domain($info);
        $this->assign(["count" => $info['count'],"page" => $info["page_show"],"info" => $info['list']]);
        $this->display();
    }
    /**
     * 用户所有virtualhost待续费业务列表
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     * @return: $list
     */
    public function virtualhostrenew()
    {
        $vh = new \Frontend\Model\VirtualhostModel();
        $where = $vh->conver_par();
        $order = I("get.order");
        $info = $vh->get_host_renew_list($where,10,$order);
        $info = Get_bind_domain($info);
        $this->assign(["count" => $info['count'],"page" => $info["page_show"],"info" => $info['list']]);
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
        $vh = new \Frontend\Model\VirtualhostModel();
        $sum = $vh->host_stateCount();
        $date["status"] = 'ok';
        $date["sum"] = $sum;
        return $this->ajaxReturn($date);
    }
    /**
     * 修改备注
     * @date: 2016年10月27日 上午11:23:22
     * @author: Lyubo
     * @param: remark，business_id
     * @return: boolean
     */
    public function remark()
    {
        $virtualhost = new \Frontend\Model\VirtualhostModel();
        $result = $virtualhost->remark_edit();
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
    /******************会员中心end****************/
    /******************业务操作****************/
    /**
     * 主机开通
     * @date: 2016年11月17日 下午4:39:51
     * @author: Lyubo
     * @return: array
     */
    public function virtualhost_open()
    {
        $order = new \Frontend\Model\OrderModel();
        $vhost = new \Frontend\Model\VirtualhostModel();
        $open_info = request();
        $order_id = $open_info['order_id']+0;
        $member_id = session('user_id');
        $product_type = $open_info['product_type'];//跳转对应的业务列表
        $type = 0;
        if($product_type == 8){
            $type = 1;
        }elseif($product_type == 16){
            $type = 2;
        }elseif($product_type == 17){
            $type = 3;
        }elseif($product_type == 15){
            $type = 4;
        }elseif(is_null($product_type) || $product_type == '' || $product_type == 7){
            $type = 0;
        }
        if(is_null($order_id) || $order_id <= 0 || !is_int($order_id))
        {
            $message = '订单不存在，或者订单错误';
            $url = U('frontend/order/orderlist',['state' => '6'],false);
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            //开通虚拟主机
            $result = $vhost->virtualhost_open($order_id,$member_id,$type);
            $message = business_code($result['code']);
            if($result['code'] == '-1')
            {
                $message = "虚拟主机开通成功！您可以在[虚拟主机列表]查看虚拟主机信息";
                //发送开通邮件
                business_sendMail('10',$result,$member_id);
                $url = U('frontend/virtualhost/virtualhostlist',['virtual_type' => $type],false);
                $this->success($message,$url);
            }
            else if($result['code'] == '3050')
            {
                $url = U('frontend/order/orderlist',['state' => '3'],false);
                $this->error($message,$url);
            }
            else
            {
                $this->error($message);
            }

        }
        else
        {
            //显示主机开通页面
            $order_info = $order->order_find($order_id);
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
     * 主机业务增值
     * @author: Guopeng
     */
    public function virtualhost_appreciation()
    {
        $user_id = intval(session("user_id"));
        $vhost = new \Frontend\Model\VirtualhostModel();
        $data = request();
        $yw_id = $data["virtualhost_id"]+0;
        $type = $data["virtual_type"]+0;
        if(is_null($type) || $type == '' || ($type != 0 && $type != 1 && $type != 2 && $type != 3))
        {
            $type = 0;
        }
        $url = U('frontend/virtualhost/virtualhostlist',['virtual_type' => $type],false);
        if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
        {
            $message = "业务编号为空或错误";
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            /**
             * 主机业务增值
             */
            $appreciation_name = $data["api_name"]; // 增值类型
            $appreciation_size = $data["app_size"]+0; // 增值容量
            if(is_null($appreciation_size) || !is_numeric($appreciation_size) || strpos($appreciation_size,".") != false || $appreciation_size <= 0)
            {
                $message = "增值容量错误";
                $this->error($message,$url);
            }
            elseif(is_null($appreciation_name))
            {
                $message = "增值类型错误";
                $this->error($message,$url);
            }
            else
            {
                $result = $vhost->virtualhost_appreciation($user_id,$yw_id,$appreciation_name,$appreciation_size);
                $product_name = "虚拟主机";
                if($type == 1){
                    $product_name = "香港虚拟主机";
                }else if($type == 2){
                    $product_name = "美国虚拟主机";
                }else if($type == 3){
                    $product_name = "云虚拟主机";
                }
                if($result == -1)
                {
                    $message = $product_name."增值成功，您可以在[会员中心]查看".$product_name."信息";
                    $this->success($message,$url);
                }
                else
                {
                    $message = business_code($result);
                    $this->error($message,$url);
                }
            }
        }
        else
        {
            /**
             * 主机增值业务信息显示
             */

            $result = $vhost->virtualhost_appreciation($user_id,$yw_id,"","","get");
            if($result <= 0)
            {
                $message = business_code($result);
                $this->error($message,$url);
            }
            $result ['virtual_type'] = $type;
            $this->assign($result);
            $this->display();

        }
    }

    /**
     * 主机业务续费
     * @author:Guopeng
     */
    public function virtualhost_renew()
    {
        $user_id = intval(session("user_id"));
        $vhost = new \Frontend\Model\VirtualhostModel();
        $data = request();
        $yw_id = $data["virtualhost_id"]+0;
        $type = $data["virtual_type"]+0;
        if(is_null($type) || $type == '' || ($type != 0 && $type != 1 && $type != 2 && $type != 3))
        {
            $type = 0;
        }
        $url = U('frontend/virtualhost/virtualhostlist',['virtual_type' => $type],false);
        if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
        {
            $message = "业务编号为空或错误";
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            /**
             * 主机业务续费
             */
            $type = $data["virtual_type"]+0;
            $url = U('frontend/virtualhost/virtualhostlist',['virtual_type' => $type],false);
            $order_time = $data["renewalstime"]+0;
            $result = $vhost->virtualhost_renewals($user_id,$yw_id,$order_time);
            $product_name = "虚拟主机";
            if($type == 1)
            {
                $product_name = "香港虚拟主机";
            }
            else if($type == 2)
            {
                $product_name = "美国虚拟主机";
            }
            else if($type == 3)
            {
                $product_name = "云虚拟主机";
            }
            if($result == -1)
            {
                $message = $product_name."续费成功，您可以在[会员中心]查看".$product_name."信息";
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
            /**
             * 主机续费业务信息显示
             */
            $result = $vhost->virtualhost_renewals($user_id,$yw_id,'','get');
            if($result <= 0)
            {
                $message = business_code($result);
                $this->error($message,$url);
            }
            $result['virtual_type'] = $type;
            $this->assign($result);
            $this->display();

        }
    }

    /**
     * 主机业务升级
     * 
     * @author :Guopeng
     */
    public function virtualhost_uplevel()
    {
        $user_id = intval(session("user_id"));
        $vhost = new \Frontend\Model\VirtualhostModel();
        $data = request();
        $yw_id = $data["virtualhost_id"] + 0;
        $type = $data["virtual_type"] + 0;
        if (is_null($type) || $type == '' || ($type != 0 && $type != 1 && $type != 2 && $type != 3)) {
            $type = 0;
        }
        $url = U('frontend/virtualhost/virtualhostlist',['virtual_type' => $type], false);
        if (is_null($yw_id) || ! is_numeric($yw_id) || strpos($yw_id, ".") != false || $yw_id <= 0) {
            $message = "业务编号为空或错误";
            $this->error($message, $url);
        }
        if (IS_POST) {
            /**
             * 主机业务升级
             */
            $url = U('frontend/virtualhost/virtualhostlist',['virtual_type' => $type], false);
            $up_product_id = $data["up_product_id"] + 0;
            $type = $data["virtual_type"] + 0;
            if (is_null($up_product_id) || ! is_numeric($up_product_id) || strpos($up_product_id, ".") != false || $up_product_id <= 0) {
                $message = "产品名称错误";
                $this->error($message, $url);
            } else {
                //验证域名是否备案
                $check = $vhost->proving($up_product_id,$yw_id);
                if(!$check){
                    $message = '您的业务中有域名未在我司备案，个人B型及企业B型绑定的域名必须在我司备案，请将域名备案接入我司后再行升级。';
                    $this->error($message, $url);
                }
                $result = $vhost->virtualhost_uplevel($user_id, $yw_id, $up_product_id, $type);
                $product_name = "虚拟主机";
                if ($type == 1) {
                    $product_name = "香港虚拟主机";
                } else 
                    if ($type == 2) {
                        $product_name = "美国虚拟主机";
                    } else 
                        if ($type == 3) {
                            $product_name = "云虚拟主机";
                        } else 
                            if ($type == 4) {
                                $product_name = "织梦主机";
                            }
                if ($result == - 1) {
                    $message = $product_name . "升级成功，您可以在[会员中心]查看" . $product_name . "信息";
                    $this->success($message, $url);
                } else {
                    $message = business_code($result);
                    $this->error($message, $url);
                }
            }
        } else {
            /**
             * 主机升级业务信息显示
             */
            $belong = $vhost->where(array("user_id" => $user_id,"id" => $yw_id))->find();
            if (! $belong) {
                $message = '业务不存在或不属于该会员！';
                $this->error($message, $url);
            } else {
                $result = $vhost->virtualhost_uplevel($user_id, $yw_id, '', '', 'get');
                if ($result <= 0) {
                    $message = business_code($result);
                    $this->error($message, $url);
                }
                $result['virtual_type'] = $type;
                $this->assign($result);
                $this->display();
            }
        }
    }

    /**
    * 同步业务信息
    * @date: 2016年11月29日 下午2:53:15
    * @author: Lyubo
    * @param: $business_id,$ptype
    */
    public function memberSync(){
        $data =request();
        $yw_id = $data['business_id']+0;
        $ptype = $data['ptype'];
        $url = U('frontend/virtualhost/virtualhostlist',['virtual_type'=>$data['virtual_type']],false);
        $vhost = new \Frontend\Model\VirtualhostModel();
        $vhostRespository = new \Common\Respository\VirtualhostBusinessRespository($vhost);
        $result = $vhostRespository->VirtualhostBusinessSynchronizing($yw_id,$ptype);
        if($result){
            $this->success("同步业务信息成功",$url,true);
        }else{
            $this->error("同步业务信息失败-".$vhostRespository->model()->getError(),$url,true);
        }
    }

    /**
     * 主机业务转正
     * @author:Guopeng
     */
    function virtualhost_onformal()
    {
        $user_id = intval(session("user_id"));
        $vh = new \Frontend\Model\VirtualhostModel();
        $data = request();
        $yw_id = $data["virtualhost_id"]+0;
        $type = $data["virtual_type"]+0;
        if(is_null($type) || $type == '' || ($type != 0 && $type != 1 && $type != 2 && $type != 3))
        {
            $type = 0;
        }
        $url = U('frontend/virtualhost/virtualhostlist',['virtual_type' => $type],false);
        if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
        {
            $message = "业务编号为空或错误";
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            /**
             * 主机转正
             */
            $service_time = $data["service_time"]+0;
            $result = $vh->virtualhost_onformal($user_id,$yw_id,$service_time);
            $url = U('frontend/virtualhost/virtualhostlist',['virtual_type' => $type],false);
            $product_name = "虚拟主机";
            if($type == 1){
                $product_name = "香港虚拟主机";
            }else if($type == 2){
                $product_name = "美国虚拟主机";
            }else if($type == 3){
                $product_name = "云虚拟主机";
            }else if($type == 4){
                $product_name = "织梦主机";
            }
            if($result == -1)
            {
                $message = $product_name."转正成功，您可以在[会员中心]查看".$product_name."信息";
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
            /**
             * 显示主机转正信息
             */
            $result = $vh->virtualhost_onformal($user_id,$yw_id,'','get');
            if($result <= 0)
            {
                $message = business_code($result);
                $this->error($message,$url);
            }
            $result ['virtual_type'] = $type;
            $this->assign($result);
            $this->display();
        }
    }
    
/******************业务操作****************/
}
