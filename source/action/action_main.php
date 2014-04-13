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

		//$total_manhour = DB::result_first('SELECT sum(`manhour`) FROM %t WHERE `uid`=%d AND `status`=1', array('manhours', $_G['uid']));
		DB::query('SET @rank=0');
		$manhour = DB::fetch_first('SELECT * FROM (SELECT `uid`,`manhour`,@rank:=@rank+1 AS rank FROM %t ORDER BY `manhour` DESC) AS t WHERE `uid`=%d', array('users', $_G['uid']), null, false);
		$topmh = DB::fetch_all('SELECT `username`, `manhour` FROM %t ORDER BY `manhour` DESC LIMIT 10', array('users'));
		$announcement = DB::fetch_all('SELECT `id`,`type`,`subject`,`starttime`,`message` FROM %t WHERE (`type`=1 OR `type`=2) AND (`starttime`=0 OR `starttime`<=%d) AND (`endtime`=0 OR `endtime`>=%d) ORDER BY `displayorder` ASC', array('announcement', TIMESTAMP, TIEMSTAMP));
		//trace($manhour);

		//$onlinenum = DB::result_first('SELECT count(*) FROM %t WHERE `lastactivity`>=%d AND `uid`>0', array('session', TIMESTAMP-1440));

		//$template->assign('total_manhour', $total_manhour, true);
		$template->assign('manhour', $manhour['manhour'], true);
		$template->assign('rank', $manhour['rank'], true);
		//$template->assign('onlinenum', $onlinenum, true);
		$template->assign('topmh', $topmh, true);
		$template->assign('announcement', $announcement, true);
		$template->display('main_index');
	}

}
