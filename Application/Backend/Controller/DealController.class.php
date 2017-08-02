<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;
use Common\Respository\DealRespository;

/**
 * -------------------------------------------------------
 * | 后台控制器模板
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午5:06:01
 * |@version: 1.0
 * -------------------------------------------------------
 */
class DealController extends BackendController
{
	

	/**
	 * ----------------------------------------------
	 * | 初始化仓储和模型
	 * | @时间: 2016年10月9日 下午4:49:54
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function _initialize(){
		parent::_initialize();
		$this->model = D('Backend/Deal');
		$this->respository = new DealRespository($this->model);
	}
	
	/**
	 * ----------------------------------------------
	 * | 产品列表页
	 * | @时间: 2016年9月30日 下午3:30:32
	 * | @author: duanbin
	 * ----------------------------------------------
	 */
	public function index(){
		
		
		
		//表头，排序相关数据
		$th = $this->model->getSortable();
		//获取表头里面真正有排序的字段
		$sorted = $this->model->getSortableField($th);
		//未加排序前缀的表头
		$th_raw = array_keys($this->model->sortable);
		
		//获取筛选字段的需要的数据,筛选所需要的字段
		$search = $this->model->getFillData()->getSearchable();
		
		//获取需要展示的字段，列表的表头
		$field = implode(',', array_keys($this->model->sortable));
		//获取所有的数据
		$data = $this->respository->finder($field);
		//获取统计数据
		$counter = $this->respository->counter($field);
		
		//获取有使用的产品类型
		$catNotTrialType = D('Common/ProductType')->getCanNotTrialType();
		
// 		dump($counter);die;
		//处理分页参数问题
		$current_page = empty(request()['p']) ? 1: request()['p'];
		$this->assign([
				'search' => $search,
				'th' => $th,
				'th_count' => count($th),	//表格列数
				'th_raw' => $th_raw,
				'sorted' => $sorted,	//真正可以排序的字段
				'data' => $data['data'],
				'sum_pages' => $data['sumPages'],
				'total' => $data['total'],
				'current_page' => $current_page,
				'per_page' => $data['perPage'],
				'pages' => $data['show'],
				'request' => request(),
				'catNotTrialType' => $catNotTrialType,
				'counter' => $counter,
		]);
		
		$this->display();
	}



	
	
	
	

}