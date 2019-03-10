<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;

use SilverStripe\Core\Kernel;
use App\Checks\EnvSettingCheck;
use SilverStripe\Control\Director;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Test the env setting check.
 */
class EnvTypeCheckTest extends SapphireTest
{
    /**
     * Check is OK when in live mode
     *
     * @return void
     */
    public function testEnvSettingLive()
    {
        /** @var Kernel $kernel */
        $kernel = Injector::inst()->get(Kernel::class);
        $kernel->setEnvironment('live');

        $this->assertTrue(Director::isLive());

        $checker = Injector::inst()->get(EnvSettingCheck::class);
        $result = $checker->check();

        $this->assertSame($result[0], EnvironmentCheck::OK);
    }

    /**
     * Check is ERROR when in test mode
     *
     * @return void
     */
    public function testEnvSettingTest()
    {
        /** @var Kernel $kernel */
        $kernel = Injector::inst()->get(Kernel::class);
        $kernel->setEnvironment('test');

        $this->assertTrue(Director::isTest());

        $checker = Injector::inst()->get(EnvSettingCheck::class);
        $result = $checker->check();

        $this->assertSame($result[0], EnvironmentCheck::ERROR);
    }

    /**
     * Check is ERROR when in dev mode
     *
     * @return void
     */
    public function testEnvSettingDev()
    {
        /** @var Kernel $kernel */
        $kernel = Injector::inst()->get(Kernel::class);
        $kernel->setEnvironment('dev');

        $this->assertTrue(Director::isDev());

        $checker = Injector::inst()->get(EnvSettingCheck::class);
        $result = $checker->check();

        $this->assertSame($result[0], EnvironmentCheck::ERROR);
    }
}
