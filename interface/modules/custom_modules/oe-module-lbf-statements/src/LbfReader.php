<?php

/**
 * Read LBF instances from forms + lbf_data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Common\Database\QueryUtils;

class LbfReader
{
    /**
     * @return array{pid:int,name:string}|null
     */
    public function patientName(int $pid): ?array
    {
        if ($pid <= 0) {
            return null;
        }
        $row = Values::assocRow(QueryUtils::querySingleRow(
            "SELECT pid, fname, lname FROM patient_data WHERE pid = ?",
            [$pid]
        ));
        if ($row === null || Values::rowInt($row, 'pid') <= 0) {
            return null;
        }
        $lname = Values::rowString($row, 'lname');
        $fname = Values::rowString($row, 'fname');
        return [
            'pid' => Values::rowInt($row, 'pid'),
            'name' => trim($lname . ', ' . $fname),
        ];
    }

    /**
     * Patients matching name / pid / pubpid (any layout).
     *
     * @return list<array{pid:int,name:string}>
     */
    public function searchPatientsWithForm(string $formId, string $query, int $limit = 20): array
    {
        if ($formId !== '') {
            Identifiers::assertFieldId($formId);
        }
        $query = trim($query);
        if ($query === '') {
            return [];
        }
        $limit = max(1, min(50, $limit));
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query) . '%';
        $rows = QueryUtils::fetchRecords(
            "SELECT pd.pid, pd.fname, pd.lname FROM patient_data pd " .
            "WHERE pd.lname LIKE ? OR pd.fname LIKE ? OR pd.pubpid LIKE ? OR CAST(pd.pid AS CHAR) LIKE ? " .
            "ORDER BY pd.lname, pd.fname, pd.pid LIMIT " . QueryUtils::escapeLimit($limit),
            [$like, $like, $like, $like]
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $pid = Values::rowInt($row, 'pid');
            if ($pid <= 0) {
                continue;
            }
            $out[] = [
                'pid' => $pid,
                'name' => trim(Values::rowString($row, 'lname') . ', ' . Values::rowString($row, 'fname')),
            ];
        }
        return $out;
    }

    /**
     * Eligible LBF instances on one encounter (formdir has statement rules).
     *
     * @param list<string> $formIds
     * @return list<array{form_id:string,instance_id:int,name:string}>
     */
    public function instancesOnEncounter(int $pid, int $encounter, array $formIds): array
    {
        if ($pid <= 0 || $encounter <= 0 || $formIds === []) {
            return [];
        }
        $safe = [];
        foreach ($formIds as $id) {
            $safe[] = Identifiers::assertFieldId($id);
        }
        $placeholders = implode(',', array_fill(0, count($safe), '?'));
        $bind = array_merge([$pid, $encounter], $safe);
        $rows = QueryUtils::fetchRecords(
            "SELECT f.formdir, f.form_id, f.form_name FROM forms f " .
            "WHERE f.pid = ? AND f.encounter = ? AND f.deleted = 0 AND f.formdir IN ($placeholders) " .
            "ORDER BY f.date DESC",
            $bind
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $out[] = [
                'form_id' => Values::rowString($row, 'formdir'),
                'instance_id' => Values::rowInt($row, 'form_id'),
                'name' => Values::rowString($row, 'form_name'),
            ];
        }
        return $out;
    }

    /**
     * Layouts (among $formIds) this patient already has an instance of, most recent first.
     *
     * @param list<string> $formIds
     * @return list<string>
     */
    public function formdirsForPatient(int $pid, array $formIds): array
    {
        if ($pid <= 0 || $formIds === []) {
            return [];
        }
        $safe = [];
        foreach ($formIds as $id) {
            $safe[] = Identifiers::assertFieldId($id);
        }
        $placeholders = implode(',', array_fill(0, count($safe), '?'));
        $bind = array_merge([$pid], $safe);
        $rows = QueryUtils::fetchRecords(
            "SELECT f.formdir FROM forms f " .
            "WHERE f.pid = ? AND f.deleted = 0 AND f.formdir IN ($placeholders) " .
            "ORDER BY f.date DESC, f.form_id DESC",
            $bind
        );
        $out = [];
        $seen = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $dir = Values::rowString($row, 'formdir');
            if ($dir === '' || isset($seen[$dir])) {
                continue;
            }
            $seen[$dir] = true;
            $out[] = $dir;
        }
        return $out;
    }

    /**
     * @return list<array{instance_id:int,encounter:int,date:string}>
     */
    public function instancesForPatient(string $formId, int $pid): array
    {
        Identifiers::assertFieldId($formId);
        $rows = QueryUtils::fetchRecords(
            "SELECT f.form_id, f.encounter, f.date FROM forms f " .
            "WHERE f.formdir = ? AND f.pid = ? AND f.deleted = 0 ORDER BY f.date DESC, f.form_id DESC",
            [$formId, $pid]
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $out[] = [
                'instance_id' => Values::rowInt($row, 'form_id'),
                'encounter' => Values::rowInt($row, 'encounter'),
                'date' => Values::rowString($row, 'date'),
            ];
        }
        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function instanceRow(int $instanceId, string $formDir): ?array
    {
        Identifiers::assertFieldId($formDir);
        return Values::assocRow(QueryUtils::querySingleRow(
            "SELECT id, pid, encounter, formdir, form_id, date FROM forms " .
            "WHERE form_id = ? AND formdir = ? AND deleted = 0",
            [$instanceId, $formDir]
        ));
    }

    /**
     * @return array<string, string>
     */
    public function readValues(int $instanceId): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT field_id, field_value FROM lbf_data WHERE form_id = ? AND field_id != ''",
            [$instanceId]
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $fieldId = Values::rowString($row, 'field_id');
            if ($fieldId === '') {
                continue;
            }
            $out[$fieldId] = Values::rowString($row, 'field_value');
        }
        return $out;
    }

    public function encounterOwnedBy(int $pid, int $encounter): bool
    {
        $row = Values::assocRow(QueryUtils::querySingleRow(
            "SELECT encounter FROM form_encounter WHERE pid = ? AND encounter = ?",
            [$pid, $encounter]
        ));
        return $row !== null && Values::rowInt($row, 'encounter') === $encounter;
    }
}
