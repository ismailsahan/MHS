<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class MainAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		require libfile('function/nav');
	}

	public function index(){
		global $_G, $template;
		$template->assign('sidebarMenu', defaultNav());
		$template->assign('adminNav', adminNav(), true);
		$template->assign('menuset', array('home'));
		$template->display('main_index');
	}

}
