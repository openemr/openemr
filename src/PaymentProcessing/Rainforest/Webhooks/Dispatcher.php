<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

// use Psr\Http\Message\ResponseInterface;
use Throwable;

readonly class Dispatcher
{
    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(
        private array $processors,
        private string $merchantId,
    ) {
    }

    public function dispatch(Webhook $webhook): void
    {
        if ($webhook->getMerchantId() !== $this->merchantId) {
            // log this
            return;
        }

        foreach ($this->getProcessorsFor($webhook->eventType) as $processor) {
            try {
                $processor->handle($webhook);
            } catch (Throwable $e) {
            }
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
