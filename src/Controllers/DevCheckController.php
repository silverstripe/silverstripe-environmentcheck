<?php

namespace SilverStripe\EnvironmentCheck\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\EnvironmentCheck\EnvironmentChecker;

/**
 * Class DevCheckController
 *
 * @package environmentcheck
 */
class DevCheckController extends Controller
{
    /**
     * @var array
     */
    public static $allowed_actions = array(
        'index'
    );

    /**
     * Permission code to check for access to this controller.
     *
     * @var string
     */
    private static $permission = 'ADMIN';

    /**
     * @param HTTPRequest $request
     *
     * @return EnvironmentChecker
     *
     * @throws HTTPResponse_Exception
     */
    public function index($request)
    {
        $suite = 'check';

        if ($name = $request->param('Suite')) {
            $suite = $name;
        }

        $checker = new EnvironmentChecker($suite, 'Environment status');
        $checker->init($this->config()->permission);

        return $checker;
    }
}
