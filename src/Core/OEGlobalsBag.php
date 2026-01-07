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

use function array_key_exists;

class OEGlobalsBag extends ParameterBag
{
    private static ?OEGlobalsBag $instance = null;

    /**
     * Get the singleton instance of OEGlobalsBag
     *
     * @return OEGlobalsBag
     */
    public static function getInstance(): OEGlobalsBag
    {
        if (null === self::$instance) {
            self::$instance = new OEGlobalsBag($GLOBALS);
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

    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);
    }

    public function set(string $key, mixed $value): void
    {
        parent::set($key, $value);

        // Push the value into GLOBALS for backwards compatibility. Eventually
        // this should be removed.
        $GLOBALS[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!parent::has($key) && array_key_exists($key, $GLOBALS)) {
            return $GLOBALS[$key];
        }

        return parent::get($key, $default);
    }

    public function has(string $key): bool
    {
        if (parent::has($key)) {
            return true;
        }

        return array_key_exists($key, $GLOBALS);
    }
}
