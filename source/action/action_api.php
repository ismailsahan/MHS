<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class ApiAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index', 'profile', 'tos', 'report', 'manhour', 'activity');

	public function __construct(){
		global $_G;

		if(!$_G['setting']['nocacheheaders']) {
			@header('Expires: -1');
			@header('Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0', FALSE);
			@header('Pragma: no-cache');
		}

		//@header('Access-Control-Allow-Origin: null');
	}

	/**
	 * 空操作
	 */
	public function index(){
		exit;
	}

	public function _empty(){
		exit;
	}

	public function profile(){
		//global $_G;

		$type = $_REQUEST['type'];
		$hash = md5($type.$_REQUEST['grade'].$_REQUEST['academy'].$_REQUEST['specialty'].$_REQUEST['league'].$_REQUEST['organization']);

		$data = Cache::get('profile_'.$hash);
		if($data === null || APP_FRAMEWORK_DEBUG){
			include libfile('class/profile');
			switch($type){
				case 'grade':        $data = Profile::exportGrades(); break;
				case 'academy':      $data = Profile::exportAcademies(); break;
				case 'specialty':    $data = Profile::exportSpecialties($_REQUEST['grade'], $_REQUEST['academy']); break;
				case 'class':        $data = Profile::exportClasses($_REQUEST['grade'], $_REQUEST['specialty']); break;
				case 'league':       $data = Profile::exportLeagues($_REQUEST['academy']); break;
				case 'organization': $data = Profile::exportOrganizations($_REQUEST['academy']); break;
				case 'department':   $data = Profile::exportDepartments($_REQUEST['league']); break;
				default: $data = array();
				//default: $data=DB::fetch_first('SELECT `name` FROM %t WHERE `id`=%d', array('profile_academies', 2));
			}
			if($_REQUEST['format']){
				$result = array();
				foreach($data as $k => &$v){
					if($k === '') continue;
					if(is_array($v)){
						$t = array();
						foreach($v as $_k => &$_v){
							$t[] = array(
								'text' => $_v,
								'value' => $_k
							);
						}
						$tmp = array(
							'text' => $k,
							'children' => $t
						);
					}else{
						$tmp = array(
							'text' => $v,
							'value' => $k
						);
					}
					$result[] = $tmp;
				}
				$data = &$result;
			}
			if(!is_array($data)) $data = array();
			//if($data){
				$data = json_encode($data);
				Cache::set('profile_'.$hash, $data, 604800);
			//}
		}

		ajaxReturn($data, 'AUTO', true);
		exit;
	}

	public function report(){
		$data = $_POST['data'];
		exit('感谢你反馈信息，我们会尽快修复问题的<br/><br/>你提交的内容是：<br/>'.$data);
	}

	/**
	 * 获取网站服务条款
	 */
	public function tos(){
		global $_G;
		$tos = Cache::get('tos');
		if($tos === null || APP_FRAMEWORK_DEBUG) {
			$tos = DB::result_first("SELECT `svalue` FROM %t WHERE `skey`='tos' LIMIT 1", array('setting'));
			Cache::set('tos', $tos, 604800);
		}
		header('Content-Type: text/plain; charset='.$_G['charset']);
		exit($tos);
	}

	/**
	 * 查询工时统计数据
	 */
	public function stat(){
		$uid = intval($_REQUEST['uid']);
		$result = DB::fetch_first('SELECT `manhour` FROM %t WHERE `uid`=%d LIMIT 1');

		ajaxReturn($result, 'AUTO');
	}

	/**
	 * 查询工时记录
	 */
	public function manhour(){
		global $_G;

		$id = intval($_REQUEST['id']);
		$uid = $_G['uid'] ? $_G['uid'] : intval($_REQUEST['uid']);

		if($uid < 1){
			ajaxReturn(array(), 'AUTO');
		}

		if($id > 0){
			$result = DB::fetch_first('SELECT `id`,`manhour`,`status`,`aid`,`actname`,`time`,`applytime`,`verifytime`,`operator`,`remark`,`verifytext` FROM %t WHERE `uid`=%d AND `id`=%d LIMIT 1', array('manhours', $uid, $id));
		}else{
			$result = DB::fetch_all('SELECT `id`,`manhour`,`status`,`aid`,`actname`,`time` FROM %t WHERE `uid`=%d ORDER BY `id` DESC', array('manhours', $uid));
		}

		ajaxReturn($result, 'AUTO');
	}

	/**
	 * 查询活动信息
	 */
	public function activity(){
		$id = intval($_REQUEST['id']);
		if($id > 0){
			$result = DB::fetch_first('SELECT * FROM %t WHERE `id`=%d LIMIT 1', array('activity', $id));
		}else{
			$result = DB::fetch_all('SELECT * FROM %t WHERE `available`=1 ORDER BY `id` DESC', array('activity'));
		}

		ajaxReturn($result, 'AUTO');
	}

	/**
	 * 申报工时
	 */
	public function applymh(){
		global $_G;
		$result = array(
			'errno' => -1,
			'msg' => '未登录或会话超时'
		);

		$activity = $_POST['aid'];
		$manhour = $_POST['manhour'];
		$date = $_POST['time'];
		$remark = $_POST['remark'];

		if($_G['uid']){
			$result['errno'] = 1;
			if(empty($activity)){
				$result['msg'] = '活动不能为空';
			}elseif(empty($manhour)){
				$result['msg'] = '工时数不能为空';
			}elseif(empty($date)){
				$result['msg'] = '日期不能为空';
			}elseif(abs(intval($manhour)) != $manhour){
				$result['msg'] = '工时数只能为正整数';
			}elseif(!preg_match("/^20[0-9]{2}-[0-9]{2}-[0-9]{2}$/", $date)){
				$result['msg'] = '日期格式不正确';
			}else{
				$act = DB::fetch_first('SELECT `name` FROM %t WHERE `id`=%d LIMIT 1', array('activity', $activity));
				if(empty($act['name'])){
					$result['msg'] = '活动无效';
				}else{
					$date = explode('-', $date);
					$date = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
					DB::query('INSERT INTO %t (`id`, `uid`, `manhour`, `status`, `aid`, `actname`, `time`, `applytime`, `verifytime`, `operator`, `remark`, `verifytext`) VALUES (NULL, %d, %d, %d, %d, %s, %d, %d, %d, %d, %s, %s)', array(
						'manhours',		// 表
						$_G['uid'],		// 用户ID
						$manhour,		// 工时数
						2,				// 状态 0无效，1有效，2审核中，3复查中，4审核失败，5复查失败，其他 错误
						$activity,		// 活动ID
						$act['name'],	// 活动名称
						$date,			// 日期
						TIMESTAMP,		// 申请时间
						0,				// 审核时间
						0,				// 审核员
						$remark,		// 申请留言
						''				// 审核留言
					));
					$result['errno'] = 0;
					$result['msg'] = '申请成功！请耐心等待审核。您需要刷新才能看到您刚才申请的工时信息';
					$result['id'] = DB::insert_id();
					$result['aid'] = $activity;
					$result['actname'] = $act['name'];
					$result['time'] = $date;
				}
			}
		}

		ajaxReturn($result, 'AUTO');
	}

	/**
	 * 复查工时
	 */
	public function checkmh(){
		$result = array(
			'errno' => 1,
			'msg' => '无效请求'
		);
		if(empty($_POST['id'])){
			$result['msg'] = '复查的工时不能为空';
		}elseif(!is_array($_POST['id'])){
			$result['msg'] = '参数无效';
		}elseif(empty($_POST['remark'])){
			$result['msg'] = '复查理由不能为空';
		}else{
			DB::query('UPDATE %t SET `status`=3, `applytime`=%d, `remark`=%s WHERE `status` IN (0,1,4,5) AND `id` IN (%n)', array('manhours', TIMESTAMP, $_POST['remark'], $_POST['id']));
			$result['errno'] = 0;
			$result['msg'] = '已申请复查'.DB::affected_rows().'个工时条目，请耐心等候审查';
		}
		ajaxReturn($result, 'AUTO');
	}

	/**
	 * 侧边栏
	 */
	public function badge(){
		global $_G;
		$result = array();
		if($_G['uid'] && !empty($_REQUEST['badge']) && is_array($_REQUEST['badge'])){
			foreach($_REQUEST['badge'] as $badge){
				switch($badge){
					case 'profile/pm'		: $result[$badge] = 0; break;
					case 'global/info'		: $result[$badge] = 0; break;
					case 'members/activate'	: $result[$badge] = 0; break;
					case 'manhour/applylog'	: $result[$badge] = 0; break;
					case 'manhour/checklog'	: $result[$badge] = 0; break;
				}
			}
		}
		ajaxReturn($result, 'AUTO');
	}
}
