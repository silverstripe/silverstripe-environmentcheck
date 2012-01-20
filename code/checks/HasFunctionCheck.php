<?php
/**
 * Check that the given function exists.
 * This can be used to check that PHP modules or features are installed.
 * @param $functionName The name of the function to look for.
 */
class HasFunctionCheck implements EnvironmentCheck {
	protected $functionName;
	
	function __construct($functionName) {
		$this->functionName = $functionName;
	}
	
	function check() {
		if(function_exists($this->functionName)) return array(EnvironmentCheck::OK, $this->functionName.'() exists');
		else return array(EnvironmentCheck::ERROR, $this->functionName.'() doesn\'t exist');
	}
}