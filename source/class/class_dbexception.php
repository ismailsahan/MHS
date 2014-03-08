<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class DbException extends Exception{

	public $sql;

	public function __construct($message, $code = 0, $sql = '') {
		//set_exception_handler(array('core', 'handleException'));
		$this->sql = $sql;
		/*$dberror = DB::error();
		if(in_array(mb_detect_encoding($dberror, array('GBK', 'UTF-8')), array('CP936', 'GBK'))) $dberror = mb_convert_encoding($dberror, 'UTF-8', 'GBK');
		$errmsg = error('db_error', array(
			'$message' => error(((empty($message) || strexists($message, ' ')) ? '' : 'db_').$message, array(), true),
			'$info' => $dberror ? error('db_error_message', array('$dberror' => $dberror), true) : '',
			'$sql' => $sql ? error('db_error_sql', array('$sql' => $sql), true) : '',
			'$errorno' => $dberrno ? error('db_error_no', array('$dberrno' => $dberrno), true) : ''
		), true);
		$message = lang('error', ((empty($message) || strexists($message, ' ')) ? '' : 'db_').$message);
		$message .= '<br/>'.$errmsg;*/
		//parent::__construct($message, $code);

		if($code){
			$dberror = '';
			$dberrno = $code;
		}else{
			$dberror = DB::error();
			$dberrno = DB::errno();
		}
		if(in_array(mb_detect_encoding($dberror, array('GBK', 'UTF-8')), array('CP936', 'GBK'))) $dberror = mb_convert_encoding($dberror, 'UTF-8', 'GBK');
		$errmsg = error('db_error', array(
			'$message' => error(((empty($message) || strexists($message, ' ')) ? '' : 'db_').$message, array(), true),
			'$info' => $dberror ? error('db_error_message', array('$dberror' => $dberror), true) : '',
			'$sql' => $sql ? error('db_error_sql', array('$sql' => $sql), true) : '',
			'$errorno' => $dberrno ? error('db_error_no', array('$dberrno' => $dberrno), true) : ''
		), true);
		$ext = array();
		if(!APP_FRAMEWORK_DEBUG) $ext['__ERRMSG__'] = error('db_error', array(
			'$message' => error(((empty($message) || strexists($message, ' ')) ? '' : 'db_').$message, array(), true),
			'$info' => $dberror && !strexists($dberror, 'SELECT ') && !strexists($dberror, 'INSERT ') && !strexists($dberror, 'UPDATE ') && !strexists($dberror, 'DELETE ') && !strexists($dberror, 'FROM ') && !strexists($dberror, '`') && !strexists($dberror, $this->tablepre) && !strexists($dberror, 'WHERE ') && !strexists($dberror, '=') ? error('db_error_message', array('$dberror' => $dberror), true) : '',
			'$sql' => '',
			'$errorno' => $dberrno ? error('db_error_no', array('$dberrno' => $dberrno), true) : ''
		), true);
		halt($errmsg, $ext);
	}

	public function getSql() {
		return $this->sql;
	}
}
?>