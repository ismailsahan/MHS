<?php

error_reporting(0);
require '../source/function/function_core.php';

if(empty($_SERVER['HTTP_X_GITHUB_EVENT']) || empty($_SERVER['HTTP_X_GITHUB_DELIVERY']) || empty($_SERVER['HTTP_X_HUB_SIGNATURE']) || substr($_SERVER['HTTP_USER_AGENT'], 0, 15) != 'GitHub Hookshot' || !in_array($_SERVER['HTTP_X_GITHUB_EVENT'], array('ping', 'push'))) {
	send_http_status(403);
	exit('Access Denied');
}

$input = file_get_contents('php://input');
$input = json_decode($input, true);

if(empty($input) || $input['hook']['config']['secret']!=='qazwsxedcGWC') {
	send_http_status(401);
	exit('Access Denied');
}

print_r($input);