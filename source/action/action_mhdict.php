<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class MhdictAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		require libfile('function/nav');
	}

	public function index(){
		show_developing('mhdict');
	}

}
