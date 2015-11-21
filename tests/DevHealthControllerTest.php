<?php

/**
 * @mixin PHPUnit_Framework_TestCase
 */
class DevHealthControllerTest extends SapphireTest
{
    public function testIndexCreatesChecker()
    {
        $controller = new DevHealthController();

        $request = new SS_HTTPRequest('GET', 'example.com');

        // we need to fake authenticated access as BasicAuth::requireLogin doesn't like empty
        // permission type strings, which is what health check uses.

        define('ENVCHECK_BASICAUTH_USERNAME', 'foo');
        define('ENVCHECK_BASICAUTH_PASSWORD', 'bar');

        $_SERVER['PHP_AUTH_USER'] = 'foo';
        $_SERVER['PHP_AUTH_PW'] = 'bar';

        $this->assertInstanceOf('EnvironmentChecker', $controller->index($request));
    }
}
