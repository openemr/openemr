<?php

/**
 * Tests for the Webhooks Dispatcher class
 *
 * @package   OpenEMR
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR, Inc
 * @license   GNU General Public License 3
 * @link      https://www.open-emr.org
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\PaymentProcessing\Rainforest\Webhooks;

use OpenEMR\PaymentProcessing\Rainforest\Webhooks\Dispatcher;
use OpenEMR\PaymentProcessing\Rainforest\Webhooks\ProcessorInterface;
use OpenEMR\PaymentProcessing\Rainforest\Webhooks\Webhook;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class DispatcherTest extends TestCase
{
    private const MERCHANT_ID = 'merchant_abc123';

    // ------- Merchant ID filtering -------

    public function testDispatchIgnoresWebhookForDifferentMerchant(): void
    {
        $processor = $this->createMock(ProcessorInterface::class);
        $processor->method('getEventTypes')->willReturn(['payin.authorized']);
        $processor->expects($this->never())->method('handle');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('notice')
            ->with('Received webhook for different merchant, ignoring');

        $dispatcher = new Dispatcher(
            processors: [$processor],
            merchantId: self::MERCHANT_ID,
            logger: $logger,
            rethrowLastProcessingException: false,
        );

        $webhook = $this->makeWebhook('payin.authorized', 'merchant_other');
        $dispatcher->dispatch($webhook);
    }

    public function testDispatchProcessesWebhookForMatchingMerchant(): void
    {
        $webhook = $this->makeWebhook('payin.authorized', self::MERCHANT_ID);

        $processor = $this->createMock(ProcessorInterface::class);
        $processor->method('getEventTypes')->willReturn(['payin.authorized']);
        $processor->expects($this->once())->method('handle')->with($webhook);

        $dispatcher = new Dispatcher(
            processors: [$processor],
            merchantId: self::MERCHANT_ID,
            logger: $this->createMock(LoggerInterface::class),
            rethrowLastProcessingException: false,
        );

        $dispatcher->dispatch($webhook);
    }

    // ------- Event routing -------

    public function testDispatchRoutesToMatchingProcessorsOnly(): void
    {
        $webhook = $this->makeWebhook('payin.authorized', self::MERCHANT_ID);

        $matching = $this->createMock(ProcessorInterface::class);
        $matching->method('getEventTypes')->willReturn(['payin.authorized']);
        $matching->expects($this->once())->method('handle')->with($webhook);

        $nonMatching = $this->createMock(ProcessorInterface::class);
        $nonMatching->method('getEventTypes')->willReturn(['refund.created']);
        $nonMatching->expects($this->never())->method('handle');

        $dispatcher = new Dispatcher(
            processors: [$matching, $nonMatching],
            merchantId: self::MERCHANT_ID,
            logger: $this->createMock(LoggerInterface::class),
            rethrowLastProcessingException: false,
        );

        $dispatcher->dispatch($webhook);
    }

    public function testDispatchCallsMultipleMatchingProcessors(): void
    {
        $webhook = $this->makeWebhook('payin.authorized', self::MERCHANT_ID);

        $first = $this->createMock(ProcessorInterface::class);
        $first->method('getEventTypes')->willReturn(['payin.authorized']);
        $first->expects($this->once())->method('handle')->with($webhook);

        $second = $this->createMock(ProcessorInterface::class);
        $second->method('getEventTypes')->willReturn(['payin.authorized', 'refund.created']);
        $second->expects($this->once())->method('handle')->with($webhook);

        $dispatcher = new Dispatcher(
            processors: [$first, $second],
            merchantId: self::MERCHANT_ID,
            logger: $this->createMock(LoggerInterface::class),
            rethrowLastProcessingException: false,
        );

        $dispatcher->dispatch($webhook);
    }

    public function testDispatchWithNoMatchingProcessorsDoesNothing(): void
    {
        $webhook = $this->makeWebhook('unknown.event', self::MERCHANT_ID);

        $processor = $this->createMock(ProcessorInterface::class);
        $processor->method('getEventTypes')->willReturn(['payin.authorized']);
        $processor->expects($this->never())->method('handle');

        $dispatcher = new Dispatcher(
            processors: [$processor],
            merchantId: self::MERCHANT_ID,
            logger: $this->createMock(LoggerInterface::class),
            rethrowLastProcessingException: false,
        );

        $dispatcher->dispatch($webhook);
    }

    public function testDispatchWithNoProcessorsDoesNothing(): void
    {
        $this->expectNotToPerformAssertions();

        $webhook = $this->makeWebhook('payin.authorized', self::MERCHANT_ID);

        $dispatcher = new Dispatcher(
            processors: [],
            merchantId: self::MERCHANT_ID,
            logger: $this->createMock(LoggerInterface::class),
            rethrowLastProcessingException: false,
        );

        $dispatcher->dispatch($webhook);
    }

    // ------- Error handling -------

    public function testDispatchCatchesProcessorExceptionAndLogs(): void
    {
        $webhook = $this->makeWebhook('payin.authorized', self::MERCHANT_ID);
        $exception = new RuntimeException('Processing failed');

        $processor = $this->createMock(ProcessorInterface::class);
        $processor->method('getEventTypes')->willReturn(['payin.authorized']);
        $processor->method('handle')->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error');

        $dispatcher = new Dispatcher(
            processors: [$processor],
            merchantId: self::MERCHANT_ID,
            logger: $logger,
            rethrowLastProcessingException: false,
        );

        // Should not throw — exception is caught and logged
        $dispatcher->dispatch($webhook);
    }

    public function testDispatchContinuesProcessingAfterException(): void
    {
        $webhook = $this->makeWebhook('payin.authorized', self::MERCHANT_ID);

        $failing = $this->createMock(ProcessorInterface::class);
        $failing->method('getEventTypes')->willReturn(['payin.authorized']);
        $failing->method('handle')->willThrowException(new RuntimeException('fail'));

        $succeeding = $this->createMock(ProcessorInterface::class);
        $succeeding->method('getEventTypes')->willReturn(['payin.authorized']);
        $succeeding->expects($this->once())->method('handle')->with($webhook);

        $dispatcher = new Dispatcher(
            processors: [$failing, $succeeding],
            merchantId: self::MERCHANT_ID,
            logger: $this->createMock(LoggerInterface::class),
            rethrowLastProcessingException: false,
        );

        $dispatcher->dispatch($webhook);
    }

    public function testDispatchThrowsWhenConfigured(): void
    {
        $webhook = $this->makeWebhook('payin.authorized', self::MERCHANT_ID);

        $failure = new RuntimeException('fail');
        $failing = $this->createMock(ProcessorInterface::class);
        $failing->method('getEventTypes')->willReturn(['payin.authorized']);
        $failing->method('handle')->willThrowException($failure);

        $succeeding = $this->createMock(ProcessorInterface::class);
        $succeeding->method('getEventTypes')->willReturn(['payin.authorized']);
        $succeeding->expects($this->once())->method('handle')->with($webhook);

        $dispatcher = new Dispatcher(
            processors: [$failing, $succeeding],
            merchantId: self::MERCHANT_ID,
            logger: $this->createMock(LoggerInterface::class),
            rethrowLastProcessingException: true,
        );

        $this->expectExceptionObject($failure);
        $dispatcher->dispatch($webhook);
    }

    // ------- Helpers -------

    private function makeWebhook(string $eventType, string $merchantId): Webhook
    {
        return new Webhook('abc123', [
            'event_type' => $eventType,
            'data' => [
                'merchant_id' => $merchantId,
            ],
        ]);
    }
}
