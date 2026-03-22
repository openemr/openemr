<?php

/**
 * OEGlobalsBag polyfill - provides OEGlobalsBag without Symfony/ParameterBag dependency.
 *
 * Used when OpenEMR's src/Core/OEGlobalsBag.php is not available (e.g. older
 * deployed containers that predate the OEGlobalsBag class introduction).
 * OEGlobalsBag::get() simply reads from PHP's $GLOBALS superglobal, which is
 * equivalent behaviour to the real implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 */

namespace OpenEMR\Core;

if (class_exists('OpenEMR\\Core\\OEGlobalsBag', false)) {
    return; // Already defined - nothing to do
}

class OEGlobalsBag
{
    private static ?OEGlobalsBag $instance = null;

    public static function getInstance(): OEGlobalsBag
    {
        if (null === self::$instance) {
            self::$instance = new OEGlobalsBag();
        }
        return self::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $GLOBALS[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $GLOBALS[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $GLOBALS);
    }
}
