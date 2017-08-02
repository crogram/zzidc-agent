<?php
namespace Frontend\Model;
use Common\Data\StateData;
use Think\Model;
use Common\Data\GiantAPIParamsData;
class ProductModel extends Model{
    protected $trueTableName = 'agent_product';

    public function conver_par(){
        $date =request();
        if($date['type']==null){
            $date['type'] = 'host';
        }
        return $date;
    }

    /**
     * 虚拟主机页面和vps页面banner图
     * @author: Guopeng
     * @param $location
     * @return mixed
     */
    public function banner($location)
    {
        $ad = new \Frontend\Model\AdModel();
        $banner = $ad->banner($location,2);
        return $banner;
    }
/*******************************************获取列表*******************************************/
    /**
    *  获取虚拟主机列表
    * @date: 2016年11月4日 下午6:47:55
    * @author: Lyubo
    * @param: $where 产品类型
    * @return:
    */
    public function get_host_product($where){
       $product_type_list = $this->get_type_list("3");//3是虚拟主机的大类
       $product_list = [];
       $key = 1;
       foreach ( $product_type_list as $ptl ) {
           $product_list[$key++] = $this->get_type_product($ptl ['id']);
       }
	  return $product_list;

    }
    /**
     *  获取VPS列表
     * @date: 2016年11月4日 下午6:47:55
     * @author: Lyubo
     * @param: $where 产品类型
     */
    public function get_vps_product($where){
        //TODO 获取手机站的价格
        $product_type_list = $this->get_type_list_all ();
        $product_list = [];
        $vps_type_list = [];
        if($where['type'] == 'fastvps'){
            $ptype = GiantAPIParamsData::PTYPE_FAST_CLOUDVPS;
        }else if($where['type'] == 'domesticvps' || $where['type'] == 'hkvps'){
            $ptype = GiantAPIParamsData::PTYPE_VPS;
        }
        foreach ( $product_type_list as $ptl ) {
            if (strcmp ( $ptl ['api_ptype'], $ptype) == 0) {
				array_push($vps_type_list,$ptl ['id']);
			}
        }
        if(count($vps_type_list)<=0){
            return null;
        }
        // 国内vps产品
        $domestic_vps_product = [];
        // 香港vps产品
        $hk_vps_product = [];
        // 快云vps产品
        $vps_product_list = [];
        for($i = 0; $i < count ( $vps_type_list ); $i ++) {
            $temp_list = $this->get_type_product ( $vps_type_list[$i] ,"product_type_id asc,up_level asc");
            if(!$temp_list){
                return null;
            }
            if(empty($vps_product_list)){
                $vps_product_list = $temp_list;
            }else{
                $vps_product_list = array_merge($vps_product_list,$temp_list);
            }
        }
        if($where['type'] !='fastvps'){
            for($i = 0; $i < count ( $vps_product_list ); $i ++) {
                // 获取api_name字段第二个.的位置
                $position = strripos ( $vps_product_list [$i] ['api_name'], '.' );
                $product_name = $vps_product_list [$i] ['api_name'];
                if($where['type'] == 'domesticvps'){
                    if (strcasecmp ( substr ( $vps_product_list [$i] ['api_name'], 0, $position ), 'temai.vps' ) == 0) {
                         $domestic_vps_product[$i] = $vps_product_list [$i];
                    }
                }
                if($where['type'] == 'hkvps'){
                    if (strcasecmp ( substr ( $vps_product_list [$i] ['api_name'], 0, $position ), 'hk.vps' ) == 0) {
                        $hk_vps_product[$i] = $vps_product_list [$i];
                    }
                }
            }
        }
        $vps_product_list['mobile_price'] = $fastvps_price;
        if($where['type'] == 'domesticvps'){
            return $domestic_vps_product;
        }else if($where['type'] == 'hkvps'){
            return $hk_vps_product;
        }else if($where['type'] == 'fastvps'){
            return $vps_product_list;
        }
    }
/*****************************域名**********************************/
    /**
    * 域名产品信息、价格
    * @date: 2016年11月20日 下午2:47:37
    * @author: Lyubo
    * @return:
    */
    public function domain_info($ptype_id){
        $map["product_state"] = array("eq",1);
        $map["product_type_id"] = array("eq" , $ptype_id);
        $map["type"] = array("eq" , 0);// 0 为标准产品，1为增值产品
        $map["_string"] = "FIND_IN_SET ('2',stage)";//此处stage
        $product = $this->queryBuilder($map)->select();//product表和product_ptype表关联查询
        $product_price = M('product_price');
        foreach ($product as $key=>$val){
            $where["state"] = 1;
           $where['api_type'] = ['eq','5'];
           $where['product_id'] = ['eq',$val['id']];
           $val['price'] = $product_price->where($where)->select();
           $product[$key] = $val;
        }
        return $product;
    }
    /**
     * 获取域名api价格
     * @date: 2016年11月27日 下午12:00:48
     * @author: Lyubo
     * @param: product__id
     * @return: api_type
     */
    public function getApiType($product_id){
        $price = M("product_price");
        $where['state'] = ["eq",1];
        $where['type'] = ["eq",\Common\Data\StateData::STATE_BUY];
        $where["id"] = ["eq",$product_id];
        return $price->field("api_type, month")
        ->where($where)->find();
    }
    /**
     * 根据id列表获取产品信息
     *
     * @param: $ids id列表，以逗号分隔
     * @param: $type 域名列表用到
     */
    public function product_by_ids($ids,$type = null) {
        $where['p.product_state'] = array('eq','1');
        if($type != null){
            $where['pt.api_ptype'] = array('eq' , $type);
        }
        $where['p.id'] = array('IN' , $ids);
        $product = $this->alias(' p ')
        ->field("p.id,p.product_name,p.system_type,p.product_type_id,p.api_name,p.product_des,pt.type_name,pt.api_ptype")
        ->join('inner join '.C('DB_PREFIX').'product_type as pt on p.product_type_id=pt.id' )
        ->where($where)->select();
        return $this->get_product_price_by_product($product);
    }
    /**
    * 所有分类信息
    * @date: 2016年11月13日 下午5:59:09
    * @author: Lyubo
    * @return:
    */
   public function get_type_list_all(){
       $product_type = M("product_type");
       return $product_type->select();
   }
    /**
     * 获取类型信息
     * @date: 2016年11月4日 下午4:49:38
     * @author: Lyubo
     * @param: $parent_id  类型父类ID
     * @return: $ptype_list  返回子类
     */
    public function get_type_list($parent_id){
        $product_type = M("product_type");
        $where["parent_id"] = array("eq" , $parent_id);
        $where["display"] = array("eq","1");
        return $product_type->where($where)->select();
    }
    /**
    * 类型下的产品信息
    * @date: 2016年11月4日 下午4:42:58
    * @author: Lyubo
    * @param: $type_id 类型ID
    */
    public function get_type_product($type_id,$order="id asc"){
        $map["product_state"] = array("eq",1);
        $map["product_type_id"] = array("eq" , $type_id);
        $map["type"] = array("eq" , 0);// 0 为标准产品，1为增值产品
        $map["_string"] = "FIND_IN_SET ('2',stage)";//此处stag
        $product = $this->queryBuilder($map)->order($order)->select();//product表和product_ptype表关联查询
       return $this->get_product_price_by_product($product);

    }
    /**
    * 获取产品价格,产品配置
    * @date: 2016年11月4日 下午6:00:46
    * @author: Lyubo
    * @param: $product
    * @return: $price
    */
    public function get_product_price_by_product($product_list){
        for($i=0;$i<count($product_list);$i++){
            //第三个参数5为查询中国数据的域名价格
            $price_list=$this->get_product_price_list($product_list[$i]['id'], \Common\Data\StateData::STATE_BUY);
            if(!$price_list){
                return false;
            }
            $product_config=$this->get_product_config($product_list[$i]['id']);
            $product_list[$i]['config']=$product_config;
            $product_list[$i]['price_list']=$price_list;
        }
        return $product_list;
    }
    /**
     * 获取产品配置信息
     * @date: 2016年11月4日 下午6:16:07
     * @author: Lyubo
     * @param: $product_id 产品ID
     * @param: $is_upgrade 是否是升级产品
     * @return: $product_config
     */
    public function get_product_config($product_id,$is_upgrade=false){
        $product_config = M("product_config");
        if ($is_upgrade) {
            $condition = " and app_name !='' ";
        }
        $where["p.id"]  = array("eq" , $product_id);
        return $product_config->alias(' c ')
        ->field('p.id,p.product_name,c.config_key,c.config_value,c.app_name,c.unit,c.en_name,c.details')
        ->join('left JOIN '.C('DB_PREFIX').'product as p on c.product_id=p.id')
        ->where($where)->select();
    }
    /**
    * 获取快云服务器价格
    * @date: 2016年11月26日 上午10:39:20
    * @author: Lyubo
    * @param: $condition
    * @return:
    */
    public function get_cloud_price_list($condition){
        $price = M('product_price');
        $where['state'] = ['eq','1'];
        $where['note_appended'] = ['eq',$condition];
         return $price->where($where)->find();
    }
    /**
     * 获取快云数据库价格
     * @author: Guopeng
     * @return:
     */
    public function get_database_price($where){
        $price = M('product_price');
        $where['state'] = ['eq','1'];
        return $price->where($where)->find();
    }
    /**
     * 获取SSL价格
     * @date: 2017年4月6日 下午17:20:20
     * @author: Guopeng
     * @param: $condition
     * @return:
     */
    public function get_ssl_price($buy_info){
        $price = 0;
        $multi = $buy_info["multi"];
        if($multi[0] != 0 && $multi[1] != 0){
            return false;
        }elseif($multi[1] != 0 && $multi[2] != 0){
            return false;
        }
        $product_where['product_type_id'] = $buy_info['product_type_id'];
        $product_where['product_des'] = ['eq',$buy_info["product_des"]];
        $product_where['product_state'] = ['eq','1'];
        $tongpei_product = $this->where($product_where)->find();
        if(!$tongpei_product){
            return false;
        }
        $product_price = M('product_price');
        $price_where['state'] = ['eq','1'];
        $price_where['product_id'] = ['eq',$tongpei_product["id"]];
        $tongpei_price = $product_price->where($price_where)->find();
        if(!$tongpei_price){
            return false;
        }
        $chanpin_price = $buy_info['product_price'];
        if(is_numeric($multi[0]) && is_numeric($multi[1]) && is_numeric($multi[2])){
            $multi_domain = $tongpei_price["product_price"] * $multi[0] * $buy_info["order_time"];
            $multi_global = ($chanpin_price * 2 - 6) * $multi[1];
            $multi_servers = $chanpin_price * $multi[2];
            if($multi[2] > 0){
                $multi_domain = $multi_domain * ($multi[2] + 1);
            }
        }else{
            return false;
        }
        $price = $chanpin_price + $multi_domain + $multi_global + $multi_servers;
        return $price;
    }
    /**
    * 获取产品价格列表
    * @date: 2016年11月4日 下午6:03:35
    * @author: Lyubo
    * @param: $product_id,buy_state
    * @return: $price
    */
    public function get_product_price_list($product_id,$state,$yid=null){
        $product_price = M("product_price");
        $where["state"] = 1;
        $where["pr.type"] = array("eq" , $state);
        $where["product_id"] = array("eq" , $product_id);
        return $product_price->alias(' pr ')
        ->field("p.type,p.product_name,pr.id,pr.product_id,pr.month,pr.giant_price,pr.min_price,pr.product_price,pr.type,pr.note_appended,pr.state,pr.create_time,pr.up_time,pr.api_type")
        ->join('inner join '.C('DB_PREFIX').'product as p on pr.product_id=p.id')
        ->order('pr.month ASC')   //时间排序，产品展示页面
        ->where($where)->select();
    }
/****************************************************主机显示*****************************************/
    /**
     * 组合查询
     * @date: 2016年10月27日 下午7:17:01
     * @author: Lyubo
     */
    function queryBuilder($where){
        return $this->alias(' p ')
        ->field("p.id,p.product_name,p.product_des,p.product_state,p.product_sales,p.product_volume,p.product_type_id,p.api_name,p.up_level,p.system_type,p.area_code,pt.api_ptype,p.size,p.unit,p.stage")
        ->join('LEFT JOIN '.C('DB_PREFIX').'product_type as pt on p.product_type_id=pt.id')
        ->where( $where );//返回带条件的总记录数
    }
    /**
     * 获取单个产品ID的所有购买价格
     * @date: 2016年11月11日 下午5:31:56
     * @author: Lyubo
     * @local   : OrderModel
     * @param: $product_id 产品ID
     * @param: array $data 查询条件
     * @return mixed
     */
    public function get_product_price_buy_time($product_id,$data=array()){
        $product_price = M("product_price");
        $where['state'] = 1;
        $where['product_id'] = array("eq" , $product_id);
        $where["type"] = array("eq",StateData::STATE_BUY);
        if(!empty($data)){
            if(!is_null($data['month'])){
                $where["month"] = $data['month'];
            }
            if(!is_null($data['type'])){
                $where['type'] = $data['type'];
            }
            if(!is_null($data['api_type'])){
                $where['api_type'] = $data['api_type'];
            }else{
                $where['_string'] = "api_type is null or api_type=''";
            }
            return $product_price->where($where)->find();
        }
        return $product_price->where($where)->select();
    }
    /**
    * 获取产品信息
    * @date: 2016年11月9日 下午3:28:42
    * @author: Lyubo
    * @param: $product_id
    * @return: $product_info
    */
    function get_product($product_id){
        $where['p.id'] = array("eq",$product_id);
        $product_info = $this->queryBuilder($where)->find();
        return $product_info;
    }
    /**
    * 获取产品类别信息
    * @date: 2016年11月10日 下午6:21:11
    * @author: Lyubo
    * @param: id 产品类别编号
    * @return:
    */
    function get_product_type_info($id){
        $product_type = M("product_type");
        $where["id"] = array("eq" , $id);
        return $product_type->where($where)->find();
    }
    /**
    * 获取价格信息
    * @date: 2016年11月9日 下午3:38:00
    * @author: Lyubo
    * @param: $price_id
    * @param: $data  年份，类型：（0：购买1：续费）
    * @return: $price_info
    */
    function get_price($price_id){
        $price = M("product_price");
        $where['id'] = array("eq",$price_id);
        return $price->where($where)->find();
    }
    /**
    * VPS详情
    * @date: 2016年11月15日 下午5:46:58
    * @author: Lyubo
    * @param: product_id
    * @return: array
    */
    function vps_info(){
        $data = request();
        $product_id = $data['product']+0;//product_id
        $price_id = $data['price']+0;
        $system_type = $data['system_type']+0;
        if($system_type != 0 && $system_type != 1 && $system_type != 2){
            return false;
        }
        if(empty($price_id)){//price_id
            return false;
        }
        $price = $this->get_price($price_id);
        if(empty($price)){
            return false;
        }
        if($price["type"] != 0){
            return false;
        }
        if(empty($product_id)){
            return false;
        }
        $product_info = $this->get_product($product_id);
        if(empty($product_info)){
            return false;
        }
        if(!in_array($product_info["product_type_id"],[1,13,14])){
            return false;
        }
        if(strpos($product_info["api_name"],"zengzhi") !== false){
            return false;
        }
        if(strpos($product_info["stage"],"2") === false){
            return false;
        }
        if($price["product_id"] != $product_info["id"]){
            return false;
        }
        $product_config_list = $this->get_product_config($product_id);
        $product_price = $this->get_product_price_buy_time($product_id);
        foreach ($product_price as $key=>$val){
            if($val['id'] == $price['id']){
                $price = $val['product_price'];
            }
        }
        $product_config = [];
        foreach ($product_config_list as $key=>$val){
            if($val['en_name'] == 'memory_config' ){
                $product_config['memory_config'] = $val['config_value'].$val['unit'];
                $product_config['memory_to_config'] = $val['config_value'].$val['unit'].$val['details'];
            }else if($val['en_name'] == 'disk_config' ){
                $product_config['disk_config'] = $val['details'];
                $product_config['disk_to_config'] = $val['config_value'].$val['unit'].$val['details'];
            }else if($val['en_name'] == 'network_bandwidth'){
                $product_config['network_bandwidth'] = $val['config_value'].$val['unit'];
            }else if($val['en_name'] == 'ip'){
                $product_config['ip'] = $val['unit'];
            }else if($val['en_name'] =='cpu_info'){
                $product_config['cpu_info'] = $val['details'];
                $product_config['v_cpu'] = $val['config_value'].$val['unit'];
            }
        }
        return $product = [
            'product_info' =>$product_info,
            'product_config'=>$product_config,
            'product_price' =>$product_price,
            'system_type'=>$data['system_type'],
            'price_id'=>$data['price'],
            'price'=>$price
        ];
    }
    /**
    * 虚拟主机详情
    * @date: 2016年11月12日 上午9:48:23
    * @author: Lyubo
    * @param: product_id
    * @return: array
    */
    function virtualhost_info(){
        $data = request();
        $product_id = $data['product']+0;//product_id
        $price_id = $data['price']+0;
        $system_type = $data['system_type']+0;
        if($system_type != 0 && $system_type != 1){
            return false;
        }
        if(empty($price_id)){//price_id
            return false;
        }
        $price = $this->get_price($price_id);
        if(empty($price)){
            return false;
        }
        if($price["type"] != 0){
            return false;
        }
        if(empty($product_id)){
            return false;
        }
        $product_info = $this->get_product($product_id);
        if(empty($product_info)){
            return false;
        }
        if(!in_array($product_info["product_type_id"],[7,8,12,15,16,17])){
            return false;
        }
        if(strpos($product_info["api_name"],"zengzhi") !== false){
            return false;
        }
        if(strpos($product_info["stage"],"2") === false){
            return false;
        }
        if($price["product_id"] != $product_info["id"]){
            return false;
        }
        $product_config_list = $this->get_product_config($product_id);
        $product_price = $this->get_product_price_buy_time($product_id);
        foreach ($product_price as $key=>$val){
            if($val['id'] == $price['id']){
                $price = $val['product_price'];
            }
        }
        $product_config = [];
        foreach ($product_config_list as $key=>$val){
            if($val['en_name'] == 'data_capacity' || $val['en_name'] == 'database_capacity'){
                if($val['config_value'] >=1024){
                    $data_capacity = ($val['config_value']/1024).'G';
                }else{
                    $data_capacity = $val['config_value'].$val['unit'];
                }
                 $product_config['data_capacity'] = $data_capacity;
            }else if($val['en_name'] == 'data_num'){
                $product_config['data_num'] = $val['config_value'].$val['unit'];
            }else if($val['en_name'] == 'month_flow_rate' || $val['en_name'] == 'flow_capacity'){
                if($val['config_value'] == 0){
                    $month_flow_rate = '不限制';
                }else{
                    $month_flow_rate = $val['config_value'].$val['unit'];
                }
                $product_config['month_flow_rate'] = $month_flow_rate;
            }else if($val['en_name'] == 'network_bandwidth'){
                $product_config['network_bandwidth'] = $val['config_value'].$val['unit'];
            }else if($val['en_name'] == 'domain_number'){
                $product_config['domain_number'] = $val['config_value'].$val['unit'];
            }else if($val['en_name'] == 'space_capacity'){
                if($val['config_value'] >=1024){
                    $space_capacity = ($val['config_value']/1024).'G';
                }else{
                    $space_capacity = $val['config_value'].$val['unit'];
                }
                $product_config['space_capacity'] = $space_capacity;
            }
        }
        return $product = [
            'product_info' =>$product_info,
            'product_config'=>$product_config,
            'product_price' =>$product_price,
            'system_type'=>$system_type,
            'price_id'=>$price_id,
            'price'=>$price
        ];
    }
    /**
    * 购买之后修改销售额和销售量
    * @date: 2016年11月11日 下午2:39:12
    * @author: Lyubo
    * @param: $product_id
    * @param: $product_sales 销售额
    * @param: $product_volume 销售量
    * @return: boolean
    */
    function product_sales_volume($product_id , $product_sales , $product_volume){
        $where['id'] = array("eq" , $product_id);
        $data["product_sales"] = $product_sales;
        $data["product_volume"] = $product_volume;
        return $this->where($where)->save($data);
    }

    /**
     * 获取业务增值信息
     * @author:Guopeng
     * @param: $business_id
     * @param: $product_type_id
     * @return mixed
     */
    public function business_appreciation_info($business_id,$product_type_id)
    {
        $app_list = M("appreciation")->alias('app')
            ->where(array("app.business_id" => $business_id,"app.product_type_id" => $product_type_id))
            ->join('LEFT JOIN '.C('DB_PREFIX').'product as p on p.id=app.app_product_id')
            ->field("app.id,app.business_id,app.app_product_id,app.create_time,app.quanity,app.product_type_id,app.ip_address")
            ->select();
        for($i = 0;$i < count($app_list);$i++)
        {
            $price_data["month"] = 1;
            $product_price = $this->get_product_price_buy_time($app_list[$i]['app_product_id'],$price_data);
            $app_list[$i]['product_price'] = $product_price;
        }
        return $app_list;
    }

    /**
     * 获取原产品配置信息，升级产品配置信息，产品配置差信息，公用方法
     * @author: Guopeng
     * @param: $business_info: 业务信息
     * @param: $overdue_month: 过期时间
     * @return: array up_product升级产品配置信息 up_config_list升级配置差信息 $current_config原配置信息
     */
    public function up_product_config_gap($business_info, $overdue_month) {
        // 获取当前配置信息
        $current_config = $this->get_product_config($business_info ["product_id"], true );
        // 获取可升级产品
        $up_product_list = $this->up_product ( $business_info ["product_id"] );
        $up_product = array ();
        $t = 0;
        //免费主机只能升级个人，免费api_name是host.mf.I，转换为host.gr.I
        if($business_info['api_name'] == 'host.mf.I'){
            for($s = 0; $s < count ( $up_product_list ); $s ++) {
                // 判断‘.’在api_name中最后一次出现的位置是否相等
                if (strripos ( 'host.gr.I', '.' ) === strripos ( $up_product_list [$s] ['api_name'], '.' )) {
                    $position = strripos ( 'host.gr.I', '.' );
                    if (strcmp ( substr ( $up_product_list [$s] ['api_name'], 0, $position ), substr ( 'host.gr.I', 0, $position ) ) === 0) {
                        $up_product [$t] = $up_product_list [$s];
                        $t += 1;
                    }
                }
            }
        }else{
            for($s = 0; $s < count ( $up_product_list ); $s ++) {
                // 判断‘.’在api_name中最后一次出现的位置是否相等
                if (strripos ( $business_info ['api_name'], '.' ) === strripos ( $up_product_list [$s] ['api_name'], '.' )) {
                    $position = strripos ( $business_info ['api_name'], '.' );
                    if (strcmp ( substr ( $up_product_list [$s] ['api_name'], 0, $position ), substr ( $business_info ['api_name'], 0, $position ) ) === 0) {
                        $up_product [$t] = $up_product_list [$s];
                        $t += 1;
                    }
                }
            }
        }
        // 升级配置差信息
        $up_config_list = array ();
        for($i = 0; $i < count ( $up_product ); $i ++) {
            // 原产品和升级产品，对比信息
            $contrast = array ();
            // 获取要升级的产品配置信息
            $up_config = $this->get_product_config($up_product [$i] ["id"], true );
            // 计算配置差信息
            for($j = 0; $j < count ( $current_config ); $j++) {
                for($k = 0; $k < count ( $up_config ); $k++) {
                    // 当前配置app_name
                    $current_name = $current_config [$j] ["config_key"];
                    // 升级配置app_name
                    $up_name = $up_config [$k] ["config_key"];
                    if (strcmp ( trim($current_name), trim($up_name) ) == 0) {
                        // 当前配置app_value
                        $current_value = $current_config [$j] ["config_value"];
                        // 升级配置app_value
                        $up_value = $up_config [$k] ["config_value"];
                        // 封装配置差信息
                        $contrast [$k] ["name"] = $current_config [$j] ["config_key"];
                        $contrast [$k] ["source"] = $current_value . $current_config [$j] ["unit"];
                        if( $up_value==0){
                            $contrast [$k] ["up"] = $up_value . $up_config [$k] ["unit"];
                            $contrast [$k] ["gap"] = '无';
                        }else{
                            $contrast [$k] ["up"] = $up_value . $up_config [$k] ["unit"];
                            $contrast [$k] ["gap"] = $up_value - $current_value . $up_config [$k] ["unit"];
                        }
                        break;
                    }
                }
            }
            // 封装期限信息
            $contrast [$k + 1] ["name"] = "期限";
            $contrast [$k + 1] ["source"] = $overdue_month . "个月";
            $contrast [$k + 1] ["up"] = $overdue_month . "个月";
            $contrast [$k + 1] ["gap"] = 0 . "个月";
            // 封装价格信息
            $contrast [$k + 2] ["name"] = "价格";
            if($business_info['product_type_id']==1 || $business_info['product_type_id']==13 || $business_info['product_type_id']==14){
                //获取产品1月的价格.产品类型：VPS，快云VPS。
                if($overdue_month==1){
                    $price_data["month"] = 1;
                    $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                    $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["source"] = round($source_price,2);
                    $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                    $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["up"] = round($up_price,2);
                    $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                }elseif($overdue_month>1 && $overdue_month<=3){
                    $price_data["month"] = 3;
                    $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                    $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["source"] = round($source_price,2);
                    $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                    $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["up"] = round($up_price,2);
                    $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                    if(empty($source_price)){
                        //如果没有3个月的价格就获取6个月的价格来计算
                        $price_data["month"] = 6;
                        $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                        $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["source"] = round($source_price,2);
                        $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                        $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["up"] = round($up_price,2);
                        $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                        if(empty($source_product_price)){
                            //如果没有6个月的价格就按12个月的价格来计算
                            $price_data["month"] = 12;
                            $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                            $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                            $contrast [$k + 2] ["source"] = round($source_price,2);
                            $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                            $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                            $contrast [$k + 2] ["up"] = round($up_price,2);
                            $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                        }
                    }
                }elseif($overdue_month>3 && $overdue_month<=6){
                    $price_data["month"] = 6;
                    $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                    $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["source"] = round($source_price,2);
                    $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                    $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["up"] = round($up_price,2);
                    $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                    if(empty($source_product_price)){
                        //如果没有6个月的价格就按12个月的价格来计算
                        $price_data["month"] = 12;
                        $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                        $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["source"] = round($source_price,2);
                        $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                        $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["up"] = round($up_price,2);
                        $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                    }
                }elseif($overdue_month>6 && $overdue_month<=12){
                    $price_data["month"] = 12;
                    $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                    $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["source"] = round($source_price,2);
                    $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                    $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["up"] = round($up_price,2);
                    $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                }elseif($overdue_month>12 && $overdue_month<=24){
                    $price_data["month"] = 24;
                    $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                    $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["source"] = round($source_price,2);
                    $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                    $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["up"] = round($up_price,2);
                    $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                    if(empty($source_price)){
                        //如果没有24个月的价格就获取12个月的价格来计算
                        $price_data["month"] = 12;
                        $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                        $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["source"] = round($source_price,2);
                        $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                        $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["up"] = round($up_price,2);
                        $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                    }
                }elseif($overdue_month>24 && $overdue_month<=36){
                    $price_data["month"] = 36;
                    $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                    $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["source"] = round($source_price,2);
                    $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                    $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["up"] = round($up_price,2);
                    $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                    if(empty($source_price)){
                        //如果没有36个月的价格就获取24个月的价格来计算
                        $price_data["month"] = 24;
                        $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                        $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["source"] = round($source_price,2);
                        $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                        $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["up"] = round($up_price,2);
                        $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                        if(empty($source_product_price)){
                            //如果没有24个月的价格就按12个月的价格来计算
                            $price_data["month"] = 12;
                            $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                            $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                            $contrast [$k + 2] ["source"] = round($source_price,2);
                            $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                            $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                            $contrast [$k + 2] ["up"] = round($up_price,2);
                            $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                        }
                    }
                }elseif($overdue_month> 36){
                    $price_data["month"] = 60;
                    $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                    $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["source"] = round($source_price,2);
                    $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                    $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                    $contrast [$k + 2] ["up"] = round($up_price,2);
                    $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                    if(empty($source_price)){
                        //如果没有60个月的价格就获取36个月的价格来计算
                        $price_data["month"] = 36;
                        $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                        $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["source"] = round($source_price,2);
                        $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                        $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                        $contrast [$k + 2] ["up"] = round($up_price,2);
                        $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                        if(empty($source_product_price)){
                            //如果没有36个月的价格就按24个月的价格来计算
                            $price_data["month"] = 24;
                            $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                            $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                            $contrast [$k + 2] ["source"] = round($source_price,2);
                            $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                            $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                            $contrast [$k + 2] ["up"] = round($up_price,2);
                            $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                            if(empty($source_product_price)){
                                //如果没有24个月的价格就按12个月的价格来计算
                                $price_data["month"] = 12;
                                $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                                $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                                $contrast [$k + 2] ["source"] = round($source_price,2);
                                $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                                $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                                $contrast [$k + 2] ["up"] = round($up_price,2);
                                $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
                            }
                        }
                    }
                }
            }else{
                //获取产品12月的价格
                $price_data["month"] = 12;
                $source_product_price=$this->get_product_price_buy_time($business_info ["product_id"],$price_data);
                $source_price = $source_product_price['product_price']*$overdue_month/$price_data["month"];
                $contrast [$k + 2] ["source"] = round($source_price,2);
                $up_product_price=$this->get_product_price_buy_time($up_product[$i]['id'],$price_data);
                $up_price = $up_product_price['product_price']*$overdue_month/$price_data["month"];
                $contrast [$k + 2] ["up"] = round($up_price,2);
                $contrast [$k + 2] ["gap"] =  round($up_price - $source_price,2);
            }
            // 价格
            if ($contrast [$k + 2] ["gap"] < 0) {
                $contrast [0] ["price"] = 0;
            } else {
                $contrast [0] ["price"] = $contrast [$k + 2] ["gap"];
            }
            $contrast [0] ["up_product_id"] = $up_product [$i] ["id"];

            // 页面显示css样式
            $contrast [$k + 2] ["source"] = "￥" . $contrast [$k + 2] ["source"] . "&nbsp;元";
            $contrast [$k + 2] ["up"] = "￥" . $contrast [$k + 2] ["up"] . "&nbsp;元";
            $contrast [$k + 2] ["gap"] = "￥" . $contrast [$k + 2] ["gap"] . "&nbsp;元";
            // 产品配置列表
            $up_config_list [$i] = $contrast;
        }
        // 封装信息
        $params = array ();
        $params ["up_product"] = $up_product;
        $params ["up_config_list"] = $up_config_list;
        $params ["current_config"] = $current_config;
        return $params;
    }
    /**
     * 根据产品类型获取 增值产品列表
     * @author: Guopeng
     * @param $product_type_id: 产品类型ID
     * @param null $system_type
     * @return mixed
     */
    public function app_product($product_info, $system_type = null) {
        $where['product_state'] = 1;
        $where['type'] = 1;
        if(is_array($product_info))
        {
            $where['product_type_id'] = $product_info['product_type_id'];
            if($product_info['area_code'] == 4002)
            {
                $where['area_code'] = array("eq",4002);
            }
            elseif($product_info['area_code'] == 4001)
            {
                $where['area_code'] = array("eq",4001);
            }
        }else
        {
            $where['product_type_id'] = $product_info;
        }
        if (! is_null ( $system_type )) {
            $where['system_type'] = $system_type;
        }
        $app_product_list = $this->where($where)->field("id,product_name,api_name,unit,size")->select();
        for ($i=0;$i<count($app_product_list);$i++){
            $price_data["month"] = 1;
            $app_product_list[$i]['product_price']=$this->get_product_price_buy_time($app_product_list[$i]['id'],$price_data);
        }
        return $app_product_list;
    }
    /**
     * 获取可升级产品列表
     * @author: Guopeng
     * @param $product_id: 产品ID
     * @return mixed
     */
    public function up_product($product_id) {
        $product = $this->where(array("id"=>$product_id))->find();
        $where["product_type_id"] = $product["product_type_id"];
        $where["up_level"] = array("gt",$product["up_level"]);
        $where["area_code"] = $product["area_code"];
        $where["type"] = 0;
        $where["_string"] = "find_in_set(2,stage)";
        $where["product_state"] = 1;
        $field = "id,product_name,api_name";
        $product_info = $this->where($where)->field($field)->order("up_level")->select();
        return $product_info;
    }
    /**
     * 根据api_name获取增值产品信息
     * @author: Guopeng
     * @param null $product_type_id
     * @return mixed
     */
    public function get_appreciation_product($api_name, $product_type_id = null) {
        $where["api_name"] = $api_name;
        $where["product_state"] = 1;
        $where["type"] = 1;
        if (! is_null ( $product_type_id )) {
            if(is_array($product_type_id))
            {
                $where["product_type_id"] = $product_type_id["product_type_id"];
                $where["area_code"] = $product_type_id["area_code"];
            }
            else
            {
                $where["product_type_id"] = $product_type_id;
            }
        }
        $appreciation_product = $this->alias('p')->where($where)
            ->field("p.id,p.product_name,p.product_des,p.create_time,p.modify_time,p.product_state,p.product_sales,p.product_volume,p.product_type_id,t.type_name,t.api_ptype,p.api_name,p.size")
            ->join('JOIN '.C('DB_PREFIX').'product_type as t on p.product_type_id=t.id')->find();
        return $appreciation_product;
    }
    /**
     * 获取增值产品配置信息
     * @author: Guopeng
     * @param: $product_id: 产品编号
     * @return mixed
     */
    public function get_app_product_config($product_id) {
        $where["product_id"] = $product_id;
        $product_config = M("product_config")->where($where)->field("config_key,config_value,app_name")->find();
        return $product_config;
    }
    
    
    
    /**
     * ----------------------------------------------
     * | 根据ssl ID获取相应的ssl信息
     * | @时间: 2016年12月5日 上午10:26:25
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function get_one_ssl_info($ssl_id){
        $data = request();
        $price_id = $data['price']+0;
        if(empty($price_id)){//price_id
            return false;
        }
        $price = $this->get_price($price_id);
        if(empty($price)){
            return false;
        }
        if($price["type"] != 0){
            return false;
        }
        $product_id = $data['product']+0;//product_id
        if(empty($product_id)){
            return false;
        }
        $product_info = $this->get_product($product_id);
        if(empty($product_info)){
            return false;
        }
        if($product_info["product_type_id"] != 20){
            return false;
        }
        if($product_info["api_name"] == ""){
            return false;
        }
        if(strpos($product_info["stage"],"2") === false){
            return false;
        }
        if($price["product_id"] != $product_info["id"]){
            return false;
        }
    	$product_config_list = $this->get_product_config($product_id);
    	$product_price = $this->get_product_price_buy_time($product_id);
    	foreach ($product_price as $key=>$val){
    		if($val['id'] == $price['id']){
    			$price = $val['product_price'];
    		}
    	}
    	$product_config = [];
    	foreach ($product_config_list as $key=>$val){
			$product_config[$val['en_name']] = [
                'config_key' => $val['config_key'],
                'config_value' => $val['config_value'],
			];
    	}
    	if ($product_info['product_name'] == '基础级DV'){
    		$product_info['address_effect'] = 4;
    		$product_info['is_global'] = false;
    		$product_info['multi-servers'] = false;
    	}else if ($product_info['product_name'] == '专业级DV Pre'){
    		$product_info['address_effect'] = 4;
    		$product_info['is_global'] = true;
    		$product_info['multi-servers'] = false;
    	}else if ($product_info['product_name'] == '企业级OV Pre'){
    		$product_info['address_effect'] = 4;
    		$product_info['is_global'] = true;
    		$product_info['multi-servers'] = false;
    	}else if ($product_info['product_name'] == '企业级OV 增强版'){
    		$product_info['address_effect'] = 4;
    		$product_info['is_global'] = true;
    		$product_info['multi-servers'] = false;
    	}else if ($product_info['product_name'] == '顶级EV Pre'){
    		$product_info['address_effect'] = 3;
    		$product_info['is_global'] = false;
    		$product_info['multi-servers'] = true;
    	}else if ($product_info['product_name'] == '顶级EV 增强版'){
    		$product_info['address_effect'] = 3;
    		$product_info['is_global'] = false;
    		$product_info['multi-servers'] = true;
    	}
        $step_id = $data['mutil_domain_step_id']+0;
    	//获取该商品的多域名step单价价格
    	$steps = M('product_price')->where(['product_id'=>['eq',$step_id]])->find();
        if(strpos($steps["note_appended"],$product_info['product_name']) === false){
            return false;
        }
    	return $product = [
    			'product_info' =>$product_info,
    			'product_config'=>$product_config,
    			'product_price' =>$product_price,
    			'extra' => [				
	    			'mutil_domain'=>$data['mutil_domain'],
	    			'mutil_server'=>$data['mutil_server'],
	    			'global_domain'=>$data['global_domain'],
	    			'mutil_domain_step_id'=>$data['mutil_domain_step_id'],
    				'mutil_domain_step' => $steps,
    			],
    			'price_id'=>$data['price'],
    			'price'=>$price
    	];
    }
    
    /**
     * ----------------------------------------------
     * | 获取ssl产品列表
     * | @时间: 2016年12月5日 下午1:56:49
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function get_ssl_product(){
		$ssl_type = 20;
    	$_products = $this->get_type_product($ssl_type);
        if(!$_products || count($_products) < 11){
            return false;
        }
    	$products = [];
    	$products_ids = [];
        $multi_domains_price = [];
    	//不知道该咋写这部分代码的注释了...
    	foreach ($_products as $k => $v){
    		$products_ids[$v['id']] = $v['product_name'];
    	}
    	foreach ($_products as $k => $v){
            if ($v['product_name'] == '基础级DV'){
                $v['address_effect'] = 4;
                $v['is_global'] = false;
                $v['multi-servers'] = false;
                array_push($products, $v);
            }else if ($v['product_name'] == '专业级DV Pre'){
                $v['address_effect'] = 4;
                $v['is_global'] = true;
                $v['multi-servers'] = false;
                array_push($products, $v);
            }else if ($v['product_name'] == '企业级OV Pre'){
                $v['address_effect'] = 4;
                $v['is_global'] = true;
                $v['multi-servers'] = false;
                array_push($products, $v);
            }else if ($v['product_name'] == '企业级OV 增强版'){
                $v['address_effect'] = 4;
                $v['is_global'] = true;
                $v['multi-servers'] = false;
                array_push($products, $v);
            }else if ($v['product_name'] == '顶级EV Pre'){
                $v['address_effect'] = 3;
                $v['is_global'] = false;
                $v['multi-servers'] = true;
                array_push($products, $v);
            }else if ($v['product_name'] == '顶级EV 增强版'){
                $v['address_effect'] = 3;
                $v['is_global'] = false;
                $v['multi-servers'] = true;
                array_push($products, $v);
            }else if ($v['product_name'] == '专业级DV Pre多域名价格'){
    			$multi_domains_price[ array_search('专业级DV Pre', $products_ids) ] = [ 'step' => $v['price_list'][0]['product_price'], 'id' => $v['id'] ] ;
    		}else if ($v['product_name'] == '企业级OV Pre多域名价格'){
    			$multi_domains_price[ array_search('企业级OV Pre', $products_ids) ] = [ 'step' => $v['price_list'][0]['product_price'], 'id' => $v['id'] ] ;
    		}else if ($v['product_name'] == '企业级OV 增强版多域名价格'){
    			$multi_domains_price[ array_search('企业级OV 增强版', $products_ids) ] = [ 'step' => $v['price_list'][0]['product_price'], 'id' => $v['id'] ] ;
    		}else if ($v['product_name'] == '顶级EV Pre多域名价格'){
    			$multi_domains_price[ array_search('顶级EV Pre', $products_ids) ] = [ 'step' => $v['price_list'][0]['product_price'], 'id' => $v['id'] ] ;
    		}else if ($v['product_name'] == '顶级EV 增强版多域名价格'){
    			$multi_domains_price[ array_search('顶级EV 增强版', $products_ids) ] = [ 'step' => $v['price_list'][0]['product_price'], 'id' => $v['id'] ] ;
    		}else{
                return false;
            }
    	}
    	return [
            'products' => $products,
            'multi_domains_price' => $multi_domains_price,
        ];
    }
}