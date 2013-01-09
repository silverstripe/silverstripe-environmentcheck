<?php

class DevCheckController extends Controller {
	function index() {
		$e = new EnvironmentChecker('check', 'Environment status');
		$e->init();
		return $e;
	}
}
