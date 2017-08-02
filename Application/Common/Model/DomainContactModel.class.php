<?php
namespace Common\Model;
use Common\Model\BaseModel;
//use Common\Data\StateData;

/**
 * -------------------------------------------------------
 * | 域名联系人模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
 
class DomainContactModel extends BaseModel{
	
	//关联的表名

	
	/**
	 * ----------------------------------------------
	 * | 获取一条域名联系人的记录
	 * | @时间: 2016年11月7日 下午6:17:11
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function firstOrfail($id, $attr = "id", $fields = "*"){
		$where[$attr] = [ 'eq', $id ];
		return $this->field($fields)->where($where)->find();
	}
	
	
	
	
	
	
	
	
	
	
	

}
