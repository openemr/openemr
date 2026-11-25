<?php

/**
 * The few database reads and writes around a CCDA import: finding the new
 * patient, cleaning up the import's audit staging rows, turning the audit
 * log off while importing, and filling in missing UUIDs afterwards.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Import;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

class ImportDatabase
{
    /** Globals the CCDA import turns off for speed, as import_ccda.php does. */
    private const AUDIT_GLOBALS = ['enable_auditlog', 'gbl_force_log_breakglass'];

    public function patientCount(): int
    {
        return $this->singleInt('SELECT COUNT(*) AS `value` FROM `patient_data`');
    }

    public function maxPid(): int
    {
        return $this->singleInt('SELECT COALESCE(MAX(`pid`), 0) AS `value` FROM `patient_data`');
    }

    /**
     * @return list<int>
     */
    public function pidsAbove(int $pid): array
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT `pid` FROM `patient_data` WHERE `pid` > ? ORDER BY `pid`',
            [$pid],
        );
        return array_map(fn (array $row): int => $this->toInt($row['pid'] ?? null), $rows);
    }

    public function maxAuditMasterId(): int
    {
        return $this->singleInt('SELECT COALESCE(MAX(`id`), 0) AS `value` FROM `audit_master`');
    }

    /**
     * The CCDA import stages each document in audit_master/audit_details;
     * the rows are left behind once the patient exists.
     */
    public function deleteAuditStagingAbove(int $auditMasterId): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `audit_details` WHERE `audit_master_id` > ?',
            [$auditMasterId],
            true,
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `audit_master` WHERE `id` > ?',
            [$auditMasterId],
            true,
        );
    }

    /**
     * Gives the patient a UUID if the import left it without one, and
     * returns it. The manifest records it, so --reuse-import can tell that
     * a pid is still the patient imported there.
     */
    public function assignPatientUuid(int $pid): string
    {
        UuidRegistry::createMissingUuidForRow('patient_data', 'pid', $pid);
        return $this->patientUuid($pid)
            ?? throw new \UnexpectedValueException('Patient ' . $pid . ' has no UUID after import');
    }

    /**
     * The UUID of the patient with this pid, or null if there is no such
     * patient or it has no UUID.
     */
    public function patientUuid(int $pid): ?string
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT `uuid` FROM `patient_data` WHERE `pid` = ?',
            [$pid],
        );
        $uuid = $rows[0]['uuid'] ?? null;
        if (!is_string($uuid) || strlen($uuid) !== 16 || $uuid === str_repeat("\0", 16)) {
            return null;
        }
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * Turns the audit log off and returns the previous values. If that fails
     * part way, the values already changed are put back first.
     *
     * @return array<string, string>
     */
    public function disableAuditLog(): array
    {
        $previous = [];
        try {
            foreach (self::AUDIT_GLOBALS as $name) {
                $rows = QueryUtils::fetchRecordsNoLog(
                    'SELECT `gl_value` FROM `globals` WHERE `gl_name` = ?',
                    [$name],
                );
                $value = $rows[0]['gl_value'] ?? '0';
                $value = is_string($value) ? $value : '0';
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET `gl_value` = 0 WHERE `gl_name` = ?',
                    [$name],
                    true,
                );
                $previous[$name] = $value;
            }
        } catch (\Throwable $e) {
            $this->restoreAuditLog($previous);
            throw $e;
        }
        return $previous;
    }

    /**
     * Puts back the values disableAuditLog() found. A setting that was
     * already off is left alone, and one is put back only while it is still
     * off: if another run overlapped this one, the run that found the log on
     * puts it back, and a run that found it off can't turn it off again
     * afterwards.
     *
     * @param array<string, string> $previous from disableAuditLog()
     */
    public function restoreAuditLog(array $previous): void
    {
        $this->restoreEach(array_keys(array_filter($previous, static fn (string $value): bool => $value !== '0')), $previous);
    }

    /**
     * Puts the settings back one by one; a later one is still tried when an
     * earlier one fails, and the failure propagates afterwards.
     *
     * @param list<string> $names
     * @param array<string, string> $previous
     */
    private function restoreEach(array $names, array $previous): void
    {
        $name = array_shift($names);
        if ($name === null) {
            return;
        }
        try {
            QueryUtils::sqlStatementThrowException(
                "UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = ? AND `gl_value` = '0'",
                [$previous[$name], $name],
                true,
            );
        } finally {
            $this->restoreEach($names, $previous);
        }
    }

    public function populateMissingUuids(): void
    {
        UuidRegistry::populateAllMissingUuids(false);
    }

    private function singleInt(string $sql): int
    {
        $rows = QueryUtils::fetchRecordsNoLog($sql);
        return $this->toInt($rows[0]['value'] ?? null);
    }

    private function toInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }
        throw new \UnexpectedValueException('Expected a whole number from the database');
    }
}
