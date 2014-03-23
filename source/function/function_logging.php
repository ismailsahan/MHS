<?php

/**
 * 登录
 * 
 * @param string $username	用户名
 * @param string $password	密码
 * @param string $errmsg	错误信息
 * @param int    $uid		用户ID
 * @param string $email		E-mail
 * @param bool   $redirect  登录后是否跳转
 * @return boolean|null
 * 
 * @example 正常登录 login($username, $password, $errmsg)
 * @example 直接登录 login($username, null, $errmsg, $uid, $email)
 * 
 */
function login($username, $password='', &$errmsg='', $uid=0, $email='', $redirect=true) {
	global $_G;
	if($uid == 0/* || empty($email)*/) {	// 正常登录
		DB::query('DELETE FROM %t WHERE `lastupdate`<%d', array('failedlogin', TIMESTAMP - $_G['setting']['failedlogin']['time'] * 60), 'UNBUFFERED');
		$failedlogin = DB::fetch_first('SELECT `count`,`lastupdate` FROM %t WHERE `ip`=%s OR `username`=%s LIMIT 1', array('failedlogin', $_G['clientip'], $username));
		if(isset($failedlogin['count']) && $failedlogin['count'] >= $_G['setting']['failedlogin']['count'] && $failedlogin['lastupdate'] >= TIMESTAMP - $_G['setting']['failedlogin']['time'] * 60){
			$errmsg = lang('logging', 'login_frozen', array('mins' => ceil(($failedlogin['lastupdate'] - TIMESTAMP)/60 + $_G['setting']['failedlogin']['time'])));
		}else{
			include_once libfile('client', '/uc_client');
			$user = uc_user_login($username, $password, isemail($username) ? 2 : 0);
			$falselogin = false;
			if($user[0] > 0){//用户名与密码匹配
				$uid = $user[0];
				$username = $user[1];
				$email = $user[3];
				return login($username, null, $errmsg, $uid, $email, $redirect);
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
					DB::query('INSERT INTO %t (`ip`, `username`, `count`, `lastupdate`) VALUES (%s, %s, %d, %d)', array('failedlogin',  $_G['clientip'], $username, 1, TIMESTAMP));
				else
					DB::query('UPDATE %t SET `count`=`count`+1, `lastupdate`=%d WHERE `ip`=%s OR `username`=%s LIMIT 1', array('failedlogin', TIMESTAMP, $_G['clientip'], $username));
				$errmsg = lang('logging', empty($errmsg) ? 'login_incorrect' : $errmsg);
				$errmsg .= lang('logging', 'login_failed_tip', array('count' => ($_G['setting']['failedlogin']['count'] - 1 - (isset($failedlogin['count']) ? $failedlogin['count'] : 0))));
			}
		}
		return false;
	} else {	// 直接登录
		$user = DB::fetch_first('SELECT * FROM %t WHERE `uid`=%d LIMIT 1', array('users', $uid));
		DB::query('UPDATE %t SET `lastlogin`=%d WHERE `uid`=%d LIMIT 1', array('users', TIMESTAMP, $uid));

		$session = array();
		$session['uid'] = $uid;
		$session['username'] = $username ? $username : $user['username'];
		$session['email'] = $email ? $email : $user['email'];
		$session['authkey'] = $_G['authkey'];
		$session['expiry'] = TIMESTAMP;
		$session['activated'] = count($user) > 0 ? true : false;

		dsetcookie('auth', authcode("{$uid}\t{$username}\t{$email}", 'ENCODE'), 0, 1, 1);

		if(!empty($failedlogin)) DB::query('DELETE FROM %t WHERE `ip`=%s LIMIT 1', array('failedlogin', $_G['clientip']), 'UNBUFFERED');

		if($session['activated']){
			$profile = DB::fetch_first('SELECT * FROM %t WHERE `uid`=%d LIMIT 1', array('users_profile', $uid));
			$_SESSION['user'] = array_merge($profile, $user, $session);
			$url = empty($_G['referer']) ? U('main/index') : $_G['referer'];
		}else{
			$_SESSION['user'] = $session;
			$url = U('logging/activate');
		}

		if($redirect){
			if(IS_AJAX){
				ajaxReturn(array(
					'errno' => 0,
					'url' => $url
				));
				exit;
			}else{
				redirect($url);
				exit;
			}
		}

		return true;
	}
}

/**
 * 注销/登出
 */
function logout(){
	dsetcookie('auth');
	//unset($_SESSION['user']);
	$keys = array_keys($_SESSION);
	foreach($keys as $k){
		unset($_SESSION[$k]);
	}

	if(IS_AJAX){
		ajaxReturn(array(
			'errno' => 0,
			'msg' => '注销成功！'
		));
		exit;
	}else{
		redirect(U('logging/login'));
		exit;
	}
}

/**
 * 注册用户（但不激活）
 * 
 * @param string $username 用户名
 * @param string $email    电子邮箱
 * @param string $password 密码
 * @return int 用户ID（大于0，表示用户注册成功）或错误码
 *         		-1 用户名不合法
 * 		        -2 包含不允许注册的词语
 * 				-3 用户名已经存在
 * 				-4 Email 格式有误
 * 				-5 Email 不允许注册
 * 				-6 该 Email 已经被注册
 */
function reguser($username, $password, $email){
	require_once libfile('client', '/uc_client');
	return uc_user_register($username, $password, $email);
}

/**
 * 激活用户
 * 
 * @param int   $uid 用户ID
 * @param array $user 用户属性，包括用户组、管理组等
 * @param array $profile 用户资料
 * @return boolean
 */
function adduser($uid, $user=array(), $profile=array()){
	if(empty($profile)) $profile = DB::fetch_first('SELECT * FROM %t WHERE `uid`=%d LIMIT 1', array('activation', $uid));
	if(empty($profile)) return false;
	if(empty($user)) $user = array(
		'status'			=> 1,
		'emailstatus'		=> 0,
		'avatarstatus'		=> 0,
		'videophotostatus'	=> 0,
		'adminid'			=> 0,
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
	);
	DB::query('REPLACE INTO %t (`uid`, `email`, `username`, `password`, `status`, `emailstatus`, `avatarstatus`, `videophotostatus`, `adminid`, `groupid`, `groupexpiry`, `extgroupids`, `regdate`, `credits`, `timeoffset`, `newpm`, `newprompt`, `accessmasks`, `allowadmincp`, `conisbind`) VALUES (%d, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %s, %d, %d, %d, %d, %d, %d, %d, %d)', array(
		'users', 					// 表
		$uid,						// 用户ID
		$profile['email'],			// 电子邮箱
		$profile['username'],		// 用户名
		'',							// 密码
		$user['status'],			// 账号状态
		$user['emailstatus'],		// 邮箱验证状态
		$user['avatarstatus'],		// 头像状态
		$user['videophotostatus'],	// 视频认证状态
		$user['adminid'],			// 管理组ID
		$user['groupid'],			// 用户组ID
		$user['groupexpiry'],		// 用户组过期时间
		$user['extgroupids'],		// 附属用户组ID
		$user['regdate'],			// 注册时间
		$user['credits'],			// 积分
		$user['timeoffset'],		// 自定义时区
		$user['newpm'],				// 
		$user['newprompt'],			// 
		$user['accessmasks'],		// 
		$user['allowadmincp'],		// 允许进入后台
		$user['conisbind']			// 绑定QQ状态
	));
	DB::query('REPLACE INTO %t (`uid`, `realname`, `gender`, `qq`, `studentid`, `grade`, `academy`, `specialty`, `class`, `organization`, `league`, `department`) VALUES (%d, %s, %d, %s, %s, %d, %d, %d, %d, %s, %s, %s)', array(
		'users_profile',			// 表
		$uid,						// 用户ID
		$profile['realname'],		// 真实姓名
		$profile['gender'],			// 性别
		$profile['qq'],				// QQ
		$profile['studentid'],		// 学号
		$profile['grade'],			// 年级ID
		$profile['academy'],		// 学院ID
		$profile['specialty'],		// 专业ID
		$profile['class'],			// 班级ID
		$profile['organization'],	// 组织ID
		$profile['league'],			// 社团ID
		$profile['department']		// 部门ID
	));
	return true;
}

/**
 * 获取用户数据
 * 
 * @param string $username 用户名或用户ID
 * @param bool   $isuid    是否是UID
 * @return array 0用户ID 1用户名 2邮箱
 */
function getuser($username, $isuid=false){
	require_once libfile('client', '/uc_client');
	return uc_get_user($username, $isuid);
}

/**
 * 更新用户资料
 * 更新资料需验证用户的原密码是否正确，除非指定 ignoreoldpw 为 1
 * 如果只修改 Email 不修改密码，可让 newpw 为空
 * 同理如果只修改密码不修改 Email，可让 email 为空
 * 
 * @param string $username 		用户名
 * @param string $oldpw 		旧密码
 * @param string $newpw 		新密码，如不修改为空
 * @param string $email 		Email，如不修改为空
 * @param bool   $ignoreoldpw 	是否忽略旧密码
 * @return int  1:更新成功
 *              0:没有做任何修改
 *             -1:旧密码不正确
 *             -4:Email 格式有误
 *             -5:Email 不允许注册
 *             -6:该 Email 已经被注册
 *             -7:没有做任何修改
 *             -8:该用户受保护无权限更改
 */
function edituser($username, $oldpw, $newpw=null, $email=null, $ignoreoldpw=false){
	require_once libfile('client', '/uc_client');
	return uc_user_edit($username, $oldpw, $newpw, $email, $ignoreoldpw);
}

/**
 * 删除用户
 * 
 * @param mixed $uid 用户ID
 * @param bool  $uc  同步到UCenter
 * @return int
 */
function deluser($uid, $uc=false){
	DB::query(str_repeat('DELETE FROM %t WHERE `uid` IN (%n);', 3), array('users', $uid, 'users_profile', $uid, 'users_connect', $uid));
	if($uc){
		require_once libfile('client', '/uc_client');
		return uc_user_delete($uid);
	}
	return 1;
}

/**
 * 检测头像是否存在
 * 
 * @param integer $uid  用户ID
 * @param string  $size 头像大小 big:大头像(200x250) middle:(默认)中头像(120x120) small:小头像(48x48)
 * @param string  $type 头像类型 real:真实头像 virtual:(默认)虚拟头像
 * @return
 */
function checkavatar($uid, $size='middle', $type='virtual'){
	require_once libfile('client', '/uc_client');
	return uc_check_avatar($uid, $size, $type);
}

/**
 * 修改头像
 * 
 * @param integer $uid        用户 ID
 * @param string  $type       头像类型 real:真实头像 virtual:(默认)虚拟头像
 * @param boolean $returnhtml 是否返回 HTML 代码  1:(默认值)是，返回设置头像的 HTML 代码  0:否，返回设置头像的 Flash 调用数组
 * @return mixed
 */
function setavatar($uid, $type='virtual', $returnhtml=true){
	require_once libfile('client', '/uc_client');
	return uc_avatar($uid, $type, $returnhtml);
}

/**
 * 删除用户头像
 * 
 * @param mixed $uid 用户ID
 */
function delavatar($uid){
	DB::query('UPDATE %t SET `avatarstatus`=0 WHERE `uid` IN (%n)', array('users', $uid));
	require_once libfile('client', '/uc_client');
	return uc_user_deleteavatar($uid);
}

/** 
 * 检查 Email 地址
 * 
 * @param string $email Email地址
 * @return int 1:可以注册
 *            -4:Email 格式有误
 *            -5:Email 不允许注册
 *            -6:该 Email 已经被注册
 */
function checkemail($email){
	require_once libfile('client', '/uc_client');
	return uc_user_checkemail($email);
}

/**
 * 检查用户名
 * 
 * @param string $username 用户名
 * @return int 1:可以注册
 *            -1:用户名不合法
 *            -2:包含要允许注册的词语
 *            -3:用户名已经存在
 */
function checkname($username){
	require_once libfile('client', '/uc_client');
	return uc_user_checkname($username);
}

/**
 * 添加保护用户
 * 
 * @param mixed  $username 保护用户名 
 * @param string $admin    操作的管理员
 * @return int
 */
function addprotected($username, $admin){
	require_once libfile('client', '/uc_client');
	return uc_user_addprotected($username, $admin);
}

/**
 * 删除保护用户
 * 
 * @param mixed  $username 保护用户名 
 * @return int
 */
function delprotected($username){
	require_once libfile('client', '/uc_client');
	return uc_user_deleteprotected($username);
}

/**
 * 得到受保护的用户名列表
 * @return array
 */
function getprotected(){
	require_once libfile('client', '/uc_client');
	return uc_user_getprotected();
}

/**
 * 合并重名用户
 * @param string  $oldusername	老用户名
 * @param string  $newusername	新用户名
 * @param integer $uid			用户 ID
 * @param string  $password		密码
 * @param string  $email		电子邮件
 * @return int >0:返回用户 ID，表示用户注册成功
 *             -1:用户名不合法
 *             -2:包含不允许注册的词语
 *             -3:用户名已经存在
 */
function mergeuser($oldusername, $newusername, $uid, $password, $email){
	require_once libfile('client', '/uc_client');
	return uc_user_merge($oldusername, $newusername, $uid, $password, $email);
}

/**
 * 移除重名用户记录
 * 
 * @param string $username 用户名
 */
function removemerge($username){
	require_once libfile('client', '/uc_client');
	return uc_user_merge_remove($username);
}
