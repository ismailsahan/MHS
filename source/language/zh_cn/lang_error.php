<?php

$lang = array(
	'LIBRARY_FILE_LOAD_ERR'=>'无法加载必需组件 {str}！',
	'CONFIG_NONEXISTENT'=>'配置文件不存在！',
	'LIBRARY_FILE_NONEXISTENT'=>'必需组件 {str} 不存在！',

	'System Message' => '站点信息',
	'error_unknow' => '未知错误',

	'REQUEST_TAINTING' => '非法的提交请求！',
	'config_notfound' => '配置文件未找到或者无法访问， 请确认您已经正确安装了程序',
	'template_notfound' => '模版文件未找到或者无法访问',
	'directory_notfound' => '目录未找到或者无法访问',
	'request_tainting' => '您当前的访问请求当中含有非法字符，已经被系统拒绝',

	'db_error' => '<b>$message</b>$errorno<br />$info$sql',
	'db_error_message' => '<b>错误信息</b>: $dberror<br />',
	'db_error_no' => ' [$dberrno]',
	//'db_query_error' => 'SQL语句错误！',
	'db_help_link' => '点击这里寻求帮助',
	//'db_error_message' => '错误信息',
	'db_error_sql' => '<b>SQL</b>: $sql<br />',
	'db_error_backtrace' => '<b>Backtrace</b>: $backtrace<br />',
	//'db_error_no' => '错误代码',
	'db_notfound_config' => '系统未能找到配置文件或者无法访问',
	'db_notconnect' => '无法连接到数据库服务器',
	'db_security_error' => '查询语句安全威胁',
	'db_query_sql' => '查询语句',
	'db_query_error' => '查询语句错误',
	'db_config_db_not_found' => '数据库配置错误，请仔细检查配置文件',
	'db_not_safe' => '系统检测到查询语句安全威胁，已拒绝访问',//It is not safe to do this query
	'db_glue_not_allowed' => '',//Not allow this glue between field and value
	'db_format_error' => '',//SQL string format error! This SQL need $count vars to replace into.
	'system_init_ok' => '网站系统初始化完成，请<a href="index.php">点击这里</a>进入',
	'backtrace' => '运行信息',
	'error_end_message' => '<a href="http://{host}">{host}</a> 已经将此出错信息详细记录, 由此给您带来的访问不便我们深感歉意',
	'mobile_error_end_message' => '<a href="http://{host}">{host}</a> 此错误给您带来的不便我们深感歉意',

	'method_undefined' => '{action} 的操作方法 {operation} 未定义！',
	'default_method_undefined' => '未定义 {action} 的默认操作方法！',

	'file_upload_error_-101' => '上传失败！上传文件不存在或不合法，请返回。',
	'file_upload_error_-102' => '上传失败！非图片类型文件，请返回。',
	'file_upload_error_-103' => '上传失败！无法写入文件或写入失败，请返回。',
	'file_upload_error_-104' => '上传失败！无法识别的图像文件格式，请返回。',

	'send_mail_failed' => '邮件发送失败！原因：{msg}'
);