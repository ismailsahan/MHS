<?php

/**
 * 返回默认侧边导航栏列表
 */
function defaultNav(){
	return array(
		array(
			'title' => 'home',
			'link' => 'main/index',
			'icon' => 'home',
			'tag' => array(),
			'children' => array()
		),
		array(
			'title' => 'manhour',
			'link' => 'manhour/index',
			'icon' => 'leaf',
			'children' => array()
		),
		array(
			'title' => '个人档案',
			'link' => 'profile/index',
			'icon' => 'user',
			'children' => array()
		),
	);
}

/**
 * 返回管理的侧边导航栏列表
 */
function adminNav(){
	return array(
		array(
			'title' => 'global',
			'link' => '',
			'icon' => 'cogs',
			'access' => 1,
			'children' => array(
				array(
					'title' => 'site_info',
					'link' => 'global/info',
					'icon' => '',
					'tag' => array(
						'type' => 'success',
						'label' => 'new'
					),
					'children' => array()
				),
				array(
					'title' => 'member_access',
					'link' => 'global/access',
					'icon' => '',
					'tooltip' => '正在开发中...',
					'children' => array()
				),
				array(
					'title' => 'seccheck',
					'link' => 'global/seccheck',
					'icon' => '',
					'children' => array()
				),
				/*array(
					'title' => 'site_func',
					'link' => 'global/func',
					'icon' => '',
					'children' => array()
				),*/
				array(
					'title' => 'seo',
					'link' => 'global/seo',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'time',
					'link' => 'global/time',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '风格管理',
					'link' => 'global/theme',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '模板管理',
					'link' => 'global/template',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '语言设置',
					'link' => 'global/language',
					'icon' => '',
					'children' => array()
				),
			)
		),
		array(
			'title' => '用户',
			'link' => '',
			'icon' => 'group',
			'children' => array(
				array(
					'title' => '用户管理',
					'link' => 'members/user',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '添加用户',
					'link' => 'members/adduser',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '发送通知',
					'link' => 'members/newsletter',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '禁止用户',
					'link' => 'members/userban',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '禁止IP',
					'link' => 'members/ipban',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '审核用户',
					'link' => 'members/verifyuser',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '管理组',
					'link' => 'members/admingroup',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '用户组',
					'link' => 'members/usergroup',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '激活请求',
					'link' => 'members/activate',
					'icon' => '',
					'children' => array()
				),
			)
		),
		array(
			'title' => '工具',
			'link' => '',
			'icon' => 'wrench',
			'children' => array(
				array(
					'title' => '运行记录',
					'link' => 'tool/runlog',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '清除缓存',
					'link' => 'tool/clearcache',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '邮件设置',
					'link' => 'tool/mailsetting',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '计划任务',
					'link' => 'tool/cronjob',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '备份恢复',
					'link' => 'tool/recovery',
					'icon' => '',
					'children' => array()
				),
			)
		),
		array(
			'title' => 'mhour',
			'link' => '',
			'icon' => 'edit',
			'children' => array(
				array(
					'title' => '申报记录',
					'link' => 'manhour/applylog',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => '添加工时',
					'link' => 'manhour/add',
					'icon' => '',
					'children' => array()
				),
			)
		),
	);
}

/**
 * 检查管理权限
 * 
 * @param string $idx 菜单/权限索引
 * @return mixed
 */
function chkPermit($idx) {
	static $permit = array();
	global $_G;

	if($_G['member']['adminid'] == 0) return false;	// 非管理组，不具备任何管理权限
	if($_G['member']['adminid'] == 1) return true;	// 超级管理组，具有全部权限
	if(empty($permit)) {	// 从数据库中获取权限信息
		$permit = DB::result(DB::query('SELECT `permit` FROM %t WHERE `admingid`=%d LIMIT 1', array('admingroup', $_G['member']['adminid'])));
		$permit = empty($permit) ? array(0) : unserialize($permit);	// 查询为空时无任何管理权限，并为 $permit 添加元素0，避免多次查询
	}

	return isset($permit[$idx]) ? $permit[$idx] : false;
}