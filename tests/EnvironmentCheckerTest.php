<?php
class EnvironmentCheckerTest extends SapphireTest {

	public function setUpOnce() {
		parent::setUpOnce();

		Phockito::include_hamcrest();
	}

	public function setUp() {
		parent::setUp();

		Config::nest();
	}

	public function tearDown() {
		Config::unnest();

		parent::tearDown();
	}

	public function testOnlyLogsWithErrors() {
		Config::inst()->update('EnvironmentChecker', 'log_results_warning', true);
		Config::inst()->update('EnvironmentChecker', 'log_results_error', true);
		EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckNoErrors());
		$checker = Phockito::spy(
			'EnvironmentChecker', 
			'test suite',
			'test'
		);

		$response = $checker->index();
		Phockito::verify($checker, 0)->log(anything(), anything());
		EnvironmentCheckSuite::reset();
	}

	public function testLogsWithWarnings() {
		Config::inst()->update('EnvironmentChecker', 'log_results_warning', true);
		Config::inst()->update('EnvironmentChecker', 'log_results_error', false);
		EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckWarnings());
		EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckErrors());
		$checker = Phockito::spy(
			'EnvironmentChecker',
			'test suite',
			'test'
		);

		$response = $checker->index();
		Phockito::verify($checker, 1)->log(containsString('warning'), anything());
		Phockito::verify($checker, 0)->log(containsString('error'), anything());
		EnvironmentCheckSuite::reset();
	}

	public function testLogsWithErrors() {
		Config::inst()->update('EnvironmentChecker', 'log_results_error', false);
		Config::inst()->update('EnvironmentChecker', 'log_results_error', true);
		EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckWarnings());
		EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckErrors());
		$checker = Phockito::spy(
			'EnvironmentChecker',
			'test suite',
			'test'
		);

		$response = $checker->index();
		Phockito::verify($checker, 0)->log(containsString('warning'), anything());
		Phockito::verify($checker, 1)->log(containsString('error'), anything());
		EnvironmentCheckSuite::reset();
	}

}

class EnvironmentCheckerTest_CheckNoErrors implements EnvironmentCheck, TestOnly{
	public function check() {
		return array(EnvironmentCheck::OK, '');
	}
}

class EnvironmentCheckerTest_CheckWarnings implements EnvironmentCheck, TestOnly{
	public function check() {
		return array(EnvironmentCheck::WARNING, "test warning");
	}
}

class EnvironmentCheckerTest_CheckErrors implements EnvironmentCheck, TestOnly{
	public function check() {
		return array(EnvironmentCheck::ERROR, "test error");
	}
}