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

use OpenEMR\BC\Deprecation;
use OpenEMR\BC\ServiceContainer;
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

    /**
     * Keys being migrated away from OEGlobalsBag. Accessing these via get() or
     * has() emits a deprecation warning. set() is intentionally omitted at
     * this time.
     *
     * When adding a key to this list, update the tests too.
     *
     * @see \OpenEMR\Tests\Isolated\Core\OEGlobalsBagIsolatedTest::deprecatedKeysProvider
     */
    private const DEPRECATED_KEYS = [
        'unit_test_placeholder' => '(placeholder)',
    ];

    protected static function createInstance(): static
    {
        /** @var array<string, mixed> $GLOBALS */
        return new self($GLOBALS);
    }

    private static function emitDeprecationIfNeeded(string $key): void
    {
        if (array_key_exists($key, self::DEPRECATED_KEYS)) {
            Deprecation::emit(sprintf(
                'Key "%s" will be removed from OEGlobalsBag. %s',
                $key,
                self::DEPRECATED_KEYS[$key],
            ));
        }
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
        self::emitDeprecationIfNeeded($key);

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
        self::emitDeprecationIfNeeded($key);

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

    /**
     * Get the include root (project_dir/interface), falling back to the
     * 'include_root' global when the Kernel is not initialized.
     */
    public function getIncludeRoot(): string
    {
        return $this->hasKernel()
            ? $this->getKernel()->getIncludeRoot()
            : $this->getString('include_root');
    }

    public function getString(string $key, string $default = ''): string
    {
        try {
            return parent::getString($key, $default);
        } catch (\UnexpectedValueException $e) {
                $this->reportUnusableValue($key, $default, $e);
                return $default;
        }
    }

    /**
     * Falls back to the default when a stored global cannot satisfy the requested filter,
     * instead of throwing the way ParameterBag does.
     *
     * ParameterBag is built for HTTP request input, where rejecting a malformed value loudly is
     * the correct behavior -- the request is the untrusted thing and failing it is cheap. This
     * bag wraps persisted configuration read from the `globals` table, where the same policy is
     * actively harmful: every typed getter runs during interface/globals.php, so a single bad row
     * takes down every page in the installation, including the Administration > Globals screen an
     * operator would use to correct it. Recovery then requires direct SQL access.
     *
     * The bar is low to trip: `globals`.`gl_value` is `varchar(255) NOT NULL DEFAULT ''`, and an
     * empty string fails FILTER_VALIDATE_INT, so any integer-typed global present in the table
     * with no value is enough.
     *
     * Falling back to the caller's default makes a corrupt value behave exactly like an absent
     * one, which is a state every caller already handles. The substitution is recorded so it does
     * not pass silently.
     *
     * @param array{flags?: int, options?: array<mixed>}|int $options
     */
    public function filter(string $key, mixed $default = null, int $filter = \FILTER_UNSAFE_RAW, mixed $options = []): mixed
    {
        try {
            return parent::filter($key, $default, $filter, $options);
        } catch (\UnexpectedValueException $e) {
            $this->reportUnusableValue($key, $default, $e);
            return $default;
        }
    }

    /**
     * Same fallback as {@see self::filter()}; getEnum() raises its own exception rather than
     * going through filter(), so it needs its own guard.
     *
     * @template T of \BackedEnum
     *
     * @param class-string<T> $class
     * @param ?T $default
     * @return ($default is null ? T|null : T)
     */
    public function getEnum(string $key, string $class, ?\BackedEnum $default = null): ?\BackedEnum
    {
        try {
            return parent::getEnum($key, $class, $default);
        } catch (\UnexpectedValueException $e) {
            $this->reportUnusableValue($key, $default, $e);
            return $default;
        }
    }

    /**
     * Records a global that could not be read as its declared type.
     *
     * Safe to call from filter(): SystemLogger reads its own configuration with get(), which does
     * not route through filter(), so logging a bad value cannot re-enter this path.
     */
    private function reportUnusableValue(string $key, mixed $default, \UnexpectedValueException $e): void
    {
        $stored = $this->get($key);
        ServiceContainer::getLogger()->warning(
            'Global is not usable as its declared type; falling back to the default. '
            . 'Correct it in Administration > Globals.',
            [
                'global' => $key,
                'stored' => is_scalar($stored) ? (string) $stored : get_debug_type($stored),
                'default' => match (true) {
                    $default instanceof \BackedEnum => $default->value,
                    is_scalar($default), $default === null => $default,
                    default => get_debug_type($default),
                },
                'exception' => $e,
            ]
        );
    }
}
