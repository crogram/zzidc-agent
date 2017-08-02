<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/11/15
 * Time: 14:08
 */

namespace Frontend\Controller;

use Frontend\Controller\FrontendController;

/**
 * 帮助中心
 * @package Frontend\Controller
 */
class HelpController extends FrontendController
{
    /**
     * 帮助中心主页
     */
    public function helpindex()
    {
        $help = new \Frontend\Model\HelpModel();
        $where = $help->select_keywords();
        $info = $help->get_help_list($where);
        $this->assign([
            "problem"=>$info["problem"],
            "product"=>$info["product"],
        ]);
        $this->assign('is_help_info',true);
        $this->display();
    }

    /**
     * 帮助中心检索
     */
    public function help_retrieval()
    {
        $help = new \Frontend\Model\HelpModel();
        $where = $help->select_keywords();
        $info = $help->get_help_retriecal_list($where);
        $keywords = I("get.keywords");
        $this->assign([
            "problems"=>$info["list"],
            "show"=>$info["show"],
            "keywords"=>$keywords,
        ]);
        $info = $help->get_help_list($where);
        $this->assign([
            "product"=>$info["product"],
        ]);
        $this->assign('is_help_info',true);
        $this->display();
    }

    /**
     * 帮助问题详细内容
     */
    public function help_details()
    {
        $help = new \Frontend\Model\HelpModel();
        $where = $help->select_keywords();
        $info = $help->get_help_details($where);
        $this->assign([
            "problem"=>$info["problem"],
            "next"=>$info["next"],
            "prev"=>$info["prev"]
        ]);
        $this->assign('is_help_info',true);
        $this->display();
    }
}