<?php
/**
 * Check that a given URL is functioning, by default, the homepage.
 * 
 * Note that Director::test() will be used rather than a CURL check.
 */
class URLCheck implements EnvironmentCheck {
	protected $url;
	protected $testString;
	
	/*
	 * @param $url The URL to check, relative to the site.  "" is the homepage.
	 * @param $testString A piece of text to optionally search for in the homepage HTML.  If omitted, no such check is made.
	 */
	function __construct($url = "", $testString = "") {
		$this->url = $url;
		$this->testString = $testString;
	}
	
	function check() {
		$response = Director::test($this->url);

		if($response->getStatusCode() != 200) {
			return array(
				EnvironmentCheck::ERROR, 
				sprintf('Error retrieving "%s" (Code: %d)', $this->url, $response->getStatusCode())
			);

		} else if($this->testString && (strpos($response->getBody(), $this->testString) === false)) {
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