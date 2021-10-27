<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\EnvironmentCheck\Checks\ExternalURLCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Class ExternalURLCheckTest
 *
 * @package environmentcheck
 */
class ExternalURLCheckTest extends SapphireTest
{
    public function testCheckReportsMissingPages()
    {
        $this->markTestSkipped('ExternalURLCheck seems faulty on some systems');

        $check = new ExternalURLCheck('http://missing-site/');

        $expected = [
            EnvironmentCheck::ERROR,
            'Success retrieving "http://missing-site/" (Code: 404)'
        ];

        $this->assertEquals($expected, $check->check());
    }
}
