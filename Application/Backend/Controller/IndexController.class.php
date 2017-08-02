<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;

/**
 * -------------------------------------------------------
 * | 后台首页
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午5:06:01
 * |@version: 1.0
 * -------------------------------------------------------
 */
class IndexController extends BackendController
{
	
	
	public function _initialize(){
		parent::_initialize();

	}

	
	/**
	 * ----------------------------------------------
	 * | 后台首页
	 * | @时间: 2016年9月30日 下午3:30:32
	 * | @author: duanbin
	 * ----------------------------------------------
	 */
	public function index(){
		
		$m_sys = new \Backend\Model\SysModel();
		$file_handler = new \Common\Aide\FileStorageAide();
		$recent_business = $m_sys->getIndexRecentBusiness();
		
		$this->assign([
				'recent_business' => $recent_business,
		]);
		$this->display();
	}

	/**
	 * ----------------------------------------------
	 * | 修改管理员密码
	 * | @时间: 2016年11月14日 下午3:31:56
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function updatePwd(){
		
		//修改密码的逻辑
		$m_admin = new \Backend\Model\UsersModel();
		$res = $m_admin->updatePwd(request(false));
		
		//返回结果
		if ($res === false) {
			$msg = $m_admin->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '修改成功';
			$url = U('Backend/Index/index', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 清除缓存
	 * | @时间: 2016年11月14日 下午4:48:23
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function clearCache() {
		
		$res = clear_cache();
		
		if ( $res['html'] == false && $res['cache'] == false && $res['data'] == false && $res['temp'] == false ) {
			$msg = '缓存文件已经不存在啦。。。';
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '删除缓存成功';
			$url = U('Backend/Index/index', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 检查代理平台是否可以升级
	 * | @时间: 2016年12月10日 上午10:38:33
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function updatable(){

		$updater = new \Common\Aide\UpdateAide();
		$version_info = $updater->checkSoftwareVersion();
		if ( $version_info === false ) {
			$msg = $updater->getInfo();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '可更新升级';
			$url = '';
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新代理平台
	 * | @时间: 2016年11月14日 下午5:45:00
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function update(){
		
		//检查版本信息
		$updater = new \Common\Aide\UpdateAide();
		$version_info = $updater->checkSoftwareVersion();
		
		if (!$version_info){
			$msg = $updater->info;
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$this->assign([
					"version_info" => $version_info,
			]);
			$msg = $this->fetch('Index/version-info');
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg, '');
			}
		}

	}
	
	/**
	 * ----------------------------------------------
	 * | 升级操作
	 * | @时间: 2016年11月15日 下午2:57:43
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function updating(){
		
		//检查版本信息
		$updater = new \Common\Aide\UpdateAide();
		$res = $updater->updating(request());
		
		$msg = $updater->info;
		if (!$res){
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg, '');
			}
		}
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 给前台用的看是否开启了set_time_limit函数
	 * | @时间: 2016年11月16日 下午3:00:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function isCallable() {
		if (!is_callable('set_time_limit')){
			$msg = "不可用";
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = "可用";
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg, '');
			}
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 发送邮件的接口，业务即将到期的提醒
	 * | @时间: 2016年11月24日 下午2:15:26
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function sendMails(){
		
		$m_sys = new \Backend\Model\SysModel();
		$res = $m_sys->sendMails();
		$msg = $m_sys->getError();
		if (!$res){
			if (IS_AJAX) {
				$this->error($msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg, '');
			}
		}
		
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 测试通信
	 * | @时间: 2016年11月25日 上午10:19:48
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function testCommunication(){
		
		$m_sys = new \Backend\Model\SysModel();
		$res = $m_sys->testCommunication();
		$msg = $m_sys->getError();
		
		if (!$res){
			if (IS_AJAX) {
				$this->error($msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg, '');
			}
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 一键备份功能
	 * | @时间: 2016年11月25日 上午11:50:31
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function oneTouchBackup(){
		if (!is_callable('set_time_limit')){
			$msg = "set_time_limit()未开启，不支持一键备份！";
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}
		
		$m_sys = new \Backend\Model\SysModel();
		$res = $m_sys->oneTouchBackup();
		if (!$res){
			$msg = $m_sys->getError();
			if (IS_AJAX) {
				$this->error($msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '备份成功，已经数据和程序备份在'.C('BACKUP_PATH').'UserBackup目录下，为了站点安全考虑，请立即移出或删除。';
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg, '');
			}
		}
		
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取交易数据
	 * | @时间: 2016年11月25日 下午4:30:52
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getDealCount() {
		
		$m_deal = new \Backend\Model\DealModel();
		$res = $m_deal->getDealCount();

		if ($res === false){
			$msg = $m_deal->getError();
			if (IS_AJAX) {
				$this->error($msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = $res;
			if (IS_AJAX) {
				$this->success($msg, '', true);
			}else {
				$this->success($msg, '');
			}
		}
	}
	/**
	* 获取景安新闻公告
	* @date: 2017年2月7日 下午6:14:44
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
public function getLatestNews()
    {
        $url = "https://www.zzidc.com/main/news/showNewsList.html";
        $content = get_url_content($url);
        preg_match_all('/<a.*?(?: \\t\\r\\n)?href=[\'"]?(.+?)[\'"]?(?:(?: \\t\\r\\n)+.*?)?>(.+?)<\/a.*?>/sim', $content, $result, PREG_PATTERN_ORDER);
        for ($i = 0; $i < count($result[1]); $i ++) {
            if (stristr($result[1][$i], 'main/news') != false) {
                $arr[$i]='<li>'."<a target=\"_blank\" href=\"https://www.zzidc.com/".$result[1][$i]."\">".$result[2][$i]."</a>".'</li>';
            }
        }
        foreach ($arr as $item) {
            if (! strpos($item, "新闻公告")) {
                $retStr = $retStr . $item;
            }
        }
        print_r($retStr);
    }
	
	
	
}