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
    /**
     * {@inheritDoc}
     * @var bool
     */
    protected $usesDatabase = true;

    public function testCheckReportsValidConnection()
    {
        $member = new Member;
        $member->FirstName = 'Bob';
        $member->write();

        $check = new DatabaseCheck();

        $expected = array(
            EnvironmentCheck::OK,
            ''
        );

        $this->assertEquals($expected, $check->check());
    }
}
