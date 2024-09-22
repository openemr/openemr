<?php

namespace PubNub\Callbacks;


use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;

abstract class SubscribeCallback
{
    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     */
    abstract function status($pubnub, $status);

    // TODO: add annotation
    abstract function message($pubnub, $message);

    // TODO: add annotation
    abstract function presence($pubnub, $presence);

    // Not marked as abstract for backward compatibility reasons.
    function signal($pubnub, $signal) {}
}