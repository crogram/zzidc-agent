<?php
namespace Frontend\Model;
use Think\Model;

class FinancialModel extends Model{
    protected $trueTableName = 'agent_member_account';
    
    public function conver_par(){
        $date =request();
        $where["user_id"] = array("eq",session("user_id"));
        if(!empty($date['select'])){
            $where["note_appended"] = array("like" , "%".$date['select']."%");
        }
        return $where;
    }
    /**
    * 获取该会员账户金额信息
    * @date: 2016年11月2日 下午4:13:51
    * @author: Lyubo
    * @return: 余额，消费记录
    */
    public function account_info($where){
        return $this->where($where)->find();
    }
    /**
    * 函数用途描述
    * @date: 2016年11月11日 下午2:28:24
    * @author: Lyubo
    * @param: $member_id
    * @param:$balance   余额
    * @param: $purchases 消费总额
    * @return: boolean
    */
    public function edit_member_account($member_id , $balance , $purchases){
        $where["user_id"] = array("eq" , $member_id);
        $data["balance"] = $balance;
        $data["purchases"] = $purchases;
        $data["update_time"] = current_date();
        return $this->where($where)->save($data);
    }
    /**
    * 会员交易列表
    * @date: 2016年11月2日 下午6:13:40
    * @author: Lyubo
    * @param: $where
    * @return: $list
    */
    public function tran_list($where,$per_page=5){
        $info = [];
        if($where){
           $sum = $this->paging($where,$per_page);
           $data = $this->queryBuilder($where)->limit($sum['page']->firstRow.','.$sum['page']->listRows)->select();
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
     * @date: 2016年10月27日 下午7:17:01
     * @author: Lyubo
     */
    function queryBuilder($where,$order="DESC"){
        $transactions = M("member_transactions");
         return $transactions->alias(' as ts ')
        ->field("user_id,login_name,order_id,account_money,product_id,note_appended,type,create_time,recharge_type,remark,(select product_name from agent_product where id = product_id) as product_name")
        ->where($where)->order("create_time ".$order);//返回带条件的总记录数
    }
    /**
     * 公共分页
     * @date: 2016年10月27日 下午6:07:17
     * @author: Lyubo
     * @param: $page
     * @return:
     */
    public  function paging($where,$per_page =5,$fields){
        $sumpage= [];
        $count      =  $this->queryBuilder($where,$fields)->count();
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
    * 会员在线冲值
    * @date: 2016年11月3日 下午4:07:42
    * @author: Lyubo
    * @param:$user_id，$money
    */
    public function user_recharge_online($user_id,$money,$order_no,$sign_type){
        $member = new \Frontend\Model\MemberModel();
        $member_where['user_id'] = ['eq',$user_id];
        $user_info=$member->get_member_info($member_where);
        $par=array(
             'user_id'=>$user_id,
            'login_name'=>$user_info['login_name'],
            'account_money'=>$money,
            'create_time'=>date('Y-m-d H:i:s'),
            'update_time'=>date('Y-m-d H:i:s'),
            'order_id'=>$order_no,
            'SFT_SignType'=>$sign_type,
            'SFT_OrderNo'=>' ',
            'note_appended'=>'用户:'.$user_info['login_name'].'在线充值：'.$money.'元',
            'state'=>\Common\Data\StateData::STATE_ONLINE_TRAN_WAIT
        );
        $transaction = M("member_online_transactions");
        $result = $transaction->add($par);
        if($result !==false){
            return $result;
        }else{
            return false;
        }
    }
    /**
    * 修改支付订单状态
    * @date: 2016年12月8日 下午2:39:54
    * @author: Lyubo
    * @param: $up_where,$update_info
    * @return: boolean
    */
    public function update_member_online($up_where,$updata_info){
       return  $this->where($up_where)->save($updata_info);
    }
    
}