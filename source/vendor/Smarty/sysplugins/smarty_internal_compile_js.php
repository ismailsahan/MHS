<?php

class Smarty_Internal_Compile_Js extends Smarty_Internal_CompileBase{
	public $required_attributes = array('link');
	public $shorttag_order = array('link');
	public $option_flags = array();
	public $optional_attributes = array();

	public function compile($args, $compiler, $parameter){
		$_attr = $this->getAttributes($compiler, $args);
		$_attr['link'] = str_replace("'", '', $_attr['link']);
		$math = strexists($_attr['link'], '.js') ? $_attr['link'] : $_attr['link'].'.js';
		$math = StaticEngine::instance()->staticfile($math);
		return '<script type="text/javascript" src="'.$math.'"></script>';
	}
}
