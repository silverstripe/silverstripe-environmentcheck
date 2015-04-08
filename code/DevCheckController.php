<?php

class DevCheckController extends Controller {

	public static $allowed_actions = array(
		'index'
	);

	/**
	 * @var string Permission code to check for access to this controller.
	 */
	private static $permission = 'ADMIN';

	function index($request) {
		$suiteName = $request->param('Suite') ? $request->param('Suite') : 'check';
		$e = new EnvironmentChecker($suiteName, 'Environment status');
		$e->init($this->config()->permission);  //check for admin permissions before running this check
		return $e;
	}
}
