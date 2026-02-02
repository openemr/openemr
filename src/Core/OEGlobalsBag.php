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

use OpenEMR\Core\Traits\SingletonTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

use function array_key_exists;

/** @final */ class OEGlobalsBag extends ParameterBag
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self($GLOBALS);
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
