<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class group {

	public static function getgroups($type) {
		return $type=='admin' ? self::admingroups() : self::usergroups();
	}

	public static function group_tree($type) {
		$groups = self::getgroups($type);
		$data = array_shift($groups);
		$data['childen'] = array();
		$gid = &$data['gid'];
		$tmp = array();
		$tmp[$gid] = &$data['children'];
		foreach($groups as $k => $grp) {
			$pid = &$grp['parent'];
			$tmp[$pid][$k] = $grp;
			if(!isset($tmp[$k])) {
				$tmp[$pid][$k]['children'] = array();
				$tmp[$k] = &$tmp[$pid][$k]['children'];
			}
		}
		trace($data);
		return $data;
	}

	public static function addgroup($type, $data) {
		global $_G;
		$tbl = $type=='admin' ? 'admingroup' : 'group';
		$groups = self::getgroups($type);

		if(empty($data['parent']) || !isset($groups[$data['parent']])) {
			throw new Exception('上级用户组非法', 1);
		}

		$parent = &$groups[$data['parent']];
		if(is_array($data['permit'])) {
			foreach($data['permit'] as $v) {
				if(!in_array($v, $parent['permit'])) {
					throw new Exception('新增的用户组越权', 1);
				}
			}
		}

		$data['relation'] = empty($parent['relation']) ? $parent['gid'] : $parent['relation'].','.$parent['gid'];
		DB::query('INSERT INTO %t (`parent`, `relation`, `name`, `note`, `formula`, `permit`) VALUES (%d, %s, %s, %s, %s, %s)', array(
			$tbl,
			$data['parent'],
			$data['relation'],
			$data['name'],
			$data['note'],
			$data['formula'],
			implode(',', $data['permit'])
		));

		Cache::delete('admingroup_'.$_G['member']['adminid']);
	}

	public static function editgroup($type, $gid, $data) {
		global $_G;
		$tbl = $type=='admin' ? 'admingroup' : 'group';
		$groups = self::getgroups($type);

		if(empty($data['parent']) || !isset($groups[$data['parent']])) {
			throw new Exception('权限不足', 1);
		}

		$parent = &$groups[$data['parent']];
		$ismoved = $data['parent'] != $groups[$gid]['parent'];
		if(is_array($data['permit'])) {
			foreach($data['permit'] as $k => $v) {
				if(!in_array($v, $parent['permit'])) {
					if($ismoved) {
						unset($data['permit'][$k]);
					} else {
						throw new Exception('用户组越权', 1);
					}
				}
			}
		}

		$data['relation'] = empty($parent['relation']) ? $parent['gid'] : $parent['relation'].','.$parent['gid'];
		$data['permit'] = implode(',', $data['permit']);
		if(isset($data['gid'])) unset($data['gid']);

		DB::update($tbl, $data, '`gid`='.$gid);
		if($ismoved) {
			DB::query('UPDATE %t SET `relation`=REPLACE(`relation`, %s, %s) WHERE FIND_IN_SET(%d, `relation`)', array(
				$tbl,
				$groups[$gid]['relation'],
				$data['relation'],
				$gid
			));
		}

		Cache::delete('admingroup_'.$_G['member']['adminid']);
	}

	public static function delgroup($type, $gid) {
		global $_G;
		$tbl = $type=='admin' ? 'admingroup' : 'group';
		$groups = self::getgroups($type);

		if(empty($gid) || !isset($groups[$gid])) {
			throw new Exception('用户组不存在或权限不足', 1);
		}
		if($gid == $_G['member']['adminid']) {
			throw new Exception('不能删除自己所在的用户组', 1);
		}

		DB::query('DELETE FROM %t WHERE FIND_IN_SET(%d, `relation`) OR `gid`=%d', array($tbl, $gid, $gid));

		Cache::delete('admingroup_'.$_G['member']['adminid']);
	}

	private static function admingroups() {
		global $_G;

		$gid = $_G['member']['adminid'];
		$cacheid = 'admingroup_'.$gid;
		$agrp = Cache::get($cacheid);

		if($agrp === null || APP_FRAMEWORK_DEBUG) {
			$agrp = DB::fetch_all('SELECT * FROM %t WHERE FIND_IN_SET(%d, `relation`) OR `gid`=%d', array('admingroup', $gid, $gid), 'gid');
			
			foreach($agrp as $k => &$grp) {
				$grp['parentgrp'] = $grp['parent'] ? ($k==$gid ? DB::result_first('SELECT `name` FROM %t WHERE `gid`=%d LIMIT 1', array('admingroup', $grp['parent'])) : $agrp[$grp['parent']]['name']) : '';
			}
			//trace($agrp);

			Cache::set($cacheid, $agrp, 604800);
		}

		return $agrp;
	}

}
