<?php

namespace SilverStripe\EnvironmentCheck\Controllers;

use Controller;
use EnvironmentChecker;


class DevHealthController extends Controller
{
    /**
     * @var array
     */
    public static $allowed_actions = array(
        'index'
    );

    /**
     * @return EnvironmentChecker
     *
     * @throws SS_HTTPResponse_Exception
     */
    public function index()
    {
        // health check does not require permission to run

        $checker = new EnvironmentChecker('health', 'Site health');
        $checker->setErrorCode(500);

        return $checker;
    }
}
