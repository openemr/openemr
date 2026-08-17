<?php

/**
 * CarePlanFormService - Database and business logic for the Care Plan encounter form.
 *
 * This is the persistence layer for the `care_plan` encounter form directory. It is
 * deliberately distinct from {@see \OpenEMR\Services\CarePlanService}, which is the
 * FHIR/USCDI read model over the same table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Forms;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\FormService;

class CarePlanFormService
{
    private const TABLE_NAME = 'form_care_plan';

    public const FORM_DIR = 'care_plan';

    public const FORM_NAME = 'Care Plan Form';

    public const ENGAGEMENT_CATEGORY_LIST_ID = 'care_plan_engagement_category';

    public function __construct(private readonly FormService $formService)
    {
    }

    /**
     * Find the existing care plan form_id for a given encounter, if one exists.
     */
    public function getExistingFormId(int $pid, int $encounter): int
    {
        $sql = "SELECT form_id FROM `forms` WHERE formdir = ? AND pid = ? AND encounter = ? AND deleted = 0 LIMIT 1";
        $records = QueryUtils::fetchRecords($sql, [self::FORM_DIR, $pid, $encounter]);

        return $this->toInt($records[0]['form_id'] ?? null);
    }

    /**
     * Option ids defined for the engagement category list.
     *
     * Read from the database rather than hardcoded so that deployment-added options -- the
     * seed set deliberately leaves seq 70+ free -- stay valid.
     *
     * Inactive options are included on purpose. A value recorded while an option was active
     * has to survive a later re-save after that option is retired; filtering to active
     * options only would silently blank historical data.
     *
     * @return list<string>
     */
    public function getEngagementCategoryOptionIds(): array
    {
        $sql = "SELECT option_id FROM `list_options` WHERE list_id = ?";
        $records = QueryUtils::fetchRecords($sql, [self::ENGAGEMENT_CATEGORY_LIST_ID]);

        $optionIds = [];
        foreach ($records as $record) {
            $optionId = $record['option_id'] ?? null;
            if (is_scalar($optionId)) {
                $optionIds[] = (string) $optionId;
            }
        }

        return $optionIds;
    }

    /**
     * Whether a care plan form is registered against this patient and encounter.
     *
     * Distinct from {@see getExistingFormId()}, which answers "does this encounter have a
     * care plan form" with an arbitrary `LIMIT 1` match. An encounter can carry more than
     * one care plan form -- the UI allows it -- so membership has to be tested for the
     * specific form id rather than compared against a single "the" form id.
     */
    public function formBelongsToEncounter(int $formId, int $pid, int $encounter): bool
    {
        $sql = "SELECT 1 FROM `forms`
            WHERE form_id = ? AND formdir = ? AND pid = ? AND encounter = ? AND deleted = 0
            LIMIT 1";

        return QueryUtils::fetchRecords($sql, [$formId, self::FORM_DIR, $pid, $encounter]) !== [];
    }

    /**
     * Fetch all care plan rows for a given form.
     *
     * `SELECT *` is intentional here. The form must round-trip every column it renders,
     * and deployments may add their own columns to `form_care_plan`; naming columns
     * explicitly would silently drop them on save.
     *
     * @return list<array<mixed>>
     */
    public function getCarePlanRows(int $formId, int $pid, int $encounter): array
    {
        $sql = "SELECT * FROM `" . self::TABLE_NAME . "` WHERE id = ? AND pid = ? AND encounter = ?";

        return QueryUtils::fetchRecords($sql, [$formId, $pid, $encounter]);
    }

    /**
     * Fetch the most recent care plan form recorded for a patient, across all encounters.
     *
     * Backs the patient dashboard card. Returns null when the patient has no care plan
     * form, and null rather than a partial result when the form exists but carries no
     * rows, so callers only have to test one thing.
     *
     * @return array{encounter: int, date: string, rows: list<array<mixed>>}|null
     */
    public function getMostRecentCarePlanForPid(int $pid): ?array
    {
        $sql = "SELECT f.form_id, f.encounter, fe.`date` AS encounter_date
            FROM `forms` f
            JOIN `form_encounter` fe ON (f.encounter = fe.encounter AND f.pid = fe.pid)
            WHERE f.pid = ? AND f.formdir = ? AND f.deleted = 0
            ORDER BY fe.`date` DESC
            LIMIT 1";
        $forms = QueryUtils::fetchRecords($sql, [$pid, self::FORM_DIR]);

        if ($forms === []) {
            return null;
        }

        $form = $forms[0];
        $encounter = $this->toInt($form['encounter'] ?? null);
        $rows = $this->getCarePlanRows($this->toInt($form['form_id'] ?? null), $pid, $encounter);

        if ($rows === []) {
            return null;
        }

        $date = $form['encounter_date'] ?? null;

        return [
            'encounter' => $encounter,
            'date' => is_scalar($date) ? (string) $date : '',
            'rows' => $rows,
        ];
    }

    /**
     * Delete all care plan rows for a given form (used before re-insert on save).
     */
    public function deleteCarePlanRows(int $formId, int $pid, int $encounter): void
    {
        $sql = "DELETE FROM `" . self::TABLE_NAME . "` WHERE id = ? AND pid = ? AND encounter = ?";
        QueryUtils::sqlStatementThrowException($sql, [$formId, $pid, $encounter]);
    }

    /**
     * Insert a single care plan row.
     *
     * @param array<string, mixed> $data
     */
    public function insertCarePlanRow(int $formId, array $data): void
    {
        $sets = "id = ?,
            pid = ?,
            groupname = ?,
            user = ?,
            encounter = ?,
            authorized = ?,
            activity = 1,
            code = ?,
            codetext = ?,
            description = ?,
            date = ?,
            date_end = ?,
            proposed_date = ?,
            plan_status = ?,
            care_plan_type = ?,
            note_related_to = ?,
            reason_code = ?,
            reason_status = ?,
            reason_description = ?,
            reason_date_low = ?,
            reason_date_high = ?,
            plan_engagement_category = ?";

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `" . self::TABLE_NAME . "` SET " . $sets,
            [
                $formId,
                $data['pid'],
                $data['groupname'],
                $data['user'],
                $data['encounter'],
                $data['authorized'],
                $data['code'],
                $data['codetext'],
                $data['description'],
                $data['date'],
                $data['date_end'],
                $data['proposed_date'],
                $data['plan_status'],
                $data['care_plan_type'],
                $data['note_related_to'],
                $data['reason_code'],
                $data['reason_status'],
                $data['reason_description'],
                $data['reason_date_low'],
                $data['reason_date_high'],
                $data['plan_engagement_category'],
            ]
        );
    }

    /**
     * Get the next available form ID (MAX(id) + 1).
     *
     * `form_care_plan.id` is the form id shared by every row of one care plan form, so
     * it cannot be an auto-increment primary key.
     */
    public function getNextFormId(): int
    {
        $sql = "SELECT MAX(id) as largestId FROM `" . self::TABLE_NAME . "`";
        $records = QueryUtils::fetchRecords($sql);
        $maxId = $this->toInt($records[0]['largestId'] ?? null);

        return $maxId > 0 ? $maxId + 1 : 1;
    }

    /**
     * Register the form against the encounter.
     */
    public function registerForm(int $encounter, int $formId, int $pid, int $authorized): void
    {
        $this->formService->addForm($encounter, self::FORM_NAME, $formId, self::FORM_DIR, $pid, $authorized);
    }

    /**
     * Parse note relations from description text.
     *
     * Extracts patterns like {|some text|} and returns them as a JSON array. Supersedes
     * the global `parse_note()` helper for this form.
     */
    public function parseNote(string $note): string
    {
        preg_match_all("/\{\|([^\]]*)\|}/", $note, $matches);

        return json_encode($matches[1]) ?: '[]';
    }

    /**
     * Normalize a string value: null if empty after trimming, otherwise the trimmed value.
     */
    public function normalizeNullableString(string $value): ?string
    {
        $trimmed = trim($value);

        return ($trimmed !== '') ? $trimmed : null;
    }

    /**
     * Narrow a database scalar to an int without casting arbitrary mixed values.
     */
    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
