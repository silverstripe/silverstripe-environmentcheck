<?php

/**
 * @mixin PHPUnit_Framework_TestCase
 */
class URLCheckTest extends SapphireTest
{
    public function testCheckReportsMissingPages()
    {
        $check = new URLCheck('foo', 'bar');

        $expected = array(
            EnvironmentCheck::ERROR,
            'Error retrieving "foo" (Code: 404)',
        );

        $this->assertEquals($expected, $check->check());
    }
}
