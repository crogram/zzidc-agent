<?php
namespace Backend\Model;
use Backend\Model\BackendModel;

/**
 * 权限规则model
 */
class AuthRuleModel extends BackendModel{
	protected $trueTableName = 'agent_auth_rule';
	/**
	 * 删除数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function deleteData($map){
		$count=$this
			->where(array('pid'=>$map['id']))
			->count();
		if($count!=0){
			return false;
		}
		$result=$this->where($map)->delete();
		return $result;
	}
	
	
	

	
	
	

}
