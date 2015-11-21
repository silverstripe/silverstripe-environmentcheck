<?php

/**
 * Check that the given class exists.
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
     * @inheritdoc
     *
     * @return array
     */
    public function check()
    {
        if (class_exists($this->className)) {
            return array(EnvironmentCheck::OK, 'Class ' . $this->className.' exists');
        } else {
            return array(EnvironmentCheck::ERROR, 'Class ' . $this->className.' doesn\'t exist');
        }
    }
}
