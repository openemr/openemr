<?php

/**
 * Tool for selecting and managing encryption strategies during installation.
 *
 * This class provides functionality to discover available encryption strategies
 * through the event system and allow selection during the installation process.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

use OpenEMR\Common\Crypto\EncryptionStrategyInterface;
use OpenEMR\Events\Crypto\EncryptionStrategyRegistrationEvent;
use OpenEMR\Common\Crypto\CryptoGenStrategy;
use OpenEMR\Common\Crypto\NullEncryptionStrategy;
use OpenEMR\Common\Logging\SystemLogger;

class EncryptionStrategySelector
{
    private SystemLogger $logger;
    private array $builtinStrategies = [];

    public function __construct()
    {
        $this->logger = new SystemLogger();
        $this->initializeBuiltinStrategies();
    }

    /**
     * Initialize the built-in encryption strategies.
     */
    private function initializeBuiltinStrategies(): void
    {
        $this->builtinStrategies = [
            'cryptogen' => [
                'id' => 'cryptogen',
                'name' => 'Standard Encryption (AES-256-CBC)',
                'description' => 'OpenEMR default encryption using AES-256-CBC with HMAC-SHA384 authentication. Recommended for most installations.',
                'strategy' => new CryptoGenStrategy()
            ],
            'null' => [
                'id' => 'null',
                'name' => 'No Encryption',
                'description' => 'Disables encryption - data is stored in plain text. Only use in development or non-sensitive environments.',
                'strategy' => new NullEncryptionStrategy()
            ]
        ];
    }

    /**
     * Get all available encryption strategies, including built-in and module-provided ones.
     *
     * @return array Array of strategies with keys: id, name, description, strategy
     */
    public function getAvailableStrategies(): array
    {
        $strategies = $this->builtinStrategies;

        // Dispatch event to collect strategies from modules
        $event = new EncryptionStrategyRegistrationEvent();
        if (isset($GLOBALS['kernel']) && method_exists($GLOBALS['kernel'], 'getEventDispatcher')) {
            $this->logger->debug("EncryptionStrategySelector: Dispatching strategy registration event");
            $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, EncryptionStrategyRegistrationEvent::STRATEGY_REGISTRATION);

            // Merge module strategies with built-in ones
            $moduleStrategies = $event->getStrategies();
            $strategies = array_merge($strategies, $moduleStrategies);

            $this->logger->debug("EncryptionStrategySelector: Found strategies", [
                'builtin_count' => count($this->builtinStrategies),
                'module_count' => count($moduleStrategies),
                'total_count' => count($strategies)
            ]);
        } else {
            $this->logger->debug("EncryptionStrategySelector: No event dispatcher available, using built-in strategies only");
        }

        return $strategies;
    }

    /**
     * Get a specific strategy by ID.
     *
     * @param string $id Strategy identifier
     * @return array|null Strategy metadata or null if not found
     */
    public function getStrategy(string $id): ?array
    {
        $strategies = $this->getAvailableStrategies();
        return $strategies[$id] ?? null;
    }

    /**
     * Validate that a strategy ID is valid and available.
     *
     * @param string $id Strategy identifier
     * @return bool True if strategy is valid
     */
    public function isValidStrategy(string $id): bool
    {
        $strategies = $this->getAvailableStrategies();
        return isset($strategies[$id]);
    }

    /**
     * Serialize a strategy for storage in the database.
     *
     * @param string $id Strategy identifier
     * @return string Serialized strategy data
     * @throws \InvalidArgumentException If strategy ID is invalid
     */
    public function serializeStrategy(string $id): string
    {
        $strategyData = $this->getStrategy($id);
        if (!$strategyData) {
            throw new \InvalidArgumentException("Unknown strategy ID: {$id}");
        }

        // Special handling for null strategy - store identifier instead of serialized object
        if ($id === 'null') {
            return 'null';
        }

        $strategy = $strategyData['strategy'];
        // Strategy must implement Serializable interface via EncryptionStrategyInterface
        // No need for instanceof check since interface already requires it

        return serialize($strategy);
    }

    /**
     * Deserialize a strategy from database storage.
     *
     * @param string $serializedData Serialized strategy data
     * @return EncryptionStrategyInterface|null Deserialized strategy or null if invalid
     */
    public function deserializeStrategy(string $serializedData): ?EncryptionStrategyInterface
    {
        // Special handling for null strategy
        if ($serializedData === 'null') {
            return new NullEncryptionStrategy();
        }

        try {
            $strategy = unserialize($serializedData);
            if ($strategy instanceof EncryptionStrategyInterface) {
                return $strategy;
            }
        } catch (\Exception $e) {
            $this->logger->error("EncryptionStrategySelector: Failed to deserialize strategy", [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Get the default strategy ID.
     *
     * @return string Default strategy identifier
     */
    public function getDefaultStrategyId(): string
    {
        return 'cryptogen';
    }

    /**
     * Get strategy metadata for display in installation UI.
     *
     * @return array Array of strategy options for UI display
     */
    public function getStrategyOptions(): array
    {
        $strategies = $this->getAvailableStrategies();
        $options = [];

        foreach ($strategies as $id => $strategy) {
            $options[] = [
                'value' => $id,
                'label' => $strategy['name'],
                'description' => $strategy['description']
            ];
        }

        return $options;
    }

    /**
     * Validate that a strategy object is properly serializable.
     *
     * @param EncryptionStrategyInterface $strategy Strategy to validate
     * @return bool True if strategy can be serialized and deserialized
     */
    public function validateStrategySerializable(EncryptionStrategyInterface $strategy): bool
    {
        // Strategy must implement Serializable interface via EncryptionStrategyInterface

        try {
            $serialized = serialize($strategy);
            $deserialized = unserialize($serialized);
            return $deserialized instanceof EncryptionStrategyInterface;
        } catch (\Exception $e) {
            $this->logger->error("EncryptionStrategySelector: Strategy serialization validation failed", [
                'error' => $e->getMessage(),
                'strategy_class' => get_class($strategy)
            ]);
            return false;
        }
    }
}
