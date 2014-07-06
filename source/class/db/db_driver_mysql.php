<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class db_driver_mysql {
	var $tablepre;
	var $version = '';
	var $drivertype = 'mysql';
	var $querynum = 0;
	var $slaveid = 0;
	var $curlink;
	var $link = array();
	var $config = array();
	var $sqldebug = array();
	var $map = array();

	function db_mysql($config = array()) {
		if(!empty($config)) {
			$this->set_config($config);
		}
	}

	function set_config($config) {
		$this->config = &$config;
		$this->tablepre = $config['1']['tablepre'];
		if(!empty($this->config['map'])) {
			$this->map = $this->config['map'];
			for($i = 1; $i <= 100; $i++) {
				if(isset($this->map['forum_thread'])) {
					$this->map['forum_thread_'.$i] = $this->map['forum_thread'];
				}
				if(isset($this->map['forum_post'])) {
					$this->map['forum_post_'.$i] = $this->map['forum_post'];
				}
				if(isset($this->map['forum_attachment']) && $i <= 10) {
					$this->map['forum_attachment_'.($i-1)] = $this->map['forum_attachment'];
				}
			}
			if(isset($this->map['common_member'])) {
				$this->map['common_member_archive'] =
				$this->map['common_member_count'] = $this->map['common_member_count_archive'] =
				$this->map['common_member_status'] = $this->map['common_member_status_archive'] =
				$this->map['common_member_profile'] = $this->map['common_member_profile_archive'] =
				$this->map['common_member_field_forum'] = $this->map['common_member_field_forum_archive'] =
				$this->map['common_member_field_home'] = $this->map['common_member_field_home_archive'] =
				$this->map['common_member_validate'] = $this->map['common_member_verify'] =
				$this->map['common_member_verify_info'] = $this->map['common_member'];
			}
		}
	}

	function connect($serverid = 1) {

		if(empty($this->config) || empty($this->config[$serverid])) {
			$this->halt('config_db_not_found');
		}

		$this->link[$serverid] = $this->_dbconnect(
			$this->config[$serverid]['dbhost'],
			$this->config[$serverid]['dbuser'],
			$this->config[$serverid]['dbpw'],
			$this->config[$serverid]['dbcharset'],
			$this->config[$serverid]['dbname'],
			$this->config[$serverid]['pconnect']
		);
		$this->curlink = $this->link[$serverid];

	}

	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect, $halt = true) {
		if($pconnect) {
			$link = @mysql_pconnect($dbhost, $dbuser, $dbpw, MYSQL_CLIENT_COMPRESS);
		} else {
			$link = @mysql_connect($dbhost, $dbuser, $dbpw, 1, MYSQL_CLIENT_COMPRESS);
		}
		if(!$link) {
			$halt && $this->halt('notconnect', $this->errno());
		} else {
			$this->curlink = $link;
			if($this->version() > '4.1') {
				$dbcharset = $dbcharset ? $dbcharset : $this->config[1]['dbcharset'];
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $link);
			}
			$dbname && @mysql_select_db($dbname, $link);
		}
		return $link;
	}

	function table_name($tablename) {
		if(!empty($this->map) && !empty($this->map[$tablename])) {
			$id = $this->map[$tablename];
			if(!$this->link[$id]) {
				$this->connect($id);
			}
			$this->curlink = $this->link[$id];
		} else {
			$this->curlink = $this->link[1];
		}
		//return '`'.$this->config[1]['dbname'].'`.`'.$this->tablepre.$tablename.'`';
		//return '`'.$this->tablepre.$tablename.'`';
		return $this->tablepre.$tablename;
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->curlink);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		if($result_type == 'MYSQL_ASSOC') $result_type = MYSQL_ASSOC;
		return mysql_fetch_array($query, $result_type);
	}

	function fetch_all($sql, $id = null) {
		$arr = array();
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) $id ? $arr[$data[$id]] = $data : $arr[] = $data;
		return $arr;
	}

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	public function query($sql, $silent = false, $unbuffered = false) {
		if(defined('APP_FRAMEWORK_DEBUG') && APP_FRAMEWORK_DEBUG) {
			$starttime = microtime(true);
		}

		if('UNBUFFERED' === $silent) {
			$silent = false;
			$unbuffered = true;
		} elseif('SILENT' === $silent) {
			$silent = true;
			$unbuffered = false;
		}

		$func = $unbuffered ? 'mysql_unbuffered_query' : 'mysql_query';

		if(!($query = $func($sql, $this->curlink))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
				$this->connect();
				return $this->query($sql, 'RETRY'.$silent);
			}
			if(!$silent) {
				$this->halt('query_error', $this->errno(), $sql);
				//$this->halt($this->error(), $this->errno(), $sql);
			}
		}

		if(defined('APP_FRAMEWORK_DEBUG') && APP_FRAMEWORK_DEBUG) {
			$this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->curlink);
		}

		$this->querynum++;
		return $query;
	}

	function tbl_structure($table, $detail = false) {
		$table = $this->table_name($table);
		$arr = array();
		$_arr = $this->fetch_all("SHOW FIELDS FROM `{$table}`");
		foreach($_arr as $k) {
			$arr[] = $detail ? $k : $k['Field'];
		}
		return $arr;
	}

	function tbl_keys($table, $detail = false) {
		$table = $this->table_name($table);
		$arr = array();
		$_arr = $this->fetch_all("SHOW KEYS FROM `{$table}`");
		foreach($_arr as $k) {
			$arr[] = $detail ? $k : $k['Column_name'];
		}
		return count($arr)==1 ? $arr[0] : $arr;
	}

	function affected_rows() {
		return mysql_affected_rows($this->curlink);
	}

	function error() {
		return (($this->curlink) ? mysql_error($this->curlink) : mysql_error());
	}

	function errno() {
		return intval(($this->curlink) ? mysql_errno($this->curlink) : mysql_errno());
	}

	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		if(empty($this->version)) {
			$this->version = mysql_get_server_info($this->curlink);
		}
		return $this->version;
	}

	function escape_string($str) {
		return mysql_escape_string($str);
	}

	function close() {
		return mysql_close($this->curlink);
	}

	function halt($message = '', $code = 0, $sql = '') {
		throw new DbException($message, $code, $sql);
	}

	/*
	function halt($message = '', $sql = '', $error = '') {
		if($error){
			$dberror = &$error;
			$dberrno = 0;
		}else{
			$dberror = $this->error();
			$dberrno = $this->errno();
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
	 */

}

?>