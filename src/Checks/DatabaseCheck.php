<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\ORM\DB;

/**
 * Check that the connection to the database is working, by ensuring that the table exists and that
 * the table contains some records.
 *
 * @package environmentcheck
 */
class DatabaseCheck implements EnvironmentCheck
{
    /**
     * @var string
     */
    protected $checkTable;

    /**
     * By default, Member will be checked.
     *
     * @param string $checkTable
     */
    public function __construct($checkTable = 'Member')
    {
        $this->checkTable = $checkTable;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function check()
    {
        if (!DB::get_schema()->hasTable($this->checkTable)) {
            return [EnvironmentCheck::ERROR, "$this->checkTable not present in the database"];
        }

        $count = DB::query("SELECT COUNT(*) FROM \"$this->checkTable\"")->value();

        if ($count > 0) {
            return [EnvironmentCheck::OK, ''];
        }

        return [EnvironmentCheck::WARNING, "$this->checkTable queried ok but has no records"];
    }
}
