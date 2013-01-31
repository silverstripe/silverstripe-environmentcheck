<?php

/**
 * Provides an interface for checking the given EnvironmentCheckSuite.
 */
class EnvironmentChecker extends RequestHandler {
	
	static $url_handlers = array(
		'' => 'index',
	);

	protected $checkSuiteName;
	
	protected $title;
	
	protected $errorCode = 500;

	public static $to_email_address = null;
	
	public static $from_email_address = null;
	
	public static $email_results = false;
	
	function __construct($checkSuiteName, $title) {
		parent::__construct();
		
		$this->checkSuiteName = $checkSuiteName;
		$this->title = $title;
	}
	
	function init($permission = 'ADMIN') {
		parent::init();
		
		if(!$this->canAccess(null, $permission)) return $this->httpError(403);
	}

	function canAccess($member = null, $permission = "ADMIN") {
		if(!$member) $member = Member::currentUser();

		// We allow access to this controller regardless of live-status or ADMIN permission only
		// if on CLI.  Access to this controller is always allowed in "dev-mode", or of the user is ADMIN.
		if(
			Director::isDev() 
			|| Director::is_cli()
			|| empty($permission)
			|| Permission::checkMember($member, $permission)
		) {
			return true;
		}

		// Extended access checks.
		// "Veto" style, return NULL to abstain vote.
		$canExtended = null;
		$results = $this->extend('canAccess', $member);
		if($results && is_array($results)) {
			if(!min($results)) return false;
			else return true;
		}

		return false;
	}
	
	function index() {
		$response = new SS_HTTPResponse;
		$result = EnvironmentCheckSuite::inst($this->checkSuiteName)->run();
		
		if(!$result->ShouldPass()) {
			$response->setStatusCode($this->errorCode);
		}
		
		$resultText = $result->customise(array(
			"Title" => $this->title,
			"Name" => $this->checkSuiteName,
			"ErrorCode" => $this->errorCode,
		))->renderWith("EnvironmentChecker");

		if (self::$email_results && !$result->ShouldPass()) {
			$email = new Email(self::$from_email_address, self::$to_email_address, $this->title, $resultText);
			$email->send();
		}

		$response->setBody($resultText);
		
		return $response;
	}

	/**
	 * Set the HTTP status code that should be returned when there's an error.
	 * Defaults to 500
	 */
	function setErrorCode($errorCode) {
		$this->errorCode = $errorCode;
	}

	public static function set_from_email_address($from) {
		self::$from_email_address = $from;
	}

	public static function get_from_email_address() {
		return self::$from_email_address;
	}

	public static function set_to_email_address($to) {
		self::$to_email_address = $to;
	}

	public static function get_to_email_address() {
		return self::$to_email_address;
	}

	public static function set_email_results($results) {
		self::$email_results = $results;
	}

	public static function get_email_results() {
		return self::$email_results;
	}
	
}