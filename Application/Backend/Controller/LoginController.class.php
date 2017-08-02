<?php
namespace Backend\Controller;
use Think\Controller;
/**
 * -------------------------------------------------------
 * | 后台登录控制器
 * | @author: duanbin
 * | @时间: 2016年10月8日 下午2:32:20
 * | @version: 1.0
 * -------------------------------------------------------
 */
class LoginController extends Controller
{	
	
	protected $aide  = null;
	
	/**
	 * ----------------------------------------------
	 * | 控制器初始化函数
	 * | 装在注册登录助手
	 * | @时间: 2016年10月8日 下午6:01:41
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _initialize(){
		$this->aide = (new \Common\Aide\AuthAide)->guard( D('Backend/Users') );
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 后台登录页面
	 * | @时间: 2016年9月30日 下午3:30:32
	 * | @author: duanbin
	 * ----------------------------------------------
	 */
	public function index(){
		$this->display();
	}

	/**
	 * ----------------------------------------------
	 * | 异步验证后台管理员用户名
	 * | @时间: 2016年10月8日 下午3:18:37
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function ajaxCheck(){
		$get = I('get.','');

		$res = $this->aide->checkField('username',$get['username']);
		if ($res) {
			$this->ajaxReturn([
					'code' => 1,
					'msg' => '用户名正确',
			], 'json');
		}else {
			$this->ajaxReturn([
					'code' => -1,
					'msg' => '用户名错误或不存在',
			], 'json');	
		}
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 异步验证登录的方法
	 * | todo: 登录日志
	 * | @时间: 2016年10月8日 下午5:59:19
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function ajaxLogin(){
		$request = request(true);
			
		$res = $this->aide->auth($request);

// 		dump($res);die;
		if ($res) {
			/**
			 * ---------------------------------------------------
			 * | 登录成功，将管理员的信息传入到session中
			 * | @时间: 2016年10月8日 下午6:11:44
			 * ---------------------------------------------------
			 */
			session('admin',$res);
			
			$this->ajaxReturn([
					'code' => 1,
					'msg' =>U('Backend/Index/index','',false),
			], 'json');
		}else {
			$this->ajaxReturn([
					'code' => $this->aide->getCode(),
					'msg' => $this->aide->getError(),
			], 'json');
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 后台获取验证码的方法
	 * | @时间: 2016年12月1日 上午10:04:36
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function captcha(){
		
		$imagesAide = new \Common\Aide\ImagesAide();
		
		$imagesAide->capthca('backend_login');
		
	}
	
	
	
	/**
	 * ----------------------------------------------
	 * | 退出操作
	 * | todo: 退出日志
	 * | @时间: 2016年10月8日 下午6:38:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public  function logout(){
		session('admin',null);
		unset($_SESSION['admin']);
		$this->success('已退出', U('Backend/Login/index'));
	}
	
	
	
	
	
	
	
}