<?php
return [
		//这是默认的模板主题
		//'DEFAULT_THEME' => 'Default',
        'SESSION_PREFIX'  => 'Frontend',
         'SHOW_PAGE_TRACE' => false,
        //开启缓存文件
        'HTML_CACHE_ON'     =>    true, // 开启静态缓存
        'HTML_CACHE_TIME'   =>    60,   // 全局静态缓存有效期（秒）
        'HTML_FILE_SUFFIX'  =>    '.html', // 设置静态缓存文件后缀
        'HTML_CACHE_RULES'  =>     array(  // 定义静态缓存规则
            // 定义格式1 数组方式
            'Product:virtualhost' => array('{:action}_{type}',0),
            'Product:vps'=>array('{:action}_{type}',0),
            'Clouddb:clouddbShow'=>array('{:action}',0),
            // 定义格式2 字符串方式
            // '静态地址'    =>     '静态规则',
        ),
];