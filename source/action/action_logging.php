<?php

/**
 * 用户登录、注册、找回密码模块
 *
 * 版本 v0.1.0
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class LoggingAction extends Action {
	public $allowed_method = array('login', 'register', 'forgotpwd', 'resetpwd', 'activate', 'expired', 'locked');

	const USER_UNACTIVATED = 0;
	const USER_AVAILABLE = 1;
	const USER_EXAMINING = 2;
	const USER_BANNED = 3;

	public function __construct(){
		global $_G;
		require libfile('function/logging');

		if(!$_G['setting']['nocacheheaders']) {
			@header('Expires: -1');
			@header('Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0', FALSE);
			@header('Pragma: no-cache');
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

		if(isset($_GET['noaccess'])) {
			$errmsg = 'siteclosed_logindenied';
		} elseif(isset($_GET['siteclosed'])) {
			$errmsg = 'siteclosed';
		} elseif(isset($_GET['msg'])) {
			$errmsg = urldecode($_GET['msg']);
		} else {
			$errmsg = '';
		}

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
			}elseif(!isemail($email)){						// 邮箱格式不正确
				$errmsg = 'email_illegal';
			}else{
				$errno = checkemail($email);
				if($errno === -4){
					$errmsg = 'email_illegal';
				}elseif($errno === 1){
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
							//login($username, null, $errmsg, $uid, $email, false);
							$template->assign('msg', lang('logging', 'register_success', array($uid, $username)), true);
							return $template->display('register_success');
							break;
					}
				}elseif($errno === -5){
					$errmsg = 'email_notallowed';
				}elseif($errno === -6){
					$errmsg = 'email_exists';
				}else{
					$errmsg = 'unknown_error';
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
		$errmsg = '';

		if(submitcheck('ForgotPwd', $errmsg)) {
			$email = $_POST['email'];

			if(empty($email)){								// 空邮箱
				$errmsg = 'email_required';
			}elseif(!isemail($email)){						// Email 格式错误
				$errmsg = 'email_illegal';
			}else{
				$username = DB::result_first('SELECT `username` FROM %t WHERE `email`=%s LIMIT 1', array('users', $email));
				if(empty($username)){						// 账号不存在
					$errmsg = 'email_inexists';
				}else{
					$newpw = rand_string(16);
					$result = edituser($username, null, $newpw, null, true);

					if($result == 1) {
						require_once libfile('class/Mail');
						Mail::init();
						Mail::addAddress($email);
						Mail::setMsg("您的密码已重置为 <b>{$newpw}</b>，请使用新密码登录，并尽快修改密码", '重置密码');
						$error = Mail::send();
					}

					return $template->display($result == 1 ? 'forgotpwd_success' : 'forgotpwd_error');
				}
			}
		}

		setToken('ForgotPwd');
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
			showlogin();
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
				'msg' => '非法操作'
			);
			if(submitcheck('Activate', $errmsg, 1)){
				if(empty($_POST['realname'])){																								// 空真实姓名
					$errmsg = 'realname_required';
				}elseif(empty($_POST['gender'])){																							// 空性别
					$errmsg = 'gender_required';
				}elseif(!in_array($_POST['gender'], array('1', '2'))){																		// 性别不合法
					$errmsg = 'gender_illeagal';
				}elseif(!empty($_POST['qq']) && !preg_match("/^[1-9]{1}[0-9]{4,10}$/", $_POST['qq'])){																// QQ格式不正确
					$errmsg = 'qq_illeagal';
				}elseif(empty($_POST['studentid'])){																						// 空学号
					$errmsg = 'studentid_required';
				}elseif(!preg_match("/^0121[0-9]{9}$/", $_POST['studentid'])){																// 学号不合法
					$errmsg = 'studentid_illeagal';
				}elseif(empty($_POST['grade'])){																							// 空年级
					$errmsg = 'grade_required';
				}elseif(!in_array($_POST['grade'], explode(',', $_G['setting']['grades']))){		// 年级不合法
					$errmsg = 'grade_illeagal';
				}elseif(empty($_POST['academy'])){																							// 空学院
					$errmsg = 'academy_required';
				}elseif(!DB::result_first('SELECT count(*) FROM %t WHERE `id`=%d', array('profile_academies', $_POST['academy']))){	// 学院不合法
					$errmsg = 'academy_illeagal';
				}else{
					if($_G['uid'] > 1 && !DB::result_first('SELECT count(*) FROM %t WHERE `status`=0', array('activation'))) {
						// Notice Admins
					}

					$_POST['remarks'] = htmlspecialchars($_POST['remarks']);
					$_POST['specialty'] = htmlspecialchars($_POST['specialty']);
					$_POST['class'] = htmlspecialchars($_POST['class']);
					$_POST['league'] = dhtmlspecialchars($_POST['league']);
					$_POST['department'] = dhtmlspecialchars($_POST['department']);

					DB::query('REPLACE INTO %t (`uid`, `email`, `username`, `status`, `submittime`, `verifytime`, `realname`, `gender`, `qq`, `studentid`, `grade`, `academy`, `specialty`, `class`, `organization`, `league`, `department`, `remark`, `operator`,`operatorname`, `verifytext`) VALUES (%d, %s, %s, %d, %d, %d, %s, %d, %s, %s, %d, %d, %s, %s, %s, %s, %s, %s, %d, %s, %s)', array(
						'activation',						// 表名
						$_G['uid'],							// 用户ID
						$_G['member']['email'],				// 邮箱
						$_G['username'],					// 用户名
						$_G['uid']===1 ? 1 : 0,				// 审核状态，0等待审核，1通过审核，2未通过审核
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
						$_POST['remarks'],					// 留言
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
							'groupid'			=> 0,
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
						DB::query('UPDATE %t SET `status`=1, `verifytime`=%d WHERE `uid`=%d LIMIT 1', array('activation', TIMESTAMP, $_G['uid']));
						$data['msg'] = '已通过审核';
					}elseif($_G['setting']['autoactivate']){
						adduser($_G['uid']);
						DB::query('UPDATE %t SET `status`=1,`operator`=%d,`operatorname`=%s,`verifytime`=%d,`verifytext`=%s WHERE `uid`=%d LIMIT 1', array('activation', 0, 'System', TIMESTAMP, 'System Auto Activate', $_G['uid']));
						$data['msg'] = '您已激活成功！<br />您可能需要先注销会话(右上角)，再重新登录才能进入系统界面';
					}

					$_POST['time'] = dgmdate(TIMESTAMP);
					$_POST['gender'] = $_POST['gender']=='1' ? '男' : '女';
					$_POST['academy'] = DB::result_first('SELECT `name` FROM %t WHERE `id`=%d LIMIT 1', array('profile_academies', intval($_POST['academy'])));
					$_POST['specialty'] = $_POST['specialty'] ? $_POST['specialty'] : '-';
					$_POST['class'] = $_POST['class'] ? $_POST['class'] : '-';
					$_POST['league'] = empty($_POST['league']) ? '-' : implode(', ', $_POST['league']);
					$_POST['department'] = empty($_POST['department']) ? '-' : implode(', ', $_POST['department']);
					$_POST['remarks'] = $_POST['remarks'] ? nl2br($_POST['remarks']) : '-';

					$_SESSION['acteml'] = $_POST;

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

		$auditInfo = DB::fetch_first('SELECT * FROM %t WHERE `uid`=%d LIMIT 1', array('activation', $_G['uid']));
		//$auditInfo = DB::fetch_first('SELECT `status`,`verifytime`,`operatorname`,`verifytext` FROM %t WHERE `uid`=%d LIMIT 1', array('activation', $_G['uid']));
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

		if(empty($_SESSION['user']) || empty($_SESSION['user']['uid'])) {
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

	/**
	 * 发送激活邮件
	 */
	public function sendeml() {
		global $_G;
		define('DISABLE_TRACE', true);
		if(empty($_SESSION['acteml'])) exit;

		$title = '激活申请';
		$text = '我们已经收到您提交的申请，请耐心等候审核结果，多谢合作！以下是您申请的信息：<br/>';
		$text .= '<table border="1" style="width:100%;text-align:left">';
		$text .=	'<tr><th style="width:5em">项</th><th>申请信息</th></tr>';
		$text .=	"<tr><td>用户ID</td><td>{$_G['uid']}</td></tr>";
		$text .=	"<tr><td>用户名</td><td>{$_G['username']}</td></tr>";
		$text .=	"<tr><td>申请时间</td><td>{$_SESSION['acteml']['time']}</td></tr>";
		$text .=	"<tr><td>真实名字</td><td>{$_SESSION['acteml']['realname']}</td></tr>";
		$text .=	"<tr><td>性别</td><td>{$_SESSION['acteml']['gender']}</td></tr>";
		$text .=	"<tr><td>QQ号码</td><td>{$_SESSION['acteml']['qq']}</td></tr>";
		$text .=	"<tr><td>学号</td><td>{$_SESSION['acteml']['studentid']}</td></tr>";
		$text .=	"<tr><td>年级</td><td>{$_SESSION['acteml']['grade']}</td></tr>";
		$text .=	"<tr><td>学院</td><td>{$_SESSION['acteml']['academy']}</td></tr>";
		$text .=	"<tr><td>专业</td><td>{$_SESSION['acteml']['specialty']}</td></tr>";
		$text .=	"<tr><td>班级</td><td>{$_SESSION['acteml']['class']}</td></tr>";
		$text .=	"<tr><td>社团</td><td>{$_SESSION['acteml']['league']}</td></tr>";
		$text .=	"<tr><td>部门</td><td>{$_SESSION['acteml']['department']}</td></tr>";
		$text .=	"<tr><td>留言</td><td>{$_SESSION['acteml']['remarks']}</td></tr>";
		$text .= '</table>';
		//$data['msg'] .= '<br/>'.$text;$data['errno']=1;ajaxReturn($data, 'JSON');exit;

		require_once libfile('class/Mail');
		Mail::init();
		Mail::addAddress($_G['member']['email']);
		Mail::setMsg($text, $title);
		$errno = Mail::send();

		if($errno) {
			;
		}else{
			unset($_SESSION['acteml']);
		}
	}

}
