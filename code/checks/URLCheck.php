<?php

/**
 * Check that a given URL is functioning, by default, the homepage.
 * 
 * Note that Director::test() will be used rather than a CURL check.
 */
class URLCheck implements EnvironmentCheck
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $testString;
    
    /*
     * @param string $url The URL to check, relative to the site (homepage is '').
     * @param string $testString An optional piece of text to search for on the homepage.
     */
    public function __construct($url = '', $testString = '')
    {
        $this->url = $url;
        $this->testString = $testString;
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @throws SS_HTTPResponse_Exception
     */
    public function check()
    {
        $response = Director::test($this->url);

        if ($response->getStatusCode() != 200) {
            return array(
                EnvironmentCheck::ERROR,
                sprintf('Error retrieving "%s" (Code: %d)', $this->url, $response->getStatusCode())
            );
        } elseif ($this->testString && (strpos($response->getBody(), $this->testString) === false)) {
            return array(
                EnvironmentCheck::WARNING,
                sprintf('Success retrieving "%s", but string "%s" not found', $this->url, $this->testString)
            );
        } else {
            return array(
                EnvironmentCheck::OK,
                sprintf('Success retrieving "%s"', $this->url)
            );
        }
    }
}
