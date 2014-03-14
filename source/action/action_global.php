<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class GlobalAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		require libfile('function/nav');
	}

	public function index(){
		redirect(U('global/info'));
	}

	public function info(){
		global $_G, $template;

		if(IS_AJAX){
			$name = &$_POST['name'];
			$value = &$_POST['value'];
			switch($name){
				case 'sitename':
					if(!is_string($value) || empty($value)){
						$this->_ajaxError('站点名称不能为空');
					}
					$this->_update('sitename', $value, 's');
					break;
				case 'copyright':
					if(!is_string($value) || empty($value)){
						$this->_ajaxError('不能为空');
					}
					$this->_update('copyright', $value, 's');
					break;
				case 'onlineservice':
					$this->_update('service', "'{$value}'", 'i');
					break;
				case 'icp':
					$this->_update('icp', $value, 's');
					break;
				case 'statcode':
					$this->_update('statcode', "'{$value}'", 'i');
					break;
				case 'nocacheheaders':
					if(!is_string($value) || !in_array($value, array('1', '0'))){
						$this->_ajaxError('参数错误');
					}
					$this->_update('nocacheheaders', $value, 'd');
					break;
				case 'debug':
					if(!is_string($value) || !in_array($value, array('1', '0'))){
						$this->_ajaxError('参数错误');
					}
					$this->_update('debug', $value, 'd');
					break;
				case 'closed':
					if(!is_string($value) || !in_array($value, array('1', '0'))){
						$this->_ajaxError('参数错误');
					}
					$this->_update('closed', $value, 'd');
					break;
				case 'closereason':
					$this->_update('closereason', $value, 's');
					break;
				default:
					$this->_ajaxError('非法请求');
			}
			send_http_status(200);
			exit;
		}else{
			if(!$template->isCached('global_info')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('global', 'site_info'));
			}
			$template->display('global_info');
		}
	}

	public function access(){
		global $_G, $template;

		if(IS_AJAX){
			;
		}else{
			if(!$template->isCached('global_access')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('global', 'member_access'));
			}
			$template->display('global_access');
		}
	}

	public function seccheck(){
		global $_G, $template;

		if(IS_AJAX){
			;
		}else{
			if(!$template->isCached('global_seccheck')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('global', 'seccheck'));
			}
			$template->display('global_seccheck');
		}
	}

	public function time(){
		global $_G, $template;

		if(IS_AJAX){
			;
		}else{
			if(!$template->isCached('global_time')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('global', 'time'));
			}
			$template->display('global_time');
		}
	}

	private function _update($name, $val, $type){
		global $_G;
		return DB::query("UPDATE %t SET `svalue`=%{$type} WHERE `skey`=%s LIMIT 1", array(
			'setting',
			$val,
			$name
		));
	}

	private function _ajaxError($msg){
		send_http_status(400);
		exit($msg);
	}

}
