<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025-2026 OpenCoreEMR Inc
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

    /**
     * Check if the kernel is initialized and is the correct type
     */
    public function hasKernel(): bool
    {
        return $this->get('kernel') instanceof Kernel;
    }

    /**
     * Get the OpenEMR Kernel instance
     *
     * @throws \RuntimeException if the kernel is not initialized
     */
    public function getKernel(): Kernel
    {
        $kernel = $this->get('kernel');
        if (!$kernel instanceof Kernel) {
            throw new \RuntimeException('OpenEMR Kernel not initialized');
        }
        return $kernel;
    }
}
