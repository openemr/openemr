<?php

/**
 * ServiceLocatorEventTest - Isolated unit tests for ServiceLocatorEvent
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Events\Services;

use OpenEMR\Events\Services\ServiceLocatorEvent;
use PHPUnit\Framework\TestCase;

class ServiceLocatorEventTest extends TestCase
{
    public function testConstructorWithServiceNameOnly(): void
    {
        $serviceName = 'TestService';
        $event = new ServiceLocatorEvent($serviceName);

        $this->assertEquals($serviceName, $event->getServiceName());
        $this->assertEquals([], $event->getContext());
        $this->assertFalse($event->hasServiceInstance());
        $this->assertNull($event->getServiceInstance());
    }

    public function testConstructorWithServiceNameAndContext(): void
    {
        $serviceName = 'TestService';
        $context = ['environment' => 'test', 'user_id' => 123];
        $event = new ServiceLocatorEvent($serviceName, $context);

        $this->assertEquals($serviceName, $event->getServiceName());
        $this->assertEquals($context, $event->getContext());
        $this->assertFalse($event->hasServiceInstance());
        $this->assertNull($event->getServiceInstance());
    }

    public function testSetAndGetServiceInstance(): void
    {
        $event = new ServiceLocatorEvent('TestService');
        $serviceInstance = new TestEventService();

        $this->assertFalse($event->hasServiceInstance());

        $returnedEvent = $event->setServiceInstance($serviceInstance);

        // Should return self for fluent interface
        $this->assertSame($event, $returnedEvent);

        $this->assertTrue($event->hasServiceInstance());
        $this->assertSame($serviceInstance, $event->getServiceInstance());
    }

    public function testSetServiceInstanceStopsPropagation(): void
    {
        $event = new ServiceLocatorEvent('TestService');
        $serviceInstance = new TestEventService();

        $this->assertFalse($event->isPropagationStopped());

        $event->setServiceInstance($serviceInstance);

        $this->assertTrue($event->isPropagationStopped());
    }

    public function testSetContext(): void
    {
        $event = new ServiceLocatorEvent('TestService');
        $context = ['key1' => 'value1', 'key2' => 'value2'];

        $returnedEvent = $event->setContext($context);

        // Should return self for fluent interface
        $this->assertSame($event, $returnedEvent);
        $this->assertEquals($context, $event->getContext());
    }

    public function testAddContext(): void
    {
        $initialContext = ['existing' => 'value'];
        $event = new ServiceLocatorEvent('TestService', $initialContext);

        $returnedEvent = $event->addContext('new_key', 'new_value');

        // Should return self for fluent interface
        $this->assertSame($event, $returnedEvent);

        $expectedContext = ['existing' => 'value', 'new_key' => 'new_value'];
        $this->assertEquals($expectedContext, $event->getContext());
    }

    public function testAddContextOverwritesExisting(): void
    {
        $initialContext = ['key' => 'original_value'];
        $event = new ServiceLocatorEvent('TestService', $initialContext);

        $event->addContext('key', 'new_value');

        $expectedContext = ['key' => 'new_value'];
        $this->assertEquals($expectedContext, $event->getContext());
    }

    public function testGetContextValue(): void
    {
        $context = ['environment' => 'production', 'debug' => true];
        $event = new ServiceLocatorEvent('TestService', $context);

        $this->assertEquals('production', $event->getContextValue('environment'));
        $this->assertTrue($event->getContextValue('debug'));
        $this->assertNull($event->getContextValue('nonexistent'));
        $this->assertEquals('default', $event->getContextValue('nonexistent', 'default'));
    }

    public function testEventConstant(): void
    {
        $this->assertEquals('service.locator.locate', ServiceLocatorEvent::EVENT_SERVICE_LOCATE);
    }

    public function testCompleteWorkflow(): void
    {
        // Simulate a complete event workflow
        $serviceName = 'MyServiceInterface';
        $context = ['environment' => 'test', 'user_id' => 456];

        // Create event
        $event = new ServiceLocatorEvent($serviceName, $context);

        // Verify initial state
        $this->assertEquals($serviceName, $event->getServiceName());
        $this->assertEquals($context, $event->getContext());
        $this->assertFalse($event->hasServiceInstance());

        // Add additional context
        $event->addContext('session_id', 'abc123');

        // Verify context was added
        $this->assertEquals('abc123', $event->getContextValue('session_id'));
        $this->assertEquals('test', $event->getContextValue('environment'));

        // Provide service instance
        $serviceInstance = new TestEventService();
        $event->setServiceInstance($serviceInstance);

        // Verify final state
        $this->assertTrue($event->hasServiceInstance());
        $this->assertSame($serviceInstance, $event->getServiceInstance());
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testMultipleServiceInstanceSets(): void
    {
        $event = new ServiceLocatorEvent('TestService');

        $firstService = new TestEventService();
        $secondService = new TestEventService();

        $event->setServiceInstance($firstService);
        $this->assertSame($firstService, $event->getServiceInstance());

        // Setting again should overwrite
        $event->setServiceInstance($secondService);
        $this->assertSame($secondService, $event->getServiceInstance());
    }

    public function testFluentInterface(): void
    {
        $event = new ServiceLocatorEvent('TestService');
        $service = new TestEventService();

        // Test chaining methods
        $result = $event
            ->setContext(['initial' => 'context'])
            ->addContext('additional', 'value')
            ->setServiceInstance($service);

        $this->assertSame($event, $result);
        $this->assertTrue($event->hasServiceInstance());
        $this->assertEquals('context', $event->getContextValue('initial'));
        $this->assertEquals('value', $event->getContextValue('additional'));
    }
}

// Test class for event testing
class TestEventService
{
    public function doSomething(): string
    {
        return 'test event service';
    }
}
