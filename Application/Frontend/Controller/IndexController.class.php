<?php
namespace Frontend\Controller;

use Frontend\Controller\FrontendController;

/**
 * -------------------------------------------------------
 * | 前台首页
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午5:06:28
 * |@version: 1.0
 * -------------------------------------------------------
 */
class IndexController extends FrontendController
{
    protected $captcha_type_white = 'frontend_white';
    
    public function index()
    {
        $ad = new \Frontend\Model\AdModel();
        $banners = $ad->banner(1,4);
        $this->assign('banners',$banners);
        $article = new \Frontend\Model\ArticleModel();
        $domain = new \Frontend\Model\DomainModel();
        $default_domain = $domain->get_default_domain();
        $news = $article->home_news(array("type"=>1));
        $notices = $article->home_news(array("type"=>3));
        $activities = $article->home_news(array("type"=>2));
        $this->assign([
            'news'=>$news,
            'notices'=>$notices,
            'activities'=>$activities,
            'default_domain'=>$default_domain,
        ]);
        $this->assign('is_index',true);
        $this->display();
    }
    /**
     * 判断前台会员是否登陆
     * @date: 2016年11月8日 上午10:23:37
     * @author: Lyubo
     * @return:
     */
    public function is_member_login(){
        if(session('?user_id')){
            $data["status"] = true;
            $data['user_name'] = session('user_name');
            $this->ajaxReturn($data);
        }else{
            $data["status"] = false;
            $this->ajaxReturn($data);
        }
    }
    public function SLA()
    {
        $this->display("Index/SLA");
    }
    public function full_refund()
    {
        $this->display();
    }
    public function worryfree_service()
    {
        $this->display();
    }
    
}