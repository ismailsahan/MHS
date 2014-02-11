<?php

/**
 * 用户登录、注册、找回密码模块
 * 
 * 版本 v0.1.0
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class LoggingAction extends Action {
	public $default_method = 'login';
	public $allowed_method = array('login', 'register', 'forgotpwd', 'resetpwd', 'activate', 'expired', 'locked');

	const USER_UNACTIVATED = 0;
	const USER_AVAILABLE = 1;
	const USER_EXAMINING = 2;
	const USER_BANNED = 3;

	public function __construct(){
		global $_G;
		require libfile('function/logging');

		if(!$_G['setting']['nocacheheaders']) {
			@header("Expires: -1");
			@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			@header("Pragma: no-cache");
		}
	}

	public function index(){
		redirect(U('logging/login'));
		return;
	}

	/**
	 * 登录
	 */
	public function login(){
		global $_G, $template;

		$errmsg = '';

		if(submitcheck('Login', $errmsg)){//登录
			$username = $_POST['username'];
			$password = $_POST['password'];

			if(empty($username)){//空用户名
				$errmsg = 'username_required';
			}elseif($username != addslashes($username)){//用户名初步安全检测不合格
				$errmsg = 'username_illegal';
			}elseif(empty($password) ){//空密码
				$errmsg = 'password_required';
			}elseif(strlen($password) < 6){//密码长度不够
				$errmsg = 'password_minlength';
			}elseif($password != addslashes($password)){//密码初步安全检测不合格
				$errmsg = 'password_illegal';
			}else{
				login($username, $password, $errmsg);
			}
		}elseif(empty($errmsg) && (IS_POST || !empty($_POST)) && !defined('CC_REQUEST')){
			$errmsg = 'invalid_request';
		}

		setToken('Login');

		//$template->assign('logintip', empty($_G['setting']['logintip']) ? '' : $_G['setting']['logintip'][array_rand($_G['setting']['logintip'])], true);
		$template->assign('errmsg', empty($errmsg) ? '' : lang('logging', $errmsg), true);

		$template->display('login');
	}

	/**
	 * 注销/登出
	 */
	public function logout(){
		return logout();
	}

	/**
	 * 注册
	 */
	public function register(){
		global $_G, $template;

		if(!$_G['setting']['regopen']){
			return $template->display('register_closed');
		}

		if(submitcheck('Register', $errmsg)){//登录
			$username = $_POST['username'];
			$password = $_POST['password'];
			$rpassword = $_POST['rpassword'];
			$email = $_POST['email'];

			if(empty($username)){							// 空用户名
				$errmsg = 'username_required';
			}elseif($username != addslashes($username)){	// 用户名初步安全检测不合格
				$errmsg = 'username_illegal';
			}elseif(empty($password)){						// 空密码
				$errmsg = 'password_required';
			}elseif(strlen($password) < 6){					// 密码长度不够
				$errmsg = 'password_minlength';
			}elseif($password != addslashes($password)){	// 密码初步安全检测不合格
				$errmsg = 'password_illegal';
			}elseif($rpassword !== $password){				// 确认密码与密码不一致
				$errmsg = 'rpassword_notmatch';
			}elseif(empty($email)){							// 空邮箱
				$errmsg = 'email_required';
			}else{
				$uid = reguser($username, $password, $email);
				switch($uid) {
					case -1:	// 用户名不合法
						$errmsg = 'username_illegal';
						break;
					case -2:	// 包含不允许注册的词语
						$errmsg = 'username_notallowed';
						break;
					case -3:	// 用户名已经存在
						$errmsg = 'username_exists';
						break;
					case -4:	// Email 格式有误
						$errmsg = 'email_illegal';
						break;
					case -5:	// Email 不允许注册
						$errmsg = 'email_notallowed';
						break;
					case -6:	// 该 Email 已经被注册
						$errmsg = 'email_exists';
						break;
					case  0:	// 未知错误
						$errmsg = 'unknown_error';
						break;
					default:	// 注册成功
						login($username, null, $errmsg, $uid, $email, false);
						$template->assign('msg', lang('logging', 'register_success', array($uid, $username)), true);
						return $template->display('register_success');
						break;
				}
			}
		}elseif(empty($errmsg) && (IS_POST || !empty($_POST)) && !defined('CC_REQUEST')){
			$errmsg = 'invalid_request';
		}

		setToken('Register');
		$template->assign('errmsg', empty($errmsg) ? '' : lang('logging', $errmsg), true);
		$template->display('register');
	}

	/**
	 * 找回密码
	 */
	public function forgotpwd(){
		global $template;

		if(submitcheck('Forgotpwd')) {
			;
		}

		setToken('Forgotpwd');
		$template->assign('errmsg', empty($errmsg) ? '' : lang('logging', $errmsg), true);
		$template->display('forgotpwd');
	}

	/**
	 * 重置密码
	 */
	public function resetpwd(){
		global $template;
		setToken('Resetpwd');
		$template->assign('errmsg', empty($errmsg) ? '' : lang('logging', $errmsg), true);
		$template->display('resetpwd');
	}

	/**
	 * 激活账号
	 */
	public function activate(){
		global $_G, $template;

		if(!$_G['uid']){					// 未登录
			redirect(U('logging/login'));
		}

		if($_G['member']['activated']){		// 已激活
			redirect(U('main/index'));
		}

		if(!$_G['setting']['actopen']){		// 关闭激活
			return IS_AJAX ? ajaxReturn(array('errno'=>32, 'msg'=>lang('logging', 'activate_closed'))) : $template->display('activate_closed');
		}

		if(IS_AJAX && IS_POST){
			$errmsg = '';
			$data = array(
				'errno' => -1,
				'msg' => ''
			);
			if(submitcheck('Activate', $errmsg, 1)){
				if(empty($_POST['realname'])){																								// 空真实姓名
					$errmsg = 'realname_required';
				}elseif(empty($_POST['gender'])){																							// 空性别
					$errmsg = 'gender_required';
				}elseif(!in_array($_POST['gender'], array('1', '2'))){																		// 性别不合法
					$errmsg = 'gender_illeagal';
				}elseif(empty($_POST['qq'])){																								// 空QQ
					$errmsg = 'qq_required';
				}elseif(!preg_match("/^[1-9]{1}[0-9]{4,10}$/", $_POST['qq'])){																// QQ格式不正确
					$errmsg = 'qq_illeagal';
				}elseif(empty($_POST['studentid'])){																						// 空学号
					$errmsg = 'studentid_required';
				}elseif(!preg_match("/^0121[0-9]{9}$/", $_POST['studentid'])){																// 学号不合法
					$errmsg = 'studentid_illeagal';
				}elseif(empty($_POST['grade'])){																							// 空年级
					$errmsg = 'grade_required';
				}elseif(!count(DB::fetch_first('SELECT * FROM %t WHERE `id`=%d', array('profile_grades', intval($_POST['grade']))))){		// 年级不合法
					$errmsg = 'grade_illeagal';
				}elseif(empty($_POST['academy'])){																							// 空学院
					$errmsg = 'academy_required';
				}elseif(!count(DB::fetch_first('SELECT * FROM %t WHERE `id`=%d', array('profile_academies', intval($_POST['academy']))))){	// 学院不合法
					$errmsg = 'academy_illeagal';
				}else{
					DB::query('REPLACE INTO %t (`uid`, `email`, `username`, `status`, `submittime`, `verifytime`, `realname`, `gender`, `qq`, `studentid`, `grade`, `academy`, `specialty`, `class`, `organization`, `league`, `department`, `remark`, `operator`,`operatorname`, `verifytext`) VALUES (%d, %s, %s, %d, %d, %d, %s, %d, %s, %s, %d, %d, %d, %d, %s, %s, %s, %s, %d, %s, %s)', array(
						'activation',						// 表名
						$_G['uid'],							// 用户ID
						$_G['member']['email'],				// 邮箱
						$_G['username'],					// 用户名
						0,									// 审核状态，0表示审核中，1表示通过审核，2表示未通过审核
						TIMESTAMP,							// 申请时间
						0,									// 审核时间
						$_POST['realname'],					// 真实名字
						$_POST['gender'],					// 性别，1男 2女
						$_POST['qq'],						// QQ号码
						$_POST['studentid'],				// 学号
						$_POST['grade'],					// 年级ID
						$_POST['academy'],					// 学院ID
						$_POST['specialty'],				// 专业ID
						$_POST['class'],					// 班级ID
						'',									// 组织ID
						implode(',', $_POST['league']),		// 社团ID
						implode(',', $_POST['department']), // 部门ID
						htmlentities($_POST['remarks']),	// 留言
						0,									// 审核员ID
						'',									// 审核员用户名
						''									// 审核信息
					));
					$data['errno'] = 0;
					$data['msg'] = '申请成功！请耐心等待审核';

					if($_G['uid'] == 1){
						adduser(1, array(
							'status'			=> 1,
							'emailstatus'		=> 0,
							'avatarstatus'		=> 0,
							'videophotostatus'	=> 0,
							'adminid'			=> 1,
							'groupid'			=> 1,
							'groupexpiry'		=> 0,
							'extgroupids'		=> '',
							'regdate'			=> TIMESTAMP,
							'credits'			=> 0,
							'timeoffset'		=> 8,
							'newpm'				=> '',
							'newprompt'			=> '',
							'accessmasks'		=> '',
							'allowadmincp'		=> 1,
							'conisbind'			=> 0
						));
						$data['msg'] = '已通过审核';
					}

					require_once libfile('class/Mail');
					Mail::init();
					Mail::addAddress($_G['member']['email']);
					Mail::setMsg('我们已经收到您提交的申请，请耐心等候审核结果，多谢合作！以下是您申请的信息：<br/>'.nl2br(var_export($_POST, true), '激活申请'));
					$errno = Mail::send();

					if($errno) {
						$data['msg'] .= '<br/>但'.$errno;
						//$data['errno']=5;
					}
				}
			}else{
				switch($errmsg){
					case 'token_expired'	: $data['errno']=2;break;
					case 'seccode_incorrect': $data['errno']=1;break;
					case 'secqaa_incorrect' : break;
					case 'undefined_err'	: break;
					default:
				}
			}

			if($errmsg){
				$data['msg']=lang('logging', $errmsg);
			}
			ajaxReturn($data, 'JSON');
		}

		$auditInfo = DB::fetch_first('SELECT `status`,`verifytime`,`operatorname`,`verifytext` FROM %t WHERE `uid`=%d LIMIT 1', array('activation', $_G['uid']));
		$status = isset($auditInfo['status']) ? $auditInfo['status'] : -1;
		$auditInfo['verifytime'] = isset($auditInfo['verifytime']) ? dgmdate($auditInfo['verifytime']) : '';
		$auditInfo['verifytext'] = isset($auditInfo['verifytext']) ? stripcslashes($auditInfo['verifytext']) : '';

		setToken('Activate');
		$template->assign('status', $status, true);
		$template->assign('auditInfo', $auditInfo, true);

		$template->display('activate');
	}

	/**
	 * 会话超时
	 */
	public function expired(){
		return $this->locked(true);
	}

	/**
	 * 锁定
	 */
	public function locked($expired=false){
		global $template, $_G;

		if(empty($_SESSION['user'])) {
			redirect(U('logging/login'));
		}

		if($_G['cookie']['auth']) dsetcookie('auth');

		$errmsg = '';

		if(submitcheck('Locked')) {
			require_once libfile('function/logging');
			login($_SESSION['user']['username'], $_POST['password'], $errmsg);
		}

		setToken('Locked');
		$template->assign('expired', $expired, true);
		$template->assign('errmsg', $errmsg, true);
		$template->display('locked');
	}

}
