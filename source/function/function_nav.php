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
			'children' => array()
		),
		array(
			'title' => 'manhour',
			'link' => 'manhour/index',
			'icon' => 'leaf',
			'children' => array()
		),
		array(
			'title' => 'self',
			'link' => 'self/index',
			'icon' => 'user',
			'children' => array(
				array(
					'title' => 'profile',
					'link' => 'self/profile',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'pm',
					'link' => 'self/pm',
					'icon' => '',
					'tag' => 'info',
					'children' => array()
				),
				/*array(
					'title' => 'cgpwd',
					'link' => 'self/cgpwd',
					'icon' => '',
					'children' => array()
				),*/
			)
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
			'children' => array(
				array(
					'title' => 'site_info',
					'link' => 'global/info',
					'icon' => '',
					'tag' => 'success',
					'children' => array()
				),
				array(
					'title' => 'member_access',
					'link' => 'global/access',
					'icon' => '',
					//'tooltip' => '正在开发中...',
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
				/*array(
					'title' => 'seo',
					'link' => 'global/seo',
					'icon' => '',
					'children' => array()
				),*/
				array(
					'title' => 'time',
					'link' => 'global/time',
					'icon' => '',
					'children' => array()
				),
				/*array(
					'title' => 'theme',
					'link' => 'global/theme',
					'icon' => '',
					'children' => array()
				),*/
				/*array(
					'title' => 'tpl',
					'link' => 'global/tpl',
					'icon' => '',
					'children' => array()
				),*/
				/*array(
					'title' => 'language',
					'link' => 'global/language',
					'icon' => '',
					'children' => array()
				),*/
			)
		),
		array(
			'title' => 'members',
			'link' => '',
			'icon' => 'group',
			'children' => array(
				array(
					'title' => 'user',
					'link' => 'members/user',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'adduser',
					'link' => 'members/adduser',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'sdmsg',
					'link' => 'members/sdmsg',
					'icon' => '',
					'children' => array()
				),
				/*array(
					'title' => 'userban',
					'link' => 'members/userban',
					'icon' => '',
					'children' => array()
				),*/
				/*array(
					'title' => 'ipban',
					'link' => 'members/ipban',
					'icon' => '',
					'children' => array()
				),*/
				array(
					'title' => 'verifyuser',
					'link' => 'members/verifyuser',
					'tag' => 'warning',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'admingroup',
					'link' => 'members/admingroup',
					'icon' => '',
					'children' => array()
				),
				/*array(
					'title' => 'usergroup',
					'link' => 'members/usergroup',
					'icon' => '',
					'children' => array()
				),*/
				/*array(
					'title' => 'activate',
					'link' => 'members/activate',
					'icon' => '',
					'tag' => 'warning',
					'children' => array()
				),*/
			)
		),
		array(
			'title' => 'tool',
			'link' => '',
			'icon' => 'wrench',
			'children' => array(
				array(
					'title' => 'runlog',
					'link' => 'tool/runlog',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'clearcache',
					'link' => 'tool/clearcache',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'mailsetting',
					'link' => 'tool/mailsetting',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'cronjob',
					'link' => 'tool/cronjob',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'recovery',
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
					'title' => 'applylog',
					'link' => 'manhour/applylog',
					'icon' => '',
					'tag' => 'warning',
					'children' => array()
				),
				array(
					'title' => 'checklog',
					'link' => 'manhour/checklog',
					'icon' => '',
					'tag' => 'warning',
					'children' => array()
				),
				array(
					'title' => 'manage',
					'link' => 'manhour/manage',
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
