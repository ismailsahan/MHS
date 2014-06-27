<?php

error_reporting(0);
set_time_limit(300);
require '../source/function/function_core.php';

if(empty($_SERVER['HTTP_X_GITHUB_EVENT']) || empty($_SERVER['HTTP_X_GITHUB_DELIVERY']) || empty($_SERVER['HTTP_X_HUB_SIGNATURE']) || substr($_SERVER['HTTP_USER_AGENT'], 0, 15) != 'GitHub Hookshot' || !in_array($_SERVER['HTTP_X_GITHUB_EVENT'], array('ping', 'push'))) {
	send_http_status(403);
	exit('Access Denied');
}

try {
	$input = file_get_contents('php://input');
	$input = json_decode($input, true);
} catch (Exception $e) {
	send_http_status(500);
	echo 'Caught Exception: ',  $e->getMessage();
	exit;
}

if($_SERVER['HTTP_X_GITHUB_EVENT'] == 'push') {
	$files = array(
		'update' => array(),
		'delete' => array()
	);

	if(count($input['commits']) > 1) {
		$json = file_get_contents($input['compare']);
		$json = json_decode($json, true);

		foreach($json['files'] as $file) {
			$files[$file['status']=='removed' ? 'delete' : 'update'][] = $file['filename'];
		}

		unset($json);
	} else {
		$files['update'] = array_merge($input['commits'][0]['added'], $input['commits'][0]['modified']);
		$files['delete'] = $input['commits'][0]['removed'];
	}

	foreach($files['update'] as $file) {
		echo "Delete: $file  [";
		echo (unlink('../'.$file) ? 'Success' : 'Failed!');
		echo "]\n";
	}

	foreach($files['delete'] as $file) {
		echo "Update: $file  ";
		try {
			$data = file_get_contents('https://raw.githubusercontent.com/WHUT-SIA/MHS/master/'.$file);
			file_put_contents('../'.$file, $data);
		} catch (Exception $e) {
			echo "[Failed!]\n";
			continue;
		}
		echo "[Success]\n";
	}
}

print_r($input);