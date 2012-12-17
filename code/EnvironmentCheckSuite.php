<?php
/**
 * Represents a suite of environment checks.
 * Also has a register for assigning environment checks to named instances of EnvironmentCheckSuite
 * 
 * Usage:
 * EnvironmentCheckSuite::register('health', 'MyHealthCheck');
 * 
 * $result = EnvironmentCheckSuite::inst('health')->run();
 */
class EnvironmentCheckSuite {
	protected $checks = array();
	
	/**
	 * Run this test suite
	 * @return The result code of the worst result.
	 */
	public function run() {
		$worstResult = 0;
		
		$result = new EnvironmentCheckSuiteResult;
		foreach($this->checkInstances() as $check) {
			list($checkClass, $checkTitle) = $check;
			try {
				list($status, $message) = $checkClass->check();
			// If the check fails, register that as an error
			} catch(Exception $e) {
				$status = EnvironmentCheck::ERROR;
				$message = $e->getMessage();
			}
			$result->addResult($status, $message, $checkTitle);
		}
		
		return $result;
	}
	
	/**
	 * Get instances of all the environment checks
	 */
	protected function checkInstances() {
		$output = array();
		foreach($this->checks as $check) {
			list($checkClass, $checkTitle) = $check;
			if(is_string($checkClass)) {
				$checkInst = Object::create_from_string($checkClass);
				if($checkInst instanceof EnvironmentCheck) {
					$output[] = array($checkInst, $checkTitle);
				} else {
					throw new InvalidArgumentException("Bad EnvironmentCheck: '$checkClass' - the named class doesn't implement EnvironmentCheck");
				}
			} else if($checkClass instanceof EnvironmentCheck) {
				$output[] = array($checkClass, $checkTitle);
			} else {
				throw new InvalidArgumentException("Bad EnvironmentCheck: " . var_export($check, true));
			}
		}
		return $output;
	}
	
	/**
	 * Add a check to this suite.
	 * 
	 */
	public function push($check, $title = null) {
		if(!$title) {
			$title = is_string($check) ? $check : get_class($check);
		}
		$this->checks[] = array($check, $title);
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////
	
	protected static $instances = array();
	
	/**
	 * Return a named instance of EnvironmentCheckSuite.
	 */
	static function inst($name) {
		if(!isset(self::$instances[$name])) self::$instances[$name] = new EnvironmentCheckSuite();
		return self::$instances[$name];
	}

	/**
	 * Register a check against the named check suite.
	 * 
	 * @param String|Array
	 */
	static function register($names, $check, $title = null) {
		if(!is_array($names)) $names = array($names);
		foreach($names as $name) self::inst($name)->push($check, $title);
	}
}

/**
 * A single set of results from running an EnvironmentCheckSuite
 */
class EnvironmentCheckSuiteResult extends ViewableData {
	protected $details, $worst = 0;
	
	function __construct() {
		parent::__construct();
		$this->details = new ArrayList();
	}
	
	function addResult($status, $message, $checkIdentifier) {
		$this->details->push(new ArrayData(array(
			'Check' => $checkIdentifier,
			'Status' => $this->statusText($status),
			'Message' => $message,
		)));
		
		$this->worst = max($this->worst, $status);
	}
	
	/**
	 * Returns true if there are no ERRORs, only WARNINGs or OK
	 */
	function ShouldPass() {
		return $this->worst <= EnvironmentCheck::WARNING;
	}
	
	/**
	 * Returns overall (i.e. worst) status as a string.
	 */
	function Status() {
		return $this->statusText($this->worst);
	}
	
	/**
	 * Returns detailed status information about each check
	 */
	function Details() {
		return $this->details;
	}
		
	/**
	 * Return a text version of a status code
	 */
	protected function statusText($status) {
		switch($status) {
			case EnvironmentCheck::ERROR: return "ERROR";
			case EnvironmentCheck::WARNING: return "WARNING";
			case EnvironmentCheck::OK: return "OK";
			case 0: return "NO CHECKS";
			default: throw new InvalidArgumentException("Bad environment check status '$status'");
		}
	}
}