<?php
namespace Common\Aide;


/**
 * -------------------------------------------------------
 * | 跳转其他地址的helper类
 * | @author: duanbin
 * | @时间: 2016年10月24日 下午5:49:06
 * | @version: 1.0
 * -------------------------------------------------------
 */
class JumpAide {
	
	protected $info = "";
	
	/**
	 * ----------------------------------------------
	 * | 获取服务码
	 * | @时间: 2016年12月17日 下午3:07:24
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function get_service_code($input_name, $api_name, $business_id, $business_mark){
		$agent = new \Common\Aide\AgentAide();
		$t = $agent->servant;
		try{
            $result = $t->get_server_code($input_name, $api_name, $business_mark, $business_id);
			$response_json_obj = json_decode($result,true);
		}catch (\Exception $e){
			$this->info = $e->getMessage();
			return false;
		}
		if (! empty($response_json_obj) && strcmp($response_json_obj['code'], '0') == 0) {
            return $response_json_obj;
		} else {
            api_log(session("user_id"),"input_name:".$input_name."-pname:".$api_name."-bis_sign:".$business_mark."-bid:".$business_id,$result,"获取服务码");
            $this->info = '获取不到服务码';
			return false;
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 跳转自助管理平台
	 * | @时间: 2016年12月17日 下午3:22:03
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function jump_to_ssp($service){
		if(empty($service) || strcmp($service['code'],'0') != 0){
			$this->info = "产品服务码获取失败";
			return false;
		}
		$service_pwd = $service['info']['passwd'];
		$service_code = $service['info']['serviceCode'];
		$agent = new AgentAide();
		$t = $agent->servant;
		try{
			$json_obj =json_decode($t->get_self_key($service_code,$service_pwd),true);
		}catch (\Exception $e){
			$this->info = $e->getMessage();
			return false;
		}
		if(empty($json_obj) || strcmp($json_obj['code'],'0') != 0){
			$this->info = "跳转自主管理平台错误";
			return false;
		}
// 		dump($service);
// 		dump($json_obj);
// 		die;
		$url = C('SSP_SITE_URL');
		//         echo '<script>window.location.href="'.$url.'?codeName='.$service_code.'&codePwd='.$service_pwd.'&loginKey='.$json_obj['info'].'";</script>';
		//print_r($json_obj);
		return  '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body></body><script type="text/javascript">
		var postForm = document.createElement("form");
		postForm.method="post";
		postForm.style.display = "none";
		postForm.action ="'.$url.'";
		var codeInput = document.createElement("input");
		codeInput.setAttribute("name","code");
		codeInput.setAttribute("value","'.$json_obj['info'].'");
		postForm.appendChild(codeInput);
		document.body.appendChild(postForm);
		postForm.submit();</script></html>';
	}
	
	/**
	 * ----------------------------------------------
	 * | 返回错误信息
	 * | @时间: 2016年12月17日 下午3:09:18
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getInfo(){
		return $this->info;
	}
	
	/**
	 * ----------------------------------------------
	 * | 设置错误信息
	 * | @时间: 2016年12月17日 下午3:09:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function setInfo($info) {
		$this->info = $info;
	}
	
	
}