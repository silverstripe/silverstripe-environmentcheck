<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use SilverStripe\EnvironmentCheck\Checks\URLCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\Dev\SapphireTest;

/**
 * Class URLCheckTest
 *
 * @package environmentcheck
 */
class URLCheckTest extends SapphireTest
{
    public function testCheckReportsMissingPages()
    {
        $check = new URLCheck('foo', 'bar');

        $expected = [
            EnvironmentCheck::ERROR,
            'Error retrieving "foo" (Code: 404)'
        ];

        $this->assertEquals($expected, $check->check());
    }
}
