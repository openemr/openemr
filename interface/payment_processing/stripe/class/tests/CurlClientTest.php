<?php

namespace Stripe;

use Stripe\HttpClient\CurlClient;

class CurlClientTest extends TestCase
{
    public function testEncode()
    {
        $a = array(
            'my' => 'value',
            'that' => array('your' => 'example'),
            'bar' => 1,
            'baz' => null
        );

        $enc = CurlClient::encode($a);
        $this->assertSame('my=value&that%5Byour%5D=example&bar=1', $enc);

        $a = array('that' => array('your' => 'example', 'foo' => null));
        $enc = CurlClient::encode($a);
        $this->assertSame('that%5Byour%5D=example', $enc);

        $a = array('that' => 'example', 'foo' => array('bar', 'baz'));
        $enc = CurlClient::encode($a);
        $this->assertSame('that=example&foo%5B%5D=bar&foo%5B%5D=baz', $enc);

        $a = array(
            'my' => 'value',
            'that' => array('your' => array('cheese', 'whiz', null)),
            'bar' => 1,
            'baz' => null
        );

        $enc = CurlClient::encode($a);
        $expected = 'my=value&that%5Byour%5D%5B%5D=cheese'
              . '&that%5Byour%5D%5B%5D=whiz&bar=1';
        $this->assertSame($expected, $enc);

        // Ignores an empty array
        $enc = CurlClient::encode(array('foo' => array(), 'bar' => 'baz'));
        $expected = 'bar=baz';
        $this->assertSame($expected, $enc);
    }
}
