<?php
namespace Common\Aide;

// use \Alipay\AlipayNotify;
// use \Alipay\AlipaySubmit;
require_cache('./vendor/alipay/lib/alipay_core.function.php');
require_cache('./vendor/alipay/lib/alipay_md5.function.php');
require_cache('./vendor/alipay/lib/AlipayNotify.class.php');
require_cache('./vendor/alipay/lib/AlipaySubmit.class.php');

/**
 * -------------------------------------------------------
 * | 与主站api同步或操作的类
 * | @author: duanbin
 * | @时间: 2016年10月24日 下午5:49:06
 * | @version: 1.0
 * -------------------------------------------------------
 */
class AlipayAide {

	private $notify = null;
	private $submit = null;

	private $alipay_config = [];
	
	private $web_config = [];
	
	public function __construct(){
		//获取网站的配置信息
		$this->web_config = WebSiteConfig();
		
		//初始化支付宝配置信息
		$alipay_config = $this->initAlipayConfig($this->web_config);

		$this->notify = new \Alipay\lib\AlipayNotify($alipay_config);
		$this->submit = new \Alipay\lib\AlipaySubmit($alipay_config);
	}
	
	/**
	 * ----------------------------------------------
	 * | 初始化配置信息
	 * | @时间: 2016年12月8日 上午10:25:08
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function initAlipayConfig($payment_config){
		//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
		//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
		$alipay_config['partner']		= $payment_config['alipay_partner'];
		
		//收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
		$alipay_config['seller_id']	= $alipay_config['partner'];
		
		// MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
		$alipay_config['key']			= $payment_config['alipay_key'];
		
		// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
		$alipay_config['notify_url'] = U('Api/Alipay/notifyurl',[ ],false, true);
		
		// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
		$alipay_config['return_url'] = U('Api/Alipay/returnurl',[ ],false, true);
		
		//签名方式
		$alipay_config['sign_type']    = strtoupper('MD5');
		
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('utf-8');
		
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert']    = getcwd().DS.'vendor'.DS.'alipay'.DS.'cacert.pem';
		
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = isHTTPS() === true ? 'https': 'http';
		
		// 支付类型 ，无需修改
		$alipay_config['payment_type'] = "1";
				
		// 产品类型，无需修改
		$alipay_config['service'] = $payment_config['site_pay_methods'];
		
		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		
		
		//↓↓↓↓↓↓↓↓↓↓ 请在这里配置防钓鱼信息，如果没开通防钓鱼功能，为空即可 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
			
		// 防钓鱼时间戳  若要使用请调用类文件submit中的query_timestamp函数
		$alipay_config['anti_phishing_key'] = "";
			
		// 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1
		$alipay_config['exter_invoke_ip'] = "";
		
		//订单名称
		$alipay_config['subject'] = $payment_config['site_name']."-".$_SERVER['SERVER_NAME'];
				
		//↑↑↑↑↑↑↑↑↑↑请在这里配置防钓鱼信息，如果没开通防钓鱼功能，为空即可 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		
		$this->alipay_config = $alipay_config;
		
		return $alipay_config;
	}
	
	/**
	 * ----------------------------------------------
	 * | 调用支付宝接口消费
	 * | @时间: 2016年12月8日 上午10:50:21
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function buySomething($order_id, $fee, $goods_info){
	    header("Content-type:text/html;charset=utf-8");
		//商户订单号，商户网站订单系统中唯一订单号，必填
		$out_trade_no = $order_id;
		
		
		//付款金额，必填
		$total_fee = $fee;
		
		//商品描述，可空
		$body = $goods_info;
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service"       => $this->alipay_config['service'],
				"partner"       => $this->alipay_config['partner'],
				"seller_id"  => $this->alipay_config['seller_id'],
				"payment_type"	=> $this->alipay_config['payment_type'],
				"notify_url"	=> $this->alipay_config['notify_url'],
				"return_url"	=> $this->alipay_config['return_url'],
				"anti_phishing_key"=>$this->alipay_config['anti_phishing_key'],
				"exter_invoke_ip"=>$this->alipay_config['exter_invoke_ip'],
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $this->alipay_config['subject'],
				"total_fee"	=> $total_fee,
				"body"	=> $body,
				"_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
				//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
				//如"参数名"=>"参数值"
		
		);
		
		//建立请求
		$html_text = $this->submit->buildRequestForm($parameter,"get", "确认");
		echo $html_text;
		
	}
	/**
	 * ----------------------------------------------
	 * | 支付宝异步通知回调接口
	 * | @时间: 2016年12月8日 上午11:01:01
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function notifyurl($callback = ''){
		$verify_result = $this->notify->verifyNotify();
		
		if($verify_result) {//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代

			if (is_callable($callback)) {
				$callback($_POST);
			}
		
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
		
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
		
			//商户订单号
		
			$out_trade_no = $_POST['out_trade_no'];
		
			//支付宝交易号
		
			$trade_no = $_POST['trade_no'];
		
			//交易状态
			$trade_status = $_POST['trade_status'];
		      
			//交易金额
			$total_fee = $_POST['total_fee'];
			
			//交易参数
			$body = $_POST['body'];
		
			if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
				//如果有做过处理，不执行商户的业务程序
		
				//注意：
				//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
		
				//调试用，写文本函数记录程序运行情况是否正常
				//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			}
			else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
				//如果有做过处理，不执行商户的业务程序
		
				//注意：
				//付款完成后，支付宝系统发送该交易状态通知
		
				//调试用，写文本函数记录程序运行情况是否正常
				//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			    $result = $this->recharge_success($out_trade_no,$trade_no,$trade_status,$body);
			    if($result === false){
			        echo "fail";		//请不要修改或删除
			    }
			}
		
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		
			echo "success";		//请不要修改或删除
		
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
			//验证失败
			echo "fail";
		
			//调试用，写文本函数记录程序运行情况是否正常
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
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
	     
	    $order_no = $arr[1];
	     
	    $user_id = $arr[2];
	    
	    $total_fee = $arr[3];
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
	    $remark = '用户-' . $login_name . '-使用支付宝在线充值，金额：' . $online_parms['account_money'] . '元，充值成功';
	    $online_tran_update_info = array(
	        'state' => \Common\Data\StateData::STATE_ONLINE_TRAN_SUCCESS,
	        'SFT_OrderNo'=>empty($trade_no) ? ' ' : $trade_no ,
	        'account_money' => $online_parms['account_money'],
	        'update_time' => current_date(),
	        'note_appended' => $remark
	    );
	    $up_where['order_id'] = ["eq",$out_trade_no];
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
	/**
	 * ----------------------------------------------
	 * | 支付宝页面跳转通知回调接口
	 * | @时间: 2016年12月8日 上午11:01:18
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function returnurl($callback = ''){
		$verify_result = $this->notify->verifyReturn();
		if($verify_result) {//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代码
			
			if (is_callable($callback)) {
				$callback($_POST);
			}
		
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
		
			//商户订单号
		
			$out_trade_no = $_GET['out_trade_no'];
		
			//支付宝交易号
		
			$trade_no = $_GET['trade_no'];
		
			//交易状态
			$trade_status = $_GET['trade_status'];
		      
			//交易金额
			$total_fee = $_GET['total_fee'];
			
			//交易参数
			$body = $_GET['body'];
		
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//如果有做过处理，不执行商户的业务程序
			    $result = $this->recharge_success($out_trade_no,$trade_no,$trade_status,$body);
			    if($result === true){
			        $msg = '支付金额：' . $total_fee . '元，充值成功';
			        $message = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><head></html><script>alert("' . $msg . '");</script>';
			        redirect(U('frontend/financial/member_transactions_list','',false),3,$message);
			    }
			}
			else {
				echo "trade_status=".$_GET['trade_status'];
			}
		
			echo "验证成功<br />";
		
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
			//验证失败
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			echo "验证失败";
		}
	}
	
	
	public function __get($name){
		if (isset($this->$name)){
			return $this->$name;
		}else {
			return null;
		}
	}
	
	
	
	
	
	
	
	
}