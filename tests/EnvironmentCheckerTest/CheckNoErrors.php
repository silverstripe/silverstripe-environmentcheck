<?php

namespace SilverStripe\EnvironmentCheck\Tests\EnvironmentCheckerTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;

class CheckNoErrors implements EnvironmentCheck, TestOnly
{
    public function check()
    {
        return [EnvironmentCheck::OK, ''];
    }
}
