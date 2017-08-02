<?php
namespace Backend\Model;
use Backend\Model\BackendModel;
use Common\Aide\InfinitenessAide;
/**
 * 菜单操作model
 */
class AdminNavModel extends BackendModel{

	protected $trueTableName = 'agent_admin_nav';
	
	
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
		$this->where(array($map))->delete();
		return true;
	}

	/**
	 * 获取全部菜单
	 * @param  string $type tree获取树形结构 level获取层级结构
	 * @return array       	结构数据
	 */
	public function getTreeData($type='tree',$order=''){
		// 判断是否需要排序
		if(empty($order)){
			$data=$this->select();
		}else{
			$data=$this->order('order_number is null,'.$order)->select();
		}

		// 获取树形或者结构数据
		if($type=='tree'){
			$data=InfinitenessAide::tree($data,'name','id','pid');
		}elseif($type=="level"){
			$data=InfinitenessAide::channelLevel($data,0,'&nbsp;','id');

			// 显示有权限的菜单
			/****************开发完成后取消注释*****************/
// 			$auth=new \Think\Auth();
// 			foreach ($data as $k => $v) {
// 				if ($auth->check($v['mca'], session( C('SESSION_ADMIN_KEY') )['id'] )) {
// 					foreach ($v['_data'] as $m => $n) {
// 						if(!$auth->check($n['mca'], session( C('SESSION_ADMIN_KEY') )['id'] )){
// 							unset($data[$k]['_data'][$m]);
// 						}

// 					}
// 				}else{
// 					// 删除无权限的菜单
// 					unset($data[$k]);
// 				}
// 			}
			/***************开发完成后取消注释*****************/
		}
		return $data;
	}


}
