# zzidc-agent
zzidc agent site

# Usage

## 代理平台环境部署方案
1. 代理平台需要PHP、MySQL、IIS\Apache Web服务器。
2. 代理平台对于Web服务器配置PHP的运行方式无要求。ISAPI或者FastCgi都可以。
3. 代理平台推荐配置为：
   - [x] PHP版本：5.4.X版本
   - [x] MySQL版本：5.5.X版本
   - [x] Web服务器只要能运行PHP都均可（nginx服务器暂时不支持伪静态）。
4. 代理平台需要的PHP配置（PHP.ini）:
  - php_gd2 验证码生成
  - php_mbstring 域名接口字符串编码
  - php_mysql 数据库连接
  - php_curl 模块
  - PHP的SESSION环境。需要配置PHP的SESSION临时保存目录。
  - PHP的上传图片临时目录配置。
5. 代理平台的目录权限设置:
  - Application、ThinkPHP为代理平台的核心文件目录。需要读、写权限。
  - Public为代理平台的CSS、JS、图片目录。需要读、写权限。
  - Uploads为文件上传目录。需要读、写权限。
  - 需要代理平台当前目录的上一级目录的读权限。比如当前目录是B。那么就需要B的上级目录A的读权限。
6. 代理平台需要外网访问。调用景安API接口和域名查询接口。
7. Application\Common\Conf\db_config.php配置文件详解。
  - DB_TYPE 数据库类型。
  - DB_HOST 服务器地址。
  - DB_NAME 数据库名。
  - DB_USER 数据库用户名。
  - DB_PWD 数据库密码。
  - DB_PORT 数据库端口。
  - DB_PREFIX 数据库表前缀。
  - DB_FIELDS_CACHE 启用字段缓存。
  - DB_CHARSET 数据库编码。
  - DB_DEPLOY_TYPE 数据库部署方式：0 集中式（单一服务器）1 分布式（主从服务器）
  - DB_DEBUG 数据库调试模式
## 常见问题：
1. 首页无数据问题？
   请查看数据库是否导入成功，数据库表是38张表，请查看是否导入完全。
   如果导入失败，请查看MYSQL版本是否满足5.5.X以上版本。
2. 购买、试用产品失败问题？
   请查看代理平台后台的网站设置->网站详情中Access Id、Acces Key是否填写正确，可以从景安主站 [https://www.zzidc.com](https://www.zzidc.com) 的会员中心->API接口查看。
   如果填写正确，请查看您当前的服务器是否能正常访问 [http://api.zzidc.com](http://api.zzidc.com)
3. 只能打开首页，其他页面显示404找不到网页问题？
   请查看您当前的web服务是否开启伪静态组件功能。
   apache请加载代理平台根目录下的.htaccess文件。
   IIS6加载根目录下的httpd.ini文件。
   IIS7加载根目录下的web.config文件。
   Nginx 修改根目录下nginx_rewrite_for_thinkphp3.2.3.example中
   ```nginx
   server {
       listen 80;
       server_name www.yourdomainname.com #将这一项该为你自己的域名
       root "your/app/root/dir/"; #将这一项改为你的项目根目录
       location / {
           index index.html index.htm index.php;
           #autoindex  on;
           if (!-e $request_filename) {
              rewrite  ^(.*)$  /index.php$1  last;
              break;
           }
       }
       location ~ \.php(.*)$ {
           fastcgi_pass   127.0.0.1:9000;
           fastcgi_index  index.php;
           fastcgi_split_path_info  ^((?U).+\.php)(/?.+)$;
           fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
           fastcgi_param  PATH_INFO  $fastcgi_path_info;
           fastcgi_param  PATH_TRANSLATED  $document_root$fastcgi_path_info;
           include        fastcgi_params;
       }
   }
   ```
   然后重命名 nginx_rewrite_for_thinkphp3.2.3.example 为 gainet.conf
   将重命名后的 gainet.conf 文件移至与nginx.conf 文件同级目录下
   然后将 nginx.conf 打开，在 http 配置块内 写入以下内容 
   ```nginx
   includegainet_agent.conf; #注意这里有分号
   ```
   打开php.ini 中cgi.fix_pathinfo= 1;前面“;”删除
   配置完成后删除根目录下的gainet.conf 文件和nginx_rewrite_for_thinkphp3.2.3.example
# 如上面问题都不符合，请联系商务或渠道人员，然后由技术人员进行错误原因的判断。