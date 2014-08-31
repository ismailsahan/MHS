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

		$data['formula'] = self::combineformula($data['formula'], $parent['formula']);

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
		} elseif ($gid == $data['parent']) {
			throw new Exception('直属上级不能是自身管理组', 1);
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
		$data['formula'] = self::combineformula($data['formula'], $parent['formula']);
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
		DB::query('UPDATE %t SET `formula`=REPLACE(`formula`, %s, %s) WHERE FIND_IN_SET(%d, `relation`)', array(
			$tbl,
			$groups[$gid]['formula'],
			$data['formula'],
			$gid
		));

		Cache::delete('admingroup_'.$_G['member']['adminid']);
	}

	public static function delgroup($type, $gid) {
		global $_G;
		static $groups = null;
		$tbl = $type=='admin' ? 'admingroup' : 'group';

		if($groups === null) $groups = self::getgroups($type);

		if(empty($gid) || !isset($groups[$gid])) {
			throw new Exception('用户组不存在或权限不足', 1);
		}
		if($gid == $_G['member']['adminid']) {
			throw new Exception('不能删除自己所在的用户组', 1);
		}

		$gids = array();
		$_gids = DB::fetch_all('SELECT `gid` FROM %t WHERE FIND_IN_SET(%d, `relation`) OR `gid`=%d', array($tbl, $gid, $gid));
		foreach($_gids as $g) $gids[] = $g['gid'];
		$gids = array_unique($gids);

		DB::query('DELETE FROM %t WHERE `gid` IN (%n)', array($tbl, $gids));
		DB::query('UPDATE %t SET `adminid`=0 WHERE `adminid` IN (%n)', array('users', $gids));

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
				if($k == $gid) {
					$parentgrp = DB::fetch_first('SELECT `name`,`formula` FROM %t WHERE `gid`=%d LIMIT 1', array('admingroup', $grp['parent']));
				}
				$grp['parentgrp'] = $grp['parent'] ? ($k==$gid ? $parentgrp['name'] : $agrp[$grp['parent']]['name']) : '';
				$grp['formula'] = $grp['formula'] ? ($k==$gid ? self::stripformula($grp['formula'], $parentgrp['formula']) : self::stripformula($grp['formula'], $agrp[$grp['parent']]['formula'])) : '';
				$grp['permit'] = $grp['permit'] ? explode(',', $grp['permit']) : array();
			}
			if(isset($agrp[1])) $agrp[1]['permit'] = getpermitlist();
			//trace($agrp);

			Cache::set($cacheid, $agrp, 604800);
		}

		return $agrp;
	}

	public static function combineformula($formula, $parentformula) {
		return $formula=='' ? $parentformula : ($parentformula=='' ? $formula : "{$parentformula} AND ({$formula})");
	}

	private static function stripformula($formula, $parentformula) {
		return $formula==$parentformula ? '' : ($parentformula=='' ? $formula : substr(substr($formula, 0, strlen($formula)-1), strlen($parentformula)+5));
	}

}
