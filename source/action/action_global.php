<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

function dateformat($string, $operation = 'formalise') {
	$string = dhtmlspecialchars(trim($string));
	$replace = $operation == 'formalise' ? array(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy')) : array(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'));
	return str_replace($replace[0], $replace[1], $string);
}

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

		has_permit('site_info');

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
				case 'logopath':
					if(!is_string($value) || empty($value)){
						$this->_ajaxError('LOGO文件路径不能为空');
					}
					$this->_update('logopath', $value, 's');
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
					$this->_ajaxError('非法请求'.$name);
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

		has_permit('member_access');

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

		has_permit('seccheck');

		if(IS_AJAX){
			$ajax = array(
				'errno' => 1,
				'msg' => ''
			);

			if(!in_array($_POST['sectype'], array('0', '1', '2', '3', '4'))){
				$ajax['msg'] = '验证码类型不合法';
			}elseif(empty($_POST['seclength']) || intval($_POST['seclength'])<2 || intval($_POST['seclength'])>8){
				$ajax['msg'] = '验证码长度不宜过长或过短';
			}elseif(empty($_POST['secwidth']) || intval($_POST['secwidth'])<100 || intval($_POST['secwidth'])>200){
				$ajax['msg'] = '验证码图片的宽度必须在 100～200 之间';
			}elseif(empty($_POST['secheight']) || intval($_POST['secheight'])<30 || intval($_POST['secheight'])>80){
				$ajax['msg'] = '验证码图片的高度必须在 30～80 之间';
			}elseif(!isset($_POST['secscatter']) || intval($_POST['secscatter'])<0){
				$ajax['msg'] = '验证码图片打散级别不合法';
			}elseif(!isset($_POST['secbackground']) || !in_array($_POST['secbackground'], array('0', '1'))){
				$ajax['msg'] = '随机图片背景不合法';
			}elseif(!isset($_POST['secadulterate']) || !in_array($_POST['secadulterate'], array('0', '1'))){
				$ajax['msg'] = '随机背景图形不合法';
			}elseif(!isset($_POST['secttf']) || !in_array($_POST['secttf'], array('0', '1'))){
				$ajax['msg'] = '随机 TTF 字体不合法';
			}elseif(!isset($_POST['secangle']) || !in_array($_POST['secangle'], array('0', '1'))){
				$ajax['msg'] = '随机倾斜度不合法';
			}elseif(!isset($_POST['secwarping']) || !in_array($_POST['secwarping'], array('0', '1'))){
				$ajax['msg'] = '随机扭曲不合法';
			}elseif(!isset($_POST['seccolor']) || !in_array($_POST['seccolor'], array('0', '1'))){
				$ajax['msg'] = '随机颜色不合法';
			}elseif(!isset($_POST['secsize']) || !in_array($_POST['secsize'], array('0', '1'))){
				$ajax['msg'] = '随机大小不合法';
			}elseif(!isset($_POST['secshadow']) || !in_array($_POST['secshadow'], array('0', '1'))){
				$ajax['msg'] = '文字阴影不合法';
			}elseif(!isset($_POST['secanimator']) || !in_array($_POST['secanimator'], array('0', '1'))){
				$ajax['msg'] = 'GIF 动画不合法';
			}else{
				$_G['setting']['seccodestatus'] = is_array($_POST['secopn']) ? $_POST['secopn'] : array();
				$_G['setting']['seccodedata']['type'] = intval($_POST['sectype']);
				$_G['setting']['seccodedata']['length'] = intval($_POST['seclength']);
				if(in_array($_G['setting']['seccodedata']['type'], array(0,1,2))){
					$_G['setting']['seccodedata']['width'] = intval($_POST['secwidth']);
					$_G['setting']['seccodedata']['height'] = intval($_POST['secheight']);
				}
				if(in_array($_G['setting']['seccodedata']['type'], array(0,1))){
					$_G['setting']['seccodedata']['scatter'] = intval($_POST['secscatter']);
					$_G['setting']['seccodedata']['background'] = intval($_POST['secbackground']);
					$_G['setting']['seccodedata']['adulterate'] = intval($_POST['secadulterate']);
					$_G['setting']['seccodedata']['ttf'] = intval($_POST['secttf']);
					$_G['setting']['seccodedata']['angle'] = intval($_POST['secangle']);
					$_G['setting']['seccodedata']['warping'] = intval($_POST['secwarping']);
					$_G['setting']['seccodedata']['color'] = intval($_POST['seccolor']);
					$_G['setting']['seccodedata']['size'] = intval($_POST['secsize']);
					$_G['setting']['seccodedata']['shadow'] = intval($_POST['secshadow']);
					$_G['setting']['seccodedata']['animator'] = intval($_POST['secanimator']);
				}
				if($_G['setting']['seccodedata']['type'] == 4){
					$_G['setting']['seccodedata']['length'] = 4;
				}

				$this->_update('seccodestatus', serialize($_G['setting']['seccodestatus']), 's');
				$this->_update('seccodedata', serialize($_G['setting']['seccodedata']), 's');

				$ajax['errno'] = 0;
				$ajax['msg'] = '验证码设置更新成功';
			}

			ajaxReturn($ajax, 'JSON');
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

		has_permit('time');

		if(IS_AJAX){
			$timeformat  = $_POST['timeformat'] == '24' ? 'H:i' : 'h:i A';
			$dateformat  = dateformat($_POST['dateformat'], 'format');
			$dateconvert = empty($_POST['dateconvert']) ? 0 : 1;
			$timeoffset  = floatval($_POST['timeoffset']);

			$this->_update('timeformat', $timeformat, 's');
			$this->_update('dateformat', $dateformat, 's');
			$this->_update('dateconvert',$dateconvert,'d');
			$this->_update('timeoffset', $timeoffset, 'f');

			ajaxReturn(array(
				'errno' => 0,
				'msg' => '时区设置已更新成功'
			), 'JSON');
		}else{
			if(!$template->isCached('global_time')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('global', 'time'));
			}
			$template->display('global_time');
		}
	}

	public function chksec(){
		define('DISABLE_TRACE', true);

		require_once libfile('function/seccode');
		$res = check_seccode($_REQUEST['seccode'], '__DEFAULT__', false);
		ajaxReturn(array(
			'errno' => 0,
			'real' => $_SESSION['seccode']['__DEFAULT__']['seccode'],
			'result' => $res
		), 'JSON');
	}

	private function _update($name, $val, $type){
		global $_G;
		DB::query("UPDATE %t SET `svalue`=%{$type} WHERE `skey`=%s LIMIT 1", array(
			'setting',
			$val,
			$name
		));
		clearcache('setting');
	}

	private function _ajaxError($msg){
		send_http_status(400);
		exit($msg);
	}

}
