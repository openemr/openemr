<?php

use RingCentral\SDK\Mocks\Mock;
use RingCentral\SDK\Test\TestCase;
use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\WebSocket\WebSocket;
use RingCentral\SDK\WebSocket\ApiResponse;
use RingCentral\SDK\WebSocket\Events\SuccessEvent;
use React\Promise\Promise;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use React\Socket\ConnectorInterface;
use React\Socket\ConnectionInterface;
use Ratchet\Client\WebSocket as RatchetWebSocket;
use Ratchet\Client\Connector;

class WebSocketTest extends TestCase
{
    public function testInitWebSocket()
    {

        $sdk = $this->getSDK();
        $this->assertTrue(empty($sdk->websocket()));
        $websocket = $sdk->initWebSocket();
        $this->assertTrue(!empty($sdk->websocket()));
        $this->assertTrue($websocket === $sdk->websocket());
        $websocket1 = $sdk->initWebSocket();
        $this->assertTrue($websocket1 === $websocket);
    }

    public function testDisconnectWebSocket()
    {

            $sdk = $this->getSDK();
            $this->assertTrue(empty($sdk->websocket()));
            $websocket = $sdk->initWebSocket();
            $this->assertTrue(!empty($sdk->websocket()));
            $sdk->disconnectWebSocket();
            $this->assertTrue(empty($sdk->websocket()));
    }

    public function testWebSocketConnectErrorAtGetToken()
    {
        $this->expectException(ApiException::class);
        $sdk = $this->getSDK([
            $this->createResponse('POST', '/restapi/oauth/wstoken', [
                'message' => 'Create token error',
            ], 400)
        ]);
        $errorEvent = null;
        $websocket = $sdk->initWebSocket();
        $websocket->addListener(WebSocket::EVENT_ERROR, function($event) use (&$errorEvent) {
            $errorEvent = $event;
        });
        $websocket->connect();
        $this->assertTrue($errorEvent !== null);
        $this->assertTrue($errorEvent->exception()->apiResponse()-> error() === 'Create token error');
    }

    public function testWebSocketConnectErrorAtConnect()
    {
        $connector = $this->createMock(Connector::class);
        $connector->expects($this->once())
            ->method('__invoke')
            ->willReturn(React\Promise\reject(new Exception('Connect error')));
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $sdk = $this->getSDK([
            $this->createResponse('POST', '/restapi/oauth/wstoken', [
                'ws_access_token' => 'TOKEN',
                'uri' => 'wss://whatever/ws',
                'expires_in' => 30,
            ], 200)
        ]);
        $errorEvent = null;
        $websocket = $sdk->initWebSocket();
        $websocket->addListener(WebSocket::EVENT_ERROR, function($event) use (&$errorEvent) {
            $errorEvent = $event;
        });
        \React\Async\await($websocket->connect($connector));
        $this->assertTrue($errorEvent->exception()->getMessage() == 'Connect error');
    }

    public function testWebSocketConnectSuccess()
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $stream = $this->createMock(ConnectionInterface::class);
        $stream->expects($this->once())
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
        $readyEvent = null;
        $websocket->addListener(WebSocket::EVENT_READY, function($event) use (&$readyEvent) {
            $readyEvent = $event;
        });
        $notificationEvent = null;
        $websocket->addListener(WebSocket::EVENT_NOTIFICATION, function($event) use (&$notificationEvent) {
            $notificationEvent = $event;
        });
        $closeEvent = null;
        $websocket->addListener(WebSocket::EVENT_CLOSE, function($event) use (&$closeEvent) {
            $closeEvent = $event;
        });
        $errorEvent = null;
        $websocket->addListener(WebSocket::EVENT_ERROR, function($event) use (&$errorEvent) {
            $errorEvent = $event;
        });
        \React\Async\await($websocket->connect($connector));
        $connection = $websocket->connection();
        $this->assertTrue($connection === $mockConnection);
        $connectionDetailMessage = '
        [
            {
              "type": "ConnectionDetails",
              "messageId": "156eec30-f154-11ea-9629-005056bf7145",
              "status": 200,
              "wsc": {
                   "token": "RTFhRG5nfE9qc0ZtMGtLTkhSTFdLMlVMNzRCZlM0RS1USWtEOVBxLXAzLUdDX2VwSjFFVmlNa1FYYlY3M3pqb2lBTnFFYmNJakRldTZva1FBVDI5OWJRb2dEaC1oeWtDaFJvbDBabkIyUllfSjJOeXJVX3FNdmtZVnZWS2RNZF9QSXhwMjJMNjNCaFl0U0RyWTAwX2FBTWt1NHFEdGxKLUh3Q1U1T1kzVGF5Zi1JUFBCbUpUWlVGcGE3WEJocHE3bXkyRURQVQ",
                   "sequence": 1
                  },
              "headers": {
                    "RCRequestId": "156eec30-f154-11ea-9629-005056bf7145"
                  }
             },
            {
              "creationTime": "2020-09-07T21:50:02.737+0000",
              "maxConnectionsPerSession": 50,
              "recoveryBufferSize": 100,
              "recoveryTimeout": 180,
              "idleTimeout": 1800,
              "absoluteTimeout": 86400,
              "maxActiveRequests": 10
             }
        ]
        ';
        $mockConnection->emit('message', [$connectionDetailMessage]);
        $this->assertTrue($websocket->ready());
        $this->assertTrue($readyEvent !== null);
        $readyEvent = null;
        $connectionDetailMessageEmpaty = '
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
        $mockConnection->emit('message', [$connectionDetailMessageEmpaty]);
        $this->assertTrue($readyEvent !== null);
        $websocket->send('test');
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
        $mockConnection->emit('error', [new Exception('error')]);
        $this->assertTrue($errorEvent !== null);
        $mockConnection->emit('close', ['code', 'reason']);
        $this->assertTrue($closeEvent !== null);
        $this->assertTrue($closeEvent->code() == 'code');
        $this->assertTrue($closeEvent->reason() == 'reason');
        $websocket->close();
        $this->assertTrue($websocket->connection() === null);
    }

    public function testWebSocketConnectSend()
    {
        $mockConnection = $this->createMock(RatchetWebSocket::class);
        $mockConnection
            ->expects($this->exactly(1))
            ->method('send');
        $mockConnection
            ->expects($this->exactly(3))
            ->method('on');
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
        $websocket->send('message');
        $response = [
            'messageId' => 'xxx',
            'status' => 200,
            'headers' => []
        ];
        $websocket->dispatch(new SuccessEvent(new ApiResponse($response, [])), WebSocket::EVENT_READY);
    }
}
