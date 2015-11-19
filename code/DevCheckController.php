<?php

/**
 * @package environmentcheck
 */

class DevCheckController extends Controller {

	private static $allowed_actions = array(
		'index'
	);

	/**
	 * Permission code to check for access to this controller.
	 *
	 * @var string
	 */
	private static $permission = 'ADMIN';

	/**
	 * @param SS_HTTPRequest $request
	 *
	 * @return EnvironmentChecker
	 *
	 * @throws SS_HTTPResponse_Exception
	 */
	public function index($request) {
		$suite = 'check';

		if ($name = $request->param('Suite')) {
			$suite = $name;
		}

		$checker = new EnvironmentChecker($suite, 'Environment status');
		$checker->init($this->config()->permission);

		return $checker;
	}
}
