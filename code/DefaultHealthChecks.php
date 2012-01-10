<?php

/**
 * This file contains a number of default environment checks that you can use.
 */


/**
 * Check that the connection to the database is working, by looking for records in some table.
 * By default, Member will be checked.
 * 
 * @param $checkTable The table that will be checked.
 */
class DatabaseCheck implements EnvironmentCheck {
	protected $checkTable;
	
	function __construct($checkTable = "Member") {
		$this->checkTable = $checkTable;
	}
	
	function check() {
		$count = DB::query("SELECT COUNT(*) FROM \"$this->checkTable\"")->value();
		
		if($count > 0) {
			return array(EnvironmentCheck::OK, "");
		} else {
			return array(EnvironmentCheck::WARNING, "$this->checkTable queried ok but has no records");
		}
	}
}

/**
 * Check that a given URL is functioning, by default, the homepage.
 * 
 * Note that Director::test() will be used rather than a CURL check.
 */
class URLCheck implements EnvironmentCheck {
	protected $url;
	protected $testString;
	
	/*
	 * @param $url The URL to check, relative to the site.  "" is the homepage.
	 * @param $testString A piece of text to optionally search for in the homepage HTML.  If omitted, no such check is made.
	 */
	function __construct($url = "", $testString = "") {
		$this->url = $url;
		$this->testString = $testString;
	}
	
	function check() {
		$response = Director::test($this->url);

		if($response->getStatusCode() != 200) {
			return array(EnvironmentCheck::ERROR, "Homepage requested and returned HTTP " . $response->getStatusCode() . " response");

		} else if($this->testString && (strpos($response->getBody(), $this->testString) === false)) {
			return array(EnvironmentCheck::WARNING, "Homepage requested ok but '$testString' not found.");

		} else {
			return array(EnvironmentCheck::OK, "");
		}
	}
}

/**
 * Check that the given function exists.
 * This can be used to check that PHP modules or features are installed.
 * @param $functionName The name of the function to look for.
 */
class HasFunctionCheck implements EnvironmentCheck {
	protected $functionName;
	
	function __construct($functionName) {
		$this->functionName = $functionName;
	}
	
	function check() {
		if(function_exists($this->functionName)) return array(EnvironmentCheck::OK, $this->functionName.'() exists');
		else return array(EnvironmentCheck::ERROR, $this->functionName.'() doesn\'t exist');
	}
}

/**
 * Check that the given class exists.
 * This can be used to check that PHP modules or features are installed.
 * @param $className The name of the class to look for.
 */
class HasClassCheck implements EnvironmentCheck {
	protected $className;
	
	function __construct($className) {
		$this->className = $className;
	}
	
	function check() {
		if(class_exists($this->className)) return array(EnvironmentCheck::OK, 'Class ' . $this->className.' exists');
		else return array(EnvironmentCheck::ERROR, 'Class ' . $this->className.' doesn\'t exist');
	}
}

/**
 * Check that the given file is writeable.
 * This can be used to check that the environment doesn't have permission set-up errors.
 * @param $path The full path.  If a relative path, it will relative to the BASE_PATH
 */
class FileWriteableCheck implements EnvironmentCheck {
	protected $path;
	
	function __construct($path) {
		$this->path = $path;
	}
	
	function check() {
		if($this->path[0] == '/') $filename = $this->path;
		else $filename = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $this->path);
		
		if(file_exists($filename)) $isWriteable = is_writeable($filename);
		else $isWriteable = is_writeable(dirname($filename));
		
		if(!$isWriteable) {
			if(function_exists('posix_getgroups')) {
				$userID = posix_geteuid();
				$user = posix_getpwuid($userID);

				$currentOwnerID = fileowner(file_exists($filename) ? $filename : dirname($filename) );
				$currentOwner = posix_getpwuid($currentOwnerID);

				$message = "User '$user[name]' needs to be able to write to this file:\n$filename\n\nThe file is currently owned by '$currentOwner[name]'.  ";

				if($user['name'] == $currentOwner['name']) {
					$message .= "We recommend that you make the file writeable.";
				} else {
					
					$groups = posix_getgroups();
					$groupList = array();
					foreach($groups as $group) {
						$groupInfo = posix_getgrgid($group);
						if(in_array($currentOwner['name'], $groupInfo['members'])) $groupList[] = $groupInfo['name'];
					}
					if($groupList) {
						$message .= "	We recommend that you make the file group-writeable and change the group to one of these groups:\n - ". implode("\n - ", $groupList)
							. "\n\nFor example:\nchmod g+w $filename\nchgrp " . $groupList[0] . " $filename";  		
					} else {
						$message .= "  There is no user-group that contains both the web-server user and the owner of this file.  Change the ownership of the file, create a new group, or temporarily make the file writeable by everyone during the install process.";
					}
				}

			} else {
				$message .= "The webserver user needs to be able to write to this file:\n$filename";
			}
			
			return array(EnvironmentCheck::ERROR, $message);
		}

		return array(EnvironmentCheck::OK,'');
	}
}