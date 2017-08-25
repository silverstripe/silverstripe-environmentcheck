<?php

namespace SilverStripe\EnvironmentCheck\Tests\EnvironmentCheckerTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;

class CheckWarnings implements EnvironmentCheck, TestOnly
{
    public function check()
    {
        return [EnvironmentCheck::WARNING, 'test warning'];
    }
}
