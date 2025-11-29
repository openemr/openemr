<?php

/**
 * ServiceLocatorEvent - Allows modules to replace service implementations via event system
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Services;

use Symfony\Contracts\EventDispatcher\Event;

class ServiceLocatorEvent extends Event
{
    /**
     * This event is triggered when the service locator is looking up a service.
     * Modules can listen to this event to provide alternative implementations.
     */
    const EVENT_SERVICE_LOCATE = 'service.locator.locate';

    /**
     * @var mixed The service instance (null initially, set by event listeners)
     */
    private $serviceInstance;

    /**
     * @var bool Whether a service has been provided by a listener
     */
    private $serviceProvided = false;

    /**
     * ServiceLocatorEvent constructor.
     *
     * @param string $serviceName The service name/interface being requested
     * @param array $context Additional context for service creation
     */
    public function __construct(private readonly string $serviceName, private array $context = [])
    {
    }

    /**
     * Get the service name/interface being requested
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * Get the service instance provided by listeners
     *
     * @return mixed
     */
    public function getServiceInstance()
    {
        return $this->serviceInstance;
    }

    /**
     * Set the service instance (called by event listeners)
     *
     * @param mixed $serviceInstance
     * @return ServiceLocatorEvent
     */
    public function setServiceInstance($serviceInstance): self
    {
        $this->serviceInstance = $serviceInstance;
        $this->serviceProvided = true;

        // Stop event propagation once a service is provided
        $this->stopPropagation();

        return $this;
    }

    /**
     * Check if a service has been provided by a listener
     *
     * @return bool
     */
    public function hasServiceInstance(): bool
    {
        return $this->serviceProvided;
    }

    /**
     * Get additional context for the service lookup
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set context information
     *
     * @param array $context
     * @return ServiceLocatorEvent
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Add context information
     *
     * @param string $key
     * @param mixed $value
     * @return ServiceLocatorEvent
     */
    public function addContext(string $key, $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Get specific context value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getContextValue(string $key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }
}
