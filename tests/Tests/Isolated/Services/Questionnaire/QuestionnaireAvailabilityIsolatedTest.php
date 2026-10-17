<?php

/**
 * Isolated tests for questionnaire availability scoping.
 *
 * Covers the pure surface: ACL defaults per surface, context immutability, and the scope
 * narrowing rules. Anything that reads content_assignments belongs in an integration test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Questionnaire;

use OpenEMR\Services\Questionnaire\AvailabilityContext;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class QuestionnaireAvailabilityIsolatedTest extends TestCase
{
    /**
     * @return array{string, list<scalar>}
     */
    private function scopeClause(AvailabilityContext $context): array
    {
        $method = new ReflectionMethod(QuestionnaireAvailabilityService::class, 'buildScopeClause');
        /** @var array{string, list<scalar>} $result */
        $result = $method->invoke(new QuestionnaireAvailabilityService(), $context);
        return $result;
    }

    public function testEverySurfaceDeclaresAnAclDefault(): void
    {
        foreach (QuestionnaireSurface::cases() as $surface) {
            [$section, $level] = $surface->defaultAcl();
            $this->assertNotSame('', $section, "{$surface->value} must name an ACL section");
            $this->assertNotSame('', $level, "{$surface->value} must name an ACL level");
        }
    }

    /**
     * These strings live in content_assignments.surface, so renaming one is a migration.
     *
     * Driven by a provider rather than comparing two literal arrays: the provider hands
     * the value over as a plain string, so the round trip is a real runtime check instead
     * of a comparison the analyser can fold away to true.
     */
    #[DataProvider('persistedSurfaceValueProvider')]
    public function testPersistedSurfaceValuesStillResolve(string $persisted, QuestionnaireSurface $expected): void
    {
        $this->assertSame($expected, QuestionnaireSurface::tryFrom($persisted));
    }

    /**
     * @return array<string, array{string, QuestionnaireSurface}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function persistedSurfaceValueProvider(): array
    {
        return [
            'dashboard' => ['dashboard', QuestionnaireSurface::Dashboard],
            'encounter' => ['encounter', QuestionnaireSurface::Encounter],
            'portal' => ['portal', QuestionnaireSurface::Portal],
            'smart' => ['smart', QuestionnaireSurface::Smart],
        ];
    }

    public function testAnUnscopedContextNarrowsNothing(): void
    {
        [$clause, $binds] = $this->scopeClause(new AvailabilityContext(QuestionnaireSurface::Dashboard));

        $this->assertSame('', $clause);
        $this->assertSame([], $binds);
    }

    public function testAKnownScopeMatchesAssignmentsThatAreNullOnThatAxis(): void
    {
        [$clause, $binds] = $this->scopeClause(
            new AvailabilityContext(QuestionnaireSurface::Encounter, facility: 3)
        );

        // NULL on the assignment means "any facility" and must still match
        $this->assertStringContainsString('ca.`facility` IS NULL', $clause);
        $this->assertStringContainsString('ca.`facility` = ?', $clause);
        $this->assertSame([3], $binds);
    }

    public function testEachSuppliedScopeAddsExactlyOneBind(): void
    {
        [$clause, $binds] = $this->scopeClause(new AvailabilityContext(
            QuestionnaireSurface::Encounter,
            visitCategoryId: 5,
            facility: 3,
            provider: 7,
        ));

        $this->assertSame(3, substr_count($clause, '?'));
        $this->assertSame([3, 7, 5], $binds);
    }

    /**
     * An unknown scope must not be treated as "unscoped assignments only" — a caller that
     * cannot answer for an axis should see every row, not just the unscoped ones.
     */
    #[DataProvider('emptyScopeProvider')]
    public function testUnknownScopesAreNotNarrowed(?int $visitCategoryId): void
    {
        [$clause, $binds] = $this->scopeClause(
            new AvailabilityContext(QuestionnaireSurface::Encounter, visitCategoryId: $visitCategoryId)
        );

        $this->assertSame('', $clause);
        $this->assertSame([], $binds);
    }

    /**
     * @return array<string, array{?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function emptyScopeProvider(): array
    {
        return [
            'null visit category' => [null],
            'zero visit category' => [0],
        ];
    }

    /**
     * The scope clause must qualify its columns, because the assignment query joins
     * questionnaire_repository and both tables carry a `category` column.
     */
    public function testScopeColumnsAreTableQualified(): void
    {
        [$clause] = $this->scopeClause(new AvailabilityContext(
            QuestionnaireSurface::Encounter,
            visitCategoryId: 5,
            facility: 3,
        ));

        $this->assertStringContainsString('ca.`facility`', $clause);
        $this->assertStringContainsString('ca.`visit_category_id`', $clause);
        $this->assertStringNotContainsString('(`facility`', $clause);
    }

    /**
     * Guards the bug class CodeRabbit found on the encounter surface: a scope was added to
     * the context and the schema, but nothing ever read it, so the restriction silently
     * did nothing.
     *
     * Every constructor scope except the three that are not SQL columns must turn into a
     * narrowing clause when supplied. If someone adds a scope to the context without
     * adding it to buildScopeClause(), this fails.
     */
    public function testEveryContextScopeReachesTheQuery(): void
    {
        $notScopeColumns = ['surface', 'pid', 'encounter'];
        $params = (new \ReflectionClass(AvailabilityContext::class))
            ->getConstructor()
            ?->getParameters() ?? [];

        $scopeNames = [];
        foreach ($params as $param) {
            if (!in_array($param->getName(), $notScopeColumns, true)) {
                $scopeNames[] = $param->getName();
            }
        }
        $this->assertNotEmpty($scopeNames, 'the context must declare at least one scope');

        $context = new AvailabilityContext(
            QuestionnaireSurface::Smart,
            visitCategoryId: 5,
            facility: 3,
            provider: 7,
            clientId: 'some-client',
        );
        [$clause, $binds] = $this->scopeClause($context);

        $this->assertCount(
            count($scopeNames),
            $binds,
            'every supplied scope must contribute exactly one bind: ' . implode(', ', $scopeNames)
        );
        $this->assertCount(
            count($scopeNames),
            $context->scopeValues(),
            'scopeValues() must name a column for every scope on the constructor: '
            . implode(', ', $scopeNames)
        );
        foreach (array_keys($context->scopeValues()) as $column) {
            $this->assertStringContainsString(
                'ca.`' . $column . '`',
                $clause,
                "scope column {$column} is mapped on the context but never narrows the query"
            );
        }
    }

    /**
     * The registry is shared by every encounter form in OpenEMR. Rows this service does
     * not speak for must survive untouched, and must not trigger an availability lookup
     * at all — an install with no questionnaires registered should pay nothing for this.
     */
    public function testNonQuestionnaireFormRowsPassThroughUnchanged(): void
    {
        $rows = [
            ['directory' => 'vitals', 'name' => 'Vitals', 'form_foreign_id' => null],
            ['directory' => 'newpatient', 'name' => 'New Encounter'],
            ['directory' => 'procedure_order', 'name' => 'Procedure Order'],
        ];

        $filtered = (new QuestionnaireAvailabilityService())->filterEncounterFormRows(
            $rows,
            new AvailabilityContext(QuestionnaireSurface::Encounter)
        );

        $this->assertCount(3, $filtered);
        $this->assertSame(
            ['vitals', 'newpatient', 'procedure_order'],
            array_column($filtered, 'directory')
        );
    }

    /**
     * Every install seeds a registry row for questionnaire_assessments with a NULL
     * form_foreign_id — the generic "New Questionnaire" launcher. It names no particular
     * questionnaire, so there is no assignment to check and it must survive the filter.
     * Dropping it removed the only route to creating a questionnaire encounter form.
     */
    public function testTheGenericQuestionnaireLauncherIsNotFiltered(): void
    {
        $rows = [
            ['directory' => 'vitals', 'name' => 'Vitals'],
            ['directory' => 'questionnaire_assessments', 'name' => 'New Questionnaire', 'form_foreign_id' => null],
            ['directory' => 'newpatient', 'name' => 'New Encounter'],
        ];

        $filtered = (new QuestionnaireAvailabilityService())->filterEncounterFormRows(
            $rows,
            new AvailabilityContext(QuestionnaireSurface::Encounter)
        );

        $this->assertCount(3, $filtered);
        $this->assertContains('New Questionnaire', array_column($filtered, 'name'));
    }

    public function testMalformedFormRowsAreDropped(): void
    {
        $filtered = (new QuestionnaireAvailabilityService())->filterEncounterFormRows(
            ['not an array', ['directory' => 'vitals']],
            new AvailabilityContext(QuestionnaireSurface::Encounter)
        );

        $this->assertCount(1, $filtered);
        $this->assertSame('vitals', $filtered[0]['directory']);
    }

    public function testWithVisitCategoryLeavesTheOriginalUntouched(): void
    {
        $base = new AvailabilityContext(QuestionnaireSurface::Encounter, pid: 11, encounter: 22);
        $narrowed = $base->withVisitCategoryId(7);

        $this->assertNull($base->visitCategoryId);
        $this->assertSame(7, $narrowed->visitCategoryId);
        $this->assertSame(11, $narrowed->pid);
        $this->assertSame(22, $narrowed->encounter);
        $this->assertSame(QuestionnaireSurface::Encounter, $narrowed->surface);
    }

    /**
     * Every assignment column the service names must exist in the shipped schema.
     *
     * This is the guard for the whole class of bug where a column is renamed in src/ and
     * not in sql/, or the reverse. It is invisible to PHPStan and to the project's SQL
     * rules: buildScopeClause() assembles the column name by concatenation, so no
     * analyser ever sees the literal, and on a live page the only symptom is an
     * "Unknown column" at render time.
     *
     * Both schema files are checked. database.sql is what a fresh install and the whole
     * E2E suite get; the upgrade script is what an existing site gets. The two drifting
     * apart is its own bug.
     *
     * @param non-empty-string $schemaFile
     */
    #[DataProvider('schemaFileProvider')]
    public function testEveryScopeColumnExistsInTheSchema(string $schemaFile): void
    {
        $columns = self::assignmentTableColumns($schemaFile);
        $context = new AvailabilityContext(QuestionnaireSurface::Encounter);

        foreach (array_keys($context->scopeValues()) as $column) {
            $this->assertContains(
                $column,
                $columns,
                "AvailabilityContext narrows by `{$column}`, which {$schemaFile} does not define"
            );
        }
    }

    /**
     * The same guard for every other column the service's SQL names directly.
     *
     * @param non-empty-string $schemaFile
     */
    #[DataProvider('schemaFileProvider')]
    public function testEveryQueriedAssignmentColumnExistsInTheSchema(string $schemaFile): void
    {
        $columns = self::assignmentTableColumns($schemaFile);

        $source = file_get_contents(
            dirname(__DIR__, 5) . '/src/Services/Questionnaire/QuestionnaireAvailabilityService.php'
        );
        $this->assertIsString($source, 'the service source must be readable');

        // Every `ca`-qualified column in the service's hand-written SQL. The alias is
        // only ever content_assignments, so anything qualified with it must be a column
        // on that table.
        preg_match_all('/\bca\.`([a-z_]+)`/', $source, $matches);
        $queried = array_values(array_unique($matches[1]));
        $this->assertNotEmpty($queried, 'the service must query content_assignments by name');

        foreach ($queried as $column) {
            $this->assertContains(
                $column,
                $columns,
                "the service selects ca.`{$column}`, which {$schemaFile} does not define"
            );
        }
    }

    /**
     * @return array<string, array{non-empty-string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function schemaFileProvider(): array
    {
        return [
            'fresh install' => ['sql/database.sql'],
            'upgrade' => ['sql/8_4_1-to-8_5_0_upgrade.sql'],
        ];
    }

    /**
     * Column names defined for content_assignments in one of the schema files.
     *
     * @return list<string>
     */
    private static function assignmentTableColumns(string $schemaFile): array
    {
        $path = dirname(__DIR__, 5) . '/' . $schemaFile;
        $sql = file_get_contents($path);
        if ($sql === false) {
            self::fail("cannot read {$schemaFile}");
        }

        if (
            preg_match(
                '/CREATE TABLE `content_assignments` \((.*?)\n\) ENGINE/s',
                $sql,
                $block
            ) !== 1
        ) {
            self::fail("{$schemaFile} does not create content_assignments");
        }

        // Leading backtick-quoted identifier on each line is the column; PRIMARY KEY and
        // KEY lines start with the keyword instead, so they drop out.
        preg_match_all('/^\s{2,}`([a-z_]+)`\s+\S/mi', $block[1], $matches);
        $columns = array_values(array_unique($matches[1]));
        self::assertNotEmpty($columns, "{$schemaFile} defines no columns for content_assignments");

        return $columns;
    }
}
