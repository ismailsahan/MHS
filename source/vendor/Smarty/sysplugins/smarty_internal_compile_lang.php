<?php

class Smarty_Internal_Compile_Lang extends Smarty_Internal_CompileBase{
	public $required_attributes = array('file');
	public $shorttag_order = array('file');
	public $option_flags = array('langvar', 'vars', 'default', 'raw');
	public $optional_attributes = array('_any');

	public function compile($args, $compiler=null, $parameter=null){
		if(is_array($args)){
			$_attr = $this->getAttributes($compiler, $args);
		}
		$file = str_replace("'", '', is_string($args) ? $args : $_attr['file']);
		return $this->lang($file);
	}
	
	public static function lang($file){
		$file = is_array($file) ? $file[1] : $file;
		if(!strexists($file, '/')) $file = $file.'/';
		@list($type, $var) = explode('/', $file);
		if(empty($var)){
			$var = $type;
			$type = 'template';
		}

		if(substr($var, 0, 1) == '$'){
			return "<?php echo lang('$type', $var, null, null, true);?>";
		}

		return lang($type, $var, null, null, true);
	}
}
