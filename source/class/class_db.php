<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

/**
 * 对Discuz CORE 中 DB Object中的主要方法进行二次封装，方便程序调用
 */
class DB {

	public static $db;
	public static $driver;
	private static $connected = false;

	/**
	 * 初始化数据库连接配置
	 * 
	 * @param string $driver 连接引擎 MySQL | MySQLi
	 * @param array  $config 数据库配置数组，格式参见配置文件中的 $_config[db]
	 * @return void
	 */
	public static function init($driver, $config) {
		self::$driver = $driver;
		self::$db = new $driver;
		self::$db->set_config($config);
		if(!$config[1]['connonuse']) {
			self::$db->connect();
			self::$connected = true;
		}
	}

	/**
	 * 返回 DB object 指针
	 */
	public static function object() {
		return self::$db;
	}

	/**
	 * 返回实际数据表名(pre_$table)
	 *
	 * @param string $table 原始表名
	 * @return string
	 */
	public static function table($table) {
		return self::$db->table_name($table);
	}

	/**
	 * 删除一条或者多条记录
	 *
	 * @param string $table			原始表名
	 * @param string $condition		条件语句，不需要写WHERE
	 * @param int $limit			删除的最大数目，为0表示不限制
	 * @param boolean $unbuffered	是否获取和缓存结果的行
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
		$sql = 'DELETE FROM ' . self::table($table) . " WHERE $where " . ($limit > 0 ? "LIMIT $limit" : '');
		return self::query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}

	/**
	 * 插入一条记录
	 *
	 * @param string $table				原始表名
	 * @param array  $data				关联数组
	 * @param boolen $return_insert_id	是否返回InsertID
	 * @param boolen $replace			设置插入方法是否为REPLACE，默认为INSERT
	 * @param boolen $silent			安静模式
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
	 * @param string $table			原始表名
	 * @param array $data			关联数组
	 * @param string $condition		条件语句，不需要写WHERE
	 * @param boolean $unbuffered	是否获取和缓存结果的行
	 * @param boolan $low_priority	该UPDATE操作是否为低优先级
	 * @return result
	 */
	public static function update($table, $data, $condition, $unbuffered = false, $low_priority = false) {
		$sql = self::implode($data);
		if(empty($sql)) {
			return false;
		}
		$cmd = 'UPDATE ' . ($low_priority ? 'LOW_PRIORITY' : '');
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
	 * @param resource $resourceid Query资源对象
	 * @param string   $type       返回的数组索引方式 可能的值: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
	 * @return array
	 * 
	 * MYSQL_ASSOC, MYSQL_NUM 和 MYSQL_BOTH 的说明
	 * 如果用了 MYSQL_BOTH，将得到一个同时包含关联和数字索引的数组
	 * 用 MYSQL_ASSOC 只得到关联索引
	 * 用 MYSQL_NUM 只得到数字索引
	 */
	public static function fetch($resourceid, $type = 'MYSQL_ASSOC') {
		return self::$db->fetch_array($resourceid, $type);
	}

	/**
	 * 依据SQL文，返回一条查询结果
	 *
	 * @param string	$sql		查询语句
	 * @param array		$arg		参数数组
	 * @param boolean	$silent		安静模式
	 * @return array
	 */
	public static function fetch_first($sql, $arg = array(), $silent = false) {
		$res = self::query($sql, $arg, $silent, false, $checkquery);
		$ret = self::$db->fetch_array($res);
		self::$db->free_result($res);
		return $ret ? $ret : array();
	}

	/**
	 * 依据查询语句，返回所有数据
	 * 以数组方式返回查询多条记录数据，且可以设置数据的 KEY 值使用某字段值
	 *
	 * @param string	$sql		SQL语句
	 * @param array		$arg		format参数数组
	 * @param string	$keyfield	把哪个字段的值作为数组索引，若未设置则使用数字
	 * @param boolean	$silent		安静模式
	 * @return array
	 */
	public static function fetch_all($sql, $arg = array(), $keyfield = '', $silent = false) {

		$data = array();
		$query = self::query($sql, $arg, $silent, false, $checkquery);
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
	 * 依据查询结果，返回结果数值
	 *
	 * @param resourceid $resourceid Query资源对象
	 * @param int        $row        行索引
	 * @return string or int
	 */
	public static function result($resourceid, $row = 0) {
		return self::$db->result($resourceid, $row);
	}

	/**
	 * 依据查询语句，返回结果数值
	 *
	 * @param string	$sql		SQL查询语句
	 * @param array		$arg		参数数组
	 * @param boolean	$silent		安静模式
	 * @return string
	 */
	public static function result_first($sql, $arg = array(), $silent = false) {
		$res = self::query($sql, $arg, $silent, false, $checkquery);
		$ret = self::$db->result($res, 0);
		self::$db->free_result($res);
		return $ret;
	}

	/**
	 * 执行查询
	 * 在非UNBUFFERED的情况下：INSERT SQL 语句返回 insert_id();UPDATE 和 DELETE SQL 语句返回 affected_rows()
	 *
	 * @param string	$sql		SQL语句
	 * @param array		$arg		SQL中的foramt参数数组，为向后兼容允许使用本参数设置$silent或$unbuffered
	 * @param boolean	$silent		是否为安静模式，为false时若出错则抛出错误并终止执行
	 * @param boolean	$unbuffered	是否获取和缓存结果的行，即选择使用mysql_unbuffered_query还是mysql_query函数来执行查询
	 * @return Resource OR Result
	 * 
	 * mysql_unbuffered_query 和 mysql_query 的区别
	 * mysql_unbuffered_query() 向 MySQL 发送一条 SQL 查询
	 * 但不像 mysql_query() 那样自动获取并缓存结果集
	 * 一方面，这在处理很大的结果集时会节省可观的内存
	 * 另一方面，可以在获取第一行后立即对结果集进行操作，而不用等到整个 SQL 语句都执行完毕
	 */
	public static function query($sql, $arg = array(), $silent = false, $unbuffered = false) {
		if(!self::$connected) {
			self::$db->connect();
			self::$connected = true;
		}

		if (!empty($arg)) {
			if (is_array($arg)) {
				$sql = self::format($sql, $arg);
			} elseif ($arg === 'SILENT') {
				$silent = true;

			} elseif ($arg === 'UNBUFFERED') {
				$unbuffered = true;
			}
		}

		if($checkquery) {
			self::checkquery($sql);
		}

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
	 * 返回 SELECT 的结果行数
	 *
	 * @param resource $resourceid Query资源对象
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

	/**
	 * 释放资源
	 * 
	 * @param resource $query query资源对象
	 * @return boolean
	 */
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

	/**
	 * 检查 SQL 语句是否具有安全威胁并尝试清除非法字符
	 * 
	 * @param string $sql SQL语句
	 * @return boolean true
	 */
	public static function checkquery($sql) {
		return database_safecheck::checkquery($sql);
	}

	/**
	 * 转换为安全的值
	 * 
	 * @param mixed $str     输入的值
	 * @param bool  $noarray 是否允许数组作为值
	 * @return string
	 */
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

	/**
	 * 给字段名加上反引号
	 * 
	 * @param string|array $field 字段名
	 * @return string|array
	 */
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

	/**
	 * 返回 SQL 语句中的 LIMIT 部分
	 * 
	 * @param int|string $start 起始数据索引
	 * @param int|string $limit 最大数目
	 * @return string
	 */
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

	/**
	 * 返回 SQL 语句中 ORDER BY 后的部分
	 * 
	 * @param string $field 字段名
	 * @param string $order 排序方式，ASC | DESC
	 * @return string
	 */
	public static function order($field, $order = 'ASC') {
		if(empty($field)) {
			return '';
		}
		$order = strtoupper($order) == 'ASC' || empty($order) ? 'ASC' : 'DESC';
		return self::quote_field($field) . ' ' . $order;
	}

	/**
	 * 返回字段与值之间的关系SQL
	 * 
	 * @param string $field 字段名
	 * @param mixed  $val   值
	 * @param string $glue  运算符
	 * @return string
	 */
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
				throw new DbException('Not allow this glue between field and value: "' . $glue . '"');
				//self::$db->halt('Not allow this glue between field and value: "' . $glue . '"');
				//self::$db->halt('glue_not_allowed: "' . $glue . '"');
		}
	}

	/**
	 * 格式化field字段和value，并组成一个字符串
	 *
	 * @param array  $array 关联数组
	 * @param string $glue  分隔符
	 * @return string
	 */
	public static function implode($array, $glue = ',') {
		$sql = $comma = '';
		$glue = ' ' . trim($glue) . ' ';
		foreach ($array as $k => $v) {
			$sql .= $comma . self::quote_field($k) . '=' . self::quote($v);
			$comma = $glue;
		}
		return $sql;
	}

	/**
	 * DB::implode 的别名
	 * 格式化field字段和value，并组成一个字符串
	 *
	 * @param array  $array 关联数组
	 * @param string $glue  分隔符
	 * @return string
	 */
	public static function implode_field_value($array, $glue = ',') {
		return self::implode($array, $glue);
	}

	/**
	 * 格式化 SQL 语句
	 * 支持的fomat语法有：
	 * %t 数据库表名	DB::table()
	 * %d 整型数据		intval()
	 * %s 字符串		addslashes()
	 * %n 列表			in IN (1,2,3)
	 * %f 浮点型数据	sprintf('%f', $var)
	 * %i 直接使用不进行处理
	 * 
	 * @param string $sql SQL语句
	 * @param array  $arg 参数数组
	 * @return string
	 */
	public static function format($sql, $arg) {
		$count = substr_count($sql, '%');
		if (!$count) {
			return $sql;
		} elseif ($count > count($arg)) {
			throw new DbException('SQL string format error! This SQL need "' . $count . '" vars to replace into.', 0, $sql);
			//self::$db->halt('SQL string format error! This SQL need "' . $count . '" vars to replace into.', $sql);
			//self::$db->halt('', $sql);
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

	/**
	 * 返回指定数据表结构信息
	 *
	 * @param string $table  数据表名
	 * @param bool   $detail 详尽数据
	 * @return array
	 */
	public static function tbl_structure($table, $detail = false) {
		return self::$db->tbl_structure($table, (boolean)$detail);
	}

	/**
	 * 返回指定数据表索引信息
	 *
	 * @param string $table  数据表名
	 * @param bool   $detail 详尽数据
	 * @return array
	 */
	public static function tbl_keys($table, $detail = false) {
		return self::$db->tbl_keys($table, (boolean)$detail);
	}

	/*private function _execute($cmd , $arg1 = '', $arg2 = '') {
		$res = self::$db->$cmd($arg1, $arg2);
		return $res;
	}*/

}


/**
 * SQL 安全隐患检查类
 */
class database_safecheck {

	protected static $checkcmd = array('SEL'=>1, 'UPD'=>1, 'INS'=>1, 'REP'=>1, 'DEL'=>1);
	protected static $config;

	/**
	 * 检查 SQL 并尝试清除非法字符
	 * 
	 * @param string $sql SQL语句
	 * @return bool
	 */
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
				trace('SQL checkquery: $check='.$check);
				throw new DbException('It is not safe to do this query', 0, $sql);
				//DB::$db->halt('It is not safe to do this query', $sql);
				//DB::$db->halt('not_safe', $sql);
			}
		}
		return true;
	}

	/**
	 * 内部方法 检查 SQL 并尝试清除非法字符
	 * 
	 * @param string $sql SQL语句
	 * @return bool
	 */
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

	/**
	 * 设置是否检查 SQL 安全隐患
	 * 
	 * @param bool $data
	 * @return void
	 */
	public static function setconfigstatus($data) {
		self::$config['status'] = $data ? 1 : 0;
	}

	/**
	 * 恢复 SQL 安全隐患检查状态
	 */
	public static function restoreconfigstatus() {
		self::$config['status'] = getglobal('config/security/querysafe/status') ? 1 : 0;
	}

}

?>