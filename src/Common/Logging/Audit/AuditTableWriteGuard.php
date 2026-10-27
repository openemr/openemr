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
        $statement = self::withoutLeadingComments($sql);
        if (preg_match('/^(?:update|delete|replace|truncate|drop)\b/i', $statement) !== 1) {
            return false;
        }

        foreach (self::TABLES as $table) {
            $pattern = '/\b(?:from|update|into|table|join|truncate)\s+(?:`?[A-Za-z0-9_]+`?\.)?`?'
                . preg_quote($table, '/') . '`?(?![\w$])/i';
            if (preg_match($pattern, $statement) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Drop a comment that sits in front of the statement.
     */
    private static function withoutLeadingComments(string $sql): string
    {
        $statement = ltrim($sql);
        while ($statement !== '') {
            if (str_starts_with($statement, '/*')) {
                $end = strpos($statement, '*/');
                if ($end === false) {
                    return '';
                }
                $statement = ltrim(substr($statement, $end + 2));
                continue;
            }
            if (str_starts_with($statement, '--') || str_starts_with($statement, '#')) {
                $end = strpos($statement, "\n");
                $statement = $end === false ? '' : ltrim(substr($statement, $end + 1));
                continue;
            }

            return $statement;
        }

        return $statement;
    }
}
