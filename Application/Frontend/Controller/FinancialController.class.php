<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;

class FinancialController extends FrontendController{
    public function _initialize()
    {
        parent::_initialize();
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
    }
    
    /**
    * 会员账户金额信息
    * @date: 2016年11月2日 下午3:57:35
    * @author: Lyubo
    * @return: 会员余额，消费记录
    */
    public function member_account_info(){
        $financial = new \Frontend\Model\FinancialModel();
        $where = $financial->conver_par();
        $member_account_info = $financial->account_info($where);
        $this->assign([
            'account_info'=>$member_account_info
        ]);
        $this->display();
    }
    /**
    * 交易记录
    * @date: 2016年11月2日 下午5:11:07
    * @author: Lyubo
    * @param: $parms
    * @return: boolean
    */
    public function member_transactions_list(){
        $financial = new \Frontend\Model\FinancialModel();
        $where = $financial->conver_par();
        $member_tran_list = $financial->tran_list($where,$per_page=10);
        $this->assign([
            "tran_list" => $member_tran_list["list"],
            "count"=>$member_tran_list['count'],
            "page"=>$member_tran_list["page_show"],
        ]);
        $this->display();
    }
/************************************************支付宝充值*****************************************/
    /**
    * 在线充值
    * @date: 2016年11月3日 上午10:59:01
    * @author: Lyubo
    * @param: 支付宝
    * @return:
    */
    public function member_recharge(){
        $member = new \Frontend\Model\MemberModel();
        $member_info = $member->get_member_money();
        $this->assign(["member_info"=>$member_info]);
        $this->display();
    }
    public function member_recharge_online(){
        $financial = new \Frontend\Model\FinancialModel();
        $alipay = new \Common\Aide\AlipayAide();
        $tenpay = new \Common\Aide\TenpayAide();
        $shengpay = new \Common\Aide\ShengpayAide();
        $member = new  \Frontend\Model\MemberModel();
        $data = request();
        
        $member_id = session("user_id");
        
        $money = round($data["money"],2);
        
        $orderNo = create_order_no ();
        $member_where['user_id'] = [ 'eq', $member_id ] ;
        $member_info = $member->get_member_info($member_where);
        
        $body = $member_info['login_name'] . '-' . $orderNo . '-' . $member_info['user_id'].'-'.$money;
        
        $zfb_parm['recharge_type'] = 2;
        
        $zfb_parm['orderNo'] = $orderNo;
        
        $zfb_parm['money'] = $money;
        
        $type = $data['paymode'];
         if($type == 'alipay'){ 
             $result = $financial->user_recharge_online($member_id, $money, $orderNo, 'ZFB');
         }elseif($type == 'tenpay'){
             $result = $financial->user_recharge_online($member_id, $money, $orderNo, 'CFT');
         }elseif ($type == 'shengpay'){
             $result = $financial->user_recharge_online($member_id, $money, $orderNo, 'SFT');
         }
        if ($result === false) {
            $this->error("充值失败，请重新充值",U("frontend/financial/member_recharge",'',false));
        } else{
            if($type == 'alipay'){
                //支付宝支付
                $alipay->buySomething($orderNo, $money, $body);
            }elseif($type == 'tenpay'){
                //财付通支付
                $tenpay->tenpay($orderNo, $money, $body);
            }elseif($type == 'shengpay'){
                //盛付通支付
                $shengpay->buySheng($orderNo, $money, $body);
            }
        }
    }
}