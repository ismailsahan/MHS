<?php

!(defined('IN_APP_FRAMEWORK') && defined('APP_FRAMEWORK_SHUTTING_DOWN')) && exit('Access Denied');

global $_G, $template;

//整理SQL
if(class_exists('DB')){
	$db = &DB::$db;
	$sqldebug = array();
	$n = $discuz_table = 0;
	$queryinfo = array(
		'select' => 0,
		'update' => 0,
		'insert' => 0,
		'replace' => 0,
		'delete' => 0
	);
	$sqlw = array();
	$queries = count($db->sqldebug);
	$links = array();
	if(is_array($db->link))
	foreach($db->link as $k => $link) {
		$links[(string)$link] = $k;
	}
	$sqltime = 0;
	if(is_array($db->sqldebug))
	foreach($db->sqldebug as $string) {
		$sqltime += $string[1];
		$extra = $dt = '';
		$n++;
		$sql = $string[0];
		$sqldebugrow = '';
		if(preg_match('/^SELECT /', $string[0])) {
			$queryinfo['select']++;
			$query = @mysql_query('EXPLAIN '.$string[0], $string[3]);
			$i = 0;
			$sqldebugrow .= '';
			//$sqldebugrow .= '[table]';
			while($row = DB::fetch($query)) {
				if(!$i) {
					//$sqldebugrow .= '[tr][td]'.implode('[/td][td]', array_keys($row)).'[/td][/tr]';
					$i++;
				}
				if(strexists($row['Extra'], 'Using filesort')) {
					$sqlw['Using filesort']++;
					$dt .= ' • [color=red]Using filesort[/color]';
					//$extra .= $row['Extra'] = str_replace('Using filesort', '[color=red]Using filesort[/color]', $row['Extra']);
				}
				if(strexists($row['Extra'], 'Using temporary')) {
					$sqlw['Using temporary']++;
					$dt .= ' • [color=red]Using temporary[/color]';
					//$extra .= $row['Extra'] = str_replace('Using temporary', '[color=red]Using temporary[/color]', $row['Extra']);
				}
				//$sqldebugrow .= '<tr><td>'.implode('[/td][td]', $row).'[/td][/tr]';
			}
			$sqldebugrow .= '';
			//$sqldebugrow .= '[/table]';
		}elseif(preg_match('/^UPDATE /', $string[0])){
			$queryinfo['update']++;
		}elseif(preg_match('/^INSERT /', $string[0])){
			$queryinfo['insert']++;
		}elseif(preg_match('/^REPLACE /', $string[0])){
			$queryinfo['replace']++;
		}elseif(preg_match('/^DELETE /', $string[0])){
			$queryinfo['delete']++;
		}

		//$sqldebugrow .= '[hide][table=1][tr][th]File[/th][th]Line[/th][th]Function[/th][/tr]';
		/*foreach($string[2] as $error) {
			$error['file'] = str_replace(array(APP_FRAMEWORK_ROOT, '\\'), array('', '/'), $error['file']);
			$error['class'] = isset($error['class']) ? $error['class'] : '';
			$error['type'] = isset($error['type']) ? $error['type'] : '';
			$error['function'] = isset($error['function']) ? $error['function'] : '';
			//$sqldebugrow .= "[tr][td]{$error['file']}[/td][td]{$error['line']}[/td][td]{$error['class']}{$error['type']}{$error['function']}()[/td][/tr]";
			if(strexists($error['file'], 'discuz/discuz_table') || strexists($error['file'], 'table/table')) {
				$dt = ' • '.$error['file'];
			}
		}*/
		//$sqldebugrow .= '[/table][/hide]'.($extra ? $extra.'[br]' : '').'[br]';
		$sqldebug[] = $string[1].'s • DBLink '.$links[(string)$string[3]].$dt.'[br][color=blue]'.$sql.'[/color][br]'.$sqldebugrow;
	}
}

$trace = array();
$tmp = trace();
$trace['base'] = array();
$trace['process'] = &$tmp['process'];
$trace['error'] = &$tmp['error'];
//$trace = $_G['debug'];
$trace['files'] = get_included_files();
//$trace['template'] = isset($template) ? $template->get_debug_info() : array();
$trace['mapping'] = class_exists('StaticEngine') ? StaticEngine::$maps : array();
$trace['sql'] = empty($sqldebug) ? array() : $sqldebug;
$trace['trace'] = &$tmp['trace'];
$trace['_G'] = &$_G;

//进一步处理文件信息
$t = array_keys($trace['mapping']);
foreach ($trace['files'] as $k => $file){
	$temp = str_replace(APP_FRAMEWORK_ROOT, '.', $file);
	$f = basename($file);
	$trace['files'][$k] = $temp.' ( '.number_format(filesize($file)/1024, 2).' KB )';
	if(in_array($f, $t)) $trace['files'][$k] .= ' -> '.$trace['mapping'][$f];
}
unset($t);
//echo '<pre>';print_r($this->cachengine->stats());echo '</pre>';

//处理基本调试信息
$temp = dmicrotime() - $_G['starttime'];
$trace['base'][] = lang('debug', 'base_request', array('str' => date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']).' '.$_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'].' : '.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'])));
$trace['base'][] = lang('debug', 'base_runtime', array('str' => $temp));
$trace['base'][] = lang('debug', 'base_throughput', array('str' => number_format(1/$temp, 2)));
$trace['base'][] = lang('debug', 'base_mem', array('str' => number_format(memory_get_usage()/1024, 2).' KB'));
$trace['base'][] = lang('debug', 'base_sql', array('str' => (isset($db->querynum) ? "{$db->querynum} queries ({$queryinfo['select']} selects, {$queryinfo['update']} updates, {$queryinfo['delete']} deletes, {$queryinfo['insert']} inserts, {$queryinfo['replace']} replaces)" : 'Unknown')));
$trace['base'][] = lang('debug', 'base_files', array('str' => count($trace['mapping'])));
$trace['base'][] = lang('debug', 'base_cache', Cache::$count);
$trace['base'][] = lang('debug', 'base_session', array('str' => session_id()));
//'time' => number_format((dmicrotime() - $_G['starttime']), 6),
//'queries' => $db->querynum,
//'memory' => $cache->option['storage']=='files' ? null : ucfirst($cache->option['storage']),

include_once libfile('PageTrace');