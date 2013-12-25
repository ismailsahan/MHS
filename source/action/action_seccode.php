<?php

/**
 * 验证码
 */

error_reporting(0);

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

require_once libfile('function/seccode');

switch($operation){
	case 'update':
		/*if(!strstr($_G['referer'], '/?') && !strstr($_G['referer'], '/index')) {
			define('IN_ADMINCP', TRUE);
			$_G['formhash'] = formhash();
		}*/
		make_seccode();
		//exit($sec[1]);
		break;
	case 'check':
		check_seccode($_GET['seccode'], $_GET['tag']);
		break;
	case 'html':
		$html = '<div class="seccodeImg" onmouseenter="$.fn.seccode.list[$.fn.seccode.id(\'#'.$id.'\')]=true" onmouseout="$.fn.seccode.hideDelayed(\''.$id.'\')">';
		$ani = $_G['setting']['seccodedata']['animator'] ? '_ani' : '';
		$id = $_REQUEST['id'];
		switch($_G['setting']['seccodedata']['type']){
			case 4:
				$_G['setting']['seccodedata']['width'] = 32;
				$_G['setting']['seccodedata']['height'] = 24;
			case 0:
			case 1:
				$html .= lang('core', 'seccode_image'.$ani.'_tips');
				$html .= '<a href="javascript:;">';
				$html .= '<img src="'.$_REQUEST['imgurl'].'&'.TIMESTAMP.'"';
				$html .= ' width="'.$_G['setting']['seccodedata']['width'].'"';
				$html .= ' height="'.$_G['setting']['seccodedata']['height'].'"';
				$html .= ' onclick="$(\'#'.$id.'\').seccodeHTML(1)"';
				//$html .= ' onblur="!$(\'#'.$id.'\').is(\':focus\')&&$(\'#'+$id+'\').poshytip(\'hide\')"';
				$html .= ' /></a>';
				break;
			case 2:
				$html .= extension_loaded('ming') ?
						lang('core', 'seccode_image'.$ani.'_tips')."<script type='text/javascript'>AC_FL_RunContent('width', '".$_G['setting']['seccodedata']['width']."', 'height', '".$_G['setting']['seccodedata']['height']."', 'src', '".$_G['siteurl'].$_REQUEST['imgurl']."','quality', 'high', 'wmode', 'transparent', 'bgcolor', '#ffffff','align', 'middle', 'menu', 'false', 'allowScriptAccess', 'never');</script>" :
						lang('core', 'seccode_image'.$ani.'_tips')."<script type='text/javascript'>AC_FL_RunContent('width', '".$_G['setting']['seccodedata']['width']."', 'height', '".$_G['setting']['seccodedata']['height']."', 'src', '".$_G['siteurl']."static/seccode/flash/flash2.swf', 'FlashVars', 'sFile=".rawurlencode("{$_G['siteurl']}{$_REQUEST['imgurl']}")."', 'menu', 'false', 'allowScriptAccess', 'never', 'swLiveConnect', 'true', 'wmode', 'transparent');</script>";
				break;
			case 3:
				$html .= lang('core', 'seccode_sound_tips')."<script type='text/javascript'>AC_FL_RunContent('id', 'seccodeplayer_$id', 'name', 'seccodeplayer_$id', 'width', '0', 'height', '0', 'src', '".$_G['siteurl']."static/seccode/flash/flash1.swf', 'FlashVars', 'sFile=".rawurlencode("{$_G['siteurl']}{$_REQUEST['imgurl']}")."', 'menu', 'false', 'allowScriptAccess', 'never', 'swLiveConnect', 'true', 'wmode', 'transparent');</script>";
				break;
		}
		$html .= '</div>';
		exit($html);
		break;
	default:
		//list($seccode, $seccodeauth) = make_seccode(isset($_GET['idhash']) ? $_GET['idhash'] : null, isset($_GET['key']) ? $_GET['key'] : null);
		$seccode = empty($_GET['tag']) ? make_seccode() : make_seccode($_GET['tag']);

		if(!$_G['setting']['nocacheheaders']) {
			@header("Expires: -1");
			@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			@header("Pragma: no-cache");
		}

		require_once libfile('class/seccode');

		$code = new seccode();
		$code->code = $seccode;
		$code->type = $_G['setting']['seccodedata']['type'];
		$code->width = empty($_GET['width']) ? $_G['setting']['seccodedata']['width'] : intval($_GET['width']);
		$code->height = empty($_GET['height']) ? $_G['setting']['seccodedata']['height'] : intval($_GET['height']);
		$code->background = isset($_GET['background']) ? (boolean)$_GET['background'] : $_G['setting']['seccodedata']['background'];
		$code->adulterate = $_G['setting']['seccodedata']['adulterate'];
		$code->ttf = $_G['setting']['seccodedata']['ttf'];
		$code->angle = $_G['setting']['seccodedata']['angle'];
		$code->warping = $_G['setting']['seccodedata']['warping'];
		$code->scatter = $_G['setting']['seccodedata']['scatter'];
		$code->color = $_G['setting']['seccodedata']['color'];
		$code->size = $_G['setting']['seccodedata']['size'];
		$code->shadow = $_G['setting']['seccodedata']['shadow'];
		$code->animator = $_G['setting']['seccodedata']['animator'];
		$code->seccodelength = $_G['setting']['seccodedata']['length'];
		//$code->force = true;
		$code->fontpath = APP_FRAMEWORK_ROOT.'/source/static/seccode/font/';
		$code->datapath = APP_FRAMEWORK_ROOT.'/source/static/seccode/';
		$code->includepath = APP_FRAMEWORK_ROOT.'/source/class/';

		$code->display();
}
