<?php 

namespace Install\Controller;
use \Think\Controller;


class IndexController extends Controller{

	
	/**
	 * ----------------------------------------------
	 * | 初始化方法
	 * | @时间: 2016年12月19日 下午3:32:07
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function _initialize(){
		//防止重复安装
		if(is_file(APP_PATH . 'Common/Conf/config.php')){
			header('Location: ./index.php');
			exit;
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 安装首页面
	 * | @时间: 2016年11月17日 下午3:31:33
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function index(){

		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 运行环境检测
	 * | @时间: 2016年11月17日 下午5:14:39
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function check(){
		
		$env = test_env();
		$file_and_dir = test_mod_about_dir_and_file();
		$func = test_func();
// 		dump($func);die;
		//系统检测没有任何错误，那么安装第一步完成，存入session中状态值
		if (!session('has_error')){
			session('step_complete', 1);
		}
		$this->assign([
				'env' => $env['env'],
				'error' => $env['error'],
				'file_and_dir' => $file_and_dir,
				'func' => $func,
		]);
		
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 配置
	 * | @时间: 2016年11月17日 下午5:21:26
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function setup(){
		//如果系统检测有问题，那么不允许安装
		if (session('has_error')){
			redirect(U('Install/Index/check', [], false));
			die;
		}
		//这里查看安装到第几步了
		$step_complete = session('step_complete');
		//如果安装步骤---检测环境进行完的话，那么就可以进行本次操作了->
		//->展示配置界面
		if ($step_complete >=1 ){
			$this->display();
			//配置界面展示后，将步骤置为2，代表可以进行配置了，
			//在配置操作是拿出来判断；
			session('step_complete', 2);
		}else {
			redirect(U('Install/Index/check', [], false));
			die;
		}
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 安装
	 * | @时间: 2016年11月17日 下午5:24:30
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function installing(){
		//这里查看安装到第几步了
		$step_complete = session('step_complete');
		//如果$step_complete == 2 说明环境检测通过了，  可以进行安装了
		if ($step_complete >=2 ){
 			$res = installing();
 			$msg = $res['info'];
 			if ($res['status'] == true){
 				//如果安装成功，设置下提示页面，并将一些必要的安装过程中的信息返回给浏览器；
 				$this->assign([
 						'info' => $res['info'],
 				]);
				$msg = $this->fetch('Index/complete');
 				if (IS_AJAX) {
 					$this->success($msg, '', true);
 				}else {
 					$this->success($msg, '');
 				}
 				session('step_complete', null);
 			}else {
 				//安装失败了就在当前页面提示错误信息
 				$msg = $res['info'];
 				$url = '';
 			}
		}else {
			//如果是用户手动跳步骤了，那么就让他回到该回的步骤
			$msg = "请先完成环境检测";
	 		$url = U('Install/Index/check', [], false);
		}
 		if (IS_AJAX) {
 			$this->error($msg, $url,true);
 		}else {
 			$this->error($msg, $url);
 		}

	}
	

}











