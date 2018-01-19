<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Sample\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sample subscriber class.
 *
 * Explicitly tell the event dispatcher to register a subscriber
 *
 * @package OpenEMR\Admin
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
class SampleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            SampleEvent::NAME => 'onSampleEvent',
        ];
    }

    public function onSampleEvent(Event $event)
    {
        print("You've been notified of an event!");
    }
}
