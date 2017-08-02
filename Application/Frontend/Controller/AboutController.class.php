<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/11/16
 * Time: 17:33
 */

namespace Frontend\Controller;

use Frontend\Controller\FrontendController;

/**
 * 关于我们
 * @package Frontend\Controller
 * @author: GuoPeng
 */
class AboutController extends FrontendController
{
    /**
     * 关于我们
     */
    public function about()
    {
        $about = new \Frontend\Model\AboutModel();
        $info = $about->get_about();
        $this->assign("about",$info);
        $this->display();
    }

    /**
     * 友情链接
     */
    public function friendlylink()
    {
        $about = new \Frontend\Model\AboutModel();
        $info = $about->get_friendlylink_list();
        $this->assign("links",$info);
        $this->display();
    }
}