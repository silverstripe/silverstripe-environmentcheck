<?php

class DevHealthController extends Controller {

	public static $allowed_actions = array(
		'index'
	);

	function index() {
		$e = new EnvironmentChecker('health', 'Site health');
		$e->init('');   //empty permission check, the "health" check does not require a permission check to run
		$e->setErrorCode(404);
		return $e;
	}
}
