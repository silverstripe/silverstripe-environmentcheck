<?php

namespace SilverStripe\EnvironmentCheck\Tests\Controllers;




use SilverStripe\EnvironmentCheck\Controllers\DevHealthController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;



/**
 * @mixin PHPUnit_Framework_TestCase
 */
class DevHealthControllerTest extends SapphireTest
{
    protected $usesDatabase = true;
    
    public function testIndexCreatesChecker()
    {
        $controller = new DevHealthController();

        $request = new HTTPRequest('GET', 'example.com');

        // we need to fake authenticated access as BasicAuth::requireLogin doesn't like empty
        // permission type strings, which is what health check uses.

        define('ENVCHECK_BASICAUTH_USERNAME', 'foo');
        define('ENVCHECK_BASICAUTH_PASSWORD', 'bar');

        $_SERVER['PHP_AUTH_USER'] = 'foo';
        $_SERVER['PHP_AUTH_PW'] = 'bar';

        $this->assertInstanceOf('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', $controller->index($request));
    }
}
