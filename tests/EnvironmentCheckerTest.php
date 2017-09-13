<?php

namespace SilverStripe\EnvironmentCheck\Tests;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\EnvironmentCheck\EnvironmentChecker;
use SilverStripe\EnvironmentCheck\EnvironmentCheckSuite;

/**
 * Class EnvironmentCheckerTest
 *
 * @package environmentcheck
 */
class EnvironmentCheckerTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected function tearDown()
    {
        EnvironmentCheckSuite::reset();
        parent::tearDown();
    }

    public function testOnlyLogsWithErrors()
    {
        Config::modify()->set(EnvironmentChecker::class, 'log_results_warning', true);
        Config::modify()->set(EnvironmentChecker::class, 'log_results_error', true);

        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest\CheckNoErrors());

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $logger->expects($this->never())->method('log');

        Injector::inst()->registerService($logger, LoggerInterface::class);

        (new EnvironmentChecker('test suite', 'test'))->index();
    }

    public function testLogsWithWarnings()
    {
        Config::modify()->set(EnvironmentChecker::class, 'log_results_warning', true);
        Config::modify()->set(EnvironmentChecker::class, 'log_results_error', false);

        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest\CheckWarnings());
        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest\CheckErrors());

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $logger->expects($this->once())
            ->method('log')
            ->withConsecutive(
                $this->equalTo(LogLevel::WARNING),
                $this->anything()
            );

        Injector::inst()->registerService($logger, LoggerInterface::class);

        (new EnvironmentChecker('test suite', 'test'))->index();
    }

    public function testLogsWithErrors()
    {
        Config::modify()->set(EnvironmentChecker::class, 'log_results_error', false);
        Config::modify()->set(EnvironmentChecker::class, 'log_results_error', true);

        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest\CheckWarnings());
        EnvironmentCheckSuite::register('test suite', new EnvironmentCheckerTest\CheckErrors());

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $logger->expects($this->once())
            ->method('log')
            ->withConsecutive(
                [$this->equalTo(LogLevel::ALERT), $this->anything()],
                [$this->equalTo(LogLevel::WARNING), $this->anything()]
            );

        Injector::inst()->registerService($logger, LoggerInterface::class);

        (new EnvironmentChecker('test suite', 'test'))->index();
    }
}
