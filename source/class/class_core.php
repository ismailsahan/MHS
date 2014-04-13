<?php

/**
 * class_core.php
 * 核心类库
 * 
 * @copyright WHUT-SIA
 * @version   0.3.2
 * @package   class
 */

//define('IN_WHUT_CONN', TRUE);
//define('CONN_ROOT', substr(dirname(__FILE__), 0, -13));
define('IN_APP_FRAMEWORK', TRUE);
define('APP_FRAMEWORK_ROOT', substr(dirname(__FILE__), 0, -13));

class core {

	var $db = null;
	var $mem = null;
	var $session = null;
	var $cachengine = null;
	var $config = array();
	var $var = array();
	var $cachelist = array();
	var $libs = array(
		'class/error',
		'class/dispatcher',
		'class/dbexception',
		'class/db',
		'vendor/phpfastcache/phpfastcache',
		'class/cache',
		'class/action',
		//'class/phpnew',
		'class/template',
	);
	var $superglobal = array(
		'GLOBALS' => 1,
		'_GET' => 1,
		'_POST' => 1,
		'_REQUEST' => 1,
		'_COOKIE' => 1,
		'_SERVER' => 1,
		'_ENV' => 1,
		'_FILES' => 1,
		//'_SESSION' => 1,
	);

	public static function &instance() {
		static $object;
		if(empty($object)) {
			$object = new C();
			register_shutdown_function(array('C', 'shutdown'));//注册注销函数
			$object->init();
		}
		return $object;
	}

	public function __construct() {
		;
	}

	public function init() {
		$this->_init_env();
		process('加载框架配置...');
		$this->_init_config();
		process('加载框架运行库...');
		$this->_init_lib();
		process('初始化输入...');
		$this->_init_input();
		process('初始化输出...');
		$this->_init_output();
		process('初始化数据库支撑组件...');
		$this->_init_db();
		process('初始化设置选项...');
		$this->_init_setting();
		process('初始化用户...');
		$this->_init_user();
		process('初始化会话SESSION...');
		$this->_init_session();
		process('初始化杂项...');
		$this->_init_misc();
		process('已加载框架');
	}

	/**
	 * 初始化环境
	 */
	private function _init_env() {
		if(PHP_VERSION < '5.3.0') {
			set_magic_quotes_runtime(0);
		}

		define('MAGIC_QUOTES_GPC', function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc());
		define('ICONV_ENABLE', function_exists('iconv'));
		define('MB_ENABLE', function_exists('mb_convert_encoding'));
		define('EXT_OBGZIP', function_exists('ob_gzhandler'));

		define('TIMESTAMP', time());

		if(!defined('APP_FRAMEWORK_CORE_FUNCTION') && !@include(APP_FRAMEWORK_ROOT.'/source/function/function_core.php')) {
			$this->error('function_core.php is missing');
		}

		self::sethandler();

		//define('IS_ROBOT', checkrobot());

		foreach ($GLOBALS as $key => $value) {
			if (!isset($this->superglobal[$key])) {
				$GLOBALS[$key] = null;
				unset($GLOBALS[$key]);
			}
		}

		global $_G;
		$_G = array(
			'uid' => 0,//用户ID
			'username' => '',//用户名
			'clientip' => $this->_get_client_ip(),//用户IP
			'sid' => '',//用户SID
			'formhash' => '',//表单令牌（已废弃）
			'timestamp' => TIMESTAMP,//时间戳
			'starttime' => dmicrotime(),//脚本开始执行的时间（精确到0.01s）
			'referer' => isset($_GET['referer']) ? urldecode($_GET['referer']) : (isset($_SERVER['REQUEST_REFERER']) ? urldecode($_SERVER['REQUEST_REFERER']) : ''),//引用页链接
			'currenturl' => '',//当前访问链接
			'currenturl_encode' => '',//当前访问链接（加密）
			'charset' => '',//项目编码
			'gzipcompress' => false,//是否已开启GZIP压缩
			'authkey' => '',//用户验证密钥
			'language' => '',//语言标识符

			'PHP_SELF' => '',
			'siteurl' => '',//
			'siteroot' => '',
			'siteport' => '',

			'member' => array(),
			'timenow' => array(),
			'debug' => array(),
			'cookie' => array(),
			'lang' => array(),
			'config' => array(),
			'setting' => array(),
		);
		$_G['PHP_SELF'] = dhtmlspecialchars($this->_get_script_url());
		$_G['basescript'] = defined('CURSCRIPT') ? CURSCRIPT : '';
		$_G['basefilename'] = basename($_G['PHP_SELF']);
		$sitepath = substr($_G['PHP_SELF'], 0, strrpos($_G['PHP_SELF'], '/'));
		$_G['isHTTPS'] = ($_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS']) != 'off') ? true : false;
		$_G['siteurl'] = dhtmlspecialchars('http'.($_G['isHTTPS'] ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$sitepath.'/');
		//$_G['scripturl'] = $_G['siteurl'].$_G['basefilename'];

		$url = parse_url($_G['siteurl']);
		$_G['siteroot'] = isset($url['path']) ? $url['path'] : '';
		$_G['siteport'] = empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' ? '' : ':'.$_SERVER['SERVER_PORT'];

		//$_G['currenturl'] = substr($_G['siteurl'], 0, -1) . urldecode($_SERVER['REQUEST_URI']);//BUG
		$_G['currenturl'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$_G['currenturl_encode'] = base64_encode($_G['currenturl']);

		$this->var = &$_G;

		define('IS_ROBOT', checkrobot());
		define('IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET');
		define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST');
		define('IS_HTTPS', $_G['isHTTPS']);
	}

	/**
	 * 加载配置
	 */
	private function _init_config() {
		$_config = array();
		if(!include(APP_FRAMEWORK_ROOT.'/config.inc.php')) halt('CONFIG_NONEXISTENT');

		$_config['security']['authkey'] = empty($_config['security']['authkey']) ? md5($_config['cookie']['cookiepre'].$_config['db']['dbname']) : $_config['security']['authkey'];

		$this->config = & $_config;


		$this->config['security']['allowedentrance'] = is_string($this->config['security']['allowedentrance']) ? explode(',', $this->config['security']['allowedentrance']) : $this->config['security']['allowedentrance'];
		if(!in_array($this->var['basefilename'], $this->config['security']['allowedentrance'])) halt('REQUEST_TAINTING');

		if(empty($this->config['debug']) || !$this->config['debug']) {
			define('APP_FRAMEWORK_DEBUG', false);
			error_reporting(0);
		} elseif(in_array($this->config['debug'], array(1, 2, 3, 4), true) || !empty($_REQUEST['debug']) && $_REQUEST['debug'] === $this->config['debug']) {
			define('APP_FRAMEWORK_DEBUG', true);
			error_reporting(E_ERROR);
			switch($this->config['debug']) {
				case 2: error_reporting(E_ALL); break;
				case 3: error_reporting(E_ALL ^ E_NOTICE); break;
				case 4: error_reporting(-1); break;
			}
			if(PHP_VERSION < '5.3.0')
				error_reporting(error_reporting() ^ E_NOTICE);
		} else {
			define('APP_FRAMEWORK_DEBUG', false);
			error_reporting(0);
		}

		if(substr($this->config['cookie']['cookiepath'], 0, 1) != '/') {
			$this->config['cookie']['cookiepath'] = '/'.$this->config['cookie']['cookiepath'];
		}
		$this->config['cookie']['cookiepre'] = $this->config['cookie']['cookiepre'].substr(md5($this->config['cookie']['cookiepath'].'|'.$this->config['cookie']['cookiedomain']), 0, 4).'_';

		$this->var['config'] = & $this->config;
		//$this->var['authkey'] = md5($this->config['security']['authkey'].$_SERVER['HTTP_USER_AGENT']);

		define('STATICURL', !empty($this->config['output']['staticurl']) ? $this->config['output']['staticurl'] : '/');
		$this->var['staticurl'] = STATICURL;

		//SESSION 初始化
		@ini_set('session.use_cookies', true);
		@ini_set('session.use_only_cookies', false);
		@ini_set('session.cookie_lifetime', 0);
		@ini_set('session.hash_function', 1);
		session_set_cookie_params(0, $this->config['cookie']['cookiepath'], $this->config['cookie']['cookiedomain'], IS_HTTPS, true);
		session_cache_limiter('private');
		session_cache_expire(60);

		if(!is_writable(session_save_path())){
			if(!is_dir(APP_FRAMEWORK_ROOT.'/cache/sessions')) mkdir(APP_FRAMEWORK_ROOT.'/cache/sessions');
			if(!file_exists(APP_FRAMEWORK_ROOT.'/cache/sessions/index.htm')) touch(APP_FRAMEWORK_ROOT.'/cache/sessions/index.htm');
			session_save_path(APP_FRAMEWORK_ROOT.'/cache/sessions');
		}

		@session_name($this->config['cookie']['cookiepre'].'SESSIONID');
		session_start();
	}

	/**
	 * 加载所需库
	 */
	private function _init_lib() {
		foreach($this->libs as $lib){
			$path = libfile($lib);
			(!@include_once($path)) && halt('LIBRARY_FILE_LOAD_ERR', $lib);
		}

		Dispatcher::dispatch();
	}

	/**
	 * 初步处理输入的数据
	 */
	private function _init_input() {

		if(isset($_GET['GLOBALS']) || isset($_POST['GLOBALS']) || isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
			halt('REQUEST_TAINTING');
		}

		if(!MAGIC_QUOTES_GPC) {
			$_GET = daddslashes($_GET);
			$_POST = daddslashes($_POST);
			$_COOKIE = daddslashes($_COOKIE);
			$_FILES = daddslashes($_FILES);
		}

		$prelength = strlen($this->config['cookie']['cookiepre']);
		foreach($_COOKIE as $key => $val) {
			if(substr($key, 0, $prelength) == $this->config['cookie']['cookiepre']) {
				$this->var['cookie'][substr($key, $prelength)] = $val;
			}
		}

		//$this->var['cookie']['auth'] = str_replace(' ', '+', isset($this->var['cookie']['auth']) ? $this->var['cookie']['auth'] : '');//用于判断登录情况
		$this->var['inajax'] = empty($_GET['inajax']) ? false : ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST'));//HTTP_ISAJAXREQUEST
		$this->var['sid'] = $this->var['cookie']['sid'] = isset($this->var['cookie']['sid']) ? dhtmlspecialchars($this->var['cookie']['sid']) : '';//random(6)
		define('IS_AJAX', $this->var['inajax']);

		if(empty($this->var['cookie']['saltkey'])) {
			$this->var['cookie']['saltkey'] = random(8);
			dsetcookie('saltkey', $this->var['cookie']['saltkey'], 86400 * 30, 1, 1);
		}
		$this->var['authkey'] = md5($this->var['config']['security']['authkey'].$this->var['cookie']['saltkey'].$_SERVER['HTTP_USER_AGENT']);

		if(!empty($this->var['cookie']['language']) && is_dir(APP_FRAMEWORK_ROOT.'/source/language/'.$this->var['cookie']['language'])){
			$this->var['language'] = $this->var['cookie']['language'];
		}else{
			$this->var['language'] = $this->config['output']['language'];
			dsetcookie('language', $this->config['output']['language'], 0);
		}
	}

	/**
	 * 输出设置
	 */
	private function _init_output() {
		//防范XSS攻击
		if($this->config['security']['urlxssdefend'] && $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_SERVER['REQUEST_URI'])) {
			$this->_xss_check();
		}
		/*if($this->config['security']['urlxssdefend'] && !empty($_SERVER['REQUEST_URI'])) {
			$temp = urldecode($_SERVER['REQUEST_URI']);
			if(strpos($temp, '<') !== false || strpos($temp, '"') !== false) {
				halt('REQUEST_TAINTING');
			}
		}*/

		//CC防御
		if($this->config['security']['attackevasive'] && (/*!defined('CURSCRIPT') || */!in_array(/*$this->var['mod']*/$_GET['action'], array('seccode', 'secqaa', 'swfupload')) && !defined('DISABLEDEFENSE'))) {
			require_once libfile('misc/security', 'include');
		}

		//检查客户端是否支持GZIP
		if(!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
			$this->config['output']['gzip'] = false;
		}

		//检查并开启GZIP压缩
		@$allowgzip = $this->config['output']['gzip'] && empty($this->var['inajax']) && EXT_OBGZIP;
		setglobal('gzipcompress', $allowgzip);
		if(!ob_start($allowgzip ? 'ob_gzhandler' : null)) {
			ob_start();
		}

		//设置编码
		setglobal('charset', $this->config['output']['charset']);
		define('CHARSET', $this->config['output']['charset']);

		//强制输出编码
		if($this->config['output']['forceheader']) {
			@header('Content-Type: text/html; charset='.$this->config['output']['charset']);
		}
	}

	/**
	 * 加载DB操作类并连接数据库
	 */
	private function _init_db() {
		//DB::init($this->config['db']);
		$driver = function_exists('mysql_connect') ? 'db_driver_mysql' : 'db_driver_mysqli';
		//$driver = class_exists('mysqli') ? 'db_driver_mysqli' : 'db_driver_mysql';
		require libfile($driver, 'class/db');
		if(getglobal('config/db/slave')) {
			$driver = function_exists('mysql_connect') ? 'db_driver_mysql_slave' : 'db_driver_mysqli_slave';
			require libfile($driver, 'class/db');
		}
		DB::init($driver, $this->config['db']);
	}

	/**
	 * 初始化设置
	 */
	private function _init_setting(){
		/*@include get_cache_path("setting", "setting");
		empty($cache) && $cache=array();
		$this->var["setting"] = & $cache;
		unset($cache);*/

		//初始化缓存类
		Cache::init();
		$this->var['setting'] = Cache::get('setting');
		if($this->var['setting'] == null || APP_FRAMEWORK_DEBUG) {
			$this->var['setting'] = array();
			$tmp = DB::fetch_all("SELECT * FROM %t WHERE `skey`!='tos'", array('setting'));
			foreach($tmp as $val){
				$this->var['setting'][$val['skey']] = strlen($val['svalue'])>2 && substr($val['svalue'], 0, 2)==='a:' ? unserialize($val['svalue']) : $val['svalue'];
			}
			Cache::set('setting', $this->var['setting'], 604800);
		}

		//初始化模板类PHPnew
		//global $template;
		/*$template = new PHPnew();
		$template->templates_dir = array_unique(array(
			APP_FRAMEWORK_ROOT.'/source/template/default/',//默认模板路径
			APP_FRAMEWORK_ROOT.'/source/template/'.$this->var['setting']['template'].'/',//模板路径
		));
		$template->templates_cache = APP_FRAMEWORK_ROOT.'/cache/';//缓存模板路径
		$template->set_auto_path(APP_FRAMEWORK_ROOT.'/source/template/metronic/');
		if($this->var['setting']['template'] != 'metronic') $template->set_auto_path(APP_FRAMEWORK_ROOT.'/source/template/'.$this->var['setting']['template'].'/');
		$template->templates_ankey = true;
		$template->templates_new = APP_FRAMEWORK_DEBUG ? true : false;
		$template->templates_isdebug = APP_FRAMEWORK_DEBUG ? true : false;
		$template->templates_direxception = array('test', 'document', 'documention', 'temp', 'demo');*/

		//初始化模板引擎Smarty
		global $template;
		$template = new template;
	}

	/**
	 * 初始化用户
	 */
	private function _init_user(){
		//判断用户是否已登录
		if(!empty($this->var['cookie']['auth'])){
			@list($uid, $username, $email) = daddslashes(explode("\t", authcode($this->var['cookie']['auth'], 'DECODE')));
			if(isset($_SESSION['user']) && $_SESSION['user']['uid'] === $uid){
				if($_SESSION['user']['expiry'] >= TIMESTAMP - 1440){
					$this->var['uid'] = $_SESSION['user']['uid'];
					$this->var['username'] = $_SESSION['user']['username'];
					$this->var['member'] = $_SESSION['user'];
					$_SESSION['user']['expiry'] = TIMESTAMP;
				}elseif(!in_array(ACTION_NAME, array('api', 'seccode'))){//会话超时
					dsetcookie('auth');
					//unset($_SESSION['user']);
					$url = U('logging/expired');
					$url .= (strexists($url, '?') ? '&' : '?').'referer='.urlencode($this->var['currenturl']);
					if(IS_AJAX){
						ajaxReturn(array('errno'=>'-255', 'url'=>$url));
					}else{
						redirect($url);
					}
					exit;
				}
			}
		}
	}

	/**
	 * 分配SESSION
	 */
	private function _init_session() {
		if($this->var['setting']['session'] && $this->var['uid']){
			$ip = explode('.', $this->var['clientip']);
			$session = DB::result_first('SELECT count(*) FROM %t WHERE `sid`=%s AND `ip1`=%d AND `ip2`=%d AND `ip3`=%d AND `ip4`=%d AND `uid`=%d AND `username`=%s LIMIT 1', array('session', $this->var['sid'], $ip[0], $ip[1], $ip[2], $ip[3], $this->var['uid'], $this->var['username']));
			if($session == 0){
				$this->var['sid'] = random(6);
				DB::query('REPLACE INTO %t (`sid`, `ip1`, `ip2`, `ip3`, `ip4`, `uid`, `username`, `lastactivity`) VALUES (%s, %d, %d, %d, %d, %d, %s, %d)', array('session', $this->var['sid'], $ip[0], $ip[1], $ip[2], $ip[3], $this->var['uid'], $this->var['username'], TIMESTAMP), null, true);
				dsetcookie('sid', $this->var['sid'], 86400 * 30);
			}else{
				DB::query('UPDATE %t SET `uid`=%d, `username`=%s, `lastactivity`=%d WHERE `sid`=%s LIMIT 1', array('session', $this->var['uid'], $this->var['username'], TIMESTAMP, $this->var['sid']), null, true);
			}
		}elseif(empty($this->var['sid'])){
			$this->var['sid'] = random(6);
			dsetcookie('sid', $this->var['sid'], 86400 * 30);
		}
	}

	/**
	 * 杂项
	 */
	private function _init_misc() {
		//确定时区
		$timeoffset = $this->var['setting']['timeoffset'] ? $this->var['setting']['timeoffset'] : 0;
		$this->timezone_set($timeoffset);

		$this->var['timenow'] = array(
			'time' => dgmdate(TIMESTAMP),
			'offset' => $timeoffset >= 0 ? ($timeoffset == 0 ? '' : '+'.$timeoffset) : $timeoffset
		);
		
		//表单令牌
		//$this->var['formhash'] = formhash();
		//define('FORMHASH', $this->var['formhash']);

		//禁用客户端浏览器缓存
		if(isset($this->var['setting']['nocacheheaders']) && $this->var['setting']['nocacheheaders']){
			@header('Expires: -1');
			@header('Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0', FALSE);
			@header('Pragma: no-cache');
		}

		$lastact = TIMESTAMP."\t".htmlspecialchars(basename($this->var['PHP_SELF']));
		dsetcookie('lastact', $lastact, 86400);

		//站点关闭
		if($this->var['setting']['closed']){
			if(!in_array(ACTION_NAME, array('api', 'seccode', 'logging')) || ACTION_NAME=='logging' && OPERATION_NAME!='login') {
				if(!$this->var['uid']) {
					redirect(U('logging/login?siteclosed=1'));
				} else {
					require_once libfile('function/nav');
					if(!chkPermit('globalaccess')) {
						redirect(U('logging/login?noaccess=1'));
					}
				}
			}
		}
	}

	/**
	 * 获取客户端IP
	 *
	 * @return string ip IP地址
	 */
	/*private function _get_client_ip() {
		
	}*/

	/**
	 * 获取客户端IP
	 *
	 * @return string ip IP地址
	 */
	private function _get_client_ip() {
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;

		/*
		 * 旧方法
		 *
		$clientip = '';
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$clientip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$clientip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$clientip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$clientip = $_SERVER['REMOTE_ADDR'];
		}

		preg_match("/[\d\.]{7,15}/", $clientip, $clientipmatches);
		$clientip = $clientipmatches[0] ? $clientipmatches[0] : 'unknown';
		return $clientip;
		 */
	}

	/**
	 * 检查是否有 XSS 攻击
	 * 
	 * @return boolean
	 */
	private function _xss_check() {
		static $check = array('"', '>', '<', '\'', '(', ')', 'CONTENT-TRANSFER-ENCODING');

		if(isset($_GET['formhash']) && $_GET['formhash'] !== formhash()) {
			system_error('REQUEST_TAINTING');
		}

		if($_SERVER['REQUEST_METHOD'] == 'GET' ) {
			$temp = $_SERVER['REQUEST_URI'];
		} elseif(empty($_GET['formhash'])) {
			$temp = $_SERVER['REQUEST_URI'].file_get_contents('php://input');
		} else {
			$temp = '';
		}

		if(!empty($temp)) {
			$temp = strtoupper(urldecode(urldecode($temp)));
			foreach ($check as $str) {
				if(strpos($temp, $str) !== false) {
					halt('REQUEST_TAINTING');
				}
			}
		}

		return true;
	}

	public function reject_robot() {
		if(IS_ROBOT) {
			exit(send_http_status(403));
		}
	}

	private function _get_script_url() {
		if(!isset($this->var['PHP_SELF'])){
			$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
			if(basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
				$this->var['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
			} else if(basename($_SERVER['PHP_SELF']) === $scriptName) {
				$this->var['PHP_SELF'] = $_SERVER['PHP_SELF'];
			} else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
				$this->var['PHP_SELF'] = $_SERVER['ORIG_SCRIPT_NAME'];
			} else if(($pos = strpos($_SERVER['PHP_SELF'],'/'.$scriptName)) !== false) {
				$this->var['PHP_SELF'] = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
			} else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT']) === 0) {
				$this->var['PHP_SELF'] = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
				$this->var['PHP_SELF'][0] != '/' && $this->var['PHP_SELF'] = '/'.$this->var['PHP_SELF'];
			} else {
				system_error('request_tainting');
			}
		}
		return $this->var['PHP_SELF'];
	}

	/**
	 * 设置时区
	 *
	 * @param int $timeoffset 时区
	 * @return void
	 */
	private function timezone_set($timeoffset = 0) {
		if(function_exists('date_default_timezone_set')) {
			@date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
		}
	}

	/**
	 * 内建错误处理
	 *
	 * @param string $msg 错误消息，可以为语言包索引
	 * @return void
	 */
	private function error($msg){
		if(defined('APP_FRAMEWORK_CORE_FUNCTION')){
			halt($msg);
		}else{
			$this->error_log($msg);
			echo($msg);
		}
	}

	/**
	 * 简单的错误记录
	 *
	 * @param string $message 错误消息
	 * @return void
	 */
	private function error_log($message) {
		$time = date("Y-m-d H:i:s", TIMESTAMP);
		$file =  APP_FRAMEWORK_ROOT.'/cache/log/errorlog_'.date("Ym").'.php';
		$message = "<?php !defined('IN_LOGHANDLE') && exit('Access Denied');?>\t{$time}:\t".str_replace(array("\t", "\r", "\n"), " ", $message)."\n";
		//error_log($message, 3, $file);
		file_put_contents($file, $message);
	}

	public static function autoload($class) {
		$class = strtolower($class);
		if(strpos($class, '_') !== false) {
			list($folder) = explode('_', $class);
			$file = 'class/'.$folder.'/'.substr($class, strlen($folder) + 1);
		} else {
			$file = 'class/'.$class;
		}

		try {

			self::import($file);
			return true;

		} catch (Exception $exc) {

			$trace = $exc->getTrace();
			foreach ($trace as $log) {
				if(empty($log['class']) && $log['function'] == 'class_exists') {
					return false;
				}
			}
			framework_error::exception_error($exc);
		}
	}

	public static function import($name, $folder = '', $force = true) {
		static $_imports = array();
		$key = $folder.$name;
		if(!isset($_imports[$key])) {
			$path = APP_FRAMEWORK_ROOT.'/source/'.$folder;
			if(strpos($name, '/') !== false) {
				$pre = basename(dirname($name));
				$filename = dirname($name).'/'.$pre.'_'.basename($name).'.php';
			} else {
				$filename = $name.'.php';
			}

			if(is_file($path.'/'.$filename)) {
				include $path.'/'.$filename;
				$_imports[$key] = true;

				return true;
			} elseif(!$force) {
				return false;
			} else {
				throw new Exception('Oops! System file lost: '.$filename);
			}
		}
		return true;
	}

	public static function analysisStart($name){
		$key = 'other';
		if($name[0] === '#') {
			list(, $key, $name) = explode('#', $name);
		}
		if(!isset($_ENV['analysis'])) {
			$_ENV['analysis'] = array();
		}
		if(!isset($_ENV['analysis'][$key])) {
			$_ENV['analysis'][$key] = array();
			$_ENV['analysis'][$key]['sum'] = 0;
		}
		$_ENV['analysis'][$key][$name]['start'] = microtime(TRUE);
		$_ENV['analysis'][$key][$name]['start_memory_get_usage'] = memory_get_usage();
		$_ENV['analysis'][$key][$name]['start_memory_get_real_usage'] = memory_get_usage(true);
		$_ENV['analysis'][$key][$name]['start_memory_get_peak_usage'] = memory_get_peak_usage();
		$_ENV['analysis'][$key][$name]['start_memory_get_peak_real_usage'] = memory_get_peak_usage(true);
	}

	public static function analysisStop($name) {
		$key = 'other';
		if($name[0] === '#') {
			list(, $key, $name) = explode('#', $name);
		}
		if(isset($_ENV['analysis'][$key][$name]['start'])) {
			$diff = round((microtime(TRUE) - $_ENV['analysis'][$key][$name]['start']) * 1000, 5);
			$_ENV['analysis'][$key][$name]['time'] = $diff;
			$_ENV['analysis'][$key]['sum'] = $_ENV['analysis'][$key]['sum'] + $diff;
			unset($_ENV['analysis'][$key][$name]['start']);
			$_ENV['analysis'][$key][$name]['stop_memory_get_usage'] = memory_get_usage();
			$_ENV['analysis'][$key][$name]['stop_memory_get_real_usage'] = memory_get_usage(true);
			$_ENV['analysis'][$key][$name]['stop_memory_get_peak_usage'] = memory_get_peak_usage();
			$_ENV['analysis'][$key][$name]['stop_memory_get_peak_real_usage'] = memory_get_peak_usage(true);
		}
		return $_ENV['analysis'][$key][$name];
	}

	/**
	 * 设定错误和异常处理
	 */
	public function sethandler(){
		if(PHP_VERSION < '5.3.0') { // 兼容 PHP 5.2
			//register_shutdown_function(array('core', 'fatalError'));
			set_error_handler('alternative_error_handler');
			set_exception_handler('alternative_exception_handler');
		} else {
			//register_shutdown_function(array('core', 'fatalError'));
			set_error_handler(array('core', 'error_handler'));
			set_exception_handler(array('core', 'exception_handler'));
		}
	}

	public static function handleException($exception) {
		framework_error::exception_error($exception);
	}


	public static function handleError($errno, $errstr, $errfile, $errline) {
		framework_error::system_error($errstr, false, true, false);
	}

	public static function handleShutdown() {
		if(($error = error_get_last()) && $error['type']) {
			framework_error::system_error($error['message'], false, true, false);
		}
	}

	/**
	 * 自定义异常处理
	 * @param mixed $e 异常对象
	 */
	public static function exception_handler($e) {
		$error = array();
		$error['message'] = $e->getMessage();
		$trace = $e->getTrace();
		if(APP_FRAMEWORK_DEBUG){
			if('throw_exception' == $trace[0]['function']){
				$error['file'] = $trace[0]['file'];
				$error['line'] = $trace[0]['line'];
			}else{
				$error['file'] = $e->getFile();
				$error['line'] = $e->getLine();
			}
			/*if(empty($trace)){
				ob_start();
				debug_print_backtrace();
				$error['trace'] = ob_get_clean();
			}else{
				$error['trace'] = &$trace;
			}*/
		}
		//Log::record($error['message'],Log::ERR);
		halt($error);
	}

	/**
	 * 自定义错误处理
	 * @param int $errno 错误类型
	 * @param string $errstr 错误信息
	 * @param string $errfile 错误文件
	 * @param int $errline 错误行数
	 * @return void
	 */
	public static function error_handler($errno, $errstr, $errfile, $errline) {
		$errfile = str_replace(APP_FRAMEWORK_ROOT, '.', $errfile);
		switch($errno){
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				//ob_end_clean();
				// 页面压缩输出支持
				/*if(C('OUTPUT_ENCODE')){
					$zlib = ini_get('zlib.output_compression');
					if(empty($zlib)) ob_start('ob_gzhandler');
				}*/
				$errorStr = "{$errstr} {$errfile} 第 {$errline} 行.";
				err($errorStr);
				//if(C('LOG_RECORD')) Log::write("[$errno] ".$errorStr,Log::ERR);
				//function_exists('halt') ? halt($errorStr) : exit('ERROR:'.$errorStr);
				class_exists('framework_error') ? halt($errorStr) : exit('ERROR:'.$errorStr);
				break;
			case E_STRICT:
			case E_USER_WARNING:
			case E_USER_NOTICE:
			default:
				$errorStr = "[{$errno}] {$errstr} {$errfile} 第 {$errline} 行.";
				//trace($errorStr,'','NOTIC');
				err($errorStr);
				break;
		}
		return true;
	}

	/**
	 * 致命错误捕获
	 */
	public static function fatalError() {
		//保存日志记录
		//if(C('LOG_RECORD')) Log::save();
		if ($e = error_get_last()) {
			switch($e['type']){
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:  
					//ob_end_clean();
					function_exists('halt') ? halt($e) : exit('ERROR:'.$e['message']. ' in <b>'.str_replace(APP_FRAMEWORK_ROOT, '.', $e['file']).'</b> on line <b>'.$e['line'].'</b>');
					break;
			}
		}
	}

	/**
	 * 脚本结束时调用，关闭数据库链接，输出缓冲区内容
	 */
	public static function shutdown(){
		define('APP_FRAMEWORK_SHUTTING_DOWN', TRUE);
		process('注销中...');
		if(($error = error_get_last()) && $error['type']) C::fatalError();
		if(APP_FRAMEWORK_DEBUG && !IS_AJAX && !defined('DISABLE_TRACE') && !in_array(ACTION_NAME, C::instance()->config['trace_disabled']) && file_exists(APP_FRAMEWORK_ROOT.'/source/PageTrace.php')){
			process('开启页面调试...');
			require_once libfile('include/trace');
		}
		class_exists('DB') && DB::$db->curlink && DB::$db->close();
		ob_end_flush();
		//exit;
	}

	function __destruct(){
		//$this->shutdown();
	}
}

class C extends core {}


if(PHP_VERSION < '5.3.0') {
	//禁用APC以避免一些问题
	/*if(extension_loaded('apc')) {
		@ini_set('apc.enabled', 0);
		@ini_set('apc.include_once_override', 0);
		@ini_set('apc.canonicalize', 0);
		@ini_set('apc.stat', 0);
	}*/
	function alternative_exception_handler($e) {
		return C::exception_handler($e);
	}
	function alternative_error_handler($errno, $errstr, $errfile, $errline) {
		return C::error_handler($errno, $errstr, $errfile, $errline);
	}
}

/*
if(function_exists('spl_autoload_register')) {
	spl_autoload_register(array('core', 'autoload'));
} else {
	function __autoload($class) {
		return core::autoload($class);
	}
}
 */
