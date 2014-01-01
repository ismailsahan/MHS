<?php

require_once __DIR__.'/class/class_core.php';

//$CORE = &C::instance();
//$CORE = new C();
//$CORE->init();
C::instance();

$action = ACTION_NAME;
$operation = OPERATION_NAME;

//if(in_array($action, $actions)){
	process('开始加载模块 action/'.ACTION_NAME);
	require_once(libfile('action/'.ACTION_NAME));
	define('CURRENT_ACTION', $action);

	//面向对象编程支持
	$classAct = ucfirst($action).'Action';
	if(class_exists($classAct)){
		$objAct = new $classAct();
		if(method_exists($classAct, 'run') ? $objAct->run() : true){
			$operation = property_exists($classAct, 'allowed_method') ? (in_array($operation, $objAct->allowed_method) ? $operation : (property_exists($classAct, 'default_method') ? $objAct->default_method : $objAct->allowed_method[0])) : (empty($operation) && property_exists($classAct, 'default_method') ? $objAct->default_method : $operation);
			if(!empty($operation) && method_exists($classAct, $operation)){
				define('CURRENT_OPERATION', $operation);
				$objAct->$operation();
			}elseif(method_exists($classAct, '_empty')){
				$objAct->_empty();
			}else{
				halt(empty($operation) ? 'default_method_undefined' : 'method_undefined', array('action'=>$classAct, 'operation'=>$operation));
			}
		}
	}
//}