<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class MainAction {
	public $default_method = 'main';

	public function main(){
		global $_G, $template;
		$template->display('main');
	}

	public function forgotpwd(){
		;
	}

	public function reset(){
		;
	}

	public function activate(){
		global $_G, $template;
		if(submitcheck('Activate')){

		}
		setToken('Activate');
		$template->display('activate');
	}

	public function expiry(){
		;
	}
}
