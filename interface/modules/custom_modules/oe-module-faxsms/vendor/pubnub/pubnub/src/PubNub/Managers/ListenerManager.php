<?php

namespace PubNub\Managers;


use PubNub\Callbacks\SubscribeCallback;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Consumer\PubSub\PNPresenceEventResult;
use PubNub\Models\Consumer\PubSub\PNSignalMessageResult;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;

class ListenerManager
{
    /** @var  PubNub */
    protected $pubnub;

    /** @var SubscribeCallback[]  */
    protected $listeners = [];

    /**
     * ListenerManager constructor.
     * @param PubNub $pubnub
     */
    public function __construct(PubNub $pubnub)
    {
        $this->pubnub = $pubnub;
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function addListener($listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function removeListener($listener)
    {
        foreach ($this->listeners as $key => $val) {
            if ($val === $listener) {
                unset($this->listeners[$key]);
            }
        }
    }

    /**
     * @param PNStatus $status
     * @throws PubNubUnsubscribeException
     */
    public function announceStatus(PNStatus $status)
    {
        foreach ($this->listeners as $listener) {
            $listener->status($this->pubnub, $status);
        }
    }

    /**
     * @param PNMessageResult $message
     * @throws PubNubUnsubscribeException
     */
    public function announceMessage(PNMessageResult $message)
    {
        foreach ($this->listeners as $listener) {
            $listener->message($this->pubnub, $message);
        }
    }

    /**
     * @param PNPresenceEventResult $presence
     * @throws PubNubUnsubscribeException
     */
    public function announcePresence(PNPresenceEventResult $presence)
    {
        foreach ($this->listeners as $listener) {
            $listener->presence($this->pubnub, $presence);
        }
    }
     /**
     * @param PNSignalMessageResult $presence
     * @throws PubNubUnsubscribeException
     */
    public function announceSignal(PNSignalMessageResult $signal)
    {
        foreach ($this->listeners as $listener) {
            $listener->signal($this->pubnub, $signal);
        }
    }
}