<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class SelfAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		require libfile('function/nav');
	}

	public function index(){
		redirect(U('self/profile'));
	}

	public function profile(){
		global $_G, $template;

		if(IS_AJAX){
			$name = &$_POST['name'];
			$value = &$_POST['value'];
			if($_G['setting']['profileset'] && $_G['setting']['profileset'][$name] && !$_G['setting']['profileset'][$name]){
				$this->_ajaxError('管理员不允许用户修改这一栏！如急需修改，请联系管理员');
			}
			require_once libfile('function/logging');
			switch($name){
				case 'email':
					if(!is_string($value)){
						$this->_ajaxError('参数错误');
					}elseif(empty($value)){
						$this->_ajaxError('Email 不能为空');
					}
					require libfile('function/logging');
					$errno = checkemail($value);
					if($errno === -4 || !isemail($value)){
						$this->_ajaxError('Email 格式有误');
					}elseif($errno === 1){
						$errno = edituser($_G['username'], null, null, $value, true);
						if($errno === 1){
							login($_G['username'], '', $errmsg, $_G['uid'], '', false);
						}elseif($errno === -4){
							$this->_ajaxError('Email 格式有误');
						}elseif($errno === -5){
							$this->_ajaxError('该 Email 不允许注册');
						}elseif($errno === -6){
							$this->_ajaxError('该 Email 已经被注册');
						}elseif($errno == 0 || $errno == -7){
							$result['msg'] = '没有做任何修改';
						}elseif($errno == -8){
							$result['msg'] = '用户受保护无权限更改';
						}else{
							$this->_ajaxError('未知错误');
						}
					}elseif($errno === -5){
						$this->_ajaxError('该 Email 不允许注册');
					}elseif($errno === -6){
						$this->_ajaxError('该 Email 已经被注册');
					}else{
						$this->_ajaxError('未知错误');
					}
					break;
				case 'realname':
					if(!is_string($value)){
						$this->_ajaxError('参数错误');
					}elseif(empty($value)){
						$this->_ajaxError('真实姓名不能为空');
					}elseif($username != addslashes($username)){
						$this->_ajaxError('真实姓名安全校验不合格');
					}
					$this->_update('realname', $value, 's');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'gender':
					if(!is_string($value)){
						$this->_ajaxError('参数错误');
					}elseif(empty($value)){
						$this->_ajaxError('性别不能为空');
					}elseif(!in_array($value, array('1', '2'))){
						$this->_ajaxError('性别错误');
					}
					$this->_update('gender', $value, 'i');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'qq':
					if(!is_string($value)){
						$this->_ajaxError('参数错误');
					}elseif(!preg_match("/^[1-9]{1}[0-9]{4,10}$/", $value)){
						$this->_ajaxError('QQ格式有误');
					}
					$this->_update('qq', $value, 's');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'studentid':
					if(!is_string($value)){
						$this->_ajaxError('参数错误');
					}elseif(empty($value)){
						$this->_ajaxError('学号不能为空');
					}elseif(!preg_match("/^0121[0-9]{9}$/", $value)){
						$this->_ajaxError('学号格式有误');
					}
					$this->_update('studentid', $value, 's');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'grade':
					if(!is_string($value)){
						$this->_ajaxError('参数错误');
					}elseif(empty($value)){
						$this->_ajaxError('年级不能为空');
					}elseif(!count(DB::fetch_first('SELECT * FROM %t WHERE `id`=%d', array('profile_grades', intval($value))))){
						$this->_ajaxError('年级有误');
					}
					$this->_update('grade', $value, 'i');
					$this->_update('specialty', 0, 'i');
					$this->_update('class', 0, 'i');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'academy':
					if(!is_string($value)){
						$this->_ajaxError('参数错误');
					}elseif(empty($value)){
						$this->_ajaxError('学院不能为空');
					}elseif(!count(DB::fetch_first('SELECT * FROM %t WHERE `id`=%d', array('profile_academies', intval($value))))){
						$this->_ajaxError('学院有误');
					}
					$this->_update('academy', $value, 'i');
					$this->_update('specialty', 0, 'i');
					$this->_update('class', 0, 'i');
					$this->_update('league', '', 's');
					$this->_update('department', '', 's');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'specialty':
					if(!empty($value) && !is_string($value)){
						$this->_ajaxError('参数错误');
					}
					$this->_update('specialty', $value, 'i');
					$this->_update('class', 0, 'i');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'class':
					if(!empty($value) && !is_string($value)){
						$this->_ajaxError('参数错误');
					}
					$this->_update('class', $value, 'i');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'league':
					if(!empty($value) && !is_array($value)){
						$this->_ajaxError('参数错误');
					}
					$this->_update('league', implode(',', $value), 's');
					$this->_update('department', '', 's');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'department':
					if(!empty($value) && !is_array($value)){
						$this->_ajaxError('参数错误');
					}
					$this->_update('department', implode(',', $value), 's');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				case 'mobile':
					if(!is_string($value)){
						$this->_ajaxError('参数错误');
					}elseif(!empty($value) && !preg_match("/^1[0-9]{10}$/", $value)){
						$this->_ajaxError('手机号格式有误');
					}
					$this->_update('mobile', $value, 's');
					login($_G['username'], '', $errmsg, $_G['uid'], '', false);
					break;
				default:
					//var_dump($_POST);
					$this->_ajaxError('非法请求');
			}
			send_http_status(200);
			exit;
		}else{
			require_once libfile('client', '/uc_client');
			if(!$template->isCached('profile')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('self', 'profile'));
			}
			setToken('cgpwd');
			$template->display('profile');
		}
	}

	public function pm(){
		global $_G, $template;

		show_developing('self');

		if(IS_AJAX){
			;
		}else{
			if(!$template->isCached('pm')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('self', 'pm'));
			}
			$template->display('pm');
		}
	}

	public function cgpwd(){
		global $_G;

		$result = array(
			'errno' => 1,
			'msg' => '非法请求'
		);

		if(submitcheck('cgpwd', $result['msg'])){
			if(empty($_POST['curpwd'])){
				$result['msg'] = '原密码不能为空';
			}elseif(strlen($_POST['curpwd']) < 6){
				$result['msg'] = '密码不能短于6个字符';
			}elseif(empty($_POST['newpwd'])){
				$result['msg'] = '新密码不能为空';
			}elseif(strlen($_POST['newpwd']) < 6){
				$result['msg'] = '密码不能短于6个字符';
			}elseif($_POST['newpwd'] == $_POST['curpwd']){
				$result['msg'] = '新密码不能与原密码相同';
			}elseif(empty($_POST['rpwd'])){
				$result['msg'] = '确认密码不能为空';
			}elseif($_POST['newpwd'] !== $_POST['rpwd']){
				$result['msg'] = '确认密码与原密码不一致';
			}else{
				require_once libfile('function/logging');
				$errno = edituser($_G['username'], $_POST['curpwd'], $_POST['newpwd']);
				if($errno == 1){
					$result['errno'] = 0;
					$result['msg'] = '密码修改成功';
				}elseif($errno == 0 || $errno == -7){
					$result['msg'] = '没有做任何修改';
				}elseif($errno == -1){
					$result['msg'] = '旧密码不正确';
				}elseif($errno == -8){
					$result['msg'] = '用户受保护无权限更改';
				}
			}

			$result['msg'] = ($result['errno'] ? '<h4 class="text-danger">密码修改失败</h4>' : '<h4 class="text-success">密码修改成功</h4>')."<p>{$result['msg']}</p>";
		}

		$result['msg'] = lang('template', $result['msg']);

		ajaxReturn($result, 'AUTO');
	}

	private function _update($name, $val, $type, $table='users_profile'){
		global $_G;
		return DB::query("UPDATE %t SET `{$name}`=%{$type} WHERE `uid`=%d LIMIT 1", array(
			$table,
			$val,
			$_G['uid']
		));
	}

	private function _ajaxError($msg){
		send_http_status(400);
		exit($msg);
	}

}
