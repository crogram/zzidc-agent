<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/11/11
 * Time: 11:49
 */

namespace Frontend\Controller;

use Frontend\Controller\FrontendController;

/**
 * Class 新闻公告活动动态资讯
 * @package Frontend\Controller
 * @date: 2016年11月11日 上午11:51:53
 * @author: GuoPeng
 */
class ArticleController extends FrontendController
{
    /**
     * 获取新闻公告活动动态资讯
     * @date: 2016年11月11日 上午11:58:42
     * @author: GuoPeng
     */
    public function articlelist()
    {
        $keywords = I('get.keywords');
        $article = new \Frontend\Model\ArticleModel();
        $where = $article->select_keywords();
        $info = $article->get_article_list($where);
        $this->assign([
            "articles"=>$info["list"],
            "show"=>$info["show"],
            "type"=>$where["type"],
            "keywords"=>$keywords
        ]);
        $this->display();
    }

    //获取资讯详细信息
    public function article_details()
    {
        $article = new \Frontend\Model\ArticleModel();
        $info = $article->article_details();
        $this->assign([
            "news"=>$info["news"],
            "next"=>$info["next"],
            "prev"=>$info["prev"]
        ]);
        $this->display();
    }
}