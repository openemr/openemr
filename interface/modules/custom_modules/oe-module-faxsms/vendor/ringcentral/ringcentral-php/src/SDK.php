<?php

namespace RingCentral\SDK;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use RingCentral\SDK\Http\Client;
use RingCentral\SDK\Http\Client as HttpClient;
use RingCentral\SDK\Http\MultipartBuilder;
use RingCentral\SDK\Platform\Platform;
use RingCentral\SDK\Subscription\PubnubSubscription;
use RingCentral\SDK\WebSocket\WebSocket;
use RingCentral\SDK\WebSocket\Subscription as WebSocketSubscription;

class SDK
{

    const VERSION = '2.3.6';
    const SERVER_PRODUCTION = 'https://platform.ringcentral.com';

    /** @var Client */
    protected $_client;

    /** @var Platform */
    protected $_platform;

    /** @var WebSocket */
    protected $_websocket;

    /** @var HttpClient */
    protected $_guzzle;

    /**
     * SDK constructor.
     *
     * @param string       $clientId
     * @param string       $clientSecret
     * @param string       $server
     * @param string       $appName
     * @param string       $appVersion
     * @param GuzzleClient $guzzle
     */
    public function __construct(
        $clientId,
        $clientSecret,
        $server,
        $appName = '',
        $appVersion = '',
        $guzzle = null
    ) {

        $pattern = "/[^a-z0-9-_.]/i";

        $appName = preg_replace($pattern, '', $appName);
        $appVersion = preg_replace($pattern, '', $appVersion);

        $this->_guzzle = $guzzle ? $guzzle : new GuzzleClient();

        $this->_client = new Client($this->_guzzle);

        $this->_platform = new Platform($this->_client, $clientId, $clientSecret, $server, $appName, $appVersion);

    }

    /**
     * @return Platform
     */
    public function platform()
    {
        return $this->_platform;
    }

    /**
     * @return PubnubSubscription | WebSocketSubscription
     */
    public function createSubscription(string $type = 'WebSocket')
    {
        if ($type == 'Pubnub') {
            trigger_error(
                'PubNub support is deprecated. Please migrate your application to WebSockets.',
                E_USER_DEPRECATED
            );
            return new PubnubSubscription($this->_platform);
        }
        if (empty($this->websocket())) {
            throw new Exception('WebSocket is not initialized');
        }
        return new WebSocketSubscription($this->platform(), $this->websocket());
    }

    /**
     * @return MultipartBuilder
     */
    public function createMultipartBuilder()
    {
        return new MultipartBuilder();
    }

    /**
     * @return WebSocket
     */
    public function initWebSocket()
    {
        if (!$this->_websocket) {
            $this->_websocket = new WebSocket($this->_platform);
        }
        return $this->_websocket;
    }

    public function disconnectWebSocket()
    {
        if ($this->_websocket) {
            $this->_websocket->close();
        }
        $this->_websocket = null;
    }

    public function websocket()
    {
        return $this->_websocket;
    }
}
