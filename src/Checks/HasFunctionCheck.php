<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Check that the given function exists.
 *
 * @package environmentcheck
 */
class HasFunctionCheck implements EnvironmentCheck
{
    /**
     * @var string
     */
    protected $functionName;

    /**
     * @param string $functionName The name of the function to look for.
     */
    public function __construct($functionName)
    {
        $this->functionName = $functionName;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function check()
    {
        if (function_exists($this->functionName ?? '')) {
            return [EnvironmentCheck::OK, $this->functionName . '() exists'];
        }
        return [EnvironmentCheck::ERROR, $this->functionName . '() doesn\'t exist'];
    }
}
