<?php

namespace SilverStripe\EnvironmentCheck\Tests\Checks;




use SilverStripe\EnvironmentCheck\Checks\FileWriteableCheck;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\Dev\SapphireTest;



/**
 * @mixin PHPUnit_Framework_TestCase
 */
class FileWritableCheckTest extends SapphireTest
{
    public function testCheckReportsWritablePaths()
    {
        $check = new FileWriteableCheck(TEMP_FOLDER);

        $expected = array(
            EnvironmentCheck::OK,
            '',
        );

        $this->assertEquals($expected, $check->check());
    }

    public function testCheckReportsNonWritablePaths()
    {
        $check = new FileWriteableCheck('/var');

        $result = $check->check();

        $this->assertEquals(EnvironmentCheck::ERROR, $result[0]);
    }
}
