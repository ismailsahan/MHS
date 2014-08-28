<?php

/**
 * 返回默认侧边导航栏列表
 */
function &defaultNav(){
	return array(
		array(
			'title' => 'home',
			'link' => 'main/index',
			'icon' => 'fa fa-home',
			'children' => array()
		),
		array(
			'title' => 'manhour',
			'link' => 'manhour/index',
			'icon' => 'fa fa-leaf',
			'children' => array()
		),
		array(
			'title' => 'self',
			'link' => 'self/profile',
			'icon' => 'fa fa-user',
			'children' => array()
		),
		/*array(
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
				)
			)
		),*/
	);
}

/**
 * 返回管理的侧边导航栏列表
 */
function &adminNav(){
	static $nav = array(
		array(
			'title' => 'global',
			'link' => '',
			'icon' => 'fa fa-cogs',
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
			'icon' => 'fa fa-group',
			'children' => array(
				array(
					'title' => 'user',
					'link' => 'members/user',
					'icon' => '',
					'children' => array()
				),
				/*array(
					'title' => 'adduser',
					'link' => 'members/adduser',
					'icon' => '',
					'children' => array()
				),*/
				/*array(
					'title' => 'sdmsg',
					'link' => 'members/sdmsg',
					'icon' => '',
					'children' => array()
				),*/
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
					'tag' => 'danger',
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
			'icon' => 'fa fa-wrench',
			'children' => array(
				/*array(
					'title' => 'runlog',
					'link' => 'tool/runlog',
					'icon' => '',
					'children' => array()
				),*/
				array(
					'title' => 'clearcache',
					'link' => 'tool/clearcache',
					'icon' => '',
					'children' => array()
				),
				/*array(
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
				),*/
			)
		),
		array(
			'title' => 'mhour',
			'link' => '',
			'icon' => 'fa fa-edit',
			'children' => array(
				array(
					'title' => 'applylog',
					'link' => 'manhour/applylog',
					'icon' => '',
					'tag' => 'danger',
					'children' => array()
				),
				array(
					'title' => 'checklog',
					'link' => 'manhour/checklog',
					'icon' => '',
					'tag' => 'danger',
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
		array(
			'title' => 'mhdict',
			'link' => '',
			'icon' => 'fa fa-file-text',
			'children' => array(
				array(
					'title' => 'activity',
					'link' => 'mhdict/activity',
					'icon' => '',
					'children' => array()
				),
				/*array(
					'title' => 'basic',
					'link' => 'mhdict/basic',
					'icon' => '',
					'children' => array()
				),
				array(
					'title' => 'league',
					'link' => 'mhdict/league',
					'icon' => '',
					'children' => array()
				),*/
				array(
					'title' => 'ann',
					'link' => 'mhdict/ann',
					'icon' => '',
					'children' => array()
				),
			)
		),
	);
	return $nav;
}

/**
 * 检查管理权限（模板用）
 * 
 * @param string $idx 菜单/权限索引
 * @return mixed
 */
function chkPermit($idx = null) {
	global $_G;
	static $menutitle = array(), $menuidx=null, $founder=null;
	//static $count = 0;

	if($founder === null) {
		if(is_array($_G['config']['admincp']['founder'])) {
			$founder = $_G['config']['admincp']['founder'];
		}elseif(is_string($_G['config']['admincp']['founder'])) {
			$founder = explode(',', $_G['config']['admincp']['founder']);
		}else{
			$founder = array($_G['config']['admincp']['founder']);
		}

		foreach($founder as $k => $v) {
			$founder[$k] = intval($v);
		}
	}

	if($idx === null) {
		if(empty($menutitle))
			$menutitle = menutitle(adminNav());
		$idx = $menuidx = $menuidx===null ? current($menutitle) : next($menutitle);
		//$idx = $menuidx = next($menutitle);
	}

	//trace($count++.' '.$idx);

	if($_G['member']['adminid'] == 1 || $_G['uid']>0 && in_array($_G['uid'], $founder)) return true;	// 超级管理组，具有全部权限
	if($_G['member']['adminid'] == 0) return false;														// 非管理组，不具备任何管理权限
	if(!isset($_G['member']['adminpermit']) && $_G['member']['adminid']>1) {							// 从数据库中获取权限信息
		$_G['member']['adminpermit'] = DB::result_first('SELECT `permit` FROM %t WHERE `gid`=%d LIMIT 1', array('admingroup', $_G['member']['adminid']));
		$_G['member']['adminpermit'] = empty($_G['member']['adminpermit']) ? array() : explode(',', $_G['member']['adminpermit']);
	}

	return isset($_G['member']['adminpermit'][$idx]) ? $_G['member']['adminpermit'][$idx] : false;
}

/**
 * 检查管理权限（后台用）
 * 若未登录则显示未登录提示
 * 若无权限则显示拒绝访问
 * 
 * @param string $idx 菜单/权限索引
 * @return mixed
 */
function has_permit($idx) {
	global $_G, $template;

	if(!chklogin()) { // 未登录
		showlogin();
	}elseif(!chkPermit($idx)) { // 无权限
		if(IS_AJAX) {
			ajaxReturn(array(
				'errno' => 401,
				'msg' => '拒绝访问'
			), 'AUTO');
		}
		$template->display('noaccess');
		exit;
	}
}

function &menutitle($nav) {
	$arr = array();
	foreach($nav as $menu) {
		$arr[] = $menu['title'];
		if(!empty($menu['children']))
			$arr = array_merge($arr, menutitle($menu['children']));
	}
	return $arr;
}
