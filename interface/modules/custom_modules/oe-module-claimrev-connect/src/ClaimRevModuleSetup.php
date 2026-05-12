<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Background\BackgroundServiceDefinition;
use OpenEMR\Services\Background\BackgroundServiceRegistry;

class ClaimRevModuleSetup
{
    public function __construct()
    {
    }

    public static function doesPartnerExists(): bool
    {
        $x12Name = OEGlobalsBag::getInstance()->get('oe_claimrev_x12_partner_name');
        $count = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS cnt FROM x12_partners WHERE name = ?",
            'cnt',
            [$x12Name]
        ));
        return $count > 0;
    }
    /**
     * Create the X12 partner record for ClaimRev.
     *
     * Populates the ISA/GS fields per the ClaimRev companion guide:
     * - ISA05/07: ZZ
     * - ISA08/GS03/x12_receiver_id: CLAIMREV
     * - ISA15: P (Production)
     * - Processing format: standard
     */
    public static function createPartnerRecord(string $idNumber = '', string $senderId = ''): void
    {
        $x12Name = OEGlobalsBag::getInstance()->get(GlobalConfig::CONFIG_X12_PARTNER_NAME) ?: 'ClaimRev';

        // Don't create if it already exists
        if (self::doesPartnerExists()) {
            return;
        }

        // Get the next available ID since x12_partners.id is not auto-increment
        $nextId = TypeCoerce::asInt(QueryUtils::fetchSingleValue(
            "SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM x12_partners",
            'next_id',
            []
        ));

        $sql = "INSERT INTO x12_partners (
            id, name, id_number,
            x12_sender_id, x12_receiver_id,
            processing_format,
            x12_isa01, x12_isa02, x12_isa03, x12_isa04,
            x12_isa05, x12_isa07, x12_isa14, x12_isa15,
            x12_gs02, x12_gs03,
            x12_per06, x12_dtp03
        ) VALUES (
            ?, ?, ?,
            ?, 'CLAIMREV',
            'standard',
            '00', '          ', '00', '          ',
            'ZZ', 'ZZ', '0', 'P',
            ?, 'CLAIMREV',
            '', 'A'
        )";

        QueryUtils::sqlStatementThrowException($sql, [$nextId, $x12Name, $idNumber, $senderId, $senderId]);
    }

    public static function couldSftpServiceCauseIssues(): bool
    {
        $sftp = self::getServiceRecord("X12_SFTP");
        if ($sftp === null) {
            return false;
        }
        $active = TypeCoerce::asInt($sftp['active'] ?? 0);
        $requireOnce = TypeCoerce::asString($sftp['require_once'] ?? '');
        return $active === 1 && $requireOnce === '/library/billing_sftp_service.php';
    }

    public static function deactivateSftpService(): void
    {
        $require_once = "/interface/modules/custom_modules/oe-module-claimrev-connect/src/SFTP_Mock_Service.php";
        self::updateBackGroundServiceSetRequireOnce("X12_SFTP", $require_once);
    }

    /**
     * Set the core 'auto_sftp_claims_to_x12_partner' global to '1' and
     * activate the X12_SFTP background service — but only when neither
     * has ever been touched. Respects an explicit '0' that an admin set
     * for either, and never re-activates a service the admin has
     * deliberately disabled.
     *
     * Intended to be called once at module enable time (and from setup
     * when the admin opts into auto-send), not on every request.
     */
    public static function ensureCoreSftpEnabled(): void
    {
        // Single atomic UPDATE per row so we don't race a concurrent admin
        // edit. The WHERE clause is what makes this safe to call repeatedly.
        QueryUtils::sqlStatementThrowException(
            "UPDATE globals SET gl_value = '1' "
            . "WHERE gl_name = 'auto_sftp_claims_to_x12_partner' "
            . "AND (gl_value IS NULL OR gl_value = '')"
        );
        // Activate X12_SFTP only when the service has never been scheduled
        // (last_run IS NULL). On a fresh install that means an admin has
        // never touched it and the documented module-enable behavior is to
        // turn it on. After the service has run once we never flip it back
        // to active here — an admin who deliberately disabled it (for
        // compliance, network policy, etc.) is entitled to keep that
        // setting across module re-enables.
        QueryUtils::sqlStatementThrowException(
            "UPDATE background_services SET active = 1, execute_interval = 1 "
            . "WHERE name = 'X12_SFTP' AND active = 0 AND last_run IS NULL"
        );
    }

    public static function reactivateSftpService(): void
    {
        $require_once = "/library/billing_sftp_service.php";
        self::updateBackGroundServiceSetRequireOnce("X12_SFTP", $require_once);
    }
    public static function updateBackGroundServiceSetRequireOnce(string $name, string $requireOnce): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE background_services SET require_once = ? WHERE name = ?",
            [$requireOnce, $name]
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getServiceRecord(string $name): ?array
    {
        $row = QueryUtils::querySingleRow(
            "SELECT * FROM background_services WHERE name = ? LIMIT 1",
            [$name]
        );
        if (!is_array($row) || $row === []) {
            return null;
        }
        /** @var array<string, mixed> $row */
        return $row;
    }
    /**
     * Reset any ClaimRev background services that are stuck in running state.
     * If running = 1 and next_run is more than 10 minutes in the past,
     * the service is stuck (PHP crash, OOM kill, etc.) and needs to be freed.
     *
     * Excludes ClaimRev_Watchdog itself: the watchdog calls this method, so a
     * watchdog run that exceeds 10 minutes would otherwise clear its own
     * running flag mid-execution and allow a second watchdog to start.
     */
    public static function resetStuckServices(): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE background_services SET running = 0 WHERE running = 1 AND next_run < (NOW() - INTERVAL 10 MINUTE) AND name LIKE '%ClaimRev%' AND name != 'ClaimRev_Watchdog'"
        );
    }

    /**
     * Run our module's table.sql directly without the core SQLUpgradeService,
     * which fires events that trigger unrelated core upgrade scripts.
     *
     * Supports: CREATE/INSERT/ALTER/UPDATE/DELETE statements,
     * #IfNotRow, #IfNotColumnType, #IfNotTable, #EndIf directives.
     */
    public static function runMigrations(): void
    {
        $modulePath = dirname(__DIR__);
        $fullname = $modulePath . '/table.sql';
        $fd = fopen($fullname, 'r');
        if ($fd === false) {
            return;
        }

        $query = '';
        $skipping = false;

        while (!feof($fd)) {
            $line = fgets($fd, 2048);
            if ($line === false) {
                break;
            }
            $line = rtrim($line);

            if (preg_match('/^\s*--/', $line) || $line === '') {
                continue;
            }

            if (preg_match('/^#IfNotRow\s+(\S+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                // Whitelist against actual schema; both helpers return the
                // identifier already wrapped in backticks and throw on miss.
                $tbl = QueryUtils::escapeTableName($matches[1]);
                $col = QueryUtils::escapeColumnName($matches[2], [$matches[1]]);
                $row = QueryUtils::fetchSingleValue(
                    "SELECT 1 AS x FROM $tbl WHERE $col = ?",
                    'x',
                    [trim($matches[3])]
                );
                $skipping = $row !== null;
                continue;
            } elseif (preg_match('/^#IfNotTable\s+(\S+)/', $line, $matches)) {
                $skipping = QueryUtils::existsTable($matches[1]);
                continue;
            } elseif (preg_match('/^#IfNotColumnType\s+(\S+)\s+(\S+)\s+(\S+)/', $line, $matches)) {
                $columnType = QueryUtils::fetchSingleValue(
                    "SELECT COLUMN_TYPE FROM information_schema.columns "
                    . "WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
                    'COLUMN_TYPE',
                    [$matches[1], $matches[2]]
                );
                $skipping = $columnType !== null && stripos(TypeCoerce::asString($columnType), $matches[3]) !== false;
                continue;
            } elseif (preg_match('/^#(EndIf|Endif)/i', $line)) {
                $skipping = false;
                continue;
            } elseif (preg_match('/^#/', $line)) {
                continue;
            }

            if ($skipping) {
                continue;
            }

            $query .= $line;
            if (preg_match('/;\s*$/', $query)) {
                $query = rtrim($query, "; \t\n\r");
                if (trim($query) !== '') {
                    QueryUtils::sqlStatementThrowException($query);
                }
                $query = '';
            }
        }

        fclose($fd);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function getBackgroundServices(): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT * FROM background_services WHERE name like '%ClaimRev%' OR name = 'X12_SFTP'"
        );
        $out = [];
        foreach ($rows as $row) {
            /** @var array<string, mixed> $row */
            $out[] = $row;
        }
        return $out;
    }
    public static function createBackGroundServices(): void
    {
        // Use BackgroundServiceRegistry so module upgrades don't silently
        // reset an admin's enable/disable toggle. The Registry's "first
        // install wins" policy preserves the active flag on upsert;
        // reinstalling this module no longer wipes admin preferences the
        // way the previous DELETE-then-INSERT pattern did.
        $registry = new BackgroundServiceRegistry();
        $billingPath = '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php';
        $eligibilityPath = '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Eligibility_ClaimRev_Service.php';
        $notificationPath = '/interface/modules/custom_modules/oe-module-claimrev-connect/src/ClaimRev_Notification_Service.php';
        $watchdogPath = '/interface/modules/custom_modules/oe-module-claimrev-connect/src/ClaimRev_Watchdog_Service.php';

        $registry->register(new BackgroundServiceDefinition(
            name: 'ClaimRev_Send',
            title: 'Send Claims To ClaimRev',
            function: 'start_X12_Claimrev_send_files',
            requireOnce: $billingPath,
            executeInterval: 1,
            sortOrder: 100,
            active: true,
        ));

        $registry->register(new BackgroundServiceDefinition(
            name: 'ClaimRev_Receive',
            title: 'Get Reports from ClaimRev',
            function: 'start_X12_Claimrev_get_reports',
            requireOnce: $billingPath,
            executeInterval: 240,
            sortOrder: 100,
            active: true,
        ));

        $registry->register(new BackgroundServiceDefinition(
            name: 'ClaimRev_Elig_Send_Receive',
            title: 'Send and Receive Eligibility from ClaimRev',
            function: 'start_send_eligibility',
            requireOnce: $eligibilityPath,
            executeInterval: 1,
            sortOrder: 100,
            active: true,
        ));

        $registry->register(new BackgroundServiceDefinition(
            name: 'ClaimRev_Notifications',
            title: 'ClaimRev Notification Check',
            function: 'start_claimrev_notifications',
            requireOnce: $notificationPath,
            executeInterval: 60,
            sortOrder: 100,
            active: true,
        ));

        $registry->register(new BackgroundServiceDefinition(
            name: 'ClaimRev_Watchdog',
            title: 'ClaimRev Stuck Service Watchdog',
            function: 'start_claimrev_watchdog',
            requireOnce: $watchdogPath,
            executeInterval: 20,
            sortOrder: 50,
            active: true,
        ));
    }
}
