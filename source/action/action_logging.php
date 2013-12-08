<?php

/**
 * 用户登录、注册、找回密码模块
 * 
 * 版本 v0.1.0
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class LoggingAction {
	public $default_method = 'login';
	public $allowed_method = array('login', 'register', 'forgotpwd', 'resetpwd', 'activate', 'expired', 'locked');

	const USER_UNACTIVATED = 0;
	const USER_AVAILABLE = 1;
	const USER_EXAMINING = 2;
	const USER_BANNED = 3;

	public function __construct(){
		global $_G;

		if(isset($_G['setting']['nocacheheaders']) && !$_G['setting']['nocacheheaders']) {
			@header("Expires: -1");
			@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			@header("Pragma: no-cache");
		}
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
			}elseif($password != addslashes($password)){//用户名初步安全检测不合格
				$errmsg = 'username_illegal';
			}elseif(empty($password) ){//空密码
				$errmsg = 'password_required';
			}elseif(strlen($password) < 6){//密码长度不够
				$errmsg = 'password_minlength';
			}elseif($password != addslashes($password)){//密码初步安全检测不合格
				$errmsg = 'password_illegal';
			}else{
				DB::query("DELETE FROM %t WHERE `lastupdate`<%d", array('failedlogin', TIMESTAMP - $_G['setting']['failedlogin']['time'] * 60), 'UNBUFFERED');
				$failedlogin = DB::fetch_first("SELECT `count`,`lastupdate` FROM %t WHERE `ip`=%s OR `username`=%s", array('failedlogin', $_G['clientip'], $username));
				if(isset($failedlogin['count']) && $failedlogin['count'] >= $_G['setting']['failedlogin']['count'] && $failedlogin['lastupdate'] >= TIMESTAMP - $_G['setting']['failedlogin']['time'] * 60){
					$errmsg = lang('template', 'login_frozen', array('mins' => ceil(($failedlogin['lastupdate'] - TIMESTAMP)/60 + $_G['setting']['failedlogin']['time'])));
				}else{
					include_once libfile('client', '/uc_client');
					$user = uc_user_login($username, $password, 0);
					$falselogin = false;
					if($user[0] > 0){//用户名与密码匹配
						$uid = $user[0];
						$email = $user[3];
						$user = DB::fetch_first("SELECT * FROM %t WHERE `uid`=%d LIMIT 1", array('users', $uid));

						$session = array();
						$session['uid'] = $uid;
						$session['username'] = $username;
						$session['email'] = $email;
						$session['authkey'] = $_G['authkey'];
						$session['expiry'] = TIMESTAMP;
						$session['activated'] = count($user) > 0 ? true : false;

						if(!empty($failedlogin)) DB::query("DELETE FROM %t WHERE `ip`=%s", array('failedlogin', $_G['clientip']), 'UNBUFFERED');

						if($session['activated']){
							$user = DB::fetch_first("SELECT * FROM %t WHERE `uid`=%d LIMIT 1", array('users_profile', $uid));
							$_SESSION['user'] = $session;
							redirect($_G['basefilename'].'?action=main');
							exit;
						}else{
							$_SESSION['user'] = $session;
							redirect($_G['basefilename'].'?action=logging&operation=activate');
							exit;
						}
					}elseif($user[0] == -1){//用户不存在，或者被删除
						$falselogin = true;
					}elseif($user[0] == -2){//密码错
						$falselogin = true;
					}elseif($user[0] == -3){//安全提问错
						$falselogin = true;
						$errmsg = 'SECQAA_ERR';
					}else{//未知错误
						$errmsg = 'LOGIN_UNDEFINED_ERR';
					}

					if($falselogin){
						if(empty($failedlogin))
							DB::query("INSERT INTO %t (`ip`, `username`, `count`, `lastupdate`) VALUES (%s, %s, %d, %d)", array('failedlogin',  $_G['clientip'], $username, 1, TIMESTAMP));
						else
							DB::query("UPDATE %t SET `count`=`count`+1, `lastupdate`=%d WHERE `ip`=%s OR `username`=%s", array('failedlogin', TIMESTAMP, $_G['clientip'], $username));
						$errmsg = lang('template', empty($errmsg) ? 'login_incorrect' : $errmsg);
						$errmsg .= lang('template', 'login_failed_tip', array('count' => ($_G['setting']['failedlogin']['count'] - 1 - (isset($failedlogin['count']) ? $failedlogin['count'] : 0))));
					}
				}
			}
		}elseif(empty($errmsg) && (IS_POST || !empty($_POST)) && !defined('CC_REQUEST')){
			$errmsg = 'invalid_request';
		}

		setToken('Login');
		setToken('Register');
		setToken('ForgotPwd');

		$template->assign('logintip', empty($_G['setting']['logintip']) ? '' : $_G['setting']['logintip'][array_rand($_G['setting']['logintip'])], true);
		$template->assign('errmsg', empty($errmsg) ? '' : lang('template', $errmsg), true);

		$template->display('login');
	}

	/**
	 * 注册
	 */
	public function register(){
		;
	}

	/**
	 * 找回密码
	 */
	public function forgotpwd(){
		;
	}

	/**
	 * 重置密码
	 */
	public function resetpwd(){
		;
	}

	/**
	 * 激活账号
	 */
	public function activate(){
		global $_G, $template;
		$errmsg = '';

		if(submitcheck('Activate', $errmsg)){

		}

		setToken('Activate');
		$template->assign('errmsg', empty($errmsg) ? '' : lang('template', $errmsg), true);
		$template->display('activate');
	}

	/**
	 * 会话超时
	 */
	public function expired(){
		return $this->locked('session_expired');
	}

	/**
	 * 锁定
	 */
	public function locked($msg = ''){
		global $template;
		$template->assign('msg', $msg, true);
		$template->display('locked');
	}

	/**
	 * 添加用户
	 * @param int $uid
	 */
	private function adduser($uid, $username, $email){
		include_once libfile('client', '/uc_client');
		return DB::query("INSERT INTO %t (`uid`, `email`, `username`, `password`, `status`, `emailstatus`, `avatarstatus`, `videophotostatus`, `adminid`, `groupid`, `groupexpiry`, `extgroupids`, `regdate`, `credits`, `timeoffset`, `newpm`, `newprompt`, `accessmasks`, `allowadmincp`, `conisbind`) VALUES (%d, %s, %s, '', %d, '0', %d, '0', '0', '0', '0', '', %d, '0', '', '0', '0', '0', '0', '0')", array('users', $uid, $email, $username, self::USER_AVAILABLE, uc_check_avatar($uid), TIMESTAMP));
	}
}
