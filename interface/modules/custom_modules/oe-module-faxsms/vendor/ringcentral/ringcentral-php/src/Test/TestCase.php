<?php

namespace RingCentral\SDK\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RingCentral\SDK\SDK;

abstract class TestCase extends BaseTestCase
{

    protected function createResponse($method = 'GET', $path = '', array $json = [], $status = 200)
    {

        $res = new Response($status, ['content-type' => 'application/json'], json_encode($json));

        //if ($status >= 400) {
        //    return new RequestException(
        //        "Error Communicating with Server",
        //        new Request($method, $path),
        //        $res
        //    );
        //}

        return $res;

    }

    protected function createGuzzle(array $responses = [])
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        return new Client(['handler' => $handler]);

    }

    protected function getSDK(array $responses = [], $authorized = true)
    {

        date_default_timezone_set('UTC');

        if ($authorized) {
            $responses = array_merge([$this->authenticationMock()], $responses);
        }

        $guzzle = $this->createGuzzle($responses);

        $sdk = new SDK('whatever', 'whatever', 'https://whatever', 'SDKTests', SDK::VERSION, $guzzle);

        if ($authorized) {
            $sdk->platform()->login([
                'jwt' => 'whatever',
            ]);
        }

        return $sdk;

    }

    function authenticationMock()
    {
        return $this->createResponse('POST', '/restapi/oauth/token', [
            'access_token'             => 'ACCESS_TOKEN',
            'token_type'               => 'bearer',
            'expires_in'               => 3600,
            'refresh_token'            => 'REFRESH_TOKEN',
            'refresh_token_expires_in' => 60480,
            'scope'                    => 'SMS RCM Foo Boo',
            'expireTime'               => time() + 3600,
            'owner_id'                 => 'foo'
        ]);
    }

    function logoutMock()
    {
        return $this->createResponse('POST', '/restapi/oauth/revoke', []);
    }

    function presenceSubscriptionMock($id = '1', $detailed = true)
    {

        $expiresIn = 15 * 60 * 60;

        return $this->createResponse('POST', '/restapi/v1.0/subscription', [
            'eventFilters'   => ['/restapi/v1.0/account/~/extension/' . $id . '/presence' . ($detailed ? '?detailedTelephonyState=true' : '')],
            'expirationTime' => date('c', time() + $expiresIn),
            'expiresIn'      => $expiresIn,
            'deliveryMode'   => [
                'transportType'       => 'PubNub',
                'encryption'          => true,
                'address'             => '123_foo',
                'subscriberKey'       => 'sub-c-foo',
                'secretKey'           => 'sec-c-bar',
                'encryptionAlgorithm' => 'AES',
                'encryptionKey'       => 'e0bMTqmumPfFUbwzppkSbA=='
            ],
            'creationTime'   => date('c'),
            'id'             => 'foo-bar-baz',
            'status'         => 'Active',
            'uri'            => 'https=>//platform.ringcentral.com/restapi/v1.0/subscription/foo-bar-baz'
        ]);

    }

    function refreshMock($failure = false, $expiresIn = 3600)
    {

        $body = !$failure
            ? [
                'access_token'             => 'ACCESS_TOKEN_FROM_REFRESH',
                'token_type'               => 'bearer',
                'expires_in'               => $expiresIn,
                'refresh_token'            => 'REFRESH_TOKEN_FROM_REFRESH',
                'refresh_token_expires_in' => 60480,
                'scope'                    => 'SMS RCM Foo Boo',
                'expireTime'               => time() + 3600,
                'owner_id'                 => 'foo'
            ]
            : ['message' => 'Wrong token (mock)'];

        $status = !$failure ? 200 : 400;

        return $this->createResponse('POST', '/restapi/oauth/token', $body, $status);

    }

    function subscriptionMock(
        $expiresIn = 54000,
        array $eventFilters = ['/restapi/v1.0/account/~/extension/1/presence']
    ) {

        return $this->createResponse('POST', '/restapi/v1.0/subscription', [
            'eventFilters'   => $eventFilters,
            'expirationTime' => date('c', time() + $expiresIn),
            'expiresIn'      => $expiresIn,
            'deliveryMode'   => [
                'transportType' => 'PubNub',
                'encryption'    => false,
                'address'       => '123_foo',
                'subscriberKey' => 'sub-c-foo',
                'secretKey'     => 'sec-c-bar'
            ],
            'id'             => 'foo-bar-baz',
            'creationTime'   => date('c'),
            'status'         => 'Active',
            'uri'            => 'https=>//platform.ringcentral.com/restapi/v1.0/subscription/foo-bar-baz'
        ]);

    }

}
