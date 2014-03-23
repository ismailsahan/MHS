<?php

/**
 * DZ验证码函数库
 * 
 * 版本 v0.1.0
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

function make_seccode($tag='__DEFAULT__', $idhash=null, $key=null){
	global $_G;
	$seccodelength = $_G['setting']['seccodedata']['length'];
	$seccode = random($seccodelength * 3, 1);
	$seccodeunits = '';
	if($_G['setting']['seccodedata']['type'] == 1) {
		$lang = lang('seccode', 'chn');
		$len = strtoupper(CHARSET) == 'GBK' ? 2 : 3;
		$code = _substr($seccode, 0, 3);
		$seccode = '';
		for($i = 0; $i < $seccodelength; $i++) {
			$seccode .= substr($lang, $code[$i] * $len, $len);
		}
	} elseif($_G['setting']['seccodedata']['type'] == 3) {
		$s = sprintf('%04s', base_convert($seccode, 10, 20));
		$seccodeunits = 'CEFHKLMNOPQRSTUVWXYZ';
	} else {
		$s = sprintf('%04s', base_convert($seccode, 10, 24));
		$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
	}
	if($seccodeunits) {
		$seccode = '';
		for($i = 0; $i < $seccodelength; $i++) {
			$unit = ord($s{$i});
			$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
		}
	}
	//$seccodeauth = authcode(strtoupper($seccode)."\t".(TIMESTAMP - 180)."\t".$idhash."\t".$_G['sid']."\t".$_G['formhash'], 'ENCODE', $key ? $key : $_G['authkey']);
	if(empty($_SESSION['seccode']) || !is_array($_SESSION['seccode'])) $_SESSION['seccode'] = array();
	$_SESSION['seccode'][$tag] = array(
		'seccode' => $seccode,
		'timestamp' => TIMESTAMP
	);
	//$_SESSION['seccodeauth'] = $seccodeauth;
	//dsetcookie('seccode'.$idhash, $auth, 0, 1, true);
	//return array($seccode, $seccodeauth);
	return $seccode;
}

function make_secqaa($idhash){
	global $_G;
	include getcache('secqaa', 'setting');
	$secqaakey = max(1, random(1, 1));
	if($_G['cache']['secqaa'][$secqaakey]['type']) {
		if(file_exists($qaafile = libfile('secqaa/'.$_G['cache']['secqaa'][$secqaakey]['question'], 'class'))) {
			@include_once $qaafile;
			$class = 'secqaa_'.$_G['cache']['secqaa'][$secqaakey]['question'];
			if(class_exists($class)) {
				$qaa = new $class();
				if(method_exists($qaa, 'make')) {
					$_G['cache']['secqaa'][$secqaakey]['answer'] = md5($qaa->make($_G['cache']['secqaa'][$secqaakey]['question']));
				}
			}
		}
	}
	dsetcookie('secqaa'.$idhash, authcode($_G['cache']['secqaa'][$secqaakey]['answer']."\t".(TIMESTAMP - 180)."\t".$idhash."\t".$_G['sid']."\t".FORMHASH, 'ENCODE', $_G['config']['security']['authkey']), 0, 1, true);
	return $_G['cache']['secqaa'][$secqaakey]['question'];
}

function check_seccode($seccode = '', $tag = '__DEFAULT__', $clear = true){
	global $errmsg;
	$time = 900;
	if(empty($seccode)) $seccode = isset($_POST['verifycode']) ? $_POST['verifycode'] : $_POST['seccode'];
	$seccode = strtoupper($seccode);
	$tag = isset($_SESSION['seccode'][$tag]) ? $tag : '__DEFAULT__';
	$tmp = $_SESSION['seccode'][$tag];
	if($clear){
		unset($_SESSION['seccode'][$tag]);
		//unset($_SESSION['seccodeauth']);
	}
	if($tmp['timestamp'] < TIMESTAMP-$time) $errmsg = 'seccode_expired';
	return $tmp['timestamp']<TIMESTAMP-$time ? false : ($seccode === $tmp['seccode']);
}

function _substr($string, $start, $length) {
	$return = array();
	$i = strlen($string);
	for($n = 0; $start <= $i; $n++) {
		$return[] = substr($string, $start, $length);
		$start = $start + $length;
	}
	return $return;
}
