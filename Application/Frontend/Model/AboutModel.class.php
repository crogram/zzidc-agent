<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/11/16
 * Time: 17:56
 */

namespace Frontend\Model;

use Think\Model;

class AboutModel extends Model
{
    protected $trueTableName = 'agent_sys';

    /**
     * 获取关于我们信息
     */
    public function get_about()
    {
        $about = $this->where(array("sys_type"=>4))->select();
        $info["title"] = $about[0]["sys_value"];
        $info["content"] = $about[1]["sys_value"];
        return $info;
    }

    /**
     * 获取友情链接信息
     */
    public function get_friendlylink_list()
    {
        return M("links")->where(array("state"=>1))->select();
    }
}