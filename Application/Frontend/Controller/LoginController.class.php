<?php
namespace Frontend\Controller;
use Common\Controller\BaseController;

/**
 * -------------------------------------------------------
 * | 前台首页
 * |@author: Lyubo
 * |@时间: 2016年10月21日 下午15:56:08
 * |@version: 1.0
 * -------------------------------------------------------
 */
class LoginController extends BaseController
{
	
	protected $captcha_type_regist = 'frontend_register';
	protected $captcha_type_login = 'frontend_sign_in';
	
	protected $model = null;
	
	
	public function _initialize(){
		parent::_initialize();
		
		$this->model = new \Frontend\Model\MemberModel();
	}
	
	
    /**
     * ----------------------------------------------
     * | 前台登录页面
     * | @时间: 2016年10月24日 18:19:52
     * | @author: Lyubo
     * ----------------------------------------------
     */
    public function login()
    {
        if (IS_POST) {
                $user = D('Member');
                $date = I('post.');
            $request['captcha'] = $date['captcha'];
            $request['type'] = $this->captcha_type_login;
            $imagesAide = new \Common\Aide\ImagesAide();
            $captcha_res = $imagesAide->checkCaptcha($request['captcha'],$request['type']);
            if (!$captcha_res){
                $msg = "验证码不对哦!";
                if(IS_AJAX){
                    $this->error($msg, U("Frontend/Login/login"), true);
                }else{
                    $this->error($msg,U("Frontend/Login/login"));
                }
            }
                $where['login_name'] = array("eq",$date['user_name']);
                $result = $user->get_member_info($where);
                if($result){
                     $url = session("?backward") ?  session("backward")  :  U("Frontend/Index/index") ;
                    //验证数据库中去出的密码是否一致
                    if($result['user_pass'] == pwd($result['login_name'],$date['user_pass'])){
                    	/**
                    	 * ---------------------------------------------------
                    	 * | 验证用户状态
                    	 * | @时间: 2017年1月4日 上午10:29:36
                    	 * ---------------------------------------------------
                    	 */
                    	if ($result['user_state'] == 1){                    		
	                        session('user_id',$result['user_id']);
	                        session('user_name',$result['user_name']);
	                        session('login_name',$result['login_name']);
	                        $this->success("登录成功",$url) ;
                    	}else {
                    		$this->error("该用户状态异常",U("Frontend/Login/login")) ;
                    	}
                    }else{
                        $this->error("用户密码错误",U("Frontend/Login/login")) ;
                    }
                }else{
                    $this->error("该用户未注册,请注册后登录",U("Frontend/Login/login")) ;
                }
        } else {
            $this->display();
        }
    }
    /**
     * ----------------------------------------------
     * | 前台退出
     * | @时间: 2016年10月24日 18:20:20
     * | @author: Lyubo
     * ----------------------------------------------
     */
    public function logout(){
        session(null);
        redirect(U('/Frontend/Index/Index', [], false), 0, '');
    }
    
    /**
     * ----------------------------------------------
     * | 注册页面
     * | @时间: 2016年12月6日 下午5:30:22
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function register(){
    	
    	$this->display();
    }
    
    /**
     * ----------------------------------------------
     * | 处理注册逻辑
     * | @时间: 2016年12月7日 上午10:31:25
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function clerk(){
    	$request = request(true);
    	//验证码的标识
    	$request['type'] = $this->captcha_type_regist;
    	
    	$res = $this->model->regist($request);
    	if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error($msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			session('user_id',$res['user_id']);
			session('user_name',$res['user_name']);
			session('login_name',$res['login_name']);
			
			$msg = '欢迎您注册为会员！:)';
			$url = U('Frontend/Member/index', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
    }

    
    /**
     * ----------------------------------------------
     * | 登录和注册获取验证码的方法
     * | @时间: 2016年12月1日 上午10:04:36
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function captcha(){
    	
    	$request = request(true);

    	$imagesAide = new \Common\Aide\ImagesAide();
    	
    	$imagesAide->capthca($request['type']);
    
    }
    
    /**
     * 通过邮箱找回密码
     * @date: 2017年2月20日 下午3:21:12
     * @author: Lyubo
     * @param: variable
     * @return:
     */
    public function find_pass() {
        if (IS_POST) {
            $user = new \Frontend\Model\MemberModel();
            $date = I('post.');
            $request['captcha'] = $date['captcha'];
            $request['type'] = $this->captcha_type_login;
            $imagesAide = new \Common\Aide\ImagesAide();
            $captcha_res = $imagesAide->checkCaptcha($request['captcha'],$request['type']);
            if (!$captcha_res){
                $this->error("验证码不对哦!",U("Frontend/Login/find_pass"));
            }
            //通过填写的登录名和用户邮箱验证
            $where['login_name'] = array("eq",$date['login_name']);
            $where['user_mail'] = array("eq",$date['user_mail']);
            $result = $user->get_member_info($where);
            if(!empty($result)){
                    /**
                     * ---------------------------------------------------
                     * | 验证用户状态
                     * | @时间: 2017年1月4日 上午10:29:36
                     * ---------------------------------------------------
                     */
                    if ($result['user_state'] == 1){
                        //生成随机密码
                        $sjzf="QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890";
                        $pas=substr(str_shuffle($sjzf),0,10);
				        $pwd=md5 ( $pas );
				        $pwd = pwd ( $date['login_name'], $pwd );
                        $member_info['user_name'] = $result['user_name'];
                        $member_info['password'] = $pas;
                        $member_where['login_name'] = ["eq",$result['login_name']];
                        $member_upd['user_pass'] = $pwd;
                        $result_upd = $user->where($member_where)->save($member_upd);
                        if($result_upd !== false){
                            session("backward", U("Frontend/Index/index")) ;
                            $content = HTMLContentForEmail("9",'',$member_info);
                            postOffice($date['user_mail'],$content['subject'],$content['body'],'9');
                            $this->success("已把新密码发送至填写邮箱中，请尽快查看登陆并修改！",U("Frontend/Login/login")) ;
                        }else{
                            $this->error("重置密码失败",U("Frontend/Index/index")) ;
                        }
                    }else {
                        $this->error("该用户已被禁用,请联系管理员",U("Frontend/Index/index")) ;
                    }
            }else{
                $this->error("该用户未注册或未填写邮箱,请联系管理员重置密码",U("Frontend/Index/index")) ;
            }
        } else {
            $this->display();
        }
    }
    /**
    * 检测用户是否注册
    * @date: 2017年4月21日 下午6:18:56
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function checkLogin(){
        $request = request(true);
        	$res = $this->model->checkLogin($request);
        	if (IS_AJAX) {
        	if ($res !== false) {
        	        $this->success($msg, '', true);
        	    }else {
        	        $this->error($msg, '',true);
        	    }
        	}else{
        	    $msg = '请求方式错误';
        	    $this->error($msg, '');
        	}
    }
}