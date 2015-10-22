<?php

namespace Hautelook\SentryClient\Tests\Request\Interfaces;

use Hautelook\SentryClient\Request\Interfaces\StackTrace;

class StackTraceTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $stackTrace = new StackTrace(array());

        $this->assertEquals(array('frames' => new \ArrayObject()), $stackTrace->toArray());
        $this->assertSame('{"frames":{}}', json_encode($stackTrace->toArray()));
    }
}
