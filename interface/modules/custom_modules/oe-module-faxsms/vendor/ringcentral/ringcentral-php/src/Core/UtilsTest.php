<?php

use RingCentral\SDK\Core\Utils;
use RingCentral\SDK\Test\TestCase;

class UtilsTest extends TestCase
{
    public function testDepth()
    {

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON Error: Maximum stack depth exceeded');

        Utils::json_parse('{"foo":{"bar":{"baz":"qux"}}}', false, 2);
    }

    /**
     * Test padded AES result
     */
    public function testControl()
    {

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON Error: Unexpected control character found');

        Utils::json_parse("{\"foo\":\"bar\"}\0");
    }

}