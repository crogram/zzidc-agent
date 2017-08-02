<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;
use Common\Data\GiantAPIParamsData;

class IpController extends FrontendController{
    public function _initialize()
    {
        parent::_initialize();
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
    }
    /**
     * 获取用户所有ip业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     * @return: $list,$page,$count
     */
    public function iplist(){
        $ip = new \Frontend\Model\IpModel();
        $where = $ip->conver_par();
        $order = I("get.order");
        $info = $ip->get_cloudserver_business_ip_list($where,10,$order,GiantAPIParamsData::PTYPE_CLOUD_SERVER);
        $this->assign([
            "count"=>$info['count'],
            "page"=>$info["page_show"],
            "info" =>$info['list']
        ]);
        $this->display();
    }
    /**
     * 获取用户所有ip待续费业务
     * @date: 2016年10月25日 下午4:42:40
     * @author: Lyubo
     * @return: $list,$page,$count
     */
    public function iprenew(){
        $ip = new \Frontend\Model\IpModel();
        $where = $ip->conver_par();
        $order = I("get.order");
        $info = $ip->get_cloudserver_renew_ip_list($where,10,$order,GiantAPIParamsData::PTYPE_CLOUD_SERVER);
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
        $ip = new \Frontend\Model\IpModel();
        $sum = $ip->cloudserver_stateCount(GiantAPIParamsData::PNAME_CLOUDSERVER_IP);
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
    * IP续费
    * @date: 2016年12月6日 下午3:43:02
    * @author: Lyubo
    * @param: $ip_id
    * @return: boolean
    */
    public function ip_renewals(){
        $ip = new \Frontend\Model\IpModel();
        $data = request();
        $ip_id = $data['ip_id'];
        $member_id  = session("user_id");
        if(IS_POST){
            $renewalstime = $data['renewalstime'];
            if (is_null ( $ip_id ) || ! is_numeric ( $ip_id ) || strpos ( $ip_id, "." ) != false) {
                $this->error("业务编号为空或错误",U('frontend/ip/iplist' ,'',false));
            } else {
                $result = $ip->ip_renewals ( $ip_id, $member_id ,$renewalstime);
                $message = business_code ( $result );
                if($result==-1){
                    $this->success("IP续费成功",U('frontend/ip/iplist' ,'',false));
                }else{
                    $this->error($message,U('frontend/ip/iplist' ,'',false));
                }
            }
        }else{
            //显示续费信息
            if (is_null ( $ip_id ) || ! is_numeric ( $ip_id ) || strpos ( $ip_id, "." ) != false || $ip_id <= 0) {
                $this->error("业务编号为空或错误",U('frontend/ip/iplist' ,'',false));
            } else {
                // 业务信息
                $ip_info = $ip->show_ip_renewals ( $member_id, $ip_id);
                if ($ip_info <= 0) {
                    $msg = business_code($ip_info);
                    $this->error($msg,U('frontend/ip/iplist' ,'',false));
                }else{
                    $this->assign($ip_info);
                    $this->display();
                }
            }
        }
    } 
    /**
    * Ip升级带宽
    * @date: 2017年1月6日 上午10:02:09
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function ip_upgrade(){
        $ip = new \Frontend\Model\IpModel();
        $data = request();
        $member_id  = session("user_id");
        $ip_id = $data['ip_id'];
        if(IS_POST){
            $daikuan = $data['daikuan'];
            if (is_null ( $ip_id ) || ! is_numeric ( $ip_id ) || strpos ( $ip_id, "." ) != false) {
                $data["mes"] = "业务编号为空或错误";
                $this->ajaxReturn($data);
            } else {
                $result = $ip->ip_upgrade ( $ip_id, $member_id ,$daikuan);
                $message = business_code ( $result );
                if($result==-1){
                    $data["mes"] = "IP升级提交成功，请稍后同步业务信息";
                    $data["url"] = U('frontend/ip/iplist' ,'',false);
                    $this->ajaxReturn($data);
                }else{
                    $data["mes"] = $message;
                    $data["url"] = U('frontend/ip/iplist' ,'',false);
                    $this->ajaxReturn($data);
                }
            }
        }else{
            //显示升级信息
            if (is_null ( $ip_id ) || ! is_numeric ( $ip_id ) || strpos ( $ip_id, "." ) != false || $ip_id <= 0) {
                $this->error("业务编号为空或错误",U('frontend/ip/iplist' ,'',false));
            } else {
                // 业务信息
                $ip_info = $ip->show_ip_upgrade ( $member_id, $ip_id);
                if ($ip_info <= 0) {
                    $msg = business_code($ip_info);
                    $this->error($msg,U('frontend/ip/iplist' ,'',false));
                }else{
                    $this->assign($ip_info);
                    $this->display();
                }
            }
        }
    }
    /**
    * 同步IP
    * @date: 2017年1月10日 下午6:37:35
    * @author: Lyubo
    * @param: variable
    * @return:
    */
   public function memberSync(){
       $data =request();
       $yw_id = $data['business_id']+0;
       $ptype = $data['ptype'];
       $url = U('frontend/ip/iplist','',false);
       $ip = new \Frontend\Model\IpModel();
       $ipRespository = new \Common\Respository\CloudserverIpBusinessRespository($ip);
       $result = $ipRespository->CloudserverIpBusinessSynchronizing($yw_id,$ptype);
       if($result){
           $this->success("同步业务信息成功",$url,true);
       }else{
           $this->error("同步业务信息失败-".$ipRespository->model()->getError(),$url,true);
       }
   }
    /**
    * 计算升级ip带宽价格
    * @date: 2017年1月6日 下午6:09:36
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function count_ipPrice(){
        $data =request();
        $ip = new \Frontend\Model\IpModel();
        $price = $ip->ipPrice($data);
        $date['price'] = $price;
        $this->ajaxReturn($price);
    }
}