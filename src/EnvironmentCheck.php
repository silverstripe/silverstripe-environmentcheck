<?php

namespace SilverStripe\EnvironmentCheck;

/**
 * Interface for environment checks
 *
 * An environment check is a test that can be performed on a live environment. They differ from
 * unit tests in that they are designed to check the state of the environment/server, rather than
 * the code.
 *
 * Environment checks should *never* alter production data.
 *
 * Some things you might make environment checks for:
 *  - Can I access the database?
 *  - Are the right PHP modules installed?
 *  - Are the file permissions correct?
 *
 * @package environmentcheck
 */
interface EnvironmentCheck
{
    /**
     * @var int
     */
    const ERROR = 3;

    /**
     * @var int
     */
    const WARNING = 2;

    /**
     * @var int
     */
    const OK = 1;

    /**
     * @return array Result with 'status' and 'message' keys.
     *
     * Status is EnvironmentCheck::ERROR, EnvironmentCheck::WARNING, or EnvironmentCheck::OK.
     */
    public function check();
}
