<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC;

use ErrorException;

use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Utility class to emit deprecation messages in a runtime-configurable mode.
 *
 * This exists for two purposes:
 *
 * 1. OpenEMR (as of writing) targets PHP 8.2+, and native `#[Deprecated]` is 8.4+
 *
 * 2. It can be called in specific paths, such as deprecated keys within
 *    a container without the entire container being deprecated.
 *
 * Legacy bootstrap paths intentionally omit ErrorHandler, so this class
 * provides a direct throw path that works regardless of handler registration.
 */
final class Deprecation
{
    public static DeprecationMode $mode = DeprecationMode::Warning;

    /**
     * @param literal-string $message
     */
    public static function emit(string $message): void
    {
        $message = 'Deprecated: ' . $message;
        if (self::$mode === DeprecationMode::Warning) {
            trigger_error($message, E_USER_DEPRECATED);
        } else {
            throw new ErrorException(
                message: $message,
                severity: E_USER_DEPRECATED,
            );
        }
    }
}
