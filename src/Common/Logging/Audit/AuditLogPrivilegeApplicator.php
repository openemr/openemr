<?php

/**
 * Installs the audit-row triggers and, when allowed, a write-only account.
 *
 * Failures are reported and do not stop the upgrade. The password is written
 * to the site directory and is not included in the returned message.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

use OpenEMR\BC\DatabaseConnectionOptions;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;

final class AuditLogPrivilegeApplicator
{
    public static function ensure(): string
    {
        $locked = false;
        try {
            $row = QueryUtils::querySingleRow(
                "SELECT GET_LOCK('openemr_audit_provision', 10) AS locked",
                [],
                false,
            );
            $got = is_array($row) ? ($row['locked'] ?? null) : null;
            $locked = $got === 1 || $got === '1';
            if (!$locked) {
                return 'Audit table protections were not changed.';
            }

            return self::apply();
        } catch (\RuntimeException | \InvalidArgumentException | \LogicException) {
            return 'Audit table protections were not changed.';
        } finally {
            if ($locked) {
                try {
                    QueryUtils::sqlStatementThrowException(
                        "SELECT RELEASE_LOCK('openemr_audit_provision')",
                        [],
                        true,
                    );
                } catch (\Throwable) {
                    // The upgrade already has its result.
                }
            }
        }
    }

    private static function apply(): string
    {
        $siteDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR');
        if ($siteDir === '') {
            return 'Audit table protections were not changed.';
        }

        $options = DatabaseConnectionOptions::forSite($siteDir);
        $current = QueryUtils::querySingleRow('SELECT CURRENT_USER() AS account', [], false);
        $account = is_array($current) ? ($current['account'] ?? null) : null;
        if (!is_string($account) || $account === '') {
            return 'Audit table protections were not changed.';
        }
        $notes = [];
        $triggers = self::run(self::missingTriggers(AuditLogGrantPlan::triggerStatements()));
        $notes[] = $triggers === 0
            ? 'Audit rows reject updates and deletes.'
            : 'Audit row triggers could not all be installed.';

        if (AuditDatabaseCredential::read($siteDir) instanceof AuditDatabaseCredential) {
            return implode(' ', $notes);
        }

        $parsed = AuditLogGrantPlan::parseAccount($account);
        $auditUser = 'oe_audit_' . substr(hash('sha256', $options->dbname), 0, 12);
        try {
            $plan = new AuditLogGrantPlan(
                database: $options->dbname,
                applicationUser: $parsed['user'],
                applicationHost: $parsed['host'],
                auditUser: $auditUser,
                auditHost: $parsed['host'],
            );
        } catch (\InvalidArgumentException) {
            $notes[] = 'A separate audit database account was not created. The application account still appends audit rows.';

            return implode(' ', $notes);
        }

        $password = bin2hex(random_bytes(24));
        $created = self::run($plan->accountStatements($password));
        if ($created !== 0) {
            $notes[] = 'A separate audit database account was not created. The application account still appends audit rows.';

            return implode(' ', $notes);
        }

        AuditDatabaseCredential::create($auditUser, $password)->write($siteDir);
        $notes[] = 'A separate audit database account now appends audit rows.';

        return implode(' ', $notes);
    }

    /**
     * Keep a trigger that is already installed. Dropping it before a create
     * that then fails would leave the table unprotected.
     *
     * @param list<string> $statements
     * @return list<string>
     */
    private static function missingTriggers(array $statements): array
    {
        $missing = [];
        foreach ($statements as $statement) {
            if (preg_match('/^CREATE TRIGGER `([A-Za-z0-9_]+)`/', $statement, $match) !== 1) {
                $missing[] = $statement;
                continue;
            }
            $found = QueryUtils::querySingleRow(
                'SELECT TRIGGER_NAME FROM information_schema.TRIGGERS'
                . ' WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME = ?',
                [$match[1]],
                false,
            );
            if (!is_array($found)) {
                $missing[] = $statement;
            }
        }

        return $missing;
    }

    /**
     * @param list<string> $statements
     */
    private static function run(array $statements): int
    {
        $failures = 0;
        foreach ($statements as $statement) {
            try {
                QueryUtils::sqlStatementThrowException($statement, [], true);
            } catch (SqlQueryException) {
                $failures++;
            }
        }

        return $failures;
    }
}
