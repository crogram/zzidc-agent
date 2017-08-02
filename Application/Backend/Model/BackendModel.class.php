<?php
namespace Backend\Model;
use Common\Model\BaseModel;
use Common\Aide\InfinitenessAide;	//无限极地归类

/**
 * -------------------------------------------------------
 * | 后台基础模型类
 * |@author: duanbin
 * |@时间: 2016年9月30日 上午10:58:27
 * |@version: 1.0
 * -------------------------------------------------------
 */
class BackendModel extends BaseModel{

	
	//填充搜索字段具体数据的数组，供fillData方法使用
	public $fillData = [];
	
	
	
    /**
     * 添加数据
     * @param  array $data  添加的数据
     * @return int          新增的数据id
     */
    public function addData($data){
        // 去除键值首尾的空格
        foreach ($data as $k => $v) {
            $data[$k]=trim($v);
        }
        $id=$this->add($data);
        return $id;
    }

    /**
     * 修改数据
     * @param   array   $map    where语句数组形式
     * @param   array   $data   数据
     * @return  boolean         操作是否成功
     */
    public function editData($map,$data){
        // 去除键值首位空格
        foreach ($data as $k => $v) {
            $data[$k]=trim($v);
        }
        $result=$this->where($map)->save($data);
        return $result;
    }

    /**
     * 删除数据
     * @param   array   $map    where语句数组形式
     * @return  boolean         操作是否成功
     */
    public function deleteData($map){
        if (empty($map)) {
            die('where为空的危险操作');
        }
        $result=$this->where($map)->delete();
        return $result;
    }

    /**
     * 数据排序
     * @param  array $data   数据源
     * @param  string $id    主键
     * @param  string $order 排序字段   
     * @return boolean       操作是否成功
     */
    public function orderData($data,$id='id',$order='order_number'){
        foreach ($data as $k => $v) {
            $v=empty($v) ? null : $v;
            $this->where(array($id=>$k))->save(array($order=>$v));
        }
        return true;
    }

    /**
     * 获取全部数据
     * @param  string $type  tree获取树形结构 level获取层级结构
     * @param  string $order 排序方式   
     * @return array         结构数据
     */
    public function getTreeData($type='tree',$order='',$name='name',$child='id',$parent='pid'){
        // 判断是否需要排序
        if(empty($order)){
            $data=$this->select();
        }else{
            $data=$this->order($order.' is null,'.$order)->select();
        }
        // 获取树形或者结构数据
        if($type=='tree'){
            $data=InfinitenessAide::tree($data,$name,$child,$parent);
        }elseif($type=="level"){
            $data=InfinitenessAide::channelLevel($data,0,'&nbsp;',$child);
        }
        return $data;
    }
  
    
    /**
     * ----------------------------------------------
     * | 处理需要排序的字段，
     * | 给每个排序的字段加上排序前缀，
     * | 在区别于查找的
     * | @时间: 2016年10月9日 下午6:23:17
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function getSortable(){
    	/**
    	 * ---------------------------------------------------
    	 * | 查看当前模型是否有sortable属性
    	 * | @时间: 2016年10月9日 下午6:26:07
    	 * ---------------------------------------------------
    	 */
    	$sortable = property_exists($this, 'sortable') ? $this->sortable: null;
    	if ($sortable === null) {
    		E('当前model未定义sortable属性');
    		return null;
    	}
    
    	/**
    	 * ---------------------------------------------------
    	 * | 处理排序前缀
    	 * | @时间: 2016年10月9日 下午6:27:07
    	 * ---------------------------------------------------
    	 */
    	$sort_prefix = C('SORT_PREFIX');
    	$return = [];
    	array_walk($sortable, function ($v, &$k, $sort_prefix) use(&$return) {
    		if ($v['sortable'] == true) {
    			$k = $sort_prefix.$k;
    		}
    		$return[$k] = $v;
    	}, $sort_prefix);
    	return $return;
    }
    
    
    /**
     * ----------------------------------------------
     * | 处理为搜索条件的字段
     * | 填充一些搜索条件的数据，需要从别的表获取
     * | @时间: 2016年10月11日 下午2:59:53
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    protected function fillSearchable( $fill_data ){
    
    	/**
    	 * ---------------------------------------------------
    	 * | 查看当前模型是否有sortable属性
    	 * | @时间: 2016年10月9日 下午6:26:07
    	 * ---------------------------------------------------
    	 */
    	$searchable = property_exists($this, 'searchable') ? $this->searchable: null;
    	if ($searchable === null) {
    		E('当前model未定义sortable属性');
    		return null;
    	}  
    
    	/**
    	 * ---------------------------------------------------
    	 * | 填充数据
    	 * | *****************这里注意*****************
    	 * | 为了填充数据更方便，同意用了数据库的字段别名(k,v)
    	 * | @时间: 2016年10月11日 下午3:02:17
    	 * ---------------------------------------------------
    	 */
    	if ( !empty( $fill_data ) ) {
	    	foreach ($fill_data as $k => $v){
	    		foreach ($v as $kk =>  $vv){
		    		$searchable[$k]['data'][$kk] = $vv;
	    		}
	    	}
    	}
//     	dump($fill_data);
//     	dump($this->searchable);die;
    	return $searchable;
    }
    

    /**
     * ----------------------------------------------
     * | 获取处理后的sortable里面的真正需要排序的字段
     * | @时间: 2016年10月12日 下午4:38:34
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function getSortableField($sortable){
    	$sorted = [];
    	foreach ($sortable as $k => $v){
    		if ($v['sortable'] == true) {
    			$sorted[$k] = $v;
    		}
    	}
    	return $sorted;
    }
    

}
