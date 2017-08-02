<?php
namespace Common\Aide;

use \GuzzleHttp\Client as Client;
use \Common\Aide\MysqlBackupAide as MysqlBackup;
use \Alchemy\Zippy\Zippy as Zippy;
require_once ('./vendor/agent/HttpClient.class.php');
/**
 * -------------------------------------------------------
 * | 系统升级类
 * | TODO:在线更新的具体实现
 * | @author: duanbin
 * | @时间: 2016年11月14日 下午6:36:36
 * | @version: 1.0
 * -------------------------------------------------------
 */
class UpdateAide {

	/**
	 * ----------------------------------------------
	 * | 处理压缩文件，或者解压缩文件
	 * | @时间: 2016年11月15日 下午5:42:29
	 * ----------------------------------------------
	 */
	private $compress_handler;
	
	/**
	 * ---------------------------------------------------
	 * | mysql数据库备份恢复文件
	 * | @时间: 2016年11月15日 下午5:42:55
	 * ---------------------------------------------------
	 */
	private $mysql_backup_hander;
	
	/**
	 * ---------------------------------------------------
	 * | 当前系统版本
	 * | @时间: 2016年11月14日 下午6:57:29
	 * ---------------------------------------------------
	 */
	private $version;
	
	/**
	 * ---------------------------------------------------
	 * | 文件操作handler
	 * | @时间: 2016年11月14日 下午6:57:50
	 * ---------------------------------------------------
	 */
	private $file_handler;
	
	/**
	 * ---------------------------------------------------
	 * | http-curl类，远程访问api
	 * | @时间: 2016年11月14日 下午6:58:08
	 * ---------------------------------------------------
	 */
	private $bus;
	
	/**
	 * ---------------------------------------------------
	 * | 软件更新的url地址
	 * | @时间: 2016年11月14日 下午7:04:00
	 * ---------------------------------------------------
	 */
	private $update_url;
	
	/**
	 * ----------------------------------------------
	 * | 用于保存一些错误信息
	 * | @时间: 2016年11月15日 上午9:50:44
	 * ----------------------------------------------
	 */
	private $info = '';
	
	/**
	 * ---------------------------------------------------
	 * | 下载下来的升级包文件的根目录
	 * | @时间: 2016年11月16日 下午5:50:12
	 * ---------------------------------------------------
	 */
	private $root_path;
	
	
	/**
	 * ----------------------------------------------
	 * | 初始化构造函数
	 * | @时间: 2016年11月15日 上午9:18:04
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function __construct(){
		
		$this->compress_handler = Zippy::load();

		$this->mysql_backup_hander = new MysqlBackup();
		$this->mysql_backup_hander->setDBName(C('DB_NAME'));
		
		$this->file_handler = new \Common\Aide\FileStorageAide();
		
		$this->version = $this->file_handler->read(C('VERSION_PATH'));
		
		$this->bus =  new Client();
		//$this->bus =  new \Agent\HttpClient($host);
		
		$this->update_url = C('UPDATE_URL');
		
		$this->root_path = getcwd().DS;
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 检查软件版本
	 * | @时间: 2016年11月15日 上午9:40:04
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function checkSoftwareVersion(){
		if (!$this->version){
			$this->info = '没有相应的版本信息！';
			return false;
		}
		//去服务器上获取相应的版本信息
		$host = $url = C('UPDATE_URL');
// 		$response = $this->bus->get($url, [ 'query'=> [ 'version' => $this->version ] ]);
// 		$version_info = $response->json();
		$response = \Agent\HttpClient::quickGet(trim($host. '?version='.$this->version));
		$version_info = json_decode($response, true);
		//处理返回结果
		if ($version_info['code'] == 2){
			$this->info = '当前版本已经是最新版本啦！';
			return false;
		}elseif ($version_info['code'] == 1){
			return $version_info['info'];
		}else {
			$this->info = '更新版本出现错误。';
			return false;
		}
	}
	
	/**
	 * ----------------------------------------------
	 * | 更新平台的主方法
	 * | @时间: 2016年11月15日 下午3:13:28
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function updating($input){
		$stage = $input['stage'];
		$version = $input['version'];
		
		//比较下要升级的版本和当前版本
		if (!version_compare($this->version, $version, '<')){
			$this->info = '当前版本为'.$this->version.',无法降级为'.$version.'版本';
			return false;
		}
		
		switch ($stage){
			//备份文件
			case 1:
				return $this->backup() ? true: false;
// 				return true;
				break;
			//下载升级包
			case 2:
				return $this->getSoftware($version) ? true: false;
// 				$update = $this->getSoftware($version) ? true: false;
				break;
			//执行升级操作
			case 3:
				return $this->unpackage($version) ? true: false;
				break;
		}
		
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 备份当前文件和数据
	 * | @时间: 2016年11月15日 下午4:11:31
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function backup(){
		//备份数据
		$res['data'] = $this->mysql_backup_hander->backup();
		//备份文件
		if (is_callable('set_time_limit')){
			$res['software'] = $this->Softwarebak(true);
		}else {
			$res['software'] = $this->Softwarebak();
		}
		if ($res['data']  !== true || !$res['software']){
			$this->info = $res['data']  !== true ? $this->mysql_backup_hander->error(): $this->info;
			return false;
		}else {
			return true;
		}

	}
	
	/**
	 * ----------------------------------------------
	 * | 备份文件
	 * | @时间: 2016年11月16日 上午9:35:03
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function Softwarebak($is_all = false, $backup_path = 'Softwarebak'){
			//备份目录，如果没有就创建./Backup/UserBackup
			$path = C('BACKUP_PATH').$backup_path;
			if (!is_dir($path)){
				mkdir($path, 0755, true);
			}
			//真正的压缩包文件名
			$name = 'agent_'.date('YmdHis') . '_'. uniqid('') . '.zip';
			$zip_file_path = $path.DS.$name;
			//临时文件名
			$zip_temp_path = $path.DS.'temp.zip';
			//不存在临时文件就创建
			if (!file_exists($zip_file_path)){
				file_put_contents($zip_temp_path, '');
			}
			//建立压缩文件句柄
			$zip = new \PclZip($zip_temp_path);
			if ($is_all){
				//全备份
				set_time_limit(60*10);
				$added_files = [
					realpath(getcwd().ltrim(APP_PATH, '.')),
					realpath(getcwd().'.'.DS.'Public'),
					realpath(getcwd().'.'.DS.'ThinkPHP'),
					realpath(getcwd().'.'.DS.'Uploads'),
					realpath(getcwd().'.'.DS.'vendor'),
					'.htaccess',
					'index.php',	
				];
			}else {
				//核心备份
				$added_files = [
						'Application' => realpath(getcwd().ltrim(APP_PATH, '.')),
						'index.php',
				];
			}
			//开始备份
			$nums = $zip->create($added_files,PCLZIP_OPT_REMOVE_PATH,realpath('./'));
			if ($nums == 0){
				$this->info = $zip->errorInfo(true);
				return false;
			}
			//将临时文件重命名为既定好的压缩文件
			if (!rename($zip_temp_path, $zip_file_path)){
				$this->info = '重命名压缩文件失败！';
				return false;
			}
			return true;

	}
	
	/**
	 * ----------------------------------------------
	 * | 获取要升级的软件包
	 * | @时间: 2016年11月16日 下午3:04:54
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getSoftware($version){
		//下载升级包文件地址
		$url = C('UPDATE_PACKAGE_DOWNOAD_URL');
		//拼凑升级包文件名称
		$file_name = str_replace('????', $version, C('UPDATE_PACKAGE_NAME'));
		$save_path = $this->root_path;
		
		try {
			//创建本地文件资源链接，用以保存下载下来的文件；
			//如果文件存在，则直接清空掉文件内容
			$fp = fopen($save_path.$file_name, 'w+');
			if (!$fp){
				$this->info = '创建下载文件失败...';
				return false;
			}
			//下载文件，并将文件保存在本地
			$stream = \GuzzleHttp\Stream\Stream::factory($fp);
			$response = $this->bus->get($url.$file_name, [ 'save_to' => $stream ]);

			//文件写入完成后，查看大小是否一致
			if (filesize($save_path.$file_name) != $response->getHeader('Content-Length')){
				unlink($save_path.$file_name);
				$this->info = '下载文件出错...';
				return false;
			}
			//都正确，说明下载文件没有问题。返回成功
			return true;
		}catch(\Exception $e){
			$this->info = $e->getMessage();
			return false;
		}
		
	}
	
	/**
	 * ----------------------------------------------
	 * | 解压缩文件，替换相应的文件执行升级操作；
	 * | @时间: 2016年11月16日 下午5:47:33
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function unpackage($version){
		$file_name = str_replace('????', $version, C('UPDATE_PACKAGE_NAME'));
		$save_path = $this->root_path;
		if (!file_exists($save_path.$file_name)){
			$this->info = '升级文件不存在！';
			return false;
		}
		
		//解压缩升级包文件
		$archive = new \PclZip($save_path.$file_name);
		//解压缩到extract/folder/这个目录中
		$list = $archive->extract(PCLZIP_OPT_PATH, $save_path);
		if ($list == 0) {
			$this->info = $archive->errorInfo(true);
			return false;
		}
//        try {
//            $archive = $this->compress_handler->open($save_path.$file_name);
//            $archive->extract($save_path.'temp');
//        }catch(\Exception $e){
//            $this->info = $e->getMessage();
//            return false;
//        }
		
		//TODO:sql导入,
		$return = true;
		$update_sql = $save_path.'Application'.DS.'Common'.DS.'Conf'.DS.'update.sql';
		if (file_exists($update_sql)){
			$res = $this->mysql_backup_hander->updateSql($update_sql);

			if ($res){
				//如果导入成功后，删除sql文件
				if (!unlink($update_sql)){
					$this->info .= 'sql文件删除失败。请手动删除'.$update_sql.'文件。';
				}
				$return = true;
			}else {
				$this->info .= '更新数据库出错，请手动导入'.'./Application'.DS.'Common'.DS.'Conf'.DS.'update.sql'.'文件到项目数据库，导入成功后删除此文件。或联系技术人员解决。';
				//解压缩完后，删除升级包文件
				if (!unlink($save_path.$file_name)){
					$this->info .= '升级软件包删除失败。请手动删除根目录下的'.$file_name.'升级软件包。';
				}
				return false;
			}
		}
		if (file_put_contents(C('VERSION_PATH'), $version) === false){
			$this->info = '软件版本信息写入失败...';
			return false;
		}
		//解压缩完后，删除升级包文件
		if (!unlink($save_path.$file_name)){
			$this->info .= '升级软件包删除失败。请手动删除根目录下的'.$file_name.'升级软件包。';
		}
		//删除升级时的备份文件
		/*if (!unlink(C('BACKUP_PATH').'Databak') && !unlink(C('BACKUP_PATH').'Softwarebak') ){
			$this->info .= '备份文件删除失败。为了站点安全，请移除(删除)站点根目录下的Backup目录下的文件夹和文件，及其里面的全部备份文件。';
			$return = true;
		}*/
		return $return;
	}
	
	
	
	
	/**
	 * ----------------------------------------------
	 * | 获取提示信息
	 * | @时间: 2016年11月15日 上午11:15:08
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function getInfo(){
		return $this->info;
	}
	
	/**
	 * ----------------------------------------------
	 * | 获取类内部属性的get方法
	 * | @时间: 2016年11月14日 下午6:59:05
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	private function __get($name){
		if (isset($this->$name)){
			return $this->$name;
		}else {
			return null;
		}
	}
	
}