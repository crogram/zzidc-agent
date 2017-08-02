<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/11/11
 * Time: 11:06
 */

namespace Frontend\Model;

use Think\Model;

class AdModel extends Model
{
    protected $trueTableName = 'agent_ad';

    //获取前台首页焦点图片
    public function banner($location,$limit)
    {
        $banners = $this->where(array("location"=>$location,"is_show"=>1))
            ->order("order_number","asc")
            ->field("title,img_url,link_url,location,description")
            ->limit($limit)
            ->select();
        return $banners;
    }
}