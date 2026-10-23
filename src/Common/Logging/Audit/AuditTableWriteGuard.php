<?php

/**
 * Stops the application from changing or deleting audit rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final class AuditTableWriteGuard
{
    /** @var list<string> */
    private const array TABLES = [
        'log',
        'log_comment_encrypt',
        'log_chain_anchor',
    ];

    public static function rejects(string $sql): bool
    {
        $statement = ltrim($sql);
        if (preg_match('/^(?:update|delete|replace|truncate|drop)\b/i', $statement) !== 1) {
            return false;
        }

        foreach (self::TABLES as $table) {
            if (preg_match('/\b' . preg_quote($table, '/') . '\b/i', $statement) === 1) {
                return true;
            }
        }

        return false;
    }
}
