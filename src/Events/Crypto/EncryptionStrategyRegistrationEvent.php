<?php

/**
 * Event class for encryption strategy registration during installation.
 *
 * This event is dispatched during the installation process to allow modules
 * to register custom encryption strategies that can be selected by the user.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Crypto;

use Symfony\Contracts\EventDispatcher\Event;
use OpenEMR\Common\Crypto\EncryptionStrategyInterface;

/**
 *
 * Example usage in a module event listener:
 *
 * ```php
 * use OpenEMR\Events\Crypto\EncryptionStrategyRegistrationEvent;
 * use YourModule\CustomEncryptionStrategy;
 *
 * // In your module's event listener registration
 * $eventDispatcher->addListener(
 *     EncryptionStrategyRegistrationEvent::STRATEGY_REGISTRATION,
 *     function (EncryptionStrategyRegistrationEvent $event) {
 *         $event->registerStrategy(
 *             'custom_encryption',
 *             'Custom Encryption Strategy',
 *             'A custom encryption implementation for specialized use cases',
 *             new CustomEncryptionStrategy()
 *         );
 *     }
 * );
 * ```
 *
 * Your custom strategy must implement EncryptionStrategyInterface and Serializable:
 * - encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
 * - decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
 * - cryptCheckStandard(?string $value): bool
 * - serialize(): string
 * - unserialize(string $data): void
 */
class EncryptionStrategyRegistrationEvent extends Event
{
    /**
     * Event name dispatched during installation to collect available encryption strategies.
     */
    const STRATEGY_REGISTRATION = 'crypto.strategy.registration';

    /**
     * @var array Array of registered strategies with metadata
     */
    private array $strategies = [];

    /**
     * Register an encryption strategy for selection during installation.
     *
     * @param string $id Unique identifier for the strategy
     * @param string $name Human-readable name for the strategy
     * @param string $description Description of the strategy's purpose and features
     * @param EncryptionStrategyInterface $strategy The strategy implementation
     * @throws \InvalidArgumentException If strategy ID is already registered or invalid
     */
    public function registerStrategy(string $id, string $name, string $description, EncryptionStrategyInterface $strategy): void
    {
        if (empty($id)) {
            throw new \InvalidArgumentException("Strategy ID cannot be empty");
        }

        if (isset($this->strategies[$id])) {
            throw new \InvalidArgumentException("Strategy ID '{$id}' is already registered");
        }

        // Strategy must implement Serializable interface via EncryptionStrategyInterface
        // No need for instanceof check since interface already requires it

        $this->strategies[$id] = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'strategy' => $strategy
        ];
    }

    /**
     * Get all registered strategies.
     *
     * @return array Array of strategy metadata with keys: id, name, description, strategy
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    /**
     * Get a specific strategy by ID.
     *
     * @param string $id Strategy identifier
     * @return array|null Strategy metadata or null if not found
     */
    public function getStrategy(string $id): ?array
    {
        return $this->strategies[$id] ?? null;
    }

    /**
     * Check if a strategy is registered.
     *
     * @param string $id Strategy identifier
     * @return bool True if strategy is registered
     */
    public function hasStrategy(string $id): bool
    {
        return isset($this->strategies[$id]);
    }

    /**
     * Get the count of registered strategies.
     *
     * @return int Number of registered strategies
     */
    public function getStrategyCount(): int
    {
        return count($this->strategies);
    }
}
