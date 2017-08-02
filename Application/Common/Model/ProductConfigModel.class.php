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
class ProductConfigModel extends BaseModel{
	
	//关联的表名


	
	
	/**
	 * ----------------------------------------------
	 * | 根据产品id获取产品配置
	 * | @时间: 2016年10月24日 下午2:28:04
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getConfigByPId($product_id, $fields = '*'){
		$where['product_id'] = [ 'eq', $product_id ];
		$where['display'] = [ 'eq', 1 ];
		

		$res = $this->field($fields)->where($where)->select();
		
		$online_management = [
				1 => '支持',
				0 => '不支持',
		];
		foreach ($res as $k => $v){
			//这里的系统类型不显示
			if ($v['config_key'] == '系统类型'){
				unset($res[$k]);
			}elseif ($v['config_key'] == '在线管理'){
				$res[$k]['config_value'] = $online_management[$v['config_value']];
			}
		}
		
		return $res;
	}


}
