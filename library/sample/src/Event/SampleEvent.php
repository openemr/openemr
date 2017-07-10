<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-10
 * Time: 00:03
 */

namespace OpenEMR\Sample\Event;

use Symfony\Component\EventDispatcher\Event;

class SampleEvent extends Event
{
    const NAME = 'sample.event';
}