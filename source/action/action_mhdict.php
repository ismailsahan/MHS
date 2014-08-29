<?php

/**
 * 主界面模块
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class MhdictAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index');

	public function __construct(){
		if(!chklogin()) showlogin();
		require libfile('function/nav');
	}

	public function index(){
		show_developing('mhdict');
	}

	public function ann() {
		global $_G, $template;

		has_permit('ann');

		if(IS_AJAX){
			$return = array(
				'errno' => 0,
				'msg' => ''
			);

			if(!empty($_POST['displayorder']) && is_array($_POST['displayorder'])) {
				if(!empty($_POST['id'])) {
					foreach($_POST['id'] as $id) {
						DB::query('DELETE FROM %t WHERE `id`=%d LIMIT 1', array('announcement', $id));
						unset($_POST['displayorder'][$id]);
					}
				}
				foreach($_POST['displayorder'] as $id => $order) {
					DB::query('UPDATE %t SET `displayorder`=%d WHERE `id`=%d LIMIT 1', array('announcement', $order, $id));
				}
				$return['msg'] = '公告列表更新成功';
			} elseif(isset($_POST['id'])) {
				$subject = htmlspecialchars(remove_xss(trim($_POST['subject'])));
				$starttime = $_POST['starttime'];
				$endtime = $_POST['endtime'];
				$type = $_POST['type'] ? 1 : 0;
				$message = htmlspecialchars(remove_xss(trim($_POST['message'])));
				$academy = chkPermit('access_to_global_ann') && $_POST['academy']=='0' ? 0 : $_G['member']['academy'];

				if(empty($subject)) {
					$return['errno'] = 1;
					$return['msg'] = '公告标题不能为空';
				} elseif(empty($message)) {
					$return['errno'] = 1;
					$return['msg'] = '公告内容不能为空';
				} elseif(!empty($starttime) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $starttime)) {
					$return['errno'] = 1;
					$return['msg'] = '起始时间格式不正确';
				} elseif(!empty($endtime) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $endtime)) {
					$return['errno'] = 1;
					$return['msg'] = '终止时间格式不正确';
				} else {
					$starttime = empty($starttime) ? 0 : strtotime($starttime);
					$endtime = empty($endtime) ? 0 : strtotime($endtime);

					if(empty($_POST['id'])) {
						DB::query('INSERT INTO %t (`id`, `author`, `subject`, `type`, `displayorder`, `starttime`, `endtime`, `message`, `academy`) VALUES (NULL, %s, %s, %d, 0, %d, %d, %s, %d)', array(
							'announcement',
							$_G['username'],
							$subject,
							$type,
							$starttime,
							$endtime,
							$message,
							$academy
						));
						$return['msg'] = '公告已成功添加';
					}else{
						DB::query('UPDATE %t SET `subject`=%s, `type`=%d, `starttime`=%d, `endtime`=%d, `message`=%s, `academy`=%d WHERE `id`=%d LIMIT 1', array(
							'announcement',
							$subject,
							$type,
							$starttime,
							$endtime,
							$message,
							$academy,
							$_POST['id']
						));
						$return['msg'] = '公告更新成功';
					}
				}
			}

			if(empty($return['msg'])){
				$return['errno'] = 1;
				$return['msg'] = '非法请求';
			}

			ajaxReturn($return, 'JSON');
		}else{
			$manage_all_ann = chkPermit('manage_all_ann');
			$access_to_global_ann = chkPermit('access_to_global_ann');
			$anns = DB::fetch_all('SELECT * FROM %t WHERE %d OR (`author`=%s AND `academy` IN (%n)) ORDER BY `displayorder` ASC', array('announcement', $manage_all_ann, $_G['username'], $access_to_global_ann ? array(0, $_G['member']['academy']) : array($_G['member']['academy'])));
			$academies = DB::fetch_all('SELECT * FROM %t', array('profile_academies'), 'id');

			if(!$template->isCached('mhdict_ann')){
				$template->assign('sidebarMenu', defaultNav());
				$template->assign('adminNav', adminNav());
				$template->assign('menuset', array('mhdict', 'ann'));
			}

			$template->assign('anns', $anns, true);
			$template->assign('academies', $academies, true);
			$template->assign('access_to_global_ann', $access_to_global_ann, true);
			$template->display('mhdict_ann');
		}
	}

}
