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
        $this->assertStringContainsString('`facility` IS NULL', $clause);
        $this->assertStringContainsString('`facility` = ?', $clause);
        $this->assertSame([3], $binds);
    }

    public function testEachSuppliedScopeAddsExactlyOneBind(): void
    {
        [$clause, $binds] = $this->scopeClause(new AvailabilityContext(
            QuestionnaireSurface::Encounter,
            visitCategory: 'office_visit',
            facility: 3,
            provider: 7,
        ));

        $this->assertSame(3, substr_count($clause, '?'));
        $this->assertSame([3, 7, 'office_visit'], $binds);
    }

    /**
     * An unknown scope must not be treated as "unscoped assignments only" — a caller that
     * cannot answer for an axis should see every row, not just the unscoped ones.
     */
    #[DataProvider('emptyScopeProvider')]
    public function testUnknownScopesAreNotNarrowed(?string $visitCategory): void
    {
        [$clause, $binds] = $this->scopeClause(
            new AvailabilityContext(QuestionnaireSurface::Encounter, visitCategory: $visitCategory)
        );

        $this->assertSame('', $clause);
        $this->assertSame([], $binds);
    }

    /**
     * @return array<string, array{?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function emptyScopeProvider(): array
    {
        return [
            'null visit category' => [null],
            'empty visit category' => [''],
        ];
    }

    public function testWithVisitCategoryLeavesTheOriginalUntouched(): void
    {
        $base = new AvailabilityContext(QuestionnaireSurface::Encounter, pid: 11, encounter: 22);
        $narrowed = $base->withVisitCategory('telehealth');

        $this->assertNull($base->visitCategory);
        $this->assertSame('telehealth', $narrowed->visitCategory);
        $this->assertSame(11, $narrowed->pid);
        $this->assertSame(22, $narrowed->encounter);
        $this->assertSame(QuestionnaireSurface::Encounter, $narrowed->surface);
    }
}
