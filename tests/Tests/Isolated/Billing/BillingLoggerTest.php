<?php

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\BillingProcessor\BillingLogger;
use PHPUnit\Framework\TestCase;

/**
 * Isolated tests for BillingLogger class
 * Uses a stub to avoid file system and GLOBALS dependencies
 */
class BillingLoggerTest extends TestCase
{
    public function testPrintToScreenAddsMessage(): void
    {
        $logger = new BillingLoggerStub();

        $logger->printToScreen('Test message 1');
        $logger->printToScreen('Test message 2');

        $billInfo = $logger->bill_info();

        $this->assertIsArray($billInfo);
        $this->assertCount(2, $billInfo);
        $this->assertEquals('Test message 1', $billInfo[0]);
        $this->assertEquals('Test message 2', $billInfo[1]);
    }

    public function testBillInfoReturnsArray(): void
    {
        $logger = new BillingLoggerStub();

        $billInfo = $logger->bill_info();
        $this->assertIsArray($billInfo);
        $this->assertEmpty($billInfo);
    }

    public function testAppendToLogPrependsMessage(): void
    {
        $logger = new BillingLoggerStub();

        // Append messages - they should be prepended to keep most recent on top
        $logger->appendToLog('First message');
        $logger->appendToLog('Second message');

        $hlog = $logger->hlog();

        // Most recent should be first
        $this->assertStringStartsWith('Second message', $hlog);
        $this->assertStringContainsString('First message', $hlog);
    }

    public function testHlogReturnsString(): void
    {
        $logger = new BillingLoggerStub();

        $hlog = $logger->hlog();
        $this->assertIsString($hlog);
    }

    public function testSetLogCompleteCallback(): void
    {
        $logger = new BillingLoggerStub();
        $callbackExecuted = false;

        $callback = function () use (&$callbackExecuted) {
            $callbackExecuted = true;
            return 'callback_result';
        };

        $logger->setLogCompleteCallback($callback);
        $result = $logger->onLogComplete();

        $this->assertTrue($callbackExecuted);
        $this->assertEquals('callback_result', $result);
    }

    public function testOnLogCompleteReturnsFalseWithoutCallback(): void
    {
        $logger = new BillingLoggerStub();

        $result = $logger->onLogComplete();
        $this->assertFalse($result);
    }

    public function testMultipleMessagesToScreen(): void
    {
        $logger = new BillingLoggerStub();

        $messages = ['Message 1', 'Message 2', 'Message 3', 'Message 4', 'Message 5'];
        foreach ($messages as $message) {
            $logger->printToScreen($message);
        }

        $billInfo = $logger->bill_info();
        $this->assertCount(5, $billInfo);
        $this->assertEquals($messages, $billInfo);
    }

    public function testAppendToLogWithMultipleLines(): void
    {
        $logger = new BillingLoggerStub();

        $logger->appendToLog("Line 1\n");
        $logger->appendToLog("Line 2\n");
        $logger->appendToLog("Line 3\n");

        $hlog = $logger->hlog();

        // Should be in reverse order since most recent is prepended
        $this->assertEquals("Line 3\nLine 2\nLine 1\n", $hlog);
    }

    public function testMixedLoggingOperations(): void
    {
        $logger = new BillingLoggerStub();

        // Mix screen and log operations
        $logger->printToScreen('Screen message 1');
        $logger->appendToLog('Log entry 1');
        $logger->printToScreen('Screen message 2');
        $logger->appendToLog('Log entry 2');

        // Verify screen messages
        $billInfo = $logger->bill_info();
        $this->assertCount(2, $billInfo);
        $this->assertEquals('Screen message 1', $billInfo[0]);
        $this->assertEquals('Screen message 2', $billInfo[1]);

        // Verify log messages (reversed order)
        $hlog = $logger->hlog();
        $this->assertStringStartsWith('Log entry 2', $hlog);
        $this->assertStringContainsString('Log entry 1', $hlog);
    }

    public function testCallbackReceivesNoArguments(): void
    {
        $logger = new BillingLoggerStub();
        $callbackArgs = null;

        $callback = function (...$args) use (&$callbackArgs) {
            $callbackArgs = $args;
            return true;
        };

        $logger->setLogCompleteCallback($callback);
        $logger->onLogComplete();

        $this->assertIsArray($callbackArgs);
        $this->assertEmpty($callbackArgs);
    }

    public function testEmptyLogOperations(): void
    {
        $logger = new BillingLoggerStub();

        // Don't add anything, just verify empty states
        $this->assertEmpty($logger->bill_info());
        $this->assertIsString($logger->hlog());
    }
}

/**
 * Stub class to avoid file system and GLOBALS dependencies
 */
class BillingLoggerStub extends BillingLogger
{
    public function __construct()
    {
        // Skip parent constructor to avoid file system and GLOBALS dependencies
        $this->bill_info = [];
        $this->hlog = '';
        $this->cryptoGen = null;
    }

    public function onLogComplete()
    {
        // Skip file writing, just call the callback if set
        if (isset($this->onLogCompleteCallback)) {
            return call_user_func($this->onLogCompleteCallback);
        }

        return false;
    }
}
