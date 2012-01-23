<?php

/**
 * Interface for environment checks
 * 
 * An environment check is a test that can be performed on a live environment.  They differ from unit
 * tests in that they are designed to check the state of the evnironment / server, rather than the code.
 * 
 * Environment checks should *never* alter production data.
 * 
 * Some things you might make environment checks for:
 *  - Can I access the database?
 *  - Are the right PHP modules installed?
 *  - Are the file permissions correct?
 */
interface EnvironmentCheck {

	const ERROR = 3;
	const WARNING = 2;
	const OK = 1;

	/**
	 * Perform this check
	 * 
	 * @return 2 element array( $status, $message )
	 * $status is EnvironmentCheck::ERROR, EnvironmentCheck::WARNING, or EnvironmentCheck::OK
	 */
	function check();

}