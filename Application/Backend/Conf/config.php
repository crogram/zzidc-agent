<?php
return array(
	//'配置项'=>'配置值'
	
		//权限认证配置
		'AUTH_CONFIG'=>array(
				'AUTH_ON' => true, //认证开关
				'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
				'AUTH_GROUP' => 'agent_auth_group', //用户组数据表名
				'AUTH_GROUP_ACCESS' => 'agent_auth_group_access', //用户组明细表
				'AUTH_RULE' => 'agent_auth_rule', //权限规则表
				'AUTH_USER' => 'agent_users'//用户信息表
		),

		//后台session前缀
		'SESSION_PREFIX' => 'backend_',
		//后台管理员入session的key
		'SESSION_ADMIN_KEY' => 'admin',
		
		//'DB_PREFIX' => 'agent_',	// 数据库表前缀
		
		//排序参数前缀
		'SORT_PREFIX' => 'sort_',
		
		'SHOW_PAGE_TRACE' => false,
		
		//登录cookie过期时间
		'BACKEND_LOGIN_COOKIE_EXPIRE' => 7*24,   //小时

);