<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\EnvironmentCheck\Checks\HasClassCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Class HasClassCheckTest
 *
 * @package environmentcheck
 */
class HasClassCheckTest extends SapphireTest
{
    public function testCheckReportsMissingClasses()
    {
        $check = new HasClassCheck('foo');

        $expected = [
            EnvironmentCheck::ERROR,
            'Class foo doesn\'t exist'
        ];

        $this->assertEquals($expected, $check->check());
    }

    public function testCheckReportsFoundClasses()
    {
        $check = new HasClassCheck('stdClass');

        $expected = [
            EnvironmentCheck::OK,
            'Class stdClass exists',
        ];

        $this->assertEquals($expected, $check->check());
    }
}
