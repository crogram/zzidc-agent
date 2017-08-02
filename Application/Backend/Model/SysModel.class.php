<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

/**
 * -------------------------------------------------------
 * | 产品模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class SysModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_sys';
	//新增是允许写入的字段
	protected $insertFields = [
			'sys_type', 'sys_key', 'sys_value','sys_des'
	];
	//更新是允许更新的字段
	protected $updateFields = [
			'sys_type', 'sys_key', 'sys_value','sys_des'
	];
	
	/**
	 * ---------------------------------------------------
	 * | 产品类型对应的业务表名
	 * | @时间: 2016年11月24日 下午2:34:09
	 * ---------------------------------------------------
	 */
	private $product_type_with_business_table_map = [
			'vps' => 'vps_business',
			'cloudhost' => 'virtualhost_business',
			'host' => 'virtualhost_business',
			'domain' => 'domain_business',
			'hkhost' => 'virtualhost_business',
			'endomain' => 'domain_business',
			'cndomain' => 'domain_business',
			'managehire' => '',
			'cloudspace' => 'cloudspace_business',
			'fastcloudvps' => 'fast_vps_business',
			'dedehost' => 'virtualhost_business',
			'usahost' => 'virtualhost_business',
			'cloudVirtual' => 'virtualhost_business',
			'cloudserver' => 'cloudserver_business',
			'cloudserverIP' => 'cloudserver_business_ip',
            'clouddb' => 'clouddb_business',
			'ssl' => 'ssl_business',
	];
	
	//sys表的sys_type所对应的
	const SYSTEM_CONFIG = 1;		//系统配置，站点的一些基本信息比如地址。电话。qq等	done
	const MAIL = 13;	//网站邮箱的配置	done
	const TOP_BAR = 3;		//整站特殊的网站头部的紧急或特殊活动的公告
	const ABOUT = 4;			//关于我们的介绍页	done
	const PAYMENT = 5;	//付款方式的介绍页	done
	const SEO = 12;	//seo优化关键字的设置	done
	const DEFAULT_DOMAIN = 10;	//默认域名
	const HOT_SELLING = 11;		//热销商品
	
	private $system_config = [];
	
	/**
	 * ----------------------------------------------
	 * | 初始化的时候  将网站的默认配置项加载进来
	 * | @时间: 2016年11月13日 下午3:53:51
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _initialize(){
		parent::_initialize();
		
		$this->system_config = WebSiteConfig(self::SYSTEM_CONFIG);
	}
	
	
	
	
	
	/**********************************新增更新回调/开始***********************************/
	
	/**
	 * ----------------------------------------------
	 * | 插入数据之前的回调
	 * | @时间: 2016年10月17日 上午11:28:21
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _before_insert(&$data, $options){

		//处理问题内容的编码，统一转换成utf-8的编码，然后查看字数
		$sys_value = mb_convert_encoding($data['sys_value'], 'utf-8');
		if ( mb_strlen($sys_value, 'utf-8') >= 10000*3) {
			$this->error = '内容最多只有10000个字';
			return false;
		}
	}

	/**
	 * ----------------------------------------------
	 * | 插入成功后的回调
	 * | @时间: 2016年10月17日 上午11:28:50
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _after_insert($data, $options){
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新数据之前做的操作
	 * | @时间: 2016年10月17日 上午11:26:36
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _before_update(&$data, $options){
		
		//处理问题内容的编码，统一转换成utf-8的编码，然后查看字数
		$sys_value = mb_convert_encoding($data['sys_value'], 'utf-8');
		if ( mb_strlen($sys_value, 'utf-8') >= 10000*3) {
			$this->error = '内容最多只有10000个字';
			return false;								
		}
		
		

	}

	/**
	 * ----------------------------------------------
	 * | 更新之后做的操作
	 * | @时间: 2016年10月17日 上午11:27:09
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _after_update($data, $options){
		
	}
	
	/**********************************新增更新回调/结束***********************************/
	
	/**
	 * ----------------------------------------------
	 * | 更新sys表的核心方法
	 * | @时间: 2016年11月11日 下午5:28:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function process($data, $type){
		$error = 0;
		$ok = 0;
		foreach ($data as $k => $v){
// 			if ( $v === 0 || !empty($v)){
			
// 			}
			$where[ 'sys_key' ] = [ 'eq', $k];
			$where[ 'sys_type' ] = [ 'eq', $type];
			//这里只修改有变动的
			if ($v != $this->system_config[$k]){		
				$res = $this->where($where)->setField( 'sys_value', clearXSS($v) );
				$res ===false ? $error++: $ok++;
			}
		
		}
		if (!$error){
			return true;
		}else {
			$this->error = '哎呦，出现了一些差错，在试一次吧！';
			return false;
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新网站配置的方法
	 * | @时间: 2016年11月11日 下午4:59:59
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function setup($data) {
		//拿到表单
		$form = strtolower($data['form']);
		unset($data['form']);
		switch ($form){
			case 'system':				
				$type = self::SYSTEM_CONFIG;
				if(isset($data['pay_sft'])){
				    $data['pay_sft'] = $data['pay_sft'];
				}else{
				    unset($data['pay_sft']);
				}
				if(isset($data['pay_cft'])){
				    $data['pay_cft'] = $data['pay_cft'];
				}else{
				    unset($data['pay_cft']);
				}
				if(isset($data['pay_zfb'])){
				    $data['pay_zfb'] = $data['pay_zfb'];
				}else{
				    unset($data['pay_zfb']);
				}
				if (isset($data['site_trial_times']) ){
					if ( ($data['site_trial_times']+0) <= 0){
						$this->error = '试用次数必须大于0的数组！';
						return false;
					}
				}
                if (isset($data['site_buy_vps']) ){
					if (!in_array($data['site_buy_vps']+0,array(0,1,2,3,4))){
						$data['site_buy_vps'] = 0;
					}else{
						$data['site_buy_vps'] = $data['site_buy_vps'];
					}
                }
                if (isset($data['site_buy_cloud']) ){
					if ($data['site_buy_cloud'] === "0" || $data['site_buy_cloud'] === 0){
						$data['site_buy_cloud'] = 0;
					}elseif($data['site_buy_cloud']+0 > 0){
						$data['site_buy_cloud'] = ceil($data['site_buy_cloud']+0);
					}else{
						$data['site_buy_cloud'] = -1;
					}
                }
				break;
			case 'mail':				
				$type = self::MAIL;
				break;
			case 'domain':				
				$type = self::DEFAULT_DOMAIN;
				//交换数据
				list($temp, $data) = [ $data, [] ];
				$data['default_domain'] = implode(',', array_merge($temp['others_domain'], $temp['chinese_domain']));
				break;
			case 'topbar':
				//顶部活动或紧急通知的处理，特殊一点
				$type = self::TOP_BAR;
				$where['sys_type'] = [ 'eq', $type ];
				//交换数据
				list($temp, $data) = [ $data, [] ];
				$data['sys_key'] = $temp['is_show']+0;
				$data['sys_value'] = $_POST['topbar'];
				$res = $this->where($where)->save($data);
				if ($res === false){
					$this->error = '哎呦，出现了一些差错，在试一次吧！';
					return false;
				}else {
					return  true;	
				}
				break;
			case 'seo':				
				$type = self::SEO;
				//交换数据
				list($temp, $data) = [ $data, [] ];
				//拼凑要修改的数据
				//去除用户输入的英文 " ! " 
				array_walk($temp, function (&$v, $k){
					$v = str_replace('!', '', $v);
				});
				$data[$temp['sys_key']] = implode('!', [ $temp['keywords'], $temp['description'], $temp['title'] ]);
				break;
			case 'payment':				
				$type = self::PAYMENT;
				$data['payment_content'] = $_POST['payment_content'];
				break;
			case 'about':				
				$type = self::ABOUT;
				$data['on_the_title'] = $_POST['on_the_title'];
				$data['on_the_content'] = $_POST['on_the_content'];
				break;
				//如果是修改logo或者是二维码
			case 'img':				
				$type = self::SYSTEM_CONFIG;
				
				//处理logo图片
				$input_name = 'logo_url';
				if(isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0){
					$path = $this->imgHandler($input_name);
					if (!$path){
						return false;
					}else {
						$data['logo_url'] = $path;
					}
				}
// 				dump($data);die;
				//处理二维码
				if (!empty($data['qr_url'])){
					//文本信息不为空时，创建二维码图片
					$qr['text'] = $data['qr_url'];		//二维码中保存的信息
					$qr['save_name'] = 'QRCode.png';	//二维码图片的名称
					$qr['logo_url'] = file_exists('.'.$data['logo_url'])? '.'.$data['logo_url']: file_exists('.'.$this->system_config['logo_url']) ? '.'.$this->system_config['logo_url']: '';	//二维码中要展示的logo图片；
					$qr['label_text'] = '';		//二维码中的提示语
					
					$path = $this->imgHandler($qr, true);
					if (!$path){
						return false;
					}else {
						$data['qr_url'] = $path;
					}
				}
				break;
				
		}
		return $this->process($data, $type);
	}
	
	
	
	
	
	/**
	 * ----------------------------------------------
	 * | 用于网站的设置的图片和二维码的处理
	 * | @时间: 2016年11月11日 下午6:16:04
	 * | @author: duanbin
	 * | @param: $input_name 二维码是要传入个数组，keys---text,save_name,logo_url,label_text;
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function imgHandler($input_name, $is_QR = false){
		
		$img_aide = new \Common\Aide\ImagesAide();
		//生成二维码
		if ($is_QR){
			try {
				$path = $img_aide->QRCoderPainter($input_name['text'], $input_name['save_name'], $input_name['logo_url'], $input_name['label_text']);
			}catch( \Exception $e){
				$this->error = $e->getMessage();
				return false;
			}
			return $path;
		}else {
			
			//普通的图片上传
			$res_info = $img_aide->ImgUploader($input_name, 'ad');
			//上传失败，返回错误信息
			if (!$res_info['ok']){
				$this->error = $res_info['error'];
				return false;
			}else {
				//上传图片成功，那么就保存图片路径到数据库
				return $res_info['file'][0];
			}
		}
		
	}
	
	
	
	/**
	 * ----------------------------------------------
	 * | 获取临近$days的所有业务
	 * | @时间: 2016年11月24日 上午11:33:55
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getIndexRecentBusiness($days = 30){
		$days += 0;
		$db_prefix = C('DB_PREFIX');
		$nowData = date("Y-m-d H:i:s");
		$a = 'a'; $b = 'b'; $c = 'c';
		$host_field = $a.'.overdue_time,'.$a.'.id,'.$c.'.login_name,'.$b.'.product_name,'.$a.'.create_time,'.$a.'.domain_name,'.$b.'.product_type_id';
		$vps_field = $a.'.overdue_time,'.$a.'.id,'.$c.'.login_name,'.$b.'.product_name,'.$a.'.create_time,'.$a.'.ip_address,'.$b.'.product_type_id';
		$cloudserver_field = $a.'.overdue_time,'.$a.'.id,'.$c.'.login_name,'.$b.'.product_name,'.$a.'.create_time,'.$a.'.api_bid,'.$b.'.product_type_id';
		$cloudspace_field = $a.'.overdue_time,'.$a.'.id,'.$c.'.login_name,'.$b.'.product_name,'.$a.'.create_time,'.$a.'.business_id,'.$b.'.product_type_id';
		
		$product_join = 'left join '.$db_prefix.'product as '.$b.' on '.$a.'.product_id = b.id';
		$member_join = 'left join '.$db_prefix.'member as '.$c.' on '.$a.'.user_id = c.user_id';
		
// 		$where['overdue_time'] = [ 'between', [ 'now()', 'date_add(now(), INTERVAL '.$days.' day)' ] ];
		$where['_string'] = ' overdue_time between now() and date_add(now(), INTERVAL '.$days.' day) and '.$a.'.user_id !=-1';
		
		$res['virtual_business'] = M('cloudvirtual_business')->alias($a)->field($host_field)->join($product_join)->join($member_join)->where($where)->select();
		$res['virtualhost_business'] = M('virtualhost_business')->alias($a)->field($host_field)->join($product_join)->join($member_join)->where($where)->select();
		
		$res['fast_vps_business'] = M('fast_vps_business')->alias($a)->field($vps_field)->join($product_join)->join($member_join)->where($where)->select();
		
		$res['vps_business'] = M('vps_business')->alias($a)->field($vps_field)->join($product_join)->join($member_join)->where($where)->select();
		
		$res['cloudserver_business'] = M('cloudserver_business')->alias($a)->field($cloudserver_field)->join($product_join)->join($member_join)->where($where)->select();
		
		$res['cloudserver_business_ip'] = M('cloudserver_business_ip')->alias($a)->field($cloudserver_field)->join($product_join)->join($member_join)->where($where)->select();
		
		$res['cloudspace_business'] = M('cloudspace_business')->alias($a)->field($cloudspace_field)->join($product_join)->join($member_join)->where($where)->select();
		
		$return['Virtualhost'] = array_merge($res['virtual_business'], $res['virtualhost_business']);
		$return['FastVps'] = $res['fast_vps_business'];
		$return['Vps'] = $res['vps_business'];
		$return['Cloudserver'] = $res['cloudserver_business'];
		$return['CloudserverIp'] = $res['cloudserver_business_ip'];
		$return['Cloudspace'] = $res['cloudspace_business'];
		
		return $return;
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 发送即将到期业务提醒邮件
	 * | @时间: 2016年11月24日 下午2:16:42
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function sendMails(){
		$request = request();
		$ids = explode(',', $request['ids']);

		$m_product_type = M('product_type');
		$m_member = M('member');
		$db_prefix = C('DB_PREFIX');
		$this->error = '';
		$ok = $err = 0 ;
		//处理相关参数
		foreach ($ids as $k => $v){
			if (!empty($v)){
				list($type_id, $business_id) = explode('-', $v);
                $type_id = $type_id+0;
				//如果参数为空略过
				if (!empty($type_id) && !empty($business_id)){
					//获取业务的api_ptype
					$type_name = $this->gussePtype($type_id, $m_product_type);
                    if(empty($type_name)){
                        $this->error .= '业务标识['.$business_id.']的业务信息不存在，发送邮件失败<br />';
                        $err++;
                        continue;
                    }
					//获取业务ptype
					$ptype = $type_name;
                    $business_name = $this->product_type_with_business_table_map[$type_name];
                    if(empty($business_name)){
                        $this->error .= '业务标识['.$business_id.']的业务信息不存在，发送邮件失败<br />';
                        $err++;
                        continue;
                    }
					//先同步业务
					$business_sync = $this->syncBusiness($type_id,$ptype,$business_id);
                    if(!$business_sync){
                        $this->error .= '业务标识['.$business_id.']的业务同步失败，发送邮件失败<br />';
                        $err++;
                        continue;
                    }
					//获取业务信息
					$where['a.id'] = [ 'eq', $business_id ];
					$business_info = M($business_name)->alias('a')->field('a.*,b.product_name as business_name')
					->join('left join '.$db_prefix.'product as b on a.product_id = b.id')->where($where)->find();
					//判断查询出来的时间是否临近30天到期
					$overdue_time = new \DateTime($business_info['overdue_time']);
					$new = new \DateTime('now + 30day');
					if($overdue_time < $new){
					    //如果有响应的业务信息
    					if (!empty($business_info)){
    						//获取会员信息
    						$member_info = $m_member->where([ 'user_id' => [ 'eq', $business_info['user_id'] ] ])->find();
    						//如果该会员有邮箱
    						if (!empty($member_info['user_mail'])){
    							//组装邮件内容
    							$mail_content = HTMLContentForEmail(1, $business_info, $member_info);
    							//发送邮件
    							$res = OvBusiness_postOffice($member_info['user_mail'], $mail_content['subject'], $mail_content['body']);
    							$res == false ? $err++: $ok++;
    						}else {
    							$this->error .= '业务标识['.$business_id.']的会员邮箱不存在，发送邮件失败<br />';
    						}
    					}else {
    						$this->error .= '业务标识['.$business_id.']的业务信息不存在，发送邮件失败<br />';
    					}
					}
				}else{
                    $this->error .= '业务标识['.$business_id.']的业务信息不存在，发送邮件失败<br />';
					$err++;
				}
			}else{
                $this->error .= '参数错误<br />';
				$err++;
			}
		}
		$this->error .= '共发送成功'.$ok.'封邮件，共发送失败'.$err.'封邮件<br />';
		return true;
	}
	
	/**
	 * ----------------------------------------------
	 * | 通过产品类型名称得到表名
	 * | @时间: 2016年11月24日 下午3:04:20
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
    protected function gusseTableName($type_id, $m_product_type = null){
        if ($m_product_type == null){
            $m_product_type = M('product_type');
        }

        $type_name = $m_product_type->field('api_ptype')->where([ 'id' => [ 'eq', $type_id ] ])->find();
        $type_name = $type_name['api_ptype'];

        return $this->product_type_with_business_table_map[$type_name];
    }
	
	/**
	 * ----------------------------------------------
	 * | 通过产品类型名称得到表名
	 * | @时间: 2016年11月24日 下午3:04:20
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function gussePtype($type_id, $m_product_type = null){
        if ($m_product_type == null){
            $m_product_type = M('product_type');
        }
        $type_name = $m_product_type->field('api_ptype')->where([ 'id' => [ 'eq', $type_id ] ])->find();
        if(empty($type_name)){
            $type_name = "";
        }else{
            $type_name = $type_name['api_ptype'];
        }
        return $type_name;
	}
	
	/**
	 * ----------------------------------------------
	 * | 同步业务信息
	 * | @时间: 2016年11月24日 下午3:04:20
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function syncBusiness($type_id,$ptype,$business_id){
	   if($type_id == '7' || $type_id == '8' || $type_id =='15' || $type_id =='16' || $type_id =='17'){
	       //虚拟主机
	       $where['id'] = ["eq",$business_id];
	       $vhost = new \Frontend\Model\VirtualhostModel();
	       $business_info = $vhost->where($where)->find();
	       $vhostRespository = new \Common\Respository\VirtualhostBusinessRespository($vhost);
	       $result = $vhostRespository->VirtualhostBusinessSynchronizing($business_info['business_id'],$ptype);
	   }elseif ($type_id == '1'){
	       //VPS
	       $vps = new \Frontend\Model\VpsModel();
	       $vpsRespository = new \Common\Respository\VpsBusinessRespository($vps);
	       $result = $vpsRespository->VpsBusinessSynchronizing($business_id,$ptype);
	   }elseif ( $type_id == '13' || $type_id == '14'){
	       //快云VPS
	       $fastvps = new \Frontend\Model\FastvpsModel();
	       $fastvpsRespository = new \Common\Respository\VpsBusinessRespository($fastvps);
	       $result = $fastvpsRespository->VpsBusinessSynchronizing($business_id,$ptype);
	   }elseif($type_id == '12'){
	       //云空间
	       $cloudspace = new \Frontend\Model\CloudspaceModel();
	       $cloudspaceRespository = new \Common\Respository\CloudspaceBusinessRespository($cloudspace);
	       $result = $cloudspaceRespository->CloudspaceBusinessSynchronizing($business_id,$ptype);
	   }elseif($type_id == '18'){
	       //快云服务器
	       $cloudserver = new \Frontend\Model\CloudserverModel();
	       $cloudserverRespository = new \Common\Respository\CloudserverBusinessRespository($cloudserver);
	       $result = $cloudserverRespository->CloudserverBusinessSynchronizing($business_id);
	   }elseif($type_id == '20'){
           //ssl
           $ssl = new \Frontend\Model\SslModel();
           $sslRespository = new \Common\Respository\SslRespository($ssl);
           $result = $sslRespository->SslBusinessSynchronizing($business_id);
       }elseif($type_id == '22'){
           //快云数据库
           $clouddb = new \Frontend\Model\SslModel();
           $clouddbRespository = new \Common\Respository\ClouddbBusinessRespository($clouddb);
           $result = $clouddbRespository->ClouddbBusinessSynchronizing($business_id);
       }else{
           $result = false;
       }
	   if($result){
	       return true;
	   }else{
	       return false;
	   }
	}
	
	/**
	 * ----------------------------------------------
	 * | 测试通信接口
	 * | @时间: 2016年11月25日 上午10:24:49
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function testCommunication(){
		try {
			$agent = new \Common\Aide\AgentAide(2);
			
			$transaction = $agent->servant;
			/**
			 * 调用接口，域名查询
			*/
			$result=$transaction->new_domain_quary ( 'zzidc',array(".com"));
		} catch ( \Exception $e ) {
			$this->error = '接口调用失败';
			return false;
		}
		$xstr = simplexml_load_string ( $result );
		$code= $xstr->code;
		$message = "通信失败，平台无法正常交易，请在系统设置里查看你的网站信息是否正确。";
		switch ($code){
			case 0: $message="可以正常通信";break;
			case 99: $message="通信失败，接口繁忙，平台无法正常交易。";break;
			case 110: $message="通信失败，已经被取消调用接口的资格，平台无法正常交易。";break;
			case 1001: $message="通信失败，签名验证错误，平台无法正常交易。";break;
			case 1002: $message="通信失败，客户端请求超时，平台无法正常交易，请更新你的服务器时间。";break;
			case 1005: $message="通信失败，Access Id错误，平台无法正常交易，请在：系统管理->网站配置->网站详情，设置你的接口信息。";break;
			case 1006: $message="通信失败，IP错误(Access Id对应的IP不对)，平台无法正常交易，请在景安会员中心设置你的接口调用IP为当前服务器IP。";break;
			case 3004: $message="通信失败，没有找到该代理会员。";break;
		}
		$this->error = $message;
		return false;
	}
	
	/**
	 * ----------------------------------------------
	 * | 一键备份
	 * | @时间: 2016年11月25日 上午11:52:28
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function oneTouchBackup(){
		
		$updater = new \Common\Aide\UpdateAide();
		//备份数据
		$updater->mysql_backup_hander->setPath(C('BACKUP_PATH').'UserBackup');
		$res['data'] = $updater->mysql_backup_hander->backup();
		
		//备份文件
		$res['software'] = $updater->Softwarebak(true, 'UserBackup');

		if ($res['data'] && $res['software']){
			return true;
		}else {
			$this->error = !$res['data'] ? $updater->mysql_backup_hander->error(): $updater->info;
			return false;
		}
		
		
	}
	/**
	* 测试邮件发送
	* @date: 2017年1月11日 上午11:14:57
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function mailtest($data){
	    $mail = $data['test'];
	    $content = HTMLContentForEmail("8",'','');
	    return postOffice($mail,$content['subject'],$content['body']);
	}
	
	
	

}
