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
        if (preg_match('/^(?:update|delete|replace|truncate|drop|alter|rename)\b/i', $statement) !== 1) {
            return false;
        }

        if (self::dropTargetsGuardedTable($statement) || self::renameTargetsGuardedTable($statement)) {
            return true;
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
     * The upgrade applicator may add a column to an audit table.
     * Application code still cannot.
     */
    public static function isUpgradeSchemaChange(string $sql): bool
    {
        if (!self::rejects($sql)) {
            return false;
        }

        return preg_match('/^alter\s+table\b/i', self::withoutLeadingComments($sql)) === 1;
    }

    /**
     * DROP TABLE IF EXISTS and a multi-table list both name the target after
     * the first keyword the simpler pattern looks for.
     */
    private static function dropTargetsGuardedTable(string $statement): bool
    {
        if (preg_match('/^drop\s+(?:temporary\s+)?table\b/i', $statement) !== 1) {
            return false;
        }

        $rest = preg_replace('/^drop\s+(?:temporary\s+)?table\s+(?:if\s+exists\s+)?/i', '', $statement);
        if (!is_string($rest)) {
            return false;
        }
        $rest = preg_replace('/\s+(?:restrict|cascade)\s*;?\s*$/i', '', $rest) ?? $rest;
        $targets = preg_split('/\s*,\s*/', trim($rest)) ?: [];
        foreach ($targets as $target) {
            if (self::targetIsGuarded($target)) {
                return true;
            }
        }

        return false;
    }

    private static function renameTargetsGuardedTable(string $statement): bool
    {
        if (preg_match('/^rename\s+table\b/i', $statement) !== 1) {
            return false;
        }

        $rest = preg_replace('/^rename\s+table\s+/i', '', $statement);
        if (!is_string($rest)) {
            return false;
        }
        $parts = preg_split('/\s*,\s*|\s+to\s+/i', $rest) ?: [];
        foreach ($parts as $target) {
            if (self::targetIsGuarded($target)) {
                return true;
            }
        }

        return false;
    }

    private static function targetIsGuarded(string $target): bool
    {
        if (preg_match('/`?([A-Za-z0-9_]+)`?\s*$/', trim($target), $match) !== 1) {
            return false;
        }

        return in_array(strtolower($match[1]), self::TABLES, true);
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
