<?php
namespace Common\Aide;

require_cache('./vendor/agent/Transaction.class.php');

/**
 * -------------------------------------------------------
 * | 与主站api同步或操作的类
 * | @author: duanbin
 * | @时间: 2016年10月24日 下午5:49:06
 * | @version: 1.0
 * -------------------------------------------------------
 */
class AgentAide {
	
	
	//景安的与主站交互的类实例
	private $servant = null;
	
	private $APIParams = null;
	
	//网站的一些配置，与主站交互的时候用的
	private $config = [];
	
	
	
	public function __construct($type = 1){
		
		//获取网站站点的配置信息；
		$this->config = WebSiteConfig();

		$this->servant = new \Agent\Transaction($type, $this->config);
		$this->APIParams = new \Common\Data\GiantAPIParamsData();
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 
	 * | @时间: 2016年12月22日 上午10:11:35
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function __get($name){
		if (isset($this->$name)){
			return $this->$name;
		}else {
			return null;
		}
	}
	
	
	
	
	
	
	
	
}