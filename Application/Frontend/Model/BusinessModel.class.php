<?php
namespace Frontend\Model;
use Think\Model;
use Common\Data\GiantAPIParamsData;
class BusinessModel extends Model{
    protected $trueTableName ='';
       /**
    * 获取所有业务
    * @date: 2016年10月25日 上午11:17:13
    * @author: Lyubo
    * @param: $where
    * @return:
    */
    public function get_business_list($where,$per_page=5,$fields,$order,$ptype = null ){
        $info = [];
        if($where){
           $sum = $this->paging($where,$per_page,$fields,$ptype);
           $data = $this->queryBuilder($where,$fields,$order,$ptype)->limit($sum['page']->firstRow.','.$sum['page']->listRows)->select();
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
            $this->error = "查询条件为空";
            return false;
        }
    }
    /**
    * 获取该会员业务
    * @date: 2016年10月29日 下午2:27:29
    * @author: Lyubo
    * @param:$user_id,$business_id
    */
    public function get_business_one($business_id){
        $where["user_id"] = array("eq",session("user_id"));
        $where["business_id"] = array("eq" , $business_id);
        return $this->where($where)->find(); 
    }
   /**
   * 获取该会员业务
   * @date: 2016年10月29日 下午2:39:12
   * @author: Lyubo
   * @param: $api_bid
   * @return:
   */
    public function get_api_bid_one($business_id){
        $where["user_id"] = array("eq",session("user_id"));
        $where["api_bid"] = array("eq" , $business_id);
        return $this->where($where)->find();
    }
    /**
    * 公共分页
    * @date: 2016年10月27日 下午6:07:17
    * @author: Lyubo
    * @param: $page
    * @return:
    */
    public  function paging($where,$per_page =5,$fields,$ptype = null){
        $sumpage = [];
        $count = $this->queryBuilder($where,$fields,'',$ptype)->count();
        $Page   = new \Think\Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig("first", "首页");
        $Page->setConfig("last", "尾页");
        $date = request();
        //分页跳转的时候保证查询条件
        foreach($date as $key=>$val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $sumpage['page'] = $Page;
        $sumpage['count'] = $count;
        return $sumpage;
    }
    /**
    * 获取带条件的总记录数
    * @date: 2016年10月27日 下午7:17:01
    * @author: Lyubo
    */
    function queryBuilder($where,$fields,$order="DESC",$ptype = null){
        if(strpos($ptype,GiantAPIParamsData::PTYPE_CLOUD_SERVER) !==false ||
            strpos ( $ptype, GiantAPIParamsData::PTYPE_SSL) !==false  ||
            strpos ( $ptype, GiantAPIParamsData::PTYPE_DOMAIN) !==false ||
            strpos ( $ptype, GiantAPIParamsData::PTYPE_CLOUD_DATABASE) !==false){
            $order_where = "bs.create_time ".$order;
        }else{
            $order_where = "bs.open_time ".$order;
        }
        $where["state"] = array("neq",2);
        return $this->alias(' bs ')
        ->field($fields)
        ->join('left join '.C('DB_PREFIX').'product as p on p.id = bs.product_id')
        ->join('inner join '.C('DB_PREFIX').'product_type as pt on p.product_type_id= pt.id ')
        ->where( $where )->order($order_where);//返回带条件的总记录数
    }
    /**
     * 获取业务总数
     * @date: 2017年4月11日 下午5:05:30
     * @author: Lyubo
     * @param: $GLOBALS
     * @return:
     */
    public function host_stateCount(){
        $date =request();
        $where["bs.user_id"] = array("eq",session("user_id"));
        if($date["state"] == '2'){//1为正常业务总数，2为待续费业务总数
            $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
            $where['bs.overdue_time'] = ['LT',$overdate];
        }
        if(empty($date["virtual_type"])){
            $where["bs.virtual_type"] = array("in","0,4");
        }else{
            $where["bs.virtual_type"] = array("eq",$date["virtual_type"]);
        }
        return $this->queryBuilder($where)->count();
    }
    /**
    * 获取快云服务器业务总数
    * @date: 2017年4月11日 下午5:05:30
    * @author: Lyubo
    * @param: $GLOBALS
    * @return:
    */
    public function cloudserver_stateCount($ptype){
         $date =request();
         $where["bs.user_id"] = array("eq",session("user_id"));
        if($date["state"] == '2'){//1为正常业务总数，2为待续费业务总数
            $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
            $where['bs.overdue_time'] = ['LT',$overdate];
            /* $hidden_business_date = date ( 'Y-m-d H:i:s', strtotime ( "-30 day" ) );
            $where['bs.overdue_time'] = ['GT',$hidden_business_date]; */
        }
        return $this->queryBuilder($where,'bs.id','',$ptype)->count();
    }
    /**
     * 获取业务总数
     * @date: 2017年4月11日 下午5:05:30
     * @author: Lyubo
     * @param: $GLOBALS
     * @return:
     */
    public function stateCount(){
        $date =request();
        $where["bs.user_id"] = array("eq",session("user_id"));
        if($date["state"] == '2'){//1为正常业务总数，2为待续费业务总数
            $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
            $where['bs.overdue_time'] = ['LT',$overdate];
        }
        return $this->queryBuilder($where)->count();
    }
    /**
    * 获取VPS业务列表
    * @date: 2016年10月28日 下午12:03:36
    * @author: Lyubo
    */
    function get_vps_business_list($where,$page,$order){//field 如果查询数据较多，用field过滤不需要的字段
        if(empty($order)){
            $order = "DESC";
        }
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.system_type,bs.business_id,bs.product_id,bs.product_name,bs.ip_address,bs.system_user,bs.open_time,bs.overdue_time,bs.create_time,bs.state,bs.service_time,bs.free_trial,bs.note_appended,bs.area_code,bs.mail_state,bs.remoteport,bs.beizhu,bs.sync_state,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order);
    }
    /**
     * 获取待续费业务列表
     * @date: 2016年10月28日 下午12:03:36
     * @author: Lyubo
     */
    function get_vps_renew_list($where,$page,$order){//field 如果查询数据较多，用field过滤不需要的字段
        if(empty($order)){
            $order = "DESC";
        }
        $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
        $where['overdue_time'] = ['LT',$overdate];
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.system_type,bs.business_id,bs.product_id,bs.product_name,bs.ip_address,bs.system_user,bs.open_time,bs.overdue_time,bs.create_time,bs.state,bs.service_time,bs.free_trial,bs.note_appended,bs.area_code,bs.mail_state,bs.remoteport,bs.beizhu,bs.sync_state,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order);
    }
    /**
    * 获取云空间业务
    * @date: 2016年10月29日 上午10:38:26
    * @author: Lyubo
    * @return:
    */
    function get_cloudspace_business_list($where,$page,$order){
        if(empty($order)){
            $order = "DESC";
        }
        $fields = "bs.id as yid,bs.user_id,bs.business_id,bs.product_id,bs.ip_address,bs.open_time,bs.overdue_time,bs.create_time,bs.state,bs.service_time,bs.free_trial,bs.note_appended,bs.mail_state,bs.beizhu,p.product_name,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order);
    }
    /**
     * 获取云空间待续费业务
     * @date: 2016年10月29日 上午10:38:26
     * @author: Lyubo
     * @return:
     */
    function get_cloudspace_renew_list($where,$page,$order){
        if(empty($order)){
            $order = "DESC";
        }
        $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
        $where['overdue_time'] = ['LT',$overdate];
        $fields = "bs.id as yid,bs.user_id,bs.business_id,bs.product_id,bs.ip_address,bs.open_time,bs.overdue_time,bs.create_time,bs.state,bs.service_time,bs.free_trial,bs.note_appended,bs.mail_state,bs.beizhu,p.product_name,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order);
    }
    /**
    * 获取虚拟主机业务列表
    * @date: 2016年10月28日 下午1:40:13
    * @author: Lyubo
    * @param: $where,$page
    * @return:
    */
    function get_host_business_list($where,$page,$order){
        if(empty($order)){
            $order = "DESC";
        }
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.business_id,bs.system_type,bs.bindDomain,bs.domain_name,bs.product_id,bs.product_name,bs.ip_address,bs.open_time,bs.overdue_time,bs.virtual_type,bs.create_time,bs.state,bs.service_time,bs.free_trial,bs.note_appended,bs.area_code,bs.mail_state,bs.beizhu,bs.sync_state,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order);
    }
    /**
     * 获取待续费业务列表
     * @date: 2016年10月28日 下午1:40:13
     * @author: Lyubo
     * @param: $where,$page
     * @return:
     */
    function get_host_renew_list($where,$page,$order){
        if(empty($order)){
            $order = "DESC";
        }
        $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
        $where['overdue_time'] = ['LT',$overdate];
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.business_id,bs.system_type,bs.bindDomain,bs.domain_name,bs.product_id,bs.product_name,bs.ip_address,bs.open_time,bs.overdue_time,bs.virtual_type,bs.create_time,bs.state,bs.service_time,bs.free_trial,bs.note_appended,bs.area_code,bs.mail_state,bs.beizhu,bs.sync_state,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order);
    }
    
    /**
    * 获取域名列表
    * @date: 2016年10月29日 下午1:39:10
    * @author: Lyubo
    * @return:
    */
    function get_domain_business_list($where,$page,$order,$ptype = null){
        $fields = "bs.id as yid,bs.user_id,bs.api_bid,bs.domain_name,bs.provider,bs.product_id,bs.product_name,bs.create_time,bs.overdue_time,bs.create_time,bs.state,bs.mail_state,bs.beizhu,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order,$ptype);
    }
    /**
    * 获取快云服务器列表
    * @date: 2016年10月29日 上午11:07:26
    * @author: Lyubo
    */
    function get_cloudserver_business_list($where,$page,$order,$ptype = null){
        if(empty($order)){
            $order = "DESC";
        }
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.ip_state,bs.api_bid,bs.product_id,bs.product_name,bs.cpu,bs.memory,bs.disk,bs.buy_time,(SELECT ipaddress FROM `agent_cloudserver_business_ip` cip WHERE cip.api_bid=bs.ip_bid) ip_address,bs.overdue_time,bs.nw_ip,bs.os_type,bs.create_time,bs.state,bs.buy_time,bs.free_trial,bs.area_code,bs.mail_state,bs.beizhu,bs.sync_state,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order,$ptype);
    }
    /**
     * 获取快云服务器待续费业务列表
     * @date: 2016年10月29日 上午11:07:26
     * @author: Lyubo
     */
    function get_cloudserver_renew_list($where,$page,$order,$ptype = null){
        if(empty($order)){
            $order = "DESC";
        }
        $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
        $where['overdue_time'] = ['LT',$overdate];
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.ip_state,bs.api_bid,bs.product_id,bs.product_name,bs.cpu,bs.memory,bs.disk,bs.buy_time,(SELECT ipaddress FROM `agent_cloudserver_business_ip` cip WHERE cip.api_bid=bs.ip_bid) ip_address,bs.overdue_time,bs.nw_ip,bs.os_type,bs.create_time,bs.state,bs.buy_time,bs.free_trial,bs.area_code,bs.mail_state,bs.beizhu,bs.sync_state,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order,$ptype);
    }
    /**
     * 获取IP列表
     * @date: 2016年10月29日 上午11:07:26
     * @author: Lyubo
     */
    function get_cloudserver_business_ip_list($where,$page,$order,$ptype = null){
        if(empty($order)){
            $order = "DESC";
        }
        $fields = "bs.id as yid, bs.user_id,bs.login_name,bs.product_id,bs.product_name,bs.create_time,bs.overdue_time,bs.buy_time,bs.free_trial,bs.belong_server,bs.des,bs.state,bs.api_bid,bs.mail_state,bs.beizhu,bs.ipaddress,bs.bandwidth,bs.sync_state,bs.area_code";
        return $this->get_business_list($where,$page,$fields,$order,$ptype);
    }
    /**
     * 获取IP待续费列表
     * @date: 2016年10月29日 上午11:07:26
     * @author: Lyubo
     */
    function get_cloudserver_renew_ip_list($where,$page,$order,$ptype = null){
        if(empty($order)){
            $order = "DESC";
        }
        $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
        $where['overdue_time'] = ['LT',$overdate];
        $fields = "bs.id as yid, bs.user_id,bs.login_name,bs.product_id,bs.product_name,bs.create_time,bs.overdue_time,bs.buy_time,bs.free_trial,bs.belong_server,bs.des,bs.state,bs.api_bid,bs.mail_state,bs.beizhu,bs.ipaddress,bs.bandwidth,bs.sync_state,bs.area_code";
        return $this->get_business_list($where,$page,$fields,$order,$ptype);
    }
    /**
     * 获取快云数据库列表
     * @author: Guopeng
     * @param $where
     * @param $page
     * @param $order
     * @param null $ptype
     * @return array|bool
     */
    function get_clouddb_business_list($where,$page,$order,$ptype = null){
        if(empty($order)){
            $order = "DESC";
        }
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.ywbs,bs.product_id,bs.product_name,bs.create_time,bs.overdue_time,bs.buy_time,bs.free_trial,bs.wwdz,bs.nwdz,bs.version,bs.memory,bs.disk,bs.iops,bs.conn,bs.state,bs.api_bid,bs.mail_state,bs.beizhu,bs.area_code,p.product_type_id,pt.api_ptype,p.api_name";
        $info = $this->get_business_list($where,$page,$fields,$order,$ptype);
        return $info;
    }
    /**
     * 获取快云数据库待续费列表
     * @param $where
     * @param $page
     * @param $order
     * @param null $ptype
     * @return array|bool
     */
    function get_clouddb_renew_list($where,$page,$order,$ptype = null){
        if(empty($order)){
            $order = "DESC";
        }
        $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
        $where['overdue_time'] = ['LT',$overdate];
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.ywbs,bs.product_id,bs.product_name,bs.create_time,bs.overdue_time,bs.buy_time,bs.free_trial,bs.wwdz,bs.nwdz,bs.version,bs.memory,bs.disk,bs.iops,bs.conn,bs.state,bs.api_bid,bs.mail_state,bs.beizhu,bs.area_code,p.product_type_id,pt.api_ptype,p.api_name";
        $info = $this->get_business_list($where,$page,$fields,$order,$ptype);
        return $info;
    }
    /**
     * 云空间信息
     * @date: 2016年12月1日 下午6:41:43
     * @author: Lyubo
     * @param: $cloudspace_id
     * @return: $cloudspace_info
     */
    public function cloudspace_info($cloudspace_id){
        $where['cs.id'] = ["eq",$cloudspace_id];
       return  $this->alias(' cs ')
        ->field("cs.id,cs.user_id,cs.product_id,cs.ip_address,cs.overdue_time,cs.create_time,cs.service_time,cs.business_id,cs.free_trial,cs.state,cs.open_time,cs.site_quantity,cs.space_capacity,cs.use_space_capacity,cs.use_db_capacity,cs.database_capacity,cs.database_quantity,cs.flow_capacity,cs.use_site,cs.use_db,cs.use_flow,cs.note_appended,p.product_name,p.api_name,p.system_type,p.product_state,u.login_name")
        ->join('inner join '.C('DB_PREFIX').'product as p on cs.product_id=p.id')
        ->join('inner join '.C('DB_PREFIX').'member as u where 1=1 and cs.id='.$cloudspace_id)
        ->find();
        }
        /**
         * 快云服务器信息
         * @date: 2016年12月1日 下午6:41:43
         * @author: Lyubo
         * @param: $cloudserver_id
         * @return: $cloudserver_info
         */
        public function cloudserver_info($cloudserver_id){
            $where['cs.id'] = ["eq",$cloudserver_id];
            return  $this->alias(' cs ')
            ->field("cs.id,cs.product_name,cs.state,cs.product_id,cs.create_time,cs.user_id,cs.overdue_time,cs.free_trial,cs.ip_bid,cs.api_bid,cs.ip_state,cs.ip_state,cip.id as ip_id,cip.ipaddress,cip.belong_server")
            ->join('inner join '.C('DB_PREFIX').'cloudserver_business_ip as cip on cs.api_bid=cip.belong_server')
            ->where($where)
            ->find();
        }
    /**
    * 修改备注信息
    * @date: 2016年10月27日 下午4:42:32
    * @author: Lyubo
    * @param: variable
    * @return:
    */
    public function remark_edit($state = false){
        $parms = request();
        $date['beizhu'] = $parms['remark'];
        return $result = $this->business_edit($parms['id'] , $date,$state);
    }
    /**
    * 修改业务信息
    * @date: 2016年10月27日 上午11:33:42
    * @author: Lyubo
    * @param: $date
    * @return: boolean
    */
    public function business_edit($id , $date,$state = false){
        if (! is_array ( $date ) || count ( $date ) <= 0) {
           $this->error = "查询条件为空";
             return false;
        }
        if($state){
            $where['Id'] = array("eq" , $id);
        }else{
            $where['id'] = array("eq" , $id);
        }
         $result = $this->where($where)->save($date);
        if($result !== false){
            return true;
        }else{
            return false;
        }
    }
    public function error(){
        return $this->error;
    }
    /**
    * 错误信息设置
    * @date: 2016年12月1日 下午4:31:29
    * @author: Lyubo
    * @param: $result
    * @return: $business_code
    */
    public function setError($code){
        if(is_numeric($code))
        {
            $this->error = business_code($code);
        }else{
            $this->error = $code;
        }
    }
    /**
     * 获取SSL证书列表
     * @author: Guopeng
     * @param $where
     * @param $page
     * @param $order
     * @return array|bool
     */
    function get_ssl_business_list($where,$page,$order,$ptype = null){
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.product_id,bs.product_name,bs.business_id,bs.create_time,bs.overdue_time,bs.buy_time,bs.state,bs.free_trial,bs.mail_state,bs.beizhu,bs.domain_name,bs.root_domain,bs.registrant,bs.mobile,bs.mail,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order,$ptype);
    }
    /**
     * 获取SSL证书列表
     * @author: Guopeng
     * @param $where
     * @param $page
     * @param $order
     * @return array|bool
     */
    function get_ssl_business_renew($where,$page,$order,$ptype = null){
        $overdate = date ( 'Y-m-d H:i:s', strtotime ( "+30 day" ) );
        $where['overdue_time'] = ['LT',$overdate];
        $fields = "bs.id as yid,bs.user_id,bs.login_name,bs.product_id,bs.product_name,bs.business_id,bs.create_time,bs.overdue_time,bs.buy_time,bs.state,bs.free_trial,bs.mail_state,bs.beizhu,bs.domain_name,bs.root_domain,bs.registrant,bs.mobile,bs.mail,p.product_type_id,pt.api_ptype,p.api_name";
        return $this->get_business_list($where,$page,$fields,$order,$ptype);
    }
}