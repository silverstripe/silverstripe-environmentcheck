<?php
/**
 * This file contains a number of default environment checks that you can use.
 */


/**
 * Check that the connection to the database is working, by ensuring that the table exists and that
 * the table contain some records.
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

		if(!DB::getConn()->hasTable($this->checkTable)) {
			return array(EnvironmentCheck::ERROR, "$this->checkTable not present in the database");
		}

		$count = DB::query("SELECT COUNT(*) FROM \"$this->checkTable\"")->value();
		
		if($count > 0) {
			return array(EnvironmentCheck::OK, "");
		} else {
			return array(EnvironmentCheck::WARNING, "$this->checkTable queried ok but has no records");
		}
	}
}