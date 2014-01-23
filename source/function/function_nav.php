<?php

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
					'access' => 0,
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
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => 'seccheck',
					'link' => 'global/seccheck',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				/*array(
					'title' => 'site_func',
					'link' => 'global/func',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),*/
				array(
					'title' => 'seo',
					'link' => 'global/seo',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => 'time',
					'link' => 'global/time',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '风格管理',
					'link' => 'global/time',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '模板管理',
					'link' => 'global/time',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '语言设置',
					'link' => 'global/language',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
			)
		),
		array(
			'title' => '用户',
			'link' => '',
			'icon' => 'group',
			'access' => 0,
			'children' => array(
				array(
					'title' => '用户管理',
					'link' => 'members/user',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '添加用户',
					'link' => 'members/adduser',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '发送通知',
					'link' => 'members/newsletter',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '禁止用户',
					'link' => 'members/userban',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '禁止IP',
					'link' => 'members/ipban',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '审核用户',
					'link' => 'members/verifyuser',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '管理组',
					'link' => 'members/admingroup',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '用户组',
					'link' => 'members/usergroup',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
			)
		),
		array(
			'title' => '工具',
			'link' => '',
			'icon' => 'wrench',
			'access' => 0,
			'children' => array(
				array(
					'title' => '运行记录',
					'link' => '',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '清除缓存',
					'link' => '',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '邮件设置',
					'link' => '',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '计划任务',
					'link' => '',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '备份恢复',
					'link' => '',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
			)
		),
		array(
			'title' => 'manhour',
			'link' => '',
			'icon' => 'edit',
			'children' => array(
				array(
					'title' => '申报记录',
					'link' => '',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
				array(
					'title' => '添加工时',
					'link' => '',
					'icon' => '',
					'access' => 0,
					'children' => array()
				),
			)
		),
	);
}