<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class Cache {

	private static $object;
	private static $prefix;
	public static $storage;
	public static $cachingtime = 604800;
	public static $count = array(
		'set' => 0,
		'get' => 0,
		'getInfo' => 0,
		'delete' => 0,
		'stats' => 0,
		'clean' => 0,
		'increment' => 0,
		'decrement' => 0,
		'touch' => 0,
		'setMulti' => 0,
		'getMulti' => 0,
		'getInfoMulti' => 0,
		'deleteMulti' => 0,
		'isExistingMulti' => 0,
		'incrementMulti' => 0,
		'decrementMulti' => 0,
		'touchMulti' => 0
	);

	public static function init() {
		global $_G;
		phpFastCache::$config['path'] = APP_FRAMEWORK_ROOT.'/data/cache';
		self::$prefix = $_G['config']['cookie']['cookiepre'];
		self::$object = new phpFastCache('auto');
		self::$object->option('path', APP_FRAMEWORK_ROOT.'/data/cache');
		self::$storage = ucfirst(self::$object->option['storage']);
		return self::$object;
	}

	public static function set($keyword, $value = '', $time = null, $option = array()) {
		self::$count['set']++;
		$time = $time===null ? self::$cachingtime : $time;
		return self::$object->set(self::$prefix.$keyword, $value, $time, $option);
	}

	public static function get($keyword, $option = array()) {
		self::$count['get']++;
		return self::$object->get(self::$prefix.$keyword, $option);
	}

	public static function getInfo($keyword, $option = array()) {
		self::$count['getInfo']++;
		return self::$object->getInfo(self::$prefix.$keyword, $option);
	}

	public static function delete($keyword, $option = array()) {
		self::$count['delete']++;
		return self::$object->delete(self::$prefix.$keyword, $option);
	}

	public static function stats($option = array()) {
		self::$count['stats']++;
		return self::$object->stats($option);
	}

	public static function clean($option = array()) {
		self::$count['clean']++;
		return self::$object->clean($option);
	}

	public static function isExisting($keyword) {
		return self::$object->isExisting(self::$prefix.$keyword);
	}

	public static function increment($keyword, $step = 1 , $option = array()) {
		self::$count['increment']++;
		return self::$object->increment(self::$prefix.$keyword, $step, $option);
	}

	public static function decrement($keyword, $step = 1 , $option = array()) {
		self::$count['decrement']++;
		return self::$object->decrement(self::$prefix.$keyword, $step, $option);
	}

	public static function touch($keyword, $time = null, $option = array()) {
		self::$count['touch']++;
		$time = $time===null ? self::$cachingtime : $time;
		return self::$object->decrement(self::$prefix.$keyword, $time, $option);
	}

	public static function setMulti($list = array()) {
		self::$count['setMulti']++;
		return self::$object->setMulti($list);
	}

	public static function getMulti($list = array()) {
		self::$count['getMulti']++;
		return self::$object->getMulti($list);
	}

	public static function getInfoMulti($list = array()) {
		self::$count['getInfoMulti']++;
		return self::$object->getInfoMulti($list);
	}

	public static function deleteMulti($list = array()) {
		self::$count['deleteMulti']++;
		return self::$object->deleteMulti($list);
	}

	public static function isExistingMulti($list = array()) {
		self::$count['isExistingMulti']++;
		return self::$object->isExistingMulti($list);
	}

	public static function incrementMulti($list = array()) {
		self::$count['incrementMulti']++;
		return self::$object->incrementMulti($list);
	}

	public static function decrementMulti($list = array()) {
		self::$count['decrementMulti']++;
		return self::$object->decrementMulti($list);
	}

	public static function touchMulti($list = array()) {
		self::$count['touchMulti']++;
		return self::$object->touchMulti($list);
	}

	public static function setup($name, $value='') {
		return self::$object->setup($name, $value);
	}

	public static function autoDriver() {
		return self::$object->autoDriver();
	}

	public static function option($name, $value = null) {
		return self::$object->option($name, $value);
	}

	public static function setOption($option = array()) {
		return self::$object->setOption($option);
	}

	public static function systemInfo() {
		return self::$object->systemInfo();
	}

	public static function getOS() {
		return self::$object->getOS();
	}

	public static function encode($data) {
		return self::$object->encode($data);
	}

	public static function decode($value) {
		return self::$object->decode($value);
	}

	public static function htaccessGen($path = '') {
		return self::$object->htaccessGen($path);
	}

	public static function isPHPModule() {
		return self::$object->isPHPModule();
	}

	public static function getPath($create_path = false) {
		return self::$object->getPath($create_path);
	}

}
