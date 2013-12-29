<?php

class Profile {
	public static function build(){
		return array(
			'gets' => $this->gets,
			'sets' => $this->sets
		);
	}

	/**
	 * 导出年级（入学年份）信息
	 */
	public static function exportGrades(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_grades'));
		$result = array();
		foreach($data as $grade){
			$result[$grade['id']] = $grade['grade'];
		}
		return $result;
	}

	/**
	 * 导出学院列表
	 */
	public static function exportAcademies(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_academies'));
		$return = array();
		foreach($data as $academy){
			$return[$academy['id']] = $academy['name'];
		}
		return $return;
	}

	/**
	 * 导出专业列表
	 */
	public static function exportSpecialties($grade, $academy){
		if(empty($academy) || !self::chkGrades($grade)) return array();
		$data = DB::fetch_all('SELECT `id`,`name` FROM %t WHERE `aid`=%d AND `g%d`=1', array('profile_specialties', $academy, $grade));
		$return = array();
		foreach($data as $specialty){
			$return[$specialty['id']] = $specialty['name'];
		}
		return $return;
	}

	/**
	 * 导出班级列表
	 */
	public static function exportClasses($grade, $specialty){
		if(empty($specialty) || !self::chkGrades($grade)) return array();
		$data = DB::fetch_all('SELECT `id`,`name` FROM %t WHERE `sid`=%d AND `gid`=%d', array('profile_classes', $specialty, $grade));
		$return = array();
		foreach($data as $classes){
			$return[$classes['id']] = $classes['name'];
		}
		return $return;
	}

	/**
	 * 导出社团列表
	 */
	public static function exportLeagues($academy){
		$data = DB::fetch_all('SELECT `id`,`aid`,`name` FROM %t WHERE `aid`=0 OR `aid`=%d', array('profile_leagues', $academy));
		$return = array();
		$return[$group0] = array();
		$group0 = '直属学校的社团和组织';
		if($academy > 0){
			$group1 = DB::fetch_first('SELECT `name` FROM %t WHERE `id`=%d', array('profile_academies', $academy));
			$group1 = $group1['name'];
			$return[$group1] = array();
		}
		foreach($data as $league){
			$return[$league['aid']==0 ? $group0 : $group1][$league['id']] = $league['name'];
		}
		return $return;
	}

	/**
	 * 导出组织列表
	 */
	public static function exportOrganizations($academy){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_organizations'));
	}

	/**
	 * 导出部门列表
	 */
	public static function exportDepartments($league){
		if(!$league) return array();
		$league = explode(',', $league);
		$groups = array();
		foreach($league as $k => $v){
			$v = intval($v);
			$tmp = DB::fetch_first('SELECT `name`,`aid` FROM %t WHERE `id`=%d', array('profile_leagues', $v));
			if(isset($tmp['name'])){
				$groups[$v] = ($tmp['aid']==0 ? '(校)' : '(院)').$tmp['name'];
			}
		}
		if(empty($groups)) return array();
		$league = array_keys($groups);
		$league = implode(',', $league);
		$data = DB::fetch_all('SELECT `id`,`lid`,`name` FROM '.DB::table('profile_departments').' WHERE `lid` IN ('.$league.')');
		foreach($data as $department){
			$return[$groups[$department['lid']]][$department['id']] = $department['name'];
		}
		return $return;
	}

	private function chkGrades($gid){
		static $grades = array();
		if(empty($grades)){
			$gids = DB::fetch_all('SELECT `id` FROM '.DB::table('profile_grades'));
			foreach($gids as $_gid){
				$grades[] = $_gid['id'];
			}
		}
		return in_array($gid, $grades);
	}
}