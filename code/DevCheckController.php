<?php

class DevCheckController extends Controller {
	function index() {
		if(!Permission::check("ADMIN")) return Security::permissionFailure();
		
		$e = new EnvironmentChecker('check', 'Environment status');
		return $e;
	}
}
