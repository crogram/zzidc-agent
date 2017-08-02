<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/11/11
 * Time: 11:23
 */

namespace Frontend\Model;

use Think\Model;

class ArticleModel extends Model
{
    protected $trueTableName = 'agent_article';

    /**
     * 获取查询条件
     * @return $where
     * @author: GuoPeng
     */
    public function select_keywords()
    {
        $date = request();
        $where["state"] = 1;
        if(!empty($date['id']))
        {
            $id = $date['id'] + 0;
            $where['id'] = $id;
        }
        if(!empty($date['type']))
        {
            $type = $date['type'] + 0;
            $where['type'] = $type;
        }
        if(!empty($date['keywords']))
        {
            $select = clearXSS($date['keywords']);
            $where['_string'] = "title like '%".$select."%' or summary like '%".$select."%' or content like '%".$select."%'";
        }

        return $where;
    }
    /**
     * 获取新闻公告活动资讯列表
     * @author: GuoPeng
     */
    public function get_article_list($where,$order="create_time desc")
    {
        $page = $this->paging($where,$order);
        $limit = $page['page']->firstRow.','.$page['page']->listRows;
        $data = $this->queryBuilder($where,$order,$limit)->select();
        for($i=0;$i<count($data);$i++)
        {
            $data[$i]["create_time"] = date("Y-m-d",strtotime($data[$i]["create_time"]));
        }
        $info['count'] = $page['count'];
        $info['show'] = $page['page']->show();
        //对自带thinkphp分页进行替换
        $show = str_replace("<div>", "", $info["show"]);
        $show = str_replace("</div>", "", $show);
        $show = str_replace("span", "a", $show);
        $show = str_replace('class="num"', "", $show);
        $show = str_replace('class="next"', "", $show);
        $show = str_replace('class="prev"', "", $show);
        $show = str_replace('class="first"', "", $show);
        $show = str_replace('class="last"', "", $show);
        $info['show'] = $show;
        $info['list'] = $data;
        return $info;
    }

    //新闻动态详细信息
    public function article_details()
    {
        $where = $this->select_keywords();
        $news = $this->queryBuilder($where)->find();
        $news["create_time"] = date("Y-m-d",strtotime($news["create_time"]));
        $next_where = $prev_where = $where;
        $order = "id asc";
        $next_where["id"] = array("gt",$where["id"]);
        $next = $this->queryBuilder($next_where,$order,1)->field("id,title")->find();
        $order = "id desc";
        $prev_where["id"] = array("lt",$where["id"]);
        $prev = $this->queryBuilder($prev_where,$order,1)->field("id,title")->find();
        $info["news"] = $news;
        $info["next"] = $next;
        $info["prev"] = $prev;
        return $info;
    }

    //获取前台首页动态资讯
    public function home_news($where)
    {
        $where["state"] = 1;
        $data = $this->queryBuilder($where,"create_time desc",$limit=2)->select();
        for($i=0;$i<count($data);$i++)
        {
            $data[$i]["create_time"] = strtotime($data[$i]["create_time"]);
        }
        return $data;
    }

    /**
     * 公共分页
     * @date: 2016年11月11日 下午3:58:17
     * @author: Guopeng
     * @param: $page
     * @return:
     */
    public  function paging($where,$order,$per_page =10){
        $sumpage= [];
        $count      =  $this->queryBuilder($where,$order)->count();
        $Page   = new \Think\Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig("first", "首页");
        $Page->setConfig("last", "尾页");
        $request = request();
        //分页跳转的时候保证查询条件
        foreach($request as $key=>$val) {
            $Page->parameter[$key]   =   urlencode($val);
        }
        $sumpage['page'] = $Page;
        $sumpage['count'] = $count;
        return $sumpage;
    }
    /**
     * 获取带条件的总记录数
     * @date: 2016年11月11日 下午4:00:01
     * @author: Guopeng
     */
    function queryBuilder($where,$order="create_time desc",$limit=""){
        return $this->where($where)->order($order)->limit($limit);//返回带条件的总记录数
    }
}