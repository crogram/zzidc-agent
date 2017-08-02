<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;
/**
* FASTVPS
* @date: 2016年10月25日 下午3:51:53
* @author: Lyubo
*/
class FastvpsController extends FrontendController
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
     * 获取用户所有FASTVPS业务
     * @author: Lyubo
     * @return: $list
     */
    public function fastvpslist(){
        $fs = new \Frontend\Model\FastvpsModel();
        //取得post查询条件
        $where = $fs->conver_par();
        $order = I("get.order");
        $info = $fs->get_vps_business_list($where,10,$order);
         $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list']
        ]);
        $this->display();
    }
    /**
     * 获取用户所有FASTVPS待续费业务
     * @author: Lyubo
     * @return: $list
     */
    public function fastvpsrenew(){
        $fs = new \Frontend\Model\FastvpsModel();
        //取得post查询条件
        $where = $fs->conver_par();
        $order = I("get.order");
        $info = $fs->get_vps_renew_list($where,10,$order);
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
        $fs = new \Frontend\Model\FastvpsModel();
        $sum = $fs->stateCount();
        $date["status"] = 'ok';
        $date["sum"] = $sum;
        return $this->ajaxReturn($date);
    }
    /**
     * 修改备注
     * @author: Lyubo
     * @param: remark，business_id
     * @return: boolean
     */
    public function remark(){
        $fs = new \Frontend\Model\FastvpsModel();
        $result = $fs->remark_edit();
        if($result){
            $data = "ok";
            $this->ajaxReturn($data,'json');
        }else{
            $data = "fail";
            $this->ajaxReturn($data,'json');
        }
    }
/**************************快云VPS业务开通***************************/
    /**
    * 快云VPS开通
    * @date: 2016年11月19日 下午3:43:39
    * @author: Lyubo
    * @param: $order_id
    * @return: array
    */
    public function fastvps_open()
    {
        $order = new \Frontend\Model\OrderModel();
        $fastvps = new \Frontend\Model\FastvpsModel();
        $open_info = request();
        $order_id = $open_info['order_id'];
        $member_id = session('user_id');
        if(is_null($order_id) || $order_id <= 0 || !is_int($order_id))
        {
            $message = '订单不存在，或者订单错误';
            $url = U('frontend/order/orderlist',['state' => '6'],false);
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            $result = $fastvps->fastvps_open($order_id,$member_id);
            $message = business_code($result['code']);
            if($result['code'] == -1)
            {
                $message = "快云VPS开通成功，您可以在[产品中心-快云VPS]查看业务信息";
                //发送开通邮件
                business_sendMail('11',$result,$member_id);
                $url = U('frontend/fastvps/fastvpslist','',false);
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
/**************************快云VPS业务开通***************************/
    /**************************快云VPS续费业务***************************/
    /**
     * 快云VPS续费业务
     * @author:Guopeng
     */
    function fastvps_renewals()
    {
        $user_id = intval(session("user_id"));
        $vs = new \Frontend\Model\FastvpsModel();
        $url = U('frontend/fastvps/fastvpslist','',false);
        $data = request();
        $yw_id = $data["fastvps_id"]+0;
        if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
        {
            $message = "业务编号为空或错误";
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            $order_time = $data["renewalstime"]+0;
            $result = $vs->fastvps_renewals($user_id,$yw_id,$order_time);
            $message = business_code($result);
            if($result == -1)
            {
                $message = "快云VPS续费成功，您可以在[会员中心]查看订单信息和快云VPS信息";
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
            $result = $vs->fastvps_renewals($user_id,$yw_id,'',"get");
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
    /**************************快云VPS续费业务***************************/
    /**************************快云VPS升级业务***************************/
    /**
     * 快云VPS升级业务
     * @author:Guopeng
     */
    function fastvps_uplevel()
    {
        $user_id = intval(session("user_id"));
        $vs = new \Frontend\Model\FastvpsModel();
        $url = U('frontend/fastvps/fastvpslist','',false);
        $data = request();
        $yw_id = $data["fastvps_id"]+0;
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
                $result = $vs->fastvps_upgrade($user_id,$yw_id,$up_product_id);
                $message = business_code($result);
                if($result == -1)
                {
                    $message = "快云VPS升级成功，您可以在[会员中心]查看订单信息和快云VPS信息";
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
            $result = $vs->fastvps_upgrade($user_id,$yw_id,'',"get");
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
    /**************************快云VPS升级业务***************************/
    /**************************快云VPS增值业务***************************/
    /**
     * 快云VPS增值业务
     * @author:Guopeng
     */
    function fastvps_appreciation()
    {
        $user_id = intval(session("user_id"));
        $vs = new \Frontend\Model\FastvpsModel();
        $url = U('frontend/fastvps/fastvpslist','',false);
        $data = request();
        $yw_id = $data["fastvps_id"]+0;
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
                $result = $vs->fastvps_appreciation($user_id,$yw_id,$appreciation_name,$appreciation_size);
                $message = business_code($result);
                if($result == -1)
                {
                    $message = "快云VPS增值成功，您可以在[会员中心]查看订单信息和快云VPS信息";
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
            //快云VPS业务信息
            $result = $vs->fastvps_appreciation($user_id,$yw_id,'','',"get");
            $message = business_code($result);
            if($result <= 0)
            {
                $this->error($message,$url);
            }
            $this->assign($result);
            $this->display();
        }
    }
    /**************************快云VPS增值业务***************************/
    /**************************快云VPS业务转正***************************/
    /**
     * 快云VPS业务转正
     * @author:Guopeng
     */
    function fastvps_onformal()
    {
        $user_id = intval(session("user_id"));
        $vs = new \Frontend\Model\FastvpsModel();
        $url = U('frontend/fastvps/fastvpslist','',false);
        $data = request();
        $yw_id = $data["fastvps_id"]+0;
        if(is_null($yw_id) || !is_numeric($yw_id) || strpos($yw_id,".") != false || $yw_id <= 0)
        {
            $message = "业务编号为空或错误";
            $this->error($message,$url);
        }
        if(IS_POST)
        {
            /**
             * 快云VPS转正
             */
            $service_time = $data["service_time"]+0;
            $result = $vs->fastvps_onformal($user_id,$yw_id,$service_time);
            $message = business_code($result);
            if($result == -1)
            {
                $message = "快云VPS转正成功，您可以在[会员中心]查看快云VPS信息";
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
             * 显示快云VPS转正信息
             */
            $result = $vs->fastvps_onformal($user_id,$yw_id,'','get');
            if($result <= 0)
            {
                $message = business_code($result);
                $this->error($message,$url);
            }
            $this->assign($result);
            $this->display();
        }
    }
    /**************************快云VPS业务转正***************************/
    /**************************快云VPS业务同步***************************/
    /**
     * 同步业务信息
     * @date: 2016年11月29日 下午2:53:15
     * @author: Lyubo
     * @param: $yw_id,$type
     * @return boolean
     */
    public function memberSync(){
        $data =request();
        $yw_id = $data['business_id']+0;
        $ptype = $data['ptype'];
        $fastvps = new \Frontend\Model\FastvpsModel();
        $url = U('frontend/fastvps/fastvpslist','',false);
        $fastvpsRespository = new \Common\Respository\VpsBusinessRespository($fastvps);
        $result = $fastvpsRespository->VpsBusinessSynchronizing($yw_id,$ptype);
        if($result){
            $this->success("同步业务信息成功",$url,true);
        }else{
            $this->error("同步业务信息失败-".$fastvpsRespository->model()->getError(),$url,true);
        }
    }
    /**************************快云VPS业务同步***************************/
}