<?php

namespace kamermans\OAuth2\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\Subscriber\History;

use kamermans\OAuth2\Utils\Helper;
use kamermans\OAuth2\OAuth2Middleware;

class OAuth2MiddlewareTest extends BaseTestCase
{
    public function setUp()
    {
        if (Helper::guzzleIs('<', 6)) {
            $this->markTestSkipped("Guzzle 6+ is required for this test");
        }
    }

    public function testConstruct()
    {
        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            //->setConstructorArgs([$client, []])
            ->disableOriginalConstructor()
            ->getMock();

        $sub = new OAuth2Middleware($grant);
    }

    public function testDoesNotTriggerForNonOAuthRequests()
    {
        $reauth_container = [];
        $reauth_history = Middleware::history($reauth_container);

        // Setup Reauthorization Client
        $reauth_responder = new MockHandler([
            new Psr7Response(200, [], json_encode(['access_token' => 'foobar'])),
        ]);

        $reauth_handler = HandlerStack::create($reauth_responder);
        $reauth_handler->push($reauth_history);

        $reauth_client = new Client([
            'handler'  => $reauth_handler,
            'base_uri' => 'http://localhost:11000/oauth_token',
        ]);

        // Setup User Client
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

        $grant = new \kamermans\OAuth2\GrantType\ClientCredentials($reauth_client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\AccessToken\BearerAuth();

        // Setup OAuth2Middleware
        $sub = new OAuth2Middleware($grant);
        $sub->setAccessTokenSigner($signer);

        $handler->push($sub);
        $handler->push($history);

        $client = new Client([
            'handler'  => $handler,
            'base_uri' => 'http://localhost:10000/api/v1',
            //'auth' => 'oauth',
        ]);

        $response = $client->get('/');

        $this->assertEmpty($reauth_container);
        $this->assertCount(1, $container);

        $this->assertSame('', $this->getHeader($container[0]['request'], 'Authorization'), "The request should not have been signed");
    }

    public function testTriggersSignerAndGrantDataProcessor()
    {

        // A random access token helps avoid false pasitives due to caching
        $mock_access_token = md5(microtime(true).mt_rand(100000, 999999));

        $reauth_container = [];
        $reauth_history = Middleware::history($reauth_container);

        // Setup Reauthorization Client
        $reauth_responder = new MockHandler([
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),
        ]);

        $reauth_handler = HandlerStack::create($reauth_responder);
        $reauth_handler->push($reauth_history);

        $reauth_client = new Client([
            'handler'  => $reauth_handler,
            'base_uri' => 'http://localhost:11000/oauth_token',
        ]);

        // Setup User Client
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

        $grant = new \kamermans\OAuth2\GrantType\ClientCredentials($reauth_client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\AccessToken\BearerAuth();

        // Setup OAuth2Middleware
        $sub = new OAuth2Middleware($grant);
        $sub->setAccessTokenSigner($signer);

        $handler->push($sub);
        $handler->push($history);

        $client = new Client([
            'handler'  => $handler,
            'base_uri' => 'http://localhost:10000/api/v1',
            'auth' => 'oauth',
        ]);

        $response = $client->get('/');

        $this->assertCount(1, $reauth_container);
        $this->assertCount(1, $container);

        // This proves that the access_token received from the reauth_client was used to authenticate this response
        $expected_auth_value = "Bearer $mock_access_token";
        $this->assertSame($expected_auth_value, $this->getHeader($container[0]['request'], 'Authorization'));
    }

    /**
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function testOnErrorDoesNotTriggerForNonOAuthRequests()
    {

        // A random access token helps avoid false pasitives due to caching
        $mock_access_token = md5(microtime(true).mt_rand(100000, 999999));

        $reauth_container = [];
        $reauth_history = Middleware::history($reauth_container);

        // Setup Reauthorization Client
        $reauth_responder = new MockHandler([
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),
        ]);

        $reauth_handler = HandlerStack::create($reauth_responder);
        $reauth_handler->push($reauth_history);

        $reauth_client = new Client([
            'handler'  => $reauth_handler,
            'base_uri' => 'http://localhost:11000/oauth_token',
        ]);

        // Setup User Client
        $response_data = [
            'foo' => 'bar',
            'key' => 'value',
        ];

        $responder = new MockHandler([
            // First request fails with 401, forcing a reauth
            new Psr7Response(401, [], json_encode($response_data)),
            // Second request succeeds because there is a new token
            new Psr7Response(200, [], json_encode($response_data)),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create($responder);

        $grant = new \kamermans\OAuth2\GrantType\ClientCredentials($reauth_client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\AccessToken\BearerAuth();

        // Setup OAuth2Middleware
        $sub = new OAuth2Middleware($grant);
        $sub->setAccessTokenSigner($signer);

        $handler->push($sub);
        $handler->push($history);

        $client = new Client([
            'handler'  => $handler,
            'base_uri' => 'http://localhost:10000/api/v1',
//            'auth' => 'oauth',
        ]);

        $response = $client->get('/');

        $this->assertCount(0, $reauth_container);
        $this->assertCount(1, $container);
    }

    public function testOnErrorDoesTriggerForOAuthRequests()
    {

        // A random access token helps avoid false pasitives due to caching
        $mock_access_token = md5(microtime(true).mt_rand(100000, 999999));

        $reauth_container = [];
        $reauth_history = Middleware::history($reauth_container);

        // Setup Reauthorization Client
        $reauth_responder = new MockHandler([
            // This token is returned and used for the first request
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),
            // The endpoint returned 401, so the previous token was deleted and this one is fetched:
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),
        ]);

        $reauth_handler = HandlerStack::create($reauth_responder);
        $reauth_handler->push($reauth_history);

        $reauth_client = new Client([
            'handler'  => $reauth_handler,
            'base_uri' => 'http://localhost:11000/oauth_token',
        ]);

        // Setup User Client
        $response_data = [
            'foo' => 'bar',
            'key' => 'value',
        ];

        $responder = new MockHandler([
            new Psr7Response(401, [], json_encode($response_data)),
            new Psr7Response(200, [], json_encode($response_data)),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create($responder);

        $grant = new \kamermans\OAuth2\GrantType\ClientCredentials($reauth_client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\AccessToken\BearerAuth();

        // Setup OAuth2Middleware
        $sub = new OAuth2Middleware($grant);
        $sub->setAccessTokenSigner($signer);

        $handler->push($sub);
        $handler->push($history);

        $client = new Client([
            'handler'  => $handler,
            'base_uri' => 'http://localhost:10000/api/v1',
            'auth' => 'oauth',
        ]);

        $response = $client->get('/');

        $this->assertCount(2, $reauth_container);
        $this->assertCount(2, $container);

        // This proves that the access_token received from the reauth_client was used to authenticate this response
        $expected_auth_value = "Bearer $mock_access_token";
        $this->assertSame($expected_auth_value, $this->getHeader($container[0]['request'], 'Authorization'));

        // Note that if we didn't catch the HTTP 401, it would have thrown an exception
        $this->assertSame(401, $container[0]['response']->getStatusCode());
        $this->assertSame(200, $container[1]['response']->getStatusCode());
    }

    public function testOnErrorDoesNotTriggerForNon401Requests()
    {

        // A random access token helps avoid false pasitives due to caching
        $mock_access_token = md5(microtime(true).mt_rand(100000, 999999));

        $reauth_container = [];
        $reauth_history = Middleware::history($reauth_container);

        // Setup Reauthorization Client
        $reauth_responder = new MockHandler([
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),
        ]);

        $reauth_handler = HandlerStack::create($reauth_responder);
        $reauth_handler->push($reauth_history);

        $reauth_client = new Client([
            'handler'  => $reauth_handler,
            'base_uri' => 'http://localhost:11000/oauth_token',
        ]);

        // Setup User Client
        $response_data = [
            'foo' => 'bar',
            'key' => 'value',
        ];

        $responder = new MockHandler([
            new Psr7Response(402, [], json_encode($response_data)),
            new Psr7Response(404, [], json_encode($response_data)),
            new Psr7Response(500, [], json_encode($response_data)),
            new Psr7Response(503, [], json_encode($response_data)),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create($responder);

        $grant = new \kamermans\OAuth2\GrantType\ClientCredentials($reauth_client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\AccessToken\BearerAuth();

        // Setup OAuth2Middleware
        $sub = new OAuth2Middleware($grant);
        $sub->setAccessTokenSigner($signer);

        $handler->push($sub);
        $handler->push($history);

        $client = new Client([
            'handler'  => $handler,
            'base_uri' => 'http://localhost:10000/api/v1',
            'auth' => 'oauth',
        ]);

        $exceptions = 0;
        for ($i=0; $i<4; $i++) {
            try {
                $response = $client->get('/');
            } catch (\Exception $e) {
                $exceptions++;
            }
        }

        $this->assertSame(4, $exceptions);
        $this->assertCount(4, $container);
        $this->assertCount(1, $reauth_container);

        // This proves that the access_token received from the reauth_client was used to authenticate this response
        $expected_auth_value = "Bearer $mock_access_token";
        $this->assertSame($expected_auth_value, $this->getHeader($container[0]['request'], 'Authorization'));
        $this->assertSame($expected_auth_value, $this->getHeader($container[1]['request'], 'Authorization'));
        $this->assertSame($expected_auth_value, $this->getHeader($container[2]['request'], 'Authorization'));
        $this->assertSame($expected_auth_value, $this->getHeader($container[3]['request'], 'Authorization'));
    }

    public function testTokenPersistenceIsUsed()
    {

        // A random access token helps avoid false pasitives due to caching
        $mock_access_token_cached = md5(microtime(true).mt_rand(100000, 999999));
        $mock_access_token = md5(microtime(true).mt_rand(100000, 999999));

        $cached_token = new \kamermans\OAuth2\Token\RawToken($mock_access_token_cached);

        $reauth_container = [];
        $reauth_history = Middleware::history($reauth_container);

        // Setup Reauthorization Client
        $reauth_responder = new MockHandler([
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),
        ]);

        $reauth_handler = HandlerStack::create($reauth_responder);
        $reauth_handler->push($reauth_history);

        $reauth_client = new Client([
            'handler'  => $reauth_handler,
            'base_uri' => 'http://localhost:11000/oauth_token',
        ]);

        // Setup User Client
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

        $grant = new \kamermans\OAuth2\GrantType\ClientCredentials($reauth_client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\AccessToken\BearerAuth();

        $mock_persistence = $this->getMockBuilder('\kamermans\OAuth2\Persistence\TokenPersistenceInterface')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();

        $mock_persistence->method('restoreToken')
            ->willReturn($cached_token);

        // Setup OAuth2Middleware
        $sub = new OAuth2Middleware($grant);
        $sub->setAccessTokenSigner($signer);
        $sub->setTokenPersistence($mock_persistence);

        $handler->push($sub);
        $handler->push($history);

        $client = new Client([
            'handler'  => $handler,
            'base_uri' => 'http://localhost:10000/api/v1',
            'auth' => 'oauth',
        ]);

        $response = $client->get('/');

        $this->assertCount(1, $container);
        $this->assertCount(0, $reauth_container);

        $this->assertNotSame($mock_access_token, $mock_access_token_cached);

        // This proves that the access_token received from the Persistence was used to authenticate this response, not the one from reauth
        $expected_auth_value = "Bearer $mock_access_token_cached";
        $this->assertSame($expected_auth_value, $this->getHeader($container[0]['request'], 'Authorization'));
    }

    public function testOnErrorDoesNotLoop()
    {
        // A random access token helps avoid false pasitives due to caching
        $mock_access_token = md5(microtime(true).mt_rand(100000, 999999));

        $reauth_container = [];
        $reauth_history = Middleware::history($reauth_container);

        // Setup Reauthorization Client
        $reauth_responder = new MockHandler([
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),
            new Psr7Response(200, [], json_encode([
                'access_token' => $mock_access_token,
            ])),

            // If we make it this far, there is a looping problem
            new Psr7Response(500, []),

        ]);

        $reauth_handler = HandlerStack::create($reauth_responder);
        $reauth_handler->push($reauth_history);

        $reauth_client = new Client([
            'handler'  => $reauth_handler,
            'base_uri' => 'http://localhost:11000/oauth_token',
        ]);

        // Setup User Client
        $response_data = [
            'foo' => 'bar',
            'key' => 'value',
        ];

        $responder = new MockHandler([
            new Psr7Response(401, [], json_encode($response_data)),
            new Psr7Response(401, [], json_encode($response_data)),
            new Psr7Response(401, [], json_encode($response_data)),
            // If we make it this far, there is a looping problem
            new Psr7Response(500, []),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create($responder);

        $grant = new \kamermans\OAuth2\GrantType\ClientCredentials($reauth_client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'scope' => 'foo,bar',
        ]);

        $signer = new \kamermans\OAuth2\Signer\AccessToken\BearerAuth();

        // Setup OAuth2Middleware
        $sub = new OAuth2Middleware($grant);
        $sub->setAccessTokenSigner($signer);

        $handler->push($sub);
        $handler->push($history);

        $client = new Client([
            'handler'  => $handler,
            'base_uri' => 'http://localhost:10000/api/v1',
            'auth' => 'oauth',
        ]);

        try {
            $response = $client->get('/');
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // We let the ServerException (the Error 500) bubble up to PHPUnit
        }

        $this->assertCount(2, $reauth_container);
        $this->assertCount(2, $container);

        // This proves that the access_token received from the reauth_client was used to authenticate this response
        $expected_auth_value = "Bearer $mock_access_token";
        $this->assertSame($expected_auth_value, $this->getHeader($container[0]['request'], 'Authorization'));

        // Note that if we didn't catch the HTTP 401, it would have thrown an exception
        $this->assertSame(401, $container[0]['response']->getStatusCode());
        $this->assertSame(401, $container[1]['response']->getStatusCode());
    }

    public function __DISABLED__testOnErrorDoesNotLoop()
    {
        // Setup Grant Type
        $grant = $this->getMockBuilder('\kamermans\OAuth2\GrantType\ClientCredentials')
            ->setMethods(['getRawData'])
            ->disableOriginalConstructor()
            ->getMock();

        $grant->expects($this->exactly(0))
            ->method('getRawData');

        // Setup OAuth2Middleware
        $sub = new OAuth2Middleware($grant);

        $client = new Client();
        $request = new Request('GET', '/', [], null, ['auth' => 'oauth']);
        // This header keeps the subscriber from trying to reauth a reauth request (infinte loop)
        $request->setHeader('X-Guzzle-Retry', 1);
        $response = new Response(401);
        $transaction = $this->getTransaction($client, $request);
        $except = new RequestException('foo', $request, $response);
        $event = new ErrorEvent($transaction, $except);

        // Force an onError event, which triggers the signer and grant data processor
        $sub->onError($event);
    }
}
