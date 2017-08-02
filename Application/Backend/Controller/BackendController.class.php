<?php
namespace Backend\Controller;
use Common\Controller\BaseController;

/**
 * -------------------------------------------------------
 * | 后台的基础控制器
 * |@author: duanbin
 * |@时间: 2016年9月30日 上午10:34:42
 * |@version: 1.0
 * -------------------------------------------------------
 */
class BackendController extends BaseController{

	/**
	 * ----------------------------------------------
	 * |初始化函数
	 * |@时间: 2016年9月30日 上午10:35:01
	 * |@author: duanbin
	 * |@params: variable
	 * |@return:
	 * ----------------------------------------------
	 */
	public function _initialize(){
		parent::_initialize();
		
		/**
		 * ---------------------------------------------------
		 * | 临时将超级管理员的数据存放进来。方便开发调试
		 * | 正式上线要删除下面两行代码
		 * | @时间: 2016年9月30日 下午3:25:55
		 * ---------------------------------------------------
		 */
 		//$admin = [ 'username' => 'admin', 'id' => 1];
 		//session('admin',$admin); 
		
		
		
		/**
		 * ---------------------------------------------------
		 * | 后台权限认证（排除超级管理员）
		 * | @时间: 2016年10月8日 上午11:59:36
		 * ---------------------------------------------------
		 */
		if (!$this->authentication()){
			$this->error('您没有权限访问');
			die;
		}
		
		/**
		 * ---------------------------------------------------
		 * | 认证通过后，可分配菜单
		 * | @时间: 2016年10月8日 下午1:42:32
		 * ---------------------------------------------------
		 */
		$this->getMune();

	}

	/**
	 * ----------------------------------------------
	 * | 代理平台后台的权限认证
	 * | 基于thinkphp3.2.3的auth认证
	 * | @时间: 2016年10月8日 下午12:01:00
	 * | @author: duanbin
	 * | @return: boolean
	 * ----------------------------------------------
	 */
	public function authentication(){
// 		dump(session('admin.id'));die;

		/**
		 * ---------------------------------------------------
		 * | 先判断看是否登录了
		 * | @时间: 2016年10月9日 上午10:23:16
		 * ---------------------------------------------------
		 */
		if (!session('?admin')) {
			$this->error('未登录，或太久时间未操作，请登录', U('Backend/Login/index','',false));
			die;
		}
		
		/**
		 * ---------------------------------------------------
		 * | 操作权限的认证
		 * | @时间: 2016年10月9日 上午10:25:53
		 * ---------------------------------------------------
		 */
		if(session('admin.id') !=1){
			$auth=new \Think\Auth();
			$rule_name=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
			return $auth->check($rule_name,session('admin.id'));
		}else {
			//超级管理员不用管
			return true;
		}
	}

	/**
	 * ----------------------------------------------
	 * | 获取当前用户拥有的菜单
	 * | todo:后期增加缓存
	 * | @时间: 2016年10月8日 下午1:38:57
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public  function getMune() {
		
		$nav_data=D('Backend/AdminNav')->getTreeData('level','order_number,id');
// 		dump($nav_data);die;
		$nav_key = property_exists($this, 'nav_key') ? $this->nav_key: 'nav_data';
		$this->assign([
				$nav_key => $nav_data,
		]);
	}

	/**
	 * ----------------------------------------------
	 * | 判断管理员是否登录
	 * | @时间: 2016年10月8日 下午1:54:10
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public  function  isLogged() {
		$key = 'admin';
		if (session('?'.$key)){
			return session($key);
		}else {
			return false;
		}
	}
}