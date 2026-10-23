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
        try {
            return self::apply();
        } catch (\RuntimeException | \InvalidArgumentException) {
            return 'Audit table protections were not changed.';
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
        $parsed = AuditLogGrantPlan::parseAccount($account);
        $plan = new AuditLogGrantPlan(
            database: $options->dbname,
            applicationUser: $parsed['user'],
            applicationHost: $parsed['host'],
            auditUser: 'openemr_audit',
            auditHost: $parsed['host'],
        );

        $notes = [];
        $triggers = self::run($plan->triggerStatements());
        $notes[] = $triggers === 0
            ? 'Audit rows reject updates and deletes.'
            : 'Audit row triggers could not all be installed.';

        if (AuditDatabaseCredential::read($siteDir) instanceof AuditDatabaseCredential) {
            return implode(' ', $notes);
        }

        $password = bin2hex(random_bytes(24));
        $created = self::run($plan->accountStatements($password));
        if ($created !== 0) {
            $notes[] = 'A separate audit database account was not created. The application account still appends audit rows.';

            return implode(' ', $notes);
        }

        AuditDatabaseCredential::create('openemr_audit', $password)->write($siteDir);
        $notes[] = 'A separate audit database account now appends audit rows.';

        return implode(' ', $notes);
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
