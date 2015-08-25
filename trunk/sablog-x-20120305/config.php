<?php

//数据库主机名或IP
$servername = 'localhost';

//数据库用户名
$dbusername = 'root';

//数据库密码
$dbpassword = '';

//数据库连接方式
$usepconnect = '0';

//数据库名
$dbname = 'sablog';

//数据表前缀
$db_prefix = 'sablog_';

//MySQL字符集
$dbcharset = '';

//系统默认字符集
$charset = 'utf-8';

//防护大量正常请求造成的拒绝服务攻击
$attackevasive = 0;
// 0=关闭, 1=cookie 刷新限制, 2=限制代理访问, 3=cookie+代理限制

//是否自动刷新模板缓存
$tplrefresh = 1;
//自动判断模板是否被修改而自动生成新的模板缓存，如果关闭自动刷新模板缓存功能，效率会有所提高，但是需要换模板的时候，要手动在模板管理中更新模板缓存。

// 是否允许在线编辑博客模板 1=是 0=否[安全]
$tpledit = 1;

// 是否允许后台恢复博客数据  1=是 0=否[安全]
$dbimport = 1;

//如您对 cookie 作用范围有特殊要求, 或论坛登录不正常, 请修改下面变量, 否则请保持默认

// cookie 前缀
$cookiepre = '';

// cookie 作用域
$cookiedomain = '';

// cookie 作用路径
$cookiepath = '/';

?>