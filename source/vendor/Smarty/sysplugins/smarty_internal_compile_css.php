<?php

class Smarty_Internal_Compile_Css extends Smarty_Internal_CompileBase{
	public $required_attributes = array('link');
	public $shorttag_order = array('link');
	public $option_flags = array();
	public $optional_attributes = array();

	public function compile($args, $compiler, $parameter){
		$_attr = $this->getAttributes($compiler, $args);
		$link = str_replace("'", '', $_attr['link']);
		$media = '';
		$pos = strpos($link, '|');
		if($pos !== false){
			$media = substr($link, 0, $pos);
			$link = substr($link, $pos+1);
			$media = empty($media) ? 'screen' : $media;
		}
		$math = strexists($link, '.css') ? $link : $link.'.css';
		$math = StaticEngine::instance()->staticfile($math);
		return '<link rel="stylesheet" type="text/css" href="'.$math.'"'.($media ? ' media="'.$media.'"' : '').' />';
	}
}
