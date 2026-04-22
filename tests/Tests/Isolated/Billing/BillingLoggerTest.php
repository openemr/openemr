<?php

namespace OpenEMR\Tests\Isolated\Billing;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use OpenEMR\Billing\BillingProcessor\BillingLogger;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\Storage\Location;
use OpenEMR\Services\Storage\ManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Isolated tests for BillingLogger class
 */
class BillingLoggerTest extends TestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
        $GLOBALS['billing_log_option'] = 2;
        $GLOBALS['drive_encryption'] = false;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['billing_log_option'], $GLOBALS['drive_encryption']);
    }

    private function createStorageManager(): ManagerInterface
    {
        $storageManager = $this->createMock(ManagerInterface::class);
        $storageManager->method('getStorage')
            ->with(Location::Documents)
            ->willReturn($this->filesystem);
        return $storageManager;
    }

    private function createLogger(): BillingLogger
    {
        return new BillingLogger(
            $this->createStorageManager(),
            $this->createStub(CryptoInterface::class),
        );
    }

    public function testPrintToScreenAddsMessage(): void
    {
        $logger = $this->createLogger();

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
        $logger = $this->createLogger();

        $billInfo = $logger->bill_info();
        $this->assertIsArray($billInfo);
        $this->assertEmpty($billInfo);
    }

    public function testAppendToLogPrependsMessage(): void
    {
        $logger = $this->createLogger();

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
        $logger = $this->createLogger();

        $hlog = $logger->hlog();
        $this->assertIsString($hlog);
    }

    public function testSetLogCompleteCallback(): void
    {
        $logger = $this->createLogger();
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
        $logger = $this->createLogger();

        $result = $logger->onLogComplete();
        $this->assertFalse($result);
    }

    public function testMultipleMessagesToScreen(): void
    {
        $logger = $this->createLogger();

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
        $logger = $this->createLogger();

        $logger->appendToLog("Line 1\n");
        $logger->appendToLog("Line 2\n");
        $logger->appendToLog("Line 3\n");

        $hlog = $logger->hlog();

        // Should be in reverse order since most recent is prepended
        $this->assertEquals("Line 3\nLine 2\nLine 1\n", $hlog);
    }

    public function testMixedLoggingOperations(): void
    {
        $logger = $this->createLogger();

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
        $logger = $this->createLogger();
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
        $logger = $this->createLogger();

        // Don't add anything, just verify empty states
        $this->assertEmpty($logger->bill_info());
        $this->assertIsString($logger->hlog());
    }

    public function testOnLogCompleteWritesToFilesystem(): void
    {
        $logger = $this->createLogger();

        $logger->appendToLog('Test log content');
        $logger->onLogComplete();

        $this->assertTrue($this->filesystem->fileExists('edi/process_bills.log'));
        $this->assertEquals('Test log content', $this->filesystem->read('edi/process_bills.log'));
    }

    public function testConstructorReadsExistingLogWhenOptionIsOne(): void
    {
        $GLOBALS['billing_log_option'] = 1;
        $this->filesystem->write('edi/process_bills.log', 'Existing log content');

        $crypto = $this->createStub(CryptoInterface::class);
        $crypto->method('cryptCheckStandard')->willReturn(false);

        $logger = new BillingLogger($this->createStorageManager(), $crypto);

        self::assertEquals('Existing log content', $logger->hlog());
    }
}
