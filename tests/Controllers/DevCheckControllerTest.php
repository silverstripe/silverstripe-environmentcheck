<?php

namespace SilverStripe\EnvironmentCheck\Tests\Controllers;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\EnvironmentCheck\Controllers\DevCheckController;

/**
 * Class DevCheckControllerTest
 *
 * @mixin PHPUnit_Framework_TestCase
 *
 * @package environmentcheck
 */
class DevCheckControllerTest extends SapphireTest
{
    /**
     * {@inheritDoc}
     * @var array
     */
    protected $usesDatabase = true;

    public function testIndexCreatesChecker()
    {
        $controller = new DevCheckController();

        $request = new HTTPRequest('GET', 'example.com');

        $this->assertInstanceOf('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', $controller->index($request));
    }
}
