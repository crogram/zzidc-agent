<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;
/**
* 展示产品
* @date: 2016年11月4日 下午4:32:32
* @author: Lyubo
*/
class ProductController extends FrontendController{
	
	protected  $model = null;
	
	public function _initialize(){
		parent::_initialize();
		
		$this->model = new \Frontend\Model\ProductModel();
		
	}
	
	
    /**
    * VPS列表
    * @date: 2016年11月13日 下午3:49:41
    * @author: Lyubo
    * @return:
    */
    public function vps(){
        $data = $this->model->conver_par();
        $product_info = $this->model->get_vps_product($data);
        $banner = $this->model->banner(3);
        $this->assign([
            'banner'=>$banner,
            'sign'=>$data['type'],
            'product_list' =>$product_info,
        ]);
        $this->display();
    }
    /**
     * VPS详情
     * @date: 2016年11月12日 上午9:45:40
     * @author: Lyubo
     * @param: product_id
     * @return: info
     */
    public function vpsDetail(){
        $vps_info = $this->model->vps_info();
        if($vps_info === false){
            $this->error("参数错误",U('frontend/index/index',[],false));
        }
        $this->assign([
            'product_info' =>$vps_info['product_info'],
            'product_config' =>$vps_info['product_config'],
            'product_price' =>$vps_info['product_price'],
            'system_type'=>$vps_info['system_type'],
            'price_id'=>$vps_info['price_id'],
            'price'=>$vps_info['price'],
            'is_product_info'=>true,
        ]); 
        $this->display("Product/vpsDetail");
    }
    /**
    * 获取虚拟主机产品列表
    * @date: 2016年11月4日 下午4:46:00
    * @author: Lyubo
    * @param: $api_type
    * @return:
    */
    public function virtualhost(){
        $data = $this->model->conver_par();
        $product_type_list = $this->model->get_type_list("3");//3是虚拟主机的大类
        $product_info = $this->model->get_host_product();
        $banner = $this->model->banner(2);
        $this->assign([
            'sign'=>$data['type'],
            'banner'=>$banner,
            'product_list' =>$product_info,
            'ptype_list'=>$product_type_list
        ]);
        $this->display();
    }
    /**
    * 虚拟主机详情
    * @date: 2016年11月12日 上午9:45:40
    * @author: Lyubo
    * @param: product_id
    * @return: info
    */
    public function vhostDetail(){
        $virtualhost_info = $this->model->virtualhost_info();
        if($virtualhost_info === false){
            $this->error("参数错误",U('frontend/index/index',[],false));
        }
        $this->assign([
            'product_info' =>$virtualhost_info['product_info'],
            'product_config' =>$virtualhost_info['product_config'],
            'product_price' =>$virtualhost_info['product_price'],
            'is_product_info'=>true,
            'system_type'=>$virtualhost_info['system_type'],
            'price_id'=>$virtualhost_info['price_id'],
            'price'=>$virtualhost_info['price'],
        ]);
        $this->display("Product/vhostDetail");
    }
    
    
    /**
     * ----------------------------------------------
     * | ssl证书列表页
     * | @时间: 2016年12月5日 上午10:10:07
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function ssl(){
    	$products = $this->model->get_ssl_product();
        $banner = $this->model->banner(4);
        if($products === false){
            $this->error("获取产品信息错误,请联系管理员！");
        }
    	$this->assign([
            'banner'=>$banner,
            'products' => $products['products'],
            'multi_domains_price' => $products['multi_domains_price'],
    	]);
    	$this->display();
    }
    
    /**
     * ----------------------------------------------
     * | ssl证书详情
     * | @时间: 2016年12月5日 上午10:41:12
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function ssldetail(){
    	$ssl_info = $this->model->get_one_ssl_info();
        if($ssl_info === false){
            $this->error("参数错误",U('frontend/index/index',[],false));
        }
    	$this->assign([
    			'product_info' =>$ssl_info['product_info'],
    			'product_config' =>$ssl_info['product_config'],
    			'product_price' =>$ssl_info['product_price'],
    			'is_product_info'=>true,
    			'extra'=>$ssl_info['extra'],
    			'price_id'=>$ssl_info['price_id'],
    			'price'=>$ssl_info['price'],
    	]);
    	
    	$this->display();
    }
    
    
    
    
    
}