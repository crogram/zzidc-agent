<?php
namespace Common\Model;
use Common\Model\BaseModel;

use \Common\Data\GiantAPIParamsData as GiantAPIParams;

/**
 * -------------------------------------------------------
 * | 产品模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class ProductTypeModel extends BaseModel{
	
	//关联的表名

	/**
	 * ----------------------------------------------
	 * | 获取所有虚拟主机的ptype
	 * | @时间: 2016年11月22日 下午3:25:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public  $hostPtype = [
			0 => GiantAPIParams::PTYPE_HOST,
			1 => GiantAPIParams::PTYPE_HK_HOST,
			2 => GiantAPIParams::PTYPE_USA_HOST,
			3 => GiantAPIParams::PTYPE_CLOUD_VIRTUAL,
			4 => GiantAPIParams::PTYPE_DEDE_HOST,
	];
	
	/**
	 * ----------------------------------------------
	 * | 获取主机类型的所有ptype
	 * | @时间: 2016年11月22日 下午4:02:22
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getHostProductType(){
		return [
				GiantAPIParams::PTYPE_HOST => '国内主机',
				GiantAPIParams::PTYPE_HK_HOST => '香港主机',
				GiantAPIParams::PTYPE_USA_HOST => '美国主机',
				GiantAPIParams::PTYPE_CLOUD_VIRTUAL => '云虚拟主机',
				GiantAPIParams::PTYPE_DEDE_HOST => '织梦主机',
		];
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取可以批量更新产品价格的ptype
	 * | @时间: 2016年11月22日 下午4:11:37
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getCanBatchSyncPriceProductType(){
		return array_merge($this->getHostProductType(), [
				GiantAPIParams::PTYPE_CLOUD_SPACE => '云空间',
				GiantAPIParams::PTYPE_DOMAIN => '域名',
                GiantAPIParams::PTYPE_FAST_CLOUDVPS => '快云VPS',
		        GiantAPIParams::PTYPE_VPS => 'VPS',
		]);
	}
	
	/**
	 * ----------------------------------------------
	 * | 通过api_type，获取id值
	 * | @时间: 2016年11月22日 下午5:13:09
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getProductIdByApiType($api_type){
		$where['api_ptype'] = [ 'eq', $api_type ];
		$where['parent_id'] = [ 'neq', 0 ];
		$res = $this->field('id')->where($where)->find();
		return $res['id'];
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 根据产品id获取的类型的api_ptype
	 * | @时间: 2016年11月21日 下午6:38:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getApiPtypeByProductId($product_id){
		$where['b.id'] = [ 'eq', $product_id ];
		
		$res = $this->alias('a')->field('a.api_ptype')
		->join('left join '.C('DB_PREFIX').'product as b on b.product_type_id = a.id')
		->where($where)->find();
		
		return $res['api_ptype'];
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取可以试用的产品类型信息
	 * | @时间: 2016年10月24日 下午2:28:04
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getCanNotTrialType(){
		return [ 18, 19, 20 ];
	}

	/**
	 * ----------------------------------------------
	 * | 获取所有的分类
	 * | @时间: 2016年11月9日 上午10:08:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getAll($field = '*', $where = [], $order = '') {
		$res = parent::getAll( $field, $where, $order);
		$_res = [];
		foreach ($res as $k => $v) {
			$_res[$v['k']] = $v['v'];
		}
// 		dump($res);die;
		return $_res;
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取所有的分类
	 * | @时间: 2016年11月9日 上午10:08:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getLowLevelType($field = '*', $where = [], $order = '') {
		$res = $this->field($field)->where($where)->order()->select();
// 		$res = parent::getAll( $field, $where, $order);
		$_res = [];
		foreach ($res as $k => $v) {
			$_res[$v['k']] = $v['v'];
		}
// 		dump($res);die;
		return $_res;
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取product-index页面用到的type类型
	 * | 这数据表的设计真蛋疼，取出来用的时候有种想死的感觉
	 * | @时间: 2016年11月24日 上午10:39:47
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getProductIndexType($fields = '*'){
		$where['display'] = [ 'eq', 1 ];
		$res = $this->field($fields)->where($where)->select();
		$_res = [];
		foreach ($res as $k => $v) {
			$_res[$v['k']] = $v['v'];
		}
		return $_res;
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取order-index页面用到的type类型
	 * | @时间: 2016年11月24日 上午10:54:29
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getOrderIndexType($fields = '*'){
		return $this->getProductIndexType($fields);
	}

	
	
	
	

}
