<?php

namespace OpenEMR\Events\RestApiExtend;

use Symfony\Component\EventDispatcher\Event;

class RestApiExtendEvent extends Event
{

    const EVENT_HANDLE = 'restConfig.route_map.extend';

    function __construct($route_map)
    {
        $this->route_map_extended = $route_map;
    }
}
