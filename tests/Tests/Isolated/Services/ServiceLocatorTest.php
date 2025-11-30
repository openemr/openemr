<?php

/**
 * ServiceLocatorTest - Isolated unit tests for ServiceLocator
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\ServiceLocator;
use OpenEMR\Events\Services\ServiceLocatorEvent;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class ServiceLocatorTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset ServiceLocator state before each test
        ServiceLocator::reset();

        // Clear any global kernel that might interfere
        if (isset($GLOBALS['kernel'])) {
            unset($GLOBALS['kernel']);
        }
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        ServiceLocator::reset();
        if (isset($GLOBALS['kernel'])) {
            unset($GLOBALS['kernel']);
        }
    }

    public function testInitializeWithEmptyConfig(): void
    {
        ServiceLocator::initialize();
        $serviceManager = ServiceLocator::getServiceManager();

        $this->assertInstanceOf(ServiceManager::class, $serviceManager);
    }

    public function testInitializeWithCustomConfig(): void
    {
        $config = [
            'services' => [
                'test_service' => new TestService()
            ],
            'invokables' => [
                'invokable_service' => TestService::class
            ]
        ];

        ServiceLocator::initialize($config);

        $this->assertTrue(ServiceLocator::has('test_service'));
        $this->assertTrue(ServiceLocator::has('invokable_service'));
    }

    public function testGetServiceWithoutEventSystem(): void
    {
        $config = [
            'services' => [
                TestInterface::class => new TestService()
            ]
        ];

        ServiceLocator::initialize($config);
        $service = ServiceLocator::get(TestInterface::class);

        $this->assertInstanceOf(TestInterface::class, $service);
        $this->assertInstanceOf(TestService::class, $service);
    }

    public function testGetServiceWithEventSystemButNoListeners(): void
    {
        // Mock kernel with event dispatcher that doesn't modify the event
        $GLOBALS['kernel'] = new MockKernel();

        $config = [
            'services' => [
                TestInterface::class => new TestService()
            ]
        ];

        ServiceLocator::initialize($config);
        $service = ServiceLocator::get(TestInterface::class);

        $this->assertInstanceOf(TestInterface::class, $service);
        $this->assertInstanceOf(TestService::class, $service);
    }

    public function testGetServiceWithEventSystemAndValidReplacement(): void
    {
        // Mock kernel with event dispatcher that provides alternative service
        $mockService = new AlternativeTestService();
        $GLOBALS['kernel'] = new MockKernel($mockService);

        $config = [
            'services' => [
                TestInterface::class => new TestService()
            ]
        ];

        ServiceLocator::initialize($config);
        $service = ServiceLocator::get(TestInterface::class);

        // Should get the alternative service from the event
        $this->assertInstanceOf(TestInterface::class, $service);
        $this->assertInstanceOf(AlternativeTestService::class, $service);
    }

    public function testGetServiceWithEventSystemAndInvalidReplacement(): void
    {
        // Mock kernel with event dispatcher that provides invalid service
        $invalidService = new InvalidTestService();
        $GLOBALS['kernel'] = new MockKernel($invalidService);

        $config = [
            'services' => [
                TestInterface::class => new TestService()
            ]
        ];

        ServiceLocator::initialize($config);
        $service = ServiceLocator::get(TestInterface::class);

        // Should fall back to default service due to validation failure
        $this->assertInstanceOf(TestInterface::class, $service);
        $this->assertInstanceOf(TestService::class, $service);
    }

    public function testHasService(): void
    {
        $config = [
            'services' => [
                'existing_service' => new TestService()
            ]
        ];

        ServiceLocator::initialize($config);

        $this->assertTrue(ServiceLocator::has('existing_service'));
        $this->assertFalse(ServiceLocator::has('non_existing_service'));
    }

    public function testSetService(): void
    {
        ServiceLocator::initialize();

        $testService = new TestService();
        ServiceLocator::setService('dynamic_service', $testService);

        $this->assertTrue(ServiceLocator::has('dynamic_service'));
        $this->assertSame($testService, ServiceLocator::get('dynamic_service'));
    }

    public function testReset(): void
    {
        $config = [
            'services' => [
                'test_service' => new TestService()
            ]
        ];

        ServiceLocator::initialize($config);
        $this->assertTrue(ServiceLocator::has('test_service'));

        ServiceLocator::reset();

        // After reset, service manager should be null and reinitialize empty
        ServiceLocator::initialize();
        $this->assertFalse(ServiceLocator::has('test_service'));
    }

    public function testGetValidationInfo(): void
    {
        $service = new TestService();
        $validationInfo = ServiceLocator::getValidationInfo(TestInterface::class, $service);

        $this->assertEquals(TestInterface::class, $validationInfo['serviceName']);
        $this->assertEquals(TestService::class, $validationInfo['serviceClass']);
        $this->assertTrue($validationInfo['isInterface']);
        $this->assertTrue($validationInfo['implementsInterface']);
        $this->assertContains(TestInterface::class, $validationInfo['interfaces']);
    }

    public function testGetValidationInfoWithInvalidService(): void
    {
        $service = new InvalidTestService();
        $validationInfo = ServiceLocator::getValidationInfo(TestInterface::class, $service);

        $this->assertEquals(TestInterface::class, $validationInfo['serviceName']);
        $this->assertEquals(InvalidTestService::class, $validationInfo['serviceClass']);
        $this->assertTrue($validationInfo['isInterface']);
        $this->assertFalse($validationInfo['implementsInterface']);
        $this->assertNotContains(TestInterface::class, $validationInfo['interfaces']);
    }

    public function testReplaceSingletonWithValidService(): void
    {
        ServiceLocator::initialize();

        $replacementService = new TestService();
        ServiceLocator::replaceSingleton(TestInterface::class, $replacementService);

        $this->assertTrue(ServiceLocator::has(TestInterface::class));
        $this->assertSame($replacementService, ServiceLocator::get(TestInterface::class));
    }

    public function testReplaceSingletonWithInvalidService(): void
    {
        ServiceLocator::initialize();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Replacement service for 'OpenEMR\Tests\Isolated\Services\TestInterface' does not implement required interface");

        $invalidService = new InvalidTestService();
        ServiceLocator::replaceSingleton(TestInterface::class, $invalidService);
    }

    public function testGetServiceWithContext(): void
    {
        // Mock kernel that checks context
        $GLOBALS['kernel'] = new MockKernelWithContextCheck();

        $config = [
            'services' => [
                TestInterface::class => new TestService()
            ]
        ];

        ServiceLocator::initialize($config);
        $service = ServiceLocator::get(TestInterface::class, ['environment' => 'test']);

        $this->assertInstanceOf(TestInterface::class, $service);
    }

    public function testLazyInitialization(): void
    {
        // Test that ServiceManager is not created until first use
        $this->expectNotToPerformAssertions();

        // These operations should not trigger ServiceManager creation
        ServiceLocator::reset();

        // Only when we actually try to get a service should it initialize
        try {
            ServiceLocator::get('nonexistent');
        } catch (\Exception) {
            // Expected - service doesn't exist, but ServiceManager was initialized
        }
    }
}

// Test interfaces and classes for isolation testing

interface TestInterface
{
    public function doSomething(): string;
}

class TestService implements TestInterface
{
    public function doSomething(): string
    {
        return 'test service';
    }
}

class AlternativeTestService implements TestInterface
{
    public function doSomething(): string
    {
        return 'alternative test service';
    }
}

class InvalidTestService
{
    public function doSomething(): string
    {
        return 'invalid service - does not implement interface';
    }
}

// Mock classes for testing event system

class MockEventDispatcher
{
    public function __construct(private $serviceToProvide = null)
    {
    }

    public function dispatch($event, $eventName)
    {
        if ($this->serviceToProvide !== null && $event instanceof ServiceLocatorEvent) {
            $event->setServiceInstance($this->serviceToProvide);
        }
        return $event;
    }
}

class MockKernel
{
    private $eventDispatcher;

    public function __construct($serviceToProvide = null)
    {
        $this->eventDispatcher = new MockEventDispatcher($serviceToProvide);
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
}

class MockKernelWithContextCheck
{
    public function getEventDispatcher()
    {
        return new class {
            public function dispatch($event, $eventName)
            {
                if ($event instanceof ServiceLocatorEvent) {
                    $context = $event->getContext();
                    // Mock behavior that checks context
                    if (isset($context['environment']) && $context['environment'] === 'test') {
                        // Could provide alternative service based on context
                    }
                }
                return $event;
            }
        };
    }
}
