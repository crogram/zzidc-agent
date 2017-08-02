<?php

/**
 * ----------------------------------------------
 * | 安装环境检测
 * | @时间: 2016年11月18日 上午11:06:38
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function test_env() {

	$env = [
			'os' => [
					'display' => '操作系统',
					'allow' => '不限制',
					'current' => PHP_OS,
					'result' => true,
			],
			'php' => [
					'display' => 'PHP版本',
					'allow' => '5.4+',
					'current' => PHP_VERSION,
					'result' => version_compare('5.4.0+', PHP_VERSION, '<'),
			],
			'upload' => [
					'display' => '附件上传',
					'allow' => '2M',
					'current' => ini_get('upload_max_filesize'),
					'result' => ( 2 <= (ini_get('upload_max_filesize')+0) ) ? true: false,	
			],
			'gd' => [
					'display' => 'GD库',
					'allow' => '2.0',
					'current' => '未开启',
					'result' => true,
			],
// 			'disk' => [
// 					'display' => '所需磁盘空间',
// 					'allow' => '300MB',
// 					'current' => '未知',
// 					'result' => true,	
// 			],
			'bcmath' => [
					'display' => 'BCMath扩展',
					'allow' => '开启',
					'current' => '未知',
					'result' => true,
			],
	];
	//检测gd
	if (function_exists('gd_info')){
		$gd_info = gd_info();
		$env['gd']['current'] = GD_VERSION;
		$env['gd']['result'] = version_compare($env['gd']['allow'], GD_VERSION, '<');
	}else {
		$env['gd']['current'] = '未检测到GD库';
		$env['gd']['result'] = false;
		session('has_error',true);
	}
	
	//检测磁盘空间
// 	if (function_exists('disk_free_space')){
// 		$free_space = disk_free_space(getcwd());
// 		if (false !== $free_space){
// 			$env['disk']['current'] = floor($free_space/1024/1024)."MB";
// 			$env['disk']['result'] = ( 300 <= ($free_space/1024/1024) )? true: false;
// 		}else {
// 			$env['disk']['current'] = '未检测到可用磁盘空间大小';
// 			$env['disk']['result'] = false;
// 			session('has_error',true);
// 		}
// 	}else {
// 		$env['disk']['current'] = '未检测到可用磁盘空间大小';
// 		$env['disk']['result'] = false;
// 		session('has_error',true);
// 	}
	
	//检测bcmath扩展
	if (is_callable('bcadd')){
		$env['bcmath']['current'] = '已开启';
		$env['bcmath']['result'] = true;
	}else {
		$env['bcmath']['current'] = '未开启该扩展不可用';
		$env['bcmath']['result'] = false;
		session('has_error',true);
	}
	
	$return['env'] = $env;
	//处理错误信息
	$return['error'] = '';
	if (!$env['php']['result']){
		$return['error'] .= '需要php版本5.4及其以上版本，当前PHP版本'.$env['php']['current'].'不符合要求<br />';
	}
	if (!$env['upload']['result']){
		$return['error'] .= '需要允许附件上传大小为'.$env['upload']['allow'].'，当前允许附件上传大小为'.$env['upload']['current'].'不符合要求<br />';
	}
	if (!$env['gd']['result']){
		$return['error'] .= '需要开启GD'.$env['gd']['allow'].'库扩展，当前'.$env['gd']['current'].'不符合要求<br />';
	}
// 	if (!$env['disk']['result']){
// 			$return['error'] .= '需要磁盘空间'.$env['disk']['allow'].'，当前'.$env['disk']['current'].'不符合要求<br />';
// 	}
	if (!$env['bcmath']['result']){
		$return['error'] .= '需要开启PHP的BCMath扩展，当前'.$env['bcmath']['current'].'不符合要求<br />';
	}

	return $return;
}

/**
 * ----------------------------------------------
 * | 检查文件目录的读写权限
 * | @时间: 2016年11月18日 上午11:32:34
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function test_mod_about_dir_and_file(){
	$file_and_dir = [
		'Uploads' => [
				'display' => '上传目录',
				'allow' => '可写',
				'current' => '未知',
				'result' => false,
		],
		'Backup' => [
				'display' => '备份目录',
				'allow' => '可写',
				'current' => '未知',
				'result' => false,
		],
		'Application'.DS.'Runtime' => [
				'display' => '缓存目录',
				'allow' => '可写',
				'current' => '未知',
				'result' => false,
		],
		'Application'.DS.'Common'.DS.'Conf' => [
				'display' => '配置目录',
				'allow' => '可写',
				'current' => '未知',
				'result' => false,
		],
		'Update' => [
				'display' => '系统更新目录',
				'allow' => '可写',
				'current' => '未知',
				'result' => false,
		],
		'Application'.DS.'Common'.DS.'Conf'.DS.'version.lock' => [
				'display' => '版本信息文件',
				'allow' => '可写',
				'current' => '未知',
				'result' => false,
		],
	];

	array_walk($file_and_dir, function (&$vv, $kk){
		if ( file_exists($kk) ){
			if (is_writable($kk)){
				$vv['current'] = '可写';
				$vv['result'] = true;
			}else {
				$vv['current'] = '不可写';
				$vv['result'] = false;
				session('has_error',true);
			}
		}else {
			if( mkdir($kk, '0755') ){
				$vv['current'] = '可写';
				$vv['result'] = true;
			}else {
				$vv['current'] = '不存在，且创建失败。';
				$vv['result'] = false;
				session('has_error',true);
			}
		}
	});

	return $file_and_dir;
	
}

/**
 * ----------------------------------------------
 * | 某些特殊函数的检测
 * | @时间: 2016年11月18日 下午2:43:39
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function test_func(){
	$func = [
			'pdo_mysql' => [
					'display' => 'pdo_mysql扩展',
					'allow' => '开启',
					'current' => '未开启',
					'result' => false,
			],
			'curl' => [
					'display' => 'curl扩展',
					'allow' => '开启',
					'current' => '未开启',
					'result' => false,
			],
			'getcwd' => [
					'display' => 'getcwd函数',
					'allow' => '开启',
					'current' => '禁用',
					'result' => false,
			],
			'fopen' => [
					'display' => 'fopen函数',
					'allow' => '开启',
					'current' => '禁用',
					'result' => false,
			],
	];
	
	$extensions = get_loaded_extensions();
	if (in_array('curl', $extensions)){
		$func['curl']['current'] = '开启';
		$func['curl']['result'] = true;
	}else {
		session('has_error',true);
	}
	if (in_array('pdo_mysql', $extensions)){
		$func['pdo_mysql']['current'] = '开启';
		$func['pdo_mysql']['result'] = true;
	}else {
		session('has_error',true);
	}
	
	if (is_callable('getcwd')){
		$func['getcwd']['current'] = '开启';
		$func['getcwd']['result'] = true;
	}else {
		session('has_error',true);
	}
	
	if (is_callable('fopen')){
		$func['fopen']['current'] = '开启';
		$func['fopen']['result'] = true;
	}else {
		session('has_error',true);
	}
	
	return $func;
}

/**
 * ----------------------------------------------
 * | 安装ing....
 * | @时间: 2016年11月20日 上午11:46:09
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function installing(){
	$return['status'] = true;
	$return['info'] = '';
	$request = I('post.');
	
	//处理参数合法性
	if ( !filter_var($request['admin_username'], FILTER_VALIDATE_EMAIL) ){
		$return['status'] = false;
		$return['info'] = '管理员账号必须是邮箱哦！';
		return $return;
	}
	if ( !preg_match_all( '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[a-zA-Z\d]{6,20}$/', $request['admin_password'], $matchs)){
		$return['status'] = false;
		$return['info'] = '管理员密码必须是数字和大小写字母 6-20位哦！';
		return $return;
	}
	if ($request['admin_password'] != $request['admin_conform_password']){
		$return['status'] = false;
		$return['info'] = '管理员密码和确认密码不一致';
		return $return;
	}
	if (is_callable('set_time_limit')){
		set_time_limit(60*10);
	}else {
		$return['status'] = false;
		$return['info'] = '由于安装时间较长，需开启set_time_limit函数！';
		return $return;
	}
	//生成随机key值
	$string = new \Org\Util\String();
	$auth_key = $string->randString(32, -1, 'oOLl01`~!@#$%^&*()_+-=[]{};:"|,.<>/?');
	$request['auth_key'] = $auth_key;
	$db = null;
	//处理数据库相关信息
	$return = test_mysql($db, $request, $return);
	if (!$return['status']){
		return $return;
	}
	//处理超级管理员
	$return = born_super_admin($db, 'agent_users', $request, $return);
	if (!$return['status']){
		return $return;
	}
	
	//将相关信息保存至配置文件
	$return = put_contents_to_config($request, $return);
	if (!$return['status']){
		return $return;
	}
	clear_cache();
	return $return;
}

/**
 * ----------------------------------------------
 * | 处理数据库相关(数据库连接是否OK，sql文件执行是否ok)
 * | @时间: 2016年11月20日 上午11:20:36
 * | @author: duanbin
 * | @param: &$db 外面定义的一个变量，方法内部会给你其赋值为一个数据库链接对象(PDO)
 * | @return: type
 * ----------------------------------------------
 */
function test_mysql(&$db, $request, $return){
	//数据库信息
	$database_username = $request['database_username'];
	$database_passwd = $request['database_password'];
	$database_port = $request['port'];
	$database_address = $request['address'];
	$database_name = $request['database_name'];
	
	//链接数据库检测
	//type://username:passwd@hostname:port/DbName#charset
// 	$dsn = 'mysql:'.'dbname='.$request['database_name'].';host='.$request['address'].':'.$request['port'];
	$dsn = 'mysql://'.$database_username.':'.$database_passwd.'@'.$request['address'].':'.$request['port'].'/'.$request['database_name'];
	try {
		$db = \Think\Db::getInstance($dsn);
// 		$db = new PDO($dsn, $database_username, $database_passwd, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
	} catch (\Exception $e) {
		$return['info'] = $e->getMessage();
		$return['status'] = false;
		return $return;
	}
	//查看旧版本升级sql是否存在
	$install_sql = CONFIG_PATH.DS.C('INSTALL_SQL_NAME');
	$update_sql = CONFIG_PATH.DS.C('UPDATE_SQL_NAME');
	//查看安装语句是否存在----这会优先安装语句
	if (!file_exists($install_sql)){
		//如果安装语句不存在，那么查看升级语句是否存在
		if (!file_exists($update_sql)){
			//如果两个都不存在，那么就报错
			$return['info'] = '安装sql语句文件不存在('.$install_sql.'或'.$update_sql.')，无法安装。';
			$return['status'] = false;
			return $return;
		}else {
			//如果升级语句存在，那么就是升级
			$execute_sql = $update_sql;
		}
	}else {
		//如果有安装的语句，那么就是安装
		$execute_sql = $install_sql;
	}
	//实例化数据库链接类 ---传入链接对象
	$m_mysql_backup_aide = new \Common\Aide\MysqlBackupAide($db);
	//更新或安装sql语句
	$res = $m_mysql_backup_aide->updateSql($execute_sql);
	if ($res){
		//如果导入成功后，删除sql文件
		if (unlink($execute_sql)){
			$return['info'] .= '安装sql语句文件删除失败。安装完成后，请手动删除根目录下的'.$execute_sql.'安装sql语句文件。<br>';
		}
	}else {
		$return['info'] = $m_mysql_backup_aide->error();
		$return['status'] = false;
		return $return;
	}
	//一切ok讲默认的标志结果返回出去
	return $return;
}


/**
 * ----------------------------------------------
 * | 创建超级管理员
 * | @时间: 2016年11月20日 上午10:51:41
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function born_super_admin($db, $table, $request, $return){
	//管理员信息
	$admin_username = $request['admin_username'];
	$admin_password = $request['admin_password'];
	$admin_conform_password = $request['admin_conform_password'];

	//如果链接数据库成功
	$users = $db->query('SELECT count(*) as count FROM '.$table);
	//如果管理员表有记录
	if ($users[0]['count'] >= 1){
		//清空管理员表
		if ($db->execute('TRUNCATE TABLE '.$table) === false){
			$return['info'] = '初始化管理员时失败。';
			$return['status'] = false;
			return $return;
		}
	}
	
	//生成超级管理员
    $sql = "INSERT INTO ".$table." (username,password,avatar,email,email_code,phone,status,register_time,last_login_ip,last_login_time) VALUES ('".$admin_username."', '".md5($admin_password.$request['auth_key'])."', '/Upload/avatar/user1.jpg', '', '', 0, 1, ".time().",'".get_client_ip(0)."',".time().")";
	$users_res = $db->execute($sql);
	if ( $users_res ===false){
		$return['info'] = '添加管理员时出错，请重新安装';
		$return['status'] = false;
		return $return;
	}
	$admin = [ 'username' => $admin_username, 'id' => $users_res];
	session('backend_admin',$admin);
	return $return;
}

/**
 * ----------------------------------------------
 * | 将相关信息写入到配置文件中
 * | @时间: 2016年11月20日 上午10:59:36
 * | @author: duanbin
 * | @param: $GLOBALS
 * | @return: type
 * ----------------------------------------------
 */
function put_contents_to_config($request, $return){
	$conf = file_get_contents(MODULE_PATH . 'Data/conf.tpl');
	$conf = str_replace('[DB_HOST]', $request['address'], $conf);
	$conf = str_replace('[DB_PORT]', $request['port'], $conf);
	$conf = str_replace('[DB_NAME]', $request['database_name'], $conf);
	$conf = str_replace('[DB_USER]', $request['database_username'], $conf);
	$conf = str_replace('[DB_PWD]', $request['database_password'], $conf);
	$conf = str_replace('[AUTH_KEY]', $request['auth_key'], $conf);
	
	//如果配置文件写入正常,返回原有标志$return
	if( file_put_contents(APP_PATH . 'Common/Conf/config.php', $conf) ){
		return $return;
	} else {
		$return['info'] = '配置文件写入失败，请重新安装';
		$return['status'] = false;
		return $return;
	}
	
}


