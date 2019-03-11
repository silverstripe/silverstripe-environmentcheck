<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use SilverStripe\Control\Director;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Check whether the environment setting is safe. Useful for live sites where a
 * non "Live" setting might disclose sensitive information.
 *
 * @package environmentcheck
 */
class EnvTypeCheck implements EnvironmentCheck
{
    /**
     * Check the environment setting.
     *
     * @return array
     */
    public function check()
    {
        $envSetting = Director::get_environment_type();
        switch ($envSetting) {
            case 'live':
                return [
                    EnvironmentCheck::OK,
                    "Env setting is 'live'",
                ];
            // Fallthrough
            default:
            case 'dev':
            case 'test':
                return [
                    EnvironmentCheck::ERROR,
                    "Env setting is '{$envSetting}' and may disclose information",
                ];
        }
    }
}
