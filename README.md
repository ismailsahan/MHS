说明和文档
===
- 建议使用 XAMPP 搭建本地 PHP+MySQL 环境，并使用 Sublime Text 开发。这两套软件可在群共享里下载到
- UCenter 需要自行安装，并手动在后台添加应用，修改 config.inc.php 的 UCenter 部分的配置信息
- 开发期间，需要你自己使用 mklink 命令建立 ./source/template/metronic/assets 到 ./assets 的符号链接，否则网页不能正常显示，这个 BUG 将在正式部署时修复
 - 注意：mklink 命令只能在 Win7 及以上版本的操作系统中使用。若没有 mklink 命令，请自行复制 ./source/template/metronic/assets 文件夹到项目根目录
 - 命令：mklink /j "./assets的绝对路径" "./source/template/metronic/assets的绝对路径"
 - 示例：mklink /j "D:\wwwroot\MHS\assets" "D:\wwwroot\MHS\source\template\metronic\assets"

第三方组件
--------
- UCenter 用户中心
 - 需另外下载安装，UTF-8编码，压缩包里已附带使用文档和接口手册
 - http://download.comsenz.com/UCenter/1.6.0/UCenter_1.6.0_SC_UTF8.zip
- phpFastcache 缓存引擎
 - http://www.phpfastcache.com/
- Smarty 模板引擎
 - http://www.smarty.net/docs/zh_CN/
- PHPnew 模板引擎（已废弃，请查看 Smarty 的相关文档）
 - http://phpnew.fenanr.com/

目录结构
===
- ./api 接口
- ./cache 缓存
 - ./cache/tpl Smarty模板引擎编译缓存
 - ./cache/cfg Smarty模板引擎配置缓存
- ./source 项目源码
 - ./source/action 动作（模块）
 - ./source/class 类库
 - ./source/function 函数库
 - ./source/include 其他组件
 - ./source/language 语言包
 - ./source/plugin 插件
 - ./source/template 模板
 - ./source/vendor 第三方组件或类库
- ./uc_client UCenter客户端

发布笔记
===
### 0.2.0 - 2013-12-08 21:49
- 增补若干函数
- 部分 BUG 修正
- 更新 Metronic 模板
- 更换模板引擎为 Smarty
- 添加面向对象编程的模式支持
- 引入 UCenter
- 优化静态文件解释组件

### 0.1.0 - 2013-11-30 18:00

===
先这么写吧，日后修改
