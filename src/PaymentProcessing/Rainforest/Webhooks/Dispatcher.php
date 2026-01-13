<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

// use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class Dispatcher
{
    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(
        private array $processors,
        private string $merchantId,
        private LoggerInterface $logger,
    ) {
    }

    public function dispatch(Webhook $webhook): void
    {
        if ($webhook->getMerchantId() !== $this->merchantId) {
            $this->logger->notice(
                'Received webhook for different merchant, ignoring',
            );
            return;
        }

        $processingError = false;
        foreach ($this->getProcessorsFor($webhook->eventType) as $processor) {
            try {
                $processor->handle($webhook);
            } catch (Throwable $e) {
                $processingError = true;
                $this->logger->error('', [
                    'exception' => $e,
                ]);
            }
        }

        if ($processingError) {
        }
    }

    /**
     * @return ProcessorInterface[]
     */
    private function getProcessorsFor(string $eventType): array
    {
        return array_filter($this->processors, function (ProcessorInterface $processor) use ($eventType): bool {
            return in_array($eventType, $processor->getEventTypes(), true);
        });
    }
}
