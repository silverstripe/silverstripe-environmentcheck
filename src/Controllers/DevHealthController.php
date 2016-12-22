<?php

namespace SilverStripe\EnvironmentCheck\Controllers;



use SilverStripe\EnvironmentCheck\EnvironmentChecker;
use SilverStripe\Control\Controller;



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
