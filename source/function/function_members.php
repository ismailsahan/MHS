<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

function subusersqlformula($formula = null, $selector = '*', $addtbl = null) {
	global $_G;
	require_once libfile('class/formula');
	$formula = $formula===null && $_G['member']['adminid']>1 ? DB::result_first('SELECT `formula` FROM %t WHERE `gid`=%d LIMIT 1', array('admingroup', $_G['member']['adminid'])) : null;
	return formula::usersql($formula, $selector, $addtbl);
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