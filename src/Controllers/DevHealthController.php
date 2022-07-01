<?php

namespace SilverStripe\EnvironmentCheck\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\EnvironmentCheck\EnvironmentChecker;

/**
 * Class DevHealthController
 *
 * @package environmentcheck
 */
class DevHealthController extends Controller
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'index'
    ];

    /**
     * @return EnvironmentChecker
     *
     * @throws HTTPResponse_Exception
     */
    public function index()
    {
        // health check does not require permission to run
        $checker = new EnvironmentChecker('health', 'Site health');
        $checker->setErrorCode(500);

        return $checker;
    }
}
