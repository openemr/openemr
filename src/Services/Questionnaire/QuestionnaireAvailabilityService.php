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

use InvalidArgumentException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Session\SessionWrapperFactory;

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
     * Codes on the InvalidArgumentException a refused write throws. The message is for
     * logs; a screen maps the code to translated text instead of showing the message.
     */
    public const ERROR_NOT_SAVED = 1;
    public const ERROR_NO_TARGET = 2;
    public const ERROR_HALF_ACL = 3;
    public const ERROR_UNSUPPORTED_SCOPE = 4;
    public const ERROR_MISSING = 5;
    public const ERROR_DUPLICATE = 6;


    /**
     * Questionnaire ids available on this surface, in assignment order.
     *
     * ACL is evaluated per assignment, so two questionnaires on the same surface can
     * require different permissions. An assignment with no `acl_section` falls back to
     * the surface default, which is the check that surface already performed.
     *
     * Each distinct section/level pair is checked once per call. Most rows share the
     * surface default, and every aclCheckCore() outside admin/super is a gacl query, so
     * checking per row would repeat the same query once per questionnaire.
     *
     * @return list<int>
     * @throws SqlQueryException if the assignment lookup cannot run
     */
    public function getAvailableIds(AvailabilityContext $context): array
    {
        $ids = [];
        $aclDecisions = [];
        foreach ($this->fetchAssignments($context) as $assignment) {
            if (!$this->isPermitted($assignment, $context->surface, $aclDecisions)) {
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
        /** @var list<array<string, mixed>> $all */
        $all = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $typed = [];
            foreach ($row as $column => $value) {
                $typed[(string)$column] = $value;
            }
            $all[] = $typed;
        }

        /** @var array<int, int>|null $available */
        $available = null;
        $filtered = [];

        foreach ($all as $typed) {
            if (($typed['directory'] ?? null) !== self::ENCOUNTER_FORM_DIRECTORY) {
                $filtered[] = $typed;
                continue;
            }

            // A questionnaire registry row with no form_foreign_id does not stand for any
            // one questionnaire — it is the generic "New Questionnaire" launcher that every
            // install seeds. There is no assignment to look up for it, so it passes
            // through; filtering it would remove the only route to creating a questionnaire
            // encounter form.
            $foreignId = self::asInt($typed['form_foreign_id'] ?? null);
            if ($foreignId <= 0) {
                $filtered[] = $typed;
                continue;
            }

            try {
                $available ??= array_flip($this->getAvailableIds($context));
            } catch (SqlQueryException $e) {
                // This runs while the encounter page is already rendering, so a failed
                // query must not take out the whole encounter. But the questionnaire rows
                // fail closed: with the rules unreadable there is no telling which ones a
                // restriction or a switched-off rule withdrew, and every other surface
                // already refuses when the lookup fails. Other forms and the generic
                // launcher stay, so a questionnaire can still be started from the
                // encounter. Logged at error level, not swallowed.
                //
                // Narrowed to SqlQueryException on purpose. ForbiddenCatchTypeRule bars
                // suppressing Error and ErrorException, and rightly so -- a TypeError or
                // a PHP warning promoted by ErrorHandler is a bug to surface, not a
                // degraded menu. A query that cannot run is the one failure the caller
                // can genuinely recover from.
                ServiceContainer::getLogger()->error(
                    'Questionnaire availability lookup failed; questionnaire forms withheld from the encounter form menu',
                    ['surface' => $context->surface->value, 'exception' => $e]
                );

                return self::withoutAssignedQuestionnaireRows($all);
            }

            if (isset($available[$foreignId])) {
                $filtered[] = $typed;
            }
        }

        return $filtered;
    }

    /**
     * Every row except the ones that stand for a particular questionnaire: other forms,
     * and the generic launcher, whose form_foreign_id is empty.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private static function withoutAssignedQuestionnaireRows(array $rows): array
    {
        $kept = [];
        foreach ($rows as $row) {
            if (
                ($row['directory'] ?? null) !== self::ENCOUNTER_FORM_DIRECTORY
                || self::asInt($row['form_foreign_id'] ?? null) <= 0
            ) {
                $kept[] = $row;
            }
        }

        return $kept;
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
     * Every assignment row, newest surface order first, for the management screen.
     *
     * Unlike getAvailableIds() this applies no ACL and no scope narrowing: the screen
     * edits what is stored, including rows that are inactive or scoped somewhere the
     * administrator is not. Ordered so a surface's rows stay together.
     *
     * @return list<AssignmentRecord>
     * @throws SqlQueryException if the lookup cannot run
     */
    public function listAssignments(?QuestionnaireSurface $surface = null): array
    {
        $binds = [self::RESOURCE_TYPE];
        $where = '';
        if ($surface !== null) {
            $where = ' AND ca.`surface` = ?';
            $binds[] = $surface->value;
        }

        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT ca.`id`, ca.`surface`, ca.`resource_id`, ca.`category`, ca.`facility`,
                    ca.`provider`, ca.`visit_category_id`, ca.`client_id`, ca.`acl_section`,
                    ca.`acl_level`, ca.`active`, ca.`seq`, qr.`name` AS `resource_name`
               FROM `' . self::TABLE_NAME . '` ca
               LEFT JOIN `questionnaire_repository` qr ON qr.`id` = ca.`resource_id`
              WHERE ca.`resource_type` = ?' . $where . '
              ORDER BY ca.`surface`, ca.`seq`, COALESCE(qr.`name`, ca.`category`), ca.`id`',
            $binds
        );

        $records = [];
        foreach ($rows as $row) {
            $surfaceValue = self::asString($row['surface'] ?? null);
            $resolved = $surfaceValue !== null ? QuestionnaireSurface::tryFrom($surfaceValue) : null;
            if ($resolved === null) {
                // A surface this build does not know about. Skipped rather than guessed
                // at, so an older or newer row cannot be silently re-pointed by an edit.
                continue;
            }
            $resourceId = self::asInt($row['resource_id'] ?? null);
            $facility = self::asInt($row['facility'] ?? null);
            $provider = self::asInt($row['provider'] ?? null);
            $visitCategory = self::asInt($row['visit_category_id'] ?? null);

            $records[] = new AssignmentRecord(
                id: self::asInt($row['id'] ?? null),
                surface: $resolved,
                resourceId: $resourceId > 0 ? $resourceId : null,
                category: self::asString($row['category'] ?? null),
                facility: $facility > 0 ? $facility : null,
                provider: $provider > 0 ? $provider : null,
                visitCategoryId: $visitCategory > 0 ? $visitCategory : null,
                clientId: self::asString($row['client_id'] ?? null),
                aclSection: self::asString($row['acl_section'] ?? null),
                aclLevel: self::asString($row['acl_level'] ?? null),
                active: self::asInt($row['active'] ?? null) === 1,
                seq: self::asInt($row['seq'] ?? null),
                resourceName: self::asString($row['resource_name'] ?? null),
            );
        }

        return $records;
    }

    /**
     * Active questionnaires, for the rows of the management grid.
     *
     * Deliberately not QuestionnaireService::getQuestionnaireList(): that one can exclude
     * questionnaires already registered as encounter forms, which is right for the import
     * picker and wrong here -- the screen must show every questionnaire an administrator
     * could assign anywhere.
     *
     * @return list<array{id: int, name: string, category: string}>
     * @throws SqlQueryException if the lookup cannot run
     */
    public function listManageableQuestionnaires(): array
    {
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT `id`, `name`, `category` FROM `questionnaire_repository`
              WHERE `active` = 1
              ORDER BY COALESCE(`category`, \'\'), `name`',
            []
        );

        $list = [];
        foreach ($rows as $row) {
            $id = self::asInt($row['id'] ?? null);
            if ($id <= 0) {
                continue;
            }
            $list[] = [
                'id' => $id,
                'name' => self::asString($row['name'] ?? null) ?? '',
                'category' => self::asString($row['category'] ?? null) ?? '',
            ];
        }

        return $list;
    }

    /**
     * Create or update an assignment and return its id.
     *
     * An existing row for the same target, surface and scope is updated rather than
     * duplicated, so saving the screen twice does not multiply rows. A row carried
     * `active = 0` from an earlier unassign is reactivated by this call -- that is an
     * administrator deciding again, which is exactly what the screen is for, and is why
     * assignDefault() is the only path that must not reactivate.
     *
     * @throws InvalidArgumentException if the row names no target, carries half an ACL
     *         pair, or is scoped on an axis its surface does not fill in
     * @throws SqlQueryException if the write cannot run
     */
    public function assign(AssignmentRecord $record): int
    {
        $this->assertWritable($record);

        $existing = $this->findMatchingAssignmentId($record);
        $userId = self::asInt(
            SessionWrapperFactory::getInstance()->getActiveSession()->get('authUserID')
        );
        $actor = $userId > 0 ? $userId : null;

        if ($existing > 0) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `' . self::TABLE_NAME . '` SET
                    `acl_section` = ?, `acl_level` = ?, `active` = ?, `seq` = ?, `updated_by` = ?
                  WHERE `id` = ?',
                [
                    $record->aclSection,
                    $record->aclLevel,
                    $record->active ? 1 : 0,
                    $record->seq,
                    $actor,
                    $existing,
                ]
            );

            return $existing;
        }

        $id = QueryUtils::sqlInsert(
            'INSERT INTO `' . self::TABLE_NAME . '` SET
                `resource_type` = ?, `resource_id` = ?, `surface` = ?, `category` = ?,
                `facility` = ?, `provider` = ?, `visit_category_id` = ?, `client_id` = ?,
                `acl_section` = ?, `acl_level` = ?, `active` = ?, `seq` = ?,
                `created_by` = ?, `updated_by` = ?',
            [
                self::RESOURCE_TYPE,
                $record->resourceId,
                $record->surface->value,
                $record->isCategoryWide() ? $record->category : null,
                $record->facility,
                $record->provider,
                $record->visitCategoryId,
                ($record->clientId ?? '') !== '' ? $record->clientId : null,
                $record->aclSection,
                $record->aclLevel,
                $record->active ? 1 : 0,
                $record->seq,
                $actor,
                $actor,
            ]
        );

        return self::asInt($id);
    }

    /**
     * Rewrite an existing rule in place -- target, scope, ACL and order.
     *
     * Separate from assign() because assign() finds its row by target and scope, and
     * editing a rule's scope changes exactly that: an edit has to address the row by id.
     * Editing in place rather than delete-and-recreate keeps the row's id, created_by
     * and created_at, so the rule's history survives the change.
     *
     * `active` is left as stored. Switching a rule on or off is its own action on the
     * screen, and an edit form that quietly re-enabled a rule someone had switched off
     * would undo that decision as a side effect.
     *
     * @throws InvalidArgumentException if the rule names no target, carries half an ACL
     *         pair, is scoped on an axis its surface does not fill in, does not exist,
     *         or would become a duplicate of another rule
     * @throws SqlQueryException if the write cannot run
     */
    public function updateAssignment(AssignmentRecord $record): void
    {
        if ($record->id <= 0) {
            throw new InvalidArgumentException(
                'a rule must be saved before it can be edited',
                self::ERROR_NOT_SAVED
            );
        }
        $this->assertWritable($record);

        $exists = QueryUtils::fetchSingleValue(
            'SELECT `id` FROM `' . self::TABLE_NAME . '` WHERE `id` = ? AND `resource_type` = ?',
            'id',
            [$record->id, self::RESOURCE_TYPE]
        );
        if (self::asInt($exists) !== $record->id) {
            throw new InvalidArgumentException('that rule no longer exists', self::ERROR_MISSING);
        }

        // An edit may not turn one rule into a copy of another. Two rows with the same
        // target and scope would make assign() ambiguous and show the administrator two
        // rules that cannot be told apart.
        $clash = $this->findMatchingAssignmentId($record);
        if ($clash > 0 && $clash !== $record->id) {
            throw new InvalidArgumentException(
                'another rule already covers that questionnaire or category with the same scope',
                self::ERROR_DUPLICATE
            );
        }

        $userId = self::asInt(
            SessionWrapperFactory::getInstance()->getActiveSession()->get('authUserID')
        );

        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE_NAME . '` SET
                `resource_id` = ?, `surface` = ?, `category` = ?, `facility` = ?,
                `provider` = ?, `visit_category_id` = ?, `client_id` = ?,
                `acl_section` = ?, `acl_level` = ?, `seq` = ?, `updated_by` = ?
              WHERE `id` = ? AND `resource_type` = ?',
            [
                $record->resourceId,
                $record->surface->value,
                $record->isCategoryWide() ? $record->category : null,
                $record->facility,
                $record->provider,
                $record->visitCategoryId,
                ($record->clientId ?? '') !== '' ? $record->clientId : null,
                $record->aclSection,
                $record->aclLevel,
                $record->seq,
                $userId > 0 ? $userId : null,
                $record->id,
                self::RESOURCE_TYPE,
            ]
        );
    }

    /**
     * Withdraw an assignment without removing the row.
     *
     * The row records that an administrator decided about this questionnaire on this
     * surface; `active` is the decision. Keeping it is what stops a later re-import
     * resurrecting a withdrawal, and it leaves `updated_by` as a trace of who did it.
     *
     * @throws SqlQueryException if the write cannot run
     */
    public function unassign(int $assignmentId): void
    {
        $this->setAssignmentActive($assignmentId, false);
    }

    /**
     * @throws SqlQueryException if the write cannot run
     */
    public function setAssignmentActive(int $assignmentId, bool $active): void
    {
        if ($assignmentId <= 0) {
            return;
        }
        $userId = self::asInt(
            SessionWrapperFactory::getInstance()->getActiveSession()->get('authUserID')
        );
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE_NAME . '` SET `active` = ?, `updated_by` = ?
              WHERE `id` = ? AND `resource_type` = ?',
            [$active ? 1 : 0, $userId > 0 ? $userId : null, $assignmentId, self::RESOURCE_TYPE]
        );
    }

    /**
     * Display order within a surface. Lower sorts first; equal seq falls back to id.
     *
     * @throws SqlQueryException if the write cannot run
     */
    public function setSeq(int $assignmentId, int $seq): void
    {
        if ($assignmentId <= 0) {
            return;
        }
        $userId = self::asInt(
            SessionWrapperFactory::getInstance()->getActiveSession()->get('authUserID')
        );
        QueryUtils::sqlStatementThrowException(
            'UPDATE `' . self::TABLE_NAME . '` SET `seq` = ?, `updated_by` = ?
              WHERE `id` = ? AND `resource_type` = ?',
            [$seq, $userId > 0 ? $userId : null, $assignmentId, self::RESOURCE_TYPE]
        );
    }

    /**
     * Remove a row outright.
     *
     * Reserved for rows an administrator explicitly discards -- a category rule that was
     * a mistake, or a scoped row that is no longer wanted. Withdrawing a questionnaire
     * from a surface is unassign(), not this, because a deleted row leaves no record that
     * the decision was ever made and a later import would re-add it.
     *
     * @throws SqlQueryException if the delete cannot run
     */
    public function deleteAssignment(int $assignmentId): void
    {
        if ($assignmentId <= 0) {
            return;
        }
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . self::TABLE_NAME . '` WHERE `id` = ? AND `resource_type` = ?',
            [$assignmentId, self::RESOURCE_TYPE]
        );
    }

    /**
     * Checks that apply to any rule before it is written, new or edited.
     *
     * Each is refused here so the screen cannot store a rule that would silently do
     * something other than what it says:
     *
     *  - no target matches nothing;
     *  - half an ACL pair is denied for everyone on the read path (see isPermitted());
     *  - a scope the surface never fills in is never narrowed, so the rule would apply
     *    everywhere on that surface (see QuestionnaireSurface::supportedScopes()).
     *
     * @throws InvalidArgumentException naming the first problem found
     */
    private function assertWritable(AssignmentRecord $record): void
    {
        if (!$record->hasTarget()) {
            throw new InvalidArgumentException(
                'an assignment must name a questionnaire or a category',
                self::ERROR_NO_TARGET
            );
        }
        if (($record->aclSection === null) !== ($record->aclLevel === null)) {
            // OpenEMR ACL sections and levels are only meaningful as pairs.
            throw new InvalidArgumentException(
                'set both acl_section and acl_level, or neither',
                self::ERROR_HALF_ACL
            );
        }
        $unsupported = array_diff(
            $record->scopedColumns(),
            $record->surface->supportedScopes()
        );
        if ($unsupported !== []) {
            // Shown to the administrator, so the axes are named in words, not columns.
            $axes = array_map(
                static fn(string $column): string => match ($column) {
                    'visit_category_id' => 'visit category',
                    'client_id' => 'SMART client',
                    default => $column,
                },
                $unsupported
            );
            throw new InvalidArgumentException(sprintf(
                'the %s surface cannot narrow by %s, so that rule would apply everywhere on it',
                $record->surface->value,
                implode(', ', $axes)
            ), self::ERROR_UNSUPPORTED_SCOPE);
        }
    }

    /**
     * Id of a row with the same target, surface and scope, or 0.
     *
     * Scope is part of the identity on purpose: "this questionnaire on the encounter at
     * facility 3" and "...at any facility" are different rules and must be able to
     * coexist.
     *
     * @throws SqlQueryException if the lookup cannot run
     */
    private function findMatchingAssignmentId(AssignmentRecord $record): int
    {
        $found = QueryUtils::fetchSingleValue(
            'SELECT `id` FROM `' . self::TABLE_NAME . '`
              WHERE `resource_type` = ?
                AND `surface` = ?
                AND `resource_id` <=> ?
                AND `category` <=> ?
                AND `facility` <=> ?
                AND `provider` <=> ?
                AND `visit_category_id` <=> ?
                AND `client_id` <=> ?
              LIMIT 1',
            'id',
            [
                self::RESOURCE_TYPE,
                $record->surface->value,
                $record->resourceId,
                $record->isCategoryWide() ? $record->category : null,
                $record->facility,
                $record->provider,
                $record->visitCategoryId,
                ($record->clientId ?? '') !== '' ? $record->clientId : null,
            ]
        );

        return self::asInt($found);
    }

    /**
     * @return list<AssignmentRow>
     * @throws SqlQueryException if the assignment lookup cannot run
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

        foreach ($context->scopeValues() as $column => $value) {
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
     * Whether the current user passes the assignment's ACL.
     *
     * An assignment overrides the surface default as a PAIR or not at all. OpenEMR's ACL
     * sections and levels are only meaningful together -- 'patients'/'med',
     * 'encounters'/'notes', 'admin'/'super' -- so mixing an assignment's section with the
     * surface default's level names a combination the installer never created, which
     * aclCheckCore denies for everyone. That failure is invisible: the questionnaire just
     * stops appearing, with nothing to say why.
     *
     * A half-set pair is therefore refused and logged rather than guessed at. Denying is
     * the right direction: whoever set one half meant to restrict access, so falling back
     * to the broader surface default would do the opposite of what was asked.
     *
     * @param AssignmentRow $assignment
     * @param array<string, bool> $aclDecisions "section|level" => decision, reused within one lookup
     * @param-out array<string, bool> $aclDecisions
     */
    private function isPermitted(array $assignment, QuestionnaireSurface $surface, array &$aclDecisions): bool
    {
        $section = $assignment['acl_section'];
        $level = $assignment['acl_level'];

        if ($section === null && $level === null) {
            [$section, $level] = $surface->defaultAcl();
        } elseif ($section === null || $level === null) {
            ServiceContainer::getLogger()->warning(
                'Questionnaire assignment names half an ACL pair and is being denied; '
                . 'set both acl_section and acl_level, or neither',
                [
                    'assignment_id' => $assignment['id'],
                    'resource_id' => $assignment['resource_id'],
                    'surface' => $surface->value,
                    'acl_section' => $section,
                    'acl_level' => $level,
                ]
            );

            return false;
        }

        return $aclDecisions[$section . '|' . $level] ??= AclMain::aclCheckCore($section, $level);
    }
}
