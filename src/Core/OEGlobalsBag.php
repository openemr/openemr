<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Symfony\Component\HttpFoundation\ParameterBag;

class OEGlobalsBag extends ParameterBag
{
    private static ?OEGlobalsBag $instance = null;

    /**
     * Get the singleton instance of OEGlobalsBag
     *
     * @param bool $compatibilityMode Enable two-way sync with $GLOBALS array
     * @return OEGlobalsBag
     */
    public static function getInstance(bool $compatibilityMode = false): OEGlobalsBag
    {
        if (null === self::$instance) {
            // In compatibility mode, import existing $GLOBALS into the bag
            $initialParams = $compatibilityMode ? $GLOBALS : [];
            self::$instance = new OEGlobalsBag($initialParams, $compatibilityMode);
        }

        return self::$instance;
    }

    /**
     * Reset the singleton instance (useful for testing)
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    public function __construct(array $parameters = [], private readonly bool $compatibilityMode = false)
    {
        parent::__construct($parameters);
    }

    public function set(string $key, mixed $value): void
    {
        parent::set($key, $value);

        if ($this->compatibilityMode) {
            // In compatibility mode, also set the value in the global $GLOBALS array
            $GLOBALS[$key] = $value;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // If in compatibility mode and key not in bag, try $GLOBALS
        if ($this->compatibilityMode && !parent::has($key) && array_key_exists($key, $GLOBALS)) {
            return $GLOBALS[$key];
        }

        return parent::get($key, $default);
    }

    public function has(string $key): bool
    {
        $hasInBag = parent::has($key);

        // In compatibility mode, also check $GLOBALS
        if ($this->compatibilityMode && !$hasInBag) {
            return array_key_exists($key, $GLOBALS);
        }

        return $hasInBag;
    }

    public function getKernel(): Kernel
    {
        $this->get('kernel');
    }

    public function setKernel(Kernel $kernel): void
    {
        $this->set('kernel', $kernel);
    }
}
