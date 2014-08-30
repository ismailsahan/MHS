<?php

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
					} elseif(!DB::result_first(subusersqlformula(DB::table('users').'.`uid`='.$uid, 'count(*)'))) {
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
			$academies = DB::fetch_all('SELECT * FROM %t', array('profile_academies'), 'id');

			if(!$template->isCached('members_user')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('members', 'user'));
			}
			$template->assign('users', $users, true);
			$template->assign('academies', $academies, true);
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
					$_POST['verifytext'] = htmlspecialchars(remove_xss($_POST['verifytext']));
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
					$_POST['verifytext'] = htmlspecialchars(remove_xss($_POST['verifytext']));
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
				$return['msg'] = '出于对用户的考虑，不允许管理员直接编辑用户的数据<br/>你可以联系用户重新申请';
				//$return['msg'] = '此功能暂未开放';
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
			$academies = DB::fetch_all('SELECT * FROM %t', array('profile_academies'), 'id');

			if(!$template->isCached('members_verifyuser')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('members', 'verifyuser'));
			}
			$template->assign('showall', !empty($_GET['showall']), true);
			$template->assign('users', $users, true);
			$template->assign('academies', $academies, true);
			$template->display('members_verifyuser');
		}
	}

	public function admingroup(){
		global $_G, $template;

		has_permit('admingroup');

		require_once libfile('class/group');

		if(IS_AJAX) {
			$return = array(
				'errno' => 0,
				'msg' => ''
			);

			if(isset($_POST['agid'])) { // Get admingroup info
				$agrp = group::getgroups('admin');
				if(isset($agrp[intval($_POST['agid'])])) {
					$return = $agrp[intval($_POST['agid'])];
					unset($return['relation']);
					ajaxReturn($return, 'JSON');
				}
			} elseif(isset($_GET['agrpmem'])) { // Manage admingroup's members
				if(isset($_GET['gid'])) {
					$agrp = group::getgroups('admin');
					$_GET['gid'] = 1;
					$users = array();
					if(isset($agrp[intval($_GET['gid'])])) {
						$query = DB::query('SELECT a.`uid`,a.`username`,b.`realname` FROM %t AS a, %t AS b WHERE a.`uid`=b.`uid` AND `adminid`=%d', array('users', 'users_profile', $_GET['gid']));
						while ($row = DB::fetch($query, MYSQL_NUM)) {
							$users[] = $row;
						}
						DB::free_result($query);
					}
					ajaxReturn(array('data'=>$users), 'JSON');
				} elseif($_POST['opmethod'] == 'add') {
					$agrp = group::getgroups('admin');
					if(!isset($agrp[intval($_POST['gid'])])) {
						$return['errno'] = 1;
						$return['msg'] = '管理组不存在或无权访问';
					} elseif(intval($_POST['uid']) == $_G['uid']) {
						$return['errno'] = 1;
						$return['msg'] = '你不能添加你自己';
					} elseif(!DB::result_first(subusersqlformula(DB::table('users').'.`uid`='.intval($_POST['uid']), 'count(*)'))) {
						$return['errno'] = 1;
						$return['msg'] = '无法添加 UID 不存在或者你不能管理的用户';
					} elseif(!($gid = DB::result_first('SELECT `adminid` FROM %t WHERE `uid`=%d LIMIT 1', array('users', $_POST['uid'])))){
						$return['errno'] = 1;
						$return['msg'] = $gid==$_POST['gid'] ? '你所添加的用户已在此组中' : "你所添加的用户已另属 ID 为 {$gid} 的管理组，请先在对应的组中移除后再尝试添加";
					} else {
						DB::query('UPDATE %t SET `adminid`=%d WHERE `uid`=%d LIMIT 1', array('users', $_POST['gid'], $_POST['uid']));
						$return['msg'] = '添加成功！';
					}
				} elseif($_POST['opmethod'] == 'remove') {
					$agrp = group::getgroups('admin');
					if(!isset($agrp[intval($_POST['gid'])])) {
						$return['errno'] = 1;
						$return['msg'] = '管理组不存在或无权访问';
					} elseif(intval($_POST['uid']) == $_G['uid']) {
						$return['errno'] = 1;
						$return['msg'] = '你不能操作你自己';
					} elseif(!DB::result_first(subusersqlformula(DB::table('users').'.`uid`='.intval($_POST['uid']), 'count(*)'))) {
						$return['errno'] = 1;
						$return['msg'] = '无法移除 UID 不存在或者你不能管理的用户';
					} else {
						$gid = DB::result_first('SELECT `adminid` FROM %t WHERE `uid`=%d LIMIT 1', array('users', $_POST['uid']));
						if($gid == $_POST['gid']) {
							DB::query('UPDATE %t SET `adminid`=0 WHERE `uid`=%d LIMIT 1', array('users', $_POST['uid']));
							$return['msg'] = '移除成功！';
						} else {
							$return['errno'] = 1;
							$return['msg'] = '该用户不在此管理组中，无法移除';
						}
					}
				}
				
			} elseif(isset($_POST['ids']) && is_string($_POST['ids'])) { // Delete admingroups
				$ids = explode(',', $_POST['ids']);
				foreach($ids as &$id) $id = intval($id);
				$ids = array_unique($ids);

				try {
					foreach($ids as $id) group::delgroup('admin', $id);
					$return['msg'] = '所选管理组已删除';
				} catch (Exception $e) {
					$return['errno'] = 1;
					$return['msg'] = $e->getMessage();
				}
			} elseif(isset($_POST['id'])) { // Add or edit admingroup
				$name = htmlspecialchars(remove_xss(trim($_POST['name'])));
				$parent = intval($_POST['parent']);
				$note = htmlspecialchars(remove_xss(trim($_POST['note'])));
				$formula = trim($_POST['formula']);
				$permit = $_POST['permit'];

				if(empty($name)) {
					$return['errno'] = 1;
					$return['msg'] = '组头衔不能为空';
				} elseif(empty($permit) || !is_array($permit)) {
					$return['errno'] = 1;
					$return['msg'] = '访问权限为空或非法';
				} else {
					$agrp = group::getgroups('admin');
					if(!isset($agrp[$parent])) {
						$return['errno'] = 1;
						$return['msg'] = '父级管理组非法';
					} elseif(strspn($formula, '()')%2 > 0) {
						$return['errno'] = 1;
						$return['msg'] = '公式语法错误';
					}

					$_formula = group::combineformula($formula, $agrp[$parent]['formula']);
					unset($agrp);
					try {
						DB::query(subusersqlformula('', 'count(*)', null, $_formula));
					} catch (Exception $e) {
						framework_error::db_error($e, false, false);
						$return['errno'] = 1;
						$return['msg'] = '公式语法错误或存在安全威胁';
					}

					if(!$return['errno']) {
						$data = array(
							'name'    => $name,
							'parent'  => $parent,
							'note'    => $note,
							'formula' => $formula,
							'permit'  => $permit
						);
						try {
							if($_POST['id']) {
								group::editgroup('admin', intval($_POST['id']), $data);
								$return['msg'] = '已成功编辑管理组 '.$name;
							} else {
								group::addgroup('admin', $data);
								$return['msg'] = '管理组 '.$name.' 已成功添加';
							}
						} catch (Exception $e) {
							$return['errno'] = 1;
							$return['msg'] = $e->getMessage();
						}
					}
				}
			}

			if(empty($return['msg'])){
				$return['errno'] = 1;
				$return['msg'] = '非法请求';
			}

			ajaxReturn($return, 'JSON');
		}else{

			if(!$template->isCached('members_admingroup')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('members', 'admingroup'));
			}

			$agrp = group::getgroups('admin');
			//$pgrp = DB::fetch_first('SELECT * FROM %t WHERE `gid`=%d LIMIT 1', array('admingroup', $agrp[$_G['member']['adminid']]['parent']));

			$template->assign('agrp', $agrp, true);
			//$template->assign('pgrp', $pgrp, true);
			$template->assign('permits', getpermitlist(), true);
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
