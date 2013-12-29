<?php

require_once './source/class/class_core.php';

$actions = array('logging', 'main', 'admin', 'api', 'seccode');//允许的ACTION

$action = isset($_GET['action']) ? strtolower(trim($_GET['action'])) : $actions[0];
$operation = isset($_GET['operation']) ? strtolower(trim($_GET['operation'])) : '';

!in_array($action, $actions, true) && $action = $actions[0];

define('CURACTION', $action);

if(in_array($action, $actions)){
	process("开始加载模块 action/{$action}");
	require_once(libfile("action/{$action}"));

	//面向对象编程支持
	$classAct = ucfirst($action).'Action';
	if(class_exists($classAct)){
		$objAct = new $classAct();
		if(method_exists($classAct, 'run') ? $objAct->run() : true){
			$operation = property_exists($classAct, 'allowed_method') ? (in_array($operation, $objAct->allowed_method) ? $operation : (property_exists($classAct, 'default_method') ? $objAct->default_method : $objAct->allowed_method[0])) : (empty($operation) && property_exists($classAct, 'default_method') ? $objAct->default_method : $operation);
			if(!empty($operation) && method_exists($classAct, $operation)){
				$objAct->$operation();
			}elseif(method_exists($classAct, '_empty')){
				$objAct->_empty();
			}else{
				halt(empty($operation) ? 'default_method_undefined' : 'method_undefined', array('action'=>$classAct, 'operation'=>$operation));
			}
		}
	}
}
