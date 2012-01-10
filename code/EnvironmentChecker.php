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
	
	function __construct($checkSuiteName, $title) {
		parent::__construct();
		
		$this->checkSuiteName = $checkSuiteName;
		$this->title = $title;
	}
	
	/**
	 * Set the HTTP status code that should be returned when there's an error.
	 * Defaults to 500
	 */
	function setErrorCode($errorCode) {
		$this->errorCode = $errorCode;
	}
	
	function init() {
		parent::init();
		
		// We allow access to this controller regardless of live-status or ADMIN permission only
		// if on CLI.  Access to this controller is always allowed in "dev-mode", or of the user is ADMIN.
		$canAccess = (Director::isDev() 
			|| Director::is_cli() 
			// Its important that we don't run this check if dev/build was requested
			|| Permission::check("ADMIN")
		);
		if(!$canAccess) return Security::permissionFailure($this);
	}
	
	function index() {
		$response = new SS_HTTPResponse;
		$result = EnvironmentCheckSuite::inst($this->checkSuiteName)->run();
		
		if(!$result->ShouldPass()) {
			$response->setStatusCode($this->errorCode);
		}
		
		$response->setBody($result->customise(array(
			"Title" => $this->title,
			"ErrorCode" => $this->errorCode,
		))->renderWith("EnvironmentChecker"));
		
		return $response;
	}
}