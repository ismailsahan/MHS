<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class ToolAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		if(!chklogin()) showlogin();
		require libfile('function/nav');
	}

	public function index(){
		show_developing('tool');
	}

	public function clearcache() {
		global $_G, $template;
		if(IS_AJAX) {
			clearcache('all');
			Cache::set('CacheId', random(4));
			ajaxReturn(array(
				'errno' => 0,
				'msg' => '全部缓存已清除完毕'
			));
		}
		if(!$template->isCached('tool_clearcache')){
			$template->assign('sidebarMenu', defaultNav());
			$template->assign('adminNav', adminNav());
			$template->assign('menuset', array('tool', 'clearcache'));
		}
		$template->display('tool_clearcache');
	}

	public function mailsetting() {
		global $_G, $template;
	}

}
