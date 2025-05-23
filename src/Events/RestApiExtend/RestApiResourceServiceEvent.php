<?php

namespace OpenEMR\Events\RestApiExtend;

class RestApiResourceServiceEvent
{
    /**
     * Used whenever the service for a rest api resource needs to be returned for metadata or other kind of resource purposes
     */
    const EVENT_HANDLE = 'restapi.service.get';

    /**
     * @var string The API resource that we need to locate a service for
     */
    private $resource;

    /**
     * @var string The original system resource for service
     */
    private $serviceClass;

    public function __construct($resource, $serviceClass)
    {
        $this->resource = $resource;
        $this->serviceClass = $serviceClass;
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
