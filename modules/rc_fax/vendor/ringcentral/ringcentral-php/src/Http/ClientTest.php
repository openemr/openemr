<?php

use RingCentral\SDK\Http\Client;
use RingCentral\SDK\Test\TestCase;

class ClientTest extends TestCase
{

    public function testQueryString()
    {

        $client = new Client($this->createGuzzle());

        $r = $client->createRequest('GET', 'http://whatever:8080/path', array('foo' => 'bar', 'baz' => 'qux', 'a2z' => array('abc', 'xyz')));

        $this->assertEquals('http', $r->getUri()->getScheme());
        $this->assertEquals('whatever', $r->getUri()->getHost());
        $this->assertEquals('8080', $r->getUri()->getPort());
        $this->assertEquals('/path', $r->getUri()->getPath());
        $this->assertEquals('foo=bar&baz=qux&a2z=abc&a2z=xyz', $r->getUri()->getQuery());

    }

    public function testURLEncoded()
    {

        $client = new Client($this->createGuzzle());

        $r = $client->createRequest('POST', 'http://whatever:8080/path', null, array('foo' => 'bar', 'baz' => 'qux'),
            array('content-type' => 'application/x-www-form-urlencoded'));

        $this->assertEquals('foo=bar&baz=qux', $r->getBody());

    }

    public function testJSON()
    {

        $client = new Client($this->createGuzzle());

        $r = $client->createRequest('POST', 'http://whatever', null, array('foo' => 'bar', 'baz' => 'qux'),
            array('content-type' => 'application/json'));

        $this->assertEquals('{"foo":"bar","baz":"qux"}', $r->getBody());

    }

    public function testJSONByDefault()
    {

        $client = new Client($this->createGuzzle());

        $r = $client->createRequest('POST', 'http://whatever', null, array('foo' => 'bar', 'baz' => 'qux'));

        $this->assertEquals('{"foo":"bar","baz":"qux"}', $r->getBody());

    }

    public function testFooContentType()
    {

        $client = new Client($this->createGuzzle());

        $r = $client->createRequest('POST', 'http://whatever', null, 'foo-encoded-text',
            array('content-type' => 'foo'));

        $this->assertEquals('foo-encoded-text', $r->getBody());

    }

}
