<?php
namespace Common\Aide;

/**
 * -------------------------------------------------------
 * | 文件上传助手
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午6:12:30
 * |@version: 1.0
 * -------------------------------------------------------
 */
class FileUploadAide{
	
	
	
	
	
	
	
	
	
	
	/**
	 * ----------------------------------------------
	 * | 文件上传方法
	 * | （上传图片并生成缩略图）
     * | 用法：
     * | $ret = uploadOne('logo', 'Goods', array(
     * |        array(600, 600),
     * |         array(300, 300),
     * |         array(100, 100),
     * |    ));
     * |  返回值：
     * | if($ret['ok'] == 1)
     * |      {
     * |          $ret['images'][0];   // 原图地址
     * |          $ret['images'][1];   // 第一个缩略图地址
     * |          $ret['images'][2];   // 第二个缩略图地址
     * |          $ret['images'][3];   // 第三个缩略图地址
     * |      }
     * |     else
     * |      {
     * |          $this->error = $ret['error'];
     * |          return FALSE;
     * |      }
	 * | @时间: 2016年11月10日 下午6:35:15
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function FileUploader($inputName, $dirName, $exts = array(), $fileSize = '', $thumb = array()){
		if(isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == 0)
		{
			$upload = new \Think\Upload();// 实例化上传类
			if (!empty($fileSize)) {
				$upload->maxSize = (int)$fileSize*1024*1024;// 设置附件上传大小
			}else{
				$upload->maxSize = (int)C('FILE_MAX_SIZE')*1024*1024;// 设置附件上传大小
			}
			if (!empty($exts)) {
				$upload->exts = $exts;// 设置附件上传类型
			}else{
				$upload->exts = C('ALLOW_FILE_EXTS');// 设置附件上传类型
			}
			$upload->savePath = $dirName . '/'; // 图片二级目录的名称
			$upload->rootPath = C('UPLOAD_ROOT_PATH'); // 图片二级目录的名称
			$rootPath = ltrim($upload->rootPath, '.');	//生产图片路径用，因为前台输出是不需要带 . 所以将其除去后用于保存图片路径
			// 上传文件
			// 上传时指定一个要上传的图片的名称，否则会把表单中所有的图片都处理，之后再想其他图片时就再找不到图片了
			$info   =   $upload->upload(array($inputName=>$_FILES[$inputName]));
			// dump($info);
			if(!$info)
			{
				return array(
						'ok' => 0,
						'error' => $upload->getError(),
				);
			}
			else
			{
				$ret['ok'] = 1;
				$ret['file'][0] = $fileName = $rootPath.$info[$inputName]['savepath'] . $info[$inputName]['savename'];
				// 判断是否生成缩略图
				if($thumb)
				{
					$image = new \Think\Image();
					// 循环生成缩略图
					foreach ($thumb as $k => $v)
					{
						$ret['file'][$k+1] = $info[$inputName]['savepath'] . 'thumb_'.$k.'_' .$info[$inputName]['savename'];
						// 打开要处理的图片
						$image->open($fileName);
						$image->thumb($v[0], $v[1])->save($ret['file'][$k+1]);
					}
				}
				return $ret;
			}
		}
	}
	
	
	
}