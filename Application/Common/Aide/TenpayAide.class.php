<?php
namespace Common\Aide;
require_cache('./ThinkPHP/Library/Think/Tenpay/RequestHandler.class.php');
require_cache('./ThinkPHP/Library/Think/Tenpay/ResponseHandler.class.php');

/**
 * -------------------------------------------------------
 * | 与主站api同步或操作的类
 * | @author: duanbin
 * | @时间: 2016年10月24日 下午5:49:06
 * | @version: 1.0
 * -------------------------------------------------------
 */
class TenpayAide {

	private $alipay_config = [];
	
	private $web_config = [];
	
	public function __construct(){
		//获取网站的配置信息
		$this->web_config = WebSiteConfig();
	}
	
	/**
	 * 财付通支付
	 * @date: 2017年1月19日 下午5:10:57
	 * @author: Lyubo
	 * @param: variable
	 * @return:
	 */
	public function tenpay($orderNo, $money, $body) {
	    $tenpay_config = $this->web_config;
	    /* 获取提交的订单号 */
	    $out_trade_no = $orderNo;
	    /* 获取提交的商品名称 */
	    $product_name = $tenpay_config['site_name']."-".$_SERVER['SERVER_NAME'];
	    /* 获取提交的商品价格 */
	    $order_price = $money;
	    /* 获取提交的备注信息 */
	    $remarkexplain = $body;
	    /* 支付方式 */
	    $trade_mode = 1;
	    $strDate = date("Ymd");
	    $strTime = date("His");
	    /* 商品价格（包含运费），以分为单位 */
	    $total_fee = $order_price * 100;
	    /* 商品名称 */
	    $desc = $product_name;
	    /* 创建支付请求对象 */
	    $reqHandler = new \Think\Tenpay\RequestHandler();
	    $reqHandler->init();
	    $reqHandler->setKey(trim($tenpay_config['cftkey']));
	    $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");
	    //----------------------------------------
	    //设置支付参数
	    //----------------------------------------
	    $reqHandler->setParameter("partner", trim($tenpay_config['cft']));
	    $reqHandler->setParameter("out_trade_no", $out_trade_no);
	    $reqHandler->setParameter("total_fee", $total_fee);  //总金额
	    $reqHandler->setParameter("return_url", U('Api/Tenpay/returnurl',[ ],false, true));
	    $reqHandler->setParameter("notify_url", U('Api/Tenpay/notifyurl',[ ],false, true));
	    $reqHandler->setParameter("body", $desc);
	    $reqHandler->setParameter("bank_type", "DEFAULT");     //银行类型，默认为财付通
	    //用户ip
	    $reqHandler->setParameter("spbill_create_ip", get_userip()); //客户端IP
	    $reqHandler->setParameter("fee_type", "1");               //币种
	    $reqHandler->setParameter("subject", $desc);          //商品名称，（中介交易时必填）
	    //系统可选参数
	    $reqHandler->setParameter("sign_type", "MD5");       //签名方式，默认为MD5，可选RSA
	    $reqHandler->setParameter("service_version", "1.0");    //接口版本号
	    $reqHandler->setParameter("input_charset", "utf-8");      //字符集
	    $reqHandler->setParameter("sign_key_index", "1");       //密钥序号
	    //业务可选参数
	    $reqHandler->setParameter("attach", "");                //附件数据，原样返回就可以了
	    $reqHandler->setParameter("product_fee", "");           //商品费用
	    $reqHandler->setParameter("transport_fee", "0");         //物流费用
	    $reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
	    $reqHandler->setParameter("time_expire", "");             //订单失效时间
	    $reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
	    $reqHandler->setParameter("goods_tag", "");               //商品标记
	    $reqHandler->setParameter("trade_mode", $trade_mode);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
	    $reqHandler->setParameter("transport_desc", "");              //物流说明
	    $reqHandler->setParameter("trans_type", "1");              //交易类型
	    $reqHandler->setParameter("agentid", "");                  //平台ID
	    $reqHandler->setParameter("agent_type", "");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
	    $reqHandler->setParameter("seller_id", "");                //卖家的商户号
	    //请求的URL
	    $reqUrl = $reqHandler->getRequestURL();
	    //获取debug信息,建议把请求和debug信息写入日志，方便定位问题
	    $reqHandler->doSend2($reqUrl);
	    $debugInfo = $reqHandler->getDebugInfo();
	}
	
	
	public function returnurl(){
	    /* 创建支付应答对象 */
	    $tenpay_config = $this->web_config;
	    $resHandler = new \Think\Tenpay\ResponseHandler();
	    $resHandler->setKey(trim($tenpay_config['cftkey']));
	    //判断签名
	    if ($resHandler->isTenpaySign()) {
	        //通知id
	        $notify_id = $resHandler->getParameter("notify_id");
	        //商户订单号
	        $out_trade_no = $resHandler->getParameter("out_trade_no");
	        //财付通订单号
	        $transaction_id = $resHandler->getParameter("transaction_id");
	        //金额,以分为单位
	        $total_fee = $resHandler->getParameter("total_fee");
	        //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
	        $discount = $resHandler->getParameter("discount");
	        //支付结果
	        $trade_state = $resHandler->getParameter("trade_state");
	        //交易模式,1即时到账
	        $trade_mode = $resHandler->getParameter("trade_mode");
	        //支付完成时间
	        $time_end = $resHandler->getParameter("time_end");
	        if ("1" == $trade_mode) {
	            if ("0" == $trade_state) {
	                $result = $this->recharge_success($out_trade_no,$transaction_id,$trade_state);
	                if($result === true){
	                    $price = $total_fee / 100;
	                    $msg = '支付金额：' . $price . '元，充值成功';
	                    $message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><head></html><script>alert("' . $msg . '");</script>';
	                    redirect(U('frontend/financial/member_transactions_list','',false),3,$message);
	                }
	            } else {
	                //当做不成功处理
	                $this->error("支付失败");
	            }
	        }
	    } else {
	        $this->error("认证签名失败，" . $resHandler->getDebugInfo());
	    }
	}
	
	public function notifyurl(){
	    $tenpay_config = $this->web_config;
	    /* 创建支付应答对象 */
	    $resHandler = new \Think\Tenpay\ResponseHandler();
	    $resHandler->setKey(trim($tenpay_config['cftkey']));
	    //判断签名
	    if ($resHandler->isTenpaySign()) {
	        //通知id
	        $notify_id = $resHandler->getParameter("notify_id");
	        //通过通知ID查询，确保通知来至财付通
	        //创建查询请求
	        $queryReq = new \Think\Tenpay\RequestHandler();
	        $queryReq->init();
	        $queryReq->setKey(trim($tenpay_config['cftkey']));
	        $queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
	        $queryReq->setParameter("partner", trim($tenpay_config['partner']));
	        $queryReq->setParameter("notify_id", $notify_id);
	        //通信对象
	        $httpClient = new \Think\Tenpay\client\TenpayHttpClient();
	        $httpClient->setTimeOut(5);
	        //设置请求内容
	        $httpClient->setReqContent($queryReq->getRequestURL());
	        //后台调用
	        if ($httpClient->call()) {
	            //设置结果参数
	            $queryRes = new \Think\Tenpay\client\ClientResponseHandler();
	            $queryRes->setContent($httpClient->getResContent());
	            $queryRes->setKey(trim($tenpay_config['key']));
	            if ($resHandler->getParameter("trade_mode") == "1") {
	                //判断签名及结果（即时到帐）
	                //只有签名正确,retcode为0，trade_state为0才是支付成功
	                if ($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
	                    //通知id
	                    $notify_id = $resHandler->getParameter("notify_id");
	                    //商户订单号
	                    $out_trade_no = $resHandler->getParameter("out_trade_no");
	                    //财付通订单号
	                    $transaction_id = $resHandler->getParameter("transaction_id");
	                    //金额,以分为单位
	                    $total_fee = $resHandler->getParameter("total_fee");
	                    //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
	                    $discount = $resHandler->getParameter("discount");
	                    //支付结果
	                    $trade_state = $resHandler->getParameter("trade_state");
	                    //交易模式,1即时到账
	                    $trade_mode = $resHandler->getParameter("trade_mode");
	                    //支付完成时间
	                    $time_end = $resHandler->getParameter("time_end");
	                    $body = $resHandler->getParameter("subject");
	                    $parameter = array(
	                        "out_trade_no" => $out_trade_no, //商户订单编号；
	                        "trade_no" => $transaction_id, //财付通订单号；
	                        "total_fee" => $total_fee, //交易金额；
	                        "trade_status" => $trade_state, //交易状态
	                        "notify_id" => $notify_id, //通知校验ID。
	                        "notify_time" => $time_end, //通知的发送时间。
	                    );
	                    $result =  $this->recharge_success($out_trade_no,$transaction_id,$trade_state,$body);
	                    if($result === false){
	                        echo "fail";		//请不要修改或删除
	                    }else{
	                        echo "success";
	                    }
	                } else {
	                    echo "fail";
	                }
	            }
	        } else {
	            //通信失败
	            echo "fail";
	            //后台调用通信失败,写日志，方便定位问题
	            echo "<br>call err:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo() . "<br>";
	        }
	    } else {
	        echo "<br/>" . "认证签名失败" . "<br/>";
	        echo $resHandler->getDebugInfo() . "<br>";
	    }
	}
	
	/**
	 * 处理支付成功方法
	 * @date: 2016年12月8日 下午4:40:47
	 * @author: Lyubo
	 * @param: variable
	 * @return:
	 */
	public function recharge_success($out_trade_no,$trade_no,$trade_status){
	    //财付通回调参数没有body所以用了平台自生成的订单做唯一标识
	    $member_online_transactions = M("member_online_transactions");
	    
	    $online_transactions_where["order_id"] = ["eq",$out_trade_no];
	    $online_info = $member_online_transactions->where($online_transactions_where)->find();
	    if (strcmp($online_info['state'],\Common\Data\StateData::STATE_ONLINE_TRAN_ERROR) == 0 ||
	        strcmp($online_info['state'], \Common\Data\StateData::STATE_ONLINE_TRAN_SUCCESS) == 0) {
	            return false;
	    }
	    $remark = '用户-' . $online_info['login_name'] . '-使用财付通在线充值，金额：' . $online_info['account_money'] . '元，充值成功';
	    $online_tran_update_info = array(
	        'state' => \Common\Data\StateData::STATE_ONLINE_TRAN_SUCCESS,
	        'SFT_OrderNo'=>empty($trade_no) ? ' ' : $trade_no ,
	        'account_money' => $online_info['account_money'],
	        'update_time' => current_date(),
	        'note_appended' => $remark
	    );
	    $up_where['order_id'] = ["eq",$out_trade_no];
	    $result = $member_online_transactions->where($up_where)->save($online_tran_update_info);
	    if ($result !== false) {
	        $member = new \Backend\Model\MemberModel();
	        $result_Balance = $member->updateAccountBalance($online_info['user_id'],1,$online_info['account_money'],$remark,1);
	        if($result_Balance !== false){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}
	
	
	
	
	
	
	
	
}