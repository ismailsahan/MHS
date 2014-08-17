<?php

/**
 * 工时模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class ManhourAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		if(!chklogin()) showlogin();
		require libfile('function/nav');
		require libfile('function/members');
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
		global $_G, $template;

		has_permit('applylog');

		if(IS_AJAX){
			$return = array(
				'errno' => 1,
				'msg' => ''
			);

			if(empty($return['msg'])){
				$return['msg'] = '非法请求';
			}

			ajaxReturn($return, 'JSON');
		}else{
			$manhours = DB::fetch_all(subusersqlformula(DB::table('manhours').'.status IN (2,4)', 'id,'.DB::table('manhours').'.uid,'.DB::table('manhours').'.status,username,realname,gender,'.DB::table('manhours').'.manhour,aid,actname,time,applytime,remark', 'manhours'));

			trace($manhours);

			if(!$template->isCached('manhour_applylog')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('mhour', 'applylog'));
			}
			$template->assign('manhours', $manhours, true);
			$template->display('manhour_applylog');
		}
	}

	public function checklog(){
		global $_G, $template;

		has_permit('checklog');

		if(IS_AJAX){
			$return = array(
				'errno' => 1,
				'msg' => ''
			);

			if(empty($return['msg'])){
				$return['msg'] = '非法请求';
			}

			ajaxReturn($return, 'JSON');
		}else{
			$manhours = DB::fetch_all(subusersqlformula(DB::table('manhours').'.status IN (0,3,5)', 'id,'.DB::table('manhours').'.uid,'.DB::table('manhours').'.status,username,realname,gender,'.DB::table('manhours').'.manhour,aid,actname,time,applytime,remark', 'manhours'));

			trace($manhours);

			if(!$template->isCached('manhour_applylog')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('mhour', 'checklog'));
			}
			$template->assign('manhours', $manhours, true);
			$template->display('manhour_applylog');
		}
	}

	public function manage(){
		show_developing('mhour');
	}

}
