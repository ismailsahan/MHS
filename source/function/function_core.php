<?php

define('APP_FRAMEWORK_CORE_FUNCTION', TRUE);

/**
 * 加解密函数
 * 
 * @param string  $string    要加密或解密的字串
 * @param string  $operation 操作模式（DECODE解密 ENCODE加密）默认为DECODE
 * @param string  $key       密钥，默认为$_G['authkey']
 * @param integer $expiry    有效期（单位秒）0表示永不过期
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	global $_G;
	//$string = ($operation == 'DECODE') ? str_replace(' ', '+', $string) : $string;

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

/**
 * 生成随机文本或数字
 * 
 * @param integer $length  长度
 * @param boolean $numeric 随机生成的结果是否为数字（1表示数字 0表示文本）默认为0
 * @return string|int|float
 */
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

/**
 * 计算表单HASH
 * 建议使用 setToken 替代
 * 
 * @deprecated
 * @param string $specialadd 可选特征值，默认为空
 * @return string
 */
function formhash($specialadd = '') {
	global $_G;
	$hashadd = defined('IN_ADMINCP') ? 'Only For Admin Control Panel' : '';
	return substr(md5(substr($_G['timestamp'], 0, -7).$_G['username'].$_SERVER['HTTP_USER_AGENT'].$_G['authkey'].$hashadd.$specialadd), 8, 8);
}

/**
 * 判断输入的字串是否为UTF-8编码
 * 
 * @param string $string 输入的字串
 * @return boolean
 */
function is_utf8($string) {
	return preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/", $string);
}

/**
 * 整理页面调试信息（非开发模式下的调试面板）
 * Processed in xxx second(s), ...
 * 
 * @return boolean 是否显示调试信息
 */
function debuginfo() {
	global $_G, $cache, $template;
	if(getglobal('setting/debug')) {
		$db = DB::object();
		$debuginfo = array(
			'time' => number_format((dmicrotime() - $_G['starttime']), 6),
			'queries' => $db->querynum,
			//'memory' => isset($_G['memory']) ? ucwords(@$_G['memory']) : null,
			'memory' => Cache::$storage=='Files' ? null : Cache::$storage,
			//'mem' => (intval(ini_get('memory_limit')) > 0) ? memory_get_usage() : ''
		);
		if($db->slaveid) {
			$debuginfo['queries'] = 'Total '.$db->querynum.', Slave '.$db->slavequery;
		}
		$_G['debug'] = $debuginfo;
		//$template->assign('debuginfo', $debuginfo, true);
		return true;
	} else {
		return false;
	}
}

/**
 * 改进的mktime
 * 
 * @param type $date 
 * @return type
 */
function dmktime($date) {
	if(strpos($date, '-')) {
		$time = explode('-', $date);
		return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
	}
	return 0;
}

/**
 * 改进的microtime
 * 
 * @return type
 */
function dmicrotime() {
	return array_sum(explode(' ', microtime()));
}

/**
 * 格式化时间
 */
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

/**
 * 判断子串是否在另一字串中存在（区分大小写）
 * 
 * @param string $string 被搜索的字符串
 * @param string $find   要查找的字符串
 * @return boolean
 */
function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

/**
 * 判断子串是否在另一字串中存在（不区分大小写）
 * 
 * @param string $string 被搜索的字符串
 * @param string $find   要查找的字符串
 * @return boolean
 */
function striexists($string, $find) {
	return !(stripos($string, $find) === FALSE);
}

function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		$keys = array_keys($string);
		foreach($keys as $key) {
			$val = $string[$key];
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

/**
 * 改进的htmlspecialchars
 * 
 * @param type $string 
 * @param type $flags 
 * @return type
 */
function dhtmlspecialchars($string, $flags = null) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val, $flags);
		}
	} else {
		if($flags === null) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== false) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		} else {
			if(PHP_VERSION < '5.4.0') {
				$string = htmlspecialchars($string, $flags);
			} else {
				if(strtolower(CHARSET) == 'utf-8') {
					$charset = 'UTF-8';
				} else {
					$charset = 'ISO-8859-1';
				}
				$string = htmlspecialchars($string, $flags, $charset);
			}
		}
	}
	return $string;
}

/**
 * 检查是否为机器人
 * 
 * @param string $useragent 用户代理识别字串
 * @return bool
 */
function checkrobot($useragent = '') {
	static $kw_spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
	static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

	$useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
	if(strpos($useragent, 'http://') === false && dstrpos($useragent, $kw_browsers)) return false;
	if(dstrpos($useragent, $kw_spiders)) return true;
	return false;
}

/**
 * 将文件数据转化为Base64处理的字符串，以嵌入HTML, CSS
 * 
 * @param string $path 文件路径
 * @return string 处理结果
 */
function file2base64($path) {
	$data = @file_get_contents($path);
	if(!$data) return $path;
	$data = base64_encode($data);
	//$data = chunk_split($data);
	switch(fileext($path)){//判读图片类型
		case 'gif': $type='image/gif'; break;
		case 'jpg': $type='image/jpg'; break;
		case 'png': $type='image/png'; break;
		default   : $type='';
	}
	return 'data:'.($type ? $type.';base64,' : ',').$data;
}

/**
 * 添加邮件到队列，或者直接发送邮件（当level为0时）
 * 
 * @param string $uids		用户 ID 多个用逗号(,)隔开
 * @param string $emails	目标email，多个用逗号(,)隔开
 * @param string $subject	邮件标题
 * @param string $message	邮件内容
 * @param string $frommail	发信人，可选参数，默认为空，uc后台设置的邮件来源作为发信人地址
 * @param string $charset	邮件字符集，可选参数，默认为 UTF-8
 * @param bool   $htmlon	是否是html格式的邮件，可选参数，默认为ture，即HTML邮件
 * @param int    $level		邮件级别，可选参数，数字大的优先发送，取值为0的时候立即发送，邮件不入队列
 * @return mixed false:失败：进入队列失败，或者发送失败  int:成功：进入队列的邮件的id，当level为0，则返回1
 */
function mailqueue($uids, $emails, $subject, $message, $frommail=null, $charset='UTF-8', $htmlon=true, $level=0) {
	require_once libfile('client', '/uc_client');
	return uc_mail_queue($uids, $emails, $subject, $message, $frommail, $charset, $htmlon, $level);
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

/**
 * 设置Cookie
 */
function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {
	global $_G;

	$config = &$_G['config']['cookie'];

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

/**
 * 改进的strpos
 * 
 * @param type $string 
 * @param type $arr 
 * @param type $returnvalue 
 * @return type
 */
function dstrpos($string, $arr, $returnvalue = false) {
	if(empty($string)) return false;
	foreach((array)$arr as $v) {
		if(strpos($string, $v) !== false) {
			$return = $returnvalue ? $v : true;
			return $return;
		}
	}
	return false;
}

/**
 * 检查格式是否符合 Email
 *
 * @param string $email 输入文本
 * @return bool
 */
function isemail($email) {
	return strlen($email) > 6 && strlen($email) <= 32 && preg_match("/^([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-]+[.][A-Za-z0-9\-.]+)$/", $email);
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

/**
 * 改进的implode
 * 
 * @param type $array 
 * @return type
 */
function dimplode($array) {
	if(!empty($array)) {
		$array = array_map('addslashes', $array);
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}

/**
 * 改进的strlen 用于计算字串长度
 * 
 * @param string $str 输入字串
 * @return int
 */
function dstrlen($str) {
	if(strtolower(CHARSET) != 'utf-8') {
		return strlen($str);
	}
	$count = 0;
	for($i = 0; $i < strlen($str); $i++){
		$value = ord($str[$i]);
		if($value > 127) {
			$count++;
			if($value >= 192 && $value <= 223) $i++;
			elseif($value >= 224 && $value <= 239) $i = $i + 2;
			elseif($value >= 240 && $value <= 247) $i = $i + 3;
	    	}
    		$count++;
	}
	return $count;
}

function cutstr($string, $length, $dot = ' ...') {
	if(strlen($string) <= $length) {
		return $string;
	}

	$pre = chr(1);
	$end = chr(1);
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

	$strcut = '';
	if(strtolower(CHARSET) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		$_length = $length - 1;
		for($i = 0; $i < $length; $i++) {
			if(ord($string[$i]) <= 127) {
				$strcut .= $string[$i];
			} else if($i < $_length) {
				$strcut .= $string[$i].$string[++$i];
			}
		}
	}

	$strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	$pos = strrpos($strcut, chr(1));
	if($pos !== false) {
		$strcut = substr($strcut,0,$pos);
	}
	return $strcut.$dot;
}

/**
 * 改进的stripslashes
 * 
 * @param type $string 
 * @return type
 */
function dstripslashes($string) {
	if(empty($string)) return $string;
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

function dreferer($default = '') {
	global $_G;

	$default = empty($default) ? $GLOBALS['_t_curapp'] : '';
	$_G['referer'] = !empty($_GET['referer']) ? $_GET['referer'] : $_SERVER['HTTP_REFERER'];
	$_G['referer'] = substr($_G['referer'], -1) == '?' ? substr($_G['referer'], 0, -1) : $_G['referer'];

	if(strpos($_G['referer'], 'member.php?mod=logging')) {
		$_G['referer'] = $default;
	}
	$_G['referer'] = dhtmlspecialchars($_G['referer'], ENT_QUOTES);
	$_G['referer'] = str_replace('&amp;', '&', $_G['referer']);
	$reurl = parse_url($_G['referer']);
	if(!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.'.$_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.'.$reurl['host']))) {
		if(!in_array($reurl['host'], $_G['setting']['domain']['app']) && !isset($_G['setting']['domain']['list'][$reurl['host']])) {
			$domainroot = substr($reurl['host'], strpos($reurl['host'], '.')+1);
			if(empty($_G['setting']['domain']['root']) || (is_array($_G['setting']['domain']['root']) && !in_array($domainroot, $_G['setting']['domain']['root']))) {
				$_G['referer'] = $_G['setting']['domain']['defaultindex'] ? $_G['setting']['domain']['defaultindex'] : 'index.php';
			}
		}
	} elseif(empty($reurl['host'])) {
		$_G['referer'] = $_G['siteurl'].'./'.$_G['referer'];
	}

	return strip_tags($_G['referer']);
}

function renum($array) {
	$newnums = $nums = array();
	foreach ($array as $id => $num) {
		$newnums[$num][] = $id;
		$nums[$num] = $num;
	}
	return array($nums, $newnums);
}

/**
 * 生成文件大小字串，支持自动选择单位
 * 
 * @param int|float $size 大小（单位字节）
 * @return string
 */
function sizecount($size) {
	if($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . ' GB';
	} elseif($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . ' MB';
	} elseif($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . ' KB';
	} else {
		$size = $size . ' Bytes';
	}
	return $size;
}

function swapclass($class1, $class2 = '') {
	static $swapc = null;
	$swapc = isset($swapc) && $swapc != $class1 ? $class1 : $class2;
	return $swapc;
}

/**
 * 检查指定IP是否具有访问权限
 * 
 * @param string $ip IP地址
 * @param type $accesslist IP地址列表
 * @return bool
 */
function ipaccess($ip, $accesslist) {
	return preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($accesslist, '/')).")/", $ip);
}

function ipbanned($onlineip) {
	global $_G;

	if($_G['setting']['ipaccess'] && !ipaccess($onlineip, $_G['setting']['ipaccess'])) {
		return TRUE;
	}

	loadcache('ipbanned');
	if(empty($_G['cache']['ipbanned'])) {
		return FALSE;
	} else {
		if($_G['cache']['ipbanned']['expiration'] < TIMESTAMP) {
			require_once libfile('function/cache');
			updatecache('ipbanned');
		}
		return preg_match("/^(".$_G['cache']['ipbanned']['regexp'].")$/", $onlineip);
	}
}

function strhash($string, $operation = 'DECODE', $key = '') {
	$key = md5($key != '' ? $key : getglobal('authkey'));
	if($operation == 'DECODE') {
		$hashcode = gzuncompress(base64_decode(($string)));
		$string = substr($hashcode, 0, -16);
		$hash = substr($hashcode, -16);
		unset($hashcode);
	}

	$vkey = substr(md5($string.substr($key, 0, 16)), 4, 8).substr(md5($string.substr($key, 16, 16)), 18, 8);

	if($operation == 'DECODE') {
		return $hash == $vkey ? $string : '';
	}

	return base64_encode(gzcompress($string.$vkey));
}

/**
 * 改进的unserialize
 * 
 * @param type $data 
 * @return type
 */
function dunserialize($data) {
	if(($ret = unserialize($data)) === false) {
		$ret = unserialize(stripslashes($data));
	}
	return $ret;
}

/**
 * 获取浏览器版本信息
 * 
 * @param string $type 类型
 * @return type
 */
function browserversion($type) {
	static $return = array();
	static $types = array('ie' => 'msie', 'firefox' => '', 'chrome' => '', 'opera' => '', 'safari' => '', 'mozilla' => '', 'webkit' => '', 'maxthon' => '', 'qq' => 'qqbrowser');
	if(!$return) {
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$other = 1;
		foreach($types as $i => $v) {
			$v = $v ? $v : $i;
			if(strpos($useragent, $v) !== false) {
				preg_match('/'.$v.'(\/|\s)([\d\.]+)/i', $useragent, $matches);
				$ver = $matches[2];
				$other = $ver !== 0 && $v != 'mozilla' ? 0 : $other;
			} else {
				$ver = 0;
			}
			$return[$i] = $ver;
		}
		$return['other'] = $other;
	}
	return $return[$type];
}

/**
 * 获取 Cookie 信息
 * 也可直接引用 $_G['cookie'][$key]
 * 
 * @param string $key Cookie名
 * @return mixed
 */
function getcookie($key) {
	global $_G;
	return isset($_G['cookie'][$key]) ? $_G['cookie'][$key] : '';
}

/**
 * 语言包调用函数
 * 
 * @param string  $file    语言包文件
 * @param string  $langvar 语言键名（为空时返回整个文件的语言数组，默认为空）
 * @param array   $vars    变量替换参数（将键名替换为对应的值，默认为空）
 * @param mixed   $default 默认值参数（默认为空）
 * @param boolean $raw     替换变量时是否返回对变量对象的引用（仅对$_G有效，默认为false）
 * @return mixed
 */
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
		if(isset($vars[0]) && is_string($return)){
			array_unshift($vars, $return);
			$return = call_user_func_array('sprintf', $vars);
		}else{
			foreach($vars as $k => $v) {
				$searchs[] = '{'.$k.'}';
				$replaces[] = $v;
			}
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
	//trace("lang($file, $langvar) = $return");
	return $return;
}

/**
 * 取得库文件路径
 * 
 * @param string  $libname      库文件名（无前缀后缀）
 * @param string  $folder       所在文件夹（默认为空）
 * @param boolean $chkExistence 检查存在性（默认为true）
 * @param string|boolean
 */
function libfile($libname, $folder = '', $chkExistence = true) {
	$libpath = !empty($folder) && substr($folder, 0, 1) == '/' ? $folder : '/source'.($folder == '' ? '' : '/'.$folder);
	if(strstr($libname, '/')) {
		list($pre, $name, $ext) = explode('/', $libname);
		$path = $ext ? "{$libpath}/{$pre}/{$name}/{$ext}" : "{$libpath}/{$pre}/{$pre}_{$name}";
	} else {
		$path = "{$libpath}/{$libname}";

	}
	//trace("libfile({$libname}, '{$folder}') $= {$path}.php");
	$return = preg_match('/^[\w\d\/\._]+$/i', $path) ? realpath(APP_FRAMEWORK_ROOT.$path.'.php') : false;
	//$chkExistence && !file_exists($path) && halt('LIBRARY_FILE_NONEXISTENT', $lib);
	$chkExistence && !$return && halt('LIBRARY_FILE_NONEXISTENT', (empty($folder) ? '' : "{$folder}/").$libname);
	return $return;
}

/**
 * 判断是否需要验证码
 * 
 * @param string|int $mod 操作名
 * @return boolean
 */
function need_seccode($mod){
	global $_G;
	return in_array($mod, $_G['setting']['seccodestatus']);
}

/**
 * 检查是否已登录
 * 
 * @return boolean
 */
function chklogin(){
	global $_G;
	if(isset($_SESSION['user']['activated']) && !$_SESSION['user']['activated']){
		redirect(U('logging/activate'));
		exit;
	}
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
 * @param string  $url   重定向的URL地址
 * @param integer $time  重定向的等待时间（秒）
 * @param string  $msg   重定向前的提示信息
 * @param boolean $theme 是否使用主题
 * @return void
 */
function redirect($url, $time=0, $msg='', $theme=false) {
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
			if($theme) {
				global $template;
				$template->assign('msg', $msg, true);
				$template->display('redirect');
			} else {
				echo($msg);
			}
		}
		exit();
	} else {
		$str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
		if ($time != 0)
			$str .= $msg;
		exit($str);
	}
}

function redirecthtml($url, $time=3000, $method='replace') {
	$url = U($url);
	return "<script type=\"text/javascript\">setTimeout(\"window.location.{$method}('{$url}')\", {$time})</script>";
}

/**
 * XML编码
 * @param mixed  $data		数据
 * @param string $root		根节点名
 * @param string $item		数字索引的子节点名
 * @param string $attr		根节点属性
 * @param string $id		数字索引子节点key转换的属性名
 * @param string $encoding	数据编码
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
 * http://httpstatus.es/
 * http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
 * http://zh.wikipedia.org/wiki/HTTP%E7%8A%B6%E6%80%81%E7%A0%81
 * 
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) {
	static $_status = array(
		// Informational 1xx
		100 => 'Continue',								// Client should continue with request
		101 => 'Switching Protocols',					// Server is switching protocols
		102 => 'Processing',							// Server has received and is processing the request
		103 => 'Checkpoint',							// resume aborted PUT or POST requests
		122 => 'Request-URI Too Long',					// URI is longer than a maximum of 2083 characters
		// Success 2xx
		200 => 'OK',									// standard response for successful HTTP requests
		201 => 'Created',								// request has been fulfilled; new resource created
		202 => 'Accepted',								// request accepted, processing pending
		203 => 'Non-Authoritative Information',			// request processed, information may be from another source
		204 => 'No Content',							// request processed, no content returned
		205 => 'Reset Content',							// request processed, no content returned, reset document view
		206 => 'Partial Content',						// partial resource return due to request header
		207 => 'Multi-Status',							// XML, can contain multiple separate responses
		208 => 'Already Reported',						// results previously returned
		226 => 'IM Used',								// request fulfilled, reponse is instance-manipulations
		// Redirection 3xx
		300 => 'Multiple Choices',						// multiple options for the resource delivered
		301 => 'Moved Permanently',						// this and all future requests directed to the given URI
			//302 => 'Moved Temporarily', // 1.1
		302 => 'Found',									// temporary response to request found via alternative URI
		303 => 'See Other',								// permanent response to request found via alternative URI
		304 => 'Not Modified',							// resource has not been modified since last requested
		305 => 'Use Proxy',								// content located elsewhere, retrieve from there
			// 306 is deprecated but reserved
		306 => 'Switch Proxy',							// subsequent requests should use the specified proxy
		307 => 'Temporary Redirect',					// connect again to different URI as provided
		308 => 'Permanent Redirect',					// connect again to a different URI using the same method
		// Client Error 4xx
		400 => 'Bad Request',							// request cannot be fulfilled due to bad syntax
		401 => 'Unauthorized',							// authentication is possible but has failed
		402 => 'Payment Required',						// payment required, reserved for future use
		403 => 'Forbidden',								// server refuses to respond to request
		404 => 'Not Found',								// requested resource could not be found
		405 => 'Method Not Allowed',					// request method not supported by that resource
		406 => 'Not Acceptable',						// content not acceptable according to the Accept headers
		407 => 'Proxy Authentication Required',			// client must first authenticate itself with the proxy
		408 => 'Request Timeout',						// server timed out waiting for the request
		409 => 'Conflict',								// request could not be processed because of conflict
		410 => 'Gone',									// resource is no longer available and will not be available again
		411 => 'Length Required',						// request did not specify the length of its content
		412 => 'Precondition Failed',					// server does not meet request preconditions
		413 => 'Request Entity Too Large',				// request is larger than the server is willing or able to process
		414 => 'Request-URI Too Long',					// URI provided was too long for the server to process
		415 => 'Unsupported Media Type',				// server does not support media type
		416 => 'Requested Range Not Satisfiable',		// client has asked for unprovidable portion of the file
		417 => 'Expectation Failed',					// server cannot meet requirements of Expect request-header field
		418 => 'I\'m a teapot',							// I'm a teapot
		419 => 'Authentication Timeout',				// 
		420 => 'Method Failure',						// 
		421 => 'Enhance Your Calm',						// 
		422 => 'Unprocessable Entity',					// 
		423 => 'Locked',								// 
		424 => 'Failed Dependency',						// 
		425 => 'Unordered Collection',					// 
		426 => 'Upgrade Required',						// 
		428 => 'Precondition Required',					// 
		429 => 'Too Many Requests',						// 
		431 => 'Request Header Fields Too Large',		// 
		440 => 'Login Timeout',							// 
		444 => 'No Response',							// 
		449 => 'Retry With',							// 
		450 => 'Blocked by Windows Parental Controls',	// 
		451 => 'Wrong Exchange Server',					// 
		494 => 'Request Header Too Large',				// 
		495 => 'Cert Error',							// 
		496 => 'No Cert',								// 
		497 => 'HTTP to HTTPS',							// 
		499 => 'Client Closed Request',					// 
		// Server Error 5xx
		500 => 'Internal Server Error',					// generic error message
		501 => 'Not Implemented',						// server does not recognise method or lacks ability to fulfill
		502 => 'Bad Gateway',							// server received an invalid response from upstream server
		503 => 'Service Unavailable',					// server is currently unavailable
		504 => 'Gateway Timeout',						// gateway did not receive response from upstream server
		505 => 'HTTP Version Not Supported',			// server does not support the HTTP protocol version
		506 => 'Variant Also Negotiates',				// 
		507 => 'Insufficient Storage',					// 
		508 => 'Loop Detected',							// 
		509 => 'Bandwidth Limit Exceeded',				// 
		510 => 'Not Extended',							// 
		511 => 'Network Authentication Required',		// 
		520 => 'Origin Error',							// 
		522 => 'Connection timed out',					// 
		523 => 'Proxy Declined Request',				// 
		524 => 'A timeout occurred',					// 
		598 => 'Network read timeout error',			// 
		599 => 'Network connect timeout error'			// 
	);
	if(isset($_status[$code])) {
		header('HTTP/1.1 '.$code.' '.$_status[$code]);
		// 确保FastCGI模式下正常
		header('Status:'.$code.' '.$_status[$code]);
	}
}

/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[动作/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $redirect 是否跳转，如果设置为true则表示跳转到该URL地址
 * @param boolean $domain 是否显示域名
 * @return string
 */
function U($url='', $vars='', $suffix=true, $redirect=false, $domain=false) {
	//trace(func_get_args());
	return Dispatcher::generate($url, $vars, $suffix, $redirect, $domain);
}

/**
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * @return string
 */
function rand_string($len=6, $type=5, $addChars='') {
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

/**
 * 输出安全的HTML
 */
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

/**
 * 转换 BBCODE
 */
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

/**
 * 设置表单令牌
 * 
 * @param string $formName 表单标识符/特征码/名字
 * @return void
 */
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
	$setName = substr(md5($setName.TIMESTAMP.random(6)), 0, 6).crc32(TIMESTAMP.$_G['authkey'].$_G['sid'].random(3));
	$_SESSION[$formName.'Token'] = array(
		'name' => $setName,
		'hash' => $codeStr,
		'time' => TIMESTAMP
	);
	$template->assign($formName.'Token', '<input type="hidden" name="'.$setName.'" value="'.$codeStr.'"/>', true);
	$template->assign($formName.'TokenHash', md5($setName), true);
	//trace("setToken: {$setName} = {$codeStr}");
}

/**
 * 检查是否正确提交表单
 * 
 * @param string  $var          表单标识符
 * @param string  $errmsg       错误信息
 * @param integer $keepToken    是否保持原表单令牌有效性
 * @param integer $allowget     是否允许GET提交
 * @param integer $seccodecheck 是否检查验证码
 * @param integer $secqaacheck  是否检查安全问答（暂不可用）
 * @return boolean
 */
function submitcheck($var, &$errmsg, $keepToken=0, $allowget=0, $seccodecheck=null, $secqaacheck=0) {
	global $_G;
	$time = 1440;
	$name = $var;
	$session = $_SESSION[$name.'Token'];
	if($unsetToken) {
		unset($_SESSION[$name.'Token']);
	} else {
		$session['time'] = $_SESSION[$name.'Token']['time'] = TIMESTAMP;
	}
	//$var = md5($var . '_Token_' . $_SERVER['HTTP_USER_AGENT'] . $_G['authkey'] . $_SERVER['HTTP_HOST'] . $_G['clientip']);
	$var = $session['name'];
	if(empty($_REQUEST[$var])) return false;
	$token = $allowget ? $_GET[$var] : $_POST[$var];
	$seccodecheck = $seccodecheck === null ? need_seccode($name) : $seccodecheck;
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

/**
 * Ajax方式返回数据到客户端
 * @access protected
 * @param mixed   $data 要返回的数据
 * @param string  $type AJAX返回数据格式
 * @param boolean $encoded $data是否已经json_encode或xml_encode处理
 * @return void
 */
function ajaxReturn($data, $type='', $encoded=false) {
	global $_G;
	if(empty($type)) $type = $_G['config']['app']['default_ajax_return'];
	switch (strtoupper($type)){
		case 'AUTO' :
			// 返回JSON数据格式到客户端 包含状态信息
			header('Content-Type:application/json; charset=utf-8');
			if(!$encoded) $data = json_encode($data);
			$handler = isset($_GET[$_G['config']['app']['var_jsonp_handler']]) ? $_GET[$_G['config']['app']['var_jsonp_handler']] : '';
			exit($handler ? $handler.'('.$data.');' : $data);
		case 'JSON' :
			// 返回JSON数据格式到客户端 包含状态信息
			header('Content-Type:application/json; charset=utf-8');
			if(!$encoded) $data = json_encode($data);
			exit($data);
		case 'JSONP':
			// 返回JSON数据格式到客户端 包含状态信息
			header('Content-Type:application/json; charset=utf-8');
			$handler = isset($_GET[$_G['config']['app']['var_jsonp_handler']]) ? $_GET[$_G['config']['app']['var_jsonp_handler']] : $_G['config']['app']['default_jsonp_handler'];
			if(!$encoded) $data = json_encode($data);
			exit($handler.'('.$data.');');
		case 'XML'  :
			// 返回XML格式数据
			header('Content-Type:text/xml; charset=utf-8');
			if(!$encoded) $data = xml_encode($data);
			exit($data);
		case 'EVAL' :
			// 返回可执行的js脚本
			header('Content-Type:text/html; charset=utf-8');
			exit($data);
		default:
			// 用于扩展其他返回格式数据
			trace('ajax_return', $data);
	}
}

/**
 * 	清除缓存
 */
function clearcache($opt = 1) {
	$options = array(
		'setting'	=> 0,
		'tos'		=> 0,
		'template'	=> 0,
	);
	$clearall = false;

	if(is_string($opt) && $opt!='all') {
		$options[$opt] = 1;
	}elseif(is_array($opt)) {
		$options = array_merge($options, $opt);
	}else{
		$clearall = true;
		foreach ($options as $k => $v) {
			$options[$k] = 1;
		}
	}

	if($clearall) {
		Cache::clean();
	} else {
		if($options['setting']) {
			Cache::delete('setting');
		}
		if($options['tos']) {
			Cache::delete('tos');
		}
	}

	if($options['template']) {
		global $template;
		$template->clearAllCache();

		clearstaticcache();
	}
}

/**
 * 清除由静态引擎产生的缓存
 */
function clearstaticcache($path = array('/', '/tpl/', '/cfg/', '/sessions/')) {
	if(is_array($path)) {
		foreach ($$path as $val) {
			clearstaticcache($val);
		}
		return;
	}

	$files = glob(APP_FRAMEWORK_ROOT.'/cache'.$path.'*');
	foreach($files as $cache) {
		if(is_file($cache) && !in_array(basename($cache), array('index.htm', 'index.html'))) {
			unlink($cache);
		}
	}

	return;
}

/**
 * 取得文件后缀/类型
 * 
 * @param string $filename 文件名或文件路径
 * @return string 文件后缀/类型
 */
function fileext($filename) {
	return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
}

/**
 * 随机生成一组字符串
 */
function build_count_rand($number, $length=4, $mode=1) {
	if($mode==1 && $length<strlen($number) ) {
		//不足以生成一定数量的不重复数字
		return false;
	}
	$rand	=  array();
	for($i=0; $i<$number; $i++) {
		$rand[] = rand_string($length,$mode);
	}
	$unqiue = array_unique($rand);
	if(count($unqiue) == count($rand)) {
		return $rand;
	}
	$count	= count($rand)-count($unqiue);
	for($i=0; $i<$count*3; $i++) {
		$rand[] = rand_string($length,$mode);
	}
	$rand = array_slice(array_unique($rand), 0, $number);
	return $rand;
}

/**
 * 清除XSS
 */
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
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root=0) {
	// 创建Tree
	$tree = array();
	if(is_array($list)) {
		// 创建基于主键的数组引用
		$refer = array();
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] = &$list[$key];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId = $data[$pid];
			if ($root == $parentId) {
				$tree[] = &$list[$key];
			}else{
				if (isset($refer[$parentId])) {
					$parent = &$refer[$parentId];
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
function list_sort_by($list, $field, $sortby='asc') {
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

/**
 * 自动转换字符集 支持数组转换
 */
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

/**
 * 调试 自定义流程信息
 */
function process($msg=null, $label=''){
	/*global $_G;
	if(APP_FRAMEWORK_DEBUG){
		$_G['debug']['process'][] = $msg;
	}*/
	return trace($msg, $label, 'process');
}

/**
 * 调试 自定义调试信息
 */
function trace($value=null, $label='', $level='trace', $record=false) {
	static $_trace = array();
	global $_G;
	if(!defined('APP_FRAMEWORK_DEBUG') || !APP_FRAMEWORK_DEBUG || defined('ACTION_NAME') && in_array('ACTION_NAME', $_G['config']['trace_disabled'])) return;
	if($value === null){//获取trace信息
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

/**
 * 调试 自定义错误信息
 */
function err($msg=null, $label=''){
	/*global $_G;
	if(APP_FRAMEWORK_DEBUG){
		$_G['debug']['error'][] = $msg;
	}*/
	return trace($msg, $label, 'error');
}

/**
 * 处理错误消息
 */
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

/**
 * 终止执行并抛出错误信息
 */
function halt($error, $param=array()){
	exit(framework_error::halt($error, $param));
}


/**
* Error Handler
*
* This function lets us invoke the exception class and
* display errors using the standard error template located
* in application/errors/errors.php
* This function will send the error page directly to the
* browser and exit.
*
* @access	public
* @return	void
*/
function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered') {
	$_error = &load_class('Exceptions', 'core');
	echo $_error->show_error($heading, $message, 'error_general', $status_code);
	exit;
}

/**
 * 显示404错误页面
 */
function show_404($page = '', $log_error = TRUE) {
	global $template;

	if(IS_AJAX) {
		ajaxReturn(array(
			'errno' => 404,
			'msg' => '您所访问的文件已经找不到了...'
		), 'AUTO');

		exit;
	}

	if(!function_exists('defaultNav')) {
		require_once libfile('function/nav');
	}

	/*
	$template->force_compile = true;
	$template->assign('sidebarMenu', defaultNav());
	$template->assign('adminNav', adminNav());
	$template->assign('menuset', array('mhour', OPERATION_NAME));
	*/

	$template->display('404');

	exit;
}

/**
 * 显示暂未上线
 */
function show_developing($action='', $operation='') {
	global $template;

	if(IS_AJAX) {
		ajaxReturn(array(
			'errno' => 501,
			'msg' => '暂未上线，请稍候访问'
		), 'AUTO');

		exit;
	}

	if(!function_exists('defaultNav')) {
		require_once libfile('function/nav');
	}

	if(empty($action))
		$action = ACTION_NAME;
	if(empty($operation))
		$operation = OPERATION_NAME;

	$template->force_compile = true;
	$template->assign('sidebarMenu', defaultNav());
	$template->assign('adminNav', adminNav());
	$template->assign('menuset', array($action, $operation));

	$template->display('developing');

	exit;
}
