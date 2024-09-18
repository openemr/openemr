<?php

namespace RingCentral\SDK\WebSocket;

use Exception;
use RingCentral\SDK\Mocks\Mock;
use RingCentral\SDK\Test\TestCase;
use RingCentral\SDK\WebSocket\Subscription;
use RingCentral\SDK\WebSocket\Events\SuccessEvent;
use RingCentral\SDK\WebSocket\Events\ErrorEvent;
use RingCentral\SDK\WebSocket\Events\NotificationEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use React\Socket\ConnectorInterface;
use React\Socket\ConnectionInterface;
use Ratchet\Client\WebSocket as RatchetWebSocket;
use Ratchet\Client\Connector;
use React\Promise\RejectedPromise;
use React\Promise\Promise;

// override uniqid() to make it return a fixed string
function uniqid() {
    return 'xxxx';
}

class WebSocketSubscriptionTest extends TestCase
{
    public function testCreateSubscriptionNoWS()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('WebSocket is not initialized');
        $sdk = $this->getSDK();
        $subscription = $sdk->createSubscription();
    }

    public function testCreateSubscriptionSuccess()
    {
        $sdk = $this->getSDK();
        $websocket = $sdk->initWebSocket();
        $subscription = $sdk->createSubscription();
        $subscription->addEvents(array(
            '/restapi/v1.0/account/~/extension/~/presence'
        ));
        $this->assertTrue($subscription !== null);
        $this->assertTrue(!$subscription->subscribed());
        $this->assertTrue(!$subscription->alive());
    }

    public function testRenewSubscriptionError()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No subscription');
        $sdk = $this->getSDK();
        $websocket = $sdk->initWebSocket();
        $subscription = $sdk->createSubscription();
        $subscription->addEvents(array(
            '/restapi/v1.0/account/~/extension/~/presence'
        ));
        $this->assertTrue($subscription !== null);
        $this->assertTrue(!$subscription->subscribed());
        $this->assertTrue(!$subscription->alive());
        $subscription->renew();
    }

    public function testRegisterSuccess()
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $stream = $this->createMock(ConnectionInterface::class);
        $stream->expects($this->exactly(2))
            ->method('write');
        $mockConnection = new RatchetWebSocket($stream, $response, $request);
        $connector = $this->createMock(Connector::class);
        $connector->expects($this->once())
            ->method('__invoke')
            ->willReturn(\React\Promise\resolve($mockConnection));
        $sdk = $this->getSDK([
            $this->createResponse('POST', '/restapi/oauth/wstoken', [
                'ws_access_token' => 'TOKEN',
                'uri' => 'wss://whatever/ws',
                'expires_in' => 30,
            ], 200)
        ]);
        $websocket = $sdk->initWebSocket();
        \React\Async\await($websocket->connect($connector));
        $connectionDetailMessage = '
        [ 
            {
              "type": "ConnectionDetails",
              "messageId": "156eec30-f154-11ea-9629-005056bf7145",
              "status": 200,
              "headers": {
                    "RCRequestId": "156eec30-f154-11ea-9629-005056bf7145" 
                }
             }
        ]
        ';
        $mockConnection->emit('message', [$connectionDetailMessage]);
        $this->assertTrue($websocket->ready());
        // overide uniqid

        $subscription = $sdk->createSubscription();
        $subscription->addEvents(array(
            '/restapi/v1.0/account/~/extension/~/presence'
        ));
        $successEvent = null;
        $subscription->addListener(Subscription::EVENT_SUBSCRIBE_SUCCESS, function (SuccessEvent $e) use (&$successEvent) {
            $successEvent = $e;
        });
        $renewEvent = null;
        $subscription->addListener(Subscription::EVENT_RENEW_SUCCESS, function (SuccessEvent $e) use (&$renewEvent) {
            $renewEvent = $e;
        });
        $notificationEvent = null;
        $subscription->addListener(Subscription::EVENT_NOTIFICATION, function (NotificationEvent $e) use (&$notificationEvent) {
            $notificationEvent = $e;
        });
        $subscription->register();
        $responseMessage = '
        [
            {
              "type": "ClientRequest",
              "messageId": "xxxx",
              "status": 200,
              "headers": {
                   "Server": "nginx"
                }
            },
           {
              "uri": "/restapi/v1.0/subscription/3ef793d9-34e3-4218-a865-e15856cd1599",
              "id": "3ef793d9-34e3-4218-a865-e15856cd1599",
              "creationTime": "2020-09-07T21:50:28.643Z",
              "status": "Active",
              "eventFilters": [
                   "/restapi/v1.0/account/400132871015/extension/400132871015/presence"
                ],
              "expirationTime": "2020-09-07T22:10:51.113Z",
              "expiresIn": 843,
              "deliveryMode": {
                   "transportType":"WebSocket",
                   "encryption": false
                }
            }
        ]
        ';
        $mockConnection->emit('message', [$responseMessage]);
        $this->assertTrue($successEvent !== null);
        $this->assertTrue($successEvent->apiResponse()->body()['id'] == '3ef793d9-34e3-4218-a865-e15856cd1599');
        $this->assertTrue($successEvent->apiResponse()->headers()['Server'] == 'nginx');
        $this->assertTrue($subscription->subscribed());
        $subscription->setEvents(array(
            '/restapi/v1.0/account/~/extension/~/presence',
            '/restapi/v1.0/account/~/extension/~/message-store'
        ));
        $subscription->register();
        $mockConnection->emit('message', [$responseMessage]);
        $this->assertTrue($renewEvent !== null);
        $notificationMessage = '
        [
            {
              "type": "ServerNotification",
              "messageId": "6419467329489567279",
              "status": 200,
              "headers": {
                  "RCRequestId":"99a94bf2-f155-11ea-9414-005056bf7145"
                }
             },
            {
              "uuid":"6419467329489567279",
              "event":"/restapi/v1.0/account/100132871000/extension/100132871000/message-store/instant?type=SMS",
              "timestamp":"2020-09-07T22:00:54.071Z",
              "subscriptionId":"3ef793d9-34e3-4218-a865-e15856cd1599",
              "ownerId":"400132871015",
              "body": {
                   "id":"872888015"
              }
            }
        ]
        ';
        $mockConnection->emit('message', [$notificationMessage]);
        $this->assertTrue($notificationEvent !== null);
        $this->assertTrue($notificationEvent->payload()['body']['id'] === '872888015');
    }

    public function testRegisterError()
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $stream = $this->createMock(ConnectionInterface::class);
        $stream->expects($this->exactly(2))
            ->method('write');
        $mockConnection = new RatchetWebSocket($stream, $response, $request);
        $connector = $this->createMock(Connector::class);
        $connector->expects($this->once())
            ->method('__invoke')
            ->willReturn(\React\Promise\resolve($mockConnection));
        $sdk = $this->getSDK([
            $this->createResponse('POST', '/restapi/oauth/wstoken', [
                'ws_access_token' => 'TOKEN',
                'uri' => 'wss://whatever/ws',
                'expires_in' => 30,
            ], 200)
        ]);
        $websocket = $sdk->initWebSocket();
        \React\Async\await($websocket->connect($connector));
        $connectionDetailMessage = '
        [ 
            {
              "type": "ConnectionDetails",
              "messageId": "156eec30-f154-11ea-9629-005056bf7145",
              "status": 200,
              "headers": {
                    "RCRequestId": "156eec30-f154-11ea-9629-005056bf7145" 
                }
             }
        ]
        ';
        $mockConnection->emit('message', [$connectionDetailMessage]);
        $this->assertTrue($websocket->ready());
        $subscription = $sdk->createSubscription();
        $subcribeError = null;
        $subscription->addListener(Subscription::EVENT_SUBSCRIBE_ERROR, function (ErrorEvent $e) use (&$subcribeError) {
            $subcribeError = $e;
        });
        $renewError = null;
        $subscription->addListener(Subscription::EVENT_RENEW_ERROR, function (ErrorEvent $e) use (&$renewError) {
            $renewError = $e;
        });
        $subscription->register([
            'events' => array(
                '/restapi/v1.0/account/~/extension/~/presence'
            )
        ]);
        $responseMessage = '
        [
            {
              "type": "ERROR",
              "messageId": "xxxx",
              "status": 400,
              "headers": {
                   "Server": "nginx"
                }
            },
           {
              "message": "Subscription failed"
            }
        ]
        ';
        $mockConnection->emit('message', [$responseMessage]);
        $this->assertTrue($subcribeError !== null);
        $this->assertTrue($subcribeError->exception()->getMessage() === 'Subscription failed');
        $this->assertTrue($subcribeError->exception()->apiResponse()->error() === 'Subscription failed');
        $this->assertTrue(!$subscription->subscribed());
        $subscription->setSubscription([
            'id' => 'xxxxx'
        ]);
        $subscription->register([
            'events' => array(
                '/restapi/v1.0/account/~/extension/~/presence',
                '/restapi/v1.0/account/~/extension/~/message-store'
            )
        ]);
        $mockConnection->emit('message', [$responseMessage]);
        $this->assertTrue($renewError !== null);
        $subscription->reset();
        $this->assertTrue(!$subscription->subscribed());
    }
}
