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
		if(!$template->isCached('main_index')){
			$template->assign('sidebarMenu', defaultNav());
			$template->assign('adminNav', adminNav());
			$template->assign('menuset', array('home'));
		}

		$total_manhour = DB::result_first('SELECT sum(`manhour`) FROM %t WHERE `uid`=%d AND `status`=1', array('manhours', $_G['uid']));
		DB::query('SET @rank=0');
		$manhour = DB::fetch_first('SELECT * FROM (SELECT `uid`,`manhour`,@rank:=@rank+1 AS rank FROM %t ORDER BY `manhour` DESC) AS t WHERE `uid`=%d', array('users', $_G['uid']), null, false);
		trace($manhour);

		$onlinenum = DB::result_first('SELECT count(*) FROM %t WHERE `lastactivity`>=%d-1440', array('session', TIMESTAMP));

		$template->assign('total_manhour', $total_manhour, true);
		$template->assign('rank', $manhour['rank'], true);
		$template->assign('onlinenum', $onlinenum, true);
		$template->display('main_index');
	}

}
