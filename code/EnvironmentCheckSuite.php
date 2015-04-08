<?php
/**
 * Represents a suite of environment checks.
 * Specific checks can be registered against a named instance of EnvironmentCheckSuite.
 *
 * Usage #1 - _config.php
 * EnvironmentCheckSuite::register('health', 'MyHealthCheck("my param")', 'Title of my health check');
 *
 * Usage #2 - config.yml
 * EnvironmentCheckSuite:
 *   registered_checks:
 *     mycheck:
 *       definition: 'MyHealthCheck("my param")'
 *       title: 'Title of my health check'
 *   registered_suites:
 *     health:
 *       - mycheck
 *
 * $result = EnvironmentCheckSuite::inst('health')->run();
 */
class EnvironmentCheckSuite extends Object {

	/**
	 * Name of this suite.
	 */
	protected $name;

	protected $checks = array();

	/**
	 * Associative array of named checks registered via the config system. Each check should specify:
	 * - definition (e.g. 'MyHealthCheck("my param")')
	 * - title (e.g. 'Is my feature working?')
	 * - state (setting this to 'disabled' will cause suites to skip this check entirely.
	 */
	private static $registered_checks;

	/**
	 * Associative array of named suites registered via the config system. Each suite should enumerate
	 * named checks that have been configured in 'registered_checks'.
	 */
	private static $registered_suites;

	/**
	 * Load checks for this suite from the configuration system. This is an alternative to the
	 * EnvironmentCheckSuite::register - both can be used, checks will be appended to the suite.
	 *
	 * @param string $suiteName The name of this suite.
	 */
	public function __construct($suiteName) {
		if (empty($this->config()->registered_suites[$suiteName])) {
			// Not registered via config system, but it still may be configured later via self::register.
			return;
		}

		foreach ($this->config()->registered_suites[$suiteName] as $checkName) {
			if (empty($this->config()->registered_checks[$checkName])) {
				throw new InvalidArgumentException(
					"Bad EnvironmentCheck: '$checkName' - the named check has not been registered."
				);
			}

			$check = $this->config()->registered_checks[$checkName];

			// Existing named checks can be disabled by setting their 'state' to 'disabled'.
			// This is handy for disabling checks mandated by modules.
			if (!empty($check['state']) && $check['state']==='disabled') continue;
			
			// Add the check to this suite.
			$this->push($check['definition'], $check['title']);
		}
	}

	/**
	 * Run this test suite
	 * @return The result code of the worst result.
	 */
	public function run() {
		$worstResult = 0;

		$result = new EnvironmentCheckSuiteResult();
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
		if(!isset(self::$instances[$name])) self::$instances[$name] = new EnvironmentCheckSuite($name);
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
	 * Convert the final result status and details to JSON.
	 * @return string
	 */
	function toJSON() {
		$result = array(
			'Status' => $this->Status(),
			'ShouldPass' => $this->ShouldPass(),
			'Checks' => array()
		);
		foreach($this->details as $detail) {
			$result['Checks'][] = $detail->toMap();
		}
		return json_encode($result);
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
