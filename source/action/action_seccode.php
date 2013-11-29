<?php

/**
 * 验证码模块
 * 
 * 版本 v0.1.0
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
