<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use EnvironmentCheck;
use DB;


/**
 * Check that the connection to the database is working, by ensuring that the table exists and that
 * the table contains some records.
 */
class DatabaseCheck implements EnvironmentCheck
{
    protected $checkTable;

    /**
     * By default, Member will be checked.
     *
     * @param string $checkTable
     */
    public function __construct($checkTable = "Member")
    {
        $this->checkTable = $checkTable;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function check()
    {
        if (!DB::getConn()->hasTable($this->checkTable)) {
            return array(EnvironmentCheck::ERROR, "$this->checkTable not present in the database");
        }

        $count = DB::query("SELECT COUNT(*) FROM \"$this->checkTable\"")->value();
        
        if ($count > 0) {
            return array(EnvironmentCheck::OK, "");
        } else {
            return array(EnvironmentCheck::WARNING, "$this->checkTable queried ok but has no records");
        }
    }
}
