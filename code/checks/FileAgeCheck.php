<?php
/**
 * Checks for the maximum age of one or more files or folders.
 * Useful for files which should be frequently auto-generated, 
 * like static caches, as well as for backup files and folders.
 * Does NOT check for existence of a file (will silently fail).
 *
 * Examples:
 * // Checks that Requirements::combine_files() has regenerated files in the last 24h
 * EnvironmentCheckSuite::register(
 * 	'check', 
 * 	'FileAgeCheck("' . ASSETS_PATH . '/_combined_files/*.js' . '", "-1 day", '>', " . FileAgeCheck::CHECK_ALL) . "'
 * );
 * 
 * // Checks that at least one backup folder has been created in the last 24h
 * EnvironmentCheckSuite::register(
 * 	'check', 
 * 	'FileAgeCheck("' . BASE_PATH . '/../backups/*' . '", "-1 day", '>', " . FileAgeCheck::CHECK_SINGLE) . "'
 * );
 */
class FileAgeCheck implements EnvironmentCheck {

	const CHECK_SINGLE = 1;

	const CHECK_ALL = 2;
	
	/**
	 * @var String Absolute path to a file or folder, compatible with glob().
	 */
	protected $path;

	/**
	 * @var String strtotime() compatible relative date specification.
	 */
	protected $relativeAge;

	/**
	 * @var String The function to use for checking file age,
	 * so filemtime(), filectime() or fileatime().
	 */
	protected $checkFn;

	/**
	 * @var Int Constant, check for a single file to match age criteria, or all of them.
	 */
	protected $checkType;

	/**
	 * @var String Either '>' or '<'.
	 */
	protected $compareOperand;

	function __construct($path, $relativeAge, $compareOperand = '>', $checkType = null, $checkFn = 'filemtime') {
		$this->path = $path;
		$this->relativeAge = $relativeAge;
		$this->checkFn = $checkFn;
		$this->checkType = ($checkType) ? $checkType : self::CHECK_SINGLE;
		$this->compareOperand = $compareOperand;
	}

	function check() {
		$cutoffTime =  strtotime($this->relativeAge, SS_Datetime::now()->Format('U'));
		$files = $this->getFiles();
		$invalidFiles = array();
		$validFiles = array();
		$checkFn = $this->checkFn;
		$allValid = true;
		if($files) foreach($files as $file) {
			$fileTime = $checkFn($file);
			$valid = ($this->compareOperand == '>') ? ($fileTime >= $cutoffTime) : ($fileTime <= $cutoffTime);
			if($valid) {
				$validFiles[] = $file;
			} else {
				$invalidFiles[] = $file;
				if($this->checkType == self::CHECK_ALL) {
					return array(
						EnvironmentCheck::ERROR,
						sprintf(
							'File "%s" doesn\'t match age check (compare %s: %s, actual: %s)', 
							$file, $this->compareOperand, date('c', $cutoffTime), date('c', $fileTime)
						)
					);	
				}
			}
		}

		// If at least one file was valid, count as passed
		if($this->checkType == self::CHECK_SINGLE && count($invalidFiles) < count($files)) {
			return array(EnvironmentCheck::OK, '');
		} else {
			return array(
				EnvironmentCheck::ERROR,
				sprintf('No files matched criteria (%s %s)', $this->compareOperand, date('c', $cutoffTime))
			);
		}
			
	}

	/**
	 * @return Array Of absolute file paths
	 */
	protected function getFiles() {
		return glob($this->path);
	}

}