<?php	

/**
 * 景安API接口 for PHP SDK v1.0.0
 */
class GiantAPIParams {

/*******************ptype参数*****************************/
	//景安API接口ptype参数 云主机常量
	const PTYPE_CLOUD_HOST = 'cloudhost';

	//景安API接口ptype参数 vps常量
	const PTYPE_VPS ='vps';
	
	//景安API接口ptype 国内虚拟主机常量
	const PTYPE_HOST='host';
	
	//景安API接口ptype 香港虚拟主机常量
	const PTYPE_HK_HOST='hkhost';
	//域名常量
	const PTYPE_DOMAIN="domain";
	//景安API接口ptype 英文域名常量
	const PTYPE_EN_DOMAIN='endomain';
	//景安API接口ptype 中文域名常量
	const PTYPE_CN_DOMAIN='cndomain';
	//托管租用常量
	const PTYPE_MANAGE_HIRE="managehire";
	//云空间 常量
	const PTYPE_CLOUD_SPACE='cloudspace';
	//云虚拟主机 常量
	const PTYPE_CLOUD_VIRTUAL='cloudVirtual';
	//快云服务器 常量
	const PTYPE_CLOUD_SERVER="cloudserver";
	//SSL 常量
	const PTYPE_SSL="ssl";
	

	const PTYPE_SELF="self";

	//快云VPS
	const PTYPE_FAST_CLOUDVPS ='fastcloudvps';

	//景安API接口ptype 织梦虚拟主机常量
	const PTYPE_DEDE_HOST='dedehost';
	//美国主机
	const  PTYPE_USA_HOST="usahost";
	
/************************************************pname参数******************************************/
	 /***************************托管租用pname****************************/
	//普及型服务器
	const PNAME_SERVER_PUJI="servier.I";
	//经典型服务器
	const PNAME_SERVER_JINGDIAN="servier.II";
	//豪华型服务器
	const PNAME_SERVER_HAOHUA="servier.III";
	//至尊型服务器
	const PNAME_SERVER_ZHIZUN="servier.IV";
    /***************************云主机购买增值业务****************************/
	//景安API接口pname参数 祥云Ⅰ型常量
	const PNAME_XIANGYUN_YI = 'xiangyun.yi';

	//景安API接口pname参数 祥云Ⅱ型常量
	const PNAME_XIANGYUN_ER = 'xiangyun.er';
	
	//景安API接口pname参数 增值内存常量
	const PNAME_ZENGZHI_NEICUN = 'zengzhi.neicun';

	//景安API接口pname参数 增值硬盘常量
	const PNAME_ZENGZHI_YINGPAN = 'zengzhi.yingpan';

	//景安API接口pname参数 增值ip常量
	const PNAME_ZENGZHI_IP = 'zengzhi.ip';
	
	//景安API接口pname参数 增值cpu常量
	const PNAME_ZENGZHI_CPU= 'zengzhi.cpu';
	
	/***************************vps购买增值业务******************************/
    //景安API接口pname参数 特卖VPS  VMWare普及型常量
	const PNAME_TEMAI_VMWARE_PUJI='temai.vmware.puji';
	
	 //景安API接口pname参数 特卖VPS  VMWare经济型常量
	const PNAME_TEMAI_VMWARE_JINGJI='temai.vmware.jingji';
	
	//景安API接口pname参数 特卖VPS  VMWare豪华型常量
	const PNAME_TEMAI_VMWARE_HAOHUA='temai.vmware.haohua';
	
	//景安API接口pname参数 香港VPS  VMWare经济型常量
	const PNAME_HK_VMWARE_JINGJI='hk.vmware.jingji';
	
	 //景安API接口pname参数 香港VPS  VMWare豪华型常量
	const PNAME_HK_VMWARE_HAOHUA='hk.vmware.haohua';
	
	//景安API接口pname参数 香港VPS  VMWare至尊型常量
	const PNAME_HK_VMWARE_ZHIZUN='hk.vmware.zhizun';
	
	//景安API接口参数 特卖VPS 增值硬盘常量
	const PNAME_VPS_ZENGZHI_YINGPAN='temai.zengzhi.yingpan';
	
	//景安API接口参数 特卖VPS 增值IP常量
	const PNAME_VPS_ZENGZHI_IP='temai.zengzhi.ip';
	
	//景安API接口参数 特卖VPS 增值内存常量
	const PNAME_VPS_ZENGZHI_NEICUN='temai.zengzhi.neicun';
	
	/****************************虚拟主机购买增值管理业务************************/
	
	//景安API接口参数 商务型I型(windows) 常量
	const PNAME_HOST_SHANGWU_WINDOWS_I='host.shangwu.windows.I';
	
	//景安API接口参数 商务型II型(windows) 常量
	const PNAME_HOST_SHANGWU_WINDOWS_II='host.shangwu.windows.II';
    
	//景安API接口参数 商务型III型(windows) 常量
	const PNAME_HOST_SHANGWU_WINDOWS_III='host.shangwu.windows.III';
	
	//景安API接口参数 商务型IV型(windows) 常量
	const PNAME_HOST_SHANGWU_WINDOWS_IV='host.shangwu.windows.IV';

	//景安API接口参数 商务型I型(Linux) 常量	
	const PNAME_HOST_SHANGWU_LINUX_I='host.shangwu.linux.I';

	//景安API接口参数 商务型II型(Linux) 常量	
	const PNAME_HOST_SHANGWU_LINUX_II='host.shangwu.linux.II';

	//景安API接口参数 商务型III型(Linux) 常量	
	const PNAME_HOST_SHANGWU_LINUX_III='host.shangwu.linux.III';

	//景安API接口参数 商务型IV型(Linux) 常量	
	const PNAME_HOST_SHANGWU_LINUX_IV='host.shangwu.linux.IV';

	//景安API接口参数 论坛I型(windows) 常量
	const PNAME_HOST_LUNTAN_WINDOWS_I='host.luntan.windows.I';
	
	//景安API接口参数 论坛II型(windows) 常量
	const PNAME_HOST_LUNTAN_WINDOWS_II='host.luntan.windows.II';
    
	//景安API接口参数 论坛III型(windows) 常量
	const PNAME_HOST_LUNTAN_WINDOWS_III='host.luntan.windows.III';
	
	//景安API接口参数 论坛IV型(windows) 常量
	const PNAME_HOST_LUNTAN_WINDOWS_IV='host.luntan.windows.IV';

	//景安API接口参数 论坛I型(Linux) 常量
	const PNAME_HOST_LUNTAN_LINUX_I='host.luntan.linux.I';
	
	//景安API接口参数 论坛II型(Linux) 常量
	const PNAME_HOST_LUNTAN_LINUX_II='host.luntan.linux.II';
    
	//景安API接口参数 论坛III型(Linux) 常量
	const PNAME_HOST_LUNTAN_LINUX_III='host.luntan.linux.III';
	
	//景安API接口参数 论坛IV型(Linux) 常量
	const PNAME_HOST_LUNTAN_LINUX_IV='host.luntan.linux.IV';

	//景安API接口参数 门户I型(windows) 常量
	const PNAME_HOST_MENHU_WINDOWS_I='host.menhu.windows.I';
	
	//景安API接口参数 门户II型(windows) 常量
	const PNAME_HOST_MENHU_WINDOWS_II='host.menhu.windows.II';
    
	//景安API接口参数 门户III型(windows) 常量
	const PNAME_HOST_MENHU_WINDOWS_III='host.menhu.windows.III';
	
	//景安API接口参数 门户IV型(windows) 常量
	const PNAME_HOST_MENHU_WINDOWS_IV='host.menhu.windows.IV';

   //景安API接口参数 门户I型(Linux) 常量
	const PNAME_HOST_MENHU_LINUX_I='host.menhu.linux.I';
	
	//景安API接口参数 门户II型(Linux) 常量
	const PNAME_HOST_MENHU_LINUX_II='host.menhu.linux.II';
    
	//景安API接口参数 门户III型(Linux) 常量
	const PNAME_HOST_MENHU_LINUX_III='host.menhu.linux.III';
	
	//景安API接口参数 门户IV型(Linux) 常量
	const PNAME_HOST_MENHU_LINUX_IV='host.menhu.linux.IV';

	//景安API接口参数 PHP香港I型(Linux) 常量
	const PNAME_HKHOST_PHP_LINUX_I='hkhost.php.linux.I';
	
	//景安API接口参数 PHP香港II型(Linux) 常量
	const PNAME_HKHOST_PHP_LINUX_II='hkhost.php.linux.II';
    
	//景安API接口参数 PHP香港III型(Linux) 常量
	const PNAME_HKHOST_PHP_LINUX_III='hkhost.php.linux.III';
	
	//景安API接口参数 PHP香港V型(Linux)  常量
	const PNAME_HKHOST_PHP_LINUX_V='hkhost.php.linux.V';

	//景安API接口参数 PHP香港G型(Linux)  常量
	const PNAME_HKHOST_PHP_LINUX_G='hkhost.php.linux.G';

    //景安API接口参数 ASP.NET香港I型(windows) 常量
	const PNAME_HKHOST_ASP_WINDOWS_I='hkhost.asp.windows.I';
	
	//景安API接口参数 ASP.NET香港II型(windows) 常量
	const PNAME_HKHOST_ASP_WINDOWS_II='hkhost.asp.windows.II';
    
	//景安API接口参数 ASP.NET香港III型(windows) 常量
	const PNAME_HKHOST_ASP_WINDOWS_III='hkhost.asp.windows.III';
	
	//景安API接口参数 ASP.NET香港V型(windows)  常量
	const PNAME_HKHOST_ASP_LINUX_V='hkhost.asp.windows.V';

	//景安API接口参数 ASP.NET香港G型(windows)  常量
	const PNAME_HKHOST_ASP_LINUX_G='hkhost.asp.windows.G';

	//景安API接口参数 国内织梦主机I型  常量
	const PNAME_DEDEHOST_CN_I='dede.cn.host.I';
	
	//景安API接口参数 国内织梦主机II型  常量
	const PNAME_DEDEHOST_CN_II='dede.cn.host.II';
	
	//景安API接口参数 国内织梦主机III型  常量
	const PNAME_DEDEHOST_CN_III='dede.cn.host.III';
	
	//景安API接口参数 国内织梦主机G型  常量
	const PNAME_DEDEHOST_CN_G='dede.cn.host.G';
	
	//景安API接口参数 香港织梦主机I型  常量
	const PNAME_DEDEHOST_HK_I='dede.hk.host.I';
	
	//景安API接口参数 香港织梦主机II型  常量
	const PNAME_DEDEHOST_HK_II='dede.hk.host.II';
	
	//景安API接口参数 香港织梦主机III型  常量
	const PNAME_DEDEHOST_HK_III='dede.hk.host.III';
	
	//景安API接口参数 香港织梦主机G型  常量
	const PNAME_DEDEHOST_HK_G='dede.hk.host.G';
	
    //景安API接口参数 增加linux空间 常量
	const PNAME_HOST_ZENGZHI_KONGJIAN_LINUX ='zengzhi.kongjian.linux';

	//景安API接口参数 增加windows空间 常量
	const PNAME_HOST_ZENGZHI_KONGJIAN_WINDOWS ='zengzhi.kongjian.windows';

    //景安API接口参数 增加linux数据库个数 常量
    const PNAME_HOST_DATA_LINUX = 'zengzhi.shujuku.linux';

	//景安API接口参数 增加windows数据库个数 常量
	const PNAME_HOST_DATA_WINDOWS = "zengzhi.shujuku.windows";

    //景安API接口参数 增加linux数据库空间 常量
	const PNAME_HOST_DATAKONGJIAN_LINUX = 'zengzhi.shujukukongjian.linux';
	
	//景安API接口参数 增加linuxIP 常量
	const PNAME_CLOUDVIRTUAL_LINUX_IP = "zengzhi.ip.linux";
	
	//景安API接口参数 增加windowsIP 常量
	const PNAME_CLOUDVIRTUAL_WINDOWS_IP = "zengzhi.ip.windows";
	
	//景安API接口参数 增加linux流量 常量
	const PNAME_CLOUDVIRTUAL_LIULINAG = "zengzhi.liuliang.linux";
	
	//景安API接口参数 增加linux空间 常量
	const PNAME_CLOUDVIRTUAL_ZENGZHI_KONGJIAN_LINUX = "zengzhi.kongjian.linux";
	
	//景安API接口参数 增加windows空间 常量
	const PNAME_CLOUDVIRTUAL_ZENGZHI_KONGJIAN_WINDOWS = "zengzhi.kongjian.windows";
	
	//景安API接口参数 增加linux数据库 常量
	const PNAME_CLOUDVIRTUAL_DATA_LINUX = "zengzhi.shujuku.linux";
	
	//景安API接口参数 增加windows数据库 常量
	const PNAME_CLOUDVIRTUAL_DATA_WINDOWS = "zengzhi.shujuku.windows";
	
	//景安API接口参数 增加linux数据库空间 常量
	const PNAME_CLOUDVIRTUAL_DATAKONGJIAN_LINUX = "zengzhi.shujukukongjian.linux";
	
	//景安API接口参数 增加windows数据库空间 常量
	const PNAME_CLOUDVIRTUAL_DATAKONGJIAN_WINDOWS = "zengzhi.shujukukongjian.windows";
	

    //景安API接口参数 增加windows数据库空间 常量
	const PNAME_HOST_DATAKONGJIAN_WINDOWS = 'zengzhi.shujukukongjian.windows';

	/*******************************站点管理*********************************/

    //景安API接口参数 增加邮箱 常量
	const PNAME_HOST_YOUXIANG = 'zengzhi.youxiang';

	//景安API接口参数 增加邮箱空间 常量
	const PNAME_HOST_YOUXIANGKONGJIAN = 'zengzhi.youxiangkongjian';

    //景安API接口参数 开通数据库 常量
	const PNAME_HOST_OPEN_DATEBASE = 'open.database';

	//景安API接口参数 开通邮局 常量
	const PNAME_HOST_OPEN_POSTOFFICE = 'open.postoffice';

	//景安API接口参数 站点设置首页 常量
	const PNAME_DEFAULT_PAGE = 'defaultpage';

	//景安API接口参数 站点设置首页 常量
	const PNAME_DEL_DEFAULT_PAGE = 'deldefaultpage';

	//景安API接口参数 修改FTP密码 常量
	const PNAME_CHANGE_FTP_PASS = 'changeftppass';

	//景安API接口参数 站点添加错误页面 常量
	const PNAME_ERROR_PAGE = 'errpage';

	//景安API接口参数 关闭开启站点 常量
	const PNAME_SITE_CLOSE = 'changesite';

	//景安API接口参数 绑定域名 常量
	const PNAME_BINDING_DOMAIN = 'bindingdomain';

	//景安API接口参数 删除绑定域名 常量
	const PNAME_REMOVE_DOMAIN = 'removedomain';

	//景安API接口参数 创建站点 常量
	const PNAME_CREATE_SITE="createsite";

	//景安API接口参数 关闭开启站点 常量
	const PNAME_CHANGE_SITE="changesite";

	//景安API接口参数 创建数据库 常量
	const PNAME_CREATE_DB="createdb";
	
	//景安API接口参数 开启关闭数据库 常量
	const PNAME_CHANGE_DB="changedb";

	//景安API接口参数 数据库扩容 常量
	const PNAME_EXTEND_DB="extenddb";

	/********************************云空间常量************************/

	//景安API接口参数 Win云空间A型 常量
	const PNAME_CLOUD_SPACE_WINDOWS_A="cloudspace.windows.a";

	//景安API接口参数 Win云空间B型 常量
	const PNAME_CLOUD_SPACE_WINDOWS_B="cloudspace.windows.b";

	//景安API接口参数 Win云空间C型 常量
	const PNAME_CLOUD_SPACE_WINDOWS_C="cloudspace.windows.c";

	//景安API接口参数 Linux云空间A型 常量
	const PNAME_CLOUD_SPACE_LINUX_A="cloudspace.linux.a";

	//景安API接口参数 Linux云空间B型 常量
	const PNAME_CLOUD_SPACE_LINUX_B="cloudspace.linux.b";

	//景安API接口参数 Linux云空间C型 常量
	const PNAME_CLOUD_SPACE_LINUX_C="cloudspace.linux.c";

	//景安API接口参数 云空间容量增值 常量
	const PNAME_CLOUD_SPACE_ZENG_ZHI_KONG_JIAN="zengzhi.kongjian";

	//景安API接口参数 云空间增加数据库个数 常量
	const PNAME_CLOUD_SPACE_ZENG_ZHI_DATA_QUANTITY="zengzhi.data.quantity";

	//景安API接口参数 云空间增加数据库空间 常量
	const PNAME_CLOUD_SPACE_ZENG_ZHI_DATA_CAPACITY="zengzhi.data.capacity";

	//景安API接口参数 云空间站点数量增值 常量
	const PNAME_CLOUD_SPACE_ZENG_ZHI_ZHAN_DIAN="zengzhi.zhandian";

	//景安API接口参数 云空间增值流量 常量
	const PNAME_CLOUD_SPACE_ZENG_ZHI_LIU_LIANG="zengzhi.liuliang";

	/********************************快云VPS常量************************/

	//景安API接口参数 Win云空间A型 常量
	const PNAME_FAST_CLOUDVPS_PUJI="fast.cloudvps.puji";

	//景安API接口参数 Win云空间B型 常量
	const PNAME_FAST_CLOUDVPS_JINGJI="fast.cloudvps.jingji";

	//景安API接口参数 Win云空间C型 常量
	const PNAME_FAST_CLOUDVPS_HAOHUA="fast.cloudvps.haohua";

	//景安API接口参数 Linux云空间A型 常量
	const PNAME_FAST_CLOUDVPS_QIJIAN="fast.cloudvps.qijian";

	//景安API接口参数 Linux云空间B型 常量
	const PNAME_FAST_CLOUDVPS_ZHIZUN="fast.cloudvps.zhizun";

	//景安API接口参数 特卖VPS 增值硬盘常量
	const PNAME_FAST_CLOUDVPS_ZENGZHI_YINGPAN='zengzhi.yingpan';
	
	//景安API接口参数 特卖VPS 增值IP常量
	const PNAME_FAST_CLOUDVPS_ZENGZHI_IP='zengzhi.ip';
	
	//景安API接口参数 特卖VPS 增值内存常量
	const PNAME_FAST_CLOUDVPS_ZENGZHI_NEICUN='zengzhi.neicun';
	
	
	/*******************************美国主机常量*********************************/
	//景安API接口参数 美国主机linuxI型 常量
	const PNAME_USAHOST_LINUX_I='usa.host.linux.I';
	//景安API接口参数 美国主机linuxII型 常量
	const PNAME_USAHOST_LINUX_II='usa.host.linux.II';
	//景安API接口参数 美国主机linuxIII型 常量
	const PNAME_USAHOST_LINUX_III='usa.host.linux.III';
	//景安API接口参数 美国主机linuxV型 常量
	const PNAME_USAHOST_LINUX_V='usa.host.linux.V';
	//景安API接口参数 美国主机linuxG型 常量
	const PNAME_USAHOST_LINUX_G='usa.host.linux.G';
	//景安API接口参数 美国主机windowsI型 常量
	const PNAME_USAHOST_WINDOWS_I='usa.host.windows.I';
	//景安API接口参数 美国主机windowsII型 常量
	const PNAME_USAHOST_WINDOWS_II='usa.host.windows.II';
	//景安API接口参数 美国主机windowsIII型 常量
	const PNAME_USAHOST_WINDOWS_III='usa.host.windows.III';
	//景安API接口参数 美国主机windowsV型 常量
	const PNAME_USAHOST_WINDOWS_V='usa.host.windows.V';
	//景安API接口参数 美国主机windowsG型 常量
	const PNAME_USAHOST_WINDOWS_G='usa.host.windows.G';
	//景安API接口参数 美国主机增值空间linux
	const PNAME_USAHOST_ZENG_ZHI_KONG_JIAN_LINUX="zengzhi.kongjian.linux";
	//景安API接口参数 美国主机增值空间windows
	const PNAME_USAHOST_ZENG_ZHI_KONG_JIAN_WINDOWS="zengzhi.kongjian.windows";
	//景安API接口参数 美国主机增值数据库windows
	const PNAME_USAHOST_ZENG_ZHI_SHUJUKU_WINDOWS="zengzhi.shujuku.windows";
	//景安API接口参数 美国主机增值数据库linux
	const PNAME_USAHOST_ZENG_ZHI_SHUJUKU_LINUX="zengzhi.shujuku.linux";
	//景安API接口参数 美国主机增值数据库空间linux
	const PNAME_USAHOST_ZENG_ZHI_SHUJUKU_KONGJIAN_LINUX="zengzhi.shujukukongjian.linux";
	//景安API接口参数 美国主机增值数据库空间windows
	const PNAME_USAHOST_ZENG_ZHI_SHUJUKU_KONGJIAN_WINDOWS="zengzhi.shujukukongjian.windows";

	
	/**********************************域名业务参数***********************/
	
	//景安API接口参数 域名注册 常量
	const PNAME_DOMAIN_REGISTRATION = 'domainregistration';

	//景安API接口参数 域名查询 常量
	const PNAME_DOMAIN_QUERY = 'domainquery';

	//景安API接口参数 域名续费 常量
	const PNAME_DOMAIN_RENEWALS = 'domainrenewals';
	
	//景安API接口参数 域名跳转 常量
	const PNAME_DOMAIN_SKIP = 'domainskip';
	
	//景安API接口参数 获取用户域名列表 常量
	const PNAME_DOMAIN_LIST='getdomainlist';

	/*******************tid参数*****************************/

	//景安API接口tid参数 购买tid常量
	const TID_BUY = 1;

	//景安API接口tid参数 开通购买订单tid常量
	const TID_OPEN = 2;

	//景安API接口tid参数 续费业务tid常量
	const TID_RENEWALS = 3;

	//景安API接口tid参数 升级业务tid常量
	const TID_UPGRADE = 4;

	//景安API接口tid参数 获取业务信息tid常量
	const TID_GET_BUSINESS_INFO = 5;

	//景安API接口tid参数 重启tid常量(只支持云VPS)
	const TID_RESTART = 6;

	//景安API接口tid参数 通过订单ID获取业务ID tid常量
	const TID_GET_BUSINESS_ID_ORDER_ID = 7;

	//景安API接口tid参数 增值交易tid常量(默认为增值时间为该业务剩余的到期时间)
	const TID_APPRECIATION = 8;

	//景安API接口tid参数 获取增值信息tid常量
	const TID_GET_APPRECIATION_INFO = 9;

	//景安API接口tid参数 申请免费试用tid常量
	const TID_TRY = 10;

	//景安API接口tid参数 转正tid常量
	const TID_POSITIVE = 11;
	
	//景安API接口tid参数 附加业务常量
	const TID_ADDITIONAL = 12;
	
	//景安API接口tid参数 站点管理常量
	const TID_SITE_MANAGMENT=13;
	
	//景安API接口tid参数  域名业务常量
	const TID_DOMAIN=15;

	const TID_GET_SERVICE_CODE_ID = 17;
	
	//同步信息
	const TID_SYNC_BUSINESS_INFO=18;
/*******************yid参数*****************************/

	//景安API接口yid参数 购买或者续费半年(6个月)常量
	const YID_SIX = 6;

	//景安API接口yid参数 购买或者续费1年(12个月)常量
	const YID_TWELVE = 12;

	//景安API接口参数 购买或者续费2年(24个月)
	const YID_TWO_YEAR = 24;

	//景安API接口参数 购买或者续费3年(36个月)
	const YID_THREE_YEAR = 36;

	//景安API接口参数 购买或者续费4年(48个月)
	const YID_FOUR_YEAR = 48;

	//景安API接口参数 购买或者续费5年(60个月)
	const YID_FIVE_YEAR = 60;

	//景安API接口参数 购买或者续费6年(72个月)
	const YID_SIX_YEAR = 72;
	//景安API接口参数 购买或者续费7年(84个月)
	const YID_SEVEB_YEAR = 84;
	
	//景安API接口参数 购买或者续费8年(96个月)
	const YID_EIGHT_YEAR = 96;
	
	//景安API接口参数 购买或者续费9年(108个月)
	const YID_NINE_YEAR = 108;

	//景安API接口参数 购买或者续费10年(120个月)
	const YID_TEN_YEAR = 120;
}