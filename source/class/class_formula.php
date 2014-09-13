<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class formula {

	//public static $selector = '*';

	public static function usersql($formula = null, $selector = '*', $addtbl = null, $addcond = '') {
		global $_G;

		//$sql = 'SELECT * FROM '.DB::table('users_profile').' WHERE ';
		$sql = 'SELECT '.$selector.' FROM '.DB::table('users').','.DB::table('users_profile').($addtbl===null ? '' : ','.DB::table($addtbl)).' WHERE '.DB::table('users').'.`uid`='.DB::table('users_profile').'.`uid`'.($addtbl===null ? '' : ' AND '.DB::table('users').'.`uid`='.DB::table($addtbl).'.`uid`').($addcond ? " AND ({$addcond})" : '').' AND ';

		//SELECT * 
		//FROM `conn_users`, `conn_users_profile` 
		//WHERE `conn_users`.`uid` =1 AND `conn_users`.`uid`=`conn_users_profile`.`uid`

		if($_G['member']['adminid'] > 1 && !empty($formula)){
			$formula = self::parse_formula($formula);
			$sql .= $formula;

			//FIND_IN_SET(%d, `department`)
		}else{
			$sql .= ($_G['member']['adminid']==1 || $_G['member']['adminid']>1 && empty($formula)) ? '1' : '0';
		}

		//trace($sql);

		return $sql;
	}

	private static function parse_formula($formula) {
		$formula = $formula===null ? '1' : $formula;
		$formula = str_replace(array('&&', '||'), array('AND', 'OR'), $formula);
		$formula = preg_replace_callback('/(uid|league|department) IN\s*\(([^\(\)]+)\)/is', array('formula', 'handle_in_func'), $formula);
		$formula = preg_replace_callback('/(league|department)\s*=\s*([^\s]+)(\s+)/is', array('formula', 'handle_league_department'), $formula);
		$formula = preg_replace_callback('/(\W{0,1})(uid|status|adminid|groupid|manhour)(\W{1})/is', array('formula', 'handle_user_prototype'), $formula);
		$formula = preg_replace_callback('/(\W{0,1})(gender|grade|academy|specialty|class|league|department)(\W{1})/is', array('formula', 'handle_user_profile'), $formula);
		return '('.$formula.')';
	}

	private static function handle_in_func($matches) {
		$prop = $matches[1];
		$id = explode(',', $matches[2]);
		foreach($id as &$val)
			$val = $prop.'='.intval($val);
		return '('.implode(' OR ', $id).')';
	}

	private static function handle_league_department($matches) {
		$prop = $matches[1];
		$name = DB::quote(str_replace("'", '', $matches[2]));
		$separator = $matches[3];
		//return "FIND_IN_SET('{$name}'," . DB::table('users_profile') . ".`{$prop}`)";
		return "FIND_IN_SET('{$name}', {$prop}){$separator}";
	}

	private static function handle_user_prototype($matches) {
		return in_array($matches[1], array('.', '`'))||in_array($matches[3], array('.', '`')) ? $matches[1].$matches[2].$matches[3] : DB::table('users').".`{$matches[2]}`{$matches[3]}";
	}

	private static function handle_user_profile($matches) {
		return in_array($matches[1], array('.', '`'))||in_array($matches[3], array('.', '`')) ? $matches[1].$matches[2].$matches[3] : DB::table('users_profile').".`{$matches[2]}`{$matches[3]}";
	}

}
