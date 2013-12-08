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
	private function exportGrades(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_grades'));
		$result = array();
		foreach($data as $grade){
			$result[$grade] = $grade;
		}
		return $result;
	}

	/**
	 * 导出学院列表
	 */
	private function exportAcademies(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_academies'));
	}

	/**
	 * 导出专业列表
	 */
	private function exportSpecialties(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_specialties'));
	}

	/**
	 * 导出班级列表
	 */
	private function exportClasses(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_classes'));
	}

	/**
	 * 导出社团列表
	 */
	private function exportLeagues(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_leagues'));
	}

	/**
	 * 导出组织列表
	 */
	private function exportOrganizations(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_organizations'));
	}

	/**
	 * 导出部门列表
	 */
	private function exportDepartments(){
		$data = DB::fetch_all('SELECT * FROM '.DB::table('profile_departments'));
	}
}