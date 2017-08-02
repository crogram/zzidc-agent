<?php
$session_path=RUNTIME_PATH . 'Session/';
if(!is_dir($session_path)){
    @mkdir($session_path,0755);
}
return [
	//'配置项'=>'配置值'
	// 禁止访问的模块列表
	'MODULE_DENY_LIST' => array('Common','Runtime','Install'),
	
	/*********全局hash的字符串，加密的salt，重要配置，不能删除和修改***********/
	'APP_KEY' => '[AUTH_KEY]',

	'TMPL_EXCEPTION_FILE'   => '/Public/error.html',

	'DEFAULT_MODULE' => 'Frontend',  // 默认模块
	'DEFAULT_CONTROLLER' => 'Index', // 默认控制器名称
	'DEFAULT_ACTION' => 'index', // 默认操作名称
	'DEFAULT_CHARSET' => 'utf-8', // 默认输出编码
	'DEFAULT_TIMEZONE' => 'PRC',  // 默认时区
	'DEFAULT_AJAX_RETURN' => 'JSON',  // 默认AJAX 数据返回格式,可选JSON XML ...
	'DEFAULT_JSONP_HANDLER' => 'jsonpReturn', // 默认JSONP格式返回的处理方法
	'DEFAULT_FILTER' => 'htmlspecialchars,strip_tags', // 默认参数过滤方法 用于I函数...
		
	'DATA_CACHE_TIME' => 0, // 数据缓存有效期 0表示永久缓存
	'DATA_CACHE_COMPRESS' => false, // 数据缓存是否压缩缓存
	'DATA_CACHE_CHECK' => false, // 数据缓存是否校验缓存
	'DATA_CACHE_PREFIX' => '', // 缓存前缀
	'DATA_CACHE_TYPE' => 'File', // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
	'DATA_CACHE_PATH' => TEMP_PATH, // 缓存路径设置 (仅对File方式缓存有效)
	'DATA_CACHE_SUBDIR' => false, // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
	'DATA_PATH_LEVEL' => 1, // 子目录缓存级别
	
	'URL_ROUTER_ON'   => true,	//开启路由	
	'URL_CASE_INSENSITIVE' => true, // 默认false 表示URL区分大小写 true则表示不区分大小写
	'URL_MODEL' => 2, // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
	// 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
		
	'URL_HTML_SUFFIX' => 'html',  // URL伪静态后缀设置
	'URL_DENY_SUFFIX' => 'ico|png|gif|jpg', // URL禁止访问的后缀设置	
	//默认全局主题
	'DEFAULT_THEME'    =>    'Default',
		
	/***************************自定义的配置项***************************/
	'FILE_MAX_SIZE' => 2,	//上传文件的大小限制，单位为MB
	'ALLOW_FILE_EXTS' => ['jpg', 'gif', 'png', 'jpeg','zip','rar','xls','xlsx','doc','docx','txt'],	//上传文件后缀限制，白名单
	'UPLOAD_ROOT_PATH' => './Uploads/',	//上传文件的根目录
	
	'UPDATE_URL' => 'http://download.api.zzidc.com/index.php',	//网站升级更新网址
	'UPDATE_PACKAGE_NAME' => 'GainetAgent????_update.zip',	//软件更新名称
	'UPDATE_PACKAGE_DOWNOAD_URL' => 'http://download.api.zzidc.com/files/',	//更新软件下载地址
	
	'VIRTUAL_HOST_SITE_URL' => 'https://ssp.zzidc.com/agentRemoteLogin.action',	//虚拟主机管理平台url
	'SSP_SITE_URL' => 'https://ssp.zzidc.com/iremotelogin.action',	//自助管理平台URL
	
	'BACKUP_PATH' => getcwd().DS.'Backup'.DS,	//系统更新升级时需要创建的备份文件夹目录
	'VERSION_PATH' => APP_PATH.'Common/Conf/version.lock',	//版本信息
	
	'AGENT_LEVEL' => [AGENT_LEVEL],	//级别
		
	/**
	 * ---------------------------------------------------
	 * |模板解析的常量配置
	 * |@时间: 2016年9月30日 下午2:25:47
	 * ---------------------------------------------------
	 */
	'TMPL_PARSE_STRING' => [
			//公共静态资源
			'__COM_CSS__'      => __ROOT__.PUBLIC_PATH.'common/css/',
			'__COM_JS__'       => __ROOT__.PUBLIC_PATH.'common/js/',
			'__COM_IMAGES__'   => __ROOT__.PUBLIC_PATH.'common/images/',
			'__COM_STATICS__' => __ROOT__.PUBLIC_PATH.'common/statics/',
			//后台静态资源
			'__BACKEND_CSS__'      => __ROOT__.PUBLIC_PATH.'backend/css/',
			'__BACKEND_JS__'       => __ROOT__.PUBLIC_PATH.'backend/js/',
			'__BACKEND_IMAGES__'   => __ROOT__.PUBLIC_PATH.'backend/images/',
			'__BACKEND_STATICS__' => __ROOT__.PUBLIC_PATH.'backend/statics/',
			'__BACKEND_ACEADMIN__' => __ROOT__.PUBLIC_PATH.'backend/statics/aceadmin/',
			//前台静态资源
			'__FRONTEND_CSS__'      => PUBLIC_PATH.'frontend/css/',
			'__FRONTEND_JS__'       => PUBLIC_PATH.'frontend/js/',
			'__FRONTEND_IMAGES__'   => PUBLIC_PATH.'frontend/images/',
			'__FRONTEND_STATICS__'  => PUBLIC_PATH.'frontend/statics/',
			'__FRONTEND_MEMBER__'  => PUBLIC_PATH.'frontend/member/',
			//微信静态资源
			'__WECHAT_CSS__'      => PUBLIC_PATH.'wechat/css/',
			'__WECHAT_JS__'       => PUBLIC_PATH.'wechat/js/',
			'__WECHAT_IMAGES__'   => PUBLIC_PATH.'wechat/images/',
			'__WECHAT_STATICS__'  => PUBLIC_PATH.'wechat/statics/',
			//api静态资源
			'__API_CSS__'      => PUBLIC_PATH.'api/css/',
			'__API_JS__'       => PUBLIC_PATH.'api/js/',
			'__API_IMAGES__'   => PUBLIC_PATH.'api/images/',
			'__API_STATICS__'  => PUBLIC_PATH.'api/statics/',
	],
		
		
		
	'DB_TYPE' => 'mysql',	// 数据库类型
	'DB_HOST' => '[DB_HOST]',	// 服务器地址
	'DB_NAME' => '[DB_NAME]',	// 数据库名
	'DB_USER' => '[DB_USER]',	// 用户名
	'DB_PWD' => '[DB_PWD]',	// 密码
	'DB_PORT' => '[DB_PORT]',	// 端口
	'DB_PREFIX' => 'agent_', // 数据库表前缀
	'DB_FIELDS_CACHE' => true,	// 启用字段缓存
	'DB_CHARSET' => 'utf8',	// 数据库编码默认采用utf8
	'DB_DEPLOY_TYPE' => 0,	// 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
	'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
		
		
		
		
		
		
		
		
		
];
