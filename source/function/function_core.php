<?php

define('APP_FRAMEWORK_CORE_FUNCTION', TRUE);

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	global $_G;
	$string = ($operation == 'DECODE') ? str_replace(' ', '+', $string) : $string;

	$ckey_length = 4;// 随机密钥长度 取值 0-32;
	// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
	// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
	// 当此值为 0 时，则不产生随机密钥

	$key = md5($key != '' ? $key : $GLOBALS['_G']['authkey']);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

function formhash($specialadd = '') {
	global $_G;
	$hashadd = defined('IN_ADMINCP') ? 'Only For Admin Control Panel' : '';
	return substr(md5(substr($_G['timestamp'], 0, -7).$_G['username'].$_SERVER['HTTP_USER_AGENT'].$_G['authkey'].$hashadd.$specialadd), 8, 8);
}

function is_utf8($string) {
	return preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/", $string);
}

function debuginfo() {
	global $_G, $cache;
	if(getglobal('setting/debug')) {
		$db = DB::object();
		$_G['debuginfo'] = array(
			'time' => number_format((dmicrotime() - $_G['starttime']), 6),
			'queries' => $db->querynum,
			//'memory' => isset($_G['memory']) ? ucwords(@$_G['memory']) : null,
			'memory' => $cache->option['storage']=='files' ? null : ucfirst($cache->option['storage']),
			//'mem' => (intval(ini_get('memory_limit')) > 0) ? memory_get_usage() : ''
		);
		if($db->slaveid) {
			$_G['debuginfo']['queries'] = 'Total '.$db->querynum.', Slave '.$db->slavequery;
		}
		return TRUE;
	} else {
		return FALSE;
	}
}

function dmktime($date) {
	if(strpos($date, '-')) {
		$time = explode('-', $date);
		return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
	}
	return 0;
}

function dmicrotime() {
	return array_sum(explode(' ', microtime()));
}

function dgmdate($timestamp, $format = 'dt', $timeoffset = '9999', $uformat = '') {
	global $_G;
	$format == 'u' && !$_G['setting']['dateconvert'] && $format = 'dt';
	static $dformat, $tformat, $dtformat, $offset, $lang_dgmdate;
	if($dformat === null) {
		$dformat = getglobal('setting/dateformat');
		$tformat = getglobal('setting/timeformat');
		$dtformat = $dformat.' '.$tformat;
		$offset = getglobal('member/timeoffset') ? getglobal('member/timeoffset') : (isset($_G['setting']['timeoffset']) ? intval($_G['setting']['timeoffset']) : 0);
		$lang_dgmdate = lang('core', 'date');
	}
	$timeoffset = $timeoffset == 9999 ? $offset : $timeoffset;
	$timestamp += $timeoffset * 3600;
	$format = empty($format) || $format == 'dt' ? $dtformat : ($format == 'd' ? $dformat : ($format == 't' ? $tformat : $format));
	if($format == 'u') {
		$todaytimestamp = TIMESTAMP - (TIMESTAMP + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
		$s = gmdate(!$uformat ? str_replace(":i", ":i:s", $dtformat) : $uformat, $timestamp);
		$time = TIMESTAMP + $timeoffset * 3600 - $timestamp;
		if($timestamp >= $todaytimestamp) {
			if($time > 3600) {
				return '<span title="'.$s.'">'.intval($time / 3600).'&nbsp;'.$lang_dgmdate['hour'].$lang_dgmdate['before'].'</span>';
			} elseif($time > 1800) {
				return '<span title="'.$s.'">'.$lang_dgmdate['half'].$lang_dgmdate['hour'].$lang_dgmdate['before'].'</span>';
			} elseif($time > 60) {
				return '<span title="'.$s.'">'.intval($time / 60).'&nbsp;'.$lang_dgmdate['min'].$lang_dgmdate['before'].'</span>';
			} elseif($time > 0) {
				return '<span title="'.$s.'">'.$time.'&nbsp;'.$lang_dgmdate['sec'].$lang_dgmdate['before'].'</span>';
			} elseif($time == 0) {
				return '<span title="'.$s.'">'.$lang_dgmdate['now'].'</span>';
			} else {
				return $s;
			}
		} elseif(($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
			if($days == 0) {
				return '<span title="'.$s.'">'.$lang_dgmdate['yday'].'&nbsp;'.gmdate($tformat, $timestamp).'</span>';
			} elseif($days == 1) {
				return '<span title="'.$s.'">'.$lang_dgmdate['byday'].'&nbsp;'.gmdate($tformat, $timestamp).'</span>';
			} else {
				return '<span title="'.$s.'">'.($days + 1).'&nbsp;'.$lang_dgmdate['day'].$lang_dgmdate['before'].'</span>';
			}
		} else {
			return $s;
		}
	} else {
		return gmdate($format, $timestamp);
	}
}

function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function setglobal($key , $value, $group = null) {
	global $_G;
	$k = explode('/', $group === null ? $key : $group.'/'.$key);
	switch (count($k)) {
		case 1: $_G[$k[0]] = $value; break;
		case 2: $_G[$k[0]][$k[1]] = $value; break;
		case 3: $_G[$k[0]][$k[1]][$k[2]] = $value; break;
		case 4: $_G[$k[0]][$k[1]][$k[2]][$k[3]] = $value; break;
		case 5: $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] =$value; break;
	}
	return true;
}

function getglobal($key, $group = null) {
	global $_G;
	$k = explode('/', $group === null ? $key : $group.'/'.$key);
	switch (count($k)) {
		case 1: return isset($_G[$k[0]]) ? $_G[$k[0]] : null; break;
		case 2: return isset($_G[$k[0]][$k[1]]) ? $_G[$k[0]][$k[1]] : null; break;
		case 3: return isset($_G[$k[0]][$k[1]][$k[2]]) ? $_G[$k[0]][$k[1]][$k[2]] : null; break;
		case 4: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]] : null; break;
		case 5: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] : null; break;
	}
	return null;
}

function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {
	global $_G;

	$config = $_G['config']['cookie'];

	$_G['cookie'][$var] = $value;
	$var = ($prefix ? $config['cookiepre'] : '').$var;
	$_COOKIE[$var] = $var;

	if($value == '' || $life < 0) {
		$value = '';
		$life = -1;
	}

	$life = $life > 0 ? getglobal('timestamp') + $life : ($life < 0 ? getglobal('timestamp') - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'].'; HttpOnly' : $config['cookiepath'];

	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	if(PHP_VERSION < '5.2.0') {
		@setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure);
	} else {
		@setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
	}
}

function dintval($int, $allowarray = false) {
	$ret = intval($int);
	if($int == $ret || !$allowarray && is_array($int)) return $ret;
	if($allowarray && is_array($int)) {
		foreach($int as &$v) {
			$v = dintval($v, true);
		}
		return $int;
	} elseif($int <= 0xffffffff) {
		$l = strlen($int);
		$m = substr($int, 0, 1) == '-' ? 1 : 0;
		if(($l - $m) === strspn($int,'0987654321', $m)) {
			return $int;
		}
	}
	return $ret;
}

function getcookie($key) {
	global $_G;
	return isset($_G['cookie'][$key]) ? $_G['cookie'][$key] : '';
}

/*function lang($file, $msg = '', $vars = array()) {
	global $_G;
	if(($pos = strpos($msg, '|')) !== false) {
		$file = substr($msg, 0, $pos);
		$msg = substr($msg, $pos + 1);
	}
	if($msg == ''){
		@list($file, $msg) = explode('/', $file);
	}

	if(!isset($_G['lang'][$file])) {
		//if(defined('IN_ADMINCP')) $fpath = QWROOT.'/admin/lang/lang_'.$file.'.php';
		//if(!defined('IN_ADMINCP') || (isset($fpath)&&!realpath($fpath))) $fpath = QWROOT.'/source/lang_'.$file.'.php';
		@include_once $fpath;
		if(!isset($lang)) $lang = array();
		$_G['lang'][$file] = &$lang;
		unset($lang, $fpath);
	}
	$return = empty($msg) ? $_G['lang'][$file] : (empty($_G['lang'][$file][$msg]) ? $msg : $_G['lang'][$file][$msg]);
	$searchs = $replaces = array();
	if($vars && is_array($vars)) {
		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
	}
	if(is_string($return) && strpos($return, '{_G/') !== false) {
		preg_match_all('/\{_G\/(.+?)\}/', $return, $gvar);
		foreach($gvar[0] as $k => $v) {
			$searchs[] = $v;
			$replaces[] = getglobal($gvar[1][$k]);
		}
	}
	$return = str_replace($searchs, $replaces, $return);
	//$return = ele_replace($searchs, $replaces, $return);
	return $return;
}*/

function lang($file, $langvar = null, $vars = array(), $default = null, $raw = false) {
	global $_G;
	//static $lang = array();
	//$_G['lang'] = &$lang;
	//trace("lang($file, $langvar)");
	$fileinput = $file;
	if(!strexists($file, '/')) $file = $file.'/';
	@list($path, $file) = explode('/', $file);
	if(!$file) {
		$file = $path;
		$path = '';
	}
	if(strpos($file, ':') !== false) {
		$path = 'plugin';
		list($file) = explode(':', $file);
	}

	$key = $path == '' ? $file : $path.'_'.$file;
	if(!isset($_G['lang'][$key])) {
		include APP_FRAMEWORK_ROOT.'/source/language/'.($_G['language'] ? $_G['language'] : ($_G['config']['output']['language'] ? $_G['config']['output']['language'] : 'zh_cn')).'/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';
		$_G['lang'][$key] = $lang;
	}
	$return = $langvar !== null ? (isset($_G['lang'][$key][$langvar]) ? $_G['lang'][$key][$langvar] : null) : $_G['lang'][$key];
	$return = $return === null ? ($default !== null ? $default : $langvar) : $return;
	$searchs = $replaces = array();
	if($vars && is_array($vars)) {
		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
	}
	if(is_string($return) && strpos($return, '{_G/') !== false) {
		preg_match_all('/\{_G\/(.+?)\}/', $return, $gvar);
		foreach($gvar[0] as $k => $v) {
			$searchs[] = $v;
			$replaces[] = $raw ? "<?=getglobal('{$gvar[1][$k]}')?>" : getglobal($gvar[1][$k]);
		}
	}
	$return = str_replace($searchs, $replaces, $return);
	return $return;
}

function libfile($libname, $folder = '', $chkExistence = true) {
	$libpath = '/source'.($folder == '' ? '' : '/'.$folder);
	if(strstr($libname, '/')) {
		list($pre, $name, $ext) = explode('/', $libname);
		$path = $ext ? "{$libpath}/{$pre}/{$name}/{$ext}" : "{$libpath}/{$pre}/{$pre}_{$name}";
	} else {
		$path = "{$libpath}/{$libname}";

	}
	$return = preg_match('/^[\w\d\/_]+$/i', $path) ? realpath(APP_FRAMEWORK_ROOT.$path.'.php') : false;
	//$chkExistence && !file_exists($path) && halt('LIBRARY_FILE_NONEXISTENT', $lib);
	$chkExistence && !$return && halt('LIBRARY_FILE_NONEXISTENT', (empty($folder) ? '' : "{$folder}/").$libname);
	return $return;
}

function need_seccode($mod){
	global $_G;
	return in_array($mod, $_G['setting']['seccodestatus']);
}

function chklogin(){
	global $_G;
	return ($_G['uid'] > 0 && !empty($_G['username']));
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
	$label = ($label === null) ? '' : rtrim($label) . ' ';
	if(!$strict){
		if (ini_get('html_errors')) {
			$output = print_r($var, true);
			$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
		} else {
			$output = $label . print_r($var, true);
		}
	}else{
		ob_start();
		var_dump($var);
		$output = ob_get_clean();
		if (!extension_loaded('xdebug')) {
			$output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
			$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
		}
	}
	if($echo){
		echo($output);
		return null;
	}else
		return $output;
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

/**
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id   数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8') {
    if(is_array($attr)){
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr = trim($attr);
    $attr = empty($attr) ? '' : " {$attr}";
    $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml .= "<{$root}{$attr}>";
    $xml .= data_to_xml($data, $item, $id);
    $xml .= "</{$root}>";
    return $xml;
}

/**
 * 数据XML编码
 * @param mixed  $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id   数字索引key转换为的属性名
 * @return string
 */
function data_to_xml($data, $item='item', $id='id') {
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if(is_numeric($key)){
            $id && $attr = " {$id}=\"{$key}\"";
            $key = $item;
        }
        $xml .= "<{$key}{$attr}>";
        $xml .= (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml .= "</{$key}>";
    }
    return $xml;
}

/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) {
    static $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}

/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $redirect 是否跳转，如果设置为true则表示跳转到该URL地址
 * @param boolean $domain 是否显示域名
 * @return string
 */
function U($url='', $vars='', $suffix=true, $redirect=false, $domain=false) {
    // 解析URL
    $info = parse_url($url);
    $url = !empty($info['path']) ? $info['path'] : ACTION_NAME;
    if(isset($info['fragment'])) { // 解析锚点
        $anchor = $info['fragment'];
        if(false !== strpos($anchor,'?')) { // 解析参数
            list($anchor,$info['query']) = explode('?',$anchor,2);
        }        
        if(false !== strpos($anchor,'@')) { // 解析域名
            list($anchor,$host) = explode('@',$anchor, 2);
        }
    }elseif(false !== strpos($url,'@')) { // 解析域名
        list($url,$host) = explode('@',$info['path'], 2);
    }
    // 解析子域名
    if(isset($host)) {
        $domain = $host.(strpos($host,'.')?'':strstr($_SERVER['HTTP_HOST'],'.'));
    }elseif($domain===true){
        $domain = $_SERVER['HTTP_HOST'];
        if(C('APP_SUB_DOMAIN_DEPLOY') ) { // 开启子域名部署
            $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');
            // '子域名'=>array('项目[/分组]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                if(false === strpos($key, '*') && 0 === strpos($url, $rule[0])) {
                    $domain = $key.strstr($domain, '.'); // 生成对应子域名
                    $url = substr_replace($url, '', 0, strlen($rule[0]));
                    break;
                }
            }
        }
    }

    // 解析参数
    if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
        parse_str($vars,$vars);
    }elseif(!is_array($vars)){
        $vars = array();
    }
    if(isset($info['query'])) { // 解析地址里面参数 合并到vars
        parse_str($info['query'],$params);
        $vars = array_merge($params,$vars);
    }
    
    // URL组装
    $depr = C('URL_PATHINFO_DEPR');
    if($url) {
        if(0=== strpos($url,'/')) {// 定义路由
            $route = true;
            $url = substr($url,1);
            if('/' != $depr) {
                $url = str_replace('/',$depr,$url);
            }
        }else{
            if('/' != $depr) { // 安全替换
                $url = str_replace('/',$depr,$url);
            }
            // 解析分组、模块和操作
            $url = trim($url,$depr);
            $path = explode($depr,$url);
            $var = array();
            $var[C('VAR_ACTION')] = !empty($path) ? array_pop($path) : ACTION_NAME;
            $var[C('VAR_MODULE')] = !empty($path) ? array_pop($path) : MODULE_NAME;
            if($maps = C('URL_ACTION_MAP')) {
                if(isset($maps[strtolower($var[C('VAR_MODULE')])])) {
                    $maps = $maps[strtolower($var[C('VAR_MODULE')])];
                    if($action = array_search(strtolower($var[C('VAR_ACTION')]), $maps)){
                        $var[C('VAR_ACTION')] = $action;
                    }
                }
            }
            if($maps = C('URL_MODULE_MAP')) {
                if($module = array_search(strtolower($var[C('VAR_MODULE')]), $maps)){
                    $var[C('VAR_MODULE')] = $module;
                }
            }            
            if(C('URL_CASE_INSENSITIVE')) {
                $var[C('VAR_MODULE')] = parse_name($var[C('VAR_MODULE')]);
            }
            if(!C('APP_SUB_DOMAIN_DEPLOY') && C('APP_GROUP_LIST')) {
                if(!empty($path)) {
                    $group = array_pop($path);
                    $var[C('VAR_GROUP')] = $group;
                }else{
                    if(GROUP_NAME != C('DEFAULT_GROUP')) {
                        $var[C('VAR_GROUP')] = GROUP_NAME;
                    }
                }
                if(C('URL_CASE_INSENSITIVE') && isset($var[C('VAR_GROUP')])) {
                    $var[C('VAR_GROUP')] = strtolower($var[C('VAR_GROUP')]);
                }
            }
        }
    }

    if(C('URL_MODEL') == 0) { // 普通模式URL转换
        $url = __APP__.'?'.http_build_query(array_reverse($var));
        if(!empty($vars)) {
            $vars = urldecode(http_build_query($vars));
            $url .= '&'.$vars;
        }
    }else{ // PATHINFO模式或者兼容URL模式
        if(isset($route)) {
            $url = __APP__.'/'.rtrim($url,$depr);
        }else{
            $url = __APP__.'/'.implode($depr,array_reverse($var));
        }
        if(!empty($vars)) { // 添加参数
            foreach ($vars as $var => $val){
                if('' !== trim($val)) $url .= $depr . $var . $depr . urlencode($val);
            }                
        }
        if($suffix) {
            $suffix = $suffix===true ? C('URL_HTML_SUFFIX') : $suffix;
            if($pos = strpos($suffix, '|')){
                $suffix = substr($suffix, 0, $pos);
            }
            if($suffix && '/' != substr($url, -1)){
                $url .= '.'.ltrim($suffix, '.');
            }
        }
    }
    if(isset($anchor)){
        $url .= '#'.$anchor;
    }
    if($domain) {
        $url = (is_ssl() ? 'https://' : 'http://').$domain.$url;
    }
    if($redirect) // 直接跳转URL
        redirect($url);
    else
        return $url;
}

/**
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * @return string
 */
function rand_string($len=6, $type='', $addChars='') {
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 4:
            $chars = '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借'.$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars = $type==1 ? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    if($type!=4) {
        $chars = str_shuffle($chars);
        $str = substr($chars,0,$len);
    }else{
        // 中文随机字
        for($i=0;$i<$len;$i++){
			$str.= msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1);
        }
    }
    return $str;
}

//输出安全的html
function h($text, $tags = null) {
	$text = trim($text);
	//完全过滤注释
	$text = preg_replace('/<!--?.*-->/','',$text);
	//完全过滤动态代码
	$text = preg_replace('/<\?|\?'.'>/','',$text);
	//完全过滤js
	$text = preg_replace('/<script?.*\/script>/','',$text);

	$text = str_replace('[','&#091;',$text);
	$text = str_replace(']','&#093;',$text);
	$text = str_replace('|','&#124;',$text);
	//过滤换行符
	$text = preg_replace('/\r?\n/','',$text);
	//br
	$text = preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
	$text = preg_replace('/<p(\s\/)?'.'>/i','[br]',$text);
	$text = preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
	//过滤危险的属性，如：过滤on事件lang js
	while(preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
		$text = str_replace($mat[0],$mat[1],$text);
	}
	while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
		$text = str_replace($mat[0],$mat[1].$mat[3],$text);
	}
	if(empty($tags)) {
		$tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
	}
	//允许的HTML标签
	$text = preg_replace('/<('.$tags.')( [^><\[\]]*)>/i','[\1\2]', $text);
	$text = preg_replace('/<\/('.$tags.')>/Ui','[/\1]', $text);
	//过滤多余html
	$text = preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i','',$text);
	//过滤合法的html标签
	while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i', $text, $mat)){
		$text = str_replace($mat[0], str_replace('>', ']', str_replace('<', '[', $mat[0])), $text);
	}
	//转换引号
	while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i', $text, $mat)){
		$text = str_replace($mat[0], $mat[1].'|'.$mat[3].'|'.$mat[4], $text);
	}
	//过滤错误的单个引号
	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i', $text, $mat)){
		$text = str_replace($mat[0], str_replace($mat[1], '', $mat[0]), $text);
	}
	//转换其它所有不合法的 < >
	$text = str_replace('<', '&lt;', $text);
	$text = str_replace('>', '&gt;', $text);
	$text = str_replace('"', '&quot;', $text);
	 //反转换
	$text = str_replace('[', '<', $text);
	$text = str_replace(']', '>', $text);
	$text = str_replace('|', '"', $text);
	//过滤多余空格
	$text = str_replace('  ', ' ', $text);
	return $text;
}

function ubb($Text) {
	$Text = trim($Text);
	//$Text = htmlspecialchars($Text);
	$Text = preg_replace("/\\t/is", '  ',$Text);
	$Text = preg_replace("/\[h1\](.+?)\[\/h1\]/is", "<h1>\\1</h1>", $Text);
	$Text = preg_replace("/\[h2\](.+?)\[\/h2\]/is", "<h2>\\1</h2>", $Text);
	$Text = preg_replace("/\[h3\](.+?)\[\/h3\]/is", "<h3>\\1</h3>", $Text);
	$Text = preg_replace("/\[h4\](.+?)\[\/h4\]/is", "<h4>\\1</h4>", $Text);
	$Text = preg_replace("/\[h5\](.+?)\[\/h5\]/is", "<h5>\\1</h5>", $Text);
	$Text = preg_replace("/\[h6\](.+?)\[\/h6\]/is", "<h6>\\1</h6>", $Text);
	$Text = preg_replace("/\[separator\]/is", '', $Text);
	$Text = preg_replace("/\[center\](.+?)\[\/center\]/is", "<center>\\1</center>", $Text);
	$Text = preg_replace("/\[url=http:\/\/([^\[]*)\](.+?)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\2</a>", $Text);
	$Text = preg_replace("/\[url=([^\[]*)\](.+?)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\2</a>", $Text);
	$Text = preg_replace("/\[url\]http:\/\/([^\[]*)\[\/url\]/is", "<a href=\"http://\\1\" target=_blank>\\1</a>", $Text);
	$Text = preg_replace("/\[url\]([^\[]*)\[\/url\]/is", "<a href=\"\\1\" target=_blank>\\1</a>", $Text);
	$Text = preg_replace("/\[img\](.+?)\[\/img\]/is", "<img src=\"\\1\">", $Text);
	$Text = preg_replace("/\[color=(.+?)\](.+?)\[\/color\]/is", "<font color=\"\\1\">\\2</font>", $Text);
	$Text = preg_replace("/\[size=(.+?)\](.+?)\[\/size\]/is", "<font size=\"\\1\">\\2</font>", $Text);
	$Text = preg_replace("/\[sup\](.+?)\[\/sup\]/is", "<sup>\\1</sup>", $Text);
	$Text = preg_replace("/\[sub\](.+?)\[\/sub\]/is", "<sub>\\1</sub>", $Text);
	$Text = preg_replace("/\[pre\](.+?)\[\/pre\]/is", "<pre>\\1</pre>", $Text);
	$Text = preg_replace("/\[email\](.+?)\[\/email\]/is", "<a href='mailto:\\1'>\\1</a>", $Text);
	$Text = preg_replace("/\[colorTxt\](.+?)\[\/colorTxt\]/eis", "color_txt('\\1')", $Text);
	$Text = preg_replace("/\[emot\](.+?)\[\/emot\]/eis", "emot('\\1')", $Text);
	$Text = preg_replace("/\[i\](.+?)\[\/i\]/is", "<i>\\1</i>", $Text);
	$Text = preg_replace("/\[u\](.+?)\[\/u\]/is", "<u>\\1</u>", $Text);
	$Text = preg_replace("/\[b\](.+?)\[\/b\]/is", "<b>\\1</b>", $Text);
	$Text = preg_replace("/\[quote\](.+?)\[\/quote\]/is", " <div class='quote'><h5>引用:</h5><blockquote>\\1</blockquote></div>", $Text);
	$Text = preg_replace("/\[code\](.+?)\[\/code\]/eis", "highlight_code('\\1')", $Text);
	$Text = preg_replace("/\[php\](.+?)\[\/php\]/eis", "highlight_code('\\1')", $Text);
	$Text = preg_replace("/\[sig\](.+?)\[\/sig\]/is", "<div class='sign'>\\1</div>", $Text);
	$Text = preg_replace("/\\n/is", '<br/>', $Text);

	$Text = preg_replace("/\[table=(.+?)\](.+?)\[\/table\]/is", "<table style='width:100%;display:none'>\\2</table>", $Text);
	$Text = preg_replace("/\[table\](.+?)\[\/table\]/is", "<table style='width:100%'>\\1</table>", $Text);
	$Text = preg_replace("/\[tr\](.+?)\[\/tr\]/is", "<tr>\\1</tr>", $Text);
	$Text = preg_replace("/\[td\](.+?)\[\/td\]/is", "<td>\\1</td>", $Text);
	$Text = preg_replace("/\[th\](.+?)\[\/th\]/is", "<th>\\1</th>", $Text);
	//$Text = preg_replace("/\[hide\](.+?)\[\/hide\]/is", "<div><p><a href='javascript:;' onclick='\$\(\$\(this\)\.next\(\)\)\.toogle\(\)'>显示\/隐藏<\/a><\/p><div style='display:none'>\\1<\/div>", $Text);
	$Text = str_replace('[hide]', '<a href="javascript:;" onclick="$(this).blur().next().toggle()">显示/隐藏</a>', $Text);
	$Text = str_replace('[/hide]', '', $Text);
	return $Text;
}

function setToken($formName){
	global $_G, $template;
	$Key = md5($_SERVER['HTTP_USER_AGENT'] . $_G['authkey'] . $_SERVER['HTTP_HOST'] . $_G['clientip']);
	$codeStr = authcode(mt_rand(1000,9999).$formName.random(6).TIMESTAMP, 'ENCODE', $Key);
	/*$sessions = $cache->get($_G['sid'].'Tokens');
	if($sessions === null) $sessions = array();
	$sessions[$formName.'Token'] = $codeStr;
	$cache->set($_G['sid'].'Tokens', $sessions, 0);*/
	//
	//$setName = authcode($formName.'_Token', 'ENCODE', $Key);
	$setName = md5($formName . '_Token_' . $_SERVER['HTTP_USER_AGENT'] . $_G['authkey'] . $_SERVER['HTTP_HOST'] . $_G['clientip']);
	$setName = substr(md5($setName.TIMESTAMP.random(6)), 0, 6).crc32(TIMESTAMP.FORMHASH.$_G['sid'].random(3));
	$_SESSION[$formName.'Token'] = array(
		'name' => $setName,
		'hash' => $codeStr,
		'time' => TIMESTAMP
	);
	$template->assign($formName.'Token', '<input type="hidden" name="'.$setName.'" value="'.$codeStr.'"/>');
	$template->assign($formName.'TokenHash', md5($setName));
	//trace("setToken: {$setName} = {$codeStr}");
}

function submitcheck($var, $allowget = 0, $seccodecheck = null, $secqaacheck = 0) {
	global $_G, $errmsg;
	$time = 300;
	$name = $var;
	$session = $_SESSION[$name.'Token'];
	unset($_SESSION[$name.'Token']);
	//$var = md5($var . '_Token_' . $_SERVER['HTTP_USER_AGENT'] . $_G['authkey'] . $_SERVER['HTTP_HOST'] . $_G['clientip']);
	$var = $session['name'];
	if(empty($_REQUEST[$var])) return false;
	$token = $allowget ? $_GET[$var] : $_POST[$var];
	$seccodecheck = $seccodecheck === null ? need_seccode($name) : $seccodecheck;
	/*$sessions = $cache->get($_G['sid'].'Tokens');
	//trace($sessions);
	$session = '';
	if($sessions !== null){
		$session = isset($sessions[$name.'Token']) ? $sessions[$name.'Token'] : '';
		unset($sessions[$name.'Token']);
		if(empty($sessions)){
			$cache->delete($_G['sid'].'Tokens');
		}else{
			$cache->set($_G['sid'].'Tokens', $sessions, 0);
		}
	}*/
	//$session = $_SESSION[$name.'Token'];
	//unset($_SESSION[$name.'Token']);
	//trace($session);
	if($session['time'] < TIMESTAMP-$time){
		$errmsg = 'token_expired';
		return false;
	}elseif($token === $session['hash']){
		require_once libfile('function/seccode');
		if($seccodecheck && !check_seccode(null, md5($var))){
			$errmsg = 'seccode_incorrect';
			return false;
		}
		if($secqaacheck){
			$errmsg = 'secqaa_incorrect';
			return false;
		}
		return true;
	}
	$errmsg = 'undefined_err';
	return false;
}

function fileext($filename) {
	return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
}

// 随机生成一组字符串
function build_count_rand($number, $length=4, $mode=1) {
    if($mode==1 && $length<strlen($number) ) {
        //不足以生成一定数量的不重复数字
        return false;
    }
    $rand   =  array();
    for($i=0; $i<$number; $i++) {
        $rand[] =   rand_string($length,$mode);
    }
    $unqiue = array_unique($rand);
    if(count($unqiue)==count($rand)) {
        return $rand;
    }
    $count   = count($rand)-count($unqiue);
    for($i=0; $i<$count*3; $i++) {
        $rand[] = rand_string($length,$mode);
    }
    $rand = array_slice(array_unique ($rand),0,$number);
    return $rand;
}

function remove_xss($val) {
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   // this prevents some character re-spacing such as <java\0script>
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

   // straight replacements, the user should never need these since they're normal characters
   // this prevents like <IMG SRC=@avascript:alert('XSS')>
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
      // ;? matches the ;, which is optional
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

      // @ @ search for the hex values
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
      // @ @ 0{0,7} matches '0' zero to seven times
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   }

   // now the only remaining whitespace attacks are \t, \n, and \r
   $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);

   $found = true; // keep replacing as long as the previous round replaced something
   while ($found == true) {
      $val_before = $val;
      for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
            if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
         if ($val_before == $val) {
            // no replacements were made, so exit the loop
            $found = false;
         }
      }
   }
   return $val;
}

/**
 * 把返回的数据集转换成Tree
 * @access public
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}

/**
 * 在数据列表中搜索
 * @access public
 * @param array $list 数据列表
 * @param mixed $condition 查询条件
 * 支持 array('name'=>$value) 或者 name=$value
 * @return array
 */
function list_search($list,$condition) {
    if(is_string($condition))
        parse_str($condition,$condition);
    // 返回的结果集合
    $resultSet = array();
    foreach ($list as $key=>$data){
        $find = false;
        foreach ($condition as $field=>$value){
            if(isset($data[$field])) {
                if(0 === strpos($value,'/')) {
                    $find = preg_match($value,$data[$field]);
                }elseif($data[$field]==$value){
                    $find = true;
                }
            }
        }
        if($find)
            $resultSet[] = &$list[$key];
    }
    return $resultSet;
}

// 自动转换字符集 支持数组转换
function auto_charset($fContents, $from='gbk', $to='utf-8') {
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = auto_charset($key, $from, $to);
            $fContents[$_key] = auto_charset($val, $from, $to);
            if ($key != $_key)
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else {
        return $fContents;
    }
}

function process($msg='[FRAMEWORK_DEBUG]', $label=''){
	/*global $_G;
	if(APP_FRAMEWORK_DEBUG){
		$_G['debug']['process'][] = $msg;
	}*/
	return trace($msg, $label, 'process');
}

/*function trace($msg){
	global $_G;
	if(APP_FRAMEWORK_DEBUG){
		$_G['debug']['trace'][] = $msg;
	}
}*/

function trace($value='[FRAMEWORK_DEBUG]', $label='', $level='trace', $record=false) {
	static $_trace = array();
	global $_G, $action;
	if(!defined('APP_FRAMEWORK_DEBUG') || !APP_FRAMEWORK_DEBUG || empty($action) || in_array($action, $_G['config']['trace_disabled'])) return;
	if($value === '[FRAMEWORK_DEBUG]'){//获取trace信息
		return $_trace;
	}else{
		$info = ($label ? $label.':' : '').print_r($value, true);
		/*if($level == 'ERR'  && C('TRACE_EXCEPTION')) {// 抛出异常
			throw_exception($info);
		}*/
		//$level = strtoupper($level);
		if(!isset($_trace[$level])) {
			$_trace[$level] = array();
		}
		$_trace[$level][] = $info;
		/*if((defined('IS_AJAX') && IS_AJAX) || !C('SHOW_PAGE_TRACE')  || $record) {
			Log::record($info,$level,$record);
		}*/
	}
}

function err($msg='[FRAMEWORK_DEBUG]', $label=''){
	/*global $_G;
	if(APP_FRAMEWORK_DEBUG){
		$_G['debug']['error'][] = $msg;
	}*/
	return trace($msg, $label, 'error');
}

function error($message, $vars = array(), $return = false) {
	global $_G;
	$message = ($_G['inajax'] && !is_utf8($message)) ? mb_convert_encoding(lang('error', $message), 'UTF-8', 'GBK') : lang('error', $message);
	if($_G['inajax']) {
		foreach($vars as $k => $v) $vars[$k] = is_utf8($v) ? $v : mb_convert_encoding($v, 'UTF-8', 'GBK');
	}
	$message = str_replace(array_keys($vars), $vars, $message);
	if($_G['inajax']) $message = is_utf8($message) ? $message : mb_convert_encoding($message, 'UTF-8', 'GBK');
	//discuz_core::error_log($message);
	if(!$return) {
		@header('Content-Type: text/html; charset='.CHARSET);
		halt($message);
		//exit($message);
	} else {
		return $message;
	}
}

function halt($error, $param=array()){
	static $halted = false;
	if(!$halted){
		$e = array();
		//ob_clean();
		$halted = true;
		ob_end_clean();
		ob_start(getglobal('gzipcompress') ? 'ob_gzhandler' : null);
		if(APP_FRAMEWORK_DEBUG) {
			if(!is_array($error)) {
				$trace = debug_backtrace();
				$e['message'] = $error;
				$e['file'] = $trace[0]['file'];
				$e['line'] = $trace[0]['line'];
				ob_start();
				debug_print_backtrace();
				$e['trace'] = ob_get_clean();
			} else {
				$e = $error;
			}
			foreach($e as $k => $v){
				if(is_array($v)){
					foreach($v as $ck => $cv){
						$e[$k][$sk] = str_replace(APP_FRAMEWORK_ROOT, '.', $sv);
					}
				}elseif(is_string($v)){
					$e[$k] = str_replace(APP_FRAMEWORK_ROOT, '.', $v);
				}
			}
		} else {
			$e['message'] = is_array($error) ? $error['message'] : $error;
		}
		$e['message'] = lang('error', $e['message'], empty($param) ? null : (is_array($param) ? $param : array('str' => $param)));
		;
		process('错误：'.$e['message']);
		if(!APP_FRAMEWORK_DEBUG && isset($param['__ERRMSG__'])) $e['message'] = lang('error', $param['__ERRMSG__']);
		if(defined('PHPNEW_STATIC_TPL')) global $template;
		if(isset($template) && file_exists($template->templates_dir.'error'.$template->templates_postfix)){
			$template->assign('e', $e);
			$template->display('error');
		}else{
			include APP_FRAMEWORK_ROOT.'/source/ErrorException.php';
		}
	}
	exit;
}

/*function error_handler($errno, $errstr, $errfile='', $errline=0, $errcontext=array()){
	//echo("ERR$errno ");
	$errfile = str_replace(APP_FRAMEWORK_ROOT, '.', $errfile);
	err("PHP[{$errno}] {$errstr}[br]　　　　@ {$errfile}:{$errline}\n");
	//if(in_array($errno, array(E_USER_ERROR, E_PARSE, E_COMPILE_ERROR))) halt("[{$errno}] {$errstr}");
	return false;
}*/

/**
 * 自定义异常处理
 * @param mixed $e 异常对象
 */
function exception_handler($e) {
	$error = array();
	$error['message'] = $e->getMessage();
	$trace = $e->getTrace();
	if('throw_exception' == $trace[0]['function']){
		$error['file'] = $trace[0]['file'];
		$error['line'] = $trace[0]['line'];
	}else{
		$error['file'] = $e->getFile();
		$error['line'] = $e->getLine();
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
function error_handler($errno, $errstr, $errfile, $errline) {
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
			function_exists('halt') ? halt($errorStr) : exit('ERROR:'.$errorStr);
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
}

// 致命错误捕获
function fatalError() {
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

//设定错误和异常处理
register_shutdown_function('fatalError');
set_error_handler('error_handler');
set_exception_handler('exception_handler');