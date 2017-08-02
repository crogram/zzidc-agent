<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

/**
 * -------------------------------------------------------
 * | 产品模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class ProductPriceModel extends BackendModel{
	
	//关联的表名
	protected $trueTableName = 'agent_product_price';
	//新增是允许写入的字段
	protected $insertFields = [
			'product_id', 'month', 'product_price',
			'type', 'note_appended', 'up_time',
			'state', 'giant_price', 'min_price', 
			'create_time', 'area_code', 'api_type'
	];
	//更新是允许更新的字段
	protected $updateFields = [
			'month', 'product_price', 'type',
			'note_appended', 'up_time', 'state',
	];
	
	
	
	//搜索条件字段
	public $searchable = [
			'area_code' => [
					'display_name' => '所属区域',
					'html_type' => 'select',
					'data' => [],
			],
			'state' => [
					'display_name' => '状态',
					'html_type' => 'select',
					'data' => [
							'0' => '无效',
							'1' => '有效',
					],
			],
			'type' => [
					'display_name' => '类型',
					'html_type' => 'select',
					'data' => [
							'0' => '订购',
							'1' => '续费',
					],
			],
			'up_time' => [
					'display_name' => '更新时间',
					'html_type' => 'date',
					'start_name' => '_start',
					'end_name' => '_end',
			],
			'key' => [
				'note_appended' => '价格描述',
			],
	];
	
	
	//排序字段
	public $sortable = [
			'id' => [
					'display_name' =>'编号',
					'sortable' => true,
			],
			'product_name' => [
					'display_name' =>'名称',
					'sortable' => false,
			],
			'month' => [
					'display_name' =>'购买期限',
					'sortable' => false,
			],
			'giant_price' => [
					'display_name' => '景安价格',
					'sortable' => false,
			],
			'product_price' => [
					'display_name' => '产品价格',
					'sortable' => false,
			],
			'min_price' => [
					'display_name' => '最低价格',
					'sortable' => true,
			],
			'type' => [
					'display_name' => '类型',
					'sortable' => false,
			],
			'note_appended' => [
					'display_name' => '价格描述',
					'sortable' => false,		
			],
			'state' => [
					'display_name' => '状态',
					'sortable' => false,
			],
			'area_code' => [
					'display_name' => '地区',
					'sortable' => true,
			],
			'up_time' => [
					'display_name' => '修改时间',
					'sortable' => true,
			],
	];

	
	/**
	 * ----------------------------------------------
	 * | 获取搜索条件所需要的数据
	 * | 可以实现getFillData方法，
	 * | 将一些需要的数据复给$this->fill_data
	 * | 例如本类的getFillData()方法
	 * | @时间: 2016年10月11日 下午3:03:09
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getSearchable($fill_data = []){
		if (empty($fill_data)) {
			return $this->fillSearchable($this->fillData);
		}else {
			return $this->fillSearchable($fill_data);
		}
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 获取搜索条件需要的数据
	 * | @时间: 2016年10月12日 下午2:54:05
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: array
	 * ----------------------------------------------
	 */
	public function getFillData(){
		
		/**
		 * ---------------------------------------------------
		 * | 这里注意返回的一定是一个数组，
		 * | 并且数组的key一定是与searchable数组的key相同 
		 * | @时间: 2016年10月12日 下午2:54:52
		 * ---------------------------------------------------
		 */
		
		//产品分类数据
// 		$fill_data['product_type_id'] = D('Common/ProductType')->getAll('id as k,type_name as v');
		//区域数据
		$fill_data['area_code'] = D('Common/Region')->getAll('region_code as k,region_name as v');
		
		//父类里继承来的属性
		$this->fillData = $fill_data;
		return $this;
	}
	

	/**********************************新增更新回调/开始***********************************/
	
	/**
	 * ----------------------------------------------
	 * | 插入数据之前的回调
	 * | @时间: 2016年10月17日 上午11:28:21
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _before_insert(&$data, $options){
		
	}

	/**
	 * ----------------------------------------------
	 * | 插入成功后的回调
	 * | @时间: 2016年10月17日 上午11:28:50
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _after_insert($data, $options){
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新数据之前做的操作
	 * | @时间: 2016年10月17日 上午11:26:36
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _before_update(&$data, $options){
		//查看产品价格是否合法
		if ( getDecimalDigit($data['product_price']) > 2 ){
			$this->error = '产品价格最多只有小数点后两位';
			return false;
		}
		//查看购买时间是否合法
		if ( !is_int($data['month']) ) {
			$this->error = '购买时间只能为整数';
			return false;			
		}
		//查看购买类型是否非法
		if ( !in_array($data['type'], [ 0,1 ], true) ) {
			$this->error = '购买类型参数非法';
			return false;						
		}
		//处理下价格说明的编码，统一转换成utf-8的编码，然后查看字数
		$note_appended = mb_convert_encoding($data['note_appended'], 'utf-8');
		if ( mb_strlen($note_appended, 'utf-8') >= 50*3) {
			$this->error = '价格说明最多只有50个字';
			return false;								
		}
		
		//添加更新时间默认值
		$data['up_time'] = date( 'Y-m-d H:i:s', time() );

	}

	/**
	 * ----------------------------------------------
	 * | 更新之后做的操作
	 * | @时间: 2016年10月17日 上午11:27:09
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function _after_update($data, $options){
		
	}
	
	/**********************************新增更新回调/结束***********************************/
	
	/**
	 * ----------------------------------------------
	 * | 批量修改价格
	 * | @时间: 2016年10月21日 上午11:53:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function updateBatchPrice(){
		$request = request();
		$type = $request["product_type"]+0;
		$month = $request['month']+0;
        if($type != -100){
            $where['type'] = $type;
        }
		if($month != 0){
			$where['month'] = $month;
		}
		$product_ids = $request["product_ids"];
		if(!is_array($product_ids)){
			$this->error = '您没有选择产品！';
			return false;
		}
        $operator = $request['operator']+0;
        $value = $request['value']+0;
        $updateField = 'product_price';
        $res = false;
        $ok = $error = 0;
		$m_product_price = M('product_price');
        foreach ($product_ids as $key => $val ){
            $where['product_id'] = ['eq',$val];
            $product_price = $m_product_price->where($where);
            $product_price_info = $product_price->select();
			if(!$product_price_info){
				$this->error = '未查询到该产品价格！';
				return false;
			}
            foreach($product_price_info as $k=>$v){
                $price_where["id"] = $v["id"];
                if ($operator == 0) {//0代表+
                    $res = $m_product_price->where($price_where)->setInc($updateField,$value);
                }elseif ($operator == 1) {//1代表-
                    $price = $v["product_price"] - $value;
                    if($price < $v["min_price"]){
                        $price = $v["min_price"];
                    }
                    $res = $m_product_price->where($price_where)->setField($updateField,$price);
                }elseif ($operator == 2) {//2代表*
                    //这里要把价钱就代表折扣，要除以10
                    $price = intval($v["giant_price"]*($value/10));
                    if($price < $v["min_price"]){
                        $price = $v["min_price"];
                    }
                    $res = $m_product_price->where($price_where)->setField($updateField, $price);
                }
                //统计成功错误
                if ($res === false) {
                    $error++;
                }else {
                    $ok++;
                }
            }
        }
        $this->error = '共修改成功'.$ok.'个:'.'修改失败'.$error.'个';
		return true;
	}

    /**
     * 修改快云服务器价格查询
     * @时间: 2017年4月11日 上午11:53:53
     * @author: Guopeng
     * return: type
     */
    public function get_cloudserver_price(){
        $product = M("product");
        $product_where["product_type_id"] = 18;
//        $product_where["product_state"] = 1;//上线状态
        $product_info = $product->where($product_where)->select();
        if(!$product_info){
            return false;
        }
        $product_id_arr = [];
        foreach($product_info as $k => $v){
            $product_id_arr[$k] = $v["id"];
        }
        $product_id = implode(",",$product_id_arr);
        $product_price_where["product_id"] = array("in",$product_id);
        $product_price_where["month"] = array("eq",1);
        $product_price_where["note_appended"] = array("neq","");
        $product_price_where["note_appended"] = array("exp","is not null");
        $product_price_info = $this->where($product_price_where)->select();
        if(!$product_price_info){
            return false;
        }
        $cloudserver_price = [];
        foreach($product_price_info as $k => $v){
            if($v["note_appended"] == "购买CPU"){
                $cloudserver_price["gn"][$v["id"]] = ["name"=>"购买CPU","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "购买硬盘"){
                $cloudserver_price["gn"][$v["id"]] = ["name"=>"购买硬盘","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "购买带宽"){
                $cloudserver_price["gn"][$v["id"]] = ["name"=>"购买带宽","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "购买带宽大于5M"){
                $cloudserver_price["gn"][$v["id"]] = ["name"=>"购买带宽大于5M","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "购买内存"){
                $cloudserver_price["gn"][$v["id"]] = ["name"=>"购买内存","price"=>$v["product_price"]];
                $cloudserver_price["hk"][$v["id"]] = ["name"=>"购买香港内存","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "购买香港CPU"){
                $cloudserver_price["hk"][$v["id"]] = ["name"=>"购买香港CPU","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "购买香港硬盘"){
                $cloudserver_price["hk"][$v["id"]] = ["name"=>"购买香港硬盘","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "购买香港带宽"){
                $cloudserver_price["hk"][$v["id"]] = ["name"=>"购买香港带宽","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "购买香港带宽大于5M"){
                $cloudserver_price["hk"][$v["id"]] = ["name"=>"购买香港带宽大于5M","price"=>$v["product_price"]];
            }
        }
        return $cloudserver_price;
    }

    /**
     * 修改快云服务器价格方法
     * @时间: 2017年4月11日 上午11:53:53
     * @author: Guopeng
     * return: type
     */
	public function cloudserver_price_update(){
		$data = request();
        $product_price = M('product_price');
		$price_data = $data["product_price"];
		if(empty($price_data)){
			$this->setError("参数错误！");
			return false;
		}
        $res = false;
        foreach($price_data as $k=>$v){
            $where["id"] = array("eq",$k);
            $price["product_price"] = $v;
            $res = $product_price->where($where)->save($price);
        }
        if($res === false){
            $this->setError("价格保存失败！");
            return false;
        }else{
            return true;
        }
	}

	/**
	 * 修改快云数据库价格查询
	 * @时间: 2017年4月11日 上午11:53:53
	 * @author: Guopeng
	 * return: type
	 */
	public function get_clouddb_price(){
		$product = M("product");
		$product_where["product_type_id"] = 22;
//		$product_where["product_state"] = 1;//上线状态
		$product_info = $product->where($product_where)->select();
		if(!$product_info){
			return false;
		}
		$product_id_arr = [];
		foreach($product_info as $k => $v){
			$product_id_arr[$k] = $v["id"];
		}
		$product_id = implode(",",$product_id_arr);
		$product_price_where["product_id"] = array("in",$product_id);
		$product_price_where["month"] = array("eq",1);
		$product_price_info = $this->where($product_price_where)->select();
		if(!$product_price_info){
			return false;
		}
		$clouddb_price = [];
		foreach($product_price_info as $k => $v){
			if($v["note_appended"] == "clouddb.dx.yp.price" && $v["type"] == 0){
				$clouddb_price["dx"][$v["id"]] = ["name"=>"购买独享版硬盘","price"=>$v["product_price"]];
			}elseif($v["note_appended"] == "clouddb.dx.yp.price" && $v["type"] == 1){
				$clouddb_price["dx"][$v["id"]] = ["name"=>"续费独享版硬盘","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "clouddb.dx.nc.price" && $v["type"] == 0){
				$clouddb_price["dx"][$v["id"]] = ["name"=>"购买独享版内存","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "clouddb.dx.nc.price" && $v["type"] == 1){
                $clouddb_price["dx"][$v["id"]] = ["name"=>"续费独享版内存","price"=>$v["product_price"]];
			}elseif($v["note_appended"] == "clouddb.gx.yp.price" && $v["type"] == 0){
                $clouddb_price["gx"][$v["id"]] = ["name"=>"购买共享版硬盘","price"=>$v["product_price"]];
            }elseif($v["note_appended"] == "clouddb.gx.yp.price" && $v["type"] == 1){
                $clouddb_price["gx"][$v["id"]] = ["name" => "续费共享版硬盘","price" => $v["product_price"]];
            }
		}
		return $clouddb_price;
	}

	/**
	 * 修改快云数据库价格方法
	 * @时间: 2017年4月11日 上午11:53:53
	 * @author: Guopeng
	 * return: type
	 */
	public function clouddb_price_update(){
		$data = request();
		$product_price = M('product_price');
		$price_data = $data["product_price"];
		if(empty($price_data)){
			$this->setError("参数错误！");
			return false;
		}
		$res = false;
		foreach($price_data as $k=>$v){
			$where["id"] = array("eq",$k);
			$price["product_price"] = $v;
			$res = $product_price->where($where)->save($price);
		}
		if($res === false){
			$this->setError("价格保存失败！");
			return false;
		}else{
			return true;
		}
	}

    /**
     * ssl证书价格查询
     * @时间: 2017年4月11日 上午11:53:53
     * @author: Guopeng
     * return: type
     */
    public function get_ssl_price(){
        $product = M("product");
        $product_where["product_type_id"] = 20;
        //		$product_where["product_state"] = 1;//上线状态
        $product_info = $product->where($product_where)->select();
        header("Content-type:text/html;charset=utf-8");
        if(!$product_info){
            return false;
        }
        $product_id_arr = [];
        foreach($product_info as $k => $v){
            $product_id_arr[$k] = $v["id"];
        }
        $product_id = implode(",",$product_id_arr);
        $product_price_where["product_id"] = array("in",$product_id);
//        $product_price_where["month"] = array("eq",1);
        $product_price_info = $this->where($product_price_where)->select();
        if(!$product_price_info){
            return false;
        }
        $ssl_price = [];
        foreach($product_price_info as $k => $v){
            if(strpos($v["note_appended"],"基础级DV") !== false){
                $ssl_price["jcdv"][$v["id"]] = ["name"=>$v["note_appended"]." ".($v["month"]/12)."年","price"=>$v["product_price"]];
            }elseif(strpos($v["note_appended"],"专业级DV") !== false){
				$ssl_price["zydv"][$v["id"]] = ["name"=>$v["note_appended"]." ".($v["month"]/12)."年","price"=>$v["product_price"]];
            }elseif(strpos($v["note_appended"],"企业级OV 增强版") !== false){
				$ssl_price["qyovzq"][$v["id"]] = ["name"=>$v["note_appended"]." ".($v["month"]/12)."年","price"=>$v["product_price"]];
            }elseif(strpos($v["note_appended"],"企业级OV") !== false){
				$ssl_price["qyov"][$v["id"]] = ["name"=>$v["note_appended"]." ".($v["month"]/12)."年","price"=>$v["product_price"]];
            }elseif(strpos($v["note_appended"],"顶级EV 增强版") !== false){
				$ssl_price["djevzq"][$v["id"]] = ["name"=>$v["note_appended"]." ".($v["month"]/12)."年","price"=>$v["product_price"]];
            }elseif(strpos($v["note_appended"],"顶级EV") !== false){
				$ssl_price["djev"][$v["id"]] = ["name"=>$v["note_appended"]." ".($v["month"]/12)."年","price"=>$v["product_price"]];
            }
        }
        return $ssl_price;
    }

    /**
     * 修改ssl证书价格方法
     * @时间: 2017年4月11日 上午11:53:53
     * @author: Guopeng
     * return: type
     */
    public function ssl_price_update(){
        $data = request();
        $product_price = M('product_price');
        $price_data = $data["product_price"];
        if(empty($price_data)){
            $this->setError("参数错误！");
            return false;
        }
        $res = false;
        foreach($price_data as $k=>$v){
            $where["id"] = array("eq",$k);
            $price["product_price"] = $v;
            $res = $product_price->where($where)->save($price);
        }
        if($res === false){
            $this->setError("价格保存失败！");
            return false;
        }else{
            return true;
        }
    }
}
