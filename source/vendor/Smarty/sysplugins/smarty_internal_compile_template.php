<?php

class Smarty_Internal_Compile_Template extends Smarty_Internal_Compile_Include{
	public function compile($args, $compiler, $parameter){
		global $_G;
		$args[0] = substr($args[0], 0, 1)=="'" ? substr($args[0], 0, strlen($args[0])-1).".html'" : $args[0].'.html';
		return parent::compile($args, $compiler, $parameter);
	}
}
