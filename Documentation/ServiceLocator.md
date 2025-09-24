# OpenEMR Service Locator Pattern

The Service Locator pattern in OpenEMR allows for dependency injection and service replacement through an event-driven system. This enables modules to provide alternative implementations of core services while maintaining interface compatibility.

## Overview

The `ServiceLocator` class provides a centralized way to:
- Locate and instantiate services
- Allow modules to replace core services via events
- Validate that replacement services implement required interfaces
- Maintain backward compatibility with existing code

## Core Components

### 1. ServiceLocator Class
**Location:** `src/Services/ServiceLocator.php`

The main service locator that:
- Dispatches events when services are requested
- Validates service interfaces
- Falls back to default implementations
- Integrates with Laminas ServiceManager

### 2. ServiceLocatorEvent
**Location:** `src/Events/Services/ServiceLocatorEvent.php`

Event fired when services are requested, allowing modules to provide alternatives.

### 3. Service Interfaces
Interfaces that define contracts for services (e.g., `TelemetryServiceInterface`).

## Basic Usage

### Getting a Service
```php
use OpenEMR\Services\ServiceLocator;
use OpenEMR\Telemetry\TelemetryServiceInterface;

// Get a service instance
$service = ServiceLocator::get(TelemetryServiceInterface::class);
$service->reportClickEvent($data);

// With context (optional)
$service = ServiceLocator::get(TelemetryServiceInterface::class, [
    'environment' => 'test',
    'user_id' => 123
]);
```

## Service Configuration

Core OpenEMR services are configured in `config/services.php`. This file defines:
- **Interfaces and implementations** for core services
- **Factory functions** for services that need dependency injection
- **Aliases** for convenient service access

```php
// config/services.php
return [
    'factories' => [
        TelemetryServiceInterface::class => function($container) {
            return new TelemetryService();
        },
    ],
    'aliases' => [
        'telemetry' => TelemetryServiceInterface::class,
    ]
];
```

### Creating a Service Interface

1. **Define the interface:**
```php
<?php
namespace OpenEMR\Services;

interface MyServiceInterface
{
    public function doSomething(): string;
    public function processData(array $data): array;
}
```

2. **Implement the interface:**
```php
<?php
namespace OpenEMR\Services;

class MyService implements MyServiceInterface
{
    public function doSomething(): string
    {
        return "Default implementation";
    }

    public function processData(array $data): array
    {
        // Default processing logic
        return $data;
    }
}
```

3. **Register in core service configuration:**
```php
// In config/services.php
'factories' => [
    MyServiceInterface::class => function($container) {
        return new MyService();
    },
],
'aliases' => [
    'my_service' => MyServiceInterface::class,
]
```

## Module Service Replacement

Modules can replace any service by listening to the `ServiceLocatorEvent::EVENT_SERVICE_LOCATE` event.

### Step 1: Create Alternative Implementation

```php
<?php
// In your module
class CustomTelemetryService implements TelemetryServiceInterface
{
    public function isTelemetryEnabled(): int
    {
        return 1; // Always enabled in this custom implementation
    }

    public function reportClickEvent(array $data, bool $normalizeUrl = false): false|string
    {
        // Custom telemetry reporting logic
        return json_encode(['success' => true, 'custom' => true]);
    }

    // ... implement other interface methods
}
```

### Step 2: Create Event Listener

```php
<?php
// In openemr.bootstrap.php
use OpenEMR\Events\Services\ServiceLocatorEvent;
use OpenEMR\Telemetry\TelemetryServiceInterface;

function myModuleServiceReplacementListener(ServiceLocatorEvent $event)
{
    $serviceName = $event->getServiceName();
    $context = $event->getContext();

    // Replace TelemetryService only in test environment
    if ($serviceName === TelemetryServiceInterface::class
        && ($context['environment'] ?? '') === 'test') {

        $customService = new CustomTelemetryService();
        $event->setServiceInstance($customService);

        // Optional: log the replacement
        error_log("Module replaced TelemetryService with custom implementation");
    }
}
```

### Step 3: Register the Event Listener

```php
<?php
// In openemr.bootstrap.php
$GLOBALS['kernel']->getEventDispatcher()->addListener(
    ServiceLocatorEvent::EVENT_SERVICE_LOCATE,
    'myModuleServiceReplacementListener'
);
```

## Interface Validation

The ServiceLocator automatically validates that replacement services implement the required interface:

### Validation Process
1. When a module provides a service, it's checked against the requested interface/class
2. If validation fails, an error is logged and the default service is used
3. This prevents broken implementations from affecting system stability

### Debugging Validation Issues

```php
use OpenEMR\Services\ServiceLocator;

// Get detailed validation info for debugging
$service = new MyCustomService();
$validationInfo = ServiceLocator::getValidationInfo(
    MyServiceInterface::class,
    $service
);

print_r($validationInfo);
// Outputs:
// [
//     'serviceName' => 'MyServiceInterface',
//     'serviceClass' => 'MyCustomService',
//     'isInterface' => true,
//     'implementsInterface' => false,  // ← Problem here!
//     'interfaces' => [...],
//     'parents' => [...]
// ]
```

## Singleton Services

Some services in OpenEMR use the Singleton pattern (e.g., `EventAuditLogger`). The ServiceLocator handles these specially:

### Working with Singletons

```php
// Getting a singleton service works the same way
$logger = ServiceLocator::get(EventAuditLoggerInterface::class);
// This returns EventAuditLogger::instance()

// For direct singleton replacement (bypass events)
$customLogger = new MyCustomAuditLogger();
ServiceLocator::replaceSingleton(EventAuditLoggerInterface::class, $customLogger);
```

### Module Replacement of Singletons

For singleton services, modules should use `replaceSingleton()` instead of events:

```php
<?php
// In module bootstrap
function myModuleInit() {
    // Create custom implementation
    $customLogger = new MyCustomEventAuditLogger();

    // Replace the singleton
    ServiceLocator::replaceSingleton(
        EventAuditLoggerInterface::class,
        $customLogger
    );
}
```

**Note:** Event-based replacement still works but returns the original singleton since `EventAuditLogger::instance()` always returns the same instance.

## Advanced Usage

### Conditional Service Replacement

```php
function conditionalServiceReplacements(ServiceLocatorEvent $event)
{
    $serviceName = $event->getServiceName();
    $context = $event->getContext();

    switch ($serviceName) {
        case TelemetryServiceInterface::class:
            // Different implementations based on context
            if ($context['environment'] === 'development') {
                $event->setServiceInstance(new DevelopmentTelemetryService());
            } elseif ($context['testing'] === true) {
                $event->setServiceInstance(new MockTelemetryService());
            }
            break;

        case CryptoGenInterface::class:
            // Only replace for specific users
            if (($context['user_id'] ?? 0) > 1000) {
                $event->setServiceInstance(new EnhancedCryptoGenService());
            }
            break;
    }
}
```

### Service Decoration Pattern

```php
class LoggingProductRegistrationService implements ProductRegistrationServiceInterface
{
    private $wrapped;

    public function __construct(ProductRegistrationServiceInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function registerProduct($email)
    {
        error_log("Registering product with email: " . $email);
        $result = $this->wrapped->registerProduct($email);
        error_log("Registration result: " . ($result ? 'success' : 'failed'));
        return $result;
    }

    // Delegate other methods to wrapped service
    public function getRegistrationStatus(): string
    {
        return $this->wrapped->getRegistrationStatus();
    }

    // ... other methods
}

function decoratorListener(ServiceLocatorEvent $event)
{
    if ($event->getServiceName() === ProductRegistrationServiceInterface::class) {
        // Let the default service be created first
        // This requires handling in a lower priority listener
    }
}

// Register with lower priority to run after default service creation
$GLOBALS['kernel']->getEventDispatcher()->addListener(
    ServiceLocatorEvent::EVENT_SERVICE_LOCATE,
    'decoratorListener',
    -100  // Lower priority
);
```

## Best Practices

### 1. Always Implement Interfaces
```php
// ✅ Good - implements interface
class MyService implements MyServiceInterface { }

// ❌ Bad - no interface compliance
class MyService { }
```

### 2. Use Meaningful Context
```php
// ✅ Good - provides useful context
$service = ServiceLocator::get(MyServiceInterface::class, [
    'user_id' => $userId,
    'environment' => 'production',
    'feature_flags' => $activeFeatures
]);

// ❌ Less useful - minimal context
$service = ServiceLocator::get(MyServiceInterface::class);
```

### 3. Handle Validation Gracefully
```php
function myListener(ServiceLocatorEvent $event)
{
    try {
        $customService = new MyCustomService();

        // Verify our implementation before providing it
        if ($customService instanceof $event->getServiceName()) {
            $event->setServiceInstance($customService);
        }
    } catch (Exception $e) {
        error_log("Failed to create custom service: " . $e->getMessage());
        // Don't set service instance - fall back to default
    }
}
```

### 4. Document Service Replacements
```php
/**
 * MyModule Service Replacements
 *
 * This module replaces the following services:
 * - ProductRegistrationServiceInterface: Adds custom validation
 * - CryptoGenInterface: Uses hardware security module
 *
 * Replacement conditions:
 * - Only in production environment
 * - Only for premium license holders
 */
```

## Migration Guide

### From Direct Instantiation
```php
// Before
$service = new ProductRegistrationService();

// After
$service = ServiceLocator::get(ProductRegistrationServiceInterface::class);
```

### From Static Methods
```php
// Before
$result = ProductRegistrationService::doSomething();

// After - if converting to instance methods
$service = ServiceLocator::get(ProductRegistrationServiceInterface::class);
$result = $service->doSomething();
```

## Why Laminas ServiceManager over Symfony DI?

OpenEMR's ServiceLocator implementation uses **Laminas ServiceManager** rather than Symfony's DependencyInjection Component for several architectural reasons:

### Service Locator vs Dependency Injection Philosophy

**Laminas ServiceManager** is explicitly designed as a **Service Locator**:
```php
// Natural service locator usage
$service = $serviceManager->get(TelemetryServiceInterface::class);
```

**Symfony DI Container** is designed for **Dependency Injection**:
```php
// Symfony DI is meant to inject dependencies, not locate them
class MyController {
    public function __construct(TelemetryServiceInterface $telemetry) {
        // Dependencies injected via constructor
    }
}
```

### Runtime Service Resolution

**Laminas ServiceManager** excels at runtime service creation:
```php
// Easy runtime service location with context
$service = ServiceLocator::get(TelemetryServiceInterface::class, [
    'environment' => 'test',
    'user_id' => 123
]);
```

**Symfony DI** is optimized for compile-time resolution and makes runtime service replacement more complex.

### Event-Driven Architecture Compatibility

**Laminas ServiceManager** integrates naturally with event systems:
```php
public static function get(string $serviceName, array $context = [])
{
    // Dispatch event BEFORE creating service
    $event = new ServiceLocatorEvent($serviceName, $context);
    $GLOBALS['kernel']->getEventDispatcher()->dispatch($event);

    if ($event->hasServiceInstance()) {
        return $event->getServiceInstance(); // Module provided replacement
    }

    return self::$serviceManager->get($serviceName); // Default service
}
```

With Symfony DI, you'd need more complex event listener configuration and compilation steps.

### Module Integration Simplicity

**Laminas ServiceManager** allows simple module override:
```php
// Module can easily replace services at runtime
$eventDispatcher->addListener(ServiceLocatorEvent::EVENT_SERVICE_LOCATE, function($event) {
    if ($event->getServiceName() === TelemetryServiceInterface::class) {
        $event->setServiceInstance(new CustomTelemetryService());
    }
});
```

**Symfony DI** would require CompilerPass definitions, service decoration, container rebuilding, and more complex module integration.

### OpenEMR's Legacy Architecture

OpenEMR has a **procedural/mixed architecture** rather than pure OOP:
```php
// Common OpenEMR pattern - procedural code needs services
function some_openemr_function() {
    $telemetry = ServiceLocator::get(TelemetryServiceInterface::class);
    $telemetry->trackEvent($data);
}
```

Symfony DI works best with pure OOP architecture, controller/service layer separation, and constructor injection patterns.

### Configuration Complexity

**Laminas ServiceManager** - Simple array configuration:
```php
return [
    'factories' => [
        TelemetryServiceInterface::class => function($container) {
            return new TelemetryService();
        },
    ],
    'aliases' => [
        'telemetry' => TelemetryServiceInterface::class,
    ]
];
```

**Symfony DI** requires more complex YAML/XML service definitions and decoration patterns.

### Summary

For OpenEMR's ServiceLocator pattern specifically, **Laminas ServiceManager is the better choice** because:

- ✅ **Aligns with Service Locator pattern** philosophically
- ✅ **Supports runtime service replacement** needed for modules
- ✅ **Integrates easily with event system** for dynamic behavior
- ✅ **Works well with OpenEMR's mixed architecture**
- ✅ **Simple configuration and maintenance**
- ✅ **Lower barrier to entry** for module developers

The ServiceLocator pattern provides a **bridge** that allows OpenEMR to gradually adopt better dependency management practices while maintaining backward compatibility with the existing codebase.

## Troubleshooting

### Service Not Found
```
Laminas\ServiceManager\Exception\ServiceNotFoundException
```
**Solution:** Ensure the service is registered in `ServiceLocator::$defaultConfig`

### Interface Validation Failed
```
ServiceLocator: Module provided service does not implement required interface
```
**Solution:** Verify your custom service implements the correct interface

### Event Not Firing
**Check:**
1. `$GLOBALS['kernel']` is available
2. Event listener is properly registered
3. Event name matches `ServiceLocatorEvent::EVENT_SERVICE_LOCATE`

### Multiple Modules Competing
When multiple modules try to replace the same service:
- First listener to call `setServiceInstance()` wins
- Event propagation stops after first replacement
- Use listener priorities to control order

## Performance Considerations

- Event dispatching adds minimal overhead (~1ms)
- Service validation is lightweight
- Consider caching service instances for heavy objects
- Use lazy loading patterns for expensive services

## Future Services to Implement

The Service Locator pattern can be extended to these core OpenEMR services:

1. **CryptoGen** - Encryption/security services
2. **EventAuditLog** - Audit logging services
3. **DatabaseService** - Database abstraction
4. **FileStorageService** - Document/file handling
5. **NotificationService** - Email/SMS notifications
6. **AuthenticationService** - User authentication
7. **ConfigurationService** - Settings management

Each would follow the same pattern: Interface → Implementation → ServiceLocator registration → Module replacement capability.
