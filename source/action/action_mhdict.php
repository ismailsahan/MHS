<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class MhdictAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		require libfile('function/nav');
	}

	public function index(){
		global $_G, $template;
		if(!$template->isCached('developing')){
			$template->assign('sidebarMenu', defaultNav());
			$template->assign('adminNav', adminNav());
			$template->assign('menuset', array('mhdict', OPERATION_NAME));
		}
		$template->display('developing');
	}

}
