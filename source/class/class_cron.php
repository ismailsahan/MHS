<?php

class Cron {

	/**
	 * 主动断开与客户端的HTTP连接
	 */
	public function closeconnection() {
		if(headers_sent()) return false;
		$size = ob_get_length();
		if(!$size) {
			echo time();
			$size = ob_get_length();
		}
		header("Content-Length: {$size}");
		header('Connection: Close');
		ob_flush();
		return true;
	}

}
