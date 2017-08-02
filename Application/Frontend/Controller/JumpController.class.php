<?php
namespace Frontend\Controller;

use Frontend\Controller\FrontendController;

class JumpController extends FrontendController{
	
	protected $aide = null;
	
/************************************************跳转自主管理平台开始************************************************/
    public function _initialize()
    {
        parent::_initialize();
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
        //加载跳转helper
        $this->aide = new \Common\Aide\JumpAide();
    }
    /**
    * 公共跳转自助管理平台
    * @date: 2016年10月26日 下午3:32:50
    * @author: Lyubo
    */
    public function user_call_self(){
        $api_name = $_GET['api_name'];
        $input_name = $_GET['input_name'];
        $business_id = $_GET['business_id'];
        $business_mark = $_GET['business_mark'];
        if($input_name == "vps"){//vps
            $vps_business = new \Frontend\Model\VpsModel();
            $result = $vps_business->get_business_one($business_id);
        }elseif($input_name == "usahost" || $input_name == "hkhost" || $input_name == "host" || $input_name== "dedehost" || $input_name == "cloudVirtual"){//美国，香港，国内，云虚拟主机
            $host_business = new \Frontend\Model\VirtualhostModel();
            $result = $host_business->get_business_one($business_id);
        }elseif($input_name == "fastcloudvps"){//快云
            $fastvps_business = new \Frontend\Model\FastvpsModel();
            $result = $fastvps_business->get_business_one($business_id);
        }elseif($input_name == "cloudspace"){//云空间
            $cloudspace_business = new \Frontend\Model\CloudspaceModel();
            $result = $cloudspace_business->get_business_one($business_id);
        }elseif($input_name == "cloudserver"){//快云服务器
            $cloudserver_business = new \Frontend\Model\CloudserverModel();
            $result = $cloudserver_business->get_api_bid_one($business_id);
        }elseif($input_name == "clouddb"){//快云数据库
            $clouddb_business = new \Frontend\Model\ClouddbModel();
            $result = $clouddb_business->get_api_bid_one($business_id);
        }
        if($result){
        	//1.获取服务码
        	$service_code = $this->aide->get_service_code($input_name, $api_name, $business_id, $business_mark);
        	//2.如果服务吗不为空
        	if (!empty($service_code)){
        		//跳转去自助管理平台
        		$res = $this->aide->jump_to_ssp($service_code);
        		if ($res){
        			echo $res;
        			die;
        		}
        	}
        	//如果有错误，获取错误信息
        	$info = $this->aide->getInfo();
        	
            //$this->self_manager($input_name,$api_name,$business_id,$business_mark);
        }else{
            session(null);
        }
        $error_info = empty($info) ? "该业务不属于该会员，请重新登陆！": $info;
        $this->error($error_info,U('frontend/index/index','',false));
    }

   
/************************************************跳转自主管理平台结束************************************************/
   
}