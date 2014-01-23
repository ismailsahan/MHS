<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

abstract class Action {

	/**
	 * 当前控制器名称
	 * @var name
	 * @access protected
	 */	  
	private $name = '';

	/**
	 * 架构函数 取得模板对象实例
	 * @access public
	 */
	public function __construct() {
		
	}

	public function __set($name, $value) {
		$setter = 'set'.$name;
		if(method_exists($this, $setter)) {
			return $this->$setter($value);
		} elseif($this->canGetProperty($name)) {
			throw new Exception('The property "'.get_class($this).'->'.$name.'" is readonly');
		} else {
			throw new Exception('The property "'.get_class($this).'->'.$name.'" is not defined');
		}
	}

	public function __get($name) {
		$getter = 'get'.$name;
		if(method_exists($this, $getter)) {
			return $this->$getter();
		} else {
			throw new Exception('The property "'.get_class($this).'->'.$name.'" is not defined');
		}
	}

	public function __call($name, $parameters) {
		if(method_exists($this, '_empty')) {
			// 如果定义了_empty操作 则调用
			return $this->_empty($method, $args);
		}elseif(property_exists($this, 'default_method')){
            $_method = $this->default_method;
            return $this->$_method();
        }
		throw new Exception('Class "'.get_class($this).'" does not have a method named "'.$name.'".');
	}

	public function canGetProperty($name) {
		return method_exists($this, 'get'.$name);
	}

	public function canSetProperty($name) {
		return method_exists($this, 'set'.$name);
	}

	public function __toString() {
		return get_class($this);
	}

	public function __invoke() {
		return get_class($this);
	}

	/**
	 * 获取当前Action名称
	 * @access protected
	 */
	protected function getActionName() {
		if(empty($this->name)) {
			// 获取Action名称
			$this->name = substr(get_class($this), 0, -6);
		}
		return $this->name;
	}

	/**
	 * 析构方法
	 * @access public
	 */
	public function __destruct() {
		
	}

}
