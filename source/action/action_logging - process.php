<?php

/**
 * 用户登录、注册、找回密码模块
 * 
 * 版本 v0.1.0
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

$errmsg = '';

if(isset($_G['setting']['nocacheheaders']) && !$_G['setting']['nocacheheaders']) {
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
}

if(submitcheck('Login')){//登录
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
		$password = md5($password);
		DB::query("DELETE FROM %t WHERE `lastupdate`<%d", array('failedlogin', TIMESTAMP - $_G['setting']['failedlogin']['time'] * 60), 'UNBUFFERED');
		$failedlogin = DB::fetch_first("SELECT `count`,`lastupdate` FROM %t WHERE `ip`=%s OR `username`=%s", array('failedlogin', $_G['clientip'], $username));
		trace($failedlogin);
		if(isset($failedlogin['count']) && $failedlogin['count'] >= $_G['setting']['failedlogin']['count'] && $failedlogin['lastupdate'] >= TIMESTAMP - $_G['setting']['failedlogin']['time'] * 60){
			$errmsg = lang('template', 'login_frozen', array('mins' => ceil(($failedlogin['lastupdate'] - TIMESTAMP)/60 + $_G['setting']['failedlogin']['time'])));
		}else{
			$user = DB::fetch_first("SELECT * FROM ".DB::table('users')." WHERE `username`='{$username}' AND `password`='{$password}' LIMIT 1");
			if(count($user) > 0){
				$_SESSION['username'] = $username;
				if(!empty($failedlogin)) DB::query("DELETE FROM %t WHERE `ip`=%s", array('failedlogin', $_G['clientip']), 'UNBUFFERED');
				redirect($_G['basefilename'].'?action=main');
				exit;
			}else{
				//if(isset($failedlogin['count']) && $failedlogin['count'] >= $_G['setting']['failedlogin']['count']) $failedlogin['count'] = 1;
				if(empty($failedlogin))
					DB::query("INSERT INTO %t (`ip`, `username`, `count`, `lastupdate`) VALUES (%s, %s, %d, %d)", array('failedlogin',  $_G['clientip'], $username, 1, TIMESTAMP));
				else
					DB::query("UPDATE %t SET `count`=`count`+1, `lastupdate`=%d WHERE `ip`=%s OR `username`=%s", array('failedlogin', TIMESTAMP, $_G['clientip'], $username));
				$errmsg = 'login_incorrect';
				$errmsg .= lang('template', 'login_failed_tip', array('count' => ($_G['setting']['failedlogin']['count'] - 1 - (isset($failedlogin['count']) ? $failedlogin['count'] : 0))));
			}
		}
	}
}elseif(submitcheck('Register')){//注册
	;
}elseif(submitcheck('ForgotPwd')){//忘记密码
	;
}elseif(submitcheck('ResetPwd')){//重置密码
	;
}elseif(submitcheck('Activate')){//激活账号
	;
}else{
	if(empty($errmsg) && (IS_POST || !empty($_POST)) && !defined('CC_REQUEST')){
		$errmsg = 'invalid_request';
	}
}

$errmsg = empty($errmsg) ? '' : lang('template', $errmsg);

setToken('Login');
setToken('Register');
setToken('ForgotPwd');

$template->assign('logintip', empty($_G['setting']['logintip']) ? '' : $_G['setting']['logintip'][array_rand($_G['setting']['logintip'])]);

$template->display('login');