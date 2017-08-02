<?php
/**
 * 安装程序配置文件
 */

define('INSTALL_APP_PATH', realpath('./') . '/');

define('CONFIG_PATH', './Application'.DS.'Common'.DS.'Conf');

return array(
    
    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 3, //URL模式 使用兼容模式安装
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符
    
	'DEFAULT_FILTER'        => 'strip_tags,htmlspecialchars',
	
	'UPDATE_SQL_NAME' => 'compatible.sql',	//安装软件是  的sql更新文件的名字，用来区别是升级安装还是新安装
	'INSTALL_SQL_NAME' => 'install.sql',		//全新安装的语句
);