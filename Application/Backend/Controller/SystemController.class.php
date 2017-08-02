<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use Backend\Model\SysModel;



class SystemController extends BackendController
{


	/**
	 * ----------------------------------------------
	 * | 初始化仓储和模型
	 * | @时间: 2016年10月9日 下午4:49:54
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function _initialize(){
		parent::_initialize();
		$this->model = new SysModel();
	}
	
	/**
	 * ----------------------------------------------
	 * | 网站设置的基本信息
	 * | @时间: 2016年11月11日 上午11:19:26
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function configuration(){
		
		$basic_info = WebSiteConfig();
		
// 		dump($basic_info);die;
		$this->assign([
				'basic_info' => $basic_info,
		]);
		
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 网站配置项
	 * | @时间: 2016年11月11日 下午2:59:58
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function configure(){
		$res = $this->model->setup(request(true));
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '操作成功';
			$url = U('Backend/System/configuration', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 邮箱配置页面
	 * | @时间: 2016年11月11日 下午5:24:20
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function mail(){
		
		$mail_info = WebSiteConfig(SysModel::MAIL);
		
// 		dump($mail_info);die;
		$this->assign([
				'mail_info' => $mail_info,
		]);
		
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 邮件设置
	 * | @时间: 2016年11月11日 下午5:35:16
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function mailSetup(){
		
		$res = $this->model->setup(request(false));
		
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '操作成功';
			$url = U('Backend/System/mail', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
		
	}
	/**
	* 测试邮件发送
	* @date: 2017年1月11日 上午10:16:54
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function mailtest(){
	    $sys = new SysModel();
	    $data = request();
	    $result = $sys->mailtest($data);
	    if($result['status'] == '0'){
	        $res['status'] ='1';
	        $res['msg'] = '发送成功';
	        $this->ajaxReturn($res);
	    }else{
	        $res['status'] ='0';
	        $res['msg'] = '配置有误或邮箱不正确';
	        $this->ajaxReturn($res);
	    }
	}
	/**
	 * ----------------------------------------------
	 * | 关于我们，支付方式介绍等页面的设置页面
	 * | @时间: 2016年11月13日 下午4:01:25
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function pages(){
		$payment_info = WebSiteConfig(SysModel::PAYMENT);
		$about_info = WebSiteConfig(SysModel::ABOUT);
		
		// 		dump($payment_info);die;
		$this->assign([
				'payment_info' => $payment_info,
				'about_info' => $about_info,
		]);
		
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 关于我们，支付方式介绍等页面的设置
	 * | @时间: 2016年11月13日 下午4:08:16
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function pagesSetup(){
	
		$res = $this->model->setup(request(false));
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '操作成功';
			$url = U('Backend/System/pages', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	
	}
	
	/**
	 * ----------------------------------------------
	 * | 网站seo优化页面
	 * | @时间: 2016年11月13日 下午4:26:32
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function seo(){
		$where['sys_type'] = [ 'eq', SysModel::SEO ];
		$seo_info = $this->model->where($where)->select();
		
// 		dump($seo_info);die;
		$this->assign([
				'seo_info' => $seo_info,
		]);
		
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 网站seo优化设置
	 * | @时间: 2016年11月13日 下午4:28:10
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function seoSetup(){

		$res = $this->model->setup(request());
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '操作成功';
			$url = U('Backend/System/seo', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	
	}
	
	/**
	 * ----------------------------------------------
	 * | 常用操作，默认域名，和整站头部特殊公告等的设置页面
	 * | @时间: 2016年11月14日 上午10:54:57
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function common(){
		
		$top_bar = WebSiteConfig(SysModel::TOP_BAR);
		$default_domain = WebSiteConfig(SysModel::DEFAULT_DOMAIN);
		
		$m_common_product = new \Common\Model\ProductModel();
		//获取所有的中文域名
		$chinese_domain = $m_common_product->getProductByProductTypeId(10);
		//获取所有的英文域名
		$others_domain = $m_common_product->getProductByProductTypeId(9);
		
// 		dump($default_domain);die;
		$this->assign([
				'top_bar' => each($top_bar),
				'default_domain' => explode(',', $default_domain['default_domain']),
				'chinese_domain' => $chinese_domain,
				'others_domain' => $others_domain,
		]);
		
		$this->display();
	}
	
	/**
	 * ----------------------------------------------
	 * | 常用操作，默认域名，和整站头部特殊公告等的设置
	 * | @时间: 2016年11月14日 上午10:54:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function commonSetup(){
// 		dump(request());die;
		$res = $this->model->setup(request());
	
		//返回结果
		if ($res === false) {
			$msg = $this->model->getError();
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$msg = '操作成功';
			$url = U('Backend/System/seo', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	
	}
	
	
	
	
	
	
	
	
	
	
}