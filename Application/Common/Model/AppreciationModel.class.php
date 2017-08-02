<?php
namespace Common\Model;
use Common\Model\BaseModel;

use \Common\Data\StateData as state;

/**
 * -------------------------------------------------------
 * | 增值记录表模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class AppreciationModel extends BaseModel{
	
	//关联的表名

	
	/**
	 * ----------------------------------------------
	 * | 获取产品增值信息(带价格信息)
	 * | 根据业务id($business_id)和产品类型id($product_type_id)
	 * | @时间: 2016年11月1日 上午10:55:32
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getAppreciationInfoWithPriceByBIdAndPTypeId($business_id, $product_type_id,$api_type='', $fields= ''){
		if ($fields == ''){
			$fields = 'a.id,a.business_id,a.create_time as product_create_time,a.app_product_id,b.product_name,b.unit,b.size,a.quanity,a.product_type_id,a.ip_address,c.month,c.product_price,c.type,c.note_appended,c.state,c.create_time as price_create_time,c.up_time';
		}
		if ($api_type != ''){
			$where['c.api_type'] = [ 'eq', $api_type ];
		}else {
			$where['_string'] =  "api_type is null or  api_type=''" ;
		}
	
		$where['a.business_id'] = [ 'eq', $business_id ];
		$where['a.product_type_id'] = [ 'eq', $product_type_id ];
	
		$where['c.state'] = [ 'eq', 1 ];	//价格状态是正常的
		$where['c.month'] = [ 'eq', 1 ];	//增值业务默认都是一个月的购买时长；
		$where['c.type'] = [ 'eq', state::STATE_BUY ];	//价格类型
	
		$res = $this->alias('b')->field($fields)
		->join('left join '.C('DB_PREFIX').'appreciation as a on b.id = a.app_product_id')
		->join('left join '.C('DB_PREFIX').'product_price as c on c.product_id = b.id')
		->where($where)->select();
		return $res;
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 获取产品增值信息(不带价格信息)
	 * | @时间: 2016年11月1日 上午11:55:25
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getAppreciationInfoByBIdAndPTypeId($business_id, $product_type_id, $fields= ''){
		if ($fields == ''){
			$fields = 'a.id,a.business_id,a.create_time as product_create_time,a.app_product_id,b.product_name,b.unit,b.size,a.quanity,a.product_type_id,a.ip_address';
		}
		$where['a.business_id'] = [ 'eq', $business_id ];
		$where['a.product_type_id'] = [ 'eq', $product_type_id ];
	
		$res = $this->alias('b')->field($fields)
		->join('left join '.C('DB_PREFIX').'appreciation as a on b.id = a.app_product_id')
		->where($where)->select();
		return $res;
	}
	
	/**
	 * ----------------------------------------------
	 * | 删除增值产品信息
	 * | @时间: 2016年11月1日 上午11:59:03
	 * | @author: duanbin
	 * | @param: $where
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function destroyAppreciation($where){
		if (empty($where)){
			$this->error = '没有相应的条件，拒绝删除！';
			return false;
		}
		$res = M('appreciation')->where($where)->delete();
		return $res;
	}
	
	/**
	 * ----------------------------------------------
	 * | 添加增值产品信息的方法
	 * | @时间: 2016年11月1日 下午1:55:03
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function enter($data, $product_info, $business_info) {
		$m_product = new \Common\Model\ProductModel();
		$insert = [];
		//组装要添加的数据
		foreach ($data as $k => $v){
			$product_info = $m_product->getProductInfoByApiNameAndApiPtype($v['appName'], $product_info['api_ptype']);
			$insert[$k]["business_id"] = $business_info['business_id'];
			$insert[$k]["app_product_id"] = $product_info['id'];
			$insert[$k]["quanity"] = $v['appSize'];
			$insert[$k]["product_type_id"] = $product_info['product_type_id'];
			$insert[$k]['create_time'] = date('Y-m-d H:i:s');
			if (empty($v['ip'])){
				$insert[$k]["ip_address"] = '无';
			}else {
				$insert[$k]["ip_address"] = $v['ip'];
			}
		}
		$res = $this->addAll($insert);
		return $res;
	}


}
