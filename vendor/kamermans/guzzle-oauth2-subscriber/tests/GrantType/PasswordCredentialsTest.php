<?php

namespace kamermans\OAuth2\Tests\GrantType;

use kamermans\OAuth2\Tests\BaseTestCase;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\Subscriber\Mock as MockResponder;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Message\Response;
use kamermans\OAuth2\Utils\Helper;
use kamermans\OAuth2\GrantType\PasswordCredentials;

class PasswordCredentialsTest extends BaseTestCase
{
    public function testConstruct()
    {
        $grant = new PasswordCredentials(new Client(), [
            'client_id' => 'foo',
            'username' => 'bilbo',
            'password' => 'baggins',
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Config is missing the following keys
     */
    public function testConstructThrowsForMissing()
    {
        $grant = new PasswordCredentials(new Client(), []);
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
            'key' => 'value',
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

        $grant = new PasswordCredentials($client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'username' => 'bilbo',
            'password' => 'baggins',
        ]);

        $signer = new \kamermans\OAuth2\Signer\ClientCredentials\BasicAuth();

        $data = $grant->getRawData($signer);

        $this->assertNotEmpty($container);
        $request_body = $container[0]['request']->getBody();

        parse_str($request_body, $form_data);

        $this->assertEquals($response_data, $data);
        $this->assertEquals('bilbo', $form_data['username']);
        $this->assertEquals('baggins', $form_data['password']);
        $this->assertEquals('password', $form_data['grant_type']);
    }

    protected function doGetRawDataLegacy()
    {
        $response_data = [
            'foo' => 'bar',
            'key' => 'value',
        ];
        $response = new Response(200, [], Stream::factory(json_encode($response_data)));

        $responder = new MockResponder([$response]);
        $history = new History();

        $client = new Client();
        $client->getEmitter()->attach($responder);
        $client->getEmitter()->attach($history);

        $grant = new PasswordCredentials($client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'username' => 'bilbo',
            'password' => 'baggins',
        ]);

        $signer = $this->getMockBuilder('\kamermans\OAuth2\Signer\ClientCredentials\BasicAuth')
            ->setMethods(['sign'])
            ->getMock();

        $signer->expects($this->once())
            ->method('sign')
            ->with($this->anything(), 'foo', 'bar')
            ->will($this->returnArgument(0));

        $data = $grant->getRawData($signer);
        $request_body = $history->getLastRequest()->getBody();

        $this->assertEquals($response_data, $data);
        $this->assertEquals('bilbo', $request_body->getField('username'));
        $this->assertEquals('baggins', $request_body->getField('password'));
        $this->assertEquals('password', $request_body->getField('grant_type'));
    }
}
