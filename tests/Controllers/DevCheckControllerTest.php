<?php

namespace SilverStripe\EnvironmentCheck\Tests\Controllers;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\EnvironmentCheck\Controllers\DevCheckController;
use SilverStripe\EnvironmentCheck\EnvironmentChecker;

/**
 * Class DevCheckControllerTest
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

        $this->assertInstanceOf(EnvironmentChecker::class, $controller->index($request));
    }

    public function testCheckIncludesDetails()
    {
        $controller = new DevCheckController();
        $request = new HTTPRequest('GET', 'example.com');

        $response = $controller->index($request)->index();

        $this->assertStringContainsString('<table>', $response->getBody());
    }
}
