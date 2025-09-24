<?php

/**
 * ServiceLocatorInterface - Interface for service locator implementations
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Laminas\ServiceManager\ServiceManager;

interface ServiceLocatorInterface
{
    /**
     * Initialize the service manager with configuration
     *
     * @param array $config Optional configuration array to merge with loaded config
     */
    public static function initialize(array $config = []): void;

    /**
     * Get a service by name or interface
     * First checks if modules provide an alternative via event system
     *
     * @param string $serviceName The service name or interface
     * @param array $context Additional context for service creation
     * @return mixed The service instance
     * @throws \Laminas\ServiceManager\Exception\ServiceNotFoundException
     */
    public static function get(string $serviceName, array $context = []);

    /**
     * Check if a service is available
     *
     * @param string $serviceName The service name or interface
     * @return bool True if service is available
     */
    public static function has(string $serviceName): bool;

    /**
     * Set a service instance directly
     *
     * @param string $serviceName The service name
     * @param mixed $service The service instance
     */
    public static function setService(string $serviceName, $service): void;

    /**
     * Reset the service manager (mainly for testing)
     */
    public static function reset(): void;

    /**
     * Get the underlying service manager instance
     *
     * @return ServiceManager
     */
    public static function getServiceManager(): ServiceManager;

    /**
     * Get detailed validation information for debugging
     *
     * @param string $serviceName The required interface or class name
     * @param mixed $serviceInstance The service instance to validate
     * @return array Validation details
     */
    public static function getValidationInfo(string $serviceName, $serviceInstance): array;

    /**
     * Special handling for singleton services that may need replacement
     * This allows modules to override singletons by providing instances directly
     *
     * @param string $serviceName The service name/interface
     * @param mixed $serviceInstance The replacement service instance
     */
    public static function replaceSingleton(string $serviceName, $serviceInstance): void;
}
