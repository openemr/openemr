<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @copyright Copyright (c) 2025 sjpadgett@gmail.com
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Symfony\Component\HttpFoundation\ParameterBag;
use Traversable;

/**
 * OEGlobalsBag provides a singleton-style global parameter bag that can optionally
 * mirror changes to the legacy $GLOBALS array when compatibility mode is enabled.
 */
class OEGlobalsBag extends ParameterBag
{
    private static ?OEGlobalsBag $instance = null;

    /**
     * Readonly state object to toggle compatibility mode dynamically.
     * We mutate its internal property, not the reference itself (safe for readonly).
     */
    private readonly \stdClass $compatState;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new OEGlobalsBag();
        }
        return self::$instance;
    }

    /**
     * Globally enable or disable compatibility mode.
     * When enabled, every set() also writes into the global $GLOBALS array.
     */
    public static function setCompatabilityMode(bool $enabled): void
    {
        $instance = self::getInstance();
        $instance->compatState->enabled = $enabled;
    }

    /**
     * Static getter to check global compatibility mode state.
     */
    public static function getCompatMode(): bool
    {
        return self::getInstance()->isCompatModeEnabled();
    }

    public function __construct(
        array $parameters = [],
        private readonly bool $compatMode = false
    ) {
        parent::__construct($parameters);
        $this->compatState = (object)['enabled' => $this->compatMode];
    }

    /**
     * Instance-level getter for current mode.
     */
    public function isCompatModeEnabled(): bool
    {
        return (bool)($this->compatState->enabled ?? false);
    }

    public function set(string $key, mixed $value): void
    {
        parent::set($key, $value);

        if ($this->compatState->enabled) {
            // Mirror into global space for legacy code
            $GLOBALS[$key] = $value;
        }
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator<string, mixed>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     */
    public function count(): int
    {
        return \count($this->parameters);
    }
}
