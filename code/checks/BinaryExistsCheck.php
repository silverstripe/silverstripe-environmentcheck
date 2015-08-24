<?php

/**
 * @package environmentcheck
 */

class BinaryExistsCheck implements EnvironmentCheck {

	protected $binary;

	function __construct($binary = "Member") {
		$this->binary = $binary;
	}

	function check() {
		$command = (PHP_OS == 'WINNT') ? 'where' : 'which';
		$binary = $this->binary;

		$process = proc_open(
			"$command $binary",
			array(
				0 => array("pipe", "r"),
				1 => array("pipe", "w"),
				2 => array("pipe", "w"),
			),
			$pipes
		);

		if ($process !== false) {
			$stdout = stream_get_contents($pipes[1]);
			$stderr = stream_get_contents($pipes[2]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			proc_close($process);

			if($stdout != '') {
				return array(EnvironmentCheck::OK, '');
			}
		}

		return array(EnvironmentCheck::ERROR, 'Could not execute '. $this->binary . ' in ');
	}
}