<?php

/**
 * @mixin PHPUnit_Framework_TestCase
 */
class ExternalURLCheckTest extends SapphireTest {
	public function testCheckReportsMissingPages() {
		$this->markTestSkipped('ExternalURLCheck seems faulty on some systems');

		$check = new ExternalURLCheck('http://missing-site/');

		$expected = array(
			EnvironmentCheck::ERROR,
			'Success retrieving "http://missing-site/" (Code: 404)',
		);

		$this->assertEquals($expected, $check->check());
	}
}
