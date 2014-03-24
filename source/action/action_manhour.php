<?php

/**
 * 工时模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class ManhourAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		if(!chklogin()) redirect(U('logging/login'));
		require libfile('function/nav');
	}

	public function index(){
		global $_G, $template;
		if(!$template->isCached('manhour_index')){
			$template->assign('sidebarMenu', defaultNav());
			$template->assign('adminNav', adminNav());
			$template->assign('menuset', array('manhour'));
		}
		$template->assign('manhours', DB::fetch_all('SELECT * FROM %t WHERE `uid`=%d ORDER BY `id` DESC', array('manhours', $_G['uid'])), true);
		$template->display('manhour_index');
	}

	public function applylog(){
		$this->_developing();
	}

	public function checklog(){
		$this->_developing();
	}

	public function manage(){
		$this->_developing();
	}

	private function _developing(){
		global $_G, $template;
		if(!$template->isCached('developing')){
			$template->assign('sidebarMenu', defaultNav());
			$template->assign('adminNav', adminNav());
			$template->assign('menuset', array('mhour', OPERATION_NAME));
		}
		$template->display('developing');
	}

}
