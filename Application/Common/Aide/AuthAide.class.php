<?php
namespace Common\Aide;

/**
 * -------------------------------------------------------
 * | 登录验证的类，如果当前使用的模型不是用户表模型
 * | 需要首先调用此类的guard()方法，设置对应的数据表
 * | @author: duanbin
 * | @时间: 2016年10月8日 下午4:27:37
 * | @version: 1.0
 * -------------------------------------------------------
 */
class AuthAide {
	
	/**
	 * ---------------------------------------------------
	 * | 用于保存用户数据表模型
	 * | @时间: 2016年10月9日 上午9:19:04
	 * ---------------------------------------------------
	 */
	private $guard = null;
	
	/**
	 * ---------------------------------------------------
	 * | 保存错误信息
	 * | @时间: 2016年10月9日 上午9:19:33
	 * ---------------------------------------------------
	 */
	protected $error = null;
	
	/**
	 * ---------------------------------------------------
	 * | 保存错误标识码
	 * | @时间: 2016年12月1日 上午10:44:00
	 * ---------------------------------------------------
	 */
	protected $code = 1;
	
	/**
	 * ----------------------------------------------
	 * |默认的登录逻辑验证
	 * |@时间: 2016年9月29日 下午5:59:23
	 * |@author: duanbin
	 * |@params: 
	 * |@return: boolean 返回认证结果
	 * ----------------------------------------------
	 */
	public function auth($request){
		$username = $request['username'];
		$password = $request['password'];
		$captcha = $request['captcha'];
		
		//如果有验证码，就先验证验证码
		if (!empty($captcha)){
			$imagesAide = new \Common\Aide\ImagesAide();
			$captcha_res = $imagesAide->checkCaptcha($captcha, 'backend_login');
			if (!$captcha_res){
				$this->code = -3;
				$this->error = '验证码错误';
				return false;
			}
		}else {
			$this->code = -3;
			$this->error = '验证码不能为空';
			return false;
		}
		
		
		/**
		 * ---------------------------------------------------
		 * | 当前使用的此模型是否设置了用户名和密码字段
		 * | 默认为username,password
		 * | @时间: 2016年10月8日 下午4:24:48
		 * ---------------------------------------------------
		 */
		$key = property_exists($this->guard, 'username') ? $this->guard->username: 'username';
		$pwd = property_exists($this->guard, 'password') ? $this->guard->password: 'password';
		$where = [ $key => $username, ];
		/**
		 * ---------------------------------------------------
		 * | 当前是否设置了表模型
		 * | @时间: 2016年10月8日 下午4:25:50
		 * ---------------------------------------------------
		 */
		$user_info = $this->getUserInfo($where);
// 		dump($user_info);die;
		/**
		 * ---------------------------------------------------
		 * | 判断用户是否存在
		 * | @时间: 2016年10月8日 下午4:26:23
		 * ---------------------------------------------------
		 */
		if (empty($user_info)) {
			$this->code = -1;
			$this->error = '不存在该用户';
			return false;
		}else {
			/**
			 * ---------------------------------------------------
			 * | 验证通过，返回登录用户的信息
			 * | @时间: 2016年10月8日 下午4:26:34
			 * ---------------------------------------------------
			 */ 
			if ( $user_info[$pwd] == md5( $password.C('APP_KEY') ) ) {
				return $user_info;
			}else {
				$this->code = -2;
				$this->error = '密码错误';
				return false;
			}
		}
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 设置自动登录方法
	 * | @时间: 2016年12月1日 上午11:06:25
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function setAutoLogin($username,$password){
		cookie(md5(('username'^C('APP_KEY'))),encrypt($username,'E',C('APP_KEY')),array('expire'=>C('BACKEND_LOGIN_COOKIE_EXPIRE')*60*60,'prefix'=>'b_'));
		cookie(md5(("password"^C('APP_KEY'))),encrypt($password,'E',C('APP_KEY')),array('expire'=>C('BACKEND_LOGIN_COOKIE_EXPIRE')*60*60,'prefix'=>'b_'));
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取cookie中保存的用户名密码
	 * | @时间: 2016年12月1日 上午11:12:38
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getAutoLoginUsernameAndPassword(){
		$cookie_username = encrypt(cookie('b_'.md5(('username'^C('APP_KEY')))),'D',C('APP_KEY'));
		$cookie_password = encrypt(cookie('b_'.md5(('password'^C('APP_KEY')))),'D',C('APP_KEY'));
		return [
				'username' => $cookie_username,
				'password' => $cookie_password,
		];
	}
	
	/**
	 * ----------------------------------------------
	 * | 验证用户名，或密码是否正确
	 * | @时间: 2016年10月8日 下午3:20:59
	 * | @author: duanbin
	 * | @param: $field 要验证的字段名， 要验证的数据
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function checkField($field, $data){
		$where = [ $field => $data, ];
		return $this->getUserInfo($where);
	}
	
	/**
	 * ----------------------------------------------
	 * | 设置数据表的方法
	 * | 如果当前使用的模型不是对应的数据表模型
	 * | 需要首先使用此方法设置相应的数据表名
	 * | @时间: 2016年10月8日 下午3:29:29
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function guard($guard){
		$this->guard = $guard;
		return $this;
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取登录用户的信息
	 * | @时间: 2016年10月8日 下午5:06:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function getUserInfo($where){
		if ($this->guard == null){
			E('未配置表模型');
			return false;
		}else {
			return $this->guard->where($where)->find();
		}
	}	
	
	/**
	 * ----------------------------------------------
	 * | 返回数据表模型实例
	 * | @时间: 2016年10月8日 下午6:06:27
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getGuard() {
		return $this->guard;
	}
	
	/**
	 * ----------------------------------------------
	 * | 返回错误信息
	 * | @时间: 2016年10月8日 下午6:07:50
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getError(){
		return $this->error;
	}
	
	/**
	 * ----------------------------------------------
	 * | 设置错误信息
	 * | @时间: 2016年12月1日 上午10:17:38
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function setError($error){
		$this->error = $error;
	}
	
	/**
	 * ----------------------------------------------
	 * | 返回错误标识码
	 * | @时间: 2016年12月1日 上午10:43:43
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getCode() {
		return $this->code;
	}
	
}
