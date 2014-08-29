<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class framework_error {

	public static function system_error($message, $show = true, $save = true, $halt = true) {
		if(!empty($message)) {
			$message = lang('error', $message);
		} else {
			$message = lang('error', 'error_unknow');
		}

		list($showtrace, $logtrace) = framework_error::debug_backtrace();

		if($save) {
			$messagesave = '<b>'.$message.'</b><br /><b>PHP:</b>'.$logtrace;
			framework_error::write_error_log($messagesave);
		}

		if($show) {
			framework_error::show_error($message, $showtrace);
		}

		if($halt) {
			exit();
		} else {
			return $message;
		}
	}

	public static function template_error($message, $tplname) {
		$message = lang('error', $message);
		$tplname = str_replace(APP_FRAMEWORK_ROOT, '', $tplname);
		$message = $message.': '.$tplname;
		framework_error::system_error($message);
	}

	public static function debug_backtrace($exception = null) {
		$skipfunc[] = 'framework_error->debug_backtrace';
		$skipfunc[] = 'framework_error->db_error';
		$skipfunc[] = 'framework_error->template_error';
		$skipfunc[] = 'framework_error->system_error';
		$skipfunc[] = 'framework_error::debug_backtrace';
		$skipfunc[] = 'framework_error::db_error';
		$skipfunc[] = 'framework_error::template_error';
		$skipfunc[] = 'framework_error::system_error';
		$skipfunc[] = 'db_mysql->halt';
		$skipfunc[] = 'db_mysql->query';
		$skipfunc[] = 'db_driver_mysql->halt';
		$skipfunc[] = 'db_driver_mysql->query';
		$skipfunc[] = 'db_driver_mysqli->halt';
		$skipfunc[] = 'db_driver_mysqli->query';
		$skipfunc[] = 'DB::_execute';
		$skipfunc[] = 'DB::checkquery';
		$skipfunc[] = 'database_safecheck::checkquery';
		$skipfunc[] = 'Smarty_Internal_Template->writeCachedContent';
		$skipfunc[] = 'Smarty_Template_Cached->write';
		$skipfunc[] = 'Smarty_Internal_CacheResource_File->writeCachedContent';
		$skipfunc[] = 'Smarty_Internal_Write_File::writeFile';
		$skipfunc[] = 'Smarty_Internal_Template->compileTemplateSource';
		$skipfunc[] = 'Smarty_Internal_TemplateCompilerBase->compileTemplate';
		$skipfunc[] = 'Smarty_Internal_SmartyTemplateCompiler->doCompile';

		$backtrace = $exception ? $exception->getTrace() : debug_backtrace();
		krsort($backtrace);
		if($exception) $backtrace[] = array('file'=>$exception->getFile(), 'line'=>$exception->getLine(), 'function'=> 'break');
		$phpmsg = array();
		foreach ($backtrace as $error) {
			if(!empty($error['function'])) {
				$fun = isset($error['class']) ? $error['class'] : '';
				$fun .= isset($error['type']) ? $error['type'] : '';
				$fun .= isset($error['function']) ? $error['function'] : '';
				if(in_array($fun, $skipfunc)) {
					break;
				}
				$fun .= '(';
				if(!empty($error['args'])) {
					$mark = '';
					foreach($error['args'] as $arg) {
						$fun .= $mark;
						if(is_array($arg)) {
							$fun .= 'Array';
						} elseif(is_bool($arg)) {
							$fun .= $arg ? 'true' : 'false';
						} elseif(is_int($arg) || is_float($arg)) {
							$fun .= $arg;
						} else {
							$fun .= '\''.dhtmlspecialchars(self::clear($arg)).'\'';
							//$fun .= '\''.dhtmlspecialchars(substr(self::clear($arg), 0, 10)).(strlen($arg) > 10 ? ' ...' : '').'\'';
						}
						$mark = ', ';
					}
				}

				$fun .= ')';
				$error['function'] = $fun;
			}
			$phpmsg[] = array(
			    'file' => str_replace(array(APP_FRAMEWORK_ROOT, '\\'), array('.', '/'), $error['file']),
			    'line' => $error['line'],
			    'function' => $error['function'],
			);
		}
		$log = '';
		foreach($phpmsg as $trace) {
			if(empty($trace['file'])) continue;
			$log .= empty($log) ? '' : ' --> ';
			$log .= $trace['file'].':'.$trace['line'];
		}
		return array($phpmsg, $log);
	}

	public static function db_error($exception) {
		global $_G;

		list($phpmsg, $logtrace) = framework_error::debug_backtrace($exception);

		$message = $exception->getMessage();
		$code = $exception->getCode();
		$sql = $exception->getSql();

		$title = strexists($message, ' ') ? $message : lang('error', 'db_'.$message);

		$db = &DB::object();
		$dberrno = $db->errno();
		$dberror = str_replace($db->tablepre,  '', $db->error());
		if(defined('CHARSET') && CHARSET=='UTF-8' && in_array(mb_detect_encoding($dberror, array('GBK', 'UTF-8')), array('CP936', 'GBK'))) $dberror = mb_convert_encoding($dberror, 'UTF-8', 'GBK');
		$sql = dhtmlspecialchars(str_replace($db->tablepre,  '', $sql));

		$msg = '<b>'.$title.'</b><br />';
		$msg_errorinfo = $dberrno ? '<b>错误信息</b>: '.$dberror.' ['.$dberrno.']<br />' : '';

		if(defined('APP_FRAMEWORK_DEBUG') && APP_FRAMEWORK_DEBUG) {
			$msg .= $msg_errorinfo . ($sql ? '<b>SQL</b>: '.$sql : '');
		}elseif(!striexists($dberror, 'SELECT') && !striexists($dberror, 'UPDATE') && !striexists($dberror, 'DELETE') && !striexists($dberror, 'REPLACE')) {
			$msg .= $msg_errorinfo;
		}

		framework_error::show_error($msg, $phpmsg);
		unset($msg, $msg_errorinfo, $phpmsg);

		$errormsg = '<b>'.$title.'</b>';
		$errormsg .= $dberrno ? "[$dberrno]<br /><b>ERR:</b> $dberror<br />" : '<br />';
		if($sql) {
			$errormsg .= '<b>SQL:</b> '.$sql.'<br />';
		}
		$errormsg .= '<b>PHP:</b> '.$logtrace;

		framework_error::write_error_log($errormsg);
		exit();

	}

	public static function exception_error($exception) {

		if($exception instanceof DbException) {
			return self::db_error($exception);
		} else {
			$errormsg = $exception->getMessage();
		}

		list($phpmsg, $logtrace) = framework_error::debug_backtrace($exception);

		self::show_error($errormsg, $phpmsg);

		framework_error::write_error_log($errormsg . '<br /><b>PHP:</b> ' . $logtrace);
		exit();

	}

	public static function show_error($errormsg, $phpmsg = '') {
		global $_G;

		ob_end_clean();
		ob_start(getglobal('gzipcompress') ? 'ob_gzhandler' : null);

		send_http_status(500);
		if(defined('IS_AJAX') && IS_AJAX || defined('ACTION_NAME') && ACTION_NAME == 'api') ajaxReturn(array(
			'errno' => 500,
			'msg'	=> $errormsg
		));

		if(!defined('APP_FRAMEWORK_DEBUG') || !APP_FRAMEWORK_DEBUG) $phpmsg = '';
		$last = is_array($phpmsg) && !empty($phpmsg) ? end($phpmsg) : array();

		global $template;
		if(!empty($template) && $template->templateExists('error')){
			$template->assign('errormsg', $errormsg, true);
			$template->assign('phpmsg', $phpmsg, true);
			$template->assign('last', $last, true);
			$template->display('error');
		}else{
			include APP_FRAMEWORK_ROOT.'/source/ErrorException.php';
		}

	}

	public static function clear($message) {
		return str_replace(array("\t", "\r", "\n"), " ", $message);
	}

	public static function sql_clear($message) {
		$message = self::clear($message);
		$message = str_replace(DB::object()->tablepre, '', $message);
		$message = dhtmlspecialchars($message);
		return $message;
	}

	public static function write_error_log($message) {

		$message = framework_error::clear($message);
		$time = time();
		$file =  APP_FRAMEWORK_ROOT.'/data/log/errorlog_'.date("Ym").'.php';
		$hash = md5($message);

		$uid = getglobal('uid');
		$ip = getglobal('clientip');

		$user = '<b>User:</b> uid='.intval($uid).'; IP='.$ip.'; RIP:'.$_SERVER['REMOTE_ADDR'];
		$uri = 'Request: '.dhtmlspecialchars(framework_error::clear($_SERVER['REQUEST_URI']));
		$message = "<?php exit;?>\t{$time}\t$message\t$hash\t$user $uri\n";
		if($fp = @fopen($file, 'rb')) {
			$lastlen = 50000;
			$maxtime = 60 * 10;
			$offset = filesize($file) - $lastlen;
			if($offset > 0) {
				fseek($fp, $offset);
			}
			if($data = fread($fp, $lastlen)) {
				$array = explode("\n", $data);
				if(is_array($array)) foreach($array as $key => $val) {
					$row = explode("\t", $val);
					if($row[0] != '<?php exit;?>') continue;
					if($row[3] == $hash && ($row[1] > $time - $maxtime)) {
						return;
					}
				}
			}
		}
		//error_log($message, 3, $file);
		file_put_contents($file, $message, FILE_APPEND);
	}

}