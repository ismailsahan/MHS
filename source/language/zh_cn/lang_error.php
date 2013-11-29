<?php

/**
 * 错误提示语言包（简体中文）
 */

$lang = array(
	'LIBRARY_FILE_LOAD_ERR'=>'无法加载必需组件 {str}！',
	'CONFIG_NONEXISTENT'=>'配置文件不存在！',
	'LIBRARY_FILE_NONEXISTENT'=>'必需组件 {str} 不存在！',

	'TEMPLATE_REPEATED'=>'Prohibit repeated reference template: {str}',
	'TEMPLATE_FILE_UNOPENABLE'=>'无法打开模板文件 {str}！',
	'CACHE_FILE_UNWRITABLE'=>'无法写入模板缓存文件 {str}！',
	'TEMPLATE_FILE_NONEXISTENT'=>'模板文件 {str} 不存在！',

	'System Message' => '站点信息',

	'REQUEST_TAINTING' => '非法的提交请求！',

	'db_error' => '<b>$message</b>$errorno<br />$info$sql',
	'db_error_message' => '<b>错误信息</b>: $dberror<br />',
	'db_error_sql' => '<b>SQL</b>: $sql<br />',
	'db_error_no' => ' [$dberrno]',
	'db_notfound_config' => '配置文件 "config_global.php" 未找到或者无法访问。',
	'db_notconnect' => '无法连接到数据库服务器',
	'db_query_error' => 'SQL语句错误！',
	'db_config_db_not_found' => '数据库配置错误，请仔细检查配置文件',
	'db_not_safe' => '系统检测到不安全的SQL语句！',//It is not safe to do this query
	'db_glue_not_allowed' => '',//Not allow this glue between field and value
	'db_format_error' => '',//SQL string format error! This SQL need $count vars to replace into.
	'system_init_ok' => '网站系统初始化完成，请<a href="index.php">点击这里</a>进入',

	'file_upload_error_-101' => '上传失败！上传文件不存在或不合法，请返回。',
	'file_upload_error_-102' => '上传失败！非图片类型文件，请返回。',
	'file_upload_error_-103' => '上传失败！无法写入文件或写入失败，请返回。',
	'file_upload_error_-104' => '上传失败！无法识别的图像文件格式，请返回。'
);