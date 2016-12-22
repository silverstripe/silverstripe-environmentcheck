<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\EnvironmentCheck\Checks\DatabaseCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\Security\Member;

/**
 * Class DatabaseCheckTest
 *
 * @mixin PHPUnit_Framework_TestCase
 *
 * @package environmentcheck
 */
class DatabaseCheckTest extends SapphireTest
{
    public function testCheckReportsValidConnection()
    {
        $check = new DatabaseCheck();

        $expected = array(
            EnvironmentCheck::OK,
            ''
        );

        $this->assertEquals($expected, $check->check());
    }
}
