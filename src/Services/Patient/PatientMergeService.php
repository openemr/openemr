<?php

/**
 * PatientMergeService merges one patient chart (the "source") into another (the "target").
 *
 * This is the engine behind the Merge Patients page. It exists to correct the error of creating a
 * duplicate patient: everything on the source chart that is not identity data (demographics,
 * history, insurance) is repointed at the target chart, the source's documents are moved, and the
 * source's identity rows are deleted.
 *
 * Two behaviors are worth knowing before changing anything here:
 *
 * 1. Encounter deduplication is detected, not chosen. If either chart has an "anonymous"
 *    encounter -- no reason and no encounter type code, which the import path creates to hold
 *    components that did not belong to a real visit -- the merge switches to deduplication mode
 *    and re-derives its target and source from that encounter pair. That can reverse the
 *    direction the operator picked. See {@see self::resolveDuplicateEncounterPair()}.
 * 2. Nothing is written when $production is false. That is the "dry run" mode: the service
 *    reports every statement it would have run without executing any of them.
 *
 * The target chart's duplication score is refreshed at the end of a merge via
 * {@see DuplicatePatientService}, so the Duplicate Patient Management report stops offering a merge
 * that has already happened.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2013-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

use Document;
use OpenEMR\BC\Utilities;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PatientMergeService
{
    /**
     * Tables that describe who the patient is. The target chart's copies are authoritative, so
     * the source's rows are deleted rather than repointed.
     */
    private const IDENTITY_TABLES = ['patient_data', 'history_data', 'insurance_data'];

    /**
     * @param string $documentsDirectory Absolute path of the site's documents directory; each
     *                                   patient's files live in a subdirectory named for their pid.
     * @param bool   $production         False performs a dry run: every step is reported, nothing
     *                                   is written.
     */
    public function __construct(
        private readonly EventAuditLogger $auditLogger,
        private readonly SessionInterface $session,
        private readonly LoggerInterface $logger,
        private readonly DuplicatePatientService $duplicatePatients,
        private readonly string $documentsDirectory,
        private readonly bool $production = true,
    ) {
    }

    public function isProduction(): bool
    {
        return $this->production;
    }

    /**
     * "Lastname, Firstname (pid)" for the chart pickers, or null when no such patient exists.
     */
    public function describePatient(int $pid): ?string
    {
        if ($pid <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow("SELECT lname, fname FROM patient_data WHERE pid = ?", [$pid]);
        if (!is_array($row) || $row === []) {
            return null;
        }

        return self::asString($row['lname'] ?? '') . ', ' . self::asString($row['fname'] ?? '') . " ($pid)";
    }

    /**
     * Merge the source chart into the target chart.
     *
     * Expected failures (charts that are not the same person, a file that could not be moved) come
     * back as an unsuccessful result carrying the steps that had already completed; the caller
     * renders both. Unexpected failures -- a broken query, a filesystem error we do not anticipate
     * -- propagate.
     */
    public function merge(PatientMergeRequest $request): PatientMergeResult
    {
        $log = new PatientMergeLog();

        try {
            $this->performMerge($request, $log);
        } catch (PatientMergeAbortedException $exception) {
            $this->logger->warning('Patient merge aborted', [
                'targetPid' => $request->targetPid,
                'sourcePid' => $request->sourcePid,
                'exception' => $exception,
            ]);

            return PatientMergeResult::failed($log->getSteps(), $exception->getUserMessage());
        }

        return PatientMergeResult::completed($log->getSteps());
    }

    private function performMerge(PatientMergeRequest $request, PatientMergeLog $log): void
    {
        $targetPid = $request->targetPid;
        $sourcePid = $request->sourcePid;

        if ($targetPid === $sourcePid) {
            throw new PatientMergeAbortedException(xl('Target and source pid may not be the same!'));
        }

        // Resolved before anything is touched, because it can redefine which chart is the target.
        $duplicateEncounters = $this->resolveDuplicateEncounterPair($targetPid, $sourcePid);
        if ($duplicateEncounters !== null) {
            $targetPid = self::asInt($duplicateEncounters['target']['pid'] ?? 0);
            $sourcePid = self::asInt($duplicateEncounters['source']['pid'] ?? 0);
        }

        $targetRow = QueryUtils::querySingleRow("SELECT * FROM patient_data WHERE pid = ?", [$targetPid]);
        $sourceRow = QueryUtils::querySingleRow("SELECT * FROM patient_data WHERE pid = ?", [$sourcePid]);

        if (!is_array($targetRow) || self::asInt($targetRow['pid'] ?? 0) <= 0) {
            throw new PatientMergeAbortedException(xl('Target patient not found'));
        }
        if (!is_array($sourceRow) || self::asInt($sourceRow['pid'] ?? 0) <= 0) {
            throw new PatientMergeAbortedException(xl('Source patient not found'));
        }

        if (!$request->skipIdentityChecks) {
            $this->assertSamePerson($targetRow, $sourceRow);
        }

        // Documents go first: a CouchDB failure must abort before any rows have been rewritten.
        $this->moveDocuments($sourcePid, $targetPid, $log);
        $this->moveEncounterDocuments($sourcePid, $targetPid, $log);

        $this->mergeAllTables($sourcePid, $targetPid, $log);

        // The pids have been merged by this point; what is left is folding the source encounter's
        // components into the target encounter.
        if ($duplicateEncounters !== null) {
            $this->mergeDuplicateEncounters($duplicateEncounters, $log);
        }

        $this->duplicatePatients->recalculateScore($targetPid);

        $log->add(xl('Merge complete.'));
    }

    /**
     * Both charts must belong to the same person unless the caller has already established that.
     *
     * @param array<mixed> $targetRow
     * @param array<mixed> $sourceRow
     */
    private function assertSamePerson(array $targetRow, array $sourceRow): void
    {
        if (self::asString($targetRow['ss'] ?? '') !== self::asString($sourceRow['ss'] ?? '')) {
            throw new PatientMergeAbortedException(xl('Target and source SSN do not match'));
        }
        if (Utilities::isDateEmpty($targetRow['DOB'] ?? null)) {
            throw new PatientMergeAbortedException(xl('Target patient has no DOB'));
        }
        if (Utilities::isDateEmpty($sourceRow['DOB'] ?? null)) {
            throw new PatientMergeAbortedException(xl('Source patient has no DOB'));
        }
        if (self::asString($targetRow['DOB'] ?? '') !== self::asString($sourceRow['DOB'] ?? '')) {
            throw new PatientMergeAbortedException(xl('Target and source DOB do not match'));
        }
    }

    /**
     * Repoint the source patient's documents at the target chart.
     *
     * Document::change_patient() also relocates the underlying file, which is why this runs before
     * any database rewriting: if the document store is unreachable we want to have changed nothing.
     */
    private function moveDocuments(int $sourcePid, int $targetPid, PatientMergeLog $log): void
    {
        $documents = QueryUtils::fetchRecords("SELECT * FROM `documents` WHERE `foreign_id` = ?", [$sourcePid]);

        foreach ($documents as $row) {
            $document = new Document(self::asInt($row['id'] ?? 0));
            $log->add(xl('Changing patient ID for document') . ' ' . self::asString($document->get_url_file()));

            if (!$this->production) {
                continue;
            }
            if (!$document->change_patient($targetPid)) {
                throw new PatientMergeAbortedException(xl('Change failed! CouchDB connect error?'));
            }
        }
    }

    /**
     * Move scanned encounter documents into the target chart and delete their container.
     *
     * These files live outside the documents table, under <documents>/<pid>/encounters.
     */
    private function moveEncounterDocuments(int $sourcePid, int $targetPid, PatientMergeLog $log): void
    {
        // Pids reach here as ints and are additionally run through check_file_dir_name().
        // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
        $targetDir = $this->documentsDirectory . '/' . self::asString(check_file_dir_name($targetPid));
        $sourceDir = $this->documentsDirectory . '/' . self::asString(check_file_dir_name($sourcePid));
        $sourceEncounterDir = $sourceDir . '/encounters';
        $targetEncounterDir = $targetDir . '/encounters';

        // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
        if (!is_dir($sourceEncounterDir)) {
            return;
        }

        if ($this->production && !file_exists($targetDir)) { // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
            mkdir($targetDir); // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
        }
        if ($this->production && !file_exists($targetEncounterDir)) { // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
            mkdir($targetEncounterDir); // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
        }

        // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
        $handle = opendir($sourceEncounterDir);
        if ($handle === false) {
            throw new PatientMergeAbortedException(xl('Cannot read directory') . " '" . $sourceEncounterDir . "'");
        }

        try {
            while (false !== ($entry = readdir($handle))) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                if ($entry === 'index.html') {
                    $log->add(xl('Deleting') . " $sourceEncounterDir/$entry");
                    if ($this->production && !unlink("$sourceEncounterDir/$entry")) { // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
                        throw new PatientMergeAbortedException(xl('Delete failed!'));
                    }
                    continue;
                }

                $log->add(
                    xl('Moving') . " $sourceEncounterDir/$entry "
                    . xl('to{{Destination}}') . " $targetEncounterDir/$entry"
                );
                if ($this->production && !rename("$sourceEncounterDir/$entry", "$targetEncounterDir/$entry")) { // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
                    throw new PatientMergeAbortedException(xl('Move failed!'));
                }
            }
        } finally {
            closedir($handle);
        }

        $log->add(xl('Deleting') . ' ' . $sourceEncounterDir);
        if ($this->production && !rmdir($sourceEncounterDir)) { // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
            // Leftover files are not worth failing the whole merge over.
            $log->add(xl('Directory delete failed; continuing.'));
        }
    }

    /**
     * Walk every table in the schema and move the source patient's rows onto the target.
     */
    private function mergeAllTables(int $sourcePid, int $targetPid, PatientMergeLog $log): void
    {
        foreach (QueryUtils::fetchRecords("SHOW TABLES") as $row) {
            $tableName = self::asString(array_values($row)[0] ?? '');
            if ($tableName === '') {
                continue;
            }

            if (in_array($tableName, self::IDENTITY_TABLES, true)) {
                $this->deleteRows($tableName, 'pid', $sourcePid, $targetPid, $log);
            } elseif ($tableName === 'chart_tracker') {
                $this->updateRows($tableName, 'ct_pid', $sourcePid, $targetPid, $log);
            } elseif ($tableName === 'documents') {
                // Already handled by moveDocuments().
                continue;
            } elseif ($tableName === 'openemr_postcalendar_events') {
                $this->updateRows($tableName, 'pc_pid', $sourcePid, $targetPid, $log);
            } elseif ($tableName === 'lists_touch') {
                $this->mergeRows($tableName, 'pid', $sourcePid, $targetPid, $log);
            } elseif ($tableName === 'log') {
                // Audit data is never rewritten.
                continue;
            } else {
                $columnRow = QueryUtils::querySingleRow(
                    "SHOW COLUMNS FROM " . QueryUtils::escapeTableName($tableName)
                    . " WHERE `Field` LIKE 'pid' OR `Field` LIKE 'patient_id'"
                );
                $columnName = is_array($columnRow) ? self::asString($columnRow['Field'] ?? '') : '';
                if ($columnName !== '') {
                    // employer_data lands here; its rows are never deleted and the most recent row
                    // for each patient is the one that is normally relevant.
                    $this->updateRows($tableName, $columnName, $sourcePid, $targetPid, $log);
                }
            }
        }
    }

    /**
     * Delete the source patient's rows from a table whose target-chart copies win outright.
     */
    private function deleteRows(
        string $tableName,
        string $columnName,
        int $sourcePid,
        int $targetPid,
        PatientMergeLog $log
    ): void {
        $count = $this->countRows($tableName, $columnName, $sourcePid);
        if ($count === 0) {
            return;
        }

        $sql = "DELETE FROM " . QueryUtils::escapeTableName($tableName)
            . " WHERE " . QueryUtils::escapeColumnName($columnName, [$tableName]) . " = ?";
        $log->add("$sql ($count)");

        if (!$this->production) {
            return;
        }

        QueryUtils::sqlStatementThrowException($sql, [$sourcePid]);
        $this->logMergeEvent(
            $targetPid,
            'delete',
            "Deleted rows with $columnName = $sourcePid in table $tableName"
        );
    }

    /**
     * Repoint the source patient's rows at the target patient.
     */
    private function updateRows(
        string $tableName,
        string $columnName,
        int $sourcePid,
        int $targetPid,
        PatientMergeLog $log
    ): void {
        $count = $this->countRows($tableName, $columnName, $sourcePid);
        if ($count === 0) {
            return;
        }

        $escapedColumn = QueryUtils::escapeColumnName($columnName, [$tableName]);
        $sql = "UPDATE " . QueryUtils::escapeTableName($tableName)
            . " SET $escapedColumn = ? WHERE $escapedColumn = ?";
        $log->add("$sql ($count)");

        if (!$this->production) {
            return;
        }

        QueryUtils::sqlStatementThrowException($sql, [$targetPid, $sourcePid]);
        $this->logMergeEvent(
            $targetPid,
            'update',
            "Updated rows with $columnName = $sourcePid to $targetPid in table $tableName"
        );
    }

    /**
     * Fold the source patient's rows into the target's, one row per `type`.
     *
     * Used for lists_touch, where both charts can hold a row for the same list type and only one
     * may survive. The older of the two wins.
     */
    private function mergeRows(
        string $tableName,
        string $columnName,
        int $sourcePid,
        int $targetPid,
        PatientMergeLog $log
    ): void {
        $count = $this->countRows($tableName, $columnName, $sourcePid);
        if ($count === 0) {
            return;
        }
        $log->add("$tableName count is ($count)");

        $escapedTable = QueryUtils::escapeTableName($tableName);
        $escapedColumn = QueryUtils::escapeColumnName($columnName, [$tableName]);

        $sourceRows = QueryUtils::fetchRecords("SELECT * FROM $escapedTable WHERE `pid` = ?", [$sourcePid]);
        $targetRows = QueryUtils::fetchRecords("SELECT * FROM $escapedTable WHERE `pid` = ?", [$targetPid]);

        $deleteSql = "DELETE FROM $escapedTable WHERE $escapedColumn = ? AND `type` = ?";
        $promoteSql = "UPDATE $escapedTable SET $escapedColumn = ? WHERE $escapedColumn = ? AND `type` = ?";

        $lastSourceType = '';

        foreach ($sourceRows as $sourceRow) {
            $sourceType = self::asString($sourceRow['type'] ?? '');
            $lastSourceType = $sourceType;

            foreach ($targetRows as $targetRow) {
                if ($sourceType !== self::asString($targetRow['type'] ?? '')) {
                    continue;
                }

                $sourceIsOlder = strcmp(
                    self::asString($sourceRow['date'] ?? ''),
                    self::asString($targetRow['date'] ?? '')
                ) < 0;

                if ($sourceIsOlder) {
                    // Drop the target's row for this type, then promote the source's in its place.
                    $log->add($deleteSql);
                    $log->add($promoteSql);
                    if ($this->production) {
                        QueryUtils::sqlStatementThrowException($deleteSql, [$targetPid, $sourceType]);
                        QueryUtils::sqlStatementThrowException($promoteSql, [$targetPid, $sourcePid, $sourceType]);
                        $this->logMergeEvent(
                            $targetPid,
                            'delete',
                            "Deleted rows with $columnName = $targetPid and type = $sourceType in table $tableName"
                        );
                        $this->logMergeEvent(
                            $targetPid,
                            'update',
                            "Updated rows with $columnName = $sourcePid to $targetPid in table $tableName"
                        );
                    }
                } else {
                    // The target's row is the older one, so the source's is simply discarded.
                    $log->add($deleteSql);
                    if ($this->production) {
                        QueryUtils::sqlStatementThrowException($deleteSql, [$sourcePid, $sourceType]);
                        $this->logMergeEvent(
                            $targetPid,
                            'delete',
                            "Deleted rows with $columnName = $sourcePid and type = $sourceType in table $tableName"
                        );
                    }
                }
            }
        }

        // Source rows survive when the target had no row of that type; those just move across.
        $remaining = $this->countRows($tableName, $columnName, $sourcePid);
        if ($remaining === 0) {
            return;
        }

        $sql = "UPDATE $escapedTable SET $escapedColumn = ? WHERE $escapedColumn = ?";
        $log->add("$sql ($remaining)");

        if (!$this->production) {
            return;
        }

        QueryUtils::sqlStatementThrowException($sql, [$targetPid, $sourcePid]);
        $this->logMergeEvent(
            $targetPid,
            'update',
            "Updated rows with $columnName = $sourcePid and type = $lastSourceType to $targetPid in table $tableName"
        );
    }

    private function countRows(string $tableName, string $columnName, int $pid): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT COUNT(*) AS count FROM " . QueryUtils::escapeTableName($tableName)
            . " WHERE " . QueryUtils::escapeColumnName($columnName, [$tableName]) . " = ?",
            [$pid]
        );

        return is_array($row) ? self::asInt($row['count'] ?? 0) : 0;
    }

    /**
     * Find the anonymous encounter that encounter deduplication works on, and the real encounter it
     * should be folded into.
     *
     * An anonymous encounter has no reason and no encounter type code. The import path creates one
     * to hold components that did not belong to a real visit, which is exactly the case this mode
     * cleans up. Its counterpart is looked for on the other chart, first by an exact date match and
     * then by any encounter whose date range covers it.
     *
     * @return array{target: array<mixed>, source: array<mixed>}|null
     *         null when neither chart has an anonymous encounter, meaning a plain merge.
     *
     * @throws PatientMergeAbortedException when an anonymous encounter exists but nothing on the
     *         other chart lines up with it.
     */
    private function resolveDuplicateEncounterPair(int $targetPid, int $sourcePid): ?array
    {
        $source = QueryUtils::querySingleRow(
            "SELECT e1.date, e1.encounter, e1.reason, e1.encounter_type_code, e1.pid
               FROM `form_encounter` e1
              WHERE e1.pid IN (?, ?) AND e1.reason IS NULL AND e1.encounter_type_code IS NULL
              LIMIT 1",
            [$targetPid, $sourcePid]
        );

        if (!is_array($source) || $source === []) {
            return null;
        }

        // Whichever chart does not own the anonymous encounter is where its counterpart lives.
        $counterpartPid = self::asInt($source['pid'] ?? 0) === $sourcePid ? $targetPid : $sourcePid;
        $sourceDate = self::asString($source['date'] ?? '');

        $target = QueryUtils::querySingleRow(
            "SELECT e1.date, e1.encounter, e1.reason, e1.encounter_type_code, e1.pid
               FROM `form_encounter` e1 WHERE e1.pid = ? AND e1.date = ? LIMIT 1",
            [$counterpartPid, $sourceDate]
        );

        if (!is_array($target) || $target === []) {
            $sourceTimestamp = strtotime($sourceDate);
            $sourceDay = $sourceTimestamp === false ? '' : date('Ymd', $sourceTimestamp);
            $target = QueryUtils::querySingleRow(
                "SELECT e1.date, e1.date_end, e1.encounter, e1.reason, e1.encounter_type_code, e1.pid
                   FROM `form_encounter` e1
                  WHERE e1.pid = ? AND ? BETWEEN e1.date AND e1.date_end LIMIT 1",
                [$counterpartPid, $sourceDay]
            );
        }

        if (!is_array($target) || self::asInt($target['pid'] ?? 0) <= 0) {
            throw new PatientMergeAbortedException(
                xl('Failed to resolve an encounter to deduplicate against. Go back and try a plain merge.')
            );
        }

        return ['target' => $target, 'source' => $source];
    }

    /**
     * Repoint everything hanging off the source encounter at the target encounter, then delete the
     * now-empty source encounter.
     *
     * @param array{target: array<mixed>, source: array<mixed>} $duplicateEncounters
     */
    private function mergeDuplicateEncounters(array $duplicateEncounters, PatientMergeLog $log): void
    {
        $targetPid = self::asInt($duplicateEncounters['target']['pid'] ?? 0);
        $targetEncounter = self::asString($duplicateEncounters['target']['encounter'] ?? '');
        $sourceEncounter = self::asString($duplicateEncounters['source']['encounter'] ?? '');

        if ($targetEncounter === '') {
            return;
        }

        // Every table carrying an encounter reference, discovered from the schema rather than
        // hard-coded so module tables are picked up too.
        $tables = QueryUtils::fetchRecords(
            "SELECT DISTINCT TABLE_NAME AS encounter_table, COLUMN_NAME AS encounter_column
               FROM INFORMATION_SCHEMA.COLUMNS
              WHERE COLUMN_NAME IN ('encounter', 'encounter_id') AND TABLE_SCHEMA = DATABASE()"
        );

        foreach ($tables as $table) {
            $tableName = self::asString($table['encounter_table'] ?? '');
            $columnName = self::asString($table['encounter_column'] ?? '');
            // form_encounter and forms are handled below, where the source encounter is retired.
            if ($tableName === '' || $columnName === '' || $tableName === 'form_encounter' || $tableName === 'forms') {
                continue;
            }

            $escapedColumn = QueryUtils::escapeColumnName($columnName, [$tableName]);
            $sql = "UPDATE " . QueryUtils::escapeTableName($tableName)
                . " SET $escapedColumn = ? WHERE $escapedColumn = ?";

            if (!$this->production) {
                $log->add($sql);
                continue;
            }

            QueryUtils::sqlStatementThrowException($sql, [$targetEncounter, $sourceEncounter]);
            if (QueryUtils::affectedRows()) {
                $log->add("$sql ($targetEncounter) : ($sourceEncounter)");
                $this->logMergeEvent(
                    $targetPid,
                    'update',
                    "Updated for duplicate encounters with $tableName.$columnName = $targetEncounter"
                );
            }
        }

        $escapedForms = QueryUtils::escapeTableName('forms');
        $formsEncounter = QueryUtils::escapeColumnName('encounter', ['forms']);
        $formsFormdir = QueryUtils::escapeColumnName('formdir', ['forms']);

        // Move every form except the encounter's own newpatient form, which is about to go away
        // along with the encounter it describes.
        $moveFormsSql = "UPDATE $escapedForms SET $formsEncounter = ? "
            . "WHERE $formsEncounter = ? AND $formsFormdir != 'newpatient'";
        $log->add($moveFormsSql);

        if (!$this->production) {
            return;
        }

        QueryUtils::sqlStatementThrowException($moveFormsSql, [$targetEncounter, $sourceEncounter]);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `forms` WHERE `encounter` = ? AND `formdir` = 'newpatient'",
            [$sourceEncounter]
        );

        $deleteEncounterSql = "DELETE FROM `form_encounter` WHERE `encounter` = ?";
        QueryUtils::sqlStatementThrowException($deleteEncounterSql, [$sourceEncounter]);
        if (QueryUtils::affectedRows()) {
            $log->add($deleteEncounterSql . $sourceEncounter);
            $this->logMergeEvent(
                $targetPid,
                'delete',
                "deleted duplicate form encounter  = $sourceEncounter after move."
            );
        }
    }

    private function logMergeEvent(int $targetPid, string $eventType, string $logMessage): void
    {
        $this->auditLogger->newEvent(
            'patient-merge-' . $eventType,
            $this->session->get('authUser'),
            $this->session->get('authProvider'),
            1,
            $logMessage,
            $targetPid
        );
    }

    /**
     * Database rows arrive as mixed. These narrow a single cell without letting a non-scalar
     * silently stringify into something meaningless.
     */
    private static function asString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private static function asInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
