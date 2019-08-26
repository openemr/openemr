<?php

namespace RingCentral\SDK;

use GuzzleHttp\Client as GuzzleClient;
use RingCentral\SDK\Http\Client;
use RingCentral\SDK\Http\Client as HttpClient;
use RingCentral\SDK\Http\MultipartBuilder;
use RingCentral\SDK\Platform\Platform;
use RingCentral\SDK\Subscription\Subscription;

class SDK
{

    const VERSION = '2.2.3';
    const SERVER_PRODUCTION = 'https://platform.ringcentral.com';
    const SERVER_SANDBOX = 'https://platform.devtest.ringcentral.com';

    /** @var Client */
    protected $_client;

    /** @var Platform */
    protected $_platform;

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
     * @return Subscription
     */
    public function createSubscription()
    {
        return new Subscription($this->_platform);
    }

    /**
     * @return MultipartBuilder
     */
    public function createMultipartBuilder()
    {
        return new MultipartBuilder();
    }

}