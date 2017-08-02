<?php
namespace Backend\Controller;
use Backend\Controller\BackendController;

/**
 * -------------------------------------------------------
 * | 站长助手
 * | @author: duanbin
 * | @时间: 2016年11月22日 下午3:01:45
 * | @version: 1.0
 * -------------------------------------------------------
 */
class HelpersController extends BackendController
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
	}
	
	/**
	 * ----------------------------------------------
	 * | 批量同步产品价格页面
	 * | @时间: 2016年11月22日 下午3:19:00
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function priceBacthSync(){
		
		$common_product_type = new \Common\Model\ProductTypeModel();
		
		$this->assign([
				'ptype' => $common_product_type->getCanBatchSyncPriceProductType(),
		]);
		$this->display("Helpers/priceBacthSync");
	}
	
	/**
	 * ----------------------------------------------
	 * | 批量同步产品价格
	 * | @时间: 2016年11月22日 下午3:01:31
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function priceBacthSynchronizer(){
		$request = request();
		$ptype = $request['ptype'];
		$type = $request['type'];
		
		$common_price = new \Common\Model\ProductPriceModel();
		$res = $common_price->priceBacthSynchronizer($ptype, $type);
		//返回结果
		$msg = $common_price->getError();
		if ($res === false) {
			if (IS_AJAX) {
				$this->error( $msg, '',true);
			}else {
				$this->error($msg);
			}
		}else {
			$url = U('Backend/Helpers/priceBacthSync', [  ], false);
			if (IS_AJAX) {
				$this->success($msg, $url, true);
			}else {
				$this->success($msg, $url);
			}
		}
	}
	
	
	
	
	
	
}