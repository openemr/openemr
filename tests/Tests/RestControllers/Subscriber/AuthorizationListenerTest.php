<?php

namespace OpenEMR\Tests\RestControllers\Subscriber;

use OpenEMR\RestControllers\Subscriber\AuthorizationListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use OpenEMR\RestControllers\Authorization\IAuthorizationStrategy;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEHttpKernel;

class AuthorizationListenerTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->markTestIncomplete("Need to implement test for getSubscribedEvents method in AuthorizationListener");
    }

    public function testOnKernelRequestSkipsProcessing()
    {
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->expects($this->atLeastOnce())
            ->method('getSystemLogger')
            ->willReturn($this->createMock(SystemLogger::class));

        $authListener = new AuthorizationListener();
        $mockStrategy = $this->createMock(IAuthorizationStrategy::class);
        $mockStrategy->expects($this->once())
            ->method('shouldProcessRequest')
            ->willReturn(false);

        // should only have a single strategy after clearing
        $authListener->addAuthorizationStrategy($mockStrategy);

        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->expects($this->atLeastOnce())
            ->method('getKernel')
            ->willReturn($kernel);
        $authListener->onKernelRequest($requestEvent);
    }

    public function testOnKernelRequestProcessesSecondStrategyIfFirstIsSkipped()
    {
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->expects($this->atLeastOnce())
            ->method('getSystemLogger')
            ->willReturn($this->createMock(SystemLogger::class));

        $authListener = new AuthorizationListener();
        $mockStrategy = $this->createMock(IAuthorizationStrategy::class);
        $mockStrategy->expects($this->atLeastOnce())
            ->method('shouldProcessRequest')
            ->willReturn(false);

        $authListener->addAuthorizationStrategy($mockStrategy);

        $mockStrategy2 = $this->createMock(IAuthorizationStrategy::class);
        $mockStrategy2->expects($this->atLeastOnce())
            ->method('shouldProcessRequest')
            ->willReturn(true);

        $mockStrategy2->expects($this->once())
            ->method("authorizeRequest")
            ->willReturn(true);

        $authListener->addAuthorizationStrategy($mockStrategy2);

        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->expects($this->atLeastOnce())
            ->method('getKernel')
            ->willReturn($kernel);
        $authListener->onKernelRequest($requestEvent);
    }

    public function testOnRestApiSecurityCheck()
    {
        $this->markTestIncomplete("Need to implement test for onRestApiSecurityCheck method in AuthorizationListener");
    }

    public function testClearAuthorizationStrategies()
    {
        $authListener = new AuthorizationListener();
        $this->assertGreaterThan(0, $authListener->getAuthorizationStrategies(), "Authorization strategies should be initialized and not empty");
        $authListener->clearAuthorizationStrategies();
        $this->assertEmpty($authListener->getAuthorizationStrategies(), "Authorization strategies should be empty after clearing");
    }

    public function testAddAuthorizationStrategy()
    {
        $this->markTestIncomplete("Need to implement test for addAuthorizationStrategy method in AuthorizationListener");
    }
}
