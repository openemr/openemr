<?php

namespace RingCentral\SDK\Subscription;

use Exception;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubCrypto;
use RingCentral\SDK\Core\Utils;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\Platform\Platform;
use RingCentral\SDK\Subscription\Events\ErrorEvent;
use RingCentral\SDK\Subscription\Events\NotificationEvent;
use RingCentral\SDK\Subscription\Events\SuccessEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PubnubCallback extends SubscribeCallback
{
    /** @var Subscription */
    protected $_subscription;

    /**
     * PubnubCallback constructor.
     *
     * @param Subscription $subscription
     */
    function __construct(Subscription $subscription)
    {
        $this->_subscription = $subscription;
    }

    /**
     * @param $pubnub
     * @param $status
     *
     * @throws PubNubUnsubscribeException
     * @throws Exception
     *
     * @return void
     */
    function status($pubnub, $status)
    {

        if (!$this->_subscription->keepPolling()) {
            $sub = $this->_subscription->subscription();
            $e = new PubNubUnsubscribeException();
            $e->setChannels($sub['deliveryMode']['address']);
            throw $e;
        }

        $cat = $status->getCategory();

        if ($cat === PNStatusCategory::PNUnexpectedDisconnectCategory ||
            $cat === PNStatusCategory::PNTimeoutCategory
        ) {
            $this->_subscription->pubnubTimeoutHandler();
        }

    }

    /**
     * @param PubNub $pubnub
     * @param PNMessageResult $message
     *
     * @throws Exception
     *
     * @return bool
     */
    function message($pubnub, $message)
    {
        return $this->_subscription->notify($message);
    }

    function presence($pubnub, $presence)
    {
    }
}

class Subscription extends EventDispatcher
{

    const EVENT_NOTIFICATION = 'notification';
    const EVENT_REMOVE_SUCCESS = 'removeSuccess';
    const EVENT_REMOVE_ERROR = 'removeError';
    const EVENT_RENEW_SUCCESS = 'renewSuccess';
    const EVENT_RENEW_ERROR = 'renewError';
    const EVENT_SUBSCRIBE_SUCCESS = 'subscribeSuccess';
    const EVENT_SUBSCRIBE_ERROR = 'subscribeError';
    const EVENT_TIMEOUT = 'timeout';

    const RENEW_HANDICAP = 120; // 2 minutes
    const SUBSCRIBE_TIMEOUT = 60; // 1 minute

    /** @var Platform */
    protected $_platform;

    /** @var string[] */
    protected $_eventFilters = array();

    /** @var array */
    protected $_subscription = array(
        'eventFilters'   => array(),
        'expirationTime' => '', // 2014-03-12T19:54:35.613Z
        'expiresIn'      => 0,
        'deliveryMode'   => array(
            'transportType' => 'PubNub',
            'encryption'    => false,
            'address'       => '',
            'subscriberKey' => '',
            'secretKey'     => ''
        ),
        'id'             => '',
        'creationTime'   => '', // 2014-03-12T19:54:35.613Z
        'status'         => '', // Active
        'uri'            => ''
    );

    /** @var Pubnub */
    protected $_pubnub;

    protected $_keepPolling = false;

    protected $_skipSubscribe = false;

    function __construct(Platform $platform)
    {
        $this->_platform = $platform;
    }

    /**
     * @return Pubnub
     */
    function pubnub()
    {
        return $this->_pubnub;
    }

    /**
     * @param array $options
     *
     * @throws Exception
     *
     * @return ApiResponse|$this
     */
    function register(array $options = array())
    {
        if ($this->alive()) {
            return $this->renew($options);
        } else {
            return $this->subscribe($options);
        }
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    function setKeepPolling($flag = false)
    {
        $this->_keepPolling = !empty($flag);
    }

    /**
     * @return bool
     */
    function keepPolling()
    {
        return $this->_keepPolling;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    function setSkipSubscribe($flag = false)
    {
        $this->_skipSubscribe = !empty($flag);
    }

    /**
     * @return bool
     */
    function skipSubscribe()
    {
        return $this->_skipSubscribe;
    }

    /**
     * @param array $events
     *
     * @return $this
     */
    function addEvents(array $events)
    {
        $this->_eventFilters = array_merge($this->_eventFilters, $events);
        return $this;
    }

    /**
     * @param array $events
     *
     * @return $this
     */
    function setEvents(array $events)
    {
        $this->_eventFilters = $events;
        return $this;
    }

    /**
     * @param array $options
     *
     * @throws Exception
     *
     * @return ApiResponse
     */
    function subscribe(array $options = array())
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        try {

            $response = $this->_platform->post('/restapi/v1.0/subscription', array(
                'eventFilters' => $this->getFullEventFilters(),
                'deliveryMode' => array(
                    'transportType' => 'PubNub'
                )
            ));

            $this->setSubscription($response->jsonArray());
            $this->subscribeAtPubnub();

            //TODO Subscription renewal when everything will become async

            $this->dispatch(self::EVENT_SUBSCRIBE_SUCCESS, new SuccessEvent($response));

            return $response;

        } catch (Exception $e) {

            $this->reset();
            $this->dispatch(self::EVENT_SUBSCRIBE_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    /**
     * @param array $options
     *
     * @throws Exception
     *
     * @return $this
     */
    function renew(array $options = array())
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        if (!$this->subscribed()) {
            throw new Exception('No subscription');
        }

        try {

            $response = $this->_platform->put('/restapi/v1.0/subscription/' . $this->_subscription['id'], array(
                'eventFilters' => $this->getFullEventFilters()
            ));

            $this->setSubscription($response->jsonArray());

            $this->dispatch(self::EVENT_RENEW_SUCCESS, new SuccessEvent($response));

            return $this;

        } catch (Exception $e) {

            $this->reset();
            $this->dispatch(self::EVENT_RENEW_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    /**
     * @throws Exception
     *
     * @return ApiResponse
     */
    function remove()
    {

        if (!$this->subscribed()) {
            throw new Exception('No subscription');
        }

        try {

            $response = $this->_platform->delete('/restapi/v1.0/subscription/' . $this->_subscription['id']);

            $this->reset();

            $this->dispatch(self::EVENT_REMOVE_SUCCESS, new SuccessEvent($response));

            return $response;

        } catch (Exception $e) {

            $this->reset();
            $this->dispatch(self::EVENT_REMOVE_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    /**
     * @return bool
     */
    function subscribed()
    {
        return (!empty($this->_subscription) &&
                !empty($this->_subscription['deliveryMode']) &&
                !empty($this->_subscription['deliveryMode']['subscriberKey']) &&
                !empty($this->_subscription['deliveryMode']['address']));
    }

    /**
     * @return bool
     */
    function alive()
    {
        return $this->subscribed() && (time() < $this->expirationTime());
    }

    /**
     * @return int
     */
    function expirationTime()
    {
        return strtotime($this->_subscription['expirationTime']) - self::RENEW_HANDICAP;
    }

    /**
     * @return array
     */
    function subscription()
    {
        return $this->_subscription;
    }

    /**
     * @param array $subscription
     *
     * @return $this
     */
    function setSubscription($subscription)
    {
        $this->_subscription = $subscription;
        return $this;
    }

    function reset()
    {

        if ($this->_pubnub && $this->alive()) {
            //$this->_pubnub->unsubscribe($this->subscription['deliveryMode']['address']);
            $this->_pubnub = null;
        }

        $this->_subscription = null;

    }

    /**
     * @throws Exception
     *
     * @return $this
     */
    protected function subscribeAtPubnub()
    {

        if (!$this->alive()) {
            throw new Exception('Subscription is not alive');
        }

        $pnconf = new PNConfiguration();

        $pnconf->setSubscribeKey($this->_subscription['deliveryMode']['subscriberKey']);
        $pnconf->setPublishKey('convince-pubnub-its-okay');
        $pnconf->setSubscribeTimeout(self::SUBSCRIBE_TIMEOUT);

        $subscribeCallback = new PubnubCallback($this);

        $this->_pubnub = new PubNub($pnconf);
        $this->_pubnub->addListener($subscribeCallback);

        if (!$this->_skipSubscribe) {
            $this->_pubnub->subscribe()
                          ->channels($this->_subscription['deliveryMode']['address'])
                          ->execute();
        }

        return $this;

    }

    /**
     * Attention, this function is NOT PUBLIC!!! The only reason it's public is due to PHP 5.3 limitations
     * @protected
     *
     * @throws Exception
     */
    public function pubnubTimeoutHandler()
    {

        $this->dispatch(self::EVENT_TIMEOUT);

        if ($this->subscribed() && !$this->alive()) {
            $this->renew();
        }

    }

    /**
     * Attention, this function is NOT PUBLIC!!! The only reason it's public is due to PHP 5.3 limitations
     * @protected
     * @param PNMessageResult $pubnubMessage
     *
     * @throws Exception
     *
     * @return bool
     */
    public function notify($pubnubMessage)
    {
        $message = $pubnubMessage->getMessage();
        $message = $this->decrypt($message);
        //print 'Message received: ' . $message . PHP_EOL;
        $this->dispatch(self::EVENT_NOTIFICATION, new NotificationEvent($message));
        return $this->_keepPolling;
    }

    /**
     * @param $message
     *
     * @throws Exception
     *
     * @return bool|mixed|string
     */
    protected function decrypt($message)
    {

        if (!$this->subscribed()) {
            throw new Exception('No subscription');
        }

        if ($this->_subscription['deliveryMode']['encryption'] && $this->_subscription['deliveryMode']['encryptionKey']) {

            $aes = new PubNubCrypto($this->_subscription['deliveryMode']['encryptionKey']);

            $message = $aes->unPadPKCS7(
                openssl_decrypt(
                    base64_decode($message),
                    'AES-128-ECB',
                    base64_decode($this->_subscription['deliveryMode']['encryptionKey']),
                    OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
                ),
                128
            );

            $message = Utils::json_parse($message, true); // PUBNUB itself always decode as array

        }

        return $message;

    }

    /**
     * @return array
     */
    protected function getFullEventFilters()
    {
        $events = array();
        foreach ($this->_eventFilters as $event) {
            $events[] = $this->_platform->createUrl($event);
        }
        return $events;
    }


}
