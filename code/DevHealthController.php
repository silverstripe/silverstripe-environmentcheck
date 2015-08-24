<?php

/**
 * A public URL which requires no authenication which returns a simple Ok, Fail
 * check.
 *
 * For a more detailed check, see {@link DevCheckController}
 *
 * @package environmentcheck
 */
class DevHealthController extends Controller {

	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'index'
	);

	public function index() {
		Config::inst()->update('EnvironmentChecker', 'template', 'DevHealthController');

		$e = new EnvironmentChecker('health', 'Site health');
		$e->init('');   //empty permission check, the "health" check does not require a permission check to run
		$e->setErrorCode(500);

		return $e;
	}
}
