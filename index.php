<?php

require_once './source/class/class_core.php';

$actions = array('logging', 'main', 'admin', 'seccode');//允许的ACTION

$action = isset($_GET['action']) ? trim($_GET['action']) : $actions[0];
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';

!in_array($action, $actions, true) && $action = $actions[0];

if(in_array($action, $actions)){
	process("开始加载模块 action/{$action}");
	require_once(libfile("action/{$action}"));
}