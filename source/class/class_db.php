<?php

/**
 * 数据库操作类库
 * 
 * 版本 v0.1.0
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

/**
 * Discuz MySQL 类的支持 程序中不直接使用
 *
 */
class db_mysql {
	var $tablepre;
	var $version = '';
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
		$this->tablepre = $config[1]['tablepre'];
		if(!empty($this->config['map'])) {
			$this->map = $this->config['map'];
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

	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect) {
		if($pconnect) {
			$link = @mysql_pconnect($dbhost, $dbuser, $dbpw, MYSQL_CLIENT_COMPRESS);
		} else {
			$link = @mysql_connect($dbhost, $dbuser, $dbpw, 1, MYSQL_CLIENT_COMPRESS);
		}
		if(!$link) {
			$this->halt('notconnect');
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
		return '`'.$this->tablepre.$tablename.'`';
		//return $this->tablepre.$tablename;
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->curlink);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
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

	function query($sql, $silent = false, $unbuffered = false) {

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
				$this->halt('query_error');
			}
		}

		if(defined('APP_FRAMEWORK_DEBUG') && APP_FRAMEWORK_DEBUG) {
			$this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace());
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
		return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query('SELECT last_insert_id()'), 0);
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

	function close() {
		return mysql_close($this->curlink);
	}

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

}

/**
 * 对Discuz CORE 中 DB Object中的主要方法进行二次封装，方便程序调用
 *
 */
class DB {

	public static $db;
	public static $dbclass;

	public static function init($config, $dbclass = 'db_mysql') {
		self::$dbclass = $dbclass;
		self::$db = new $dbclass;
		self::$db->set_config($config);
		self::$db->connect();
	}

	/**
	 * 返回 DB object 指针
	 *
	 * @return pointer of db object from discuz core
	 */
	public static function object() {
		return self::$db;
	}

	/**
	 * 返回表名(pre_$table)
	 *
	 * @param 原始表名 $table
	 * @return 增加pre之后的名字
	 */
	public static function table($table) {
		return self::$db->table_name($table);
	}

	/**
	 * 删除一条或者多条记录
	 *
	 * @param string $table 原始表名
	 * @param string $condition 条件语句，不需要写WHERE
	 * @param int $limit 删除条目数
	 * @param boolean $unbuffered 立即返回？
	 */
	public static function delete($table, $condition, $limit = 0, $unbuffered = true) {
		if (empty($condition)) {
			return false;
		} elseif (is_array($condition)) {
			if (count($condition) == 2 && isset($condition['where']) && isset($condition['arg'])) {
				$where = self::format($condition['where'], $condition['arg']);
			} else {
				$where = self::implode_field_value($condition, ' AND ');
			}
		} else {
			$where = $condition;
		}
		$limit = dintval($limit);
		$sql = "DELETE FROM " . self::table($table) . " WHERE $where " . ($limit > 0 ? "LIMIT $limit" : '');
		return self::query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}

	/**
	 * 插入一条记录
	 *
	 * @param string $table 原始表名
	 * @param array $data 数组field->vlaue 对
	 * @param boolen $return_insert_id 返回 InsertID?
	 * @param boolen $replace 是否是REPLACE模式
	 * @param boolen $silent 屏蔽错误？
	 * @return InsertID or Result
	 */
	public static function insert($table, $data, $return_insert_id = false, $replace = false, $silent = false) {

		$sql = self::implode($data);

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

		$table = self::table($table);
		$silent = $silent ? 'SILENT' : '';

		return self::query("$cmd $table SET $sql", null, $silent, !$return_insert_id);
	}

	/**
	 * 更新一条或者多条数据记录
	 *
	 * @param string $table 原始表名
	 * @param array $data 数据field-value
	 * @param string $condition 条件语句，不需要写WHERE
	 * @param boolean $unbuffered 迅速返回？
	 * @param boolan $low_priority 延迟更新？
	 * @return result
	 */
	public static function update($table, $data, $condition, $unbuffered = false, $low_priority = false) {
		$sql = self::implode($data);
		if(empty($sql)) {
			return false;
		}
		$cmd = "UPDATE " . ($low_priority ? 'LOW_PRIORITY' : '');
		$table = self::table($table);
		$where = '';
		if (empty($condition)) {
			$where = '1';
		} elseif (is_array($condition)) {
			$where = self::implode($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$res = self::query("$cmd $table SET $sql WHERE $where", $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}

	/**
	 * 格式化field字段和value，并组成一个字符串
	 *
	 * @param array $array 格式为 key=>value 数组
	 * @param 分割符 $glue
	 * @return string
	 */
	public static function implode_field_value($array, $glue = ',') {
		return self::implode($array, $glue);
	}

	/**
	 * 返回插入的ID
	 *
	 * @return int
	 */
	public static function insert_id() {
		return self::$db->insert_id();
	}

	/**
	 * 依据查询结果，返回一行数据
	 *
	 * @param resourceID $resourceid
	 * @return array
	 */
	public static function fetch($resourceid, $type = MYSQL_ASSOC) {
		return self::$db->fetch_array($resourceid, $type);
	}

	/**
	 * 依据SQL文，返回一条查询结果
	 *
	 * @param string $query 查询语句
	 * @return array
	 */
	public static function fetch_first($sql, $arg = array(), $silent = false) {
		$res = self::query($sql, $arg, $silent, false);
		$ret = self::$db->fetch_array($res);
		self::$db->free_result($res);
		return $ret ? $ret : array();
	}

	/**
	 * 依据查询语句，返回所有数据
	 *
	 * @param string $sql
	 * @param ID $id
	 * @return array
	 */
	public static function fetch_all($sql, $arg = array(), $keyfield = '', $silent=false) {

		$data = array();
		$query = self::query($sql, $arg, $silent, false);
		while ($row = self::$db->fetch_array($query)) {
			if ($keyfield && isset($row[$keyfield])) {
				$data[$row[$keyfield]] = $row;
			} else {
				$data[] = $row;
			}
		}
		self::$db->free_result($query);
		return $data;
	}

	/**
	 * 返回某表结构信息
	 *
	 * @param string $table
	 * @param 详尽数据开关 $detail
	 * @return array
	 */
	public static function tbl_structure($table, $detail = false) {
		return DB::_execute('tbl_structure', $table, (boolean)$detail);
	}

	/**
	 * 返回某表索引信息
	 *
	 * @param string $table
	 * @param 详尽数据开关 $detail
	 * @return array OR string
	 */
	public static function tbl_keys($table, $detail = false) {
		return DB::_execute('tbl_keys', $table, (boolean)$detail);
	}

	/**
	 * 依据查询结果，返回结果数值
	 *
	 * @param resourceid $resourceid
	 * @return string or int
	 */
	public static function result($resourceid, $row = 0) {
		return self::$db->result($resourceid, $row);
	}

	/**
	 * 依据查询语句，返回结果数值
	 *
	 * @param string $query SQL查询语句
	 * @return unknown
	 */
	public static function result_first($sql, $arg = array(), $silent = false) {
		$res = self::query($sql, $arg, $silent, false);
		$ret = self::$db->result($res, 0);
		self::$db->free_result($res);
		return $ret;
	}

	/**
	 * 执行查询
	 *
	 * @param string $sql
	 * @param 类型定义 $type UNBUFFERED OR SILENT
	 * @return Resource OR Result
	 */
	public static function query($sql, $arg = array(), $silent = false, $unbuffered = false) {
		if (!empty($arg)) {
			if (is_array($arg)) {
				$sql = self::format($sql, $arg);
			} elseif ($arg === 'SILENT') {
				$silent = true;

			} elseif ($arg === 'UNBUFFERED') {
				$unbuffered = true;
			}
		}
		self::checkquery($sql);

		$ret = self::$db->query($sql, $silent, $unbuffered);
		if (!$unbuffered && $ret) {
			$cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
			if ($cmd === 'SELECT') {

			} elseif ($cmd === 'UPDATE' || $cmd === 'DELETE') {
				$ret = self::$db->affected_rows();
			} elseif ($cmd === 'INSERT') {
				$ret = self::$db->insert_id();
			}
		}
		return $ret;
	}

	/**
	 * 返回select的结果行数
	 *
	 * @param resource $resourceid
	 * @return int
	 */
	public static function num_rows($resourceid) {
		return self::$db->num_rows($resourceid);
	}

	/**
	 * 返回sql语句所影响的记录行数
	 *
	 * @return int
	 */
	public static function affected_rows() {
		return self::$db->affected_rows();
	}

	public static function free_result($query) {
		return self::$db->free_result($query);
	}

	/**
	 * 返回sql语句的错误信息
	 *
	 * @return string
	 */
	public static function error() {
		return self::$db->error();
	}

	/**
	 * 返回sql语句的错误代码
	 *
	 * @return int
	 */
	public static function errno() {
		return self::$db->errno();
	}

	function _execute($cmd , $arg1 = '', $arg2 = '') {
		$res = self::$db->$cmd($arg1, $arg2);
		return $res;
	}

	public static function checkquery($sql) {
		return database_safecheck::checkquery($sql);
	}

	public static function quote($str, $noarray = false) {

		if (is_string($str))
			return '\'' . addcslashes($str, "\n\r\\'\"\032") . '\'';

		if (is_int($str) or is_float($str))
			return '\'' . $str . '\'';

		if (is_array($str)) {
			if($noarray === false) {
				foreach ($str as &$v) {
					$v = self::quote($v, true);
				}
				return $str;
			} else {
				return '\'\'';
			}
		}

		if (is_bool($str))
			return $str ? '1' : '0';

		return '\'\'';
	}

	public static function quote_field($field) {
		if (is_array($field)) {
			foreach ($field as $k => $v) {
				$field[$k] = self::quote_field($v);
			}
		} else {
			if (strpos($field, '`') !== false)
				$field = str_replace('`', '', $field);
			$field = '`' . $field . '`';
		}
		return $field;
	}

	public static function limit($start, $limit = 0) {
		$limit = intval($limit > 0 ? $limit : 0);
		$start = intval($start > 0 ? $start : 0);
		if ($start > 0 && $limit > 0) {
			return " LIMIT $start, $limit";
		} elseif ($limit) {
			return " LIMIT $limit";
		} elseif ($start) {
			return " LIMIT $start";
		} else {
			return '';
		}
	}

	public static function order($field, $order = 'ASC') {
		if(empty($field)) {
			return '';
		}
		$order = strtoupper($order) == 'ASC' || empty($order) ? 'ASC' : 'DESC';
		return self::quote_field($field) . ' ' . $order;
	}

	public static function field($field, $val, $glue = '=') {

		$field = self::quote_field($field);

		if (is_array($val)) {
			$glue = $glue == 'notin' ? 'notin' : 'in';
		} elseif ($glue == 'in') {
			$glue = '=';
		}

		switch ($glue) {
			case '=':
				return $field . $glue . self::quote($val);
				break;
			case '-':
			case '+':
				return $field . '=' . $field . $glue . self::quote((string) $val);
				break;
			case '|':
			case '&':
			case '^':
				return $field . '=' . $field . $glue . self::quote($val);
				break;
			case '>':
			case '<':
			case '<>':
			case '<=':
			case '>=':
				return $field . $glue . self::quote($val);
				break;

			case 'like':
				return $field . ' LIKE(' . self::quote($val) . ')';
				break;

			case 'in':
			case 'notin':
				$val = $val ? implode(',', self::quote($val)) : '\'\'';
				return $field . ($glue == 'notin' ? ' NOT' : '') . ' IN(' . $val . ')';
				break;

			default:
				//self::$db->halt('Not allow this glue between field and value: "' . $glue . '"');
				self::$db->halt('glue_not_allowed: "' . $glue . '"');
		}
	}

	public static function implode($array, $glue = ',') {
		$sql = $comma = '';
		$glue = ' ' . trim($glue) . ' ';
		foreach ($array as $k => $v) {
			$sql .= $comma . self::quote_field($k) . '=' . self::quote($v);
			$comma = $glue;
		}
		return $sql;
	}

	public static function format($sql, $arg) {
		$count = substr_count($sql, '%');
		if (!$count) {
			return $sql;
		} elseif ($count > count($arg)) {
			//self::$db->halt('SQL string format error! This SQL need "' . $count . '" vars to replace into.', $sql);
			self::$db->halt('', $sql);
		}

		$len = strlen($sql);
		$i = $find = 0;
		$ret = '';
		while ($i <= $len && $find < $count) {
			if ($sql{$i} == '%') {
				$next = $sql{$i + 1};
				if ($next == 't') {
					$ret .= self::table($arg[$find]);
				} elseif ($next == 's') {
					$ret .= self::quote(is_array($arg[$find]) ? serialize($arg[$find]) : (string) $arg[$find]);
				} elseif ($next == 'f') {
					$ret .= sprintf('%F', $arg[$find]);
				} elseif ($next == 'd') {
					$ret .= dintval($arg[$find]);
				} elseif ($next == 'i') {
					$ret .= $arg[$find];
				} elseif ($next == 'n') {
					if (!empty($arg[$find])) {
						$ret .= is_array($arg[$find]) ? implode(',', self::quote($arg[$find])) : self::quote($arg[$find]);
					} else {
						$ret .= '0';
					}
				} else {
					$ret .= self::quote($arg[$find]);
				}
				$i++;
				$find++;
			} else {
				$ret .= $sql{$i};
			}
			$i++;
		}
		if ($i < $len) {
			$ret .= substr($sql, $i);
		}
		return $ret;
	}

}

class database_safecheck {

	protected static $checkcmd = array('SEL'=>1, 'UPD'=>1, 'INS'=>1, 'REP'=>1, 'DEL'=>1);
	protected static $config;

	public static function checkquery($sql) {
		if (self::$config === null) {
			self::$config = getglobal('config/security/querysafe');
		}
		if (self::$config['status']) {
			$check = 1;
			$cmd = strtoupper(substr(trim($sql), 0, 3));
			if(isset(self::$checkcmd[$cmd])) {
				$check = self::_do_query_safe($sql);
			} elseif(substr($cmd, 0, 2) === '/*') {
				$check = -1;
			}

			if ($check < 1) {
				//DB::$db->halt('It is not safe to do this query', $sql);
				DB::$db->halt('not_safe', $sql);
			}
		}
		return true;
	}

	private static function _do_query_safe($sql) {
		$sql = str_replace(array('\\\\', '\\\'', '\\"', '\'\''), '', $sql);
		$mark = $clean = '';
		if (strpos($sql, '/') === false && strpos($sql, '#') === false && strpos($sql, '-- ') === false && strpos($sql, '@') === false && strpos($sql, '`') === false) {
			$clean = preg_replace("/'(.+?)'/s", '', $sql);
		} else {
			$len = strlen($sql);
			$mark = $clean = '';
			for ($i = 0; $i < $len; $i++) {
				$str = $sql[$i];
				switch ($str) {
					case '`':
						if(!$mark) {
							$mark = '`';
							$clean .= $str;
						} elseif ($mark == '`') {
							$mark = '';
						}
						break;
					case '\'':
						if (!$mark) {
							$mark = '\'';
							$clean .= $str;
						} elseif ($mark == '\'') {
							$mark = '';
						}
						break;
					case '/':
						if (empty($mark) && $sql[$i + 1] == '*') {
							$mark = '/*';
							$clean .= $mark;
							$i++;
						} elseif ($mark == '/*' && $sql[$i - 1] == '*') {
							$mark = '';
							$clean .= '*';
						}
						break;
					case '#':
						if (empty($mark)) {
							$mark = $str;
							$clean .= $str;
						}
						break;
					case "\n":
						if ($mark == '#' || $mark == '--') {
							$mark = '';
						}
						break;
					case '-':
						if (empty($mark) && substr($sql, $i, 3) == '-- ') {
							$mark = '-- ';
							$clean .= $mark;
						}
						break;

					default:

						break;
				}
				$clean .= $mark ? '' : $str;
			}
		}

		if(strpos($clean, '@') !== false) {
			return '-3';
		}

		$clean = preg_replace("/[^a-z0-9_\-\(\)#\*\/\"]+/is", "", strtolower($clean));

		if (self::$config['afullnote']) {
			$clean = str_replace('/**/', '', $clean);
		}

		if (is_array(self::$config['dfunction'])) {
			foreach (self::$config['dfunction'] as $fun) {
				if (stripos($clean, $fun . '(') !== false)
					return '-1';
			}
		}

		if (is_array(self::$config['daction'])) {
			foreach (self::$config['daction'] as $action) {
				if (stripos($clean, $action) !== false)
					return '-3';
			}
		}

		if (self::$config['dlikehex'] && stripos($clean, 'like0x')) {
			return '-2';
		}

		if (is_array(self::$config['dnote'])) {
			foreach (self::$config['dnote'] as $note) {
				if (strpos($clean, $note) !== false)
					return '-4';
			}
		}

		return 1;
	}

	public static function setconfigstatus($data) {
		self::$config['status'] = $data ? 1 : 0;
	}

}

?>