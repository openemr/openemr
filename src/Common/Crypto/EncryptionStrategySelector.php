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
 *
 */

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

use OpenEMR\Common\Crypto\EncryptionStrategyInterface;
use OpenEMR\Common\Crypto\CryptoGenStrategy;
use OpenEMR\Common\Crypto\NullEncryptionStrategy;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Logging\SystemLogger;

class EncryptionStrategySelector
{
    /**
     * Default encryption strategy identifier.
     */
    public const DEFAULT_STRATEGY_ID = 'cryptogen';

    private SystemLogger $systemLogger;

    /**
     * Array of built-in encryption strategy instances.
     *
     * @var array<string, EncryptionStrategyInterface> Built-in encryption strategies
     */
    private array $builtinStrategies = [];

    public function __construct()
    {
        $this->systemLogger = new SystemLogger();
        $this->initializeBuiltinStrategies();
    }

    /**
     * Initialize the built-in encryption strategies.
     */
    private function initializeBuiltinStrategies(): void
    {
        $this->builtinStrategies = [
            'cryptogen' => new CryptoGenStrategy(),
            'null' => new NullEncryptionStrategy()
        ];
    }

    /**
     * Get all available encryption strategies (core implementations only).
     *
     * @return array<string, EncryptionStrategyInterface> Array of strategy instances indexed by ID
     */
    public function getAvailableStrategies(): array
    {
        return $this->builtinStrategies;
    }

    /**
     * Get a specific strategy instance by ID.
     *
     * @param string $id Strategy identifier
     * @return EncryptionStrategyInterface|null Strategy instance or null if not found
     */
    public function getStrategy(string $id): ?EncryptionStrategyInterface
    {
        return $this->builtinStrategies[$id] ?? null;
    }

    /**
     * Validate that a strategy ID is valid and available.
     *
     * @param string $id Strategy identifier
     * @return bool True if strategy is valid
     */
    public function isValidStrategy(string $id): bool
    {
        return isset($this->builtinStrategies[$id]);
    }

    /**
     * Get a strategy instance by name.
     *
     * @param string $strategyName Strategy identifier
     * @return EncryptionStrategyInterface|null Strategy instance or null if not found
     */
    public function getStrategyByName(string $strategyName): ?EncryptionStrategyInterface
    {
        $strategy = $this->getStrategy($strategyName);
        if (!$strategy instanceof \OpenEMR\Common\Crypto\EncryptionStrategyInterface) {
            $this->systemLogger->warning("EncryptionStrategySelector: Unknown strategy requested", [
                'strategy_name' => $strategyName
            ]);
            return null;
        }

        return $strategy;
    }

    /**
     * Get the default strategy instance.
     *
     * @return EncryptionStrategyInterface Default strategy instance (never null)
     * @throws CryptoGenException If default strategy cannot be found
     */
    public function getDefaultStrategy(): EncryptionStrategyInterface
    {
        $strategy = $this->getStrategy(self::DEFAULT_STRATEGY_ID);
        if ($strategy instanceof \OpenEMR\Common\Crypto\EncryptionStrategyInterface) {
            return $strategy;
        }

        throw new CryptoGenException("Fatal error: Default encryption strategy '" . self::DEFAULT_STRATEGY_ID . "' not found");
    }

    /**
     * Get strategy metadata for display in installation UI.
     *
     * @return array<int, array{value: string, label: string, description: string}> Array of strategy options for UI display
     */
    public function getStrategyOptions(): array
    {
        $strategies = $this->getAvailableStrategies();
        $options = [];

        foreach ($strategies as $strategy) {
            $options[] = [
                'value' => $strategy->getId(),
                'label' => $strategy->getName(),
                'description' => $strategy->getDescription()
            ];
        }

        return $options;
    }

    /**
     * Get the strategy from global configuration.
     *
     * @return EncryptionStrategyInterface Strategy instance based on global configuration
     */
    public function getConfiguredStrategy(): EncryptionStrategyInterface
    {
        $strategyName = $GLOBALS['encryption_strategy_name'] ?? 'cryptogen';
        if (!is_string($strategyName)) {
            $this->systemLogger->warning("EncryptionStrategySelector: Invalid configured strategy type, falling back to default", [
                'configured_strategy' => $strategyName,
                'fallback_strategy' => 'cryptogen'
            ]);
            $strategyName = 'cryptogen';
        }

        $strategy = $this->getStrategyByName($strategyName);

        if ($strategy instanceof \OpenEMR\Common\Crypto\EncryptionStrategyInterface) {
            return $strategy;
        }

        $this->systemLogger->warning("EncryptionStrategySelector: Configured strategy not found, falling back to default", [
            'configured_strategy' => $strategyName,
            'fallback_strategy' => self::DEFAULT_STRATEGY_ID
        ]);
        return $this->getDefaultStrategy();
    }
}
