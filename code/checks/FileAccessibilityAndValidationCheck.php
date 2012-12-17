<?php
/**
 * Checks for the accessiblility and tiletype validation of one or more files or folders.
 *
 * Examples:
 * // Checks /assets/caluclator_files has .json files and all files are valid json files.
	* EnvironmentCheckSuite::register('check', 'FileAccessibilityAndValidationCheck("' . BASE_PATH . '/assets/caluclator_files/*.json",
 *  "jsonValidate", '.FileAccessibilityAndValidationCheck::CHECK_ALL.')', 'Check a json file exist and are all valid json files'
 * );
 * 
 * // Checks /assets/caluclator_files/calculator.json exists and is valid json file.
 * EnvironmentCheckSuite::register('check', 'FileAccessibilityAndValidationCheck("' . BASE_PATH . '/assets/caluclator_files/calculator.json",
 *  "jsonValidate", '.FileAccessibilityAndValidationCheck::CHECK_SINGLE.')', 'Check a calculator.json exists and is valid json file'
 * );
 *
 * // Check only existence 
 * EnvironmentCheckSuite::register('check', 'FileAccessibilityAndValidationCheck("' . BASE_PATH . '/assets/caluclator_files/calculator.json")', 
 * 'Check a calculator.json exists only'
 * );
 */
class FileAccessibilityAndValidationCheck implements EnvironmentCheck {

	const CHECK_SINGLE = 1;

	const CHECK_ALL = 2;

	/**
	 * @var String Absolute path to a file or folder, compatible with glob().
	 */
	protected $path;

	/**
	 * @var Int Constant, check for a single file to match age criteria, or all of them.
	 */
	protected $fileTypeValidateFunc;

		/**
	 * @var Int Constant, check for a single file to match age criteria, or all of them.
	 */
	protected $checkType;

	function __construct($path, $fileTypeValidateFunc = 'noVidation', $checkType = null) {
		$this->path = $path;
		$this->fileTypeValidateFunc = ($fileTypeValidateFunc)? $fileTypeValidateFunc:'noVidation';
		$this->checkType = ($checkType) ? $checkType : self::CHECK_SINGLE;
	}

	function check() {
		$origStage = Versioned::get_reading_mode();
		Versioned::set_reading_mode('Live');

		$files = $this->getFiles();
		if($files){
			$fileTypeValidateFunc = $this->fileTypeValidateFunc;
			if(method_exists ($this, $fileTypeValidateFunc)){
				$invalidFiles = array();
				$validFiles = array();

				foreach($files as $file){
					if($this->$fileTypeValidateFunc($file)){
						$validFiles[] = $file;
					}else{
						$invalidFiles[] = $file;
					}
				}

				// If at least one file was valid, count as passed
				if($this->checkType == self::CHECK_SINGLE && count($invalidFiles) < count($files)) {
					$validFileList = "\n";
					foreach($validFiles as $vf){
						$validFileList .= $vf."\n";
					}
					if($fileTypeValidateFunc == 'noVidation') {
						$checkReturn = array(
							EnvironmentCheck::OK,
							sprintf('At least these file(s) accessible: %s', $validFileList)
						);
					}else{
						$checkReturn = array(
							EnvironmentCheck::OK,
							sprintf('At least these file(s) passed file type validate function "%s": %s', $fileTypeValidateFunc, $validFileList)
						);
					}
				} else {
					if (count($invalidFiles) == 0) $checkReturn = array(EnvironmentCheck::OK, 'All files valideted');
					else {
						$invalidFileList = "\n";
						foreach($invalidFiles as $vf){
							$invalidFileList .= $vf."\n";
						}

						if($fileTypeValidateFunc == 'noVidation'){
							$checkReturn = array(
								EnvironmentCheck::ERROR,
								sprintf('File(s) not accessible: %s', $invalidFileList)
							);
						}else{
							$checkReturn = array(
								EnvironmentCheck::ERROR,
								sprintf('File(s) not passing the file type validate function "%s": %s', $fileTypeValidateFunc, $invalidFileList)
							);
						}
	
					}
				}
			}else{
				$checkReturn =  array(
					EnvironmentCheck::ERROR,
					sprintf("Invalid file type validation method name passed: %s ", $fileTypeValidateFunc)
				);
			}

		}else{
			$checkReturn = array(
				EnvironmentCheck::ERROR,
				sprintf("No files accessible at path %s", $this->path)
			);
		}

		Versioned::set_reading_mode($origStage);

		return $checkReturn;
	}

	private function jsonValidate($file){
		$json = json_decode(file_get_contents($file));
		if(!$json) {
			return false;
		}else{
			return true;
		}
	}

	protected function noVidation($file) {
		return true;
	}

	/**
	 * @return Array Of absolute file paths
	 */
	protected function getFiles() {
		return glob($this->path);
	}

}