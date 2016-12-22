<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use SilverStripe\EnvironmentCheck\Checks\HasFunctionCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\Dev\SapphireTest;

/**
 * Class HasFunctionCheckTest
 *
 * @mixin PHPUnit_Framework_TestCase
 *
 * @package environmentcheck
 */
class HasFunctionCheckTest extends SapphireTest
{
    public function testCheckReportsMissingFunctions()
    {
        $check = new HasFunctionCheck('foo');

        $expected = array(
            EnvironmentCheck::ERROR,
            'foo() doesn\'t exist'
        );

        $this->assertEquals($expected, $check->check());
    }

    public function testCheckReportsFoundFunctions()
    {
        $check = new HasFunctionCheck('class_exists');

        $expected = array(
            EnvironmentCheck::OK,
            'class_exists() exists'
        );

        $this->assertEquals($expected, $check->check());
    }
}
