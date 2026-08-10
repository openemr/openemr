<?php

/**
 * Minimal SQL stubs for CalendarRenderDataBuilder screen tests.
 *
 * Screen event decoration calls dateformat() → getLanguageTitle(), which
 * needs sqlStatement/sqlFetchArray. Isolated PHPUnit has no DB; these
 * stubs return a single English language row so dateformat() can finish
 * without warnings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace {

    if (!function_exists('sqlStatement')) {
        /**
         * @param array<int|string, mixed>|false $binds
         */
        function sqlStatement(string $sql, array|false $binds = false): \ArrayIterator
        {
            // ArrayIterator keeps cursor state across sqlFetchArray calls
            // (plain arrays are passed by value and cannot).
            return new \ArrayIterator([
                ['lang_description' => 'English'],
            ]);
        }
    }

    if (!function_exists('sqlFetchArray')) {
        /**
         * @return array<string, mixed>|false
         */
        function sqlFetchArray(mixed $result): array|false
        {
            if ($result instanceof \Iterator) {
                if (!$result->valid()) {
                    return false;
                }
                $row = $result->current();
                $result->next();
                return is_array($row) ? $row : false;
            }

            return false;
        }
    }

    if (!function_exists('sqlQuery')) {
        /**
         * @param array<int|string, mixed>|false $binds
         * @return array{lang_description: string, lang_is_rtl: int}
         */
        function sqlQuery(string $sql, array|false $binds = false): array
        {
            return [
                'lang_description' => 'English',
                'lang_is_rtl' => 0,
            ];
        }
    }
}
