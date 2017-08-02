<?php
namespace Common\Controller;
use Think\Controller;

/**
 * -------------------------------------------------------
 * | 全局基础控制器
 * |@author: duanbin
 * |@时间: 2016年9月30日 上午10:35:44
 * |@version: 1.0
 * -------------------------------------------------------
 */
class BaseController extends Controller{

	//仓库管理员
	protected $respository = null;
	//数据模型
	protected $model = null;

	public function _empty()
	{
		$this->error("无法加载操作：".ACTION_NAME);
	}
	
	public function _initialize(){
		//5.4版本的php获取系统临时目录的bug的临时解决方案。
		if (!getenv('TMPDIR') && !getenv('TEMP') && !getenv('TMP') && !ini_get('sys_temp_dir')){
// 			if (PHP_OS == 'Windows' || PHP_OS == 'WIN32' || PHP_OS == 'WINNT'){				
				$temp = TEMP_PATH;
// 			}else {
// 				$temp = '/tmp';				
// 			}

			$temp_dir = getenv('TMPDIR') ? getenv('TMPDIR'): $temp;
			putenv('TMPDIR='.$temp_dir);
			$temp_dir = getenv('TEMP') ? getenv('TEMP'): $temp;
			putenv('TEMP='.$temp_dir);
			$temp_dir = getenv('TMP') ? getenv('TMP'): $temp;
			putenv('TMP='.$temp_dir);
			ini_set("sys_temp_dir", $temp);
		}

	}

}
