<?php

class Smarty_Internal_Compile_U extends Smarty_Internal_CompileBase{
	public $required_attributes = array('url');
	public $shorttag_order = array('url');
	public $option_flags = array('vars', 'suffix', 'redirect', 'domain');
	public $optional_attributes = array('_any', 'vars', 'suffix', 'redirect', 'domain');

	public function compile($args, $compiler=null, $parameter=null){
		if(is_array($args)){
			$attr = $this->getAttributes($compiler, $args);
		}
		$url = str_replace("'", '', is_string($args) ? $args : $attr['url']);
		$vars = empty($attr['vars']) ? '' : $attr['vars'];
		$suffix = isset($attr['suffix']) ? $attr['suffix'] : true;
		$redirect = isset($attr['redirect']) ? $attr['redirect'] : false;
		$domain = isset($attr['domain']) ? $attr['domain'] : false;
		return substr($url,0,1)=='$' ? "<?php echo Dispatcher::generate($url);?>" : Dispatcher::generate($url, $vars, $suffix, $redirect, $domain);
	}
}
