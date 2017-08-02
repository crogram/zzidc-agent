<?php
namespace Common\Aide;

/**
 * -------------------------------------------------------
 * | 与盛付通api同步或操作的类
 * | @时间: 2016年10月24日 下午5:49:06
 * | @version: 1.0
 * -------------------------------------------------------
 */
class ShengpayAide {

	private $shengpay_config = [];
	
	private $web_config = [];
	
	public function __construct(){
		//获取网站的配置信息
		$this->web_config = WebSiteConfig();
		//初始化配置信息
		$this->shengpay_config = $this->initAlipayConfig($this->web_config);
	}
    /**
	 * ----------------------------------------------
	 * | 初始化配置信息
	 * | @时间: 2016年12月8日 上午10:25:08
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function initAlipayConfig($payment_config){
		//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
		//版本名称
	    $date = date('YmdHis');
	    
		$shengpay_config['Name']	=  'B2CPayment';
		
		//版本号
		$shengpay_config['Version'] = 'V4.1.1.1.1';
		
		//字符集
		$shengpay_config['Charset'] = 'UTF-8';
		
		//发送方标识
		$shengpay_config['MsgSender'] = $payment_config['msgsender'];
		
		//商户订单提交时间
		$shengpay_config['OrderTime'] = $date;
		
		//支付成功后客户端浏览器回调地址
		$shengpay_config['PageUrl'] = U('Api/Shengpay/returnurl',[ ],false, true);
		
		//服务端通知发货地址
		$shengpay_config['NotifyUrl'] = U('Api/Shengpay/notifyurl',[ ],false, true);
		
		//商品名称
		$shengpay_config['ProductName'] = $payment_config['site_name']."-".$_SERVER['SERVER_NAME'];
		
		//签名类型
		$shengpay_config['SignType'] = 'MD5';
		
		//商户密钥
		$shengpay_config['sftkey'] = $payment_config['sftkey'];
		
		return $shengpay_config;
	}
	
	/**
	* 提交参数
	* @date: 2017年3月20日 下午4:11:10
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function buySheng($order_id, $fee, $goods_info){
	    header("Content-type:text/html;charset=utf-8");
	    //商户订单号，商户网站订单系统中唯一订单号，必填
	    $out_trade_no = $order_id;
	    
	    //付款金额，必填
	    $total_fee = $fee;
	    
	    //商品描述，可空
	    $body = $goods_info;
	    $arr = explode("-", $body);
	    
	    $login_name = $arr[0];
	    
	    $order_no = $arr[1];
	    
	    $user_id = $arr[2];
	     
	    $total_fee = $arr[3];
	    
	    $Origin = 
	   	    
	   	$this->shengpay_config['Name'] . '|' .
	    
	    $this->shengpay_config['Version'] . '|' .
	    
	    $this->shengpay_config['Charset'] . '|' .
	    
	    $this->shengpay_config['MsgSender'] . '|' .
	    
	    $out_trade_no . '|' .
	    
	    $fee . '|' .
	    
	    $this->shengpay_config['OrderTime'] . '|' .
	    
	    $this->shengpay_config['PageUrl'] . '|' .
	    
	    $this->shengpay_config['NotifyUrl'] . '|' .
	    
	    $this->shengpay_config['ProductName'] . '|' .
	    
	    $login_name.'-'.$user_id . '|' . $this->shengpay_config['SignType'] . '|';
	    
	    $this->shengpay_config['SignMsg'] = strtoupper(md5($Origin . $this->shengpay_config['sftkey']));
	   
	    
	    $parameter = [
	        'Name' =>  $this->shengpay_config['Name'],
	    
	        'Version' => $this->shengpay_config['Version'],
	    
	        'Charset' =>  $this->shengpay_config['Charset'],
	    
	        'MsgSender' => $this->shengpay_config['MsgSender'],
	    
	        'OrderNo' => $out_trade_no,
	    
	        'OrderAmount' => $fee,
	    
	        'OrderTime' => $this->shengpay_config['OrderTime'],
	    
	        'PageUrl' => $this->shengpay_config['PageUrl'],
	    
	        'NotifyUrl' => $this->shengpay_config['NotifyUrl'],
	    
	        'ProductName' => $this->shengpay_config['ProductName'],
	    
	        'Ext1' => $login_name.'-'.$user_id,
	    
	        'SignType' => $this->shengpay_config['SignType'],
	    
	        'SignMsg' => $this->shengpay_config['SignMsg']
	    ] ;
	    $html_text = $this->buildRequestForm($parameter,'post','确认');
	    echo $html_text;
	    
	}
	
	/**
	 * 建立请求，以表单HTML形式构造（默认）
	 * @param $para_temp 请求参数数组
	 * @param $method 提交方式。两个值可选：post、get
	 * @param $button_name 确认按钮显示文字
	 * @return 提交表单HTML文本
	 */
	function buildRequestForm($para_temp, $method, $button_name) {
	
	    $sHtml = "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
	    $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='https://mas.shengpay.com/web-acquire-channel/cashier.htm' method='".$method."'>";
	    while (list ($key, $val) = each ($para_temp)) {
	        $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
	    }
	    //submit按钮控件请不要含有name属性
	    $sHtml = $sHtml."<input type='submit'  value='".$button_name."' style='display:none;'></form>";
	
	    $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
	
	    return $sHtml;
	}
	/**
	 * 判断参数是否被设置（盛付通）
	 * @date: 2017年3月21日 下午4:51:53
	 * @author: Lyubo
	 * @param: $GLOBALS
	 * @return:
	 */
	public function isEmpty($var){
	    if(isset($var)&&$var!=""){
	        return false;
	    }else{
	        return true;
	    }
	}
	/**
	* 盛付通returnurl
	* @date: 2017年3月21日 下午2:18:23
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function returnurl(){
	    $verifyResult = "false";
	    $key =$this->web_config['sftkey'];//密钥
	     $signMessage="";
        $signMessage.=$this->isEmpty($_REQUEST["Name"])?"":$_REQUEST["Name"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["Version"])?"":$_REQUEST["Version"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["Charset"])?"":$_REQUEST["Charset"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TraceNo"])?"":$_REQUEST["TraceNo"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["MsgSender"])?"":$_REQUEST["MsgSender"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["SendTime"])?"":$_REQUEST["SendTime"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["InstCode"])?"":$_REQUEST["InstCode"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["OrderNo"])?"":$_REQUEST["OrderNo"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["OrderAmount"])?"":$_REQUEST["OrderAmount"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransNo"])?"":$_REQUEST["TransNo"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransAmount"])?"":$_REQUEST["TransAmount"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransStatus"])?"":$_REQUEST["TransStatus"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransType"])?"":$_REQUEST["TransType"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransTime"])?"":$_REQUEST["TransTime"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["MerchantNo"])?"":$_REQUEST["MerchantNo"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["ErrorCode"])?"":$_REQUEST["ErrorCode"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["ErrorMsg"])?"":$_REQUEST["ErrorMsg"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["Ext1"])?"":$_REQUEST["Ext1"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["SignType"])?"":$_REQUEST["SignType"]."|";
        $signMessage.=$key;
	    
	    $signMsg= strtoupper(md5($signMessage));
	    $org_signMsg = $_REQUEST["SignMsg"];
	    if (isset($org_signMsg) && strcasecmp($signMsg, $org_signMsg) === 0) {
            $verifyResult = "true";
        }
        $SignMsgMerchant= $signMsg;
        if (isset($verifyResult)&&strcasecmp($verifyResult, "true")===0)
        {
            $transStatus=$_REQUEST["TransStatus"];
            if (isset($transStatus)&&strcasecmp(trim($transStatus), "01")===0)
            {
                $body = $_REQUEST["Ext1"].'-'.$_REQUEST["OrderAmount"];
                $result = $this->recharge_success($_REQUEST["OrderNo"],$_REQUEST["TransNo"],$transStatus,$body);
			    if($result === true){
			        $msg = '支付金额：' . $_REQUEST["OrderAmount"] . '元，充值成功';
			        $message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><head></html><script>alert("' . $msg . '");</script>';
			        redirect(U('frontend/financial/member_transactions_list','',false),3,$message);
			    }
            }
        }
	}
	/**
	* 盛付通notifyurl
	* @date: 2017年3月21日 下午3:28:34
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function notifyurl(){
	    $key =$this->web_config['sftkey'];//密钥
	   $signMessage="";
        $signMessage.=$this->isEmpty($_REQUEST["Name"])?"":$_REQUEST["Name"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["Version"])?"":$_REQUEST["Version"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["Charset"])?"":$_REQUEST["Charset"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TraceNo"])?"":$_REQUEST["TraceNo"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["MsgSender"])?"":$_REQUEST["MsgSender"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["SendTime"])?"":$_REQUEST["SendTime"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["InstCode"])?"":$_REQUEST["InstCode"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["OrderNo"])?"":$_REQUEST["OrderNo"]."|"; //商户订单号
        $signMessage.=$this->isEmpty($_REQUEST["OrderAmount"])?"":$_REQUEST["OrderAmount"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransNo"])?"":$_REQUEST["TransNo"]."|";//盛付通订单号
        $signMessage.=$this->isEmpty($_REQUEST["TransAmount"])?"":$_REQUEST["TransAmount"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransStatus"])?"":$_REQUEST["TransStatus"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransType"])?"":$_REQUEST["TransType"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["TransTime"])?"":$_REQUEST["TransTime"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["MerchantNo"])?"":$_REQUEST["MerchantNo"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["ErrorCode"])?"":$_REQUEST["ErrorCode"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["ErrorMsg"])?"":$_REQUEST["ErrorMsg"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["Ext1"])?"":$_REQUEST["Ext1"]."|";
        $signMessage.=$this->isEmpty($_REQUEST["SignType"])?"":$_REQUEST["SignType"]."|";
        $signMessage.=$key;
	    $signMsg= strtoupper(md5($signMessage));
	    $SignMsgMerchant = $_REQUEST["SignMsg"];
	    if (isset($SignMsgMerchant) && strcasecmp($signMsg, $SignMsgMerchant) === 0) {
            // 处理自己的业务逻辑
	        $info = explode("-", $_REQUEST["Ext1"]);
	        $login_name = $info['0'];
	        $user_id = $info['1'];
	        $transStatus=$_REQUEST["TransStatus"];
	        $body = $login_name.'-'.$user_id.'-'.$_REQUEST["OrderAmount"].'-'.$_REQUEST["SignType"].'-'.$_REQUEST["TransNo"];
	        $result = $this->recharge_success($_REQUEST["OrderNo"],$_REQUEST["TransNo"],$transStatus,$body);
	        if($result === false){
	            echo "Fail";		
	        }else{
	            echo "OK";
	        }
        }
	}
	
	
	/**
	 * 处理支付成功方法
	 * @date: 2016年12月8日 下午4:40:47
	 * @author: Lyubo
	 * @param: variable
	 * @return:
	 */
	public function recharge_success($out_trade_no,$trade_no,$trade_status,$body){
	    $arr = explode("-", $body);
	    $login_name = $arr[0];
	    $user_id = $arr[1];
	    $total_fee = $arr[2];
	    $sign_type = $arr[3];
	    $transNo = $arr[4];
	    //先查询盛付通订单号是否存在，存在直接返回true
	    $member_online_transactions = M("member_online_transactions");
	    $online_transactions_where["SFT_OrderNo"] = ["eq",$trade_no];
	    $online_info = $member_online_transactions->where($online_transactions_where)->find();
	    if(!empty($online_info)){
	        return true;
	    }
	    //查询商户订单号不成功的
	    $online_parms_where['order_id'] = ['eq',$out_trade_no];
	    $online_parms_where['user_id'] = ['eq',$user_id];
	    $online_parms_where['state'] = ['neq',\Common\Data\StateData::STATE_ONLINE_TRAN_SUCCESS];
	    $online_parms = $member_online_transactions->where($online_parms_where)->find();
	    $remark = '用户-' . $login_name . '-使用盛付通在线充值，金额：' . $online_parms['account_money'] . '元，充值成功';
	    $online_tran_update_info = array(
	        'state' => \Common\Data\StateData::STATE_ONLINE_TRAN_SUCCESS,
	        'SFT_OrderNo'=>empty($trade_no) ? ' ' : $trade_no ,
	        'SFT_SignType'=>empty($sign_type) ? ' ' : $sign_type ,
	        'SFT_TransNo'=>empty($transNo) ? ' ' : $transNo ,
	        'account_money' => $online_parms['account_money'],
	        'update_time' => current_date(),
	        'note_appended' => $remark
	    );
	    $up_where['order_id'] = ["eq",$out_trade_no];
	    $up_where['user_id'] = ["eq",$online_parms['user_id']];
	    $result = $member_online_transactions->where($up_where)->save($online_tran_update_info);
	    if ($result !== false) {
	        $member = new \Backend\Model\MemberModel();
	        $result_Balance = $member->updateAccountBalance($online_parms['user_id'],1,$online_parms['account_money'],$remark,1);
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