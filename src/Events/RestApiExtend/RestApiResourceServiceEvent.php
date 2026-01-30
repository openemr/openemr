<?php

namespace OpenEMR\Events\RestApiExtend;

use Symfony\Contracts\EventDispatcher\Event;

class RestApiResourceServiceEvent extends Event
{
    /**
     * Used whenever the service for a rest api resource needs to be returned for metadata or other kind of resource purposes
     */
    const EVENT_HANDLE = 'restapi.service.get';

    /**
     * @param string $resource The API resource that we need to locate a service for
     * @param string $serviceClass The original system resource for service
     */
    public function __construct(
        private $resource,
        private $serviceClass
    ) {
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     * @return RestApiResourceServiceEvent
     */
    public function setResource(string $resource): RestApiResourceServiceEvent
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceClass(): ?string
    {
        return $this->serviceClass;
    }

    /**
     * @param string $serviceClass
     * @return RestApiResourceServiceEvent
     */
    public function setServiceClass(?string $serviceClass): RestApiResourceServiceEvent
    {
        $this->serviceClass = $serviceClass;
        return $this;
    }
}
