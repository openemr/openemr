<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://opencoreemr.com
 * @author    Michael Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Translation;

use OpenEMR\Core\OEGlobalsBag;

/**
 * Factory for creating and managing Translator instances.
 *
 * Provides singleton-like behavior for performance while allowing reset for testing.
 * The global xl() function and related wrappers use this factory to get the translator.
 */
class TranslatorFactory
{
    /** @var TranslatorInterface|null The shared translator instance */
    private static ?TranslatorInterface $instance = null;

    /** @var bool Whether to use compatibility mode with $GLOBALS */
    private static bool $compatibilityMode = true;

    /**
     * Get the shared translator instance.
     *
     * Creates a new instance if one doesn't exist.
     *
     * @return TranslatorInterface
     */
    public static function getInstance(): TranslatorInterface
    {
        if (self::$instance === null) {
            self::$instance = self::create();
        }

        return self::$instance;
    }

    /**
     * Create a new Translator instance.
     *
     * Used internally and for when a fresh instance is needed.
     *
     * @param OEGlobalsBag|null $globalsBag Optional globals bag
     * @param callable|null $sessionAccessor Optional session accessor for testing
     * @return TranslatorInterface
     */
    public static function create(
        ?OEGlobalsBag $globalsBag = null,
        ?callable $sessionAccessor = null
    ): TranslatorInterface {
        if ($globalsBag === null && self::$compatibilityMode) {
            $globalsBag = OEGlobalsBag::getInstance(true);
        }

        return new Translator($globalsBag, $sessionAccessor);
    }

    /**
     * Set the shared instance.
     *
     * Allows injection of mock translators for testing.
     *
     * @param TranslatorInterface|null $translator The translator to use, or null to clear
     */
    public static function setInstance(?TranslatorInterface $translator): void
    {
        self::$instance = $translator;
    }

    /**
     * Reset the factory state.
     *
     * Clears the shared instance and resets configuration.
     * Essential for testing to ensure clean state between tests.
     */
    public static function reset(): void
    {
        self::$instance = null;
        self::$compatibilityMode = true;
    }

    /**
     * Set compatibility mode.
     *
     * When enabled (default), uses OEGlobalsBag with $GLOBALS synchronization.
     *
     * @param bool $enabled Whether to use $GLOBALS compatibility
     */
    public static function setCompatibilityMode(bool $enabled): void
    {
        self::$compatibilityMode = $enabled;
    }

    /**
     * Check if compatibility mode is enabled.
     *
     * @return bool True if compatibility mode is enabled
     */
    public static function isCompatibilityMode(): bool
    {
        return self::$compatibilityMode;
    }
}
