<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class MhdictAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		if(!chklogin()) showlogin();
		require libfile('function/nav');
	}

	public function index(){
		show_developing('mhdict');
	}

	public function ann() {
		global $_G, $template;

		has_permit('ann');

		if(IS_AJAX){
			$return = array(
				'errno' => 1,
				'msg' => ''
			);

			if($_POST['type'] === 'pass') {
				;
			}elseif($_POST['type'] === 'reject') {
				;
			}elseif($_POST['type'] === 'edit') {
				;
			}elseif($_POST['type'] === 'del') {
				;
			}

			if(empty($return['msg'])){
				$return['msg'] = '非法请求';
			}

			ajaxReturn($return, 'JSON');
		}else{
			$manage_all_ann = chkPermit('manage_all_ann');
			$access_to_global_ann = chkPermit('access_to_global_ann');
			$anns = DB::fetch_all('SELECT * FROM %t WHERE %d OR (`author`=%s AND `academy` IN (%n)) ORDER BY `displayorder` ASC', array('announcement', $manage_all_ann, $_G['username'], $access_to_global_ann ? array(0, $_G['member']['academy']) : array($_G['member']['academy'])));
			$academies = DB::fetch_all('SELECT * FROM %t', array('profile_academies'), 'id');

			if(!$template->isCached('mhdict_ann')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('mhdict', 'ann'));
			}

			$template->assign('anns', $anns, true);
			$template->assign('academies', $academies, true);
			$template->display('mhdict_ann');
		}
	}

}
