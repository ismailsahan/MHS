<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class group {

	private static $keysarr = array(0 => '');

	public static function getgroups($type) {
		return $type=='admin' ? self::admingroups() : self::usergroups();
	}

	private static function admingroups() {
		global $_G;

		$gid = $_G['member']['adminid'];
		$cacheid = 'admingroup_'.$gid;
		$agrp = Cache::get($cacheid);

		if($agrp === null || APP_FRAMEWORK_DEBUG) {
			$agrp = array();
			$data = array();

			$query = DB::query('SELECT * FROM %t', array('admingroup'));
			while($row = DB::fetch($query)) {
				if(!isset($data[$row['inherit']])) {
					$data[$row['inherit']] = array();
				}

				if($row['gid'] == $gid) {
					$cgrp = $row;
				}

				$data[$row['inherit']][] = $row;
				self::$keysarr[$row['gid']] = $row['name'];
			}
			DB::free_result($query);

			$agrp = &self::get_all_children_grps($gid, $data);
			if(isset($cgrp)) {
				$cgrp['parent'] = self::$keysarr[$cgrp['inherit']];
				array_unshift($agrp, $cgrp);
			}
			//trace($agrp);

			Cache::set($cacheid, $agrp, 604800);
		}

		return $agrp;
	}

	private static function &get_all_children_grps($gid, &$rawdata) {
		$agrp = array();

		if(isset($rawdata[$gid])) {
			$agrp = $rawdata[$gid];
			unset($rawdata[$gid]);
		}

		foreach($agrp as $k => $grp) {
			$agrp[$k]['parent'] = self::$keysarr[$grp['inherit']];
			$tmp = &self::get_all_children_grps($grp['gid'], $rawdata);
			$agrp = array_merge($agrp, $tmp);
		}

		return $agrp;
	}

}
