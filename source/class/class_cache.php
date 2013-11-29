<?php

/**
 * 二级缓存封装类
 * 已废弃
 * 
 * 版本 v0.1.0
 */

class Cache {
	var $storage = 'auto';
	var $gets = 0;
	var $sets = 0;

	public function __get($field){
		return phpFastCache($this->storage)->$field;
	}

	public function __set($field, $value){
		return phpFastCache($this->storage)->$field = $value;
	}

	public function __call($method, $args){
		return call_user_func_array(array($this, $method), $args);
	}

	public function __callStatic($method, $args){
		return call_user_func_array(array($this, $method), $args);
	}

	public function stat(){
		return array(
			'gets' => $this->gets,
			'sets' => $this->sets
		);
	}
}