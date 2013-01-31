<?php

class DevCheckController extends Controller {
	function index() {
		$e = new EnvironmentChecker('check', 'Environment status');
		$e->init('ADMIN');  //check for admin permissions before running this check
		return $e;
	}
}
