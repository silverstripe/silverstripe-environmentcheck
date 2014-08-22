<?php

class DevCheckController extends Controller {

	public static $allowed_actions = array(
		'index'
	);

	function index($request) {
		$suiteName = $request->param('Suite') ? $request->param('Suite') : 'check';
		$e = new EnvironmentChecker($suiteName, 'Environment status');
		$e->init('ADMIN');  //check for admin permissions before running this check
		return $e;
	}
}
