<?php
/**
 * ---------------------------------------------------
 * |某些函数类采用了composer自动加载
 * |@时间: 2016年9月28日 下午5:24:19
 * ---------------------------------------------------
 */

/**
 * 加密解密函数
 * @param  [type] $string    [description]
 * @param  [type] $operation [description]
 * @param  string $key       [description]
 * @return [type]            [description]
 */
function encrypt($string,$operation,$key=''){
	$key=md5($key);
	$key_length=strlen($key);
	$string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
	$string_length=strlen($string);
	$rndkey=$box=array();
	$result='';
	for($i=0;$i<=255;$i++){
		$rndkey[$i]=ord($key[$i%$key_length]);
		$box[$i]=$i;
	}
	for($j=$i=0;$i<256;$i++){
		$j=($j+$box[$i]+$rndkey[$i])%256;
		$tmp=$box[$i];
		$box[$i]=$box[$j];
		$box[$j]=$tmp;
	}
	for($a=$j=$i=0;$i<$string_length;$i++){
		$a=($a+1)%256;
		$j=($j+$box[$a])%256;
		$tmp=$box[$a];
		$box[$a]=$box[$j];
		$box[$j]=$tmp;
		$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
	}
	if($operation=='D'){
		if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
			return substr($result,8);
		}else{
			return'';
		}
	}else{
		return str_replace('=','',base64_encode($result));
	}
}

/**
 * ----------------------------------------------
 * | 清楚xss脚步攻击
 * |@时间: 2016年9月28日 下午5:23:18
 * |@author: duanbin
 * |@params: $html 要过滤的文本
 * |@return: 过滤后的文本
 * ----------------------------------------------
 */
function clearXSS($html){
	// 生成配置对象
	$cleaner_config = HTMLPurifier_Config::createDefault();
	// 以下就是配置：
	$cleaner_config->set('Core.Encoding', 'UTF-8');
	// 设置允许使用的HTML标签
	$cleaner_config->set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
	// 设置允许出现的CSS样式属性
	$cleaner_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
	// 设置a标签上是否允许使用target="_blank"
	$cleaner_config->set('HTML.TargetBlank', TRUE);
	// 使用配置生成过滤用的对象
	$cleaner = new HTMLPurifier($cleaner_config);
	// 过滤字符串
	return $cleaner->purify($html);	
}

/**
 * ----------------------------------------------
 * |判断 是否是移动端
 * |@时间: 2016年9月28日 下午5:14:56
 * |@author: duanbin
 * |@params: 
 * |@return: pc(电脑) or phone(手机) or tablet(平板)
 * ----------------------------------------------
 */
function isMobile(){
	$detect = new Mobile_Detect;
	return $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'pc');
}

/**
 * ----------------------------------------------
 * | 判断密码是否够安全
 * | @时间: 2016年11月14日 下午3:37:07
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: true or false
 * ----------------------------------------------
 */
function passwordIsStrong($password){
	$reg = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[a-zA-Z\d]{8,20}$/';
	return preg_match_all($reg, $password, $matchs);
}

/**
 * ----------------------------------------------
 * | 前台生成密码方法
 * | @时间: 2016年10月24日 16:40:46
 * | @author: Lyubo
 * | @param: $user_name
 * | @param: $user_pwd
 * | @return: string
 * ----------------------------------------------
 */
function pwd($user_name, $user_pwd) {
	$str = md5 ( md5 ( $user_name . $user_pwd ) );
	return $str;
}

/**
 * ----------------------------------------------
 * | 后台管理员的密码加密
 * | @时间: 2016年10月9日 上午11:54:07
 * | @author: duanbin
 * | @param: $password
 * | @return: type
 * ----------------------------------------------
 */
function md5Password($password){
	return md5( $password.C('APP_KEY') );
}


/**
 * ----------------------------------------------
 * | 获取所有的请求参数
 * | @时间: 2016年10月11日 上午9:48:25
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function request( $rowable = false ){
	$get = I('get.', '');
	$post = I('post.', '');
	$request = array_merge_recursive($get, $post);
	unset($request['/'.__INFO__]);

	if (empty($request)) {
		return [];
	}
	if (!$rowable){
		array_walk_recursive($request, function (&$v, $k){
			if (is_numeric($v)) {
				$v += 0;
			}elseif (is_string($v)){
				$v = clearXSS($v);
			}
		});
	}
	return $request;
}


/**
 * ----------------------------------------------
 * | 判断小数位数
 * | @时间: 2016年10月17日 上午11:46:05
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function isDecimalDigitN( $float, $n = 2 ){
	
	if (preg_match('/^[0-9]+(.[0-9]{1,'.$n.'})?$/', $float)) {
		return true;
	}else{
		return false;
	}
	
}


/**
 * ----------------------------------------------
 * | 获取小数点后的位数
 * | @时间: 2016年10月17日 上午11:54:45
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function getDecimalDigit( $float ){

	if (strpos($float, '.')){
		$temp = explode('.', $float);
		return strlen($temp[1]);
	}else {
		return false;
	}

}


/**
 * ----------------------------------------------
 * | 发送邮件的方法
 * | @时间: 2016年10月25日 下午3:50:05
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function postOffice($to, $subject, $content,$type){
        require './ThinkPHP/Library/Org/Net/class.phpmailer.php';
        require './ThinkPHP/Library/Org/Net/class.smtp.php';
        /**
         * ---------------------------------------------------
         * | 获取发送邮件的配置项
         * | @时间: 2016年10月25日 下午3:56:34
         * ---------------------------------------------------
         */
        $config = WebSiteConfig(13);
    	//参数验证
    	if (empty($to)){
    		return ["status"=>1];
    	}
	    $mail = new \PHPMailer (); // 建立邮件发送类
	    $mail->IsSMTP (); // 使用SMTP方式发送
	    $mail->CharSet = "UTF-8";
	    $mail->Host = $config ['smtpaddress']; // 您的企业邮局域名
	    $mail->SMTPAuth = true; // 启用SMTP验证功能
	    $mail->Username = $config ['mailaddress']; // 邮局用户名(请填写完整的email地址)
	    $mail->Password = $config ['mailpassword']; // 邮局密码
	    $mail->Port = $config ['portaddress'];
	    $mail->From = $config ['mailaddress']; // 邮件发送者email地址
	    $mail->AddReplyTo("$config ['mailaddress']","管理员");//回复地址
	    $mail->IsHTML(true);
	    $mail->FromName = $config['site_name']."-管理员";
	    $mail->AddAddress($to);
	    if($type != '9' || $type == null){//如果是找回密码则不抄送邮件
	    $mail->AddAddress($config['mailaddcc']);
	    }
        // 设置邮件标题
        $mail->Subject=$subject;
        // 设置邮件正文
        $mail->Body=$content;
        // 发送邮件。
        if(!$mail->Send()) {
            $phpmailererror=$mail->ErrorInfo;
            return ["status"=>1,"message"=>$phpmailererror];
        }else{
            return ["status"=>0];
        }
	
}
/**
 * ----------------------------------------------
 * | 发送邮件的方法
 * | @时间: 2016年10月25日 下午3:50:05
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function OvBusiness_postOffice($to, $subject, $content){
    /**
     * ---------------------------------------------------
     * | 获取发送邮件的配置项
     * | @时间: 2016年10月25日 下午3:56:34
     * ---------------------------------------------------
     */
    $config = WebSiteConfig(13);
    /**
     * ---------------------------------------------------
     * | swift配置项
     * | @时间: 2016年10月25日 下午4:09:52
     * ---------------------------------------------------
    */
    $transport = \Swift_SmtpTransport::newInstance($config['smtpaddress'], $config['portaddress'])
    ->setUsername($config['mailaddress'])
    ->setPassword($config['mailpassword']);
    /**
     * ---------------------------------------------------
     * | 发送邮件
     * | @时间: 2016年10月25日 下午4:10:08
     * ---------------------------------------------------
    */
    $mailer  = \Swift_Mailer::newInstance($transport);
    $message = \Swift_Message::newInstance()
    ->setSubject($subject)
    ->setFrom($config['mailaddress'])
    ->setTo($to)
    ->setBody($content, 'text/html', 'utf-8');

    return $mailer->send($message);

}
/**
 * ----------------------------------------------
 * | 生成邮件内容的方法，
 * | $type == 1--->业务到期提醒
 * | $type == 2--->业务过期提醒
 * | $type == 3--->试用业务审核通过提醒
 * | $type == 4--->试用业务提醒
 * | $type == 5--->修改密码(5,'',$user_info)--->$user_info['login_name'],$user_info['user_pass'],
 * | $type == 6--->注册 (6,'',$user_info)--->$user_info['login_name']
 * | $type == 7--->续费(7,'',$user_info)
 * | $type == 8--->测试邮件(8,'',$user_info)
 * | $type == 9--->找回密码(9,'',$user_info)
 * | $type == 10--->主机业务开通(10,'',$user_info)
 * | $type == 11--->VPS业务开通(11,'',$user_info)
 * | @时间: 2016年10月26日 上午9:31:29
 * | @author: duanbin
 * | @return: $content['subject'] $content['body']
 * ----------------------------------------------
 */
function HTMLContentForEmail($type,$business_info,$user_info){
	
	$site_config = WebSiteConfig();
	$content = [];
	$content['subject'] = $business_info ['business_name'];
	$content['body'] = "尊敬的用户" .$user_info ['user_name'];
	//业务到期提醒的邮件模板；
	if ($type == 1){
		$content['subject'] .= "业务到期提醒"; // 邮件标题
		$content['body'] .= "：<br/>您在我公司业务标识为[" . 
					$business_info ['ip_address'] . "]的[" . 
					$business_info ['business_name'] . "]业务将于" . 
					$business_info ['overdue_time'] . "到期,为了不对您的业务造成不便，请您在" . 
					$business_info ['overdue_time'] . "之前对您的业务续费。感谢您的使用与支持！<br/>";

		//业务过期提醒的邮件模板
	}elseif ($type == 2){
		$content['subject'] .= "业务过期提醒"; // 邮件标题
		$content['body'] .="：<br/>您在我公司业务标识为[" . 
		$business_info ['ip_address'] . "]的[" . 
		$business_info ['business_name'] . "]业务于". 
		$business_info ['overdue_time'] . "已过期,为了不对您的业务造成不便，请您对您的业务续费。不续费七天之后会自动清除业务。感谢您的使用与支持！<br/>";
		
		//业务审核提醒
	}elseif ($type == 3){
		$content['subject'] .= "业务审核提醒"; // 邮件标题
		$content['body'] .="：<br/>您在我公司订单业务为[" . 
		$business_info ['business_name'] . "]的业务审核通过";
		
		//业务试用提醒
	}elseif ($type == 4){
		$content['subject'] .= "业务试用提醒"; // 邮件标题
		if(strcmp($business_info['api_ptype'], GiantAPIParams::PTYPE_HOST) == 0 || strcmp($business_info['api_ptype'], GiantAPIParams::PTYPE_HK_HOST) == 0 || strcmp($business_info['api_ptype'], GiantAPIParams::PTYPE_DEDE_HOST) == 0 || strcmp($business_info['api_ptype'], GiantAPIParams::PTYPE_USA_HOST) == 0 || strcmp($business_info['api_ptype'], GiantAPIParams::PTYPE_CLOUD_VIRTUAL) == 0){
			if ($site_config['site_reviewed'] == 'no') {
				$content['body'] .="：<br/>您在我公司的业务试用,请到网站订单列表进行开通使用！";
			}
		}else{
			$content['body'] .="：<br/>您在我公司的业务试用申请,审核成功后会以邮件方式提醒。请注意查收！";
		}
		
		//修改密码通知
	}elseif ($type == 5){
		$content['subject'] = '密码修改'; // 邮件标题
		$content['body'] = "尊敬的用户" . $user_info ['login_name'] . "：<br/>您的密码重置为：".$user_info['user_pass']."请尽快登录重置密码，以免出现不必要的损失。祝您生活愉快！";// 邮件内容
	
		//注册成功通知
	}elseif ($type == 6){
		$content['subject'] = "感谢您加入"; // 邮件标题
		$content['body'] ="尊敬的用户" . $user_info ['login_name'] . "：<br/>" . "欢迎您加入".$site_config ['site_name'];

		//消费信息通知
	}elseif($type == 7){
		$content['subject'] .= "消费信息通知"; // 邮件标题
		$content['body'] = "尊敬的用户".$user_info ['user_name']."您好：<br/>".
			"您在我公司的网站:"."<a href=".$_SERVER['HTTP_HOST'].">".$_SERVER['HTTP_HOST']."</a>".
			"    消费项目: ".$business_info['product_name']."(".$business_info['product_des'].")<br>".
			"    消费类型: ".$business_info['acct_type']."<br>".
			"    本次消费：".$business_info['totalprice']." 元<br>".
			"    扣费后余额：".$business_info['balance']." 元<br>";
		
		//测试邮件
	}elseif($type == 8){
	    $content['subject'] .= "测试邮件通知"; // 邮件标题
	    $content['body'] = "This is a test mail！";
	    return $content;
	    
	    //修改密码邮件
	}elseif ($type == 9){
	    $content['subject'] .= "密码修改"; // 邮件标题
	    $content['body'] = "尊敬的用户".$user_info ['user_name']."您好：<br/>".
	   	"您的密码已重置为:<span style='color:red'>".$user_info['password']."</span>请尽快登录重置密码，以免出现不必要的损失。祝您生活愉快！";
	    
	    //虚拟主机开通邮件
	}elseif ($type == 10){
	    $content['subject'] .= $business_info['product_name']."业务开通通知"; // 邮件标题
	    $content['body'] = "尊敬的用户".$user_info ['user_name']."您好：<br/>".
	        "您在我公司的网站:"."<a href=".$_SERVER['HTTP_HOST'].">".$_SERVER['HTTP_HOST']."</a>".
	        "    订单编号为: ".$business_info['order_id']."<br>".
	        "    FTP链接地址: ".$business_info['ftp_address']."<br>".
	        "    FTP帐户：webmaster@".$business_info['domain_name']."<br>".
	        "    FTP密码：".$business_info['ftp_password']."<br>".
	        "    开通时间：".$business_info['open_time']." <br>".
	        "    到期时间：".$business_info['overdue_time']." <br>";
	    
	    //VPS、快云VPS开通邮件
	}elseif ($type == 11){
	    $content['subject'] .= $business_info['product_name']."业务开通通知"; // 邮件标题
	    $content['body'] = "尊敬的用户".$user_info ['user_name']."您好：<br/>".
	        "您在我公司的网站:"."<a href=".$_SERVER['HTTP_HOST'].">".$_SERVER['HTTP_HOST']."</a>".
	        "    订单编号为: ".$business_info['order_id']."<br>".
	        "    独享 IP: ".$business_info['ip_address']."<br>".
	        "    初始登录用户名：".$business_info['system_user']."<br>".
	        "    初始登录密码：".$business_info['system_password']."<br>".
	        "    远程桌面端口号：".$business_info['remoteport']."<br>".
	        "    开通时间：".$business_info['createDate']." <br>".
	        "    到期时间：".$business_info['overDate']." <br>";
	}
	
	
	    $content['body'] .=  "<br />".
	   	date('Y-m-d H:i:s').
	   	"<br/>--------------------------<br/>".
		$site_config ['site_name']."<br/>".
		"电子邮箱：".$site_config ['site_mail']."<br/>".
		"网站地址：".$_SERVER[HTTP_HOST]."<br/>".
		"邮政编码：".$site_config['site_post_code'].
		"<br/>电话/传真：".$site_config['site_telphone']."/".$site_config['site_fax'].
		"<br/>QQ:".$site_config['site_qq']; // 邮件内容
	
	return $content;
}


/**
 * 验证身份证合法性
 * @param  [type] $idcard [description]
 * @return [type]         [description]
 */
function checkIdCard($idcard){

	$City = array(11=>"北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",21=>"辽宁",22=>"吉林",23=>"黑龙江",31=>"上海",32=>"江苏",33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",41=>"河南",42=>"湖北",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",71=>"台湾",81=>"香港",82=>"澳门",91=>"国外");
	$iSum = 0;
	$idCardLength = strlen($idcard);
	//长度验证
	if(!preg_match('/^\d{17}(\d|x)$/i',$idcard) and!preg_match('/^\d{15}$/i',$idcard))
	{
		return false;
	}
	//地区验证
	if(!array_key_exists(intval(substr($idcard,0,2)),$City))
	{
		return false;
	}
	// 15位身份证验证生日，转换为18位
	if ($idCardLength == 15)
	{
		$sBirthday = '19'.substr($idcard,6,2).'-'.substr($idcard,8,2).'-'.substr($idcard,10,2);
		$d = new DateTime($sBirthday);
		$dd = $d->format('Y-m-d');
		if($sBirthday != $dd)
		{
			return false;
		}
		$idcard = substr($idcard,0,6)."19".substr($idcard,6,9);//15to18
		$Bit18 = getVerifyBit($idcard);//算出第18位校验码
		$idcard = $idcard.$Bit18;
	}
	// 判断是否大于2078年，小于1900年
	$year = substr($idcard,6,4);
	if ($year<1900 || $year>2078 )
	{
		return false;
	}

	//18位身份证处理
	$sBirthday = substr($idcard,6,4).'-'.substr($idcard,10,2).'-'.substr($idcard,12,2);
	$d = new DateTime($sBirthday);
	$dd = $d->format('Y-m-d');
	if($sBirthday != $dd)
	{
		return false;
	}
	//身份证编码规范验证
	$idcard_base = substr($idcard,0,17);
	if(strtoupper(substr($idcard,17,1)) != getVerifyBit($idcard_base))
	{
		return false;
	}
	return true;
}


/**
 * 计算身份证校验码，根据国家标准GB 11643-1999
 * 跟上面是一套的，被上面的函数（checkIdCard）调用
 * @param  [type] $idcard_base [description]
 * @return [type]              [description]
 */
function getVerifyBit($idcard_base)
{
	if(strlen($idcard_base) != 17)
	{
		return false;
	}
	//加权因子
	$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	//校验码对应值
	$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4','3', '2');
	$checksum = 0;
	for ($i = 0; $i < strlen($idcard_base); $i++)
	{
		$checksum += substr($idcard_base, $i, 1) * $factor[$i];
	}
	$mod = $checksum % 11;
	$verify_number = $verify_number_list[$mod];
	return $verify_number;
}



/**
 * 验证手机号码是否合法
 * @param  [type]  $tel [description]
 * @return boolean      [description]
 */
function is_tel($tel){

	//先判断是手机号还是固话，以0开头的就是固话，以1开头的就是手机号
	$tel_str = (string)$tel;
	if (stripos($tel_str, '0') === 0 || stripos($tel_str, '0') === '0' || stripos($tel_str, '4') === 0 || stripos($tel_str, '8') === '0') {
		//说明是固话
		$reg = "/(^(0|4|8)\d{2}-?\d{8}$)|(^(0|4|8)\d{2}-?\d{7}$)|(^(0|4|8)\d{3}-?\d{7}$)|(^(0|4|8)\d{3}-?\d{8}$)/";
	}else{
		//说明是手机号
		$reg = "/^1[3|4|7|5|8][0-9]\d{8}$/";
	}

	preg_match_all($reg, $tel, $matches);

	return empty($matches[0][0])?false:true;

	//验证固话合法性的正则（带 - 的）
	//[0\d{2}-\d{8}|0\d{2}-\d{7}|0\d{3}-\d{7}|0\d{3}-\d{8}]

	//验证手机号码的正确性的正则(11位)
	//^0?1[3|4|7|5|8][0-9]\d{8}$
}

/**
 * ----------------------------------------------
 * | 邮编验证
 * | @时间: 2016年10月24日 下午5:53:48
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function postalCodesValidator($postCode){
	preg_match_all("/^\d{6}$/", $postCode, $matches);
	
	return empty($matches[0][0])?false:true;
}

/**
 * ----------------------------------------------
 * | 获取网站的配置信息，(源数据)从数据库里读出
 * | 加有缓存项
 * | 默认获取站点信息
 * | @时间: 2016年10月24日 下午5:54:55
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function WebSiteConfig($type = 1){
	
	$prefix = C('DB_PREFIX').$type;
	static $sys_config = null;
	$sys_config = S($prefix.'sys_config');
	if (empty($sys_config)) {
		$cache = S(array('type'=>'File','prefix'=>$prefix,'expire'=>0));
		$sys_config = M('sys')->where([ 'sys_type' => [ 'eq' , $type+0] ])->select();
		$sys_config = rows_to_column($sys_config);
		$cache->sys_config = $sys_config;
	}

	return $sys_config;
	
}

/**
 * ----------------------------------------------
 * | 矩阵(数组)，行列互换
 * | @时间: 2016年10月24日 下午6:06:37
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function rows_to_column($result) {
	$arr = array ();
	// 行转换为列
	while ( list ( $key, $val ) = each ( $result ) ) {
		$arr [$val ["sys_key"]] = $val ["sys_value"];
	}
	return $arr;
}



/**
 * 生成订单号
 *
 * @return string
 */
function create_order_no() {
	$year_code = array (
				
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z'
	);
	$order_sn = $year_code [intval ( date ( 'Y' ) ) - 2013] . strtoupper ( dechex ( date (

			'm' ) ) ) . date ( 'd' ) . substr ( time (), - 5 ) . substr ( microtime (), 2, 5 ) . sprintf (

					'%02d', rand ( 0, 99 ) );
	return $order_sn;
}



/* 检查英文域名是否符合条件 */
function checkEnDomainName($domainName) {
	if (empty ( $domainName )) {
		return false;
	}
	$arrayOfStrings = explode ( ".", $domainName );
	if (count ( $arrayOfStrings ) == 1) {
		for($i = 0; $i < count ( arrayOfStrings ); $i ++) {
			$str = $arrayOfStrings [$i];
			if (strlen ( str ) == 0) {
				return false;
			}
			$re = '/[^a-zA-Z0-9\-]+/';
			if (preg_match ( $re, $str )) {
				return false;
			}
			$re = '/^\-/';
			if (preg_match ( $re, $str )) {
				return false;
			}
			$re = '/\-$/';
			if (preg_match ( $re, $str )) {
				return false;
			}
		}
		return true;
	}
	return false;
}

/**
 * 检查中文域名
 *
 * @param
 *        	$domainName
 */
function checkCnDomainName($domainName) {
	$str = $domainName;
	// must not exceed 20 chars
	if (strlen ( $str ) == 0 || strlen ( $str ) > 20) {
		return false;
	}
	// with only leagal chars
	$re = '/^[a-zA-Z0-9\-\x{4e00}-\x{9fa5}]+$/u';
	if (! preg_match ( $re, $str )) {
		return false;
	}
	// must with a chinese char at least
	$re = '/^[a-zA-Z0-9\-]+$/';
	if (preg_match ( $re, $str )) {
		return false;
	}
	// must not start with '-'
	$re = '/^\-/';
	if (preg_match ( $re, $str )) {
		return false;
	}
	// must not end with '-'
	$re = '/\-$/';
	if (preg_match ( $re, $str )) {
		return false;
	}
	return true;
}

/**
 * 验证状态是否错误
 *
 * @param
 *        	$state
 */
function match_state($state) {
	
	switch ($state) {
		case \Common\Data\StateData::SUCCESS :
			break;
		case \Common\Data\StateData::DELETE :
			break;
		case \Common\Data\StateData::FAILURE :
			break;
		case \Common\Data\StateData::OVERDUE :
			break;
		default :
			return false;
	}
	return true;
}


/**
 * 执行业务返回code
 */
function business_code($code) {
	$message = "";
	switch ($code) {
		case 0 :
			$message = "失败,请联系管理员";
			break;
		case - 1 :
			$message = "成功";
			break;
		case - 2 :
			$message = "产品信息获取失败";
			break;
		case - 3 :
			$message = "会员信息获取失败";
			break;
		case - 4 :
			$message = "会员账号信息获取失败";
			break;
		case - 5 :
			$message = "账号余额不足,请前往充值";
			break;
		case - 6 :
			$message = "会员账户异常";
			break;
		case - 7 :
			$message = "交易成功，订单表修改失败";
			break;
		case - 8 :
			$message = "交易成功，交易记录生成失败";
			break;
		case - 9 :
			$message = "接口调用失败，请联系客服";
			break;
		case - 10 :
			$message = "获取不到业务信息或业务已失效";
			break;
		case - 11 :
			$message = "获取不到接口业务编号";
			break;
		case - 12 :
			$message = "调用接口成功，修改业务失败";
			break;
		case - 13 :
			$message = "获取不到订单";
			break;
		case - 14 :
			$message = "接口订单ID错误";
			break;
		case - 15 :
			$message = "调用接口成功，添加业务失败";
			break;
		case - 16 :
			$message = "获取不到配置信息";
			break;
		case - 17 :
			$message = "添加联系人信息失败";
			break;
		case - 18 :
			$message = "单位名称错误";
			break;
		case - 19 :
			$message = "英文单位名称错误";
			break;
		case - 20 :
			$message = "注册人姓只能为中文";
			break;
		case - 21 :
			$message = "注册人名只能为中文";
			break;
		case - 22 :
			$message = "注册人国家只能为中国";
			break;
		case - 23 :
			$message = "注册人地址英文错误";
			break;
		case - 24 :
			$message = "注册人邮政编码错误";
			break;
		case - 25 :
			$message = "注册人电话错误";
			break;
		case - 26 :
			$message = "注册人传真同电话";
			break;
		case - 27 :
			$message = "注册人邮箱错误";
			break;
		case - 28 :
			$message = "联系人姓只能为中文";
			break;
		case - 29 :
			$message = "联系人名只能为中文";
			break;
		case - 30 :
			$message = "联系人国家只能为中国";
			break;
		case - 31 :
			$message = "联系人地址英文错误";
			break;
		case - 32 :
			$message = "联系人邮政编码错误";
			break;
		case - 33 :
			$message = "联系人电话错误";
			break;
		case - 34 :
			$message = "联系人传真同电话";
			break;
		case - 35 :
			$message = "联系人邮箱错误";
			break;
		case - 36 :
			$message = "过期时间超过30天，不能续费";
			break;
		case - 37 :
			$message = "获取不到升级产品信息";
			break;
		case - 38 :
			$message = "升级产品与原产品不匹配";
			break;
		case - 39 :
			$message = "扣费失败！";
			break;
		case - 40 :
			$message = "调用接口失败！";
			break;
		case - 41 :
			$message = "获取接口业务编号失败！";
			break;
		case - 42 :
			$message = "该域名无法注册";
			break;
		case - 43 :
			$message = "查询域名失败，请重试";
			break;
		case - 44 :
			$message = "该产品不属于此业务";
			break;
		case - 45 :
			$message = "证件号码错误";
			break;
		case - 100 :
			$message = '试用业务不能执行此操作';
			break;
		case - 101 :
			$message = '业务不存在或不属于该会员';
			break;
		case - 102 :
			$message = '交易成功，业务表修改失败';
			break;
		case - 103 :
			$message = '业务表插入失败';
			break;
		case - 104 :
			$message = '订单表修改失败';
			break;
		case - 105 :
			$message = '空间大小必须大于100且为100的倍数';
			break;
		case - 106 :
			$message = 'IP个数为大于0的整数';
			break;
		case - 107 :
			$message = '流量大小必须为1000的倍数';
			break;
		case - 108 :
			$message = '备份个数为大于0的整数';
			break;
		case - 109 :
			$message = '邮箱空间大小为100的倍数的整数';
			break;
		case - 110 :
			$message = '增值类型错误';
			break;
		case - 111 :
			$message = '增值记录失败';
			break;
		case - 112 :
			$message = '升级失败';
			break;
		case - 113 :
			$message = '订单不存在或不属于该会员';
			break;
		case - 114 :
			$message = '不是试用业务，无需转正';
			break;
		case - 115 :
			$message = '已经到达最多试用次数，不能再免费使用';
			break;
		case - 116 :
			$message = '增值大小错误';
			break;
		case - 117 :
			$message = '不是测试业务不需要审核';
			break;
		case - 118 :
			$message = '站点创建成功，业务信息修改失败';
			break;
		case - 119 :
			$message = '站点数据库成功，业务信息修改失败';
			break;
		case - 120 :
			$message = '此类主机购买次数达到上线，如需购买请联系网站管理员';
			break;
		case - 121 :
			$message = '续费限制5分钟之内不得重复续费';
			break;
		case - 122 :
			$message = '域名注册成功,添加域名业务信息失败';
			break;
		case - 123 :
			$message = '产品价格获取失败';
			break;
		case - 124 :
		    $message = '该订单已开通，无需重复开通';
			break;
	    case - 125 :
	        $message = '订单开通失败';
		    break;
		case - 126 :
			$message = '该业务已过期，请同步后再操作';
			break;
		case 3046 :
			$message = '免费主机到期时间超出限制，请在到期时间剩余一年内续费';
			break;
		case 3047 :
			$message = "今天的一台免费额度已被别人使用，请明天再来";//每个会员每天限开通一个免费主机
			break;
		case 5021 :
			$message = "快云服务器，业务正在开通中";
			break;
		case 5023 :
			$message = "快云服务器，IP信息查询失败";
			break;
		default :
			$message = api_code ( $code );
			break;
	}
	return $message;
}

/**
 * 处理接口返回code为异常信息
 *
 * @param
 *        	$code
 */
function api_code($code) {
	$message = "";
	switch ($code) {
		case 1 :
			$message = "未知错误";
			break;
		case 99 :
			$message = "接口繁忙";
			break;
		case 110 :
			$message = "已经被取消调用接口的资格";
			break;
		case 601 :
			$message = "没有可用资源获取资源失败";
			break;
		case 600:
			$message = "已经增值过ip";
			break;
		case 603:
			$message = "增值服务不适合该产品";
			break;
		case 604:
			$message = "此增值暂未开放";
			break;
		case 606:
			$message = "扣费成功，更改配置失败";
			break;
		case 608:
			$message = "已增值过定时备份";
			break;
		case 1000 :
			$message = "请求的method错误";
			break;
		case 1001 :
			$message = "签名验证错误";
			break;
		case 1002 :
			$message = "客户端请求超时（超过10分钟）";
			break;
		case 1005 :
			$message = "Access Id错误";
			break;
		case 1006 :
			$message = "IP错误(Access Id对应的IP不对)";
			break;
		case 1007 :
			$message = "ptype错误";
			break;
		case 1008 :
			$message = "pname错误";
			break;
		case 1009 :
			$message = "tid错误";
			break;
		case 1010 :
			$message = "sid错误";
			break;
		case 1011 :
			$message = "yid错误";
			break;
		case 1012 :
			$message = "获取不到业务（bid错误或者不属于该会员）";
			break;
		case 1013 :
			$message = "开通失败";
			break;
		case 1014 :
			$message = "调用接口，交易成功，配置失败，请联系客服";
			break;
		case 1015 :
			$message = "开通成功，获取IP失败，请联系客服获取";
			break;
		case 1016 :
			$message = "重启失败";
			break;
		case 1017 :
			$message = "续费成功，开机失败";
			break;
		case 1018 :
			$message = "did错误，未找到该订单";
			break;
		case 1019 :
			$message = "产品升级错误，未找到新的产品";
			break;
		case 1020 :
			$message = "升级成功，开机失败";
			break;
		case 1021 :
			$message = "size大小值不符合递增规则";
			break;
		case 1022 :
			$message = "此产品没有此业务";
			break;
		case 1023 :
			$message = "此业务已经过期";
			break;
		case 1024 :
			$message = "测试业务无法进行该操作";
			break;
		case 1025 :
			$message = "资源不足";
			break;
		case 1026 :
			$message = "接口订单状态错误";
			break;
		case 1027 :
			$message = "续费失败";
			break;
		case 1028 :
			$message = "申请次数受到限制";
			break;
		case 1029 :
			$message = "升级方案错误";
			break;
		case 1030 :
			$message = "FTP密码格式错误";
			break;
		case 1031 :
			$message = "业务无需转正";
			break;
		case 1032 :
			$message = "到期时间不足一个月";
			break;
		case 1033 :
			$message = "增值失败";
			break;
		case 1034 :
			$message = "参数不能为空";
			break;
		case 1035 :
			$message = "数据库类型不匹配";
			break;
		case 1036 :
			$message = "创建数据库失败";
			break;
		case 1037 :
			$message = "输入参数错误";
			break;
		case 1038 :
			$message = "名称已经存在";
			break;
		case 1039 :
			$message = "输入密码错误";
			break;
		case 1040 :
			$message = "超出可创建数据库数量";
			break;
		case 1041 :
			$message = "数据库名称已经存在";
			break;
		case 1042 :
			$message = "获取数据库信息失败";
			break;
		case 1043 :
			$message = "首页名字错误";
			break;
		case 1044 :
			$message = "获取邮局信息失败";
			break;
		case 1045 :
			$message = "超出可创建邮箱个数";
			break;
		case 1046 :
			$message = "超出可创建空间大小";
			break;
		case 1047 :
			$message = "输入时间错误";
			break;
		case 1048 :
			$message = "密码强度不够";
			break;
		case 1049 :
			$message = "绑定域名失败";
			break;
		case 1050 :
			$message = "站点已经处于关闭状态";
			break;
		case 1051 :
			$message = "首页已经存在";
			break;
		case 1052 :
			$message = "错误页面代码error";
			break;
		case 1053 :
			$message = "错误页面定义失败";
			break;
		case 1054 :
			$message = "网站已经开启";
			break;
		case 1055 :
			$message = "无法删除";
			break;
		case 1056 :
			$message = "删除域名失败";
			break;
		case 1057 :
			$message = "产品类型错误";
			break;
		case 1058 :
			$message = "参数不能为空";
			break;
		case 1059 :
			$message = "业务标识错误";
			break;
		case 1060 :
			$message = "服务项目只能1-3个";
			break;
		case 1061 :
			$message = "手机号码错误";
			break;
		case 1062 :
			$message = "固话错误";
			break;
		case 1063 :
			$message = "产品类型错误";
			break;
		case 1064 :
			$message = "获取服务项目失败";
			break;
		case 1065 :
			$message = "提交工单失败";
			break;
		case 1066 :
			$message = "起始或截至时间不符合规则";
			break;
		case 1067 :
			$message = "工单正在处理中，完成之前不能再次提交";
			break;
		case 1068 :
			$message = "工单编号不能为空";
			break;
		case 1069 :
			$message = "回复内容不能为空";
			break;
		case 1070 :
			$message = "获取不到工单";
			break;
		case 1071 :
			$message = "处理速度，服务质量，服务态度不能为空";
			break;
		case 1072 :
			$message = "无法评价工单";
			break;
		case 1073 :
			$message = "评价工单失败";
			break;
		case 1074 :
			$message = "服务速度，服务质量，服务态度必须为1-5的整数";
			break;
		case 1075 :
			$message = "工单不属于该用户";
			break;
		case 1076 :
			$message = "工单问题已经解决，无法提交回复";
			break;
		case 1077 :
			$message = "该状态的工单无法回复";
			break;
		case 1078 :
			$message = "工单回复失败";
			break;
		case 1079 :
			$message = "没有与业务标识对应的服务项目";
			break;
		case 1080 :
			$message = "回复，问题描述，评价内容不能超过500字";
			break;
		case 1081 :
			$message = "联系人姓名长度必须小于20位";
			break;
		case 1082 :
			$message = "域名后缀错误";
			break;
		case 1083 :
			$message = "域名注册参数不能为空";
			break;
		case 1084 :
			$message = "域名名称错误";
			break;
		case 1085 :
			$message = "域名注册参数错误";
			break;
		case 1086 :
			$message = "域名格式错误";
			break;
		case 1087 :
			$message = "中文 单位名称只能为中文且长度>=5";
			break;
		case 1088 :
			$message = "英文单位名称只能为字母和数字,且长度为3-200";
			break;
		case 1089 :
			$message = "注册人或联系人姓只能为中文";
			break;
		case 1090 :
			$message = "注册人或联系人名只能是中文";
			break;
		case 1091 :
			$message = "注册人或联系人国家只能为中国";
			break;
		case 1092 :
			$message = "注册人或联系人省份错误";
			break;
		case 1093 :
			$message = "注册人或联系人城市错误";
			break;
		case 1094 :
			$message = "注册人或联系人地址英文只能为数字字母，且长度6-64";
			break;
		case 1095 :
			$message = "注册人或联系人邮政编码只能为数字，长度为6";
			break;
		case 1096 :
			$message = "注册人或联系人电话错误(86+区号+电话号码[+分机],如：86+0100+8888666[+1000])";
			break;
		case 1097 :
			$message = "注册人或联系人传真错误(86+区号+电话号码[+分机],如：86+0100+8888666[+1000])";
			break;
		case 1098 :
			$message = "注册人或联系人邮箱错误";
			break;
		case 1099 :
			$message = "获取域名产品信息错误";
			break;
		case 1100 :
			$message = "域名信息格式不正确";
			break;
		case 1101 :
			$message = "域名信息不符合相关产品";
			break;
		case 1102 :
			$message = "获取价格失败";
			break;
		case 1103 :
			$message = "扣费失败";
			break;
		case 1104 :
			$message = "域名已经被注册";
			break;
		case 1105 :
			$message = "过期30天不能续费";
			break;
		case 1106 :
			$message = "解析A记录的IP地址错误";
			break;
		case 1107 :
			$message = "解析类型错误";
			break;
		case 1108 :
			$message = "获取域名列表失败";
			break;
		case 1109 :
			$message = "该产品已经被禁用";
			break;
		case 1111 :
			$message = "操作类型错误";
			break;
		case 1112 :
			$message = "数据库名称错误";
			break;
		case 1113 :
			$message = "数据库容量错误";
			break;
		case 1114 :
			$message = "数据库编号错误";
			break;
		case 1115 :
			$message = "地区编号错误";
			break;
		case 1116 :
			$message = "修改密码失败";
			break;
		case 1117 :
			$message = "域名密码格式错误";
			break;
		case 1118 :
			$message = "操作系统类型错误";
			break;
		case 1119 :
			$message = "域名接口错误";
			break;
		case 1120 :
			$message = "此产品不能试用";
			break;
		case 1121 :
			$message = "备案号码错误";
			break;
		case 1122 :
			$message = "域名与备案号不一致";
			break;
		case 1123 :
			$message = "暂无资源，请选购其他期限";
			break;
		case 1131 :
			$message = "时间间隔太短，续费限制5分钟之内不得重复续费";
			break;
		case 3001 :
			$message = "接口账户余额不足";
			break;
		case 3002 :
			$message = "会员账户被冻结";
			break;
		case 3003 :
			$message = "会员账户异常";
			break;
		case 3004 :
			$message = "没有找到该会员";
			break;
		case 3005 :
			$message = "业务不属于该会员";
			break;
		case 3010 :
			$message = "产品结算失败";
			break;
		case 3050 :
			$message = "主机开通异常，请稍后到订单列表获取开通业务";
			break;
		case 3044 :
			$message = '免费主机开通名额已满';
			 break;
		case 3201 :
			$message = "订单号验证失败";
			break;
		case 3202 :
			$message = "业务标识验证失败";
			break;
		case 3203 :
			$message = "业务标识已存在（域名已被他人使用）";
			break;
		case 3204 :
			$message = "业务密码验证失败";
			break;
		case 3205 :
			$message = "参数错误";
			break;
		case 3206 :
			$message = "错误的产品编号";
			break;
		case 3207 :
			$message = "试用机会已满,不得再次试用";
			break;
		case 3208 :
			$message = "主机无需转正(已是正式业务)";
			break;
		case 3209 :
			$message = "操作失败(非正式业务无法进行操作)";
			break;
		case 3210 :
			$message = "无法操作,主业务已到期(续费操作可以)";
			break;
		case 3211 :
			$message = "升级失败(无法跨操作系统进行升级)";
			break;
		case 3212 :
			$message = "根据条件未能找到匹配的产品信息";
			break;
		case 3213 :
			$message = "升级失败(新产品与业务现产品一致)";
			break;
		case 3214 :
			$message = "产品升级失败(新产品的某些配置低于老产品)";
			break;
		case 3215 :
			$message = "默认首页错误";
			break;
		case 3216 :
			$message = "站点操作失败";
			break;
		case 3217 :
			$message = "绑定域名失败(域名已存在)";
			break;
		case 3218 :
			$message = "删除绑定域名失败(www域名不得删除)";
			break;
		case 3219 :
			$message = "站点已被管理员关闭，无法开启";
			break;
		case 3220 :
			$message = "业务产品类别验证失败";
			break;
		case 3221 :
			$message = "增值数据库失败";
			break;
		case 4001 :
			$message = "参数错误";
			break;
		case 4002 :
			$message = "产品编号错误";
			break;
		case 4003 :
			$message = "未能找到匹配的产品信息";
			break;
		case 4004 :
			$message = "资源不足";
			break;
		case 4005 :
			$message = "试用次数已满";
			break;
		case 4006 :
			$message = "云空间增值失败(配置为不限,无需增值)";
			break;
		case 4007 :
			$message = "云空间无需转正(已是正式业务)";
			break;
		case 4008 :
			$message = "产品升级失败(新产品的某些配置低于老产品)";
			break;
		case 4009 :
			$message = "无法操作,主业务已到期(续费操作可以)";
			break;
		case 4010 :
			$message = "创建站点失败";
			break;
		case 4011 :
			$message = "创建站点失败(站点数量已达上限)";
			break;
		case 4012 :
			$message = "创建站点失败(所传站点空间大小大于业务所剩余的空间)";
			break;
		case 4013 :
			$message = "没有找到该站点的数据信息";
			break;
		case 4014 :
			$message = "站点操作失败";
			break;
		case 4015 :
			$message = "创建数据库失败";
			break;
		case 4016 :
			$message = "创建数据库失败(数据库数量已达上限)";
			break;
		case 4017 :
			$message = "创建数据库失败(所传数据库空间大小大于业务所剩余的空间)";
			break;
		case 4018 :
			$message = "开启/关闭数据库失败";
			break;
		case 4019 :
			$message = "设置数据库配额大小";
			break;
		case 4020 :
			$message = "站点容量错误";
			break;
		case 4021 :
			$message = "站点流量错误";
			break;
		case 4022 :
			$message = "站点编号错误";
			break;
		case 4023 :
			$message = "创建站点失败(站点已存在)";
			break;
		case 4024 :
			$message = "绑定域名失败(域名已存在)";
			break;
		case 4025 :
			$message = "添加默认页失败(默认页已存在)";
			break;
		case 4026 :
			$message = "升级失败(无法跨操作系统进行升级)";
			break;
		case 4027 :
			$message = "操作失败(非正式业务无法进行操作)";
			break;
		case 4028 :
			$message = "无法找到产品对应的配置信息";
			break;
		case 4029 :
			$message = "增值失败(增值大小不是产品定型中数量的整数倍)";
			break;
		case 4030 :
			$message = "创建站点失败(所传站点流量大小大于业务所剩余的流量)";
			break;
		case 4031 :
			$message = "升级失败(新产品与业务现产品一致)";
			break;
		case 4032 :
			$message = "删除绑定域名失败(www域名不得删除)";
			break;
		case 5002 :
			$message = "快云VPS修改密码 用户名错误";
			break;
		case 5003 :
			$message = "快云VPS修改密码 密码错误";
			break;
		case 5004 :
			$message = "业务的IP不合法";
			break;
		case 5005 :
			$message = "修改密码的端口不通";
			break;
		case 5007 :
			$message = "未知的增值产品类型";
			break;
		case 5008 :
			$message = "快云VPS增值大小不符合规则";
			break;
		case 5010 :
			$message = "产品个数已达上限，请联系管理员";
			break;
        case 5014 :
            $message = "快云服务器，购买失败";//快云服务器，添加购物车失败
            break;
		case 5016 :
			$message = "快云服务器，ip不存在";
			break;
		case 5017 :
			$message = "快云服务器，ip无法绑定";
			break;
		case 5018 :
			$message = "快云服务器，ip无法解绑";
			break;
		case 5019 :
			$message = "快云服务器，订单信息为空";
			break;
		case 5020 :
			$message = "快云服务器，业务信息异常";
			break;
		case 5033 :
			$message = "IP正在绑定，请稍后查看";
			break;
		case 5034 :
		    $message = "未获取到业务信息";
		    break;
		case 5202 :
			$message = "无效的云主机配置";
			break;
		case 5203 :
			$message = "无效的订单号";
			break;
		case 5204 :
			$message = "资源不足";
			break;
		case 5205 :
			$message = "当前状态下不可进行操作";
			break;
		case 5206 :
			$message = "无效的镜像编号";
			break;
		case 5300 :
			$message = "业务的IP不合法";
			break;
		case 5301 :
			$message = "向数据库保存业务信息失败";
			break;
		case 5302 :
			$message = "无法获取订单表";
			break;
		case 5303 :
			$message = "无法获取交易信息";
			break;
		case 5304 :
			$message = "业务资源不足";
			break;
		case 5305 :
			$message = "测试次数已达上限";
			break;
		case 5306 :
			$message = "添加产品至购物车失败";
			break;
		case 5401 :
			$message = "向数据库中保存服务码记录时出现错误,请联系您的商务处理";
			break;
		case 5402 :
			$message = "查询不到符合条件的服务码";
			break;
		case 5502 :
			$message = "获取接口链接失败";
			break;
		case 5503 :
			$message = "找不到业务所在服务器";
			break;
		case 5504 :
			$message = "无可用IP资源";
			break;
		case 5505 :
			$message = "订单处理成功,开通失败";
			break;
		case 5506 :
			$message = "续费成功,开机失败,请联系您的商务人员";
			break;
		case 5603 :
			$message = "业务所在服务器位置存在问题,请联系您的商务处理";
			break;
		case 5604 :
			$message = "未找到业务所在服务器,请联系您的商务处理";
			break;
		case 5605 :
			$message = "找不到适当的存储,请联系您的商务处理";
			break;
		case 5606 :
			$message = "您业务在服务器上的名称存在问题,请联系您的商务处理";
			break;
		case 5608 :
			$message = "未找到业务的网卡设备";
			break;
		case 5612 :
			$message = "找不到适当的存储";
			break;
		case 5615 :
			$message = "未找到系统虚拟设备";
			break;
		case 5622 :
			$message = "业务机器没有快照,请联系您的商务处理";
			break;
		case 5625 :
			$message = "您的业务机器未安装操作系统或者未安装VMTools";
			break;
		case 5626 :
			$message = "在服务器上找不到您的业务机器,请联系您的商务处理";
			break;
		case 5627 :
			$message = "未找到宿主服务器";
			break;
		case 5630 :
			$message = "业务未关机";
			break;
		case 5632 :
			$message = "业务所在服务器上的名称已经存在,请联系您的处理";
			break;
		case 5633 :
			$message = "VMWareTools未安装,请安装并运行";
			break;
		case 5634 :
			$message = "VMWareTools未运行,请运行";
			break;
		case 5635 :
			$message = "VMWareTools版本太旧,请升级";
			break;
		case 5636 :
			$message = "当前业务状态不允许此操作";
			break;
		case 5640 :
			$message = "未找到模板";
			break;
		case 5683 :
			$message = "本业务所在宿主机的用户名、密码存在问题,请联系您的商务处理";
			break;
		case 5901 :
			$message = "请检测修改密码服务是否运行正常";
			break;
		case 5902 :
			$message = "修改密码服务端没有启动";
			break;
		case 5903 :
			$message = "请检测用户名是否存在";
			break;
		case 5904 :
			$message = "修改密码的端口不通";
			break;
		case 5905 :
			$message = "网络出现异常";
			break;
		case 7001 :
			$message = "ssl购买，RMI信息返回失败";
			break;
		case 7002 :
			$message = "ssl业务信息异常";
			break;
		case 7003 :
			$message = "ssl提交信息异常NULL";
			break;
		case 7004 :
			$message = "ssl提交域名验证异常";
			break;
		case 7005 :
			$message = "ssl邮箱错误";
			break;
		case 7006 :
			$message = "ssl所传递进度不一致";
			break;
		case 7007 :
			$message = "ssl申请人姓名不对";
			break;
		case 7008 :
			$message = "ssl获取管理邮箱失败";
			break;
		case 7009 :
			$message = "ssl发送邮箱验证码失败";
			break;
		case 7010 :
			$message = "ssl邮箱验证失败";
			break;
		case 7011 :
			$message = "ssl身份证号验证失败";
			break;
		case 7012 :
			$message = "ssl手机号验证失败";
			break;
		case 7013 :
			$message = "sslQQ号验证失败";
			break;
		case 7014 :
			$message = "ssl单位类型验证失败";
			break;
		case 7015 :
			$message = "ssl单位名称验证失败";
			break;
		case 7016 :
			$message = "ssl单位邮箱验证失败";
			break;
		case 7017 :
			$message = "ssl单位电话验证失败";
			break;
		case 7018 :
			$message = "ssl上传的文件为空";
			break;
		case 7019 :
			$message = "ssl文件保存异常";
			break;
		case 7020 :
			$message = "ssl没有需要开通的状态";
			break;
		case 7021 :
			$message = "ssl没有需要验证的SSL状态";
			break;
		case 7044 :
			$message = "不能转正";
			break;
		case 7052 :
			$message = "您的企业名称格式不正确";
			break;
		case 7053 :
			$message = "您的营业执照编号不能为空";
			break;
		case 7054 :
			$message = "该营业执照编号已经被验证";
			break;
		case 7055 :
			$message = "您的固定电话格式不正确";
			break;
		case 7056 :
			$message = "对不起，您不是代理会员";
			break;
		case 7057 :
			$message = "对不起，您已经提交过了，请耐心等待";
			break;
		case 7058 :
			$message = "对不起，该产品不能进行认证";
			break;
		case 7059 :
			$message = "对不起，查询不到该试用业务";
			break;
		case 7060 :
			$message = "申请提交失败";
			break;
		case 7061 :
			$message = "上传的图片为空";
			break;
		case 7062 :
			$message = "上传的图片过大，请您上传小于10M的图片";
			break;
		case 7063 :
			$message = "上传文件格式错误";
			break;
		case 7064 :
			$message = "图片转化异常";
			break;
		case 7067 :
			$message = "云服务器临时增值带宽调用接口消息队列失败";
			break;
		case 8001 :
			$message = "域名注册接口不存在";
			break;
		case 8002 :
			$message = "交易失败";
			break;
		case 8003 :
			$message = "操作失败";
			break;
		case 8004 :
			$message = "修改失败";
			break;
		case 8005 :
			$message = "修改异常";
			break;
		case 8006 :
			$message = "云服务器IP临时增值获取不到集群编号";
			break;
	}
	$message .= "请联系客服。";
	return $message;
}


/**
 * 下载远程文件
 *
 * @param: $url 远程文件路径
 * @param: $savePath 保存路径
 * @param: $fileName 保存文件名称
 * @param: $type 获取远程文件方法
 *        	1：curl 0:Output
 */
function get_Files($url, $savePath, $fileName, $type = 0) {
	if (is_null ( trim ( $url ) )) {
		return false;
	}
	if (is_null ( trim ( $savePath ) )) {
		$savePath = "." . DIRECTORY_SEPARATOR;
	}
	// 文件路径最后加/
	if (0 !== strrpos ( $savePath, DIRECTORY_SEPARATOR )) {
		$savePath .= DIRECTORY_SEPARATOR;
	}
	// 查看保存目录是否存在，不存在则创建
	if (! file_exists ( $savePath ) && ! mkdir ( $savePath, 0777, true )) {
		return false;
	}
	// 获取远程文件所采用的方法。$type=1而且curl扩展开启的话用curl否则用output
	if ($type && extension_loaded ( "curl" )) {
		$ch = curl_init ();
		$timeout = 5;
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		$content = curl_exec ( $ch );
	} else {
		ob_start ();
		readfile ( $url );
		$content = ob_get_contents ();
		ob_end_clean ();
	}
	$fp2 = @fopen ( $savePath . $fileName, 'a' );
	fwrite ( $fp2, $content );
	fclose ( $fp2 );
	unset ( $content, $url );
	return array (
			'file_name' => $fileName,
			'save_path' => $savePath . $fileName
	);
}

/**
 *透过代理获取用户真实IP
 *
 */
function get_userip() {
	$unknown = 'unknown';
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	/*
	 处理多层代理的情况
	 或者使用正则方式：$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
	 */
	if (false !== strpos($ip, ',')) $ip = reset(explode(',', $ip));
	return $ip;
}


/**
 * 记录API执行记录，api_log
 *
 * @param: $user_id 用户ID
 * @param: $input 执行API参数
 * @param: $output API返回结果
 * @param: $des 说明信息
 */
function api_log($user_id,$input,$output,$des = null) {
	$m_api_log = M('api_log');
	if($user_id==''){
		$user_id = '-1';
	}
	$data ["user_id"] = $user_id;
	$data ["input"] = $input;
	$data ["output"] = $output;
	$data ["create_time"] = date ( 'Y-m-d H:i:s' );
	$data ["des"] = $des;
	return $m_api_log->add ( $data );
}
/**
* 记录会员信息
* @date: 2016年10月27日 下午5:05:40
* @author: Lyubo
* @param: $user_info
* @return:
*/
function rememberMember($user_info){
    session(array( 'prefix'=>'Frontend' ));//前台session前缀
    session('user_id',$user_info['user_id']);
    session('user_name',$user_info['user_name']);
    session('login_name',$user_info['login_name']);
}
/**
 * ----------------------------------------------
 * | 添加一条交易记录的快捷方法，
 * | member_transactions表
 * | @时间: 2016年10月27日 上午10:17:51
 * | @author: duanbin
 * | @param: $type '交易类型0充值 1消费 2取款' 
 * | @param: $recharge '0:手动录入1：在线支付'
 * | @param: $remark '录款备注'
 * | @return: type
 * ----------------------------------------------
 */
function add_transactions($user_id, $transactions_money, $note_appended,$type, $recharge=1, $product_id = null,$order_id=null,$remark=null){
	$m_common_deal = new \Common\Model\DealModel();
	return $m_common_deal->addRecord($user_id, $transactions_money, $note_appended,$type, $recharge, $product_id, $order_id, $remark);
}

/**
 * ----------------------------------------------
 * | 添加增值业务的快捷方法
 * | @时间: 2016年11月1日 下午2:24:34
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function add_appreciation($addValue, $product_info, $business_info){
	$m_common_appreciation = new \Common\Model\AppreciationModel();
	return $m_common_appreciation->enter($addValue, $product_info, $business_info);
}
/**
 * 插入订单方法
 * @date: 2016年11月11日 上午10:38:57
 * @author: Lyubo
 * @param: $GLOBALS
 * @return:
 */
function add_order($order_info){
    $order = new \Frontend\Model\OrderModel();
    return $order->add_order($order_info);
}
/**
 * ----------------------------------------------
 * | 百度编辑器调用的方法
 * | @时间: 2016年11月9日 下午2:28:43
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function ueditor(){

	$data = new \Common\Aide\UeditorAide();
	echo $data->output();
}


/**
 * ----------------------------------------------
 * | 图片文件上传的快捷方法
 * | @时间: 2016年11月11日 上午11:09:51
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function imgUploader($input_name, $dirName, $fileSize = '', $thumb = array()){
	$img_uploader = new \Common\Aide\ImagesAide();
	return $img_uploader->ImgUploader($input_name, $dirName, $fileSize, $thumb);
}

/**
 * ----------------------------------------------
 * | 文件上传的快捷方法
 * | @时间: 2016年11月11日 上午11:10:19
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function fileUploader($inputName, $dirName, $exts = array(), $fileSize = '', $thumb = array()){
	$file_uploader = new \Common\Aide\FileUploadAide();
	return $file_uploader->FileUploader($inputName, $dirName, $exts, $fileSize, $thumb);
}

/**
 * ----------------------------------------------
 * | 删除文件的快捷方法(基于tp的文件驱动类)
 * | @时间: 2016年11月13日 下午3:00:15
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function remove_file($path){
	$file = new \Think\Storage\Driver\File();
	return ($file->has($path) && $file->unlink($path)) ? true: false;
}

/**
 * ----------------------------------------------
 * | 递归删除文件夹下面的文件；
 * | @时间: 2016年11月14日 下午5:27:43
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function deleteDirRecursion($path, $remove_dir = true){
	$file_handler = new \Common\Aide\FileStorageAide();
	return $file_handler->deleteDirRecursion($path, $remove_dir);
}

/**
 * ----------------------------------------------
 * | 清楚缓存文件
 * | @时间: 2016年11月15日 上午11:39:15
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: array [ 'html' => bool, 'cache' => bool, 'data' => bool, 'temp' => bool, ]
 * ----------------------------------------------
 */
function clear_cache(){
	$res = [];
	$file_handler = new \Common\Aide\FileStorageAide();
	
	//真静态缓存文件
	$html_path = APP_PATH.'Html'.DS;
	if (is_dir($html_path)){
		$res['html'] = $file_handler->deleteDirRecursion($html_path);
	}
	
	//静态页缓存文件
	$cache_path = APP_PATH.'Runtime'.DS.'Cache'.DS;
	if (is_dir($cache_path)){
		$res['cache'] = $file_handler->deleteDirRecursion($cache_path);
	}
	
	//数据缓存
	$data_path = APP_PATH.'Runtime'.DS.'Data'.DS;
	if (is_dir($data_path)){
		$res['data'] = $file_handler->deleteDirRecursion($data_path);
	}
	
	//临时缓存文件
	$temp_path = APP_PATH.'Runtime'.DS.'Temp'.DS;
	if (is_dir($temp_path)){
		$res['temp'] = $file_handler->deleteDirRecursion($temp_path);
	}
	//删除动态生成的缓存
	$common_runtime_file = APP_PATH.'Runtime'.DS.'common~runtime.php';
	if (file_exists($common_runtime_file)){
		unlink($common_runtime_file);
	}
	
	return $res;
}

/**
 * 截取字符串
 * @param $text
 * @param $length
 * @return string
 */
function subtext($text, $length)
{
	$text = strip_tags($text);
	if(mb_strlen($text,'utf8') > $length)
		return mb_substr($text, 0, $length,'utf8').'...';
	return $text;
}
/**
* 快云服务器计算价格
* @date: 2016年11月26日 下午5:14:55
* @author: Lyubo
* @param: $parms
* @return: $price
*/
function cloudserverPrice($parms){
    $cloudserver = new \Frontend\Model\CloudserverModel();
    return $cloudserver->CalculatedPrice($parms);
}

/**
 * 获取当前时间公共方法
 * @author: Guopeng
 */
function current_date() {
	date_default_timezone_set ( 'Etc/GMT-8' );
	return date ( 'Y-m-d H:i:s' );
}
/**
 * 计算相加日期
 * @author: Guopeng
 * @param: $b
 */
function add_dates($Date_1, $month) {
	$date_year = date ( 'Y-m-d H:i:s', strtotime ( "$Date_1 + $month month" ) );
	return $date_year;
}
/**
 * 计算增值月份
 * @author: Guopeng
 * @param: $over
 */
function app_month($over) {
	$arr = explode ( "-", $over );
	$current = explode ( "-", current_date());
	// 年
	$y = $arr [0] - $current [0];
	// 月
	$m = $arr [1] - $current [1];
	if ($y < 0) {
		return 0;
	}
	$qx = 0;
	// 大于天数+1月
	if ($arr [2] > $current [2]) {
		$qx = $y * 12 + $m + 1;
	} else {
		$qx = $y * 12 + $m;
	}
	return $qx;
}
/**
 * 计算过期天数
 * @author: Guopeng
 * @param: $Date_2
 */
function overdue_day($Date_2) {
	$Date_1 = date("Y-m-d H:i:s");
	$d1 = strtotime ( $Date_1 );
	$d2 = strtotime ( $Date_2 );
	$Days = round ( ($d2 - $d1) / 3600 / 24 );
	if ($Days == - 0)
		$Days = 0;
	return $Days;
}
/**
 * 添加增值信息
 * @author: Guopeng
 * @param: $business_id 业务ID
 * @param: $product_id 增值产品ID
 * @param: $quanity 增值大小
 */
function add_appreciations($business_id, $app_product_id, $quanity,$product_type_id, $ip_address) {
	$common_appreciation = new \Common\Model\AppreciationModel();
	$params ["business_id"] = $business_id;
	$params ["app_product_id"] = $app_product_id;
	$params ["quanity"] = $quanity;
	$params ["product_type_id"] = $product_type_id;
	$params ["create_time"] = current_date ();
	$params ["ip_address"] = $ip_address;
	return $common_appreciation->add($params);
}

// 验证密码
function reg_user_pass($str) {
    return preg_match ( '/^\S{8,30}$/', $str );
}
/**
 * ----------------------------------------------
 * | 判断当前域名是否是https的
 * | @时间: 2016年12月8日 上午10:42:09
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function isHTTPS(){
	if(!isset($_SERVER['HTTPS']))  return FALSE;
	if($_SERVER['HTTPS'] === 1){  //Apache
		return TRUE;
	}elseif($_SERVER['HTTPS'] === 'on'){ //IIS
		return TRUE;
	}elseif($_SERVER['SERVER_PORT'] == 443){ //其他
		return TRUE;
	}
	return FALSE;
}
/**
* 获取景安新闻公告
* @date: 2017年2月7日 下午6:11:10
* @author: Lyubo
* @param: $GLOBALS
* @return:
*/
function get_url_content($url){
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    $result=curl_exec($ch);
    return $result;
}
/**
* 获取主机的绑定域名
* @date: 2016年12月28日 下午12:01:41
* @author: Lyubo
* @param: $info
* @return: $array
*/
function Get_bind_domain($info){
    foreach($info['list'] as $key=>$val){
        $domain=str_replace("[","",$val['binddomain']);
        $bdDomain=explode("]",$domain);
        foreach ($bdDomain as $k=>$v){
            if(preg_match('/^([a-z0-9]+([a-z0-9-]*(?:[a-z0-9]+))?\.)?[a-z0-9]+([a-z0-9-]*(?:[a-z0-9]+))?(\.us|\.tv|\.org\.cn|\.org|\.net\.cn|\.net|\.mobi|\.me|\.la|\.info|\.hk|\.gov\.cn|\.edu|\.com\.cn|\.com|\.co\.jp|\.co|\.cn|\.cc|\.biz)$/i', $v)){
                $gym=$v;
                $info['list'][$key]['binddomain']=$gym;
                break;
            }
            if(strstr($val['beizhu'],'%')){
                $info['page_list'][$key]['beizhu']=urldecode($val['beizhu']);
            }
        }
       // dump($info);
    }
    return $info;
}
/**
* 业务开通邮件发送
* @date: 2017年2月24日 下午4:01:14
* @author: Lyubo
* @param: $GLOBALS
* @return:
*/
function business_sendMail($type,$business_info,$member_id){
        //获取会员信息
        $where["user_id"] = ["eq",$member_id];
        $member = new \Frontend\Model\MemberModel();
        $member_info =   $member->get_member_info($where);
        $content = HTMLContentForEmail($type,$business_info,$member_info);
        postOffice($member_info['user_mail'],$content['subject'],$content['body']);
}
/*
 * get method
*/
function get($url, $param){
    $url=$url.$param;
    $httph =curl_init($url);
    curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 1);
	curl_setopt($httph,CURLOPT_RETURNTRANSFER, true);
    $rst=curl_exec($httph);
	curl_close($httph);
	return $rst;
}
/**
* 白名单添加
* @date: 2017年2月22日 上午9:41:40
* @author: Lyubo
* @param: 
* @return:
*/
function WhiteList($type,$parms){
    $site_config = WebSiteConfig();
    $where['ip'] = $site_config['white_ip'];
    $where['secretKey'] = $site_config['white_key'];
    if($type == "select"){
        $where['ym'] = $parms['domain'];
        $url = "https://beian.zzidc.com/service/getDomainWhiteList?domain=";
        $parms = json_encode($where);
        $result = get($url,$parms);
        return json_decode($result,true);
    }elseif($type =="add"){
        $where['yms'] =[ (object)['ymip'=>$parms['ymip'],'ym'=>$parms['domain']]];
        $url = "https://beian.zzidc.com/service/addDomainWhite?domain=";
        $parms = json_encode($where);
        $result = get($url,$parms);
        return json_decode($result,true);
    }
} 
/**
* 去除空格
* @date: 2017年3月28日 下午1:52:38
* @author: Lyubo
* @param: $GLOBALS
* @return:
*/
function trimall($str)
{
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str);
}