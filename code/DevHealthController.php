<?php

class DevHealthController extends Controller {
	function index() {
		$e = new EnvironmentChecker('health', 'Site health');
		$e->init();
		$e->setErrorCode(404);
		return $e;
	}
}