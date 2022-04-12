<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Check that the given class exists.
 *
 * @package environmentcheck
 */
class HasClassCheck implements EnvironmentCheck
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @param string $className The name of the class to look for.
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function check()
    {
        if (class_exists($this->className ?? '')) {
            return [EnvironmentCheck::OK, 'Class ' . $this->className.' exists'];
        }
        return [EnvironmentCheck::ERROR, 'Class ' . $this->className.' doesn\'t exist'];
    }
}
