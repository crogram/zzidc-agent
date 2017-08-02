<?php
namespace Frontend\Controller;

use Frontend\Controller\FrontendController;

/**
 * VPS
 * @date: 2016年10月25日 下午3:51:53
 * @author: Lyubo
 */
class VpsController extends FrontendController
{
    public function _initialize()
    {
        parent::_initialize();
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
    }

    /**
     * 获取用户所有VPS业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     * @return: $list
     */
    public function vpslist()
    {
        $fs = new \Frontend\Model\VpsModel();
        $where = $fs->conver_par();
        $order = I("get.order");
        $info = $fs->get_vps_business_list($where,10,$order);
        $this->assign(["count" => $info['count'],"page" => $info["page_show"],"info" => $info['list']]);
        $this->display();
    }
    /**
     * 获取用户所有VPS待续费业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     * @return: $list
     */
    public function vpsrenew()
    {
        $fs = new \Frontend\Model\VpsModel();
        $where = $fs->conver_par();
        $order = I("get.order");
        $info = $fs->get_vps_renew_list($where,10,$order);
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
        $fs = new \Frontend\Model\VpsModel();
        $sum = $fs->stateCount();
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
        $vps = new \Frontend\Model\VpsModel();
        $result = $vps->remark_edit();
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
    /**************************VPS业务开通***************************/
    /**
     * 开通VPS业务
     * @date: 2016年11月19日 上午10:02:37
     * @author: Lyubo
     * @param: $order
     * @return:
     */
    public function vps_open()
    {
        $order = new \Frontend\Model\OrderModel();
        $vps = new \Frontend\Model\VpsModel();
        $open_info = request();
        $order_id = $open_info['order_id'];
        $member_id = session('user_id');
        if(is_null($order_id) || $order_id <= 0 || !is_int($order_id))
        {
            $message = '订单不存在';
            $url = U('frontend/order/orderlist',['state' => '6'],false);
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            $result = $vps->vps_open($order_id,$member_id);
            $message = business_code($result['code']);
            if($result['code'] == -1)
            {
                $message = "VPS开通成功，您可以在[产品中心-VPS]查看VPS业务信息";
                //发送开通邮件
                business_sendMail('11',$result,$member_id);
                $url = U('frontend/vps/vpslist','',false);
                $this->success($message,$url);
            }
            else
            {
                $this->error($message);
            }
        }
        else
        {
            //显示开通页面
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
    /**************************VPS业务开通***************************/
    /**************************VPS业务续费***************************/
    /**
     * VPS业务续费
     * @author:Guopeng
     */
    function vps_renewals()
    {
        $user_id = intval(session("user_id"));
        $vs = new \Frontend\Model\VpsModel();
        $url = U('frontend/vps/vpslist','',false);
        $data = request();
        $yw_id = $data["vps_id"]+0;
        if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
        {
            $message = "业务编号为空或错误";
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            $order_time = $data["renewalstime"] + 0;
            $result = $vs->vps_renewals($user_id,$yw_id,$order_time);
            $message = business_code($result);
            if($result == -1)
            {
                $message = "VPS续费成功，您可以在[会员中心]查看订单信息和VPS信息";
                $this->success($message,$url);
            }
            else
            {
                $this->error($message,$url);
            }

        }
        else
        {
            // 业务信息
            $result = $vs->vps_renewals($user_id,$yw_id,'',"get");
            $message = business_code($result);
            if($result <= 0)
            {
                $this->error($message,$url);
            }
            // 续费业务信息
            $this->assign($result);
            $this->display();

        }
    }
    /**************************VPS业务续费***************************/
    /**************************VPS业务升级***************************/
    /**
     * VPS业务升级
     * @author:Guopeng
     */
    function vps_uplevel()
    {
        $user_id = session("user_id");
        $vs = new \Frontend\Model\VpsModel();
        $url = U('frontend/vps/vpslist','',false);
        $data = request();
        $yw_id = $data["vps_id"]+0;
        if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
        {
            $message = "业务编号为空或错误";
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            $up_product_id = $data["up_product_id"]+0;
            if(is_null($up_product_id) || !is_numeric($up_product_id) || strpos($up_product_id,".") != false || $up_product_id <= 0)
            {
                $message = "产品名称错误";
                $this->error($message,$url);
            }
            else
            {
                $result = $vs->vps_upgrade($user_id,$yw_id,$up_product_id);
                $message = business_code($result);
                if($result == -1)
                {
                    $message = "VPS升级成功，您可以在[会员中心]查看订单信息和VPS信息";
                    $this->success($message,$url);
                }
                else
                {
                    $this->error($message,$url);
                }
            }
        }
        else
        {
            // 业务信息
            $result = $vs->vps_upgrade($user_id,$yw_id,'',"get");
            $message = business_code($result);
            if($result <= 0)
            {
                $this->error($message,$url);
            }
            // 续费业务信息
            $this->assign($result);
            $this->display();
        }
    }
    /**************************VPS业务升级***************************/
    /**************************VPS业务增值***************************/
    /**
     * VPS业务增值
     * @author:Guopeng
     */
    function vps_appreciation()
    {
        $user_id = intval(session("user_id"));
        $vs = new \Frontend\Model\VpsModel();
        $url = U('frontend/vps/vpslist','',false);
        $data = request();
        $yw_id = $data["vps_id"]+0;
        if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
        {
            $message = "业务编号为空或错误";
            $this->error($message,$url);
        }
        if(IS_POST)
        {
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
                $result = $vs->vps_appreciation($user_id,$yw_id,$appreciation_name,$appreciation_size);
                $message = business_code($result);
                if($result == -1)
                {
                    $message = "VPS增值成功，您可以在[会员中心]查看订单信息和VPS信息";
                    $this->success($message,$url);
                }
                else
                {
                    $this->error($message,$url);
                }
            }
        }
        else
        {
            $method = "get";
            // 业务信息
            $result = $vs->vps_appreciation($user_id,$yw_id,'','',$method);
            $message = business_code($result);
            if($result <= 0)
            {
                $this->error($message,$url);
            }
            // 续费业务信息
            $this->assign($result);
            $this->display();
        }
    }
    /**************************VPS业务增值***************************/
    /**************************VPS业务转正***************************/
    /**
     * VPS业务转正
     * @author:Guopeng
     */
    function vps_onformal()
    {
        $user_id = intval(session("user_id"));
        $vs = new \Frontend\Model\VpsModel();
        $url = U('frontend/vps/vpslist','',false);
        $data = request();
        $yw_id = $data["vps_id"]+0;
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
            $result = $vs->vps_onformal($user_id,$yw_id,$service_time);
            $message = business_code($result);
            if($result == -1)
            {
                $message = "VPS转正成功，您可以在[会员中心]查看VPS信息";
                $this->success($message,$url);
            }
            else
            {
                $this->error($message,$url);
            }
        }
        else
        {
            /**
             * 显示主机转正
             */
            $result = $vs->vps_onformal($user_id,$yw_id,'','get');
            if($result <= 0)
            {
                $message = business_code($result);
                $this->error($message,$url);
            }
            $this->assign($result);
            $this->display();
        }
    }
    /**************************VPS业务转正***************************/
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
        $ptype = $data['ptype'];
        $url = U('frontend/vps/vpslist','',false);
        $vps = new \Frontend\Model\VpsModel();
        $vpsRespository = new \Common\Respository\VpsBusinessRespository($vps);
        $result = $vpsRespository->VpsBusinessSynchronizing($yw_id,$ptype);
        if($result){
            $this->success("同步业务信息成功",$url,true);
        }else{
            $this->error("同步业务信息失败-".$vpsRespository->model()->getError(),$url,true);
        }
    }
    /**
    * 手机站VPS购买页
    * @date: 2017年5月3日 上午9:16:49
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function vps_buy(){
        echo 1;die();
        $this->display();
    }
    
}