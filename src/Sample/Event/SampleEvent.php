<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Sample\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Holder of all events related to Sample actions.
 *
 * Central holder for all Sample events, a convienence location for users to
 * subscribe. For instance, when registering, always subscribe to
 * `SampleEvent::NAME` instead of `sample.event`
 *
 * @package OpenEMR\Sample
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
class SampleEvent extends Event
{
    const NAME = 'sample.event';
}
