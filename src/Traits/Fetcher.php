<?php

namespace SilverStripe\EnvironmentCheck\Traits;

use SilverStripe\Control\Director;

/**
 * Simple helper for env checks which require HTTP clients.
 *
 * @package environmentcheck
 */
trait Fetcher
{
    /**
     * Client for making requests, set vi Injector.
     *
     * @see SilverStripe\EnvironmentCheck\Services
     *
     * @var GuzzleHttp\Client
     */
    public $client = null;

    /**
     * Absolute URL for requests.
     *
     * @var string
     */
    protected $url;

    /**
     * Set URL for requests.
     *
     * @param string $url Relative URL
     * @return self
     */
    public function setURL($url)
    {
        $this->url = Director::absoluteURL($url);
        return $this;
    }

    /**
     * Getter for URL
     *
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }
}
