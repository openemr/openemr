<?php

/**
 * One lock shared by a signature and a save of the same row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\ESign;

use OpenEMR\Common\Database\QueryUtils;

final class SignatureWriteLock
{
    public static function acquire(string $table, int $id): bool
    {
        if (!self::applies($table, $id)) {
            return true;
        }
        $got = QueryUtils::fetchSingleValue("SELECT GET_LOCK(?, 10) AS got", "got", [self::name($table, $id)]);

        return $got === 1 || $got === "1";
    }

    public static function release(string $table, int $id): void
    {
        if (!self::applies($table, $id)) {
            return;
        }
        QueryUtils::fetchSingleValue("SELECT RELEASE_LOCK(?) AS rel", "rel", [self::name($table, $id)]);
    }

    private static function applies(string $table, int $id): bool
    {
        return $id > 0 && ($table === "forms" || $table === "form_encounter");
    }

    private static function name(string $table, int $id): string
    {
        return "esign:" . $table . ":" . $id;
    }
}
