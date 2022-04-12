<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use Psr\Http\Message\ResponseInterface;
use SilverStripe\EnvironmentCheck\Traits\Fetcher;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Check that a given URL does not generate a session.
 *
 * @author Adrian Humphreys
 * @package environmentcheck
 */
class SessionCheck implements EnvironmentCheck
{
    use Fetcher;

    /**
     * Set up check with URL
     *
     * @param string $url The route, excluding the domain
     * @inheritdoc
     */
    public function __construct($url = '')
    {
        $this->setURL($url);
    }

    /**
     * Check that the response for URL does not create a session
     *
     * @return array
     */
    public function check()
    {
        $response = $this->client->get($this->getURL());
        $cookie = $this->getCookie($response);
        $fullURL = $this->getURL();

        if ($cookie) {
            return [
                EnvironmentCheck::ERROR,
                "Sessions are being set for {$fullURL} : Set-Cookie => " . $cookie,
            ];
        }
        return [
            EnvironmentCheck::OK,
            "Sessions are not being created for {$fullURL} 👍",
        ];
    }

    /**
     * Get PHPSESSID or SECSESSID cookie set from the response if it exists.
     *
     * @param ResponseInterface $response
     * @return string|null Cookie contents or null if it doesn't exist
     */
    public function getCookie(ResponseInterface $response)
    {
        $result = null;
        $cookies = $response->getHeader('Set-Cookie');

        foreach ($cookies as $cookie) {
            if (strpos($cookie ?? '', 'SESSID') !== false) {
                $result = $cookie;
            }
        }
        return $result;
    }
}
