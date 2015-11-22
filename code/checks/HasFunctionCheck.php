<?php

/**
 * Check that the given function exists.
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
     * @inheritdoc
     *
     * @return array
     */
    public function check()
    {
        if (function_exists($this->functionName)) {
            return array(EnvironmentCheck::OK, $this->functionName.'() exists');
        } else {
            return array(EnvironmentCheck::ERROR, $this->functionName.'() doesn\'t exist');
        }
    }
}
