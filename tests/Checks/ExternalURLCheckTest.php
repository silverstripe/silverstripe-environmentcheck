<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;




use SilverStripe\EnvironmentCheck\Checks\ExternalURLCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\Dev\SapphireTest;



/**
 * @mixin PHPUnit_Framework_TestCase
 */
class ExternalURLCheckTest extends SapphireTest
{
    public function testCheckReportsMissingPages()
    {
        $this->markTestSkipped('ExternalURLCheck seems faulty on some systems');

        $check = new ExternalURLCheck('http://missing-site/');

        $expected = array(
            EnvironmentCheck::ERROR,
            'Success retrieving "http://missing-site/" (Code: 404)',
        );

        $this->assertEquals($expected, $check->check());
    }
}
