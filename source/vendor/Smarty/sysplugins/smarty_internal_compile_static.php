<?php

class Smarty_Internal_Compile_Static extends Smarty_Internal_CompileBase{
	public $required_attributes = array('link');
	public $shorttag_order = array('link');
	public $option_flags = array();
	public $optional_attributes = array();

	public function compile($args, $compiler, $parameter){
		$_attr = $this->getAttributes($compiler, $args);
		$link = str_replace("'", '', $_attr['link']);
		return StaticEngine::instance()->staticfile($link);
	}
}
