<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use SapphireTest;
use HasClassCheck;
use EnvironmentCheck;


/**
 * @mixin PHPUnit_Framework_TestCase
 */
class HasClassCheckTest extends SapphireTest
{
    public function testCheckReportsMissingClasses()
    {
        $check = new HasClassCheck('foo');

        $expected = array(
            EnvironmentCheck::ERROR,
            'Class foo doesn\'t exist',
        );

        $this->assertEquals($expected, $check->check());
    }

    public function testCheckReportsFoundClasses()
    {
        $check = new HasClassCheck('stdClass');

        $expected = array(
                EnvironmentCheck::OK,
                'Class stdClass exists',
        );

        $this->assertEquals($expected, $check->check());
    }
}
