<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class ApiAction extends Action {
	public $default_method = 'index';
	public $allowed_method = array('index', 'profile', 'tos', 'report', 'manhour', 'activity');

	public function __construct(){
		global $_G;

		//@header('Access-Control-Allow-Origin: null');
	}

	/**
	 * 空操作
	 */
	public function index(){
		exit;
	}

	/**
	 * 空操作
	 */
	public function _empty(){
		exit;
	}

	public function profile(){
		//global $_G;

		$this->_nocacheheaders(false);

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

	public function getnamebyid(){
		$this->_nocacheheaders(false);

		$data = array('name'=>'');
		$tbls = array(
			'grade'			=> 'profile_grades',
			'academy'		=> 'profile_academies',
			'specialty'		=> 'profile_specialties',
			'class'			=> 'profile_classes',
			'league'		=> 'profile_leagues',
			'organization'	=> 'profile_organizations',
			'department'	=> 'profile_departments'
		);

		if(isset($_REQUEST['id']) && $_REQUEST['id']=='0') {
			$data['name'] = '';
		}elseif(array_key_exists($_REQUEST['type'], $tbls)){
			$sql = 'SELECT `%i` FROM %t WHERE `id`';
			$label = in_array($_REQUEST['type'], array('league', 'organization', 'department'));
			if($label){
				$sql .= ' IN (%n)';
				$_REQUEST['id'] = explode(',', $_REQUEST['id']);
				foreach($_REQUEST['id'] as &$id)
					$id = intval(trim($id));
			}else{
				$sql .= '=%d LIMIT 1';
			}
			$arr = array($_REQUEST['type']=='grade' ? 'grade' : 'name', $tbls[$_REQUEST['type']], $_REQUEST['id']);
			$data['name'] = $label ? DB::fetch_all($sql, $arr) : DB::result_first($sql, $arr);
		}

		ajaxReturn($data, 'AUTO');
		exit;
	}

	public function report(){
		$data = $_POST['data'];
		exit('感谢你反馈信息，我们会尽快修复问题的<br/><br/>你提交的内容是：<br/>'.$data);
	}

	public function getann(){
		$id = $_REQUEST['id'];
		$ann = DB::fetch_first('SELECT `author`,`subject`,`type`,`starttime`,`endtime`,`message`,`academy` FROM %t WHERE `id`=%d LIMIT 1', array('announcement', $id));
		if(!empty($ann)) {
			if($ann['starttime'] != 0) {
				$ann['starttime'] = dgmdate($ann['starttime']);
			} else {
				unset($ann['starttime']);
			}

			if($ann['endtime'] != 0) {
				$ann['endtime'] = dgmdate($ann['endtime']);
			} else {
				unset($ann['endtime']);
			}
		}
		ajaxReturn($ann, 'JSON');
	}

	/**
	 * 获取网站服务条款
	 */
	public function tos(){
		global $_G;

		$this->_nocacheheaders(false);

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
		$result = DB::fetch_first('SELECT `username`,`manhour`,`rank` FROM %t WHERE `uid`=%d LIMIT 1', array('users', $uid));

		ajaxReturn($result, 'AUTO');
	}

	public function monthdata() {
		global $_G;

		if(!$_G['uid']) {
			ajaxReturn(array(array('非法请求',0)), 'JSON');
		}

		$date = getdate();

		$month = array('一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月');
		for($i=0; $i<$date['mon']; $i++) {
			array_push($month, array_shift($month));
		}

		$ajax = array();
		$mon = $date['mon'] + 1;
		$year = $date['year'] - 1;
		// mktime(hour, minute, second, month, day, year)
		$endtime = mktime(0, 0, 0, $mon, 1, $year);
		foreach($month as $k=>$v) {
			$mon++;
			if($mon > 12) {
				$mon = 1;
				$year++;
			}
			$starttime = $endtime;
			$endtime = mktime(0, 0, 0, $mon, 1, $year);
			$sum = DB::result_first('SELECT sum(`manhour`) FROM %t WHERE `uid`=%d AND `status`=1 AND `time`>=%d AND `time`<%d', array('manhours', $_G['uid'], $starttime, $endtime));
			$ajax[] = array($v, intval($sum));
		}

		ajaxReturn($ajax, 'JSON');
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
		global $_G;

		$this->_nocacheheaders(false);

		$id = intval($_REQUEST['id']);

		if($id > 0){
			$result = DB::fetch_first('SELECT * FROM %t WHERE `id`=%d LIMIT 1', array('activity', $id));
		}else{
			//require_once libfile('function/members');
			//$result = DB::result_all(subusersqlformula(null, '`id`,`name`,`place`,`starttime`,`endtime`,`sponsor`,`undertaker`,`intro`', 'activity').' AND %t.`status` IN (0,3,5)', array('manhours'));
			$result = DB::fetch_all('SELECT `id`,`name`,`place`,`starttime`,`endtime`,`sponsor`,`undertaker`,`intro` FROM %t WHERE `available`=1 AND `academy` IN (0,%d) ORDER BY `id` DESC', array('activity', $_G['member']['academy']));
		}

		ajaxReturn($result, 'AUTO');
	}

	/**
	 * 添加工时
	 */
	public function addmh(){
		global $_G;
		$result = array(
			'errno' => -1,
			'msg' => '未登录或会话超时'
		);

		$uids = $_POST['uid'];
		$activity = $_POST['aid'];
		$manhour = $_POST['manhour'];
		$date = $_POST['time'];
		$remark = htmlspecialchars(remove_xss($_POST['remark']));

		if($_G['uid']){
			$result['errno'] = 1;
			require_once libfile('function/nav');
			require_once libfile('function/members');
			if(!chkPermit('addmh')) {
				$result['msg'] = '无权操作';
			}elseif(empty($uids)){
				$result['msg'] = '用户不能为空';
			}elseif(empty($activity)){
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
					$uids = explode(',', $uids);
					foreach($uids as &$uid) {
						$uid = abs(intval($uid));
					}
					$uids = array_unique($uids);

					if(empty($uids) || DB::result_first(subusersqlformula(DB::table('users').'.`uid` IN ('.implode(',', $uids).')', 'count(*)'))!=count($uids)) {
						$result['msg'] = '用户为空或你无权管理部分用户';
					}else{
						$date = explode('-', $date);
						$date = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
						$user = DB::fetch('SELECT `realname`,`gender`,`studentid`,`academy`,`specialty`,`class` FROM %t WHERE `uid`=%d LIMIT 1', array('users_profile', $uid));
						$zybj = trim($user['specialty'].' '.$user['class']);
						foreach($uids as &$uid) {
							DB::query('INSERT INTO %t (`id`, `uid`, `realname`, `gender`, `studentid`, `academy`, `zybj`, `manhour`, `status`, `aid`, `actname`, `time`, `applytime`, `verifytime`, `operator`, `remark`, `verifytext`) VALUES (NULL, %d, %d, %d, %d, %s, %d, %d, %d, %d, %s, %s)', array(
								'manhours',         // 表
								$uid,               // 用户ID
								$user['realname'],  // 真实名字
								$user['gender'],    // 性别 1男 2女
								$user['studentid'], // 学号
								$user['academy'],   // 学院ID
								$zybj,              // 专业班级
								$manhour,           // 工时数
								1,                  // 状态 0无效，1有效，2审核中，3复查中，4审核失败，5复查失败，其他 错误
								$activity,          // 活动ID
								$act['name'],       // 活动名称
								$date,              // 日期
								TIMESTAMP,          // 申请时间
								TIMESTAMP,          // 审核时间
								$_G['uid'],         // 审核员
								$remark,            // 申请留言
								''                  // 审核留言
							));
						}

						require_once libfile('function/manhour');
						foreach($uids as $uid) update_user_manhour($uid);
						update_rank();

						$result['errno'] = 0;
						$result['msg'] = '添加成功！';
					}
				}
			}
		}

		ajaxReturn($result, 'AUTO');
	}

	/**
	 * 导入工时
	 */
	public function importmh(){
		global $_G;
		$result = array(
			'errno' => -1,
			'msg' => '未登录或会话超时'
		);

		$activity = $_POST['aid'];
		$date = $_POST['time'];

		if($_G['uid']){
			$result['errno'] = 1;
			require_once libfile('function/nav');
			require_once libfile('function/members');
			$actname = empty($activity) ? '' : DB::result_first('SELECT `name` FROM %t WHERE `id`=%d LIMIT 1', array('activity', $activity));

			if(!chkPermit('addmh')) {
				$result['msg'] = '无权操作';
			} elseif(empty($actname)) {
				$result['msg'] = '活动为空或无效';
			} elseif(empty($date)) {
				$result['msg'] = '日期不能为空';
			} elseif(!preg_match("/^20[0-9]{2}-[0-9]{2}-[0-9]{2}$/", $date)) {
				$result['msg'] = '日期格式不正确';
			} elseif(empty($_FILES['mh_excel']) || empty($_FILES['mh_excel']['size']) || $_FILES['mh_excel']['error']!=UPLOAD_ERR_OK) {
				$result['msg'] = '没有文件被上传或上传失败';
			} elseif(!in_array(fileext($_FILES['mh_excel']['name']), array('xls', 'xlsx'))) {
				$result['msg'] = '文件不受支持';
			} else {
				require_once APP_FRAMEWORK_ROOT.'/source/plugin/PHPExcel.php';
				require_once APP_FRAMEWORK_ROOT.'/source/plugin/PHPExcel/IOFactory.php';
				$objPHPExcel = PHPExcel_IOFactory::load($_FILES['mh_excel']['tmp_name']);
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

				$date = explode('-', $date);
				$date = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
				$uids = array();
				$counter = 0;

				foreach($sheetData as $k => $v) {
					if($k < 5 || empty($v['A'])) continue;
					$sno      = &$v['A'];
					$realname = &$v['C'];
					$gender   = $v['D'] == '男' ? 1 : 2;
					$zybj     = &$v['E'];
					$manhour  = intval($v['F']);
					$note     = htmlspecialchars(trim($v['G']));

					$uid = DB::result_first('SELECT `uid` FROM %t WHERE `sno`=%d LIMIT 1', array('users_profile', $sno));
					if(empty($uid)) {
						$uid = 0;
					} else {
						$uid = intval($uid);
						$uids[] = $uid;
					}

					DB::query('INSERT INTO %t (`id`, `uid`, `realname`, `gender`, `studentid`, `zybj`, `academy`, `manhour`, `status`, `aid`, `actname`, `time`, `applytime`, `verifytime`, `operator`, `remark`, `verifytext`) VALUES (NULL, %d, %d, %d, %d, %s, %d, %d, %d, %d, %s, %s)', array(
						'manhours',   // 表
						$uid,         // 用户ID
						$realname,    // 真实名字
						$gender,      // 性别 1男 2女
						$sno,         // 学号
						0,            // 学院ID
						$zybj,        // 专业班级
						$manhour,     // 工时数
						1,            // 状态 0无效，1有效，2审核中，3复查中，4审核失败，5复查失败，其他 错误
						$activity,    // 活动ID
						$actname,     // 活动名称
						$date,        // 日期
						TIMESTAMP,    // 申请时间
						TIMESTAMP,    // 审核时间
						$_G['uid'],   // 审核员
						$note,        // 申请留言
						''            // 审核留言
					));

					$counter++;
				}

				require_once libfile('function/manhour');
				foreach($uids as $uid) update_user_manhour($uid);
				update_rank();

				$result['errno'] = 0;
				$result['msg'] = "已导入 $counter 个工时条目";
			}
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
		$remark = htmlspecialchars(remove_xss($_POST['remark']));

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
		global $_G;

		$result = array(
			'errno' => 1,
			'msg' => '无效请求'
		);

		if(!$_G['uid']){
			$result['msg'] = '未登录或会话超时';
		}elseif(empty($_POST['id'])){
			$result['msg'] = '复查的工时不能为空';
		}elseif(!is_array($_POST['id'])){
			$result['msg'] = '参数无效';
		}elseif(empty($_POST['remark'])){
			$result['msg'] = '复查理由不能为空';
		}else{
			$_POST['remark'] = htmlspecialchars(remove_xss($_POST['remark']));
			DB::query('UPDATE %t SET `status`=3, `applytime`=%d, `remark`=%s WHERE `status` IN (0,1,4,5,6) AND `id` IN (%n)', array('manhours', TIMESTAMP, $_POST['remark'], $_POST['id']));
			$result['errno'] = 0;
			$result['msg'] = '已申请复查'.DB::affected_rows().'个工时条目，请耐心等候审查';

			require_once libfile('function/manhour');
			update_user_manhour($_G['uid']);
			update_rank();
		}
		ajaxReturn($result, 'AUTO');
	}

	/**
	 * 侧边栏
	 */
	public function badge(){
		global $_G;
		require_once libfile('function/members');

		$this->_nocacheheaders();

		$result = array();
		if($_G['uid'] && !empty($_REQUEST['badge']) && is_array($_REQUEST['badge'])){
			foreach($_REQUEST['badge'] as $badge){
				switch($badge){
					case 'profile/pm':
						$result[$badge] = 0;
						break;
					case 'global/info':
						$result[$badge] = 0;
						break;
					case 'members/verifyuser':
						$result[$badge] = DB::fetch_first('SELECT count(`uid`) AS num, max(`submittime`) AS time FROM %t WHERE `status`=0', array('activation'));
						if(!empty($result[$badge]['time'])) $result[$badge]['time'] = dgmdate($result[$badge]['time'], 'u');
						break;
					case 'manhour/applylog':
						$result[$badge] = DB::fetch_first(subusersqlformula(null, 'count(`id`) AS num, max(`applytime`) AS time', 'manhours').' AND %t.`status` IN (2)', array('manhours'));
						if(!empty($result[$badge]['time'])) $result[$badge]['time'] = dgmdate($result[$badge]['time'], 'u');
						break; //2,4
					case 'manhour/checklog':
						$result[$badge] = DB::fetch_first(subusersqlformula(null, 'count(`id`) AS num, max(`applytime`) AS time', 'manhours').' AND %t.`status` IN (3)', array('manhours'));
						if(!empty($result[$badge]['time'])) $result[$badge]['time'] = dgmdate($result[$badge]['time'], 'u');
						break; //0,3,5
				}
			}
		}
		ajaxReturn($result, 'AUTO');
	}

	private function _nocacheheaders($nocache = true){
		global $_G;
		if($nocache && !$_G['setting']['nocacheheaders']) {
			@header('Expires: -1');
			@header('Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0', TRUE);
			@header('Pragma: no-cache');
		}elseif(!$nocache && $_G['setting']['nocacheheaders']){
			@session_cache_limiter('public');
			@header('Expires: '.gmdate("D, d M Y H:i:s", time() + 7*60*60*24).' GMT');
			@header('Cache-Control: public');
			@header('Pragma: cache');
		}
	}
}
