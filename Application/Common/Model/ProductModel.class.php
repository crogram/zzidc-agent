<?php
namespace Common\Model;
use Common\Model\BaseModel;



/**
 * -------------------------------------------------------
 * | 产品模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class ProductModel extends BaseModel{
	
	//关联的表名


	
	
	/**
	 * ----------------------------------------------
	 * | 通过接口返回的api_name去查询代理数据库的产品信息
	 * | @时间: 2016年10月31日 下午6:43:10
	 * | @author: duanbin
	 * | @param: $api_name 接口返回的数组 
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getProductInfoByApiNameAndApiPtype($api_name, $api_ptype = '', $fields = "" ,$area_code){
		if (!empty($api_ptype)){
			$where['b.api_ptype'] = [ 'eq', $api_ptype ];
		}
		if (!empty($area_code)){
		    $where['a.area_code'] = [ 'eq', $area_code ];
		}
		$where['a.api_name'] = [ 'eq', $api_name ];
		
		if (empty($fields)){
			$fields = 'a.id,a.product_type_id,a.product_name,a.api_name,a.area_code,b.api_ptype';
		}
		
		$res = $this->alias('a')->field($fields)
		->join('left join '.C('DB_PREFIX').'product_type as b on a.product_type_id = b.id')
		->where($where)->find();
		
		return $res;
	}
	
	/**
	 * ----------------------------------------------
	 * | 查看是否有ipv6的值
	 * | @时间: 2016年11月1日 上午9:41:46
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function Ipv6Exists($ipv6){
		$m_ipv6 = M('ipv6');
		$where['IPv6'] = [ 'eq', $ipv6 ];
		$res = $m_ipv6->field("*")->where($where)->select();
		return empty($res)? false: $res;
	}
	
	/**
	 * ----------------------------------------------
	 * | 添加ipv6的值
	 * | @时间: 2016年11月1日 上午9:46:17
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function addIpv6($data) {
		$m_ipv6 = M('ipv6');
		return $m_ipv6->add($data);		
	}

	/**
	 * ----------------------------------------------
	 * | 通过product_type_id获取产品
	 * | @时间: 2016年11月14日 上午11:12:55
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getProductByProductTypeId($product_type_id, $fields = '*'){
		$where['product_type_id'] = [ 'eq', $product_type_id+0 ];
		return $this->field($fields)->where($where)->select();
	}
	
	/**
	 * ----------------------------------------------
	 * | 通过产品名称和产品类型获取一条产品记录(基本上只有域名查询用到了)
	 * | @时间: 2016年11月23日 下午6:09:45
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getProductByProductNameAndProductTypeId($product_name, $product_type_id, $fields = '*'){
		$where['product_name'] = [ 'eq', $product_name ];
		$where['product_type_id'] = [ 'eq', $product_type_id+0 ];
		return $this->field($fields)->where($where)->find();
	}
	

}
