<?php

function update_user_manhour($uid) {
	global $_G;

	if(is_array($uid)) {
		foreach($uid as $u) {
			update_user_manhour($u);
		}
		return true;
	} elseif (!$uid) {
		return false;
	}

	$total_manhour = DB::result_first('SELECT sum(`manhour`) FROM %t WHERE `uid`=%d AND `status`=1', array('manhours', $uid));
	$current_manhour = DB::result_first('SELECT `manhour` FROM %t WHERE `uid`=%d LIMIT 1', array('users', $uid));

	if($total_manhour != $current_manhour) {
		DB::query('UPDATE %t SET `manhour`=%d WHERE `uid`=%d LIMIT 1', array('users', $total_manhour, $uid));
		if($_G['uid'] == $uid) $_SESSION['user']['manhour'] = $total_manhour;
	}

	return true;
}

function update_rank() {
	global $_G;
	$data = DB::fetch_all('SELECT `uid`,`manhour` FROM %t', array('users'), 'uid');
	foreach($data as &$user) {
		$user = $user['manhour'];
	}
	arsort($data, SORT_NUMERIC);
	$count = array_count_values($data);
	$uids = array_keys($data);

	$data = array();
	$rank = 1;
	$offset = 0;
	foreach($count as $tmp) {
		$data[$rank] = array_slice($uids, $offset, $tmp);
		$offset += $tmp;
		$rank++;
	}

	foreach($data as $rank => $user) {
		if(in_array($_G['uid'], $user)) $_SESSION['user']['rank'] = $rank;
		DB::query('UPDATE %t SET `rank`=%d WHERE `uid` IN (%n)', array('users', $rank, $user));
	}

	return true;
}