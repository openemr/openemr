<?php

use RingCentral\SDK\Core\Utils;
use RingCentral\SDK\Test\TestCase;

class UtilsTest extends TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage JSON Error: Maximum stack depth exceeded
     */
    public function testDepth()
    {

        Utils::json_parse('{"foo":{"bar":{"baz":"qux"}}}', false, 2);

    }

    /**
     * Test padded AES result
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage JSON Error: Unexpected control character found
     */
    public function testControl()
    {

        Utils::json_parse("{\"foo\":\"bar\"}\0");

    }

}