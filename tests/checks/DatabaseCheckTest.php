<?php

/**
 * @mixin PHPUnit_Framework_TestCase
 */
class DatabaseCheckTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testCheckReportsValidConnection()
    {
        $check = new DatabaseCheck();

        $expected = array(
            EnvironmentCheck::OK,
            '',
        );

        $this->assertEquals($expected, $check->check());
    }
}
