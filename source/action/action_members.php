<?php

/**
 * 主界面模块
 * SELECT * FROM `conn_users_profile` WHERE FIND_IN_SET('4', `department`)
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class MembersAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		if(!chklogin()) showlogin();
		require libfile('function/nav');
		require libfile('function/members');
	}

	public function index(){
		//redirect(U('members/info'));

		show_developing('members');
	}

	public function user(){
		global $_G, $template;

		has_permit('user');

		if(IS_AJAX){
			$ret = array(
				'errno' => -1,
				'msg' => '未定义操作'
			);

			switch($_REQUEST['type']) {
				case 'deluser':
					$uid = intval($_POST['uid']);
					$uc = $_POST['deluc'] ? true : false;

					require_once libfile('function/logging');

					if($uid == $_G['uid']) {
						$ret['msg'] = '你不能删除自己的账号！';
					} elseif(!DB::result_first(subusersqlformula(null, 'count(*)', null, 'AND uid='.$uid))) {
						$ret['msg'] = '你不能删除不存在或你无权管理的用户！';
					} else {
						$errno = deluser($uid, $uc);
						$ret['errno'] = $errno ? 0 : 1;
						$ret['msg'] = $errno ? '删除成功' : '删除失败';
					}
					break;
				case 'getuser':
					break;
				case 'edituser':
					break;
			}

			ajaxReturn($ret, 'JSON');
		}else{
			$sql = subusersqlformula();
			$users = DB::fetch_all($sql);

			if(!$template->isCached('members_user')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('members', 'user'));
			}
			$template->assign('users', $users, true);
			$template->display('members_user');
		}
	}

	public function verifyuser(){
		global $_G, $template;

		has_permit('verifyuser');

		if(IS_AJAX){
			$return = array(
				'errno' => 1,
				'msg' => ''
			);

			if($_POST['type'] === 'pass') {
				if(empty($_POST['uids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}else{
					require_once libfile('function/logging');
					$_POST['verifytext'] = htmlspecialchars($_POST['verifytext']);
					$uids = explode(',', $_POST['uids']);
					foreach($uids as $k=>$uid) {
						$uids[$k] = intval($uid);
						if($uid > 1) adduser($uid);
					}

					DB::query('UPDATE %t SET `status`=1,`operator`=%d,`operatorname`=%s,`verifytime`=%d,`verifytext`=%s WHERE `uid` IN (%n) AND `status`<>1', array('activation', $_G['uid'], $_G['username'], TIMESTAMP, $_POST['verifytext'], $uids));
					$return['errno'] = 0;
					$return['msg'] = '已通过 '.DB::affected_rows().' 个申请条目';

					$emls = DB::fetch_all('SELECT `email` FROM %t WHERE `uid` IN (%n) AND `status`=1', array('activation', $uids));
					if(!empty($emls)){
						$time = dgmdate(TIMESTAMP);
						$text = '您提交的激活申请已于 '.$time.' 通过审核<br />';
						if(!empty($_POST['verifytext'])) $text .= '审核理由：'.nl2br($_POST['verifytext']).'<br />';
						$text .= '快进我们的工时系统探一探究竟吧';

						require_once libfile('class/Mail');
						Mail::init();
						foreach($emls as $eml) Mail::addAddress($eml['email']);
						Mail::setMsg($text, '账户激活');
						$errno = Mail::send();
					}

					if($errno) $return['msg'] .= '，但在向用户发送邮件时出现了以下问题：<br />'.$errno;
				}
			}elseif($_POST['type'] === 'reject') {
				if(empty($_POST['uids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}elseif(empty($_POST['verifytext'])) {
					$return['msg'] = '拒绝时审核附言不能留空';
				}else{
					$_POST['verifytext'] = htmlspecialchars($_POST['verifytext']);
					$uids = explode(',', $_POST['uids']);
					foreach($uids as $k=>$uid) {
						$uids[$k] = intval($uid);
					}

					DB::query('UPDATE %t SET `status`=2,`operator`=%d,`operatorname`=%s,`verifytime`=%d,`verifytext`=%s WHERE `uid` IN (%n) AND `status`<>2', array('activation', $_G['uid'], $_G['username'], TIMESTAMP, $_POST['verifytext'], $uids));
					$return['errno'] = 0;
					$return['msg'] = '已拒绝 '.DB::affected_rows().' 个申请条目';

					require_once libfile('function/logging');
					deluser($uids);

					$emls = DB::fetch_all('SELECT `email` FROM %t WHERE `uid` IN (%n) AND `status`=2', array('activation', $uids));
					if(!empty($emls)){
						$time = dgmdate(TIMESTAMP);
						$text = '您提交的激活申请于 '.$time.' 被拒绝，审核详情：<br />';
						$text .= nl2br($_POST['verifytext']).'<br />';
						$text .= '请登录工时系统查看详情并重新申请激活';

						require_once libfile('class/Mail');
						Mail::init();
						foreach($emls as $eml) Mail::addAddress($eml['email']);
						Mail::setMsg($text, '激活申请未能通过审核');
						$errno = Mail::send();
					}

					if($errno) $return['msg'] .= '，但在向用户发送邮件时出现了以下问题：<br />'.$errno;
				}
			}elseif($_POST['type'] === 'edit') {
				$return['msg'] = '此功能暂未开放';
			}elseif($_POST['type'] === 'del') {
				if(empty($_POST['uids'])) {
					$return['msg'] = '请至少选择一个有效申请项';
				}else{
					$uids = explode(',', $_POST['uids']);
					foreach($uids as &$uid) {
						$uid = intval($uid);
					}
					DB::query('DELETE FROM %t WHERE `uid` IN (%n)', array('activation', $uids));
					$return['errno'] = 0;
					$return['msg'] = '已删除 '.DB::affected_rows().' 个申请条目';
				}
			}

			if(empty($return['msg'])){
				$return['msg'] = '非法请求';
			}

			ajaxReturn($return, 'JSON');
		}else{
			$sql = 'SELECT * FROM %t WHERE ';
			$sql .= empty($_GET['showall']) ? '`status`=0' : '1';
			$users = DB::fetch_all($sql, array('activation'));

			if(!$template->isCached('members_verifyuser')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('members', 'verifyuser'));
			}
			$template->assign('showall', !empty($_GET['showall']), true);
			$template->assign('users', $users, true);
			$template->display('members_verifyuser');
		}
	}

	public function admingroup(){
		global $_G, $template;

		has_permit('admingroup');

		require_once libfile('class/group');

		if(IS_AJAX) {
			;
		}else{

			if(!$template->isCached('members_admingroup')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('members', 'admingroup'));
			}

			$agrp = group::getgroups('admin');

			$template->assign('agrp', $agrp, true);
			$template->display('members_admingroup');
		}
	}

	public function agrp(){
		define('DISABLE_TRACE', true);
		has_permit('admingroup');

		require_once libfile('class/group');
		$agrp = group::getgroups('admin');
		$grps = array();
		foreach($agrp as $grp) {
			$grps[] = array(
				'id' => $grp['gid'],
				'parent' => $grp['parent'],
				'text' => $grp['name'],
				'state' => array(
					'opened' => true,
				)
			);
		}
		$grps[0]['parent'] = '#';
		ajaxReturn($grps, 'JSON');
	}

}
