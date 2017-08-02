<?php

/**
 * ----------------------------------------------
 * | 本地文件存储类
 * | @时间: 2016年11月9日 下午6:27:32
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
namespace Common\Aide;
use Think\Storage\Driver\File;

class FileStorageAide extends File{
	/**
	 * 本地写文件
	 */
	public function put($rootpath,$filename, $content,$maxSize=-1, $cover = TRUE){
		$filename = '.'.$rootpath.$filename;
		if($maxSize!=-1){
			if(strlen($content>$maxSize)){
				return '文件大小超过限制';
			}
		}
		$dir         =  dirname($filename);
        if(!is_dir($dir))
            mkdir($dir,0755,true);
        if(false === file_put_contents($filename,$content)){
            E(L('_STORAGE_WRITE_ERROR_').':'.$filename);
        }else{
            $this->contents[$filename]=$content;
            return true;
        }
	}

	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	
	public function listFile($rootpath, $path ,$allowFiles='all'){
		$path = $_SERVER['DOCUMENT_ROOT'].__ROOT__.$rootpath.$path;
		return $this->getList($path, $allowFiles);
	}
	
	public function getList($path ,$allowFiles='all' , &$files = array()){
		if (!is_dir($path)) return null;
	    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
	    $handle = opendir($path);
	    while (false !== ($file = readdir($handle))) {
	        if ($file != '.' && $file != '..') {
	            $path2 = $path . $file;
	            if (is_dir($path2)) {
	                $this->getList($path2, $allowFiles, $files);
	            } else {
	            	if($allowFiles!='all'){
		                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
		                    $files[] = array(
		                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
		                        'mtime'=> filemtime($path2)
		                    );
		                }
	            	}else{
	            		$files[] = array(
		                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
		                        'mtime'=> filemtime($path2)
	            		);
	            	}
	            }
	        }
	    }
	    return $files;
	}

	/**
	 * 得到路径
	 */
	public function getPath($rootpath,$path){
		$path = __ROOT__.$rootpath.$path;
		return $path;
	}
	
	/**
	 * ----------------------------------------------
	 * | 递归删除文件夹及其下面的子文件
	 * | @时间: 2016年11月14日 下午4:59:25
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function deleteDirRecursion($path, $remove_dir = true) {
		//如果是文件就直接删除文件
		if (is_file($path)){
			return unlink($path);
			//否则就是文件夹
		}else if (is_dir($path)){
			//打开文件夹
			if ($handle = opendir($path)){
				//循环目录
				while (false !== ($file = readdir($handle))) {
					//跳过父文件夹和当前文件夹；
					if ($file == '.' || $file == '..'){
						continue;
					}
					//递归调用自身；
					$this->deleteDirRecursion($path.'/'.$file, $remove_dir);			
				}
				//关闭文件夹资源
				closedir($handle);
			}
			//清空目录里的文件后  删除当前文件夹；
			if ($remove_dir){
				return rmdir($path);
			}
		}else {
			return false;
		}
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}