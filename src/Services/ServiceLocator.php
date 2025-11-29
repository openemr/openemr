<?php

/**
 * ServiceLocator - Generic service locator using Laminas ServiceManager with OpenEMR event integration
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Config;
use OpenEMR\Events\Services\ServiceLocatorEvent;

class ServiceLocator implements ServiceLocatorInterface
{
    /**
     * @var ServiceManager|null
     */
    private static ?ServiceManager $serviceManager = null;

    /**
     * @var array|null Cached service configuration
     */
    private static $serviceConfig = null;

    /**
     * Initialize the service manager with configuration
     *
     * @param array $config Optional configuration array to merge with loaded config
     */
    public static function initialize(array $config = []): void
    {
        // Load service configuration from files
        $loadedConfig = self::loadServiceConfiguration();

        // Merge user config with loaded configuration
        $mergedConfig = array_merge_recursive($loadedConfig, $config);

        $serviceConfig = new Config($mergedConfig);
        self::$serviceManager = new ServiceManager();
        $serviceConfig->configureServiceManager(self::$serviceManager);
    }

    /**
     * Load service configuration from configuration files
     *
     * @return array Service configuration array
     */
    private static function loadServiceConfiguration(): array
    {
        if (self::$serviceConfig !== null) {
            return self::$serviceConfig;
        }

        // Default empty configuration
        self::$serviceConfig = [
            'services' => [],
            'factories' => [],
            'invokables' => [],
            'aliases' => []
        ];

        // Load main services configuration
        $configFile = __DIR__ . '/../../config/services.php';
        if (file_exists($configFile)) {
            $loadedConfig = require $configFile;
            if (is_array($loadedConfig)) {
                self::$serviceConfig = array_merge_recursive(self::$serviceConfig, $loadedConfig);
            }
        }

        // Note: Module service replacements should use the event system,
        // not configuration files. This keeps core services separate from module customizations.

        return self::$serviceConfig;
    }

    /**
     * Get a service by name or interface
     * First checks if modules provide an alternative via event system
     *
     * @param string $serviceName The service name or interface
     * @param array $context Additional context for service creation
     * @return mixed The service instance
     * @throws \Laminas\ServiceManager\Exception\ServiceNotFoundException
     */
    public static function get(string $serviceName, array $context = [])
    {
        // First, dispatch an event to allow modules to provide alternative services
        if (isset($GLOBALS['kernel'])) {
            $event = new ServiceLocatorEvent($serviceName, $context);
            $dispatchedEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch(
                $event,
                ServiceLocatorEvent::EVENT_SERVICE_LOCATE
            );

            // If a module provided a service, validate and return it
            if ($dispatchedEvent->hasServiceInstance()) {
                $providedService = $dispatchedEvent->getServiceInstance();

                // Validate that the provided service implements the required interface/class
                if (self::validateServiceInterface($serviceName, $providedService)) {
                    return $providedService;
                }
                // Log validation failure and fall back to default
                error_log("ServiceLocator: Module provided service for '{$serviceName}' does not implement required interface. Falling back to default.");
            }
        }

        // Fall back to default service manager behavior
        if (self::$serviceManager === null) {
            self::initialize();
        }

        return self::$serviceManager->get($serviceName);
    }

    /**
     * Check if a service is available
     *
     * @param string $serviceName The service name or interface
     * @return bool True if service is available
     */
    public static function has(string $serviceName): bool
    {
        if (self::$serviceManager === null) {
            self::initialize();
        }

        return self::$serviceManager->has($serviceName);
    }

    /**
     * Set a service instance directly
     *
     * @param string $serviceName The service name
     * @param mixed $service The service instance
     */
    public static function setService(string $serviceName, $service): void
    {
        if (self::$serviceManager === null) {
            self::initialize();
        }

        self::$serviceManager->setService($serviceName, $service);
    }

    /**
     * Reset the service manager (mainly for testing)
     */
    public static function reset(): void
    {
        self::$serviceManager = null;
        self::$serviceConfig = null;
    }


    /**
     * Get the underlying service manager instance
     *
     * @return ServiceManager
     */
    public static function getServiceManager(): ServiceManager
    {
        if (self::$serviceManager === null) {
            self::initialize();
        }

        return self::$serviceManager;
    }

    /**
     * Validate that a service implements the required interface or extends the required class
     *
     * @param string $serviceName The required interface or class name
     * @param mixed $serviceInstance The service instance to validate
     * @return bool True if valid, false otherwise
     */
    private static function validateServiceInterface(string $serviceName, $serviceInstance): bool
    {
        return (interface_exists($serviceName) && $serviceInstance instanceof $serviceName);
    }

    /**
     * Get detailed validation information for debugging
     *
     * @param string $serviceName The required interface or class name
     * @param mixed $serviceInstance The service instance to validate
     * @return array Validation details
     */
    public static function getValidationInfo(string $serviceName, $serviceInstance): array
    {
        return [
            'serviceName' => $serviceName,
            'serviceClass' => $serviceInstance::class,
            'isInterface' => interface_exists($serviceName),
            'isClass' => class_exists($serviceName),
            'implementsInterface' => interface_exists($serviceName) ? $serviceInstance instanceof $serviceName : null,
            'extendsClass' => class_exists($serviceName) ? $serviceInstance instanceof $serviceName : null,
            'interfaces' => class_implements($serviceInstance),
            'parents' => class_parents($serviceInstance)
        ];
    }

    /**
     * Special handling for singleton services that may need replacement
     * This allows modules to override singletons by providing instances directly
     *
     * @param string $serviceName The service name/interface
     * @param mixed $serviceInstance The replacement service instance
     */
    public static function replaceSingleton(string $serviceName, $serviceInstance): void
    {
        if (self::$serviceManager === null) {
            self::initialize();
        }

        // Validate the replacement service
        if (self::validateServiceInterface($serviceName, $serviceInstance)) {
            self::$serviceManager->setService($serviceName, $serviceInstance);
        } else {
            throw new \InvalidArgumentException(
                "Replacement service for '{$serviceName}' does not implement required interface"
            );
        }
    }
}
