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
}
