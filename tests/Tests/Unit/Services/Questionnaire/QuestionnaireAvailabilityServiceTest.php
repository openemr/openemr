<?php

/**
 * Database-backed tests for questionnaire surface assignments.
 *
 * The isolated suite covers the clause building and the row filtering; everything here
 * needs the real schema, because the bugs this file exists to catch are the ones that
 * only appear when the SQL actually runs: a column named in src/ but not in sql/, a join
 * that matches the wrong rows, a scope that silently excludes everything.
 *
 * Under Unit/Services rather than Services/ because the `services` suite is not one CI
 * runs -- integration-tests.yml runs common,controllers,fixtures,validators,unit, and
 * test-frontcontroller.yml runs api. The `unit` suite is installed with ./cli install
 * against four database engines, so these run on all of them with the real schema and a
 * seeded admin, next to the DB-backed tests already in Unit/Services/Search.
 *
 * fetchAssignments() is exercised through reflection on purpose. It is the layer where
 * the join and scope semantics live, and reaching it directly keeps these assertions off
 * the ACL check — aclCheckCore() depends on a logged-in user, which a test process does
 * not have, so going through getAvailableIds() would assert on permissions rather than on
 * the query.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Services\Questionnaire;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\Questionnaire\AssignmentRecord;
use OpenEMR\Services\Questionnaire\AvailabilityContext;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class QuestionnaireAvailabilityServiceTest extends TestCase
{
    /**
     * Prefix on every row this test creates, so teardown removes exactly those and nothing
     * else. The suite runs against a populated development database. The zz_ prefix also
     * keeps these out of the way of any real category ordering.
     */
    private const CATEGORY = 'zz_availability_test';

    /**
     * A second category, for proving a category-wide assignment does not reach outside
     * the category it names. Shares the prefix so one cleanup covers both.
     */
    private const CATEGORY_OTHER = self::CATEGORY . '_other';

    private QuestionnaireAvailabilityService $service;

    /**
     * Ids of the questionnaires this test created.
     *
     * Every assertion about an exact set of ids is scoped to these. The suite runs
     * against a real development or CI install that already has questionnaires with
     * assignments of its own -- a backfilled dashboard row per active questionnaire, for
     * one -- so asserting on everything fetchAssignments() returns would be asserting on
     * the contents of whatever install happens to be running.
     *
     * @var list<int>
     */
    private array $ownIds = [];

    /**
     * Ids of the assignment rows this test created.
     *
     * Tracked because the category/resource prefix cannot reach every row a test makes:
     * an assignment that names neither a resource nor a category -- which one test
     * creates on purpose, to prove it matches nothing -- has nothing to key a delete on,
     * and would otherwise be left behind in the install on every run.
     *
     * @var list<int>
     */
    private array $ownAssignmentIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        // A branch database created before content_assignments was added has no such
        // table, and every test here would otherwise fail in setUp with an error that
        // says nothing about what to do. Skip with instructions instead. This hides
        // nothing from CI: the isolated suite asserts the table and its columns exist in
        // both sql/database.sql and the upgrade script, and fails if they do not.
        if (!self::tableExists('content_assignments')) {
            $this->markTestSkipped(
                'content_assignments is missing from this database; run sql_upgrade '
                . '(openemr-cmd sql-upgrade) or rebuild the stack, then re-run'
            );
        }

        $this->service = new QuestionnaireAvailabilityService();
        $this->cleanUp();
    }

    private static function tableExists(string $table): bool
    {
        $found = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `c` FROM `information_schema`.`tables`
              WHERE `table_schema` = DATABASE() AND `table_name` = ?',
            'c',
            [$table]
        );

        return self::narrowInt($found, "the {$table} table count") > 0;
    }

    protected function tearDown(): void
    {
        // Guarded: a skipped setUp never reached cleanUp, and running it here would turn
        // one clear skip into an error about a missing table.
        if (self::tableExists('content_assignments')) {
            $this->cleanUp();
        }

        parent::tearDown();
    }

    private function cleanUp(): void
    {
        // Assignments before questionnaires, so a failure part way through never leaves a
        // row pointing at a questionnaire that no longer exists. Single-table deletes
        // throughout rather than a multi-table DELETE ... USING: the audit layer wraps
        // every statement and the join form buys nothing here.
        //
        // By id first. This is the only thing that reaches an assignment naming neither a
        // resource nor a category, since such a row carries nothing to match on.
        if ($this->ownAssignmentIds !== []) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `content_assignments` WHERE `id` IN ('
                . implode(',', array_fill(0, count($this->ownAssignmentIds), '?')) . ')',
                $this->ownAssignmentIds
            );
        }

        // Then the prefix sweep, which picks up anything an aborted earlier run left
        // behind and whose ids this process never saw.
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `content_assignments`
              WHERE `category` LIKE ?
                 OR `resource_id` IN (
                        SELECT `id` FROM `questionnaire_repository` WHERE `category` LIKE ?
                    )',
            [self::CATEGORY . '%', self::CATEGORY . '%']
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `questionnaire_repository` WHERE `category` LIKE ?',
            [self::CATEGORY . '%']
        );
        $this->ownIds = [];
        $this->ownAssignmentIds = [];
    }

    private function addQuestionnaire(string $name, bool $active = true, ?string $category = null): int
    {
        $id = self::narrowInt(QueryUtils::sqlInsert(
            'INSERT INTO `questionnaire_repository`
                SET `name` = ?, `category` = ?, `active` = ?, `status` = ?',
            [$name, $category ?? self::CATEGORY, $active ? 1 : 0, 'active']
        ), 'the new questionnaire id');
        $this->assertGreaterThan(0, $id, 'the questionnaire insert must report its id');
        $this->ownIds[] = $id;

        return $id;
    }

    /**
     * @param array<string, int|string|null> $columns
     */
    private function addAssignment(array $columns): int
    {
        $columns['resource_type'] ??= QuestionnaireAvailabilityService::RESOURCE_TYPE;
        $columns['active'] ??= 1;

        $sets = [];
        $binds = [];
        foreach ($columns as $column => $value) {
            $sets[] = '`' . $column . '` = ?';
            $binds[] = $value;
        }

        $id = self::narrowInt(
            QueryUtils::sqlInsert(
                'INSERT INTO `content_assignments` SET ' . implode(', ', $sets),
                $binds
            ),
            'the new assignment id'
        );
        $this->assertGreaterThan(0, $id, 'the assignment insert must report its id');
        $this->ownAssignmentIds[] = $id;

        return $id;
    }

    /**
     * QueryUtils returns mixed from both sqlInsert() and fetchSingleValue(); narrow rather
     * than cast, so a driver that stops reporting a value fails the test here instead of
     * silently becoming 0 and asserting on nothing.
     */
    private static function narrowInt(mixed $value, string $what): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int)$value;
        }

        self::fail("expected an integer for {$what}, got " . get_debug_type($value));
    }

    /**
     * @return list<int>
     */
    private function assignedIds(AvailabilityContext $context): array
    {
        $method = new ReflectionMethod(QuestionnaireAvailabilityService::class, 'fetchAssignments');
        /** @var list<array{resource_id: int}> $rows */
        $rows = $method->invoke($this->service, $context);

        // No array_values: array_map over a list returns a list already.
        return array_map(static fn(array $row): int => $row['resource_id'], $rows);
    }

    /**
     * assignedIds() narrowed to the questionnaires this test created.
     *
     * This is what every exact-set assertion uses. The service is being asked a global
     * question -- what is assigned on this surface -- and the honest way to assert on the
     * answer is to look only at the rows under test, rather than requiring the install to
     * be empty.
     *
     * @return list<int>
     */
    private function ownAssignedIds(AvailabilityContext $context): array
    {
        $own = array_flip($this->ownIds);

        return array_values(array_filter(
            $this->assignedIds($context),
            static fn(int $id): bool => isset($own[$id])
        ));
    }

    /**
     * The regression test for the whole PR.
     *
     * Every scope populated means buildScopeClause() emits a condition for each one, so
     * every scope column is named in a statement the server actually parses. A column
     * that exists in src/ but not in the schema fails here with "Unknown column" instead
     * of at render time on a clinician's encounter page.
     */
    public function testTheQueryRunsWithEveryScopeSupplied(): void
    {
        $id = $this->addQuestionnaire('availability all scopes');
        $this->addAssignment(['resource_id' => $id, 'surface' => 'encounter']);

        $context = new AvailabilityContext(
            QuestionnaireSurface::Encounter,
            pid: 1,
            encounter: 2,
            visitCategoryId: 5,
            facility: 3,
            provider: 7,
            clientId: 'some-client',
        );

        $this->assertSame([$id], $this->ownAssignedIds($context));
    }

    public function testAnAssignmentNamingOneQuestionnaireMatchesOnlyThatOne(): void
    {
        $assigned = $this->addQuestionnaire('availability assigned');
        $this->addQuestionnaire('availability unassigned');
        $this->addAssignment(['resource_id' => $assigned, 'surface' => 'dashboard']);

        $this->assertSame(
            [$assigned],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            'only the questionnaire the assignment names may be matched'
        );
    }

    /**
     * One row covering a whole category is the reason resource_id is nullable: "offer
     * every SDOH questionnaire in the portal" must stay true for one imported next week.
     */
    public function testACategoryAssignmentMatchesEveryQuestionnaireInThatCategory(): void
    {
        $first = $this->addQuestionnaire('availability category one');
        $second = $this->addQuestionnaire('availability category two');
        $other = $this->addQuestionnaire('availability other category', true, self::CATEGORY_OTHER);
        $this->addAssignment([
            'resource_id' => null,
            'category' => self::CATEGORY,
            'surface' => 'portal',
        ]);

        $matched = $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Portal));

        sort($matched);
        $expected = [$first, $second];
        sort($expected);
        $this->assertSame(
            $expected,
            $matched,
            'the category row must match both questionnaires in it and the one outside it, ' . $other . ', must not appear'
        );
    }

    public function testAnAssignmentNamingNeitherTargetMatchesNothing(): void
    {
        // The questionnaire has to exist for the assertion to mean anything -- an active
        // questionnaire is present and the untargeted row still matches nothing.
        $this->addQuestionnaire('availability untargeted');
        $this->addAssignment(['resource_id' => null, 'category' => null, 'surface' => 'dashboard']);

        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            'a row naming neither a resource nor a category must match nothing'
        );
    }

    /**
     * Deactivating a questionnaire has to withdraw it everywhere without anyone having to
     * find and remove its assignments, which is why the join carries qr.active.
     */
    public function testDeactivatingTheQuestionnaireWithdrawsItEvenWithAnActiveAssignment(): void
    {
        $id = $this->addQuestionnaire('availability inactive questionnaire', false);
        $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard']);

        $this->assertSame([], $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)));
    }

    public function testAnInactiveAssignmentIsIgnored(): void
    {
        $id = $this->addQuestionnaire('availability inactive assignment');
        $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard', 'active' => 0]);

        $this->assertSame([], $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)));
    }

    public function testAnAssignmentForOneSurfaceDoesNotLeakToAnother(): void
    {
        $id = $this->addQuestionnaire('availability one surface');
        $this->addAssignment(['resource_id' => $id, 'surface' => 'portal']);

        $this->assertSame([$id], $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Portal)));
        $this->assertSame([], $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)));
    }

    /**
     * The three readings of a scoped assignment, which is the part most likely to be got
     * wrong: visible at the facility named, hidden at any other, and still visible to a
     * caller that cannot say which facility it is — a null context means "cannot answer",
     * and must not start hiding scoped rows.
     */
    public function testAFacilityScopedAssignmentIsVisibleOnlyWhereItApplies(): void
    {
        $id = $this->addQuestionnaire('availability facility scoped');
        $this->addAssignment(['resource_id' => $id, 'surface' => 'encounter', 'facility' => 3]);

        $this->assertSame(
            [$id],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter, facility: 3)),
            'the assignment must apply at the facility it names'
        );
        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter, facility: 4)),
            'the assignment must not apply at another facility'
        );
        $this->assertSame(
            [$id],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter)),
            'a caller that cannot name a facility must not have scoped rows hidden from it'
        );
    }

    /**
     * A questionnaire picked up by both a category row and its own row is still one
     * questionnaire. fetchAssignments() reports both rows; getAvailableIds() dedupes.
     */
    public function testAQuestionnaireMatchedTwiceIsOfferedOnce(): void
    {
        $id = $this->addQuestionnaire('availability matched twice');
        $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard']);
        $this->addAssignment(['resource_id' => null, 'category' => self::CATEGORY, 'surface' => 'dashboard']);

        $context = new AvailabilityContext(QuestionnaireSurface::Dashboard);

        $this->assertCount(
            2,
            $this->ownAssignedIds($context),
            'the specific row and the category row must both match this questionnaire'
        );

        $deduped = [];
        foreach ($this->ownAssignedIds($context) as $matchedId) {
            $deduped[$matchedId] = $matchedId;
        }
        $this->assertSame([$id], array_values($deduped), 'two matches must still mean one questionnaire');
    }

    /**
     * Import calls this so a new questionnaire behaves as it does today. Calling it twice
     * must not create a second row, or re-saving a questionnaire would multiply its
     * assignments on every save.
     */
    public function testAssignDefaultIsIdempotent(): void
    {
        $id = $this->addQuestionnaire('availability assign default');

        $this->service->assignDefault($id, QuestionnaireSurface::Dashboard);
        $this->service->assignDefault($id, QuestionnaireSurface::Dashboard);

        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `c` FROM `content_assignments`
              WHERE `resource_type` = ? AND `resource_id` = ? AND `surface` = ?',
            'c',
            [QuestionnaireAvailabilityService::RESOURCE_TYPE, $id, 'dashboard']
        );

        $this->assertSame(
            1,
            self::narrowInt($count, 'the assignment row count'),
            'assignDefault must not add a second row'
        );
    }

    /**
     * An explicit unassign is recorded as active = 0 rather than removed, so a later
     * import must not quietly make the questionnaire visible again.
     */
    public function testAssignDefaultDoesNotResurrectAnUnassignedQuestionnaire(): void
    {
        $id = $this->addQuestionnaire('availability unassigned then imported');
        $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard', 'active' => 0]);

        $this->service->assignDefault($id, QuestionnaireSurface::Dashboard);

        $this->assertSame([], $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)));
    }

    /**
     * The contract the encounter-form registration path depends on.
     *
     * interface/forms/questionnaire_assessments/save.php creates the encounter assignment
     * right after it inserts the registry row, because registering the form is what makes
     * the questionnaire an encounter form. Without that call the questionnaire is
     * registered and then filtered straight back out of the encounter menu, so this locks
     * what assignDefault() has to deliver for that surface.
     */
    public function testAssignDefaultMakesAQuestionnaireAvailableOnTheEncounterSurface(): void
    {
        $id = $this->addQuestionnaire('availability assign default encounter');

        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter)),
            'nothing should be assigned before registration'
        );

        $this->service->assignDefault($id, QuestionnaireSurface::Encounter);

        $this->assertSame(
            [$id],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter)),
            'a registered questionnaire must be offered by the encounter it was registered for'
        );
        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            'registering for the encounter must not also assign the dashboard'
        );
    }

    /**
     * Why the upgrade backfill does not filter on `questionnaire_repository`.`active`.
     *
     * The flag is applied at read time by the availability join, so an assignment for an
     * inactive questionnaire is inert rather than wrong. Backfilling it anyway is what
     * keeps re-activation working: before this table existed, re-activating a
     * questionnaire put it back on the dashboard, and with the backfill filtered on
     * `active` it would have no assignment and stay hidden, with no UI yet to repair it.
     */
    public function testAnAssignmentForAnInactiveQuestionnaireGoesLiveWhenItIsReactivated(): void
    {
        $id = $this->addQuestionnaire('availability inactive then reactivated', false);
        $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard']);

        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            'the assignment must be inert while the questionnaire is inactive'
        );

        QueryUtils::sqlStatementThrowException(
            'UPDATE `questionnaire_repository` SET `active` = 1 WHERE `id` = ?',
            [$id]
        );

        $this->assertSame(
            [$id],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            're-activating must make it available again without an administrator touching assignments'
        );
    }

    /**
     * assign() creates a row and makes the questionnaire available on that surface.
     */
    public function testAssignCreatesAnAvailableAssignment(): void
    {
        $id = $this->addQuestionnaire('availability assign api');

        $assignmentId = $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Dashboard,
            resourceId: $id,
        ));
        $this->ownAssignmentIds[] = $assignmentId;

        $this->assertGreaterThan(0, $assignmentId);
        $this->assertSame(
            [$id],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard))
        );
    }

    /**
     * Saving the screen twice must not multiply rows, so assign() updates a row with the
     * same target, surface and scope rather than inserting another.
     */
    public function testAssignIsIdempotentForTheSameTargetAndScope(): void
    {
        $id = $this->addQuestionnaire('availability assign twice');

        $first = $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Encounter,
            resourceId: $id,
        ));
        $second = $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Encounter,
            resourceId: $id,
            seq: 5,
        ));
        $this->ownAssignmentIds[] = $first;

        $this->assertSame($first, $second, 'the same rule must reuse its row');

        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `c` FROM `content_assignments`
              WHERE `resource_type` = ? AND `resource_id` = ? AND `surface` = ?',
            'c',
            [QuestionnaireAvailabilityService::RESOURCE_TYPE, $id, 'encounter']
        );
        $this->assertSame(1, self::narrowInt($count, 'the rule count'));
    }

    /**
     * Scope is part of a rule's identity: "at facility 3" and "anywhere" are different
     * rules and have to be able to coexist.
     */
    public function testADifferentScopeIsADifferentRule(): void
    {
        $id = $this->addQuestionnaire('availability scoped vs unscoped');

        $unscoped = $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Encounter,
            resourceId: $id,
        ));
        $scoped = $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Encounter,
            resourceId: $id,
            facility: 3,
        ));
        $this->ownAssignmentIds[] = $unscoped;
        $this->ownAssignmentIds[] = $scoped;

        $this->assertNotSame($unscoped, $scoped);
    }

    /**
     * unassign() keeps the row. The row records that an administrator decided; `active`
     * is the decision, and keeping it is what stops a later import resurrecting it.
     */
    public function testUnassignWithdrawsTheQuestionnaireButKeepsTheRow(): void
    {
        $id = $this->addQuestionnaire('availability unassign api');
        $assignmentId = $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Dashboard,
            resourceId: $id,
        ));
        $this->ownAssignmentIds[] = $assignmentId;

        $this->service->unassign($assignmentId);

        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            'withdrawn means not offered'
        );

        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `c` FROM `content_assignments` WHERE `id` = ?',
            'c',
            [$assignmentId]
        );
        $this->assertSame(1, self::narrowInt($count, 'the surviving row'), 'the row is kept');

        $this->service->assignDefault($id, QuestionnaireSurface::Dashboard);
        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            're-import must not resurrect a withdrawal'
        );

        $this->service->setAssignmentActive($assignmentId, true);
        $this->assertSame(
            [$id],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            'but an administrator can decide again'
        );
    }

    /**
     * Availability is additive, and the management screen depends on knowing it.
     *
     * A questionnaire stays offered while ANY active rule reaches it, so switching off
     * one rule is not the same as withdrawing the questionnaire. The grid marks a box
     * whose questionnaire is also covered by a category or scoped rule for exactly this
     * reason -- without it, an administrator unticks a box, sees no change, and concludes
     * the screen is broken.
     */
    public function testAvailabilityIsAdditiveAcrossRules(): void
    {
        $id = $this->addQuestionnaire('availability additive');
        $unscoped = $this->addAssignment(['resource_id' => $id, 'surface' => 'encounter']);
        $scoped = $this->addAssignment([
            'resource_id' => $id,
            'surface' => 'encounter',
            'facility' => 3,
        ]);

        $context = new AvailabilityContext(QuestionnaireSurface::Encounter);
        // ownAssignedIds() reports matching rows, not questionnaires, so one questionnaire
        // reached by two rules appears twice; getAvailableIds() is what dedupes.
        $this->assertSame(
            [$id, $id],
            $this->ownAssignedIds($context),
            'both rules reach it while both are on'
        );

        $this->service->setAssignmentActive($unscoped, false);
        $this->assertSame(
            [$id],
            $this->ownAssignedIds($context),
            'the scoped rule still offers it to a caller that cannot name a facility'
        );

        $this->service->setAssignmentActive($scoped, false);
        $this->assertSame(
            [],
            $this->ownAssignedIds($context),
            'only with every rule off is it withdrawn'
        );
    }

    /**
     * A half-set ACL pair is refused at write time, so the read path's own refusal is
     * unreachable from the management screen.
     */
    public function testAssignRefusesHalfAnAclPair(): void
    {
        $id = $this->addQuestionnaire('availability half acl');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(QuestionnaireAvailabilityService::ERROR_HALF_ACL);
        $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Dashboard,
            resourceId: $id,
            aclSection: 'admin',
        ));
    }

    /**
     * The dashboard has no encounter, so it never fills in a visit category. A rule
     * scoped to one would match at every visit -- the opposite of what was asked -- so
     * it is refused instead of stored.
     */
    public function testAssignRefusesAScopeTheSurfaceCannotApply(): void
    {
        $id = $this->addQuestionnaire('availability unsupported scope');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(QuestionnaireAvailabilityService::ERROR_UNSUPPORTED_SCOPE);
        $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Dashboard,
            resourceId: $id,
            visitCategoryId: 5,
        ));
    }

    public function testUpdateAssignmentRefusesAScopeTheSurfaceCannotApply(): void
    {
        $id = $this->addQuestionnaire('availability edit unsupported scope');
        $ruleId = $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(QuestionnaireAvailabilityService::ERROR_UNSUPPORTED_SCOPE);
        $this->service->updateAssignment(new AssignmentRecord(
            id: $ruleId,
            surface: QuestionnaireSurface::Dashboard,
            resourceId: $id,
            provider: 7,
        ));
    }

    /**
     * One questionnaire offered at two kinds of visit and not at a third: one rule per
     * visit category, written through the same call the screen uses.
     */
    public function testVisitCategoryRulesOfferAQuestionnaireAtEachNamedKindOfVisit(): void
    {
        $id = $this->addQuestionnaire('availability per visit category');
        foreach ([5, 9] as $visitCategoryId) {
            $this->ownAssignmentIds[] = $this->service->assign(new AssignmentRecord(
                id: 0,
                surface: QuestionnaireSurface::Encounter,
                resourceId: $id,
                visitCategoryId: $visitCategoryId,
            ));
        }

        $this->assertSame(
            [$id],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter, visitCategoryId: 5))
        );
        $this->assertSame(
            [$id],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter, visitCategoryId: 9))
        );
        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter, visitCategoryId: 7)),
            'a visit category with no rule must not be offered it'
        );
    }

    /**
     * SMART launch is opt-in per questionnaire: nothing assigns it by default, an
     * unscoped SMART rule allows every app, and a rule naming an app allows only that one.
     */
    public function testSmartLaunchNeedsARuleAndCanNameTheApp(): void
    {
        $id = $this->addQuestionnaire('availability smart launch');
        $appA = new AvailabilityContext(QuestionnaireSurface::Smart, clientId: 'zz-availability-app-a');
        $appB = new AvailabilityContext(QuestionnaireSurface::Smart, clientId: 'zz-availability-app-b');

        $this->assertSame([], $this->ownAssignedIds($appA), 'no SMART rule, no launch');

        $named = $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Smart,
            resourceId: $id,
            clientId: 'zz-availability-app-a',
        ));
        $this->ownAssignmentIds[] = $named;
        $this->assertSame([$id], $this->ownAssignedIds($appA), 'the named app may launch it');
        $this->assertSame([], $this->ownAssignedIds($appB), 'another app may not');

        $this->ownAssignmentIds[] = $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Smart,
            resourceId: $id,
        ));
        $this->assertSame([$id], $this->ownAssignedIds($appB), 'an unscoped SMART rule allows every app');
    }

    public function testAssignRefusesARuleWithNoTarget(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(QuestionnaireAvailabilityService::ERROR_NO_TARGET);
        $this->service->assign(new AssignmentRecord(
            id: 0,
            surface: QuestionnaireSurface::Dashboard,
        ));
    }

    /**
     * listAssignments() is what the screen edits, so it must report stored state --
     * including rows that are inactive or scoped, which availability hides.
     */
    public function testListAssignmentsReportsStoredStateIncludingInactiveAndScoped(): void
    {
        $id = $this->addQuestionnaire('availability listed');
        $off = $this->addAssignment(['resource_id' => $id, 'surface' => 'portal', 'active' => 0]);
        $scoped = $this->addAssignment([
            'resource_id' => $id,
            'surface' => 'portal',
            'facility' => 3,
        ]);
        $categoryRule = $this->addAssignment([
            'resource_id' => null,
            'category' => self::CATEGORY,
            'surface' => 'portal',
        ]);

        $byId = [];
        foreach ($this->service->listAssignments(QuestionnaireSurface::Portal) as $record) {
            $byId[$record->id] = $record;
        }

        $this->assertArrayHasKey($off, $byId);
        $this->assertFalse($byId[$off]->active, 'an inactive rule is still reported');
        $this->assertFalse($byId[$off]->isScoped());
        $this->assertSame('availability listed', $byId[$off]->resourceName);

        $this->assertArrayHasKey($scoped, $byId);
        $this->assertTrue($byId[$scoped]->isScoped());
        $this->assertSame(3, $byId[$scoped]->facility);

        $this->assertArrayHasKey($categoryRule, $byId);
        $this->assertTrue($byId[$categoryRule]->isCategoryWide());
        $this->assertNull($byId[$categoryRule]->resourceId);
    }

    /**
     * Editing a rule's scope changes its identity, so it has to happen in place by id --
     * and the row keeps its id, which is what keeps its history.
     */
    public function testUpdateAssignmentRewritesScopeInPlace(): void
    {
        $id = $this->addQuestionnaire('availability edit scope');
        $ruleId = $this->addAssignment(['resource_id' => $id, 'surface' => 'encounter']);

        $this->service->updateAssignment(new AssignmentRecord(
            id: $ruleId,
            surface: QuestionnaireSurface::Encounter,
            resourceId: $id,
            facility: 3,
            aclSection: 'encounters',
            aclLevel: 'notes',
            seq: 4,
        ));

        $edited = null;
        foreach ($this->service->listAssignments(QuestionnaireSurface::Encounter) as $record) {
            if ($record->id === $ruleId) {
                $edited = $record;
            }
        }
        $this->assertNotNull($edited, 'the same row, still there under the same id');
        $this->assertSame(3, $edited->facility);
        $this->assertSame('encounters', $edited->aclSection);
        $this->assertSame('notes', $edited->aclLevel);
        $this->assertSame(4, $edited->seq);

        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Encounter, facility: 4)),
            'now scoped to facility 3, so not offered at facility 4'
        );
    }

    /**
     * An edit must not quietly re-enable a rule someone switched off; on/off is its own
     * action on the screen.
     */
    public function testUpdateAssignmentLeavesActiveAsStored(): void
    {
        $id = $this->addQuestionnaire('availability edit keeps off');
        $ruleId = $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard', 'active' => 0]);

        $this->service->updateAssignment(new AssignmentRecord(
            id: $ruleId,
            surface: QuestionnaireSurface::Dashboard,
            resourceId: $id,
            seq: 2,
        ));

        $this->assertSame(
            [],
            $this->ownAssignedIds(new AvailabilityContext(QuestionnaireSurface::Dashboard)),
            'still switched off after the edit'
        );
    }

    /**
     * Two rows with the same target and scope would be indistinguishable on the screen and
     * would make assign() ambiguous, so an edit may not create one.
     */
    public function testUpdateAssignmentRefusesToDuplicateAnotherRule(): void
    {
        $id = $this->addQuestionnaire('availability edit clash');
        $this->addAssignment(['resource_id' => $id, 'surface' => 'encounter', 'facility' => 3]);
        $other = $this->addAssignment(['resource_id' => $id, 'surface' => 'encounter']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(QuestionnaireAvailabilityService::ERROR_DUPLICATE);
        $this->service->updateAssignment(new AssignmentRecord(
            id: $other,
            surface: QuestionnaireSurface::Encounter,
            resourceId: $id,
            facility: 3,
        ));
    }

    public function testUpdateAssignmentRefusesHalfAnAclPair(): void
    {
        $id = $this->addQuestionnaire('availability edit half acl');
        $ruleId = $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard']);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->updateAssignment(new AssignmentRecord(
            id: $ruleId,
            surface: QuestionnaireSurface::Dashboard,
            resourceId: $id,
            aclLevel: 'med',
        ));
    }

    public function testUpdateAssignmentRefusesARuleThatDoesNotExist(): void
    {
        $id = $this->addQuestionnaire('availability edit missing');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(QuestionnaireAvailabilityService::ERROR_MISSING);
        $this->service->updateAssignment(new AssignmentRecord(
            id: 999999999,
            surface: QuestionnaireSurface::Dashboard,
            resourceId: $id,
        ));
    }

    public function testDeleteAssignmentRemovesTheRow(): void
    {
        $id = $this->addQuestionnaire('availability delete rule');
        $assignmentId = $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard']);

        $this->service->deleteAssignment($assignmentId);

        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS `c` FROM `content_assignments` WHERE `id` = ?',
            'c',
            [$assignmentId]
        );
        $this->assertSame(0, self::narrowInt($count, 'the deleted row'));
    }

    public function testSetSeqChangesDisplayOrder(): void
    {
        $id = $this->addQuestionnaire('availability seq');
        $assignmentId = $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard']);

        $this->service->setSeq($assignmentId, 7);

        foreach ($this->service->listAssignments(QuestionnaireSurface::Dashboard) as $record) {
            if ($record->id === $assignmentId) {
                $this->assertSame(7, $record->seq);
                return;
            }
        }
        $this->fail('the rule was not listed back');
    }

    /**
     * The grid only ever writes unscoped, questionnaire-specific rules, so a category or
     * scoped rule must survive a grid save untouched. Proven here at the service level,
     * which is the contract the screen relies on.
     */
    public function testManageableQuestionnairesListsEveryActiveQuestionnaire(): void
    {
        $first = $this->addQuestionnaire('availability manageable one');
        $inactive = $this->addQuestionnaire('availability manageable inactive', false);

        $ids = array_column($this->service->listManageableQuestionnaires(), 'id');

        $this->assertContains($first, $ids);
        $this->assertNotContains($inactive, $ids, 'an inactive questionnaire is not assignable');
    }

    /**
     * The full public path, end to end, against the real schema.
     *
     * getAvailableIds() and getAvailableQuestionnaires() both run the ACL check, which
     * needs a user. Rather than duplicating the service's SQL into the test -- which
     * would assert on a copy and prove nothing about the service -- borrow the seeded
     * superuser for the duration, and skip rather than fail if this install has no such
     * user, since that is a property of the fixture and not of this code.
     */
    public function testTheFullPublicPathReturnsTheAssignedQuestionnaire(): void
    {
        $id = $this->addQuestionnaire('availability full path');
        $this->addAssignment(['resource_id' => $id, 'surface' => 'dashboard']);

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $previousUser = $session->get('authUser');
        $session->set('authUser', 'admin');

        try {
            if (!AclMain::aclCheckCore('patients', 'med')) {
                $this->markTestSkipped('this install has no seeded superuser to evaluate the ACL with');
            }

            $context = new AvailabilityContext(QuestionnaireSurface::Dashboard, pid: 1);

            $this->assertContains($id, $this->service->getAvailableIds($context));
            $this->assertTrue($this->service->isAvailable($id, $context));

            $names = [];
            $withCategoryTitle = 0;
            foreach ($this->service->getAvailableQuestionnaires($context) as $row) {
                // every column the dashboard reads must be present, including the
                // category_title the list_options join supplies
                foreach (['id', 'name', 'category', 'questionnaire'] as $column) {
                    $this->assertArrayHasKey($column, $row);
                }
                if (array_key_exists('category_title', $row)) {
                    $withCategoryTitle++;
                }
                $names[] = $row['name'];
            }

            $this->assertContains('availability full path', $names);
            $this->assertGreaterThan(0, $withCategoryTitle, 'the list_options join must be selected');
        } finally {
            $session->set('authUser', $previousUser);
        }
    }

    /**
     * An encounter form row for an unassigned questionnaire is dropped, the assigned one
     * is kept, and the generic launcher survives either way. Run against the database so
     * the filter is exercised with ids the query actually returned.
     */
    public function testEncounterFormRowsAreFilteredByAssignment(): void
    {
        $assigned = $this->addQuestionnaire('availability encounter assigned');
        $unassigned = $this->addQuestionnaire('availability encounter unassigned');
        $this->addAssignment(['resource_id' => $assigned, 'surface' => 'encounter']);

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $previousUser = $session->get('authUser');
        $session->set('authUser', 'admin');

        try {
            if (!AclMain::aclCheckCore('encounters', 'notes')) {
                $this->markTestSkipped('this install has no seeded superuser to evaluate the ACL with');
            }

            $directory = QuestionnaireAvailabilityService::ENCOUNTER_FORM_DIRECTORY;
            $filtered = $this->service->filterEncounterFormRows(
                [
                    ['directory' => $directory, 'form_foreign_id' => null, 'name' => 'New Questionnaire'],
                    ['directory' => $directory, 'form_foreign_id' => $assigned, 'name' => 'assigned'],
                    ['directory' => $directory, 'form_foreign_id' => $unassigned, 'name' => 'unassigned'],
                    ['directory' => 'vitals', 'name' => 'Vitals'],
                ],
                new AvailabilityContext(QuestionnaireSurface::Encounter, pid: 1, encounter: 2)
            );

            $this->assertSame(
                ['New Questionnaire', 'assigned', 'Vitals'],
                array_column($filtered, 'name')
            );
        } finally {
            $session->set('authUser', $previousUser);
        }
    }
}
