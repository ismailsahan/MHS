<?php

/**
 * class_core.php
 * 核心类库
 * 
 * @author    gwc0721
 * @copyright WHUT-SIA
 * @version   0.1.1
 * @package   function
 */

//define('IN_WHUT_CONN', TRUE);
//define('CONN_ROOT', substr(dirname(__FILE__), 0, -13));
define('IN_APP_FRAMEWORK', TRUE);
define('APP_FRAMEWORK_ROOT', substr(dirname(__FILE__), 0, -13));

class C {

	var $db = null;
	var $mem = null;
	var $session = null;
	var $cachengine = null;
	var $config = array();
	var $var = array();
	var $cachelist = array();
	var $libs = array(
		'class/db',
		'class/phpnew',
		'plugin/phpfastcache/phpfastcache',
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
		}
		return $object;
	}

	public function __construct() {
		$this->_init_env();
		$this->_init_config();
		$this->_init_lib();
		$this->_init_input();
		$this->_init_output();
		$this->_init_db();
		$this->_init_setting();
		$this->_init_misc();
		$this->_init_session();
		$this->_init_user();
		process('核心组件初始化完成！');
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

		//define('IS_ROBOT', checkrobot());

		foreach ($GLOBALS as $key => $value) {
			if (!isset($this->superglobal[$key])) {
				$GLOBALS[$key] = null;
				unset($GLOBALS[$key]);
			}
		}

		global $_G;
		$_G = array(
			'uid' => 0,
			'username' => '',//用户名
			'clientip' => $this->_get_client_ip(),//用户IP
			'sid' => '',//用户SID
			'formhash' => '',//表单令牌
			'timestamp' => TIMESTAMP,
			'starttime' => dmicrotime(),
			'referer' => isset($_GET['referer']) ? urldecode($_GET['referer']) : (isset($_SERVER['REQUEST_REFERER']) ? urldecode($_SERVER['REQUEST_REFERER']) : ''),
			'currenturl' => '',
			'currenturl_encode' => '',
			'charset' => '',
			'gzipcompress' => '',
			'authkey' => '',
			'language' => '',

			'PHP_SELF' => '',
			'siteurl' => '',
			'siteroot' => '',
			'siteport' => '',

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

		$url = parse_url($_G['siteurl']);
		$_G['siteroot'] = isset($url['path']) ? $url['path'] : '';
		$_G['siteport'] = empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' ? '' : ':'.$_SERVER['SERVER_PORT'];

		//$_G['currenturl'] = substr($_G['siteurl'], 0, -1) . urldecode($_SERVER['REQUEST_URI']);//BUG
		$_G['currenturl'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		$this->var = &$_G;

		define('IS_ROBOT', checkrobot());
		define('IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET');
		define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST');
		define('IS_HTTPS', $this->var['isHTTPS']);
	}

	/**
	 * 加载配置
	 */
	private function _init_config() {
		$_config = array();
		if(!@include(APP_FRAMEWORK_ROOT.'/config.inc.php')) halt('CONFIG_NONEXISTENT');

		$_config['security']['authkey'] = empty($_config['security']['authkey']) ? md5($_config['cookie']['cookiepre'].$_config['db']['dbname']) : $_config['security']['authkey'];

		$this->config = & $_config;


		$this->config['security']['allowedentrance'] = is_string($this->config['security']['allowedentrance']) ? explode(',', $this->config['security']['allowedentrance']) : $this->config['security']['allowedentrance'];
		if(!in_array($this->var['basefilename'], $this->config['security']['allowedentrance'])) halt('REQUEST_TAINTING');

		if(empty($this->config['debug']) || !$this->config['debug']) {
			define('APP_FRAMEWORK_DEBUG', false);
			error_reporting(0);
		} elseif(in_array($this->config['debug'], array(1, 2, 3), true) || !empty($_REQUEST['debug']) && $_REQUEST['debug'] === $this->config['debug']) {
			define('APP_FRAMEWORK_DEBUG', true);
			error_reporting(E_ERROR);
			switch($this->config['debug']) {
				case 2: error_reporting(E_ALL); break;
				case 3: error_reporting(E_ALL ^ E_NOTICE); break;
			}
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
	}

	/**
	 * 加载所需库
	 */
	private function _init_lib() {
		foreach($this->libs as $lib){
			$path = libfile($lib);
			(!@include_once($path)) && halt('LIBRARY_FILE_LOAD_ERR', $lib);
		}
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

		$this->var['cookie']['auth'] = str_replace(' ', '+', isset($this->var['cookie']['auth']) ? $this->var['cookie']['auth'] : '');//用于判断登录情况
		$this->var['inajax'] = empty($_GET['inajax']) ? false : ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'/* && ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST')*/);//HTTP_ISAJAXREQUEST
		$this->var['sid'] = $this->var['cookie']['sid'] = isset($this->var['cookie']['sid']) ? dhtmlspecialchars($this->var['cookie']['sid']) : '';//random(6)
		define('IS_AJAX', $this->var['inajax']);

		if(empty($this->var['cookie']['saltkey'])) {
			$this->var['cookie']['saltkey'] = random(8);
			dsetcookie('saltkey', $this->var['cookie']['saltkey'], 86400 * 30, 1, 1);
		}
		$this->var['authkey'] = md5($this->var['config']['security']['authkey'].$this->var['cookie']['saltkey']);

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

		if($this->config['security']['attackevasive'] && (!defined('CURSCRIPT') || !in_array($this->var['mod'], array('seccode', 'secqaa', 'swfupload')) && !defined('DISABLEDEFENSE'))) {
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
		/*$this->db = &DB::object();
		$this->db->set_config($this->config['db']);
		$this->db->connect();*/
		DB::init($this->config['db']);
	}

	/**
	 * 初始化设置
	 */
	private function _init_setting(){
		/*@include get_cache_path("setting", "setting");
		empty($cache) && $cache=array();
		$this->var["setting"] = & $cache;
		unset($cache);*/

		global $cache;
		$cache = phpFastCache('auto');
		//$this->cachengine = &$cache;
		$this->var['setting'] = $cache->get('setting');
		if($this->var['setting'] == null || APP_FRAMEWORK_DEBUG) {
			$this->var['setting'] = array();
			$tmp = DB::fetch_all('SELECT * FROM '.DB::table('setting'));
			foreach($tmp as $val){
				$this->var['setting'][$val['skey']] = strexists($val['svalue'], 'a:') ? unserialize($val['svalue']) : $val['svalue'];
			}
			$cache->set('setting', $this->var['setting'], 604800);
		}

		//初始化模板类PHPnew
		global $template;
		$template = new PHPnew();
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
		$template->templates_depth = 4;
		$template->templates_direxception = array('test', 'document', 'documention', 'temp', 'demo');
		//$template->templates_source = APP_FRAMEWORK_ROOT.'/static/';//CSS JS目录

		if($this->var['setting']['closed']){
			$template->display('closed');
			exit;
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
			@header("Expires: -1");
			@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			@header("Pragma: no-cache");
		}

		$lastact = TIMESTAMP."\t".htmlspecialchars(basename($this->var['PHP_SELF']));
		dsetcookie('lastact', $lastact, 86400);
		setglobal('currenturl_encode', base64_encode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
	}

	/**
	 * 分配SESSION
	 */
	private function _init_session() {
		session_set_cookie_params(0, $this->config['cookie']['cookiepath'], $this->config['cookie']['cookiedomain'], IS_HTTPS, true);
		@ini_set('session.use_cookies', true);
		@ini_set('session.use_only_cookies', false);
		@ini_set('session.cookie_lifetime', 0);
		@ini_set('session.hash_function', 1);
		session_cache_limiter('private');

		if(!is_writable(ini_get('session.save_path'))){
			if(!is_dir(APP_FRAMEWORK_ROOT.'/cache/sessions')) mkdir(APP_FRAMEWORK_ROOT.'/cache/sessions');
			session_save_path(APP_FRAMEWORK_ROOT.'/cache/sessions');
		}

		@session_name($this->config['cookie']['cookiepre'].'SESSIONID');
		session_start();

		if(empty($_COOKIE['sid'])) dsetcookie('sid', $this->var['sid'], 86400 * 30);
	}

	/**
	 * 初始化用户
	 */
	private function _init_user(){
		if(!empty($this->var['cookie']['auth'])){
			@list($username, $auth) = daddslashes(explode("\t", authcode($this->var['cookie']['auth'], 'DECODE')));
			if($auth === $this->var['authkey']){
				$this->var['username'] = $username;
			}
		}

		if(isset($_SESSION['user']) && $_SESSION['user']['uid'] > 0 && $_SESSION['user']['authkey'] == $this->var['authkey']){
			$this->var['uid'] = $_SESSION['user']['uid'];
			$this->var['username'] = $_SESSION['user']['username'];
		}
	}

	/**
	 * 获取客户端IP
	 *
	 * @return string ip IP地址
	 */
	/*private function _get_client_ip() {
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
	}

	/**
	 * 检查是否有 XSS 攻击
	 * 
	 * @return boolean
	 */
	private function _xss_check() {
		$temp = strtoupper(urldecode(urldecode($_SERVER['REQUEST_URI'])));
		if(strpos($temp, '<') !== false || strpos($temp, '"') !== false || strpos($temp, 'CONTENT-TRANSFER-ENCODING') !== false) {
			halt('REQUEST_TAINTING');
		}
		return true;
	}

	public function reject_robot() {
		if(IS_ROBOT) {
			exit(header("HTTP/1.1 403 Forbidden"));
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

	/**
	 * 脚本结束时调用，关闭数据库，输出缓冲区内容
	 */
	private function end($obj = null){
		static $executed = false;
		global $action;
		process('准备结束...');
		if(!$executed){
			if(empty($obj))
				$obj = &$this;
			if(APP_FRAMEWORK_DEBUG && !IS_AJAX  && file_exists(APP_FRAMEWORK_ROOT.'/source/PageTrace.php') && !in_array($action, $this->config['trace_disabled'])){
				global $_G, $template;

				//整理SQL
				if(class_exists('DB')){
					$db = DB::object();
					$sqldebug = array();
					$n = $discuz_table = 0;
					$queryinfo = array(
						'select' => 0,
						'update' => 0,
						'insert' => 0,
						'replace' => 0,
						'delete' => 0
					);
					$sqlw = array();
					$queries = count($db->sqldebug);
					$links = array();
					foreach($db->link as $k => $link) {
						$links[(string)$link] = $k;
					}
					$sqltime = 0;
					foreach($db->sqldebug as $string) {
						$sqltime += $string[1];
						$extra = $dt = '';
						$n++;
						$sql = $string[0];
						$sqldebugrow = '';
						if(preg_match('/^SELECT /', $string[0])) {
							$queryinfo['select']++;
							$query = @mysql_query('EXPLAIN '.$string[0], $string[3]);
							$i = 0;
							$sqldebugrow .= '';
							while($row = DB::fetch($query)) {
								if(!$i) {
									$sqldebugrow .= ''.implode('_OR_', array_keys($row)).'';
									$i++;
								}
								if(strexists($row['Extra'], 'Using filesort')) {
									$sqlw['Using filesort']++;
									$extra .= $row['Extra'];
								}
								if(strexists($row['Extra'], 'Using temporary')) {
									$sqlw['Using temporary']++;
									$extra .= $row['Extra'];
								}
								$sqldebugrow .= ''.implode('_SP_', $row).'';
							}
							$sqldebugrow .= '';
						}elseif(preg_match('/^UPDATE /', $string[0])){
							$queryinfo['update']++;
						}elseif(preg_match('/^INSERT /', $string[0])){
							$queryinfo['insert']++;
						}elseif(preg_match('/^REPLACE /', $string[0])){
							$queryinfo['replace']++;
						}elseif(preg_match('/^DELETE /', $string[0])){
							$queryinfo['delete']++;
						}

						$sqldebugrow .= '[hide][table=1][tr][th]File[/th][th]Line[/th][th]Function[/th][/tr]';
						foreach($string[2] as $error) {
							$error['file'] = str_replace(array(APP_FRAMEWORK_ROOT, '\\'), array('', '/'), $error['file']);
							$error['class'] = isset($error['class']) ? $error['class'] : '';
							$error['type'] = isset($error['type']) ? $error['type'] : '';
							$error['function'] = isset($error['function']) ? $error['function'] : '';
							$sqldebugrow .= "[tr][td]{$error['file']}[/td][td]{$error['line']}[/td][td]{$error['class']}{$error['type']}{$error['function']}()[/td][/tr]";
							/*if(strexists($error['file'], 'discuz/discuz_table') || strexists($error['file'], 'table/table')) {
								$dt = ' • '.$error['file'];
							}*/
						}
						$sqldebugrow .= '[/table][/hide]'.($extra ? $extra.'[br]' : '').'[br]';
						$sqldebug[] = $string[1].'s • DBLink '.$links[(string)$string[3]].$dt.'[br][color=blue]'.$sql.'[/color][br]'.$sqldebugrow;
					}
				}

				$trace = array();
				$tmp = &trace();
				$trace['base'] = array();
				$trace['process'] = &$tmp['process'];
				$trace['error'] = &$tmp['error'];
				//$trace = $_G['debug'];
				$trace['files'] = get_included_files();
				$trace['template'] = isset($template) ? $template->get_debug_info() : array();
				$trace['mapping'] = empty($template) ? array() : $template->templates_mapping();
				$trace['sql'] = empty($sqldebug) ? array() : $sqldebug;
				$trace['trace'] = &$tmp['trace'];
				$trace['_G'] = &$_G;

				//进一步处理文件信息
				$t = array_keys($trace['mapping']);
				foreach ($trace['files'] as $k => $file){
					$temp = str_replace(APP_FRAMEWORK_ROOT, '.', $file);
					$f = basename($file);
					$trace['files'][$k] = $temp.' ( '.number_format(filesize($file)/1024, 2).' KB )';
					if(in_array($f, $t)) $trace['files'][$k] .= ' -> '.$trace['mapping'][$f];
				}
				unset($t);
				//echo '<pre>';print_r($this->cachengine->stats());echo '</pre>';

				//处理基本调试信息
				$temp = dmicrotime() - $_G['starttime'];
				$trace['base'][] = lang('debug', 'base_request', array('str' => date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']).' '.$_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'].' : '.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])));
				$trace['base'][] = lang('debug', 'base_runtime', array('str' => $temp));
				$trace['base'][] = lang('debug', 'base_throughput', array('str' => number_format(1/$temp, 2)));
				$trace['base'][] = lang('debug', 'base_mem', array('str' => number_format(memory_get_usage()/1024, 2).' KB'));
				$trace['base'][] = lang('debug', 'base_sql', array('str' => (isset($db->querynum) ? "{$db->querynum} queries ({$queryinfo['select']} selects, {$queryinfo['update']} updates, {$queryinfo['delete']} deletes, {$queryinfo['insert']} inserts, {$queryinfo['replace']} replaces)" : 'Unknown')));
				$trace['base'][] = lang('debug', 'base_files', array('str' => count($trace['mapping'])));
				//$trace['base'][] = lang('debug', 'base_cache', array('str' => '?'));
				$trace['base'][] = lang('debug', 'base_session', array('str' => session_id()));
				//'time' => number_format((dmicrotime() - $_G['starttime']), 6),
				//'queries' => $db->querynum,
				//'memory' => $cache->option['storage']=='files' ? null : ucfirst($cache->option['storage']),

				include_once APP_FRAMEWORK_ROOT.'/source/PageTrace.php';
			}
			class_exists('DB') && $db->curlink && $db->close();
			ob_end_flush();
			$executed = true;
		}
		exit;
	}

	function __destruct(){
		$this->end();
	}
}

//$CORE = &C::instance();
$CORE = new C();
//$CORE->init();