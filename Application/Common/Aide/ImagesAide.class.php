<?php
namespace Common\Aide;

use Endroid\QrCode\QrCode;

/**
 * -------------------------------------------------------
 * | 图像助手
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午6:12:30
 * |@version: 1.0
 * -------------------------------------------------------
 */
class ImagesAide{
	
	
	
	/**
	 * ----------------------------------------------
	 * |二维码生成器
	 * |@时间: 2016年9月28日 下午6:14:09
	 * |@author: duanbin
	 * |@params: 
	 * |@return:
	 * ----------------------------------------------
	 */
	public function QRCoderPainter($text, $save_name = '', $logo_url = '', $label_text = ''){
		$qrCode = new QrCode();
		$qrCode
		->setText($text)
		->setSize(300)
		->setPadding(10)
		->setErrorCorrection('high')
		->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
		->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
		;
		//是否设置logo在二维码中
		 if ($logo_url != '' && file_exists($logo_url)){
		 	$qrCode->setExtension(pathinfo($logo_url, PATHINFO_EXTENSION))->setLogo($logo_url)->setLogoSize(100);
		 }
		 
		 //是否设置额外的提示文字
		 if ($label_text !=''){
		 	$qrCode->setLabel($label_text)->setLabelFontSize(16);
		 }

		 //选择是直接返回图片还是生成图片并保持；
		if ($save_name != ''){
			$save_name = C('UPLOAD_ROOT_PATH').$save_name;
			$qrCode->save($save_name);
			return ltrim($save_name, '.');
		}else{
			header('Content-Type: '.$qrCode->getContentType());
			echo $qrCode->render();die;
		}
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 图片上传方法
	 * | TODO：
	 * | @时间: 2016年11月10日 下午6:35:15
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function ImgUploader($input_name, $dirName, $fileSize = '', $thumb = array()){
		$file_upload = new \Common\Aide\FileUploadAide();
		return $file_upload->FileUploader($input_name, $dirName, ['jpg', 'gif', 'png', 'jpeg']);
	}
	
	
	/**
	 * ----------------------------------------------
	 * | 生成验证码的方法
	 * | @时间: 2016年12月1日 上午10:06:58
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function capthca($flag, $config = []){
		
		if (empty($config)){
			$config = [
					'fontSize' => 16,
					'fontttf' => '5.ttf',
					'length' => 4,
					'useNoise' => false,
					'codeSet' => '234678abcdefhijkmnpqrtuvwxyz',
					'imageW' => 120,
					'useCurve' => false,
					'imageH' => 34,
					'reset' => true,
			];
		}
		if($flag == 'frontend_white'){
		    $config = [
		        'fontSize' => 16,
		        'fontttf' => '5.ttf',
		        'length' => 4,
		        'useNoise' => false,
		        'codeSet' => '234678abcdefhijkmnpqrtuvwxyz',
		        'imageW' => 120,
		        'useCurve' => false,
		        'imageH' => 55,
		        'reset' => true,
		    ];
		}
		$Verify = new \Think\Verify($config);
		
		$Verify->entry($flag);
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 检验验证码是否正确
	 * | @时间: 2016年12月1日 上午10:12:32
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function checkCaptcha($code, $flag){
		$verify = new \Think\Verify();
		return $verify->check($code, $flag);
	}
	
	
	
	
	
	
	
	
	
	
	
}