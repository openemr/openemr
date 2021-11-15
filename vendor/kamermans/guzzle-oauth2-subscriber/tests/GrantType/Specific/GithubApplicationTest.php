<?php

namespace kamermans\OAuth2\Tests\GrantType;

use kamermans\OAuth2\Tests\BaseTestCase;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\Subscriber\Mock as MockResponder;
use GuzzleHttp\Subscriber\History;
use kamermans\OAuth2\Utils\Helper;
use kamermans\OAuth2\GrantType\Specific\GithubApplication;

class GithubApplicationTest extends BaseTestCase
{
    public function testConstruct()
    {
        $grant = new GithubApplication(new Client(), [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'username' => 'bilbo',
            'password' => 'baggins',
            'note' => 'github test',
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Config is missing the following keys
     */
    public function testConstructThrowsForMissing()
    {
        $grant = new GithubApplication(new Client(), []);
    }

    public function testGetRawData()
    {
        if (Helper::guzzleIs('<', 6)) {
            $this->doGetRawDataLegacy();
        } else {
            $this->doGetRawData6Plus();
        }
    }

    protected function doGetRawData6Plus()
    {
        $response_data = [
            'foo' => 'bar',
            // GitHub responds with "token" instead of "access_token"
            'token' => '0123456789abcdef',
        ];

        $responder = new MockHandler([
            new Psr7Response(200, [], json_encode($response_data)),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $handler = HandlerStack::create($responder);
        $handler->push($history);

        $client = new Client([
            'handler'  => $handler,
            'base_uri' => 'http://localhost:10000/oauth_token',
        ]);

        $grant = new GithubApplication($client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'username' => 'bilbo',
            'password' => 'baggins',
            'note' => 'github test',
        ]);

        $signer = new \kamermans\OAuth2\Signer\ClientCredentials\BasicAuth();

        $raw_data = $grant->getRawData($signer);

        $this->assertEquals('bar', $raw_data['foo']);
        $this->assertEquals('0123456789abcdef', $raw_data['access_token']);

        $this->assertNotEmpty($container);
        $request_body = json_decode($container[0]['request']->getBody(), true);
        $this->assertEquals('foo', $request_body['client_id']);
        $this->assertEquals('bar', $request_body['client_secret']);
        $this->assertEquals('github test', $request_body['note']);
    }

    protected function doGetRawDataLegacy()
    {
        $response_data = [
            'foo' => 'bar',
            // GitHub responds with "token" instead of "access_token"
            'token' => '0123456789abcdef',
        ];
        $response = new Response(200, [], Stream::factory(json_encode($response_data)));

        $responder = new MockResponder([$response]);
        $history = new History();

        $client = new Client();
        $client->getEmitter()->attach($responder);
        $client->getEmitter()->attach($history);

        $grant = new GithubApplication($client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'username' => 'bilbo',
            'password' => 'baggins',
            'note' => 'github test',
        ]);

        $signer = $this->getMockBuilder('\kamermans\OAuth2\Signer\ClientCredentials\BasicAuth')
            ->setMethods(['sign'])
            ->getMock();

        $signer->expects($this->once())
            ->method('sign')
            ->with($this->anything(), 'bilbo', 'baggins');

        // Verify response data
        $raw_data = $grant->getRawData($signer);
        $this->assertEquals('bar', $raw_data['foo']);
        $this->assertEquals('0123456789abcdef', $raw_data['access_token']);

        // Verify request body data
        $request_body = json_decode($history->getLastRequest()->getBody(), true);
        $this->assertEquals('foo', $request_body['client_id']);
        $this->assertEquals('bar', $request_body['client_secret']);
        $this->assertEquals('github test', $request_body['note']);
    }
}
