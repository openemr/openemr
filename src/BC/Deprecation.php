<?php

namespace OpenEMR\BC;

use LogicException;

/**
 * Utility class to emit deprecation messages in a runtime-configurable mode.
 *
 * This exists for two purposes:
 *
 * OpenEMR (as of writing) targets PHP 8.2+, and native `#[Deprecated]` is 8.4+
 *
 * It can be called in specific paths, such as deprecated kys within
 * a container without the entire container being deprecated.
 *
 */
final class Deprecation
{
    public static DeprecationMode $mode = DeprecationMode::Warning;

    /**
     * @param literal-string $message
     */
    public static function emit(string $message): void
    {
        $message = 'OpenEMR deprecation: ' . $message;
        if (self::$mode === DeprecationMode::Warning) {
            trigger_error($message, E_USER_DEPRECATED);
        } else {
            throw new LogicException($message);
        }
    }
}
