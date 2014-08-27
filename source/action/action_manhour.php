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

			if($_POST['type'] === 'pass') {
				if(empty($_POST['ids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}else{
					$_POST['verifytext'] = htmlspecialchars($_POST['verifytext']);
					$ids = explode(',', $_POST['ids']);
					foreach($uids as $k=>$uid) {
						$uids[$k] = intval($uid);
					}

					DB::query('UPDATE %t SET `status`=1,`operator`=%d,`verifytime`=%d,`verifytext`=%s WHERE `id` IN (%n) AND `status` IN (2,4)', array('manhours', $_G['uid'], TIMESTAMP, $_POST['verifytext'], $ids));
					$return['errno'] = 0;
					$return['msg'] = '已通过 '.DB::affected_rows().' 个工时申报条目';

					$_uids = DB::fetch_all('SELECT `uid` FROM %t WHERE `id` IN (%n)', array('manhours', $ids));
					$uids = array();
					foreach($_uids as $u) {
						$uids[] = $u['uid'];
					}

					require_once libfile('function/manhour');
					update_user_manhour($uids);
					update_rank();

					$emls = DB::fetch_all('SELECT `email` FROM %t WHERE `uid` IN (%n)', array('users', $uids));
					if(!empty($emls)){
						$time = dgmdate(TIMESTAMP);
						$text = '您提交的一个或多个工时申报已于 '.$time.' 通过审核<br />';
						if(!empty($_POST['verifytext'])) $text .= '审核理由：'.nl2br($_POST['verifytext']).'<br />';

						require_once libfile('class/Mail');
						Mail::init();
						foreach($emls as $eml) Mail::addAddress($eml['email']);
						Mail::setMsg($text, '工时申报已通过审核');
						$errno = Mail::send();
					}

					if($errno) $return['msg'] .= '，但在向用户发送邮件时出现了以下问题：<br />'.$errno;
				}
			}elseif($_POST['type'] === 'reject') {
				if(empty($_POST['ids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}elseif(empty($_POST['verifytext'])) {
					$return['msg'] = '拒绝时理由不能留空';
				}else{
					$_POST['verifytext'] = htmlspecialchars($_POST['verifytext']);
					$ids = explode(',', $_POST['ids']);
					foreach($uids as $k=>$uid) {
						$uids[$k] = intval($uid);
					}

					DB::query('UPDATE %t SET `status`=4,`operator`=%d,`verifytime`=%d,`verifytext`=%s WHERE `id` IN (%n) AND `status` IN (2,4)', array('manhours', $_G['uid'], TIMESTAMP, $_POST['verifytext'], $ids));
					$return['errno'] = 0;
					$return['msg'] = '已拒绝 '.DB::affected_rows().' 个工时申报条目';

					$_uids = DB::fetch_all('SELECT `uid` FROM %t WHERE `id` IN (%n)', array('manhours', $ids));
					$uids = array();
					foreach($_uids as $u) {
						$uids[] = $u['uid'];
					}
					$emls = DB::fetch_all('SELECT `email` FROM %t WHERE `uid` IN (%n)', array('users', $uids));
					if(!empty($emls)){
						$time = dgmdate(TIMESTAMP);
						$text = '您提交的一个或多个工时申报记录于 '.$time.' 被拒绝，详情：<br />';
						$text .= nl2br($_POST['verifytext']).'<br />';
						$text .= '如有疑问，请重新申请、复查此记录或与管理员联系';

						require_once libfile('class/Mail');
						Mail::init();
						foreach($emls as $eml) Mail::addAddress($eml['email']);
						Mail::setMsg($text, '工时申报未能通过审核');
						$errno = Mail::send();
					}

					if($errno) $return['msg'] .= '，但在向用户发送邮件时出现了以下问题：<br />'.$errno;
				}
			}elseif($_POST['type'] === 'edit') {
				$return['msg'] = '出于对用户的考虑，不允许管理员直接编辑数据';
			}elseif($_POST['type'] === 'del') {
				if(empty($_POST['ids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}else{
					$ids = explode(',', $_POST['ids']);
					foreach($ids as &$id) {
						$id = intval($id);
					}
					DB::query('DELETE FROM %t WHERE `id` IN (%n) AND `status` IN (2,4)', array('manhours', $ids));
					$return['errno'] = 0;
					$return['msg'] = '已删除 '.DB::affected_rows().' 个工时申报条目';

					require_once libfile('function/manhour');
					$_uids = DB::fetch_all('SELECT `uid` FROM %t WHERE `id` IN (%n)', array('manhours', $ids));
					$uids = array();
					foreach($_uids as $u) $uids[] = $u['uid'];
					update_user_manhour($uids);
					update_rank();
				}
			}

			if(empty($return['msg'])){
				$return['msg'] = '非法请求';
			}

			ajaxReturn($return, 'JSON');
		}else{
			$status = empty($_GET['showall']) ? '2' : '2,4';
			$manhours = DB::fetch_all(subusersqlformula(DB::table('manhours').".status IN ({$status})", 'id,'.DB::table('manhours').'.uid,'.DB::table('manhours').'.status,username,realname,gender,'.DB::table('manhours').'.manhour,aid,actname,time,applytime,remark', 'manhours'));

			if(!$template->isCached('manhour_applylog')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('mhour', 'applylog'));
			}

			$template->assign('showall', !empty($_GET['showall']), true);
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

			if($_POST['type'] === 'pass') {
				if(empty($_POST['ids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}else{
					$_POST['verifytext'] = htmlspecialchars($_POST['verifytext']);
					$ids = explode(',', $_POST['ids']);
					foreach($uids as $k=>$uid) {
						$uids[$k] = intval($uid);
					}

					DB::query('UPDATE %t SET `status`=1,`operator`=%d,`verifytime`=%d,`verifytext`=%s WHERE `id` IN (%n) AND `status` IN (0,3,5)', array('manhours', $_G['uid'], TIMESTAMP, $_POST['verifytext'], $ids));
					$return['errno'] = 0;
					$return['msg'] = '已通过 '.DB::affected_rows().' 个工时申报条目';

					$_uids = DB::fetch_all('SELECT `uid` FROM %t WHERE `id` IN (%n)', array('manhours', $ids));
					$uids = array();
					foreach($_uids as $u) {
						$uids[] = $u['uid'];
					}

					require_once libfile('function/manhour');
					update_user_manhour($uids);
					update_rank();

					$emls = DB::fetch_all('SELECT `email` FROM %t WHERE `uid` IN (%n)', array('users', $uids));
					if(!empty($emls)){
						$time = dgmdate(TIMESTAMP);
						$text = '您提交的一个或多个工时申报已于 '.$time.' 通过审核<br />';
						if(!empty($_POST['verifytext'])) $text .= '审核理由：'.nl2br($_POST['verifytext']).'<br />';

						require_once libfile('class/Mail');
						Mail::init();
						foreach($emls as $eml) Mail::addAddress($eml['email']);
						Mail::setMsg($text, '工时申报已通过审核');
						$errno = Mail::send();
					}

					if($errno) $return['msg'] .= '，但在向用户发送邮件时出现了以下问题：<br />'.$errno;
				}
			}elseif($_POST['type'] === 'reject') {
				if(empty($_POST['ids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}elseif(empty($_POST['verifytext'])) {
					$return['msg'] = '拒绝时理由不能留空';
				}else{
					$_POST['verifytext'] = htmlspecialchars($_POST['verifytext']);
					$ids = explode(',', $_POST['ids']);
					foreach($uids as $k=>$uid) {
						$uids[$k] = intval($uid);
					}

					DB::query('UPDATE %t SET `status`=5,`operator`=%d,`verifytime`=%d,`verifytext`=%s WHERE `id` IN (%n) AND `status` IN (0,3,5)', array('manhours', $_G['uid'], TIMESTAMP, $_POST['verifytext'], $ids));
					$return['errno'] = 0;
					$return['msg'] = '已拒绝 '.DB::affected_rows().' 个工时申报条目';

					$_uids = DB::fetch_all('SELECT `uid` FROM %t WHERE `id` IN (%n)', array('manhours', $ids));
					$uids = array();
					foreach($_uids as $u) {
						$uids[] = $u['uid'];
					}
					$emls = DB::fetch_all('SELECT `email` FROM %t WHERE `uid` IN (%n)', array('users', $uids));
					if(!empty($emls)){
						$time = dgmdate(TIMESTAMP);
						$text = '您提交的一个或多个工时申报记录于 '.$time.' 被拒绝，详情：<br />';
						$text .= nl2br($_POST['verifytext']).'<br />';
						$text .= '如有疑问，请重新申请、复查此记录或与管理员联系';

						require_once libfile('class/Mail');
						Mail::init();
						foreach($emls as $eml) Mail::addAddress($eml['email']);
						Mail::setMsg($text, '工时申报未能通过审核');
						$errno = Mail::send();
					}

					if($errno) $return['msg'] .= '，但在向用户发送邮件时出现了以下问题：<br />'.$errno;
				}
			}elseif($_POST['type'] === 'edit') {
				$return['msg'] = '出于对用户的考虑，不允许管理员直接编辑数据';
			}elseif($_POST['type'] === 'del') {
				if(empty($_POST['ids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}else{
					$ids = explode(',', $_POST['ids']);
					foreach($ids as &$id) {
						$id = intval($id);
					}
					DB::query('DELETE FROM %t WHERE `id` IN (%n) AND `status` IN (0,3,5)', array('manhours', $ids));
					$return['errno'] = 0;
					$return['msg'] = '已删除 '.DB::affected_rows().' 个工时申报条目';

					require_once libfile('function/manhour');
					$_uids = DB::fetch_all('SELECT `uid` FROM %t WHERE `id` IN (%n)', array('manhours', $ids));
					$uids = array();
					foreach($_uids as $u) $uids[] = $u['uid'];
					update_user_manhour($uids);
					update_rank();
				}
			}

			if(empty($return['msg'])){
				$return['msg'] = '非法请求';
			}

			ajaxReturn($return, 'JSON');
		}else{
			$status = empty($_GET['showall']) ? '3' : '0,3,5,6';
			$manhours = DB::fetch_all(subusersqlformula(DB::table('manhours').".status IN ({$status})", 'id,'.DB::table('manhours').'.uid,'.DB::table('manhours').'.status,username,realname,gender,'.DB::table('manhours').'.manhour,aid,actname,time,applytime,remark', 'manhours'));

			if(!$template->isCached('manhour_checklog')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('mhour', 'checklog'));
			}

			$template->assign('showall', !empty($_GET['showall']), true);
			$template->assign('manhours', $manhours, true);
			$template->display('manhour_checklog');
		}
	}

	public function manage(){
		show_developing('mhour');
	}

}
