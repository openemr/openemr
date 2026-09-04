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

class LbfReader
{
    /**
     * @param Queries $sql Database access, or a test fake.
     */
    public function __construct(
        private readonly Queries $sql = new Queries()
    ) {
    }

    /**
     * @return array{pid:int,name:string}|null
     */
    public function patientName(int $pid): ?array
    {
        if ($pid <= 0) {
            return null;
        }
        $row = Values::assocRow($this->sql->querySingleRow(
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
        $rows = $this->sql->fetchRecords(
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
        $rows = $this->sql->fetchRecords(
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
     * @return list<array{instance_id:int,encounter:int,date:string,reason:string,form_name:string}>
     */
    public function instancesForPatient(string $formId, int $pid): array
    {
        Identifiers::assertFieldId($formId);
        $rows = $this->sql->fetchRecords(
            "SELECT f.form_id, f.encounter, f.date, f.form_name, " .
            "fe.date AS encounter_date, fe.reason " .
            "FROM forms f " .
            "LEFT JOIN form_encounter fe ON fe.pid = f.pid AND fe.encounter = f.encounter " .
            "WHERE f.formdir = ? AND f.pid = ? AND f.deleted = 0 " .
            "ORDER BY COALESCE(fe.date, f.date) DESC, f.form_id DESC",
            [$formId, $pid]
        );
        $out = [];
        foreach ($rows as $raw) {
            $row = Values::assocRow($raw);
            if ($row === null) {
                continue;
            }
            $date = Values::rowString($row, 'encounter_date');
            if ($date === '') {
                $date = Values::rowString($row, 'date');
            }
            if (function_exists('oeFormatShortDate') && $date !== '') {
                $formatted = oeFormatShortDate(substr($date, 0, 10));
                if (is_string($formatted) && $formatted !== '') {
                    $date = $formatted;
                }
            }
            $reason = trim(strip_tags(Values::rowString($row, 'reason')));
            if (strlen($reason) > 80) {
                $reason = substr($reason, 0, 77) . '...';
            }
            $out[] = [
                'instance_id' => Values::rowInt($row, 'form_id'),
                'encounter' => Values::rowInt($row, 'encounter'),
                'date' => $date,
                'reason' => $reason,
                'form_name' => Values::rowString($row, 'form_name'),
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
        return Values::assocRow($this->sql->querySingleRow(
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
        $rows = $this->sql->fetchRecords(
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

    /**
     * True when this encounter row belongs to $pid.
     */
    public function encounterOwnedBy(int $pid, int $encounter): bool
    {
        $row = Values::assocRow($this->sql->querySingleRow(
            "SELECT encounter FROM form_encounter WHERE pid = ? AND encounter = ?",
            [$pid, $encounter]
        ));
        return $row !== null && Values::rowInt($row, 'encounter') === $encounter;
    }
}
