<?php

namespace Agent;

use Common\Data\GiantAPIParamsData as GiantAPIParams;

define ( 'DOMAIN_URL', 'http://panda.www.net.cn/cgi-bin/check_muitl.cgi' );
ini_set ( 'max_execution_time', '1200' );
/**
 * 景安API接口 for PHP SDK v1.0.0
 */
require_once ('HttpClient.class.php');
require_once ('GiantException.class.php');

date_default_timezone_set ( 'PRC' ); // 设置为中国时间



/**
 * 交易操作类
 */
class Transaction {
	private $access_id; // 用户标识
	private $access_key; // 签名密钥
	private $host_url; // 接口地址
	
	/**
	 * 构造函数，初始化交易操作参数
	 *
	 * @param int $format_id
	 *        	默认为1
	 * @throws GiantException
	 */
	public function __construct($format_id = 1,$config = []) {
		$this->host_url = $config ['api_url'];
		$this->access_id = trim($config ['api_access_id']);
		$this->access_key = trim($config ['api_access_key']);
		if (! empty ( $format_id )) {
			$this->format_id = $format_id;
		} else {
			throw new GiantException ( 'format_id为空' );
		}
		if (empty ( $this->host_url )) {
			throw new GiantException ( '接口地址为空' . $this->host_url );
		}
		if (empty ( $this->access_id ) || empty ( $this->access_key )) {
			throw new GiantException ( 'Acccess Id为空或者Access Key为空' );
		}
	}
	
	/**
	 * 初始化公共参数并发送Http请求
	 *
	 * @param array $par        	
	 * @throws GiantException
	 * @return string http请求返回值
	 */
	private function init_params_send($par) {
		$date = date ( 'YmdHis' );
		$value = $this->access_id . $par ['ptype'] . $par ['tid'] . $date . $this->access_key;
		$gsig = strtoupper ( hash_hmac ( "sha1", $value, $this->access_key ) );
		$par ['aid'] = $this->access_id;
		$par ['date'] = $date;
		$par ['formatid'] = $this->format_id;
		$par ['gsig'] = $gsig;
		$par ['request_type'] = 1;
		$pageContents = HttpClient::quickPost ( $this->host_url, $par );
		if (empty ( $pageContents )) {
			throw new GiantException ( "接口返回值为空" );
		}
		if (strpos ( $pageContents, "HTTP Status" ) != false) {
			throw new GiantException ( "接口返回值错误" );
		}
		return $pageContents;
	}
	/**
	 * 购买快云服务器业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $yid
	 *        	接口yid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function cloud_buy($ptype, $yid, $area_code = null, $buy_info,$usetype = -1) {
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype或者pname为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['yid'] = $yid;
		$par ['area_code'] = $area_code;
		$par ['input_name'] =$buy_info['os'];
		$par ['cpucount'] = $buy_info['cpu'];
		$par ['memory'] = $buy_info['mem'];
		$par ['disk']= $buy_info['disk'];
		$par ['bandwidth'] = $buy_info['bandwidth'];
		$par ['tid'] = GiantAPIParams::TID_BUY;
        if(is_numeric($usetype) && $usetype+0 >= 0){
            $par ['usetype'] = intval(ceil($usetype+0));
        }
		return $this->init_params_send ( $par );
	}
	/**
	 * 购买SSL业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $yid
	 *        	接口yid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function ssl_buy($ptype,$pname,$dym=0,$fwq=0,$tpjg=0,$order_time=null) {
		if (empty($ptype) || empty($pname)) {
			throw new GiantException ( "ptype或者pname为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['yid'] = $order_time;
		$par ['dymzsgs'] =$dym;
		$par ['dfwqzs'] = $fwq;
		$par ['tpxzsgs'] = $tpjg;
		$par ['tid'] = GiantAPIParams::TID_BUY;
		return $this->init_params_send ( $par );
	}
	/**
	 * 购买业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $yid
	 *        	接口yid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function buy($ptype,$pname,$yid,$area_code = null,$system_type = null,$usetype = "") {
		if (empty ( $ptype ) || empty ( $pname )) {
			throw new GiantException ( "ptype或者pname为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['tid'] = GiantAPIParams::TID_BUY;
		$par ['yid'] = $yid;
		//echo $pname;
		//add by xuanyd 20140731
		if($system_type != null && $ptype != 'fastcloudvps' && $ptype !='vps' && $ptype !='cloudspace'){
			$par['systemType'] = $system_type;
		}else{
		    $par['input_name'] = $system_type;
		}
		//add by end xuanyd 20140731
		if ($area_code != null) {
			$par ['area_code'] = $area_code;
		}
        if(in_array($usetype,array(1,2,3,4))){
            $par ['usetype'] = $usetype;
        }
		return $this->init_params_send ( $par );
	}
	/**
	 * 同步产品价格
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function syncprice($ptype, $tid, $extra = [] ,$pname) {
	    if (empty ( $ptype ) || empty ( $tid )) {
	        throw new GiantException ( "ptype或者tid为空" );
	    }
	    $par = array ();
	    $par ['ptype'] = $ptype;
	    $par ['tid'] = '35';
	    /**
	     * ---------------------------------------------------
	     * | 增加域名价格同步的信息
	     * | @时间: 2017年1月10日 下午3:45:53
	     * ---------------------------------------------------
	     */
	    //下面是判断VPS、快云VPS的价格同步
	    if($ptype ==   GiantAPIParams::PTYPE_SELF){
	        $par['pname'] = $pname;
	    }
	    if (!empty($extra)){	    	
		    $par = array_merge($par, $extra);
	    }
	    //dump($par);die;
	    return $this->init_params_send ( $par );
	}
	/**
	 * 开通业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $did
	 *        	接口did参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function open($ptype, $did,$system_type = null) {
		if (empty ( $ptype ) || empty ( $did )) {
			throw new GiantException ( "ptype或者did为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = GiantAPIParams::TID_OPEN;
		$par ['systemType'] = $system_type;
		return $this->init_params_send ( $par );
	}
	/**
	 * 开通业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $did
	 *        	接口did参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function cloudserver_open($ptype, $did, $password = null) {
		if (empty ( $ptype ) || empty ( $did )) {
			throw new GiantException ( "ptype或者did为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = GiantAPIParams::TID_OPEN;
		$par ['password'] = $password;
		return $this->init_params_send ( $par );
	}
	/**
	 * 获取快云服务器进度
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $did
	 *        	接口did参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function cloudserver_getinfo($ptype, $pname, $did, $tid) {
		if (empty ( $ptype ) || empty ( $did )) {
			throw new GiantException ( "ptype或者did为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname']=$pname;
		$par ['did'] = $did;
		$par ['tid'] = $tid;
		return $this->init_params_send ( $par );
	}
	/**
	 * 同步快云服务器IP信息
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $did
	 *        	接口did参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function cloudserverip_getinfo($ptype, $pname, $did, $tid) {
	    if (empty ( $ptype ) || empty ( $did )) {
	        throw new GiantException ( "ptype或者did为空" );
	    }
	    $par = array ();
	    $par ['ptype'] = $ptype;
	    $par ['pname']=$pname;
	    $par ['bid'] = $did;
	    $par ['tid'] = GiantAPIParams::TID_SYNC_BUSINESS_INFO;
	    return $this->init_params_send ( $par );
	}
	/**
	 * 快云服务器绑定IP
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param int $ipdid
	 *        	接口did参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
    public function cloudserver_bound($ptype,$bid,$ipdid){
        if (empty ( $ptype ) || empty ( $bid )) {
            throw new GiantException ( "ptype或者bid为空" );
        }
        $par = array ();
        $par ['ptype'] = $ptype;
        $par ['bid'] = $bid;
        $par ['ipdid'] = $ipdid;
        $par ['tid'] = GiantAPIParams::TID_IP_BOUND;//快云服务器绑定IP
        return $this->init_params_send ( $par );
    }
    /**
     * 获取绑定IP进度
     * @param string $pname
     *        接口pname参数
     * @param string $ptype
     *        	接口ptype参数
     * @param int $bid
     *        	接口bid参数
     * @param int $ipdid
     *        	接口did参数
     * @throws GiantException
     * @return string 接口返回值 json 或者 xml
     */
    public function cloudserver_getip($ptype,$bid,$ipdid){
        if (empty ( $ptype ) || empty ( $bid )) {
            throw new GiantException ( "ptype或者bid为空" );
        }
        $par = array ();
        $par ['ptype'] = $ptype;
        $par ['bid'] = $bid;
        $par ['ipdid'] = $ipdid;
        $par ['tid'] = '34';//获取IP绑定进度
        return $this->init_params_send ( $par );
    }
    /**
     * 快云服务器解绑IP
     *
     * @param string $ptype
     *        	接口ptype参数
     * @param int $bid
     *        	接口bid参数
     * @param int $ipdid
     *        	接口did参数
     * @throws GiantException
     * @return string 接口返回值 json 或者 xml
     */
    public function cloudserver_relieve($ptype,$bid,$ipdid){
        if (empty ( $ptype ) || empty ( $bid )) {
            throw new GiantException ( "ptype或者bid为空" );
        }
        $par = array ();
        $par ['ptype'] = $ptype;
        $par ['bid'] = $bid;
        $par ['ipdid'] = $ipdid;
        $par ['tid'] = GiantAPIParams::TID_IP_RELIEVE;//快云服务器解绑IP
        return $this->init_params_send ( $par );
    }
	/**
	 * 续费业务，默认续费一年
	 * @param string $ptype 接口ptype参数
	 * @param int $bid 接口bid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function renewals($ptype, $bid, $yid,$usetype = -1) {
		if (empty ( $ptype ) || empty ( $bid )) {
			throw new GiantException ( "ptype或者bid为空" );
		}
		
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		if (! is_null ( $yid )) {
			$par ['yid'] = $yid;
		}
		$par ['tid'] = GiantAPIParams::TID_RENEWALS;
		if(in_array($usetype,array(1,2,3,4))){
			$par ['usetype'] = $usetype;
		}
        if($ptype == "clouddb"){
            if(is_numeric($usetype) && $usetype+0 >= 0){
                $par ['usetype'] = intval(ceil($usetype+0));
            }
        }
		return $this->init_params_send ( $par );
	}
	/**
	 * 续费快云服务器业务，默认续费一年
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function cloudserver_renewals($ptype,$pname, $bid, $yid,$usetype=-1) {
		if (empty ( $ptype ) || empty ( $bid )) {
			throw new GiantException ( "ptype或者bid为空" );
		}
	
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname']=$pname;
		$par ['bid'] = $bid;
		if (! is_null ( $yid )) {
			$par ['yid'] = $yid;
		}
		$par ['tid'] = GiantAPIParams::TID_RENEWALS;
        if(is_numeric($usetype) && $usetype+0 >= 0){
            $par ['usetype'] = intval(ceil($usetype+0));
        }
		return $this->init_params_send ( $par );
	}
	/**
	 * 续费快云服务器业务，默认续费一年
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function cloudserverip_renewals($ptype,$pname, $bid, $yid) {
		if (empty ( $ptype ) || empty ( $bid )) {
			throw new GiantException ( "ptype或者bid为空" );
		}
	
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		$par ['pname']=$pname;
		if (! is_null ( $yid )) {
			$par ['yid'] = $yid;
		}
		$par ['tid'] = GiantAPIParams::TID_RENEWALS;
		return $this->init_params_send ( $par );
	}
	/**
	* IP升级
	* @date: 2017年1月10日 上午11:49:13
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function cloudserverip_upgrade($ptype,$pname,$bid,$daikuan,$usetype=-1){
	    if (empty ( $ptype ) || empty ( $bid )) {
	        throw new GiantException ( "ptype或者bid为空" );
	    }
	    $par = array ();
	    $par ['ptype'] = $ptype;
	    $par ['bid'] = $bid;
	    $par ['pname']=$pname;
	    $par ['bandwidth'] = $daikuan;
	    $par ['tid'] = GiantAPIParams::TID_UPGRADE;
        if(is_numeric($usetype) && $usetype+0 >= 0){
            $par ['usetype'] = intval(ceil($usetype+0));
        }
	    return $this->init_params_send ( $par );
	}
	/**
	 * 升级业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function upgrade($ptype, $pname, $bid,$system_type) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid )) {
			throw new GiantException ( "ptype、pname或者bid为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		if($system_type!=null){
		$par['systemType'] = $system_type;
		}
		$par ['tid'] = GiantAPIParams::TID_UPGRADE;
		return $this->init_params_send ( $par );
	}
	/**
	* 主机升级验证域名是否备案
	* @date: 2017年1月11日 下午3:44:03
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function proving($ptype,$bid){
	    $par = array ();
	    $par ['ptype'] = $ptype;
	    $par ['bid'] = $bid;
	    $par ['tid'] = GiantAPIParams::TID_PROVING_TYPE;
	    return $this->init_params_send ( $par );
	}
	/**
	 * 获取虚拟主机FTP密码
	 * @date: 2017年1月11日 下午3:44:03
	 * @author: Lyubo
	 * @param: variable
	 * @return:
	 */
	public function GetFtpPwd($bid){
	    $par = array ();
	    $par ['ptype'] = GiantAPIParams::PTYPE_SELF;
	    $par ['bid'] = $bid;
	    $par ['tid'] = GiantAPIParams::TID_VHOST_FTPPWD;
	    return $this->init_params_send ( $par );
	}
	/**
	 * 获取业务信息
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function get_business_info($ptype, $bid) {
		if (empty ( $ptype ) || empty ( $bid )) {
			throw new GiantException ( "ptype或者bid为空" );
		}
		
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		$par ['tid'] = GiantAPIParams::TID_GET_BUSINESS_INFO;
		return $this->init_params_send ( $par );
	}
	
	/**
	 * 重启 ，只支持云VPS操作
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function restart($ptype, $bid) {
		if (empty ( $ptype ) || empty ( $bid )) {
			throw new GiantException ( "ptype或者bid为空" );
		}
		
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		$par ['tid'] = GiantAPIParams::TID_RESTART;
		return $this->init_params_send ( $par );
	}
	/**
	 * 根据订单ID获取业务ID
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function get_business_id_order_id($ptype, $did) {
		if (empty ( $ptype ) || empty ( $did )) {
			throw new GiantException ( "ptype或者bid为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = GiantAPIParams::TID_GET_BUSINESS_ID_ORDER_ID;
		return $this->init_params_send ( $par );
	}
	
	/**
	 * 增值
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param int $size
	 *        	接口size参数 增值大小
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function appreciation($ptype, $pname, $bid, $size) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid )) {
			throw new GiantException ( "ptype||pname||bid为空" );
		}
		
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['size'] = $size;
		$par ['tid'] = GiantAPIParams::TID_APPRECIATION;
		return $this->init_params_send ( $par );
	}
	
	/**
	 * 获取增值信息
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param int $size
	 *        	接口size参数 增值大小
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function get_appreciation_info($ptype, $bid) {
		if (empty ( $ptype ) || empty ( $bid )) {
			throw new GiantException ( "ptype或者bid为空" );
		}
		
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		$par ['tid'] = GiantAPIParams::GET_APPRECIATION_INFO;
		return $this->init_params_send ( $par );
	}
	
	/**
	 * 试用业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function _try($ptype, $pname,$area_code=null, $system_type=null) {
		if (empty ( $ptype ) || empty ( $pname )) {
			throw new GiantException ( "ptype或者pname为空" );
		}
		
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['tid'] = GiantAPIParams::TID_TRY;
		//add by xuanyd 20140731
	   if($system_type != null && $ptype != 'fastcloudvps' && $ptype !='vps' && $ptype !='cloudspace'){
			$par['systemType'] = $system_type;
		}else{
		    $par['input_name'] = $system_type;
		}
		if ($area_code != null) {
			$par ['area_code'] = $area_code;
		}
		return $this->init_params_send ( $par );
	}
	
	/**
	 * 转正业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param int $yid
	 *        	接口yid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function onformal($ptype, $bid, $yid) {
		if (empty ( $ptype ) || empty ( $bid ) || empty ( $yid )) {
			throw new GiantException ( "ptype或者bid或yid为空" );
		}
		
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		$par ['yid'] = $yid;
		$par ['tid'] = GiantAPIParams::TID_POSITIVE;
		return $this->init_params_send ( $par );
	}
	/**
	 * 设置站点首页
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param string $input_namr
	 *        	接口input_name参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function defaultpage($ptype, $pname, $bid, $input_name) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name )) {
			throw new GiantException ( "ptype或者pname或者bid或者input_name为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		$par ['input_name'] = $input_name;
		return $this->init_params_send ( $par );
	}
	/**
	 * 修改FTP密码
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param string $password
	 *        	接口password参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function changeftppass($ptype, $pname, $bid, $password) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $password )) {
			throw new GiantException ( "ptype或pname或bid或password为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['password'] = $password;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 站点同步
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param string $password
	 *        	接口password参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function websynchronism($ptype, $pname, $bid) {
	    if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid )) {
	        throw new GiantException ( "ptype或pname或bid为空" );
	    }
	    $par = array ();
	    $par ['ptype'] = $ptype;
	    $par ['pname'] = $pname;
	    $par ['bid'] = $bid;
	    $par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
	    return $this->init_params_send ( $par );
	}
	/**
	 * 关闭开启站点
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param int $input_name
	 *        	接口input_name参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function changesite($ptype, $pname, $bid, $input_name) {
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['input_name'] = $input_name;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 添加绑定域名
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param string $input_name
	 *        	接口input_name参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function bindingdomain($ptype, $pname, $bid, $input_name) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name )) {
			throw new GiantException ( "ptype或pname或bid或input_name为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pnamr'] = $pname;
		$par ['bid'] = $bid;
		$par ['input_name'] = $input_name;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 错误页面定义
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param string $input_name
	 *        	接口input_name参数
	 * @param int $errorpage_code
	 *        	接口errorpage_code参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function errpage($ptype, $pname, $bid, $input_name, $errorpage_code) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name ) || empty ( $errorpage_code )) {
			throw new GiantException ( "ptype或pname或bid或input_name或errorpage_code为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['input_name'] = $input_name;
		$par ['errorpage_code'] = $errorpage_code;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 开通虚拟主机数据库
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param string $input_name
	 *        	接口input_name参数
	 * @param int $database_type
	 *        	接口database_type参数
	 * @param string $password
	 *        	接口password参数
	 * @param int $size
	 *        	接口size参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function opendatabase($ptype, $pname, $bid, $input_name, $database_type, $password, $size) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name ) || empty ( $database_type ) || empty ( $password ) || empty ( $size )) {
			throw new GiantException ( "ptype或pname或bid或input_name或database_type或password或size为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['input_name'] = $input_name;
		$par ['database_type'] = $database_type;
		$par ['password'] = $password;
		$par ['size'] = $size;
		$par ['tid'] = GiantAPIParams::TID_ADDITIONAL;
		return $this->init_params_send ( $par );
	}
	/**
	 * 开通虚拟主机邮局
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param string $input_name
	 *        	接口input_name参数
	 * @param string $password
	 *        	接口password参数
	 * @param int $size
	 *        	接口size参数
	 * @param string $input_date
	 *        	接口input_date参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function openpostoffice($ptype, $pname, $bid, $input_name, $password, $size, $input_date = null) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name ) || empty ( $password ) || empty ( $size )) {
			throw new GiantException ( "ptype或pname或bid或input_name或password或size为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['input_name'] = $input_name;
		$par ['password'] = $password;
		$par ['size'] = $size;
		$par ['input_date'] = $input_date;
		$par ['tid'] = GiantAPIParams::TID_ADDITIONAL;
		return $this->init_params_send ( $par );
	}
	/**
	 * 删除绑定域名
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param string $pname
	 *        	接口pname参数
	 * @param int $bid
	 *        	接口bid参数
	 * @param string $input_name
	 *        	接口input_name参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function removedomain($ptype, $pname, $bid, $input_name) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name )) {
			throw new GiantException ( "ptype或pname或bid或input_name为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['input_name'] = $input_name;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 域名查询
	 *
	 * @param $domainname 域名        	
	 * @param $suffix 后缀列表        	
	 */
	public function domainquery($domainname, $suffix) {
		if (empty ( $domainname ) || empty ( $suffix )) {
			throw new GiantException ( "domainname或suffix为空" );
		}
		$domain = "";
		// 拼接域名列表
		for($i = 0; $i < count ( $suffix ); $i ++) {
			if ($i == 0) {
				$domain .= urlencode ( $domainname . $suffix [$i] );
			} else {
				$domain .= "," . urlencode ( $domainname . $suffix [$i] );
			}
		}
		$result = HttpClient::quickGet ( DOMAIN_URL . "?domain=" . $domain );
		return mb_convert_encoding ( $result, "UTF-8", "GB2312" );
	}
	/**
	 * 域名注册
	 *
	 * @param string $domainname
	 *        	接口domainname参数
	 * @param string $domainparams
	 *        	接口domainparams参数
	 * @param int $yid
	 *        	接口yid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function domainregistration($domainname, $domainparams, $yid, $api_type) {
		if (empty ( $domainname ) || empty ( $domainparams ) || empty ( $yid )) {
			throw new GiantException ( "pname或domainname或suffix或domainparams或yid为空" );
		}
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_DOMAIN;
		$par ['pname'] = GiantAPIParams::PNAME_DOMAIN_REGISTRATION;
		$par ['domainname'] = urlencode ( $domainname );
		$par ['yid'] = $yid;
		//添加接口编号(3 万网，4 新网，5 中国数据，不提供此值默认为中国数据)
		$par ['input_name'] = $api_type;
		//add end
		$par ['domainparams'] = urlencode ( $domainparams );
		$par ['tid'] = GiantAPIParams::TID_DOMAIN;
		return $this->init_params_send ( $par );
	}
	/**
	 * 获取用户域名业务列表
	 *
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function getdomainlist() {
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_DOMAIN;
		$par ['pname'] = GiantAPIParams::PNAME_DOMAIN_LIST;
		$par ['tid'] = GiantAPIParams::TID_DOMAIN;
		return $this->init_params_send ( $par );
	}
	/**
	 * 获取用户域名业务详细信息
	 *
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function getdomaininfo($id) {
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_DOMAIN;
		$par ['pname'] = "getbusinfo";
		$par ['tid'] = GiantAPIParams::TID_DOMAIN;
		$par ['bid'] = $id;
		return $this->init_params_send ( $par );
	}
	/**
	 * 获取用户域名业务详细信息
	 *
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function domaininfoedit($id,$params) {
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_DOMAIN;
		$par ['pname'] = "modifyinfo";
		$par ['tid'] = GiantAPIParams::TID_DOMAIN;
		$par ['domainparams'] = $params;
		return $this->init_params_send ( $par );
	}
	/**
	 * 修改域名管理密码
	 *
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function domainpwdedit($business_id,$mm) {
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_DOMAIN;
		$par ['pname'] = "modifypwd";
		$par ['tid'] = GiantAPIParams::TID_DOMAIN;
		$par ['bid'] = $business_id;
		$par ['password'] = $mm;
		return $this->init_params_send ( $par );
	}
	/**
	 * 域名续费
	 *
	 * @param int $bid
	 *        	接口bid参数
	 * @param int $yid
	 *        	接口yid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function domainrenewals($bid, $yid) {
		if (empty ( $bid ) || empty ( $yid )) {
			throw new GiantException ( "ptype或pname或bid或yid为空" );
		}
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_DOMAIN;
		$par ['pname'] = GiantAPIParams::PNAME_DOMAIN_RENEWALS;
		$par ['bid'] = $bid;
		$par ['yid'] = $yid;
		$par ['tid'] = GiantAPIParams::TID_DOMAIN;
		return $this->init_params_send ( $par );
	}
	/**
	 * 域名跳转控制面板
	 *
	 * @param int $bid
	 *        	接口bid参数
	 * @param int $yid
	 *        	接口pname参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function domainskip($bid){
	    if (empty ( $bid )) {
	        throw new GiantException ( "ptype或pname或bid或yid为空" );
	    }
	    $par = array ();
	    $par ['ptype'] = GiantAPIParams::PTYPE_DOMAIN;
	    $par ['pname'] = GiantAPIParams::PNAME_DOMAIN_SKIP;
	    $par ['bid'] = $bid;
	    $par ['tid'] = GiantAPIParams::TID_DOMAIN;
	    return $this->init_params_send ( $par );
	}
	/**
	 * 创建站点（现针对云空间）
	 *
	 * @param int $bid
	 *        	接口业务编号参数
	 * @param int $site_capacity
	 *        	接口站点容量
	 * @param int $site_flow
	 *        	接口站点流量
	 * @param string $password
	 *        	接口站点密码
	 * @param int $input_name
	 *        	接口站点域名
	 */
	public function createsitecs($ptype, $pname, $bid, $site_capacity, $site_flow, $password, $input_name) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $site_capacity )  || empty ( $password ) || empty ( $input_name )) {
		    throw new GiantException ( "ptype或pname或bid或site_capacity或site_flow或password或input_name为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['site_capacity'] = $site_capacity;
		$par ['site_flow'] = $site_flow;
		$par ['password'] = $password;
		$par ['input_name'] = $input_name;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 设置/删除站点默认页(现针对云空间)
	 *
	 * @param
	 *        	string pname 操作名称：defaultpage（设置默认页）deldefaultpage（删除默认页）
	 * @param int $bid
	 *        	业务编号
	 * @param String $input_name
	 *        	首页名称（html|htm|asp|aspx|php|jsp|shtml）
	 * @param int $site_id
	 *        	站点编号
	 * @throws GiantException
	 */
	public function defaultpagecs($ptype, $pname, $bid, $input_name, $site_id) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name ) || empty ( $site_id ) || empty ( $input_name )) {
			throw new GiantException ( "ptype或pname或bid或site_id或input_name为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['site_id'] = $site_id;
		$par ['input_name'] = $input_name;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 开启关闭站点（现针对云空间）
	 *
	 * @param string $ptype
	 *        	产品类型
	 * @param string $pname
	 *        	产品名称
	 * @param int $bid
	 *        	业务编号
	 * @param int $site_id
	 *        	站点编号
	 * @param int $operation_type
	 *        	操作类型 0：关闭1：开启
	 */
	public function changesitecs($ptype, $pname, $bid, $site_id, $operation_type) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $site_id ) || empty ( $operation_type )) {
			throw new GiantException ( "ptype或pname或bid或site_id或operation_type为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['site_id'] = $site_id;
		$par ['operation_type'] = $operation_type;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 修改站点FTP密码（现针对云空间）
	 *
	 * @param string $ptype
	 *        	产品类型
	 * @param string $pname
	 *        	操作名称
	 * @param int $bid
	 *        	业务编号
	 * @param int $site_id
	 *        	站点编号
	 * @param string $password
	 *        	新密码
	 * @throws GiantException
	 */
	public function changeftppasscs($ptype, $pname, $bid, $site_id, $password) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $site_id ) || empty ( $password )) {
			throw new GiantException ( "ptype或pname或bid或site_id或password为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['site_id'] = $site_id;
		$par ['password'] = $password;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 绑定/删除绑定域名（现针对云空间）
	 *
	 * @param string $ptype
	 *        	产品类型
	 * @param string $pname
	 *        	操作名称bindingdomain(绑定域名)removedomain（删除绑定域名）
	 * @param unknown_type $bid        	
	 * @param unknown_type $input_name        	
	 * @param unknown_type $site_id        	
	 * @throws GiantException
	 * @return Ambigous <string, boolean>
	 */
	public function bindingdomaincs($ptype, $pname, $bid, $input_name, $site_id) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name ) || empty ( $site_id ) || empty ( $input_name )) {
			throw new GiantException ( "ptype或pname或bid或site_id或input_name为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['site_id'] = $site_id;
		$par ['input_name'] = $input_name;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 站点设置错误页(现针对云空间)
	 *
	 * @param string $ptype
	 *        	产品类型
	 * @param string $pname
	 *        	操作名称
	 * @param int $bid
	 *        	业务编号
	 * @param string $input_name
	 *        	错误页面 错误页面以"/"开头,后缀必须为html|htm|asp|aspx|php|jsp|shtml
	 * @param int $site_id
	 *        	站点编号
	 * @param int $errorpage_code
	 *        	错误编号 暂时仅限403,404,500,503
	 * @throws GiantException
	 * @return Ambigous <string, boolean>
	 */
	public function errpagecs($ptype, $pname, $bid, $input_name, $site_id, $errorpage_code) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name ) || empty ( $site_id ) || empty ( $input_name ) || empty ( $errorpage_code )) {
			throw new GiantException ( "ptype或pname或bid或site_id或input_name或errorpage_code为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['site_id'] = $site_id;
		$par ['input_name'] = $input_name;
		$par ['errorpage_code'] = $errorpage_code;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 创建数据库（现针对云空间）
	 *
	 * @param string $ptype
	 *        	产品类型
	 * @param string $pname
	 *        	操作名称
	 * @param int $bid
	 *        	业务编号
	 * @param string $input_name
	 *        	数据库名称
	 * @param int $site_id
	 *        	站点编号
	 * @param string $password
	 *        	数据库密码
	 * @param int $db_capacity
	 *        	数据库容量
	 * @throws GiantException
	 * @return Ambigous <string, boolean>
	 */
	public function createdbcs($ptype, $pname, $bid, $input_name, $password, $db_capacity) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $input_name ) || empty ( $input_name ) || empty ( $password ) || empty ( $db_capacity )) {
			throw new GiantException ( "ptype或pname或bid或input_name或errorpage_code或password或db_capacity为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['input_name'] = $input_name;
		$par ['password'] = $password;
		$par ['db_capacity'] = $db_capacity;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 数据库扩容（现针对云空间）
	 *
	 * @param string $ptype
	 *        	产品类型
	 * @param string $pname
	 *        	操作名称
	 * @param int $bid
	 *        	业务编号
	 * @param int $db_id
	 *        	数据库编号
	 * @param int $db_capacity
	 *        	数据库容量
	 * @throws GiantException
	 * @return Ambigous <string, boolean>
	 */
	public function extenddbcs($ptype, $pname, $bid, $db_id, $db_capacity) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $db_id ) || empty ( $db_capacity )) {
			throw new GiantException ( "ptype或pname或bid或db_id或db_capacity为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['db_id'] = $db_id;
		$par ['db_capacity'] = $db_capacity;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	/**
	 * 开启关闭数据库（现针对云空间）
	 * 
	 * @param string $ptype
	 *        	产品类型
	 * @param string $pname
	 *        	操作名称
	 * @param int $bid
	 *        	业务编号
	 * @param int $db_id
	 *        	数据库编号
	 * @param int $operation_type
	 *        	操作类型0：关闭数据库1：开启数据库
	 * @throws GiantException
	 * @return Ambigous <string, boolean>
	 */
	public function changedbcs($ptype, $pname, $bid, $db_id, $operation_type) {
		if (empty ( $ptype ) || empty ( $pname ) || empty ( $bid ) || empty ( $db_id ) || empty ( $operation_type )) {
			throw new GiantException ( "ptype或pname或bid或db_id或operation_type为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bid'] = $bid;
		$par ['db_id'] = $db_id;
		$par ['operation_type'] = $operation_type;
		$par ['tid'] = GiantAPIParams::TID_SITE_MANAGMENT;
		return $this->init_params_send ( $par );
	}
	
	/**
	 * 获取登录自助管理平台Key
	 */
	public function get_self_key($service_code, $service_code_pwd) {
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_SELF;
		$par ['tid'] = GiantAPIParams::TID_GET_BUSINESS_INFO;
		$par ['service_code'] = $service_code;
		$par ['service_code_pwd'] = $service_code_pwd;
		$par ['input_name'] = $ip = get_userip();
		return $this->init_params_send ( $par );
	}
	
	/**
	 * 获取服务码
	 */
	public function get_server_code($ptype, $pname, $bis_sign, $bid) {
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_SELF;
		$par ['tid'] = GiantAPIParams::TID_GET_SERVICE_CODE_ID;
		$par ['input_name'] = $ptype;
		$par ['pname'] = $pname;
		$par ['bis_sign'] = $bis_sign;
		$par ['bid'] = $bid;
		return $this->init_params_send ( $par );
	}
	/**
	 * 根据订单编号获取业务信息
	 * 
	 * @param
	 *        	$ptype
	 * @param
	 *        	$did
	 */
	public function get_business_info_order_id($ptype, $did) {
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['tid'] = GiantAPIParams::TID_GET_BUSINESS_INFO;
		$par ['did'] = $did;
		return $this->init_params_send ( $par );
	}
	/**
	 * 调用主站域名查询方法
	 * @param unknown_type $domainname
	 * @param unknown_type $suffix
	 * @throws GiantException
	 */
	public function new_domain_quary($domainname, $suffix){
		if (empty ( $domainname ) || empty ( $suffix )) {
			throw new GiantException ( "domainname或suffix为空" );
		}
		$suffixs = "";
		// 拼接域名列表
		 for($i = 0; $i < count ( $suffix ); $i ++) {
			if ($i == 0) {
				$suffixs .= urlencode (  $suffix [$i] );
			} else {
				$suffixs .= "-" . urlencode ( $suffix [$i] );
			}
		} 
		$suffixs=mb_convert_encoding ( $suffixs, "UTF-8", "GB2312" );
		$domainname=mb_convert_encoding ( urlencode($domainname), "UTF-8", "GB2312" );
		$par = array ();
		$par ['ptype'] = GiantAPIParams::PTYPE_DOMAIN;
		$par ['suffix'] = $suffixs;
		$par ['domainname'] = $domainname;
		$par ['tid'] = GiantAPIParams::TID_DOMAIN;
		$par ['pname'] =GiantAPIParams::PNAME_DOMAIN_QUERY;
		return $this->init_params_send ( $par );
	}
	/**
	 * 支付宝验证notify_id合法性
	 */
	public function getSignVeryfy($veryfyurl) {
		if (empty ( $veryfyurl )) {
			throw new GiantException ( "veryfyurl为空" );
		}
		$result = HttpClient::quickGet ($veryfyurl );
		return $result;
	}
	/**
	 * 同步业务信息
	 * @param unknown_type $ptype
	 * @param unknown_type $bid
	 * @return Ambigous <string, boolean>
	 */
	public function syncBusinessInfo($ptype,$bid,$pname){
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['tid'] = GiantAPIParams::TID_SYNC_BUSINESS_INFO;
		$par ['bid'] = $bid;
		$par ['pname'] = $pname;
		return $this->init_params_send ( $par );
	}
	/**
	 * 同步所有业务信息
	 * @param unknown $ptype
	 * @param unknown $tid
	 * @return Ambigous <string, boolean>
	 */
	public function  syscAllBusinessInfo($ptype,$tid,$yid,$pname){
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['tid'] =$tid;
		//yid:指定同步时间
		$par ['yid'] =$yid;
		$par ['pname'] = $pname;
		return $this->init_params_send ( $par );
	}
	/**
	 * 开通虚拟主机企业型业务
	 *
	 * @param string $ptype
	 *        	接口ptype参数
	 * @param int $did
	 *        	接口did参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function open_qy($ptype, $did, $input_name ,$baNo) {
		if (empty ( $ptype ) || empty ( $did )) {
			throw new GiantException ( "ptype或者did为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = GiantAPIParams::TID_OPEN;
		return $this->init_params_send ( $par );
	}
	/**
	 * 获取根域名
	 */
	public function RealDomain($domain,$ptype){
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype为空" );
		}
		$par = array ();
		$par ['domain'] = $domain;
		$par ['ptype'] = $ptype;
		$par ['tid'] = "27";//25为获取域名的交易ID
		return $this->init_params_send ( $par );
	}
	/**
	 * 域名绑定
	 */
	public function bdDomain($ptype,$did,$progress,$domains,$applyName="",$email="",$mobile="",$keytype=""){
		if (empty ( $ptype ) || empty($did)) {
			throw new GiantException ( "ptype为空或订did为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = GiantAPIParams::TID_OPEN;
		$par ['progress'] = $progress;
		$par ['domain'] = $domains;
		if(!empty($applyName)){
			$par ['applyName'] = $applyName;
		}
		if(!empty($email)){
			$par ['email'] = $email;
		}
		if(!empty($mobile)){
			$par ['mobile'] = $mobile;
		}
		if(!empty($keytype)){
			$par ['keytype'] = $keytype;
		}
		return $this->init_params_send ( $par );
	}
	/**
	 * 域名绑定
	 */
	public function domainvalidate($ptype,$domain){
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype为空" );
		}
		$par = array();
		$par ['ptype'] = $ptype;
		$par ['domain'] = $domain;
		$par ['tid'] = 21;//21是获取邮箱的TID
		return $this->init_params_send ( $par );
	}
	/**
	 * 填写资料
	 */
	public function fillinfo($ptype,$did,$progress,$data){
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype为空" );
		}
		$par = array();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = 2;//2是开通SSL证书（OV Pre ，EV pre）-填写资料
        $par ['progress'] = $progress;
        $par ["applyName"] = $data["applyName"];
        $par ["email"] = $data["email"];
        $par ["mobile"] = $data["mobile"];
		if(!empty($data["QQ"])){
			$par ["QQ"] = $data["QQ"];
		}
        $par ["compType"] = $data["compType"];
        $par ["compName"] = $data["compName"];
        $par ["compEmail"] = $data["compEmail"];
        $par ["compPhone"] = $data["compPhone"];
		return $this->init_params_send ( $par );
	}
	/**
	 * 上传资料
	 */
	public function uploadinfo($ptype,$did,$progress,$data){
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype为空" );
		}
		$par = array();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = 2;//2是开通
		$par ['progress'] = $progress;//开通第三步SSL证书（OV Pre ，EV pre）-上传资料
		$par ["letter"] = $data["letterImg"];
		$par ["applyIdImg"] = $data["applyIdImg"];
		if(isset($data["busiLicenceImg"])){
			$par ["busiLicence"] = $data["busiLicenceImg"];
		}
		if(isset($data["billImg"])){
			$par ["bill"] = $data["billImg"];
		}
		return $this->init_params_send ( $par );
	}
	/**
	 * 用户验证
	 */
	public function userValidate($ptype,$did,$progress,$data){
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype为空" );
		}
		$par = array();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = 2;//2是开通
		$par ['progress'] = $progress;//开通第三步SSL证书（OV Pre ，EV pre）-上传资料
		$par ["compName"] = $data["compName"];
		$par ["idnum"] = $data["idnum"];
		$par ["applyName"] = $data["applyName"];
		return $this->init_params_send ( $par );
	}
	/**
	 * 域名邮箱验证
	 */
	public function domainverification($ptype,$bid,$email,$yzym){
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype为空" );
		}
		$par = array();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		$par ['domain'] = $yzym;
		$par ['email'] = $email;
		$par ['tid'] = 22;//22是域名邮箱验证的TID
		return $this->init_params_send ( $par );
	}
	/**
	 * 验证邮箱验证码
	 */
	public function validatedomain($ptype,$bid,$domain,$code){
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype为空" );
		}
		$par = array();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		$par ['domain'] = $domain;
		$par ['code'] = $code;
		$par ['tid'] = 23;//23是域名邮箱验证的TID
		return $this->init_params_send ( $par );
	}
	/**
	 * 获取SSL开通信息
	 */
	public function ssl_getinfo($ptype,$did,$tid){
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype为空" );
		}
		$par = array();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = $tid;//23是域名邮箱验证的TID
		return $this->init_params_send ( $par );
	}
	
/*********************************************************快云服务器****************************************************/
	/**
	* 快云服务器获取操作系统
	* @date: 2016年11月25日 下午3:06:32
	* @author: Lyubo
	* @param: $ptype
	* @param: $pname
	* @param: $tid
	*/
	public function CloudserverOsType($ptype,$pname,$tid,$area_code=4001) {
	    if (empty($ptype) || empty($pname)) {
	        throw new GiantException ( "ptype或者pname为空" );
	    }
	    $par = array ();
	    $par ['ptype'] = $ptype;
	    $par ['pname'] = $pname;
	    $par ['tid'] = $tid;
		$par ['area_code'] = $area_code;
	    return $this->init_params_send ( $par );
	}

	/*********************************************************快云数据库****************************************************/
	/**
	 * 购买快云数据库业务
	 *
	 * @param string $ptype 接口ptype参数
	 * @param string $pname 接口pname参数
	 * @param int $yid 接口yid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function clouddb_buy($ptype,$yid,$area_code,$buy_info,$usetype = -1) {
		if (empty ( $ptype )) {
			throw new GiantException ( "ptype或者pname为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
        $par ['tid'] = GiantAPIParams::TID_BUY;
        $par ['pname'] =$buy_info['version'];
        $par ['memory'] = $buy_info['memsize'];
        $par ['disk'] = $buy_info['spacesize'];
        $par ['input_name'] = 1;
        $par ['yid'] = $yid;
        $par ['area_code'] = $area_code;
        if(is_numeric($usetype) && $usetype+0 >= 0){
            $par ['usetype'] = intval(ceil($usetype+0));
        }
		return $this->init_params_send ( $par );
	}
	/**
	 * 快云数据库开通业务
	 * @author: Guopeng
	 * @param string $ptype 接口ptype参数
	 * @param int $did 接口did参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function clouddb_open($ptype,$did,$open_info) {
		if (empty ( $ptype ) || empty ( $did )) {
			throw new GiantException ( "ptype或者did为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
        $par ['tid'] = GiantAPIParams::TID_OPEN;
		$par ['did'] = $did;
		$par ['input_name'] = $open_info['name'];
        $par ['applyName'] = $open_info['user'];
        $par ['password'] = $open_info['password'];
        $par ['usetype'] = $open_info['type'];
		return $this->init_params_send ( $par );
	}
	/**
	 * 获取快云服务器开通进度
	 * @author: Guopeng
	 * @param string $ptype 接口ptype参数
	 * @param int $did 接口did参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function clouddb_getinfo($ptype,$did,$tid) {
		if (empty ( $ptype ) || empty ( $did )) {
			throw new GiantException ( "ptype或者did为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['did'] = $did;
		$par ['tid'] = $tid;
		return $this->init_params_send($par);
	}
	/**
	 * 升级快云数据库业务
	 * @author: Guopeng
	 * @param string $ptype 接口ptype参数
	 * @param string $pname 接口pname参数
	 * @param int $bid 接口bid参数
	 * @throws GiantException
	 * @return string 接口返回值 json 或者 xml
	 */
	public function clouddb_upgrade($ptype,$bid,$data) {
		if (empty ( $ptype ) || empty ( $bid )) {
			throw new GiantException ( "ptype或者bid为空" );
		}
		$par = array ();
		$par ['ptype'] = $ptype;
		$par ['bid'] = $bid;
		$par ['memory'] = $data['memory'];
		$par ['disk'] = $data['disk'];
		$par ['tid'] = GiantAPIParams::TID_UPGRADE;
		return $this->init_params_send ( $par );
	}
    /**
     * 获取快云服务器升级进度
     * @author: Guopeng
     * @param string $ptype 接口ptype参数
     * @param int $did 接口did参数
     * @throws GiantException
     * @return string 接口返回值 json 或者 xml
     */
    public function clouddb_getupinfo($ptype,$bid,$tid) {
        if (empty ( $ptype ) || empty ( $bid )) {
            throw new GiantException ( "ptype或者bid为空" );
        }
        $par = array ();
        $par ['ptype'] = $ptype;
        $par ['bid'] = $bid;
        $par ['tid'] = $tid;
        return $this->init_params_send($par);
    }
	/*********************************************************快云数据库****************************************************/
}
