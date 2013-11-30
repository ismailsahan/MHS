<?php
/*
# @copyright: 分享工作室 Yuan 2013 08 08
# @filename; PHPnew.class.php
# @version: PHPnew CACHE_TPL 7.3.2;
#
# http://phpnew.fenanr.com/
*/

class PHPnew {
	public $templates_dir		 = array();		   //模板路径;
	public $templates_cache	   = null;			   //缓存模板路径;
	public $templates_postfix	 = '.html';		   //模板后缀;
	public $templates_caching	 = '.php';			//缓存后缀;
	public $templates_var		 = 'All';				//变量获取模式, All,ASSIGN;
	public $templates_auto		= true;				 //自动更新模板;
	public $templates_new		 = false;				 //设置当次更新, 系统更新可强制配置为true;
	public $templates_space	   = false;			   //清除无意义字符
	public $templates_ankey	   = false;			   // 加密模板文件名,避免被猜测到.
	public $templates_isdebug	 = null;
	public $templates_replace	 = array();	   // 全局替换块.
	public $templates_depth = 4;//静态文件搜索深度
	public $templates_direxception = array();
	
	//结果集,请不要修改以下内容;
	private $templates_lang = array();		  // 语言数组.
	private $templates_autofile = array();	  // 自动匹配文件数组.
	private $templates_file = array();		  //模板文件
	private $templates_cache_file = array();	//缓存文件;
	private $templates_name = null;			 //标识名
	private $templates_message = null;		  //html内容;
	private $templates_update = 0;			  //更新次数
	private $templates_assign = array();		//用户用smarty模式;
	private $templates_static_assign = array(); // 静态变量数组. 用于css.
	private $templates_debug = array();		 //错误信息;
	private $templates_blockreplace = array();	  // block替换数组.
	private $templates_viewcount  = 0;		  // 视图次数.
	private $templates_writecount  = 0;
	private $PHPnew = 'PHPnew CACHE_TPL 7.3.2';
	
	
	public function __construct(){
		$this->preg__debug('PHPnew CACHE_TPL 初始化开始.....', true);
		$this->preg__debug("\n", true);
		$dirags = func_get_args();
		foreach($dirags AS $val)
			$this->set_templates_path($val);
		$this->templates_cache = './Data/cache_tpl/';
		return $this;
	}
	
	public function get_debug_info(){
		$ret = array();
		if($this->templates_isdebug){
			$this->templates_debug[]['Notice'] = "\n";
			$this->templates_debug[]['Notice'] = 'PHPnew CACHE_TPL 所有工作已经结束.....';
			//echo '<br /><hr />';
			
			# 植入几个全局统计.
			$newarrr = array();
			foreach($this->templates_debug AS $key => $val){
				$newarrr[] = $val;
				if($key === 1){
					$newarrr[] = array('Notice'=>'模板文件信息: '. implode(',[br]', $this->templates_file));
					$newarrr[] = array('Notice'=>'缓存文件信息: '. implode(',[br]', $this->templates_cache_file));
					$newarrr[] = array('Notice'=>'自动匹配路径: '. implode(',', $this->set_auto_path(true)).' * 在此目录或者子目录的文件都可以直接匹配');
					$newarrr[] = array('Notice'=>'语言数组数据: '. implode(',', array_keys($this->templates_lang)));
					$newarrr[] = array('Notice'=>'变量数组数据: '. count($this->templates_assign));
					$newarrr[] = array('Notice'=>'静态变量数据: '. count($this->templates_static_assign).' * 主要用于CSS, JS等');
					$newarrr[] = array('Notice'=>'block解析数据: '. count($this->templates_blockreplace));
					$newarrr[] = array('Notice'=>"\n");
					
					$newarrr[] = array('Notice'=>"模板更新次数: ".$this->templates_update);
					$newarrr[] = array('Notice'=>"加载视图次数: ".$this->templates_viewcount);
					$newarrr[] = array('Notice'=>"写入文件次数: ".$this->templates_writecount);
					$newarrr[] = array('Notice'=>"全局替换次数: ".count($this->templates_replace));
					
					$newarrr[] = array('Notice'=>"全局设置: 模板后缀:".var_export($this->templates_postfix, true).'; 缓存后缀: '.var_export($this->templates_caching, true).'; 变量模式: '.$this->templates_var.'; 自动更新: '.var_export($this->templates_auto, true).'; 当次强制更新: '.var_export($this->templates_new, true).'; 清除无意义字符: '.var_export($this->templates_space, true).'; 安全码: '.var_export($this->templates_ankey, true) );
					$newarrr[] = array('Notice'=>"\n");
				}
			}
			
			$this->templates_debug = &$newarrr;
			foreach($this->templates_debug AS $key => $val){
				$trues = false;
				if(isset($val['Notice'])){
					$cls = 'Notice';
					$val = $val['Notice'];
					$trues = true;
				}else if($val['Warn']){
					$cls = 'Warning';
					$val = $val['Warn'];
					$trues = true;
				}
				
				if($trues){
					$clstr = '[b][color=#BAE7DD]'.$cls.':[/color][/b]';
					$val = &str_replace(array(APP_FRAMEWORK_ROOT, str_replace('\\', '/', APP_FRAMEWORK_ROOT)), '.', $val);
					if($cls === 'Warning'){
						$clstr = '[b][color=#FF8040]'.$cls.':[/color][/b]';
						$val = '[color=#FF8040]'.$val.'[/color]';
					}
					if($val === "\n"){
						$val = '';
						$clstr = '';
					}
					//echo('<div style="background-color: #498BBC; text-align: left; border-bottom: 1px solid #F2F8FB; padding: 2px 6px; font-size:13px; color: white;">'.$clstr.' '.$val.'</div>');
					$ret[] = $clstr.' '.$val;
				}
			}
		}
		return $ret;
	}
	
	//公共方法: 文件名, 是否返回缓存文件.
	public function display($PHPnew_file_name, $returnpath = false){
		static $once = 0;
		global $_G;
		$this->templates_viewcount += 1;
		if($once === 0){
			$this->templates_postfix = '.'.ltrim($this->templates_postfix, '.');
			
			if(is_dir($this->templates_cache) == false){
				halt('缓存目录一定要存在: '.var_export($this->templates_cache, true));
			}
			
			if($this->templates_isdebug){
				if(isset($this->templates_default) && is_dir($this->templates_default) === false)
					$this->templates_default = false;
				
				$tplnotice = $this->templates_dir?'模板目录已经被指定:'.implode(', ',$this->templates_dir).' (验证存在)':'未指定模板目录, 系统将从自动目录中寻找模板';
				$this->preg__debug($tplnotice);
				$autodir = $this->set_auto_path(true);
				
				foreach($autodir AS $key => $val){
					$this->preg__debug('文件匹配自动目录: '.$val .' (验证存在)');
				}
			}
			$once = 1;
		}
		
		$this->preg__debug("\n");
		$this->preg__debug($this->templates_viewcount.' 次模板调用开始.....', E_NOTICE);
		$htmlname = basename($PHPnew_file_name);
		if(isset($this->templates_debug[$PHPnew_file_name]) === true || !$PHPnew_file_name){
			$this->preg__debug('参数为空 或者 重复模板调用:'. var_export($PHPnew_file_name, true).' 函数停止前进',E_WARNING);
			return false;
		}
		
		strpos($PHPnew_file_name,'.') === false && $PHPnew_file_name .= $this->templates_postfix;
		$this->templates_name = $PHPnew_file_name;
		
		$tplcache = $this->__get_path($PHPnew_file_name);
		$true_check = $this->__check_update($tplcache);
		
		$this->templates_cache_file[$PHPnew_file_name] = $tplcache['cache'];
		$this->templates_file[$PHPnew_file_name] = $tplcache['tpl'];
		$this->templates_debug[$PHPnew_file_name] = array();
		
		$PHPnew_path = false;
		if($true_check === true){
			if($tplcache['cache'])
			$PHPnew_path = $this->templates_cache_file[$PHPnew_file_name];
		}else{
			if(!$this->templates_file[$PHPnew_file_name] || !$this->templates_message = $this->preg__file($this->templates_file[$PHPnew_file_name])){
				$this->preg__debug('模板文件'.$PHPnew_file_name.' 读取失败,请检查模板是否存在',E_WARNING);
			}
			
			if($this->templates_message){
				$this->templates_message = $this->__parse_html($this->templates_message);
				$PHPnew_path = $this->templates_cache_file[$PHPnew_file_name];
				if(!$this->preg__file($PHPnew_path,$this->templates_message,true))
					$this->preg__debug('模板文件无法写入: '. $htmlname);
				
				$this->templates_message = null;
				$this->templates_update += 1;
			}
		}
		
		unset($tplcache , $PHPnew_file_name);
		if($this->templates_viewcount === 1 && $returnpath === false && $PHPnew_path){
			$this->__parse_var();
			$this->preg__debug("第".$this->templates_viewcount."次输出: ".$htmlname.' & '. $PHPnew_path);
			include $PHPnew_path;
		}else{
			if($returnpath !== false){
				$this->preg__debug("第".$this->templates_viewcount."次强制返回路径: ".$htmlname.' & ' .$PHPnew_path);
			}else if(!$PHPnew_path){
				$this->preg__debug("第".$this->templates_viewcount."次错误的模板: ".$htmlname);
			}else{
				$this->preg__debug("第".$this->templates_viewcount."次返回路径: ".$htmlname.' & ' .$PHPnew_path);
			}
		}
		
		return $PHPnew_path;
	}
	
	public function load(){
		return call_user_func_array(array($this, 'display'), func_get_args());
	}
	
	//公共方法: 用户用强制性变量赋值;
	public function assign($phpnew_var, $phpnew_value = null){
		if(!$phpnew_var) return false;
		if($phpnew_var === true)
			return $this->templates_assign;
		$i = 0;
		if($phpnew_value === null && is_array($phpnew_var) === true){
			foreach ($phpnew_var as $php_key => $php_val){
				$this->templates_assign[$php_key] = $php_val;
				$i ++;
			}
		} else{
			$this->templates_assign[$phpnew_var] = $phpnew_value;
			$i++;
		}
		return $this->templates_assign;
	}
	
	public function set_templates_type($parema='变量模式[All,ASSIGN]'){
		if($parema !== true){
			$this->templates_var = $parema;
		}
		return $this->templates_var;
	}
	
	public function set_templates_suffix($parema='模板后缀', $paremb='缓存后缀'){
		if($parema !== true){
			$this->templates_postfix = $parema;
			$this->templates_caching = $paremb;
		}
		return array('templates_postfix'=>$this->templates_postfix,'templates_caching'=>$this->templates_caching);
	}
	
	public function set_templates_auto($parem='设置自动更新[bool]'){
		$this->templates_auto = $parem;
		return $this->templates_auto;
	}
	
	public function set_templates_space($parem='清除多余空白[bool]'){
		$this->templates_space = $parem;
		return $this->templates_space;
	}
	
	 public function set_templates_isdebug($parem='启用调试[bool]'){
		$this->templates_isdebug = $parem;
		return $this->templates_isdebug;
	}
	
	public function set_templates_oncenew($parem='当次更新[bool]'){
		$this->templates_new = $parem;
		return $this->templates_new;
	}
	
	public function set_templates_ankey($parem='安全码'){
		if($parem !== true)
		$this->templates_ankey = $parem;
		return $this->templates_ankey;
	}
	
	public function set_templates_path($path='模板路径'){
		if(!$path) return false;
		if($path === true)
			return $this->templates_dir;
		
		if(is_dir($path) === true){
			$this->templates_dir[$path] = $path;
		}else{
			$this->preg__debug('set_templates_path 模板目录不存在, 自动忽略:'.$path);
		}
		return $this->templates_dir;
	}
	
	public function set_templates_replace($phpnew_var='关键值,替换值', $phpnew_value = null){
		if($phpnew_var === true)
			return $this->templates_replace;
		
		$i = 0;
		if($phpnew_value === null && is_array($phpnew_var) === true){
			foreach ($phpnew_var as $php_key => $php_val){
				$this->templates_replace[$php_key] = $php_val;
				$i ++;
			}
		} else{
			$this->templates_replace[$phpnew_var] = $phpnew_value;
			$i ++;
		}

		return $this->templates_replace;
	}
	
	public function set_cache_path($dir='缓存目录路径'){
		if($dir !== true){
			$this->templates_cache = $dir;
		}
		return $this->templates_cache;
	}
	
	//公共方法: 定义静态变量, 主要用于css, js.
	public function set_static_assign($var1=null, $var2 = null){
		if(!$var1) return false;
		if($var1 === true)
			return $this->templates_static_assign;
		
		$i = 0;
		if($var2 === null && is_array($var1) === true){
			foreach($var1 AS $key => $var){
				$this->templates_static_assign[$key] = $var;
				$i ++;
			}
		}else{
			$this->templates_static_assign[$var1] = $var2;
			$i ++;
		}
		return $this->templates_static_assign;
	}
	
	//公共方法: 设置语言数组, 模板中就可以用{lang str}
	public function set_language($var1=null, $var2 = null){
		if(!$var1) return false;
		if($var1 === true)
			return $this->templates_lang;
		$i = 0;
		if($var2 === null && is_array($var1) === true){
			foreach($var1 AS $key => $var){
				$this->templates_lang[$key] = $var;
				$i ++;
			}
		}else{
			$this->templates_lang[$var1] = $var2;
			$i ++;
		}
		return $this->templates_lang;
	}
	
	//公共方法: 设置自动匹配的路径, 默认先不工作, 等有此语法再读取目录.
	public function set_auto_path($set_path = '自动搜索目录路径'){
		static $path = array();
		if($set_path !== true && strpos($set_path, '/') !== false){
			$set_path = rtrim($set_path,'\\/').'/';
			if(is_dir($set_path) === true){
				$path[] = $set_path;
			}else{
				$this->preg__debug("set_auto_path 设置自动搜索目录失败 , {$set_path} 目录不存在!", true);
			}
			$i = count($path);
		}
		return $path;
	}
	
	//私有方法: 当语法有自动匹配功能时, 此方法会被调用. 
	private function __real_alldir($dir=null){
		if(!$dir)
			return array();
		$this->templates_autofile[] = $dir;
		$fplist = @glob($dir.'*', GLOB_ONLYDIR);
		if($fplist){
			foreach($fplist AS $val){
			   $dir = rtrim($val,'/').'/';
			   $this->__real_alldir($dir);
			}
		}
	}
	
	// 内部方法: 检查是否应该更新, 参数:当前配置数组.
	private function __check_update($html_array){
		if(is_dir($this->templates_cache) === false)
			$this->preg__debug('缓存目录不存在: '. $this->templates_cache, E_WARNING, 1);	
		if(empty($html_array['tpl']) === true)
			$this->preg__debug('模板文件不存在: '. $this->templates_name, E_WARNING, 1);
		if($this->templates_new === true){
			$this->preg__debug('templates_new 自动更新已经开启!');
			return false;
		}
		
		if(!$html_array['cache'] || is_file($html_array['cache']) === false){
			$this->preg__debug(var_export($html_array['cache'], true).'缓存文件不存在, 解析更新已开启!');
			return false;
		}
		return true;
	}
	
	// 内部方法: 取得路径信息.
	private function __get_path($htmlfile){
		$rename = false;
		if(stripos($htmlfile,'/') === false){
			$rename = $this->__search_tpl($htmlfile);
		}else{
			if(is_file($htmlfile) === false){
				if(strpos($htmlfile, $this->templates_postfix.'}') !== false){
					$htmlfile = strtr($htmlfile, array($this->templates_postfix=>''));
					$htmlfile = ltrim($htmlfile, '/').$this->templates_postfix;
				}else{
					$htmlfile = ltrim($htmlfile, '/');
				}
				$htmlfile = strtr($htmlfile, array('\\'=>'/','\\\\'=>'/','//'=>'/'));
				$rename = $this->__search_tpl($htmlfile);
			}
		}
		
		if($rename){
			$this->preg__debug('模板文件自动搜索到路径: '. $rename);
		}else{
			$this->preg__debug('模板文件搜索不到路径: '. $htmlfile, E_WARNING);
		}
		
		$htmlfile = $rename;
		$retruans = array();
		if($htmlfile !== false){
			$md5 = $this->templates_auto === true ? md5_file($htmlfile) : md5($htmlfile.$this->templates_ankey.getglobal('language'));
			//$md5 = md5($htmlfile.$this->templates_ankey.getglobal('language'));
			//$md5 = $this->templates_auto === true && file_exists($this->templates_cache.$md5.'_tpl'.$this->templates_caching) && filemtime($this->templates_cache.$md5.'_tpl'.$this->templates_caching)<filemtime($htmlfile) ? md5($htmlfile.microtime().random(3)) : $md5;
			$retruans = array('tpl'=>$htmlfile, 'cache'=>$this->templates_cache . $md5 . '_tpl' . $this->templates_caching);
		}
		self::templates_mapping($retruans['tpl'], $retruans['cache']);
		return $retruans;
	}
	
	private function __search_tpl($htmlfile){
		static $autodir = array();
		if($this->templates_dir){
			$dir = $this->templates_dir;
		}else{
			if(!$autodir)
			$autodir = $this->set_auto_path(true);
			$dir = $autodir;
		}
		
		if($dir)
		$dir = array_reverse($dir);
		
		$paths = false;
		foreach($dir AS $val){
			if($val){
				$val = strtr($val, array('\\'=>'/','\\\\'=>'/','//'=>'/'));
				if(is_file($val.$htmlfile) === true){
					$paths = $val.$htmlfile;
					break;
				}
			}
		}
		return $paths;
	}
	
	// 内部方法: 取得全局变量并且赋予模板.
	private function __parse_var(){
		static $savevar = 0;
		if($savevar === 0 && $this->templates_var !== 'ASSIGN'){
			$allvar = array_diff_key($GLOBALS, array('GLOBALS'=>0,'_ENV'=>0,'HTTP_ENV_VARS'=>0,'ALLUSERSPROFILE'=>0,'CommonProgramFiles'=>0,'COMPUTERNAME'=>0,'ComSpec'=>0,'FP_NO_HOST_CHECK'=>0,'NUMBER_OF_PROCESSORS'=>0,'OS'=>0,'Path'=>0,'PATHEXT'=>0,'PROCESSOR_ARCHITECTURE'=>0,'PROCESSOR_IDENTIFIER'=>0,'PROCESSOR_LEVEL'=>0,'PROCESSOR_REVISION'=>0,'ProgramFiles'=>0,'SystemDrive'=>0,'SystemRoot'=>0,'TEMP'=>0,'TMP'=>0,'USERPROFILE'=>0,'VBOX_INSTALL_PATH'=>0,'windir'=>0,'AP_PARENT_PID'=>0,'uchome_loginuser'=>0,'supe_cookietime'=>0,'supe_auth'=>0,'Mwp6_lastvisit'=>0,'Mwp6_home_readfeed'=>0,'Mwp6_smile'=>0,'Mwp6_onlineindex'=>0,'Mwp6_sid'=>0,'Mwp6_lastact'=>0,'PHPSESSID'=>0,'HTTP_ACCEPT'=>0,'HTTP_REFERER'=>0,'HTTP_ACCEPT_LANGUAGE'=>0,'HTTP_USER_AGENT'=>0,'HTTP_ACCEPT_ENCODING'=>0,'HTTP_HOST'=>0,'HTTP_CONNECTION'=>0,'HTTP_COOKIE'=>0,'PATH'=>0,'COMSPEC'=>0,'WINDIR'=>0,'SERVER_SIGNATURE'=>0,'SERVER_SOFTWARE'=>0,'SERVER_NAME'=>0,'SERVER_ADDR'=>0,'SERVER_PORT'=>0,'REMOTE_ADDR'=>0,'DOCUMENT_ROOT'=>0,'SERVER_ADMIN'=>0,'SCRIPT_FILENAME'=>0,'REMOTE_PORT'=>0,'GATEWAY_INTERFACE'=>0,'SERVER_PROTOCOL'=>0,'REQUEST_METHOD'=>0,'QUERY_STRING'=>0,'REQUEST_URI'=>0,'SCRIPT_NAME'=>0,'PHP_SELF'=>0,'REQUEST_TIME'=>0,'argv'=>0,'argc'=>0,'_POST'=>0,'HTTP_POST_VARS'=>0,'_GET'=>0,'HTTP_GET_VARS'=>0,'_COOKIE'=>0,'HTTP_COOKIE_VARS'=>0,'_SERVER'=>0,'HTTP_SERVER_VARS'=>0,'_FILES'=>0,'HTTP_POST_FILES'=>0,'_REQUEST'=>0));
			foreach($allvar as $key => $val){
				$this->templates_assign[$key] = $val;
			}
			$savevar = 1;
			unset($allvar);
		}
	}
 
	// 内部方法: 读文件与写文件的公用方法.
	private function preg__file($path, $lock='rb' ,$cls = false){
		$mode = $cls === true?'wb':$lock;
		if($cls === false && is_file($path) === false) return false;
		if(!$fp = fopen($path, $mode))
		if(!$fp = fopen($path, $mode))
		if(!$fp = fopen($path, $mode))
		if(!$fp = fopen($path, $mode))
		if(!$fp = fopen($path, $mode))
			return false;
		
		if($cls === true){
			flock($fp, LOCK_EX | LOCK_NB);
			if(!$ints = fwrite($fp, $lock))
			if(!$ints = fwrite($fp, $lock))
			if(!$ints = fwrite($fp, $lock))
			if(!$ints = fwrite($fp, $lock))
			if(!$ints = fwrite($fp, $lock))
				return 0;
			$this->preg__debug('文件写入成功: '.$path);
			$this->templates_writecount ++;
			flock($fp, LOCK_UN);
			fclose($fp);
			return $ints;
		}else{
			$data = '';
			flock($fp, LOCK_SH | LOCK_NB);
				while(!feof($fp)){
					$data .= fread($fp, 4096);
				}
			flock($fp, LOCK_UN);
			fclose($fp);
			return $data;
		}
	}
	
	// 内部方法: css,js静态文件解析方法.
	private function __preg_source_parse($template){
		/*if(strexists($template, '0Parse.')){
			$parse = false;
			$template = str_replace('0Parse.', '', $template);
		}*/
		if(!$template || is_file($template) === false)
			return $template;
		$this->cssname = $template;
		$static_file = $template;

		$tem = explode('.', $static_file);
		$caename_file = $this->templates_cache.'static_'.md5(realpath($static_file).getglobal('language')).'.'.end($tem);

		if(!file_exists($caename_file) || $this->templates_new || (getglobal('config/output/tplrefresh') && filemtime($caename_file) > filemtime($static_file))){
			$extension = pathinfo($template);
			$extension = $extension['extension'];
			$parse = in_array($extension, array('css', 'js', 'htm', 'html', 'tpl'))&&!strexists($template, 'min.js') ? true : false;//strlen($template)>2 && substr($template, 0, 2)!='/*'

			$template = $this->preg__file($static_file);

			if($parse){
				# 增加todo bug标注支持.
				$template = preg_replace_callback("/([\s]*?)\/\/\s+(?:TODO|BUG):(.+?)([;\n\r\t]+?)/i",array(&$this, 'preg__todobug'),$template);

				//替换直接变量输出
				$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", '{$1}', $template);
				$varRegexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
				$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
				$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", '<?=$1?>', $template);
				//$template = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->langvar('\\1', 1)", $template);
				$template = preg_replace_callback("/\{lang\s+(.+?)\}/is", array(&$this, 'lang_static'), $template);
				$template = preg_replace_callback("/\{__([^\s]+?\.[^\s]+?)\}/", array(&$this, 'preg__autofile_css'), $template, -1, $regint);
				$template = preg_replace_callback("/url\((?:['\"]*)([^\(\)\"']+)(?:['\"]*)\)/", array(&$this, 'preg__css_url'), $template);
				$template = preg_replace_callback("/$varRegexp/is", array(&$this,'preg__var'), $template);
				$template = preg_replace_callback("/\<\?\=\<\?\=$varRegexp\?\>\?\>/is",array(&$this,'preg__var'), $template);
				$template = preg_replace_callback("/<\?\=$varRegexp\?\>/is",array(&$this,'preg_cssjs_var'), $template);
				$template = preg_replace_callback("/\{$const_regexp\}/s", array(&$this,'preg_cssjs_var'), $template);
				$template = preg_replace_callback("/\{__([^\s]*?\.[^\s]*?)\}/s", array(&$this, 'preg_static_autofile'), $template);
			}

			$this->preg__file($caename_file, $template, true);
		}

		self::templates_mapping($static_file, $caename_file);

		$caename_file = str_replace(APP_FRAMEWORK_ROOT.'/', '', $caename_file);
		return $caename_file;
	}
	
	// 内部方法: css,js静态文件路径计算方法, 跟preg__autofile有小小区别.
	private function preg_static_autofile($math){
		static $reals = '';
		$file = call_user_func_array(array($this,'preg__autofile'), func_get_args());
		if(!$reals){
			# 计算回调多少层.
			$tem = explode('/', rtrim($this->templates_cache, '/'));
			foreach($tem AS $key => $val){
				if($val !== '.' && $val){
					if($key !== 0){
						$tem[$key] = '..';
					}else{
						if($val !== '..')
						$tem[$key] = '.';
					}
				}else{
					if(!$val)
						unset($tem[$key]);
				}
			}
			$reals = implode('/', $tem).'/';
		}
		
		if(is_file($file) === true){
			if(strpos($this->cssname, '.css') !== false){
				return $reals.ltrim($file,'./');
			}else{
				return $file;
			}
		}
			
	}
	 // 内部方法: css,js静态文件变量计算方法.
   	private function preg_cssjs_var($math){
		if(is_string($math) === false)
			$math = $math[1];
		if($math && strpos($math,'$') !== false){
			$math = strtr($math, array('"'=>'',"'"=>''));
			# 直接返回变量的值.
			$math = strtr(ltrim($math,'$'),array(']['=>'.'));
			$math = strtr(ltrim($math,'$'),array(']'=>'','['=>'.'));
			
			$tem = explode('.',$math);
			if(!isset($this->templates_css_assign)){
				$this->__parse_var();
				$this->templates_css_assign = $this->templates_assign;
			}
			
			$travar = $this->templates_css_assign;
			foreach($tem AS $val){
				$travar = $travar[$val];
			}
			return $travar;
		}else{
			#常量替换
			$tem = get_defined_constants(true);
			$tem = $tem['user'];
			return $tem[$math];
		}
	}
	// 内部方法: css文件引用规范方法.
	private function preg__css($math){
		if(!$math[1])
			return false;
		if(strpos($math[0],'link') !== false){
			if(strpos($math[0],'/php') !== false){
				$css_file_path = $this->__preg_source_parse($math[1]);
				$math[0] = preg_replace('/ href="[^"]*"/is','', $math[0]);
				$math[0] =preg_replace('/ type="[^"]*"/is','', $math[0]);
				$math[0] = strtr($math[0], array('<link'=>"<link type=\"text/css\" href=\"{$css_file_path}\""));
				$this->preg__debug('CSS 自动匹配: '.$css_file_path);
			}
			return $math[0];
		}else{
			$css_file_path = $this->__preg_source_parse($math[1]);
			$this->preg__debug('CSS 自动匹配: '.$css_file_path);
			return '<link rel="stylesheet" type="text/css" href="'.$css_file_path.'" />';
		}
	}
	// 内部方法: js文件引用规范方法.
	private function preg__js($math){
		if(strpos($math[0],'src') !== false){
			if(strpos($math[0],'/php') !== false){
				$js_file_path = $this->__preg_source_parse(trim($math[1]));
				$math[0] = preg_replace('/ src="[^"]*"/is'," src=\"$js_file_path\"", $math[0]);
				$this->preg__debug('JS 自动匹配: '.$js_file_path);
			}
			return $math[0];
		}else{
			$js_file_path = $this->__preg_source_parse(trim($math[1]));
			$this->preg__debug('JS 自动匹配: '.$js_file_path);
			return '<script type="text/javascript" src="'.$js_file_path.'"></script>';
		}
	}
	
	// 内部方法: html代码自动匹配路径方法
	private function preg__autofile($math){
		static $allpath = array();
		//$parse = true;
		/*if(is_string($math) === false){
			$mathfile = $math[1];
			/*if(strexists($math[1], '0Parse.')){
				$mathfile = str_replace('0Parse.', '', $math[1]);
				$parse = false;
			}else{
				$mathfile = $math[1];
			}* /
		}else{
			$mathfile = $math;
			/*if(strexists($math, '0Parse.')){
				$mathfile = str_replace('0Parse.', '', $math);
				$parse = false;
			}else{
				$mathfile = $math;
			}* /
		}*/
		$mathfile = is_string($math) ? $math : $math[1];
		// 带变量的?
		if(strpos($mathfile, '$') !== false || substr_count($mathfile,'{') >0){
			//替换直接变量输出
			$template = $mathfile;
			unset($mathfile);
			$template = $this->__parse_htmlvar($template);
			if(strpos($template, '<?=') !== false)
				$template = strtr($template,array('<?='=>'{','?>'=>'}'));			
			$returns = $this->preg__base('<?php echo $this->preg__autofile('."\"$template\"".');?>');
		}else{
			if(!$allpath){
				$allpath = $this->set_auto_path(true);
				//$dirlist = array();
				foreach($allpath AS $val){
					$this->__real_alldir($val);
				}
				$allpath = array_unique($this->templates_autofile);
				if($this->templates_depth >= 0){
					;
				}
				foreach($allpath as $key => $value){
					//trace(str_replace(APP_FRAMEWORK_ROOT, '', $value));
					foreach($this->templates_direxception as $except){
						if(strexists($value, "/{$except}/"))
							unset($allpath[$key]);
					}
				}
				if($allpath) $allpath = array_reverse($allpath);
			}
			
			foreach($allpath AS $val){
				if(is_file($val.$mathfile) === true){
					$returns = $val.$mathfile;
					break;
				}
			}
			
			if(!isset($returns))
				$returns = $mathfile;
		}
		$returns = str_replace(APP_FRAMEWORK_ROOT.'/', '', $returns);
		return $returns;
		//return ($parse ? '' : '0Parse.').$returns;
	}

    private function preg__static_link($math){
        $math = is_string($math) ? $math : $math[1];
        if(strexists($math, 'http:') || strexists($math, 'https:') || strexists($math, '//') || substr($math, 0, 1) == '/') return $math;
        if(strexists($math, '$') || substr_count($math, '{') > 0){
            $math = $this->__parse_htmlvar($math);
            if(strexists($math, '<?='))
                $math = strtr($math, array('<?=' => '{', '?>' => '}'));           
            $ret = $this->preg__base('<?php echo $this->preg__static_link("'.$math.'");?>');
        }else{
            $ret = self::set_auto_path(true);
            $ret = $ret[0];
            $ret = file_exists($ret.$math) ? $ret.$math : self::preg__autofile($math);
        }
        $ret = str_replace(APP_FRAMEWORK_ROOT.'/', '', $ret);
        ;;;;;;;;;;
        return $ret;
    }

    private function preg__csslink($math){
        $math = is_string($math) ? $math : $math[1];
        $math = strexists($math, '.css') ? $math : $math.'.css';
        $math = self::__preg_source_parse(self::preg__static_link($math));
        return '<link rel="stylesheet" type="text/css" href="'.$math.'" />';
    }

    private function preg__jslink($math){
        $math = is_string($math) ? $math : $math[1];
        $math = strexists($math, '.js') ? $math : $math.'.js';
        $math = self::__preg_source_parse(self::preg__static_link($math));
        return '<script type="text/javascript" src="'.$math.'"></script>';
    }

	private function preg__autofile_css($math){
		$ret = $this->preg__autofile($math);
		$ret = $this->__preg_source_parse($ret);
		$tmp = str_replace(APP_FRAMEWORK_ROOT.'/', '', $this->templates_cache);
		$ret = str_replace($tmp, '', $ret);
		return $ret;
		//if(in_array(pathinfo($template)['extension'], array('css', 'js'))) $ret = $this->__preg_source_parse($ret);
		//return '../../'.$ret;
	}

	private function preg__css_url($math){
		if(strexists($math[1], 'http:') || strexists($math[1], 'https:') || strexists($math[1], '//') || substr($math[1], 0, 1) == '/') return "url('{$math[1]}')";
        //$ret = basename($math[1]);
		$ret = parse_url($math[1]);
		if(!isset($ret['path'])){
			$symbol = array('#', '&', '?');
			foreach($symbol as $str) {
				$tmp = strpos($math[1], $str);
				if($tmp !== false) $math[1] = substr($math[1], 0, $tmp);
			}
			$ret = parse_url($math[1]);
		}
		$ret = $ret['path'];
		if(empty($this->templates_autofile)){
			$allpath = $this->set_auto_path(true);
			$dirlist = array();
			foreach($allpath AS $val){
				$this->__real_alldir($val);
			}
		}
		foreach($this->templates_autofile AS $val){
			if(is_file($val.$ret)){
				/*$ret = realpath($val.$ret);
				$ret = str_replace(array(APP_FRAMEWORK_ROOT, '\\'), array('', '/'), $ret);
				$ret = substr($ret, 1);*/
				$ret = $this->__preg_source_parse($val.$ret);
				$tmp = str_replace(APP_FRAMEWORK_ROOT.'/', '', $this->templates_cache);
				$ret = str_replace($tmp, '', $ret);
				break;
			}
		}
		//trace($this->templates_autofile);
		return "url('{$ret}')";
	}
	
	// 处理变量与常量.
	private function __parse_htmlvar($template){
		$varRegexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
		$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", '<?=$1?>', $template);
		$template = preg_replace_callback("/$varRegexp/is", array(&$this,'preg__var'), $template);
		$template = preg_replace_callback("/\<\?\=\<\?\=$varRegexp\?\>\?\>/is",array(&$this,'preg__var'), $template);
		$template = preg_replace("/\{$const_regexp\}/sU", "<?=$1?>", $template);
		return $template;
	}
	
	private function preg__binary($math){
		if($math)
			$math = explode('|',$math[1]);
		
		$var1 = $this->__parse_htmlvar($math[1]);
		$var2 = $this->__parse_htmlvar($math[2]);
		$var = trim($math[0], '<?=>');
		$var1 = trim($var1, '<?=>');
		$var2 = trim($var2, '<?=>');
		
		if($var1=="''" || $var1=='""'){
			$var1 = '';
		}
		if($var2=="''" || $var2=='""'){
			$var2 = '';
		}
		
		if(isset($math[2]) === false){
			$math[1] = $var;
			$var2 = $var1;
			$var1 = ltrim($var,'!');
		}
		
		if($var1 != '' && strpos($var1, '$') !== 0){
			$var1 = "'{$var1}'";
		}else{
			if(!$var1)
				$var1 = "'{$var1}'";
		}
		
		 if($var2 != '' && strpos($var2, '$') !== 0){
			$var2 = "'{$var2}'";
		 }else{
			if(!$var2)
				$var2 = "'{$var2}'";
		 }
		 return $this->preg__base("<?php echo $var?{$var1}:{$var2};?>");
	}
	
	// TODO: 核心代码开始
	//内部函数: 模板语法处理替换
	private function __parse_html($template){
		if(empty($template) === true)
			return $template;
		$this->preg__debug('模板解析开始... 内容共计: '. strlen($template).' 字节');
		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", '{$1}', $template);
		$template = preg_replace_callback("/\{html\s+(.+?)\}/i",array(&$this,'preg__static'), $template);
		
		$template = str_ireplace(array('{loads','{load'),array('{templatesub','{template'),$template);
		
		$template = preg_replace_callback("/([\s]*?)\/\/\s+(?:TODO|BUG):(.+?)([;\n\r\t]+?)/i", array(&$this, 'preg__todobug'), $template, -1, $regint);
		$this->preg__debug('解析模板细节: // TODO|BUG TODO,BUG 描述解析次数: '.($regint));
		
		$template = preg_replace_callback("/\{templatesub\s+([^\s]+?)\}[\n\r\t]*/is", array(&$this,'preg__contents'), $template, -1, $regints);
		$template = preg_replace_callback("/\{template\s+([^\s]+?)\}([\n\r\t]*)/is", array(&$this,'preg__template'), $template, -1, $regint);
		$this->preg__debug('解析模板细节: {load name} 解析次数: '.($regint+$regints));
		$this->templates_blockreplace = array();
		$template = preg_replace_callback("/\{block\s+([^\s]*)\}(.*?)\{\/block\}([\n\r\t]*)/is", array(&$this, 'preg__stripblock'), $template,-1,$regint);
		$this->preg__debug('解析模板细节: {block name} block块解析次数: '.($regint));
		if($regint){
		  $ri = 0;
		  foreach($this->templates_blockreplace AS $keys => $vals){
			 $r2 = strtr($keys , array('{'=>'{block '));
			 if(strpos($template, $r2) !== false){
				$ri ++;
				 $template = strtr($template, array($r2=>$vals));
			 }else if(strpos($template, $keys) !== false){
				$ri ++;
				$template = strtr($template, array($keys=>$vals));
			 }
		  }
		  $this->preg__debug('解析模板细节: block 注入块替换次数: '.($ri));
		}
		
		//处理自动搜索文件路径
		$template = preg_replace_callback("/\{__([^\s]+?\.[^\s]+?)\}/", array(&$this, 'preg__autofile'), $template, -1, $regint);
		$this->preg__debug('解析模板细节: {__name} 自动匹配路径解析次数: '.($regint));

        $template = preg_replace_callback("/\{js\s+(.+?)\}/is", array(&$this, 'preg__jslink'), $template);
        $template = preg_replace_callback("/\{css\s+(.+?)\}/is", array(&$this, 'preg__csslink'), $template);
		
		// 处理掉所有的路径问题.
		$template = preg_replace_callback("/\<link[^>]*?href=\"([^\s]*)\".*?\/\>/i",array(&$this,'preg__css'), $template,-1,$regint);
		$template = preg_replace_callback("/\<style[^>]*?\>([^\s]+?\.css)\<\/style\>/i",array(&$this,'preg__css'), $template,-1,$regints);
		$this->preg__debug('解析模板细节: <link><style> CSS路径自动匹配路径解析次数: '.($regint+$regints));
		$template = preg_replace_callback("/\<script[^>]*?src=\"([^\s]*)\".*?\>\<\/script\>/i",array(&$this,'preg__js'), $template,-1,$regint);
		$template = preg_replace_callback("/\<script[^>]*?\>([^\s]*\.js)\<\/script\>/i",array(&$this,'preg__js'), $template,-1,$regints);
		$this->preg__debug('解析模板细节: <script> JS路径自动匹配路径解析次数: '.($regint+$regints));
		
		//替换语言包/静态变量/php代码.
		$template = preg_replace_callback("/\{eval\s+(.+?)\}([\n\r\t]*)/is",array(&$this,'preg__evaltags'), $template, -1, $regint);
		$this->preg__debug('解析模板细节: {eval phpcode} eval运行php代码解析次数: '.($regint));
		$template = preg_replace_callback("/\<\?php\s+(.+?)\?\>/is", array(&$this,'preg__base'), $template, -1, $regint);
		$this->preg__debug('解析模板细节: <?php code ?> 原生态php代码解析次数: '.($regint));
		//$template = preg_replace_callback("/\{lang\s+(.+?)\}/is", array(&$this,'preg__language'), $template,-1,$regint);
		$template = preg_replace_callback("/\{lang\s+(.+?)\}/is", array(&$this,'lang_html'), $template, -1, $regint);
		//$template = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->langvar('\\1')", $template);
		$this->preg__debug('解析模板细节: {lang name} 语言包代码解析次数: '.($regint));
		$template = str_replace("{LF}", '<?="\\n"?>', $template);
		
		// 二元判断
		$template = preg_replace_callback("/\{([\!]*\\$[^\n]*\|[^\n]*)\}/iU",array(&$this,'preg__binary'), $template, -1, $regint);
		$this->preg__debug('解析模板细节: {reg|1|0} 二元判断代码解析次数: '.($regint));
		
		// 普通变量数组转化.
		$varRegexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
		$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", '<?=$1?>', $template);
		$template = preg_replace_callback("/$varRegexp/is", array(&$this,'preg__var'), $template);
		$template = preg_replace_callback("/\<\?\=\<\?\=$varRegexp\?\>\?\>/is",array(&$this,'preg__var'), $template,-1,$regint);
		$this->preg__debug('解析模板细节: {$var} 变量,数组代码解析次数: '.($regint));
		
		//替换特定函数
		$template = preg_replace_callback("/\{if\s+(.+?)\}/is",array(&$this,'preg__if'), $template);
		$template = preg_replace_callback("/\{else[ ]*if\s+(.+?)\}/is",array(&$this,'preg__ifelse'), $template);
		$template = preg_replace("/\{else\}/is", "<? } else { ?>", $template);
		$template = preg_replace("/\{\/if\}/is", "<? } ?>", $template,-1,$regint);
		$template = preg_replace_callback("/\{loop\s+(\S+)\s+(\S+)\}/is", array(&$this,'preg__loopone'), $template,-1,$reginta);
		$template = preg_replace_callback("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/is",array(&$this,'preg__looptwo'), $template,-1,$regintb);
		$template = preg_replace("/\{\/loop\}/is", "<? }} ?>", $template);
		$this->preg__debug('解析模板细节: {if else /if} if流程判断代码解析次数: '.($regint));
		$this->preg__debug('解析模板细节: {loop all} 循环输出代码解析次数: '.($reginta+$regintb));
		
		// 常量替换
		$template = preg_replace("/\{$const_regexp\}/U", "<?=$1?>", $template,-1,$regint);
		$this->preg__debug('解析模板细节: {CONST} 常量代码解析次数: '.($regint));
		
		//其他替换
		$template = preg_replace_callback("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/is", array(&$this, 'preg__transamp'), $template);
		$template = preg_replace_callback("/\<script[^\>]*?src=\"(.+?)\".*?\>\s*\<\/script\>/is",array(&$this, 'preg__stripscriptamp'), $template);
		
		if($this->templates_space === true){
			$template = preg_replace(array('/\r\n/isU', '/<<<EOF/isU'), array('', "\r\n<<<EOF\r\n"), $template);
		}
		
		$template = strtr($template, array('<style>' => '<style type="text/css">', '<script>' => '<script type="text/javascript">'));
		
		if($this->templates_viewcount === 1){
			$template = '<?php if(!$this || is_object($this) === false){exit(\'Hacking!\');} extract($this->templates_assign);?>'.$template;
		}else{
			$template = '<?php if(!$this || is_object($this) === false){exit(\'Hacking!\');} ?>'.$template;
		}
		
		$template = strtr($template, array('<?php' => '<?', '<?php echo' => '<?='));
		$template = strtr($template, array('<?' => '<?php', '<?=' => '<?php echo '));
		
		# input 修复兼容
		if(stripos($template, '<input') !== false){
		   $template = preg_replace_callback('/<input.*type="([^"]*)".*\/>/isU',array(&$this,'preg__input'), $template,-1, $regint);
		   $this->preg__debug('解析模板细节: <input> 标签注入默认class次数: '.$regint);
		}
		
		# 最终再释放所有的php代码.
		$template = preg_replace_callback('/\[base\](.*)\[\/base\]/isU',array(&$this, 'preg__debase'), $template);
		
		if($this->templates_replace){
		  $template = strtr($template, $this->templates_replace);
		  $this->preg__debug('解析模板细节: templates_replace 全局替换数据次数: '.count($this->templates_replace));
		}
		
		$this->preg__debug('模板解析结束... 内容共计: '.strlen($template).' 字节');
		return $template;
	}
	
	protected function preg__static($math){
		if(is_string($math) === false)
		   $math = $math[1];
		if($math){
			$this->__parse_var();
			$varname = ltrim(trim($math),'$');
			$varname = $this->templates_assign[$varname];
			if(!$varname)
				$varname = $math[0];
			
			return $varname;
		}
	}
	
	protected function preg__evaltags($math) {
		$php = rtrim(trim($math[1]),';');
		$lf  = $math[2];
		$php = str_replace('\"', '"', $php);
		return $this->preg__base("<?php $php; ?>$lf");
	}
	
	protected function preg__todobug($math){
		if(strpos($math[1],"\n")!== false && strpos($math[3],"\n")!== false){
			return "\n";
		}
		return ''; //默认todo, bug全部隐藏.
	}
	protected function preg__if($math){
		$expr = "<? if({$math[1]}){ ?>";
		return $this->preg__stripvtags($expr);
	}
	protected function preg__ifelse($math){
		$expr = "<? }else if({$math[1]}){ ?>";
		return $this->preg__stripvtags($expr);
	}
	protected function preg__loopone($math){
		$expr = "<? if(is_array({$math[1]})===true){foreach({$math[1]} AS {$math[2]}){ ?>";
		return $this->preg__stripvtags($expr);
	}
	protected function preg__looptwo($math){
		$expr = "<? if(is_array({$math[1]})===true){foreach({$math[1]} AS {$math[2]} => {$math[3]}){ ?>";
		return $this->preg__stripvtags($expr);
	}
	protected function preg__template($math){
		$lf = $math[2];
		if(is_string($math) === false)
			$math = trim($math[1]);
		if($math){
			if(strpos($math,'$') !== false){
				$math = $this->__parse_htmlvar($math);
				$math = strtr($math, array('<?='=>'','?>'=>''));
				$retunrstr = '<?php ($phpnewtpl = $this->display('.$math.')) && include($phpnewtpl);?>'.$lf;
			}else{
				$retunrstr = '<?php ($phpnewtpl = $this->display(\''.$math.'\')) && include($phpnewtpl);?>'.$lf;
			}
			$this->preg__debug('解析模板细节: 引入文件: '. $math);
			return $this->preg__base($retunrstr);
		}else{
			$this->preg__debug('解析模板细节: 无法解析的引入: '. var_export($math[0], true));
		}
		return false;
	}

	protected function preg__language($math){
		if(is_string($math) === false){
		   $math = $math[1];
		   return $this->preg__base("<?php echo \$this->preg__language('$math'); ?>");
		}else{
			$varname = ltrim($math, '$');
			$returnstr = $varname;
			
			if($this->templates_lang[$varname])
				$returnstr = $this->templates_lang[$varname];
			return $returnstr;
		}
	}

	protected function langvar($math, $s = false){
		if(!strexists($math, '/')) $math = $math.'/';
		@list($type, $var) = explode('/', $math);
		if(empty($var)){
			$var = $type;
			$type = 'template';
		}
		return lang($type, $var, null, null, true);
	}

	protected function lang_html($math){
		return self::langvar($math[1], false);
	}

	protected function lang_static($math){
		return self::langvar($math[1], true);
	}

	protected function preg__var($math){
		if(is_string($math) === false)
			$math = $math[1];
		if($math){
			$varname = "<?={$math}?>";
			$returnstr = str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $varname));
			return $returnstr;
		}
	}
	
	protected function preg__base($math){
		if(is_string($math) === false)
		   $math = $math[0];
		if($math){
			$returnstr = '[base]'.base64_encode($math).'[/base]';
			return $returnstr;
		}
	}
	protected function preg__debase($math){
		if(is_string($math) === false)
		   $math = $math[1];
		if($math){
			$returnstr = base64_decode($math);
			return $returnstr;
		}
	}
	protected function preg__stripvtags($math){
		if(is_string($math) === false)
		   $math = $math[1];
		if($math){
			$returnstr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $math));
			return $returnstr;
		}
	}
	
	protected function preg__input($math){
		$inputvar = trim($math[0]);
		$type = trim($math[1]);
		if(stripos($inputvar, 'id=') === false){
			if(stripos($inputvar, 'class=') !== false){
			   $inputvar = preg_replace('/class="([^"]*)"/isU','class="$1 input'.$type.'"', $inputvar);
			}else{
				$inputvar = strtr($inputvar, array('type='=>"class=\"input{$type}\" type="));
			}
		}
		return $inputvar;
	}
	
	protected function preg__contents($math){
		static $savearray = array();
		$filename = trim($math[1]);
		if($savearray[$filename] >= 2){
			return '';
		}
		
		strpos($filename,'.') === false && $filename .= $this->templates_postfix;
		$html_array = $this->__get_path($filename);
		if(empty($html_array['tpl']) === false){
			$filedata = $this->preg__file($html_array['tpl']);
			$filedata = str_ireplace(array('{loads','{load'),array('{templatesub','{template'),$filedata);
			// 让叠加数据也兼容模板化处理.
			$filedata = preg_replace("/\<\!\-\-\{(.*?)\}\-\-\>/s", '{$1}', $filedata);
			if(stripos($filedata, '{templatesub') !== false){
			  $savearray[$filename] += 1;
			  $this->preg__debug('解析细节: 静态引入文件:'.$filedata);
			  $filedata = preg_replace_callback("/{templatesub\s+(.+?)\}/is", array($this,'preg__contents'),$filedata);
			}
			return $filedata;
		}
		
		return '';
	}

	protected function preg__transamp($math){
	   $s = trim($math[0]);
	   if($s){
			$s = str_replace('&', '&amp;', $s);
			$s = str_replace('&amp;amp;', '&amp;', $s);
			$s = str_replace('\"', '"', $s);
			return $s;
		}
	}

	protected function preg__stripscriptamp($math){
		$s = trim($math[1]);
		if($s){
		  $s = str_replace('&amp;', '&', $s);
		  return "<script src=\"$s\" type=\"text/javascript\"></script>";
		}
		return false;
	}
 
	protected function preg__stripblock($math){
		$var	= $math[1];
		$text   = trim($math[2]);
		if($var && $text)
			$this->templates_blockreplace["{{$var}}"] = $text;
		return '';
	}
	
	private function preg__debug($mess, $cls = E_NOTICE, $halt=false){
		if(($this->templates_isdebug || $cls === true) && $mess){
			//$mess = htmlspecialchars($mess);
			if($cls === true || in_array($cls, array('0',E_NOTICE)) === true){
				$cls = 'Notice';
			}else{
				$cls = 'Warn';
			}
			
			$this->templates_debug[][$cls] = $mess;
		}

		if($halt) halt($mess);
		return $this->templates_debug;
	}
	
	//公共方法: 删除模板缓存,假如不传入参数, 将默认删除缓存目录的所有文件.;
	public function cache_dele($path = null){
		if($path === null){
			$path = $this->templates_cache;
			$file_arr = scandir($path);
			foreach ($file_arr as $val){
				if($val === '.' || $val === '..'){
					continue;
				}
				if(is_dir($path . $val) === true)
					$this->cache_dele($path . $val . '/');
				if(is_file($path . $val) === true && $val !== 'index.html')
					unlink($path . $val);
			}
		}else{
			if(is_file($path) === true)
				unlink($path);
		}
	}

	public function templates_mapping($tpl = null, $cache = null){
		static $mappings = array();
		if($tpl == null) return $mappings;
		$mappings[basename($cache)] = basename($tpl);
	}
}