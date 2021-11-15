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
use kamermans\OAuth2\GrantType\ClientCredentials;

class ClientCredentialsTest extends BaseTestCase
{
    public function testConstruct()
    {
        $grant = new ClientCredentials(new Client(), [
            'client_id' => 'foo',
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Config is missing the following keys
     */
    public function testConstructThrowsForMissing()
    {
        $grant = new ClientCredentials(new Client(), []);
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

        $grant = new ClientCredentials($client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\ClientCredentials\BasicAuth();

        $data = $grant->getRawData($signer);

        $this->assertNotEmpty($container);
        $request_body = $container[0]['request']->getBody();

        parse_str($request_body, $form_data);

        $this->assertEquals($response_data, $data);
        $this->assertEquals('foo,bar', $form_data['scope']);
        $this->assertEquals('client_credentials', $form_data['grant_type']);
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

        $grant = new ClientCredentials($client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\ClientCredentials\BasicAuth();

        $data = $grant->getRawData($signer);
        $request_body = $history->getLastRequest()->getBody();

        $this->assertEquals($response_data, $data);
        $this->assertEquals('foo,bar', $request_body->getField('scope'));
        $this->assertEquals('client_credentials', $request_body->getField('grant_type'));
    }
}
