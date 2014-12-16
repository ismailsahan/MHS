<?php

function subusersqlformula($addcond = '', $selector = '*', $addtbl = null, $formula = null) {
	global $_G;
	if(!class_exists('formula')) {
		require_once libfile('class/formula');
	}
	$formula = $formula===null && $_G['member']['adminid']>1 ? DB::result_first('SELECT `formula` FROM %t WHERE `gid`=%d LIMIT 1', array('admingroup', $_G['member']['adminid'])) : $formula;
	return formula::usersql($formula, $selector, $addtbl, $addcond);
}

function getuserformula() {
	global $_G;
	$formula = $_G['member']['adminid']>1 ? DB::result_first('SELECT `formula` FROM %t WHERE `gid`=%d LIMIT 1', array('admingroup', $_G['member']['adminid'])) : '';
	return $formula;
}

function getAcademyAccess() {
	global $_G;
	if(!$_G['member']['adminid']) return 0;
	$formula = getuserformula();
	preg_match('/academy\s*=\s*(\d+)/', $formula, $matches);
	return empty($matches[1]) ? -1 : $matches[1];
}

function checkformulasyntax($formula, $operators, $tokens) {
	$var = implode('|', $tokens);
	$operator = implode('', $operators);

	$operator = str_replace(
		array('+', '-', '*', '/', '(', ')', '{', '}', '\''),
		array('\+', '\-', '\*', '\/', '\(', '\)', '\{', '\}', '\\\''),
		$operator
	);

	if(!empty($formula)) {
		if(!preg_match("/^([$operator\.\d\(\)]|(($var)([$operator\(\)]|$)+))+$/", $formula) || !is_null(eval(preg_replace("/($var)/", "\$\\1", $formula).';'))){
			return false;
		}
	}
	return true;
}

function checkformulacredits($formula) {
	return checkformulasyntax(
		$formula,
		array('+', '-', '*', '/', ' '),
		array('extcredits[1-8]', 'digestposts', 'posts', 'threads', 'oltime', 'friends', 'doings', 'polls', 'blogs', 'albums', 'sharings')
	);
}

function getusernamebyuid($uid) {
	static $users = array();
	if(!isset($users[$uid])) {
		$users[$uid] = DB::result_first('SELECT `username` FROM %t WHERE `uid`=%d LIMIT 1', array('users', $uid));
	}
	return $users[$uid];
}
