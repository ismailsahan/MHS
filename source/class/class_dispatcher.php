<?php

/**
 * 完成URL解析、路由和调度
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

//if(!class_exists('class_name')) require libfile('');

class Dispatcher {

	const URL_COMMON	= 0; // 普通模式
	const URL_PATHINFO	= 1; // PATHINFO模式
	const URL_REWRITE	= 2; // REWRITE模式
	const URL_COMPAT	= 3; // 兼容模式

	public static function dispatch($config = array()) {
		global $_G;
		$config = &$_G['config']['router'];

		$urlMode = $config['url_model'];
		if(isset($_GET[$config['var_pathinfo']])) { // 判断URL里面是否有兼容模式参数
			$_SERVER['PATH_INFO'] = $_GET[$config['var_pathinfo']];
			unset($_GET[$config['var_pathinfo']]);
		}

		if(isset($_SERVER['PATH_INFO']) && !strexists($_SERVER['PATH_INFO'], $config['url_html_suffix']) && strpos($_SERVER['PATH_INFO'], '.')) {
			define('DISABLE_TRACE', true);
			$pos = stripos($_SERVER['PATH_INFO'], '/assets/');
			if($pos === false){
				$pos = strripos($_SERVER['PATH_INFO'], '/'.$_G['basefilename']);
				if($_SERVER['argc'] > 0) {
					$url = U('?'.$_SERVER['argv'][0]);
				}
			}
			if($pos === false){
				$pos = stripos($_SERVER['PATH_INFO'], '/cache/');
			}
			send_http_status(301);
			header('Location: '.(isset($url) ? $url : $_G['siteurl'].substr($_SERVER['PATH_INFO'], $pos+1)));
			exit;
		}

		if($config['url_model'] > 0 || isset($_SERVER['PATH_INFO'])) {
			if(!isset($_SERVER['PATH_INFO'])) {
				$types = explode(',', $config['url_pathinfo_fetch']);
				foreach ($types as $type){
					if(0 === strpos($type, ':')) {// 支持函数判断
						$_SERVER['PATH_INFO'] = call_user_func(substr($type,1));
						break;
					}elseif(!empty($_SERVER[$type])) {
						$_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ? substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
						break;
					}
				}
			}

			$depr = $config['url_pathinfo_depr'];
			if(!empty($_SERVER['PATH_INFO'])) {
				$part = pathinfo($_SERVER['PATH_INFO']);
				define('__EXT__', isset($part['extension']) ? strtolower($part['extension']) : '');
				if(__EXT__){
					if($config['url_deny_suffix'] && preg_match('/\.('.trim($config['url_deny_suffix'], '.').')$/i', $_SERVER['PATH_INFO'])){
						send_http_status(404);
						exit;
					}
					if($config['url_html_suffix']) {
						$_SERVER['PATH_INFO'] = preg_replace('/\.('.trim($config['url_html_suffix'], '.').')$/i', '', $_SERVER['PATH_INFO']);
					}else{
						$_SERVER['PATH_INFO'] = preg_replace('/.'.__EXT__.'$/i', '', $_SERVER['PATH_INFO']);
					}
				}

				if(!self::routerCheck()){   // 检测路由规则 如果没有则按默认规则调度URL
					$paths = explode($depr, trim($_SERVER['PATH_INFO'], '/'));
					if($config['var_url_params']) {
						// 直接通过$_GET['_URL_'][1] $_GET['_URL_'][2] 获取URL参数 方便不用路由时参数获取
						$_GET[$config['var_url_params']] = $paths;
					}
					//$var = array();
					/*if(!isset($_GET[$config['var_module']])) {// 还没有定义模块名称
						$var[$config['var_module']] = array_shift($paths);
					}*/
					if(!isset($_GET[$config['var_action']])) {// 还没有定义动作名称
						$_GET[$config['var_action']] = array_shift($paths);
					}
					if(count($paths) > 0 && !isset($_GET[$config['var_operation']])) {// 还没有定义操作名称
						$_GET[$config['var_operation']] = array_shift($paths);
					}
					// 解析剩余的URL参数
					preg_replace('@(\w+)\/([^\/]+)@e', '$_GET[\'\\1\']=strip_tags(\'\\2\');', implode('/', $paths));
					//$_GET = array_merge($var, $_GET);
				}
			}
		}

		define('ACTION_NAME', self::getAction($config['var_action']));
		define('OPERATION_NAME', self::getOperation($config['var_operation']));
	}

	/**
	 * URL组装 支持不同URL模式
	 * @param string $url URL表达式，格式：'[动作/操作#锚点@域名]?参数1=值1&参数2=值2...'
	 * @param string|array $vars 传入的参数，支持数组和字符串
	 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
	 * @param boolean $redirect 是否跳转，如果设置为true则表示跳转到该URL地址
	 * @param boolean $domain 是否显示域名
	 * @return string
	 */
	public static function generate($url='', $vars='', $suffix=true, $redirect=false, $domain=false) {
		global $_G;
		$config = &$_G['config']['router'];
		if(is_array($url)) {
			$url = $url[1];
		}
		// 解析URL
		$info = parse_url($url);
		$url = empty($info['path']) ? (defined('ACTION_NAME') ? ACTION_NAME : $config['default_action']) : $info['path'];
		if(isset($info['fragment'])) { // 解析锚点
			$anchor = $info['fragment'];
			if(false !== strpos($anchor, '?')) { // 解析参数
				list($anchor, $info['query']) = explode('?', $anchor, 2);
			}		
			if(false !== strpos($anchor, '@')) { // 解析域名
				list($anchor, $host) = explode('@', $anchor, 2);
			}
		}elseif(false !== strpos($url, '@')) { // 解析域名
			list($url, $host) = explode('@', $info['path'], 2);
		}
		// 解析子域名
		if(isset($host)) {
			$domain = $host.(strpos($host, '.') ? '' : strstr($_SERVER['HTTP_HOST'], '.'));
		}elseif($domain === true){
			$domain = $_SERVER['HTTP_HOST'];
		}

		// 解析参数
		if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
			parse_str($vars, $vars);
		}elseif(!is_array($vars)){
			$vars = array();
		}
		if(isset($info['query'])) { // 解析地址里面参数 合并到vars
			parse_str($info['query'], $params);
			$vars = array_merge($params, $vars);
		}
		
		// URL组装
		$depr = $config['url_pathinfo_depr'];
		if($url) {
			if(0 === strpos($url, '/')) {// 定义路由
				$route = true;
				$url = substr($url, 1);
				if('/' != $depr) {
					$url = str_replace('/', $depr, $url);
				}
			}else{
				if('/' != $depr) { // 安全替换
					$url = str_replace('/', $depr, $url);
				}
				// 解析分组、模块和操作
				$url = trim($url, $depr);
				$path = explode($depr, $url);
				$var = array();
				$t = empty($info['path']);
				if($t) {
					$_path = array();
					if(isset($vars[$config['var_action']])) {
						$_path[] = $vars[$config['var_action']];
						unset($vars[$config['var_action']]);
					}
					if(isset($vars[$config['var_operation']])) {
						$_path[] = $vars[$config['var_operation']];
						unset($vars[$config['var_operation']]);
					}
					if(!empty($_path)) $path = $_path;
				}
				//$var[$config['var_action']] = !empty($path) ? array_pop($path) : ACTION_NAME;
				//$var[$config['var_module']] = !empty($path) ? array_pop($path) : MODULE_NAME;
				$var[$config['var_operation']] = !empty($path) ? array_pop($path) : (defined('OPERATION_NAME') ? OPERATION_NAME : $config['default_operation']);
				$var[$config['var_action']] = !empty($path) ? array_pop($path) : (defined('ACTION_NAME') ? ACTION_NAME : $config['default_action']);
				if($maps = $config['url_action_map']) {
					//if(isset($maps[strtolower($var[$config['var_module']])])) {
					//	$maps = $maps[strtolower($var[$config['var_module']])];
						if($action = array_search(strtolower($var[$config['var_action']]), $maps)){
							$var[$config['var_action']] = $action;
						}
					//}
				}
				/*if($maps = $config['url_module_map']) {
					if($module = array_search(strtolower($var[$config['var_module']]), $maps)){
						$var[$config['var_module']] = $module;
					}
				}*/
				/*if($config['url_case_insensitive']) {
					$var[$config['var_module']] = parse_name($var[$config['var_module']]);
				}*/
				/*if(!$config['app_sub_domain_deploy'] && $config['app_group_list']) {
					if(!empty($path)) {
						$group = array_pop($path);
						$var[$config['var_group']] = $group;
					}else{
						if(GROUP_NAME != $config['default_group']) {
							$var[$config['var_group']] = GROUP_NAME;
						}
					}
					if($config['url_case_insensitive'] && isset($var[$config['var_group']])) {
						$var[$config['var_group']] = strtolower($var[$config['var_group']]);
					}
				}*/
			}
		}

		if($config['url_model'] == 0) { // 普通模式URL转换
			$url = '?'.http_build_query(array_reverse($var));
			if(!empty($vars)) {
				$vars = urldecode(http_build_query($vars));
				$url .= '&'.$vars;
			}
		}else{ // PATHINFO模式或者兼容URL模式
			if(isset($route)) {
				$url = '/'.rtrim($url, $depr);
			}else{
				$url = '/'.implode($depr, array_reverse($var));
			}
			if(!empty($vars)) { // 添加参数
				foreach ($vars as $var => $val){
					/*if('' !== trim($val))*/ $url .= $depr . $var . $depr . urlencode($val);
				}
			}
			if($suffix) {
				$suffix = $suffix===true ? $config['url_html_suffix'] : $suffix;
				if($pos = strpos($suffix, '|')){
					$suffix = substr($suffix, 0, $pos);
				}
				if($suffix && '/' != substr($url, -1)){
					$url .= '.'.ltrim($suffix, '.');
				}
			}
		}
		$url = $_G['basefilename'].($config['url_model']==3 ? '?'.$config['var_pathinfo'].'=' : '').$url;
		if(isset($anchor)){
			$url .= '#'.$anchor;
		}
		if($domain) {
			$url = (IS_HTTPS ? 'https://' : 'http://').$domain.$url;
		}else{
			$url = $_G['siteurl'].$url;
		}
		if($redirect) // 直接跳转URL
			redirect($url);
		else
			return $url;
	}

	/**
	 * 路由检测
	 * @access public
	 * @return void
	 */
	static public function routerCheck() {
		$return = false;
		// 路由检测标签
		return $return;
	}

	/**
	 * 获得实际的模块名称
	 * @access private
	 * @return string
	 */
	static private function getModule($var) {
		global $_G;
		$config = &$_G['config']['router'];
		$module = (!empty($_GET[$var])? $_GET[$var] : $config['default_module']);
		unset($_GET[$var]);
		if($maps = $config['url_module_map']) {
			$_module = strtolower($module);
			if(isset($maps[$_module])) {
				// 记录当前别名
				define('MODULE_ALIAS', $_module);
				// 获取实际的模块名
				return $maps[MODULE_ALIAS];
			}elseif(array_search($_module, $maps)){
				// 禁止访问原始模块
				return '';
			}
		}
		if($config['url_case_insensitive']) {
			// URL地址不区分大小写
			// 智能识别方式 index.php/user_type/index/ 识别到 UserTypeAction 模块
			$module = ucfirst(parse_name($module, 1));
		}
		return strip_tags($module);
	}

	/**
	 * 获得实际的操作名称
	 * @access private
	 * @return string
	 */
	static private function getAction($var) {
		global $_G;
		$config = &$_G['config']['router'];
		$action = !empty($_POST[$var]) ? $_POST[$var] : (!empty($_GET[$var]) ? $_GET[$var] : $config['default_action']);
		if(!in_array($action, $_G['config']['app']['actions'], true)) $action = $config['default_action'];
		unset($_POST[$var], $_GET[$var]);
		if($maps = $config['url_action_map']) {
			$_action = strtolower($action);
			if(isset($maps[$_action])) {
				// 记录当前别名
				define('ACTION_ALIAS', $_action);
				// 获取实际的操作名
				return $maps[ACTION_ALIAS];
			}elseif(array_search($_action, $maps)){
				// 禁止访问原始操作
				return '';
			}
		}
		return strip_tags($config['url_case_insensitive'] ? strtolower($action) : $action);
	}

	static private function getOperation($var) {
		global $_G;
		$config = &$_G['config']['router'];
		$operation = !empty($_POST[$var]) ? $_POST[$var] : (!empty($_GET[$var]) ? $_GET[$var] : $config['default_operation']);
		unset($_POST[$var], $_GET[$var]);
		if($maps = $config['url_operation_map']) {
			$_operation = strtolower($operation);
			if(isset($maps[$_operation])) {
				// 记录当前别名
				define('OPERATION_ALIAS', $_operation);
				// 获取实际的操作名
				return $maps[OPERATION_ALIAS];
			}elseif(array_search($_operation, $maps)){
				// 禁止访问原始操作
				return '';
			}
		}
		return strip_tags($config['url_case_insensitive'] ? strtolower($operation) : $operation);
	}

	public function stripTags($k, $v) {
		$_G[$k] = strip_tags($v);
	}
}