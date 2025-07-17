<?php

namespace OpenEMR\Common\Crypto;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event class for encryption strategy selection.
 *
 * This event is dispatched when a CryptoGen instance is created,
 * allowing modules to register custom encryption strategies.
 *
 * Example usage in a module event listener:
 *
 * ```php
 * use OpenEMR\Common\Crypto\EncryptionStrategyEvent;
 * use YourModule\CustomEncryptionStrategy;
 *
 * // In your module's event listener registration
 * $eventDispatcher->addListener(
 *     EncryptionStrategyEvent::STRATEGY_SELECT,
 *     function (EncryptionStrategyEvent $event) {
 *         // Only set strategy if none has been set yet
 *         if (!$event->hasStrategy()) {
 *             $event->setStrategy(new CustomEncryptionStrategy());
 *         }
 *     }
 * );
 * ```
 *
 * Your custom strategy must implement EncryptionStrategyInterface:
 * - encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
 * - decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
 * - cryptCheckStandard(?string $value): bool
 */
class EncryptionStrategyEvent extends Event
{
    /**
     * The STRATEGY_SELECT event occurs when CryptoGen is instantiated
     * and allows modules to provide a custom encryption strategy.
     */
    const STRATEGY_SELECT = 'crypto.strategy.select';

    private ?EncryptionStrategyInterface $strategy = null;

    /**
     * Set a custom encryption strategy.
     *
     * @param EncryptionStrategyInterface $strategy The encryption strategy to use
     */
    public function setStrategy(EncryptionStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Get the registered encryption strategy.
     *
     * @return EncryptionStrategyInterface|null The registered strategy or null if none set
     */
    public function getStrategy(): ?EncryptionStrategyInterface
    {
        return $this->strategy;
    }

    /**
     * Check if a strategy has been set.
     *
     * @return bool True if a strategy has been registered
     */
    public function hasStrategy(): bool
    {
        return $this->strategy !== null;
    }
}
