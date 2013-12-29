<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class ApiAction {
	public $default_method = 'def';
	public $allowed_method = array('def', 'profile');

	public function __construct(){
		global $_G;

		if(!$_G['setting']['nocacheheaders']) {
			@header("Expires: -1");
			@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			@header("Pragma: no-cache");
		}
	}

	/**
	 * 空操作
	 */
	public function def(){
		exit;
	}

	public function profile(){
		global $_G, $template, $cache;

		$type = $_REQUEST['type'];
		$hash = crc32($type.$_REQUEST['grade'].$_REQUEST['academy'].$_REQUEST['specialty'].$_REQUEST['league'].$_REQUEST['organization']);

		$data = $cache->get('mhs_profile_'.$hash);
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
				//default: $data=DB::fetch_first('SELECT `name` FROM %t WHERE `id`=%d', array('profile_academies', 2));
			}
			if($data){
				$data = json_encode($data);
				$cache->set('mhs_profile_'.$hash, $data, 604800);
			}
		}

		exit($data ? (isset($_REQUEST['callback']) ? $_REQUEST['callback'].'('.$data.')' : $data) : '');
	}
}
