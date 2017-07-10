<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-10
 * Time: 00:02
 */

namespace OpenEMR\Sample\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SampleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            SampleEvent::NAME => 'onSampleEvent',
        ];
    }

    public function onSampleEvent(SampleEvent $event)
    {
        print("You've been notified of an event!");
    }
}