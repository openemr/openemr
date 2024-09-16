<?php

namespace RingCentral\SDK\Subscription;

use RingCentral\SDK\Platform\Platform;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SubscriptionBase extends EventDispatcher
{
    const EVENT_NOTIFICATION = 'notification';
    const EVENT_REMOVE_SUCCESS = 'removeSuccess';
    const EVENT_REMOVE_ERROR = 'removeError';
    const EVENT_RENEW_SUCCESS = 'renewSuccess';
    const EVENT_RENEW_ERROR = 'renewError';
    const EVENT_SUBSCRIBE_SUCCESS = 'subscribeSuccess';
    const EVENT_SUBSCRIBE_ERROR = 'subscribeError';
    const EVENT_TIMEOUT = 'timeout';

    /** @var string[] */
    protected $_eventFilters = [];

    /** @var array */
    protected $_subscription = [
        'eventFilters'   => [],
        'expirationTime' => '', // 2014-03-12T19:54:35.613Z
        'expiresIn'      => 0,
        'deliveryMode'   => [
            'transportType' => 'PubNub',
            'encryption'    => false,
            'address'       => '',
            'subscriberKey' => '',
            'secretKey'     => ''
        ],
        'id'             => '',
        'creationTime'   => '', // 2014-03-12T19:54:35.613Z
        'status'         => '', // Active
        'uri'            => ''
    ];

    /** @var Platform */
    protected $_platform;

    function __construct(Platform $platform)
    {
        $this->_platform = $platform;
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

    /**
     * @return array
     */
    protected function getFullEventFilters()
    {
        $events = [];
        foreach ($this->_eventFilters as $event) {
            $events[] = $this->_platform->createUrl($event);
        }
        return $events;
    }
}
