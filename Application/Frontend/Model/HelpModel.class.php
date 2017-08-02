<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/11/15
 * Time: 14:58
 */

namespace Frontend\Model;

use Think\Model;

class HelpModel extends Model
{
    protected $trueTableName = 'agent_common_problem';

    /**
     * 获取查询条件
     * @author: GuoPeng
     */
    public function select_keywords(){
        $date =request();
        $where["state"] = 1;
        if(!empty($date)){
            if($date['id'] != "")
            {
                $id = $date['id']+0;
                $where['id'] = $id;
            }
            $select = clearXSS($date['keywords']);
            $type_id = $date['type_id']+0;
            if(!empty($type_id))
            {
                $where["product_type"] = $type_id;
            }
            if(!empty($select))
            {
                $product_type = M("product_type")->where(array("type_name"=>$select))->find();
                if($product_type)
                {
                    $where["product_type"] = $product_type["id"];
                }
                else
                {
                    $where['_string'] = "problem_title like '%".$select."%' or problem_content like '%".$select."%' ";
                }
            }
        }
        return $where;
    }

    /**
     * 获取帮助信息
     * @author: GuoPeng
     */
    public function get_help_list($where,$order="create_time desc")
    {
        $data["problem"] = $this->queryBuilder($where,$order)->limit(10)->field("id,problem_title")->select();
        $product_type = M("product_type")->field("id,type_name,type_des")->limit(6)->select();
        for($i=0;$i<count($product_type);$i++)
        {
            $where["product_type"] = $product_type[$i]["id"];
            $product_type[$i]["type_des"] = $this->queryBuilder($where,$order)->limit(6)->field("problem_title,id")->select();
        }
        $data["product"] = $product_type;
        return $data;
    }

    /**
     * 获取帮助信息检索列表
     * @author: GuoPeng
     */
    public function get_help_retriecal_list($where,$order="create_time desc")
    {
        $page = $this->paging($where,$order);
        $limit = $page['page']->firstRow.','.$page['page']->listRows;
        $data = $this->queryBuilder($where,$order)->limit($limit)->field("id,problem_title,update_at,problem_content")->select();
        for($i=0;$i<count($data);$i++)
        {
            $data[$i]["update_at"] = date("Y-m-d",strtotime($data[$i]["update_at"]));
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

    /**
     * 获取帮助信息问题详情
     * @author: GuoPeng
     */
    public function get_help_details($where)
    {
        $problem = $this->queryBuilder($where,"")->find();
        $problem["create_time"] = date("Y-m-d",strtotime($problem["create_time"]));
        $next_where = $prev_where = $where;
        $order = "id asc";
        $next_where["id"] = array("gt",$where["id"]);
        $next = $this->queryBuilder($next_where,$order)->field("id,problem_title")->find();
        $order = "id desc";
        $prev_where["id"] = array("lt",$where["id"]);
        $prev = $this->queryBuilder($prev_where,$order)->field("id,problem_title")->find();
        $info["problem"] = $problem;
        $info["next"] = $next;
        $info["prev"] = $prev;
        return $info;
    }

    /**
     * 公共分页
     * @author: Guopeng
     */
    public  function paging($where,$order,$per_page =10){
        $sumpage= [];
        $count      =  $this->queryBuilder($where,$order)->count();
        $Page   = new \Think\Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig("first", "首页");
        $Page->setConfig("last", "尾页");
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
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
    protected function queryBuilder($where,$order){
        return $this->where($where)->order($order);//返回带条件的总记录数
    }

    //快云服务器帮助信息获取
    public function home_news($select)
    {
        $where['_string'] = "problem_title like '%".$select."%' or problem_content like '%".$select."%'";
        $where["state"] = 1;
        $data = $this->queryBuilder($where,"create_time desc")->limit(2)->select();
        for($i=0;$i<count($data);$i++)
        {
            $data[$i]["create_time"] = date("Y-m-d",strtotime($data[$i]["create_time"]));
        }
        return $data;
    }
}