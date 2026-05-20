<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 *
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025-2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use OpenEMR\Core\Traits\SingletonTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

use function array_key_exists;

/**
 * Typed wrapper around $GLOBALS. Extends Symfony ParameterBag.
 *
 * Prefer typed getters over get() + cast:
 *
 * @see ParameterBag::getString()   getString(string $key, string $default = ''): string
 * @see ParameterBag::getInt()      getInt(string $key, int $default = 0): int
 * @see ParameterBag::getBoolean()  getBoolean(string $key, bool $default = false): bool
 * @see ParameterBag::getAlpha()    getAlpha(string $key, string $default = ''): string — letters only
 * @see ParameterBag::getAlnum()    getAlnum(string $key, string $default = ''): string — alphanumeric only
 * @see ParameterBag::getDigits()   getDigits(string $key, string $default = ''): string — digits only
 * @see ParameterBag::getEnum()     getEnum(string $key, string $class, ?BackedEnum $default = null): ?BackedEnum
 *
 * @final — not enforced at runtime because tests mock this class
 */
class OEGlobalsBag extends ParameterBag
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        /** @var array<string, mixed> $GLOBALS */
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
        // During the transition from $GLOBALS to OEGlobalsBag, legacy code may
        // still write to or unset from $GLOBALS directly. For the singleton
        // instance, use $GLOBALS as the sole source of truth.
        if ($this === (self::$instances[static::class] ?? null)) {
            return array_key_exists($key, $GLOBALS) ? $GLOBALS[$key] : $default;
        }

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

    /**
     * Get the project directory, falling back to the 'fileroot' global
     * when the Kernel is not initialized (e.g. CLI --skip-globals).
     */
    public function getProjectDir(): string
    {
        return $this->hasKernel()
            ? $this->getKernel()->getProjectDir()
            : $this->getString('fileroot');
    }

    /**
     * Get the web root path, falling back to the 'webroot' global
     * when the Kernel is not initialized.
     */
    public function getWebRoot(): string
    {
        return $this->hasKernel()
            ? $this->getKernel()->getWebRoot()
            : $this->getString('webroot');
    }

    /**
     * Get the src (library) directory, falling back to the 'srcdir' global
     * when the Kernel is not initialized.
     */
    public function getSrcDir(): string
    {
        return $this->hasKernel()
            ? $this->getKernel()->getSrcDir()
            : $this->getString('srcdir');
    }
}
