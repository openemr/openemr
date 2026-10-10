<?php

/**
 * Decides which questionnaires are offered on a given surface.
 *
 * Single place that answers "which questionnaires should this user see here", replacing
 * four independent and inconsistent answers:
 *
 *  - the patient dashboard read every row with `questionnaire_repository`.`active` = 1
 *  - the encounter read every `registry` row in the questionnaire_assessments directory
 *  - the portal went through document_templates / document_template_profiles
 *  - SMART was gated on a single global rather than bound per questionnaire
 *
 * An assignment names its target one of two ways, and never both:
 *
 *  - `resource_id` set: the row applies to that one questionnaire.
 *  - `resource_id` NULL and `category` set: the row applies to every questionnaire whose
 *    own `questionnaire_repository`.`category` matches, so "offer every SDOH
 *    questionnaire in the portal" is one row rather than one per questionnaire, and a
 *    questionnaire imported later into that category is covered without a new row.
 *
 * Category is a property of the resource rather than of the caller, which is why it has
 * no counterpart in AvailabilityContext. A row with neither set matches nothing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;

/**
 * @phpstan-type AssignmentRow array{
 *     id: int,
 *     resource_id: int,
 *     surface: string,
 *     acl_section: ?string,
 *     acl_level: ?string,
 *     seq: int
 * }
 */
class QuestionnaireAvailabilityService
{
    public const TABLE_NAME = 'content_assignments';

    /**
     * Only questionnaires participate today. The column exists so document templates and
     * other content can be assigned through the same table without a migration.
     */
    public const RESOURCE_TYPE = 'questionnaire';

    /**
     * Every questionnaire registered as an encounter form shares this registry directory
     * and is told apart by `registry`.`form_foreign_id`.
     */
    public const ENCOUNTER_FORM_DIRECTORY = 'questionnaire_assessments';


    /**
     * Questionnaire ids available on this surface, in assignment order.
     *
     * ACL is evaluated per assignment, so two questionnaires on the same surface can
     * require different permissions. An assignment with no `acl_section` falls back to
     * the surface default, which is the check that surface already performed.
     *
     * @return list<int>
     */
    public function getAvailableIds(AvailabilityContext $context): array
    {
        $ids = [];
        foreach ($this->fetchAssignments($context) as $assignment) {
            if (!$this->isPermitted($assignment, $context->surface)) {
                continue;
            }
            $resourceId = $assignment['resource_id'];
            // a questionnaire can match more than one assignment row on the same surface
            // (one unscoped, one scoped to a facility, say). Matching twice still means
            // visible once.
            $ids[$resourceId] = $resourceId;
        }

        return array_values($ids);
    }

    /**
     * Full questionnaire_repository rows for a surface, ordered for display.
     *
     * Returns the repository columns the callers already use plus the resolved category
     * title, so a call site can drop its own SELECT entirely.
     *
     * @return list<array<string, mixed>>
     */
    public function getAvailableQuestionnaires(AvailabilityContext $context): array
    {
        $ids = $this->getAvailableIds($context);
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT
                    qr.id,
                    qr.uuid,
                    qr.name,
                    qr.version,
                    qr.profile,
                    qr.type,
                    qr.status,
                    qr.code,
                    qr.code_display,
                    qr.category,
                    qr.questionnaire,
                    lo.title AS category_title
                FROM `questionnaire_repository` qr
                LEFT JOIN `list_options` lo
                    ON lo.list_id = 'Observation_Types'
                    AND lo.option_id = qr.category
                    AND lo.activity = 1
                WHERE qr.id IN ($placeholders)
                  AND qr.active = 1
                ORDER BY COALESCE(lo.seq, 999999), COALESCE(lo.title, qr.category), qr.name";

        $rows = [];
        foreach (QueryUtils::fetchRecordsNoLog($sql, $ids) as $row) {
            // DB rows come back with mixed key types; normalise to string keys so callers
            // get a declared shape rather than a bare array
            $typed = [];
            foreach ($row as $column => $value) {
                $typed[(string)$column] = $value;
            }
            $rows[] = $typed;
        }

        return $rows;
    }

    /**
     * Whether one questionnaire is offered on a surface.
     */
    public function isAvailable(int $questionnaireId, AvailabilityContext $context): bool
    {
        return in_array($questionnaireId, $this->getAvailableIds($context), true);
    }

    /**
     * Drop questionnaire rows from an encounter form list that this encounter may not use.
     *
     * Rows for every other form type pass through untouched. The registry is shared by
     * all of OpenEMR's encounter forms and this service speaks only for questionnaires,
     * so anything it does not recognise is none of its business.
     *
     * The availability lookup is deferred until a questionnaire row actually turns up,
     * so an installation with no questionnaires registered pays no query for this.
     *
     * @param iterable<mixed> $rows registry rows as getFormsByCategory() returns them
     * @return list<array<string, mixed>>
     */
    public function filterEncounterFormRows(iterable $rows, AvailabilityContext $context): array
    {
        /** @var array<int, int>|null $available */
        $available = null;
        $filtered = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $typed = [];
            foreach ($row as $column => $value) {
                $typed[(string)$column] = $value;
            }

            if (($typed['directory'] ?? null) !== self::ENCOUNTER_FORM_DIRECTORY) {
                $filtered[] = $typed;
                continue;
            }

            $available ??= array_flip($this->getAvailableIds($context));
            $foreignId = self::asInt($typed['form_foreign_id'] ?? null);
            if ($foreignId > 0 && isset($available[$foreignId])) {
                $filtered[] = $typed;
            }
        }

        return $filtered;
    }

    /**
     * Create the assignment a freshly imported questionnaire needs to be visible.
     *
     * Import calls this so a new questionnaire behaves the way it does today — it shows
     * up on the dashboard without an administrator having to assign it first. An explicit
     * unassign later removes the row, and no rows then correctly means not visible.
     */
    public function assignDefault(int $questionnaireId, QuestionnaireSurface $surface): void
    {
        $existing = QueryUtils::fetchSingleValue(
            'SELECT `id` FROM `' . self::TABLE_NAME . '`
              WHERE `resource_type` = ? AND `resource_id` = ? AND `surface` = ?
              LIMIT 1',
            'id',
            [self::RESOURCE_TYPE, $questionnaireId, $surface->value]
        );
        if (self::asInt($existing) > 0) {
            return;
        }

        QueryUtils::sqlInsert(
            'INSERT INTO `' . self::TABLE_NAME . '` SET
                `resource_type` = ?, `resource_id` = ?, `surface` = ?, `active` = 1',
            [self::RESOURCE_TYPE, $questionnaireId, $surface->value]
        );
    }

    /**
     * @return list<AssignmentRow>
     */
    private function fetchAssignments(AvailabilityContext $context): array
    {
        [$where, $binds] = $this->buildScopeClause($context);

        // The join carries two rules. An assignment never outlives its questionnaire being
        // deactivated, and an assignment targets EITHER one questionnaire by id OR every
        // questionnaire in a category. resource_id is therefore read from the repository
        // side, since a category-wide row has none of its own.
        $sql = 'SELECT ca.`id`, qr.`id` AS `resource_id`, ca.`surface`, ca.`acl_section`,
                       ca.`acl_level`, ca.`seq`
                FROM `' . self::TABLE_NAME . '` ca
                INNER JOIN `questionnaire_repository` qr
                    ON qr.`active` = 1
                   AND (
                        ca.`resource_id` = qr.`id`
                     OR (ca.`resource_id` IS NULL
                         AND ca.`category` IS NOT NULL
                         AND ca.`category` <> \'\'
                         AND ca.`category` = qr.`category`)
                   )
                WHERE ca.`resource_type` = ?
                  AND ca.`surface` = ?
                  AND ca.`active` = 1
                  ' . $where . '
                ORDER BY ca.`seq`, ca.`id`';

        $rows = QueryUtils::fetchRecordsNoLog(
            $sql,
            array_merge([self::RESOURCE_TYPE, $context->surface->value], $binds)
        );

        $assignments = [];
        foreach ($rows as $row) {
            $resourceId = self::asInt($row['resource_id'] ?? null);
            if ($resourceId <= 0) {
                continue;
            }
            $assignments[] = [
                'id' => self::asInt($row['id'] ?? null),
                'resource_id' => $resourceId,
                'surface' => self::asString($row['surface'] ?? null) ?? '',
                'acl_section' => self::asString($row['acl_section'] ?? null),
                'acl_level' => self::asString($row['acl_level'] ?? null),
                'seq' => self::asInt($row['seq'] ?? null),
            ];
        }

        return $assignments;
    }

    /**
     * Narrow a database value to an int without casting mixed.
     *
     * A column that is absent, null, or not integer-shaped yields 0, which every caller
     * here already treats as "no usable value".
     */
    private static function asInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int)$value;
        }

        return 0;
    }

    /**
     * Narrow a database value to a non-empty string, or null.
     *
     * Null and empty string mean the same thing for the nullable assignment columns —
     * unscoped on that axis — so they collapse to null here.
     */
    private static function asString(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Narrowing clause for every scope the caller supplied.
     *
     * A null column on the assignment means unscoped on that axis and always matches. A
     * null value in the context means the caller cannot answer for that axis, so the axis
     * is not narrowed at all rather than being treated as "unscoped only" — a dashboard
     * that does not know the visit category must not start hiding visit-scoped rows.
     *
     * @return array{string, list<scalar>}
     */
    private function buildScopeClause(AvailabilityContext $context): array
    {
        $clauses = [];
        $binds = [];

        /** @var array<string, int|string|null> $scopes */
        $scopes = [
            'facility' => $context->facility,
            'provider' => $context->provider,
            'visit_category_id' => $context->visitCategoryId,
            'client_id' => $context->clientId,
        ];

        foreach ($scopes as $column => $value) {
            // 0 means unknown, not a real scope. Every id this narrows by is an
            // auto-increment starting at 1, and callers coerce with is_numeric() ? (int) :
            // null, so a missing or malformed value arrives here as 0. Narrowing on it
            // would match only assignments scoped to a category or facility that cannot
            // exist, silently hiding every questionnaire.
            if ($value === null || $value === '' || $value === 0) {
                continue;
            }
            $clauses[] = 'AND (ca.`' . $column . '` IS NULL OR ca.`' . $column . '` = ?)';
            $binds[] = $value;
        }

        return [implode(' ', $clauses), $binds];
    }

    /**
     * @param AssignmentRow $assignment
     */
    private function isPermitted(array $assignment, QuestionnaireSurface $surface): bool
    {
        [$defaultSection, $defaultLevel] = $surface->defaultAcl();
        $section = $assignment['acl_section'] ?? $defaultSection;
        $level = $assignment['acl_level'] ?? $defaultLevel;

        return AclMain::aclCheckCore($section, $level);
    }
}
