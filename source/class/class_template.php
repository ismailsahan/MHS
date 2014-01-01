<?php

/**
 * Smarty 引擎优化
 */

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

!class_exists('Smarty') && include libfile('vendor/Smarty/Smarty.class');

class template extends Smarty{
	public function __construct(){
		global $_G;
		parent::__construct();

		$this->addTemplateDir(APP_FRAMEWORK_ROOT.'/source/template/'.$_G['setting']['template'].'/');
		if($_G['setting']['template'] != 'metronic') $this->addTemplateDir(APP_FRAMEWORK_ROOT.'/source/template/metronic/');
		$this->setCompileDir(APP_FRAMEWORK_ROOT.'/cache/tpl');
		$this->setConfigDir(APP_FRAMEWORK_ROOT.'/cache/cfg');
		$this->setCacheDir(APP_FRAMEWORK_ROOT.'/cache');
		$this->caching = true;
		//$this->cache_lifetime = -1;
		//$this->debugging = APP_FRAMEWORK_DEBUG;
		$this->compile_check = APP_FRAMEWORK_DEBUG;
		$this->force_compile = APP_FRAMEWORK_DEBUG;

		$this->left_delimiter  = '{';
		$this->right_delimiter = '}';

		$this->assignByRef('_G', $_G);
		//$this->assign('_G', $_G);
		//$this->assign('sitename', $_G['setting']['sitename']);
		//$this->assign('logopath', $_G['setting']['logopath']);
		//$this->assign('basefilename', $_G['basefilename']);
		//$this->assign('copyright', $_G['setting']['copyright']);
		//$this->assignByRef('debuginfo', $_G['debug']);

		StaticEngine::$cache_lifetime = -1;
		StaticEngine::$compile_check = APP_FRAMEWORK_DEBUG;
		StaticEngine::$force_compile = APP_FRAMEWORK_DEBUG;
	}

	public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false){
		global $_G;
		return parent::fetch($template===null ? null : (strexists($template, '.html') ? $template : $template.'.html'), /*$cache_id===null ? $_G['language'] : */$cache_id, $compile_id===null ? $_G['language'] : $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
	}

	public function display($template = null, $cache_id = null, $compile_id = null, $parent = null){
		global $_G;
		return parent::fetch($template===null ? null : (strexists($template, '.html') ? $template : $template.'.html'), /*$cache_id===null ? $_G['language'] : */$cache_id, $compile_id===null ? $_G['language'] : $compile_id, $parent, true);
	}
}

class StaticEngine {
	protected $allpath = array();
	public static $force_compile;
	public static $compile_check;
	public static $cache_lifetime;
	public static $maps = array();

	public static function &instance() {
		static $object;
		if(empty($object)) {
			$object = new StaticEngine;
		}
		return $object;
	}

	public function staticfile($link, $static = false){
		global $_G;
		if(is_array($link)) $link = $link[1];
		if(strexists($link, 'http:') || strexists($link, 'https:') || substr($link, 0, 1) == '/') return $link;
		$source = file_exists($link) ? $link : $this->getpath($link);
		$ext = fileext($source);
		$cachename = md5($_G['language'].$source).'.'.$ext;
		$cache = APP_FRAMEWORK_ROOT.'/cache/'.$cachename;
		if(APP_FRAMEWORK_DEBUG) self::$maps[$cachename] = str_replace(APP_FRAMEWORK_ROOT, '.', $source);
		if(!file_exists($cache) || in_array($ext, array('js', 'css')) && (self::$force_compile || self::$compile_check && filemtime($cache)>filemtime($source) || self::$cache_lifetime!=-1 && filemtime($cache)>filemtime($source)+self::$cache_lifetime)){
			$this->compile($source, $cache);
		}
		return ($static ? '' : 'cache/').$cachename;//str_replace('\\', '/', str_replace(APP_FRAMEWORK_ROOT.'/', '', $cache));
	}

	private function compile($source, $cache){
		if(in_array(fileext($source), array('js', 'css'))/* && !strexists($source, DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR)*/){
			//$template = parent::fetch($link, $_G['language'], $_G['language']);
			$template = file_get_contents($source);
			//if(strexists($source, '.min.') || strexists($source, '-min.')){
			//	if(!copy($source, $cache)) throw new Exception('Cannot copy file to '.$cache);
			//}else{
				//$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", '{$1}', $template);
				//$varRegexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
				$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
				/*$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", '<?=$1?>', $template);*/
				$template = preg_replace_callback("/\{lang\s+(.+?)\}/is", array('Smarty_Internal_Compile_Lang', 'lang'), $template);
				$template = preg_replace_callback("/\{U\s+(.+?)\}/is", array('Dispatcher', 'generate'), $template);
				$template = preg_replace_callback("/\{static\s+(?:[\"']*)(.+?)(?:[\"']*)\}/is", array(&$this, 'staticfile'), $template);
				$template = preg_replace_callback("/url\((?:['\"]*)([^\(\)\"']+)(?:['\"]*)\)/", array(&$this, 'cssurl'), $template);
				//$template = preg_replace_callback("/$varRegexp/is", array(&$this,'preg__var'), $template);
				//$template = preg_replace_callback("/\<\?\=\<\?\=$varRegexp\?\>\?\>/is",array(&$this,'preg__var'), $template);
				//$template = preg_replace_callback("/<\?\=$varRegexp\?\>/is",array(&$this,'preg_cssjs_var'), $template);
				$template = preg_replace_callback("/\{(?:[\$]*)_G([a-zA-Z0-9\[\]'\"_\x7f-\xff]+)\}/is", array(&$this, 'preg_global'), $template);
				$template = preg_replace_callback("/\{$const_regexp\}/s", array(&$this,'preg_cssjs_var'), $template);

				if(file_put_contents($cache, $template) === false) exit('Cannot write cache '.$cache);
				//ob_start();
				//include_once $cache;
				//$template = ob_get_clean();
				//if(file_put_contents($cache, $template) === false) exit('Cannot write cache '.$cache);
			//}
			unset($template);
		}else{
			if(!copy($source, $cache)) throw new Exception('Cannot copy file to '.$cache);
		}
	}

	private function cssurl($math){
		//检测是否是网络URL或根URL
		if(strexists($math[1], 'http:') || strexists($math[1], 'https:') || substr($math[1], 0, 1) == '/') return "url('{$math[1]}')";

		//标准化路径
		$ret = parse_url($math[1]);
		if(!isset($ret['path'])){
			$symbol = array('#', '&', '?');
			foreach($symbol as $str) {
				$tmp = strpos($math[1], $str);
				if($tmp !== false) $math[1] = substr($math[1], 0, $tmp);
			}
			$ret = parse_url($math[1]);
		}
		$ret = $this->getpath($ret['path']);

		$ret = $this->staticfile($ret, true);

		return "url('{$ret}')";
	}

	private function getpath($link){
		if(empty($this->allpath)) $this->autopath($this->getTemplateDir());
		foreach($this->allpath as $path){
			$file = realpath($path.$link);
			if($file) return $file;
		}
		throw new Exception("Unable to find the file {$link}");
		return false;
	}

	private function autopath($dir = null){
		if(is_array($dir)){
			foreach($dir as $_path){
				$this->autopath($_path);
			}
		}elseif(is_string($dir) && $this->chkException($dir)){
			$this->allpath[] = $dir;
			$fplist = @glob($dir.'*', GLOB_ONLYDIR);
			if($fplist){
				foreach($fplist AS $val){
					$dir = rtrim($val,'/').'/';
					$this->autopath($dir);
				}
			}
		}
		return $this->allpath;
	}

	private function getTemplateDir(){
		global $_G;
		$tpl = array();
		$tpl[] = APP_FRAMEWORK_ROOT.'/source/template/'.$_G['setting']['template'].'/';
		if($_G['setting']['template'] != 'metronic') $tpl[] = APP_FRAMEWORK_ROOT.'/source/template/metronic/';
		return $tpl;
	}

	private function chkException($path){
		static $exception = array('test', 'document', 'documention', 'temp', 'demo');
		foreach($exception as $except){
			if(strexists($path, "/{$except}/"))
				return false;
		}
		return true;
	}

	private function preg__var($math){
		if(is_array($math))
			$math = $math[1];
		if($math){
			$math = str_replace('\\"', '"', preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $math));
			/*$ext = '';
			$pos = strpos($math, '[');
			if($pos !== false){
				$ext = substr($math, $pos);
			}
			$math = '$_smarty_tpl->tpl_vars[\''.substr($math, 1, $pos-1).'\']->value'.$ext;*/
			//$math = '<?php echo '.$math.';? >';
			$math = "{$math};";
			return $math;
		}
	}

	private function preg_cssjs_var($math){
		if(is_array($math))
			$math = $math[1];
		if($math && strpos($math,'$') !== false){
			return $this->preg__var($math);
		}else{
			//常量替换
			$tem = get_defined_constants(true);
			$tem = $tem['user'];
			return isset($tem[$math]) ? $tem[$math] : '';
		}
	}

	private function preg_global($math){
		$math = is_array($math) ? $math[1] : $math;
		$math = str_replace(array("'", '"', ']'), array('', '', ''), $math);
		$math = explode('[', $math);
		array_shift($math);
		$math = implode('/', $math);
		return getglobal($math);
	}
}