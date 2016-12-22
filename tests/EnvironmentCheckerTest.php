<?php

namespace SilverStripe\EnvironmentCheck\Tests;

use Phockito;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheckSuite;

/**
 * Class EnvironmentCheckerTest
 *
 * @package environmentcheck
 */
class EnvironmentCheckerTest extends SapphireTest
{
    /**
     * {@inheritDoc}
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * {@inheritDoc}
     */
    public function setUpOnce()
    {
        parent::setUpOnce();

        Phockito::include_hamcrest();

        $logger = Injector::inst()->get('Logger');
        if ($logger instanceof \Monolog\Logger) {
            // It logs to stderr by default - disable
            $logger->pushHandler(new \Monolog\Handler\NullHandler);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        Config::nest();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        Config::unnest();

        parent::tearDown();
    }

    public function testOnlyLogsWithErrors()
    {
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'log_results_warning', true);
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'log_results_error', true);
        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckNoErrors());
        $checker = Phockito::spy(
            'SilverStripe\\EnvironmentCheck\\EnvironmentChecker',
            'test suite',
            'test'
        );

        $response = $checker->index();
        Phockito::verify($checker, 0)->log(anything(), anything());
        EnvironmentCheckSuite::reset();
    }

    public function testLogsWithWarnings()
    {
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'log_results_warning', true);
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'log_results_error', false);
        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckWarnings());
        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckErrors());
        $checker = Phockito::spy(
            'SilverStripe\\EnvironmentCheck\\EnvironmentChecker',
            'test suite',
            'test'
        );

        $response = $checker->index();
        Phockito::verify($checker, 1)->log(containsString('warning'), anything());
        Phockito::verify($checker, 0)->log(containsString('error'), anything());
        EnvironmentCheckSuite::reset();
    }

    public function testLogsWithErrors()
    {
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'log_results_error', false);
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'log_results_error', true);
        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckWarnings());
        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest_CheckErrors());
        $checker = Phockito::spy(
            'SilverStripe\\EnvironmentCheck\\EnvironmentChecker',
            'test suite',
            'test'
        );

        $response = $checker->index();
        Phockito::verify($checker, 0)->log(containsString('warning'), anything());
        Phockito::verify($checker, 1)->log(containsString('error'), anything());
        EnvironmentCheckSuite::reset();
    }
}

class EnvironmentCheckerTest_CheckNoErrors implements EnvironmentCheck, TestOnly
{
    public function check()
    {
        return array(EnvironmentCheck::OK, '');
    }
}

class EnvironmentCheckerTest_CheckWarnings implements EnvironmentCheck, TestOnly
{
    public function check()
    {
        return array(EnvironmentCheck::WARNING, 'test warning');
    }
}

class EnvironmentCheckerTest_CheckErrors implements EnvironmentCheck, TestOnly
{
    public function check()
    {
        return array(EnvironmentCheck::ERROR, 'test error');
    }
}
