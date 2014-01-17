<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class MainAction {
	public $default_method = 'index';

	public static function navMenu(){
		return array(
			array(
				'title' => 'home',
				'link' => 'main/index',
				'icon' => 'home',
				'access' => 0,
				'tag' => array(),
				'children' => array()
			),
			array(
				'title' => '查询',
				'link' => 'main/query',
				'icon' => 'info-circle',
				'access' => 0,
				'tooltip' => '正在开发中...',
				'children' => array()
			),
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
						'title' => 'site_func',
						'link' => 'global/func',
						'icon' => '',
						'access' => 0,
						'children' => array()
					),
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
				'title' => '用户设置',
				'link' => '',
				'icon' => 'user',
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
		);
	}

	public function index(){
		global $_G, $template;
		$template->assign('sidebarMenu', $this->navMenu());
		$template->assign('menuset', array('home'));
		$template->display('common_main');
	}

}
