<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class formula {

	//public static $selector = '*';

	public static function usersql($formula = null, $selector = '*', $addtbl = null, $addcond = '') {
		global $_G;

		//$sql = 'SELECT * FROM '.DB::table('users_profile').' WHERE ';
		$sql = 'SELECT '.$selector.' FROM '.($addtbl===null ? '' : DB::table($addtbl).',').DB::table('users').','.DB::table('users_profile').' WHERE '.DB::table('users').'.`uid`='.DB::table('users_profile').'.`uid`'.($addtbl===null ? '' : ' AND '.DB::table('users').'.`uid`='.DB::table($addtbl).'.`uid`').($addcond ? " {$addcond}" : '').' AND ';

		//SELECT * 
		//FROM `conn_users`, `conn_users_profile` 
		//WHERE `conn_users`.`uid` =1 AND `conn_users`.`uid`=`conn_users_profile`.`uid`

		if($_G['member']['adminid'] > 1 || $formula !== null){
			$formula = $formula===null ? '1' : $formula;

			$formula = str_replace(array('&&', '||'), array('AND', 'OR'), $formula);

			$formula = preg_replace_callback('/(uid|league|department) IN\s*\(([^\(\)]+)\)/is', array($this, 'handle_in_func'), $formula);

			$formula = preg_replace_callback('/(league|department)\s*=\s*(\d+)/is', array($this, 'handle_league_department'), $formula);

			$formula = preg_replace_callback('/(uid|status|adminid|groupid|manhour)/is', array($this, 'handle_user_prototype'), $formula);

			$formula = preg_replace_callback('/(gender|grade|academy|specialty|class|league|department)/is', array($this, 'handle_user_profile'), $formula);

			$sql .= '('.$formula.')';

			//FIND_IN_SET(%d, `department`)
		}else{
			$sql .= $_G['member']['adminid']==1 ? '1' : '0';
		}

		//trace($sql);

		return $sql;
	}

	private function handle_in_func($matches) {
		$prop = $matches[1];
		$id = explode(',', $matches[2]);
		foreach($id as &$val)
			$val = $prop.'='.intval($val);
		return '('.implode(' OR ', $id).')';
	}

	private function handle_league_department($matches) {
		$prop = $matches[1];
		$id = intval($matches[2]);
		//return "FIND_IN_SET('{$id}'," . DB::table('users_profile') . ".`{$prop}`)";
		return "FIND_IN_SET('{$id}', {$prop})";
	}

	private function handle_user_prototype($matches) {
		return DB::table('users').".`{$matches[1]}`";
	}

	private function handle_user_profile($matches) {
		return DB::table('users_profile').".`{$matches[1]}`";
	}

}
