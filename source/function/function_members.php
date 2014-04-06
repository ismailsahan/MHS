<?php

function subusersqlformula($formula = null, $selector = '*', $addtbl = null) {
	global $_G;

	//$sql = 'SELECT * FROM '.DB::table('users_profile').' WHERE ';
	$sql = 'SELECT '.$selector.' FROM '.($addtbl===null ? '' : DB::table($addtbl).',').DB::table('users').','.DB::table('users_profile').' WHERE '.DB::table('users').'.`uid`='.DB::table('users_profile').'.`uid`'.($addtbl===null ? '' : ' AND '.DB::table('users').'.`uid`='.DB::table($addtbl).'.`uid`').' AND ';

	//SELECT * 
	//FROM `conn_users`, `conn_users_profile` 
	//WHERE `conn_users`.`uid` =1 AND `conn_users`.`uid`=`conn_users_profile`.`uid`

	if($_G['member']['adminid'] > 1 || $formula !== null){
		$formula = $formula===null ? 'academy=2' : $formula;

		$formula = str_replace(array('&&', '||'), array('AND', 'OR'), $formula);

		$formula = preg_replace_callback('/(uid|league|department) IN\s*\(([^\(\)]+)\)/is', 'subusersqlformula_callback1', $formula);

		$formula = preg_replace_callback('/(league|department)\s*=\s*(\d+)/is', 'subusersqlformula_callback2', $formula);

		$formula = preg_replace_callback('/(uid|status|adminid|groupid|manhour)/is', 'subusersqlformula_callback3', $formula);

		$formula = preg_replace_callback('/(gender|grade|academy|specialty|class|league|department)/is', 'subusersqlformula_callback4', $formula);

		$sql .= '('.$formula.')';

		//FIND_IN_SET(%d, `department`)
	}else{
		$sql .= $_G['member']['adminid']==1 ? '1' : '0';
	}

	//trace($sql);

	return $sql;
}

function subusersqlformula_callback1($matches) {
	$prop = $matches[1];
	$id = explode(',', $matches[2]);
	foreach($id as &$val)
		$val = $prop.'='.intval($val);
	return '('.implode(' OR ', $id).')';
}

function subusersqlformula_callback2($matches) {
	$prop = $matches[1];
	$id = intval($matches[2]);
	//return "FIND_IN_SET('{$id}'," . DB::table('users_profile') . ".`{$prop}`)";
	return "FIND_IN_SET('{$id}', {$prop})";
}

function subusersqlformula_callback3($matches) {
	return DB::table('users').".`{$matches[1]}`";
}

function subusersqlformula_callback4($matches) {
	return DB::table('users_profile').".`{$matches[1]}`";
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