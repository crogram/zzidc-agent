<?php
namespace Frontend\Model;
use Think\Model;
/**
* 处理订单公共类
* @date: 2016年10月29日 下午4:45:25
* @author: Lyubo
*/
class OrderManageModel extends Model{
    /**
    * 获取条件全部订单
    * @date: 2016年10月29日 下午5:30:57
    * @author: Lyubo
    * @return:
    */
    public function get_order_list($where,$per_page=5,$fields="*"){
        $info = [];
        if($where){
            $sum = $this->paging($where,$per_page,$fields);
            $data = $this->queryBuilder($where,$fields)->limit($sum['page']->firstRow.','.$sum['page']->listRows)->select();
            $info['count'] = $sum['count'];
            $info['show'] = $sum['page']->show();
            //对自带thinkphp分页进行替换
            $show = str_replace("<div>", "", $info["show"]);
            $show = str_replace("</div>", "", $show);
            $show = str_replace("span", "a", $show);
            $info['page_show'] = $show;
            $info['list'] = $data;
            return $info;
        }else{
            $this->error="查询条件为空";
            return false;
        }
    }
    /**
    * 获取带条件的总记录数
    * @date: 2016年10月29日 下午5:32:44
    * @author: Lyubo
    * @return:
    */
    public  function queryBuilder($where,$field){
         return $this->field($field)->where( $where );
    }
    /**
    * 函数用途描述
    * @date: 2016年10月29日 下午5:53:25
    * @author: Lyubo
    * @param: $GLOBALS
    * @return:
    */
    public  function paging($where,$per_page =5,$fields){
            $sumpage= [];
            $count =  $this->queryBuilder($where,$fields)->count();
            $Page   = new \Think\Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig("first", "首页");
            $Page->setConfig("last", "尾页");
            //分页跳转的时候保证查询条件
            foreach($where as $key=>$val) {
                $Page->parameter[$key]   =   urlencode($val);
            }
            $sumpage['page'] = $Page;
            $sumpage['count'] = $count;
            return $sumpage;
        }
    }
