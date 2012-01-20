<?php
/**
 * Check that the given class exists.
 * This can be used to check that PHP modules or features are installed.
 * @param $className The name of the class to look for.
 */
class HasClassCheck implements EnvironmentCheck {
	protected $className;
	
	function __construct($className) {
		$this->className = $className;
	}
	
	function check() {
		if(class_exists($this->className)) return array(EnvironmentCheck::OK, 'Class ' . $this->className.' exists');
		else return array(EnvironmentCheck::ERROR, 'Class ' . $this->className.' doesn\'t exist');
	}
}