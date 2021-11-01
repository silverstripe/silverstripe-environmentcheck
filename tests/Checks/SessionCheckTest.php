<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Dev\SapphireTest;
use GuzzleHttp\Handler\MockHandler;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\EnvironmentCheck\Checks\SessionCheck;

/**
 * Test session checks.
 */
class SessionCheckTest extends SapphireTest
{
    /**
     * @var SilverStripe\EnvironmentCheck\Checks\SessionCheck
     */
    public $sessionCheck = null;

    /**
     * Create a session check for use by tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionCheck = new SessionCheck('/');
    }

    /**
     * Env check reports error when session cookies are being set.
     *
     * @return void
     */
    public function testSessionSet()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['Set-Cookie' => 'PHPSESSID:foo']),
            new Response(200, ['Set-Cookie' => 'SECSESSID:bar'])
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->sessionCheck->client = $client;

        // Check for PHPSESSID
        $this->assertContains(EnvironmentCheck::ERROR, $this->sessionCheck->check());

        // Check for SECSESSID
        $this->assertContains(EnvironmentCheck::ERROR, $this->sessionCheck->check());
    }

    /**
     * Env check responds OK when no session cookies are set in response.
     *
     * @return void
     */
    public function testSessionNotSet()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200)
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->sessionCheck->client = $client;

        $this->assertContains(EnvironmentCheck::OK, $this->sessionCheck->check());
    }
}
