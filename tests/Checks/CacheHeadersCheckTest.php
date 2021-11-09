<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Dev\SapphireTest;
use GuzzleHttp\Handler\MockHandler;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\EnvironmentCheck\Checks\CacheHeadersCheck;

/**
 * Test session checks.
 */
class CacheHeadersCheckTest extends SapphireTest
{
    /**
     * Test that directives that must be included, are.
     *
     * @return void
     */
    public function testMustInclude()
    {
        // Create a mock and queue responses
        $mock = new MockHandler([
            new Response(200, ['Cache-Control' => 'must-revalidate', 'ETag' => '123']),
            new Response(200, ['Cache-Control' =>'no-cache', 'ETag' => '123']),
            new Response(200, ['ETag' => '123']),
            new Response(200, ['Cache-Control' => 'must-revalidate, private', 'ETag' => '123']),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $cacheHeadersCheck = new CacheHeadersCheck('/', ['must-revalidate']);
        $cacheHeadersCheck->client = $client;

        // Run checks for each response above
        $this->assertContains(EnvironmentCheck::OK, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::ERROR, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::ERROR, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::OK, $cacheHeadersCheck->check());
    }

    /**
     * Test that directives that must be excluded, are.
     *
     * @return void
     */
    public function testMustExclude()
    {
        // Create a mock and queue responses
        $mock = new MockHandler([
            new Response(200, ['Cache-Control' => 'must-revalidate', 'ETag' => '123']),
            new Response(200, ['Cache-Control' =>'no-cache', 'ETag' => '123']),
            new Response(200, ['ETag' => '123']),
            new Response(200, ['Cache-Control' =>'private, no-store', 'ETag' => '123']),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $cacheHeadersCheck = new CacheHeadersCheck('/', [], ["no-store", "no-cache", "private"]);
        $cacheHeadersCheck->client = $client;

        // Run checks for each response above
        $this->assertContains(EnvironmentCheck::OK, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::ERROR, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::OK, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::ERROR, $cacheHeadersCheck->check());
    }

    /**
     * Test that Etag header must exist in response.
     *
     * @return void
     */
    public function testEtag()
    {
        // Create a mock and queue responses
        $mock = new MockHandler([
            new Response(200, ['Cache-Control' => 'must-revalidate', 'ETag' => '123']),
            new Response(200, ['Cache-Control' =>'no-cache']),
            new Response(200, ['ETag' => '123']),
            new Response(200, []),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $cacheHeadersCheck = new CacheHeadersCheck('/');
        $cacheHeadersCheck->client = $client;

        // Run checks for each response above
        $this->assertContains(EnvironmentCheck::OK, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::ERROR, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::OK, $cacheHeadersCheck->check());
        $this->assertContains(EnvironmentCheck::ERROR, $cacheHeadersCheck->check());
    }
}
