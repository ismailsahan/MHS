<?php
function make_seccode($idhash=null, $key=null){
	global $_G;
	$seccodelength = $_G["setting"]["seccodedata"]["length"];
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
	$seccodeauth = authcode(strtoupper($seccode)."\t".(TIMESTAMP - 180)."\t".$idhash."\t".$_G['sid']."\t".$_G['formhash'], 'ENCODE', $key ? $key : $_G['authkey']);
	//dsetcookie('seccode'.$idhash, $auth, 0, 1, true);
	return array($seccode, $seccodeauth);
}

function make_secqaa($idhash) {
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

function _substr($string, $start, $length) {
	$return = array();
	$i = strlen($string);
	for($n = 0; $start <= $i; $n++) {
		$return[] = substr($string, $start, $length);
		$start = $start + $length;
	}
	return $return;
}

function sechtml($seccodeauth, $extend="\t|&||&|") {
	global $_G;
	@list($src, $attr) = explode("\t", $extend);
	@list($width, $height, $attr) = explode("|&|", $attr);
	$width = $width ? $width : $_G['setting']['seccodedata']['width'];
	$height = $height ? $height : $_G['setting']['seccodedata']['height'];
	if($_G['setting']['seccodedata']['type'] == 0 || $_G['setting']['seccodedata']['type'] == 1) {
		return "<img src='index.php?action=seccode{$src}&seccodeauth={$seccodeauth}&{$_G['timestamp']}' width='{$width}' height='{$height}' {$attr}></img>";
	} elseif($_G['setting']['seccodedata']['type'] == 2) {
		return "<embed width='{$width}' height='{$height}' src='index.php?action=seccode{$src}&seccodeauth={$seccodeauth}&{$_G['timestamp']}' quality='high' wmode='transparent' align='middle' menu='false' allowscriptaccess='sameDomain' type='application/x-shockwave-flash'{$attr}>";
	} elseif($_G['setting']['seccodedata']['type'] == 3) {
		$flashvars = urlencode($_G["siteroot"]."index.php?action=seccode{$src}&seccodeauth={$seccodeauth}&{$_G['timestamp']}");
		return "请输入你听到的字符: <embed id='seccodeplayer' width='0' height='0' src='static/seccode/flash/flash1.swf' flashvars='sFile={$flashvars}' menu='false' allowscriptaccess='sameDomain' swliveconnect='true' type='application/x-shockwave-flash'><img border='0' style='vertical-align:middle' src='static/seccode/seccodeplayer.gif'><a href='javascript:;' onclick='$$(\"seccodeplayer\").SetVariable(\"isPlay\", \"1\")'>播放验证码</a>";
	} else {
		return "<img src='index.php?action=seccode&seccodeauth={$seccodeauth}&{$_G['timestamp']}' width='32' height='24'{$attr}></img>";
	}
}

?>