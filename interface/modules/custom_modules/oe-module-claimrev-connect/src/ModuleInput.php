<?php

/**
 * Typed wrapper around filter_input() for $_POST/$_GET/$_SERVER access.
 *
 * Module-local helper that routes all superglobal reads through filter_input()
 * so the openemr.forbiddenRequestGlobals PHPStan rule passes, and gives callers
 * properly typed return values instead of mixed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

final class ModuleInput
{
    public static function postString(string $key, string $default = ''): string
    {
        $v = filter_input(INPUT_POST, $key);
        return is_string($v) ? $v : $default;
    }

    public static function postInt(string $key, int $default = 0): int
    {
        $v = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
        return is_int($v) ? $v : $default;
    }

    public static function postFloat(string $key, float $default = 0.0): float
    {
        $v = filter_input(INPUT_POST, $key, FILTER_VALIDATE_FLOAT);
        return is_float($v) ? $v : $default;
    }

    public static function postBool(string $key): bool
    {
        $v = filter_input(INPUT_POST, $key, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $v === true;
    }

    public static function postExists(string $key): bool
    {
        return filter_input(INPUT_POST, $key) !== null;
    }

    public static function getString(string $key, string $default = ''): string
    {
        $v = filter_input(INPUT_GET, $key);
        return is_string($v) ? $v : $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $v = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
        return is_int($v) ? $v : $default;
    }

    public static function getExists(string $key): bool
    {
        return filter_input(INPUT_GET, $key) !== null;
    }

    public static function isPostRequest(): bool
    {
        return filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST';
    }
}
