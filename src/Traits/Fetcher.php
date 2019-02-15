<?php

namespace SilverStripe\EnvironmentCheck\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Simple helper for fetching responses using Guzzle client.
 *
 * @package environmentcheck
 */
trait Fetcher
{
    /**
     * Configuration for the Guzzle client
     *
     * @var array
     */
    protected $clientConfig = [];

    /**
     * Merges configuration arrays and returns the result
     *
     * @param array $extraConfig
     * @return array
     */
    private function getClientConfig(array $extraConfig = [])
    {
        return array_merge($this->clientConfig, $extraConfig);
    }

    /**
     * Fetch a response for a URL using Guzzle client.
     *
     * @param string $url
     * @param array|null $extraConfig Extra configuration
     * @return ResponseInterface
     */
    public function fetchResponse(string $url, array $extraConfig = [])
    {
        $config = $this->getClientConfig($extraConfig);
        $client = new Client($config);
        return $client->get($url);
    }
}
