<?php
namespace Common\Respository;

/**
 * -------------------------------------------------------
 * | 基础仓储类
 * | 子类可继承实现的方法有
 * | whereExtra()方法，扩充where条件
 * | orderExtra()方法，扩充order条件
 * | joinsExtra()方法，扩充连表
 * | 以上三个方法是为了连表时加别名的情况，子类可视情况具体实现
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:50:29
 * | @version: 1.0
 * -------------------------------------------------------
 */
class BaseRespository {
	
	//存储模型对象
	private $model = null;
	
	protected $alias = 'a';
	
	protected $table_prefix = '';
	
	
	/**
	 * ----------------------------------------------
	 * | 构造函数传入模型
	 * | @时间: 2016年10月9日 下午4:47:48
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function __construct($model = null){
		if ($model !==null){
			$this->model = $model;
		}
		$this->table_prefix = C('DB_PREFIX');
	}
	
	/**
	 * ----------------------------------------------
	 * | 手动传入模型
	 * | @时间: 2016年10月9日 下午3:57:31
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public  function makeModel($model) {
		$this->model = $model;
		return $this;
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取传入的模型
	 * | @时间: 2016年10月9日 下午3:57:58
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function model(){
		return $this->model;
	}
	
	/**
	 * ----------------------------------------------
	 * | where条件构造器
	 * | @时间: 2016年10月9日 下午4:02:03
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function whereBuilder(){
		
		$where = [];
		
		//获取请求参数
		$request = request();
		//获取允许筛选的字段和值；
		$searchable = $this->model()->getFillData()->getSearchable();
		
		/**
		 * ---------------------------------------------------
		 * | 处理where条件，用参数与searchable数组进行map
		 * | TODO: 对于时间字段的where条件处理
		 * | @时间: 2016年10月12日 下午4:16:18
		 * ---------------------------------------------------
		 */
		foreach ($searchable as $k => $v){
			if (!empty($v['data'])) {
				if ( array_key_exists($request[$k], $v['data']) ){
					$where[$k] = [ 'eq', $request[$k] ];
				}				
			}else if($v['html_type'] == 'date'){
				//判断是否是时间参数
				$start_str = strtotime($request[ $k.$v['start_name'] ]) ? $request[ $k.$v['start_name'] ]: false;
				$end_str = strtotime($request[ $k.$v['end_name'] ]) ? $request[ $k.$v['end_name'] ]: false;
				
				$start_time = strtotime($start_str . ' 00:00:00 ');
				$end_time = strtotime($end_str . ' 23:59:59 ');
				$begin = $end = '';
				//开始，结束时间都有
				if (  !empty($start_str)   &&  !empty($end_str)   ) {
					if ($start_time > $end_time) {
						$end = $end_str . ' 23:59:59 ';
						$begin = $start_str . ' 00:00:00 ';
					}else {
						$begin =  $start_str . ' 00:00:00 ';
						$end = $end_str . ' 23:59:59 ';
					}
					$where[$k] = [ 'BETWEEN', [ $begin, $end ] ];
					//只有开始时间
				}elseif ( !empty($start_str)  && empty($end_str) ){
					$begin = $start_str . ' 00:00:00 ';
					$where[$k] = [ 'EGT', $begin ];
					//只有结束时间
				}elseif ( empty($start_str) && !empty($end_str) ){
					$end = $end_str . ' 23:59:59 ';
					$where[$k] = [ 'ELT', $end ];
				}
				
			}else if ($k == 'key'){
				if ( !empty($request['value']) ){
					if ( array_key_exists($request[$k], $v) ){
						$where[$request[$k]] = [ 'like', '%'.$request['value'].'%' ];
					}
				}				
			}
		}
		
		
		/**
		 * ---------------------------------------------------
		 * | 这里试探的调用下_whereExtra方法
		 * | where条件扩充方法
		 * | 如果是连表的话，可能会有别名的说法
		 * | 所以条件可能要再次处理下
		 * | @时间: 2016年10月13日 下午6:23:42
		 * ---------------------------------------------------
		 */
		if (method_exists($this, 'whereExtra')){
			$where = $this->whereExtra($where, $request);
		}

// 		dump($where);die;
		return $where;
		
	}
	
	
	/**
	 * ----------------------------------------------
	 * | order排序构造器
	 * | @时间: 2016年10月9日 下午4:02:03
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function orderBuilder(){
		
		$order = '';
		//获取参数
		$request = request();
		
		/**
		 * ---------------------------------------------------
		 * | 获取处理th表头后的需要排序的字段
		 * | @时间: 2016年10月12日 下午4:41:05
		 * ---------------------------------------------------
		 */
		$model = $this->model();
		$sorted = $model->getSortableField($model->getSortable());
		/**
		 * ---------------------------------------------------
		 * | 处理排序参数，值为1为正序，值为-1为倒叙
		 * | @时间: 2016年10月12日 下午4:15:35
		 * ---------------------------------------------------
		 */
		foreach ($sorted as $k => $v){
			if ( array_key_exists($k, $request) && in_array($request[$k], [ 1, -1 ])){
				$field = str_ireplace(C('SORT_PREFIX'), '', $k);
				if ($request[$k] == 1) {
					$order .= $field.' ASC ';
				}elseif ($request[$k] == -1){
					$order .= $field.' DESC ';
				}
				break;
			}
		}
		
		/**
		 * ---------------------------------------------------
		 * | 这里试探的调用下_whereExtra方法
		 * | where条件扩充方法
		 * | 如果是连表的话，可能会有别名的说法
		 * | 所以条件可能要再次处理下
		 * | @时间: 2016年10月13日 下午6:23:42
		 * ---------------------------------------------------
		 */
		if (method_exists($this, 'orderExtra')){
			$order = $this->orderExtra($order, $request);
		}
		
		return $order;
	}
	
	
	
	/**
	 * ----------------------------------------------
	 * | 查询构建器
	 * | find=>查询; page=>分页;
	 * | @时间: 2016年10月11日 下午6:15:41
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function queryBuilder( $field, $where, $order, $page = '' ){
		if ($order == '') {
			//排序条件
			$order = $this->orderBuilder();
		}
		
		//白名单列表不需要关联查询
		if (CONTROLLER_NAME == 'White'){
		    $order = 'create_time DESC';
		}
		//这里扩充下看是否有join的额外条件有的话就调用这个方法
		if (method_exists($this, 'joinsExtra')){
			$join = $this->joinsExtra();
		}else {
			$join = [];
		}
		//如果有连表，加上别名
		if (empty($join)) {
			$query = $this->model()->field($field);
		}else {
			$query = $this->model()->alias( $this->alias )->field($field)->join( $join );
		}

		if ($page != '') {
			$data = $query
			->where($where)->order($order)
			->limit($page['page']->firstRow.','.$page['page']->listRows)->select();
		}else{
			$data = $query->where($where)->count();
// 			dump($query->_sql());die;
		}
		return $data;
	}
	
	/**
	 * ----------------------------------------------
	 * | 搜索函数，返回相应的结果集
	 * | @时间: 2016年10月9日 下午4:03:21
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function finder($field = '*', $perPage = 15, $where = [],$order = ''){
		/**
		 * ---------------------------------------------------
		 * | 处理下读取的表字段
		 * | @时间: 2016年10月14日 上午10:24:40
		 * ---------------------------------------------------
		 */
		if (method_exists($this, 'fieldsExtra')){

			$field = $this->fieldsExtra($field);
		}
		if (is_array($field)) {
			$field = implode(',', $field);
		}
		
		//where条件
		if ($where == []) {
			$where = $this->whereBuilder();
		}
// 		dump($where);die;
		//分页
		$page = $this->pagination($field, $where, $perPage);
		//获取所有符合条件的数据
		$data['data'] = $this->queryBuilder($field, $where, $order, $page);
	   
		$data['sumPages'] = $page['sumPages'];
		$data['total'] = $page['total'];
		$data['show'] = $page['show'];
		$data['perPage'] = $page['perPage'];
		
		foreach ($data['data'] as $k => $v){
			if (empty($v['login_name'])){
				if (CONTROLLER_NAME == 'ApiLog'){
					$data['data'][$k]['login_name'] = '管理员';					
				}else {					
					$data['data'][$k]['login_name'] = '待转让会员';
				}
			}
		}
		
		return $data;
	}
	
	
	
	/**
	 * ----------------------------------------------
	 * | 获取相应模型表的总记录数
	 * | @时间: 2016年10月9日 下午3:58:48
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	protected function pagination($field, $where, $perPage){
		
		/**
		 * ---------------------------------------------------
		 * | 获取总记录数
		 * | @时间: 2016年10月11日 下午6:28:32
		 * ---------------------------------------------------
		*/
		$count = $this->queryBuilder($field, $where);
		//拿到请求的参数
		$request = request();
		if ($request['p'] > ceil($count/$perPage)) {
			$request['p'] = ceil($count/$perPage);
		}
	
		//处理每页显示多少条
		if ( !empty($request['perpage']) ){
			$perPage = $request['perpage'];
		}
	
		//实例化tp自带的分页类
		$page = new \Think\Page($count, $perPage);
		$data['page'] = $page;
		//分页跳转的时候保证查询条件
		foreach($request as $key=>$val) {
			$page->parameter[$key]   =   $val;
		}
		//处理返回数据
		$data['show'] = $page->show();
		$data['sumPages'] = ceil($count/$perPage);
		$data['total'] = $count;
		$data['perPage'] = $perPage;
		return $data;
	}
	
	
	
	/**
	 * ----------------------------------------------
	 * | 根据条件，返回一条记录，失败返回false
	 * | @时间: 2016年10月14日 下午3:05:07
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function firstOrFalse($flag, $fields='*'){
		$res = null;
		if (is_array($flag)){
			$res = $this->model()->field( $fields )->where($flag)->find();
		}else {
			$res = $this->model()->field( $fields )->find($flag);
		}

		return !$res ? false: $res;
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新某条记录
	 * | @时间: 2016年10月17日 上午9:49:53
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function update(array $data, $id, $attribute="id"){
		return $this->model()->where([ $attribute =>[ 'eq', $id ] ])->save($data);
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 添加
	 * | @时间: 2016年10月17日 上午9:56:40
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function create(array $data){
		return $this->model()->add($data);
	}
	
	/**
	 * ----------------------------------------------
	 * | 删除
	 * | @时间: 2016年10月17日 上午9:57:08
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function delete($where){
		if (empty($where)){
			return false;
		}
		return $this->model()->where($where)->delete();
	}
	
	
	
}