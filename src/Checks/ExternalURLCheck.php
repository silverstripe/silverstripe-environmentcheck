<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Checks that one or more URLs are reachable via HTTP.
 * Note that the HTTP connectivity can just be verified from the server to the remote URL,
 * it can still fail if the URL in question is requested by the client, e.g. through an iframe.
 *
 * Requires curl to present, so ensure to check it before with the following:
 * <code>
 * EnvironmentCheckSuite::register(
 *     'check',
 *     'HasFunctionCheck("curl_init")',
 *     "Does PHP have CURL support?"
 * );
 * </code>
 */
class ExternalURLCheck implements EnvironmentCheck
{
    /**
     * @var array
     */
    protected $urls = [];

    /**
     * @var Int Timeout in seconds.
     */
    protected $timeout;

    /**
     * @param string $urls Space-separated list of absolute URLs.
     * @param int $timeout
     */
    public function __construct($urls, $timeout = 15)
    {
        if ($urls) {
            $this->urls = explode(' ', $urls ?? '');
        }
        $this->timeout = $timeout;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function check()
    {
        $urls = $this->getURLs();

        $chs = [];
        foreach ($urls as $url) {
            $ch = curl_init();
            $chs[] = $ch;
            curl_setopt_array($ch, $this->getCurlOpts($url) ?? []);
        }
        // Parallel execution for faster performance
        $mh = curl_multi_init();
        foreach ($chs as $ch) {
            curl_multi_add_handle($mh, $ch);
        }

        $active = null;
        // Execute the handles
        do {
            $mrc = curl_multi_exec($mh, $active);
            curl_multi_select($mh);
        } while ($active > 0);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        $hasError = false;
        $msgs = [];
        foreach ($chs as $ch) {
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch) || $code >= 400) {
                $hasError = true;
                $msgs[] = sprintf(
                    'Error retrieving "%s": %s (Code: %s)',
                    $url,
                    curl_error($ch),
                    $code
                );
            } else {
                $msgs[] = sprintf(
                    'Success retrieving "%s" (Code: %s)',
                    $url,
                    $code
                );
            }
        }

        // Close the handles
        foreach ($chs as $ch) {
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);

        if ($hasError) {
            return [EnvironmentCheck::ERROR, implode(', ', $msgs)];
        }

        return [EnvironmentCheck::OK, implode(', ', $msgs)];
    }

    /**
     * @return array
     */
    protected function getCurlOpts($url)
    {
        return [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_TIMEOUT => $this->timeout,
        ];
    }

    /**
     * @return array
     */
    protected function getURLs()
    {
        return $this->urls;
    }
}
