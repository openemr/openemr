<?php

/**
 * Isolated tests for OpenEMR\Release\ChangelogGenerator.
 *
 * Focus areas: the ported noise filter (isNoise/isDockerBump/
 * isNoOpVersionBump/scopeOf), section ordering, area sub-grouping,
 * developer-changes bucket, and the compareLinkOverride behaviour that
 * ChangelogMutator uses at release-prep time (aspirational vNEW URL
 * while the git-range still resolves against the rel branch).
 *
 * A fixture-based end-to-end regression lives alongside in
 * ChangelogGeneratorFixtureTest — this file covers filter branches
 * discretely so a regression on any one is localized to a single test.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Release\ChangelogGenerator;
use OpenEMR\Release\GitHubApi;
use PHPUnit\Framework\TestCase;

final class ChangelogGeneratorTest extends TestCase
{
    /**
     * @param list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}> $prs
     */
    private function generate(
        array $prs,
        ?string $title = '8.2.1',
        string $base = 'v8_2_0',
        string $head = 'rel-820',
        ?string $compareLinkOverride = null,
    ): string {
        $shas = array_map(static fn (array $pr): string => str_pad((string) $pr['number'], 40, '0'), $prs);
        $api = new FakeGitHubApi(shas: $shas, prs: $prs, advisories: []);
        return (new ChangelogGenerator($api))->generate(
            $base,
            $head,
            $title,
            includeGhsa: false,
            compareLinkOverride: $compareLinkOverride,
        );
    }

    /**
     * @param list<string> $labels
     * @return array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}
     */
    private function pr(int $number, string $title, array $labels = [], string $author = 'someone'): array
    {
        return [
            'number' => $number,
            'title' => $title,
            'labels' => array_map(static fn (string $l): array => ['name' => $l], $labels),
            'url' => sprintf('https://github.com/openemr/openemr/pull/%d', $number),
            'author' => $author,
        ];
    }

    public function testReleaseBotAuthoredPrsAreDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'chore: bump version', author: 'openemr-release-bot[bot]'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
    }

    public function testTestBracketTitleIsDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, '[TEST] scratch PR to trigger CI'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
    }

    public function testBackportInTitleIsDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'fix(backport): patch to rel-800'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
    }

    public function testMachineryScopedCommitsAreDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'chore(release): bump for 8.2.1-dev'),
            $this->pr(3, 'ci(release-prep): tweak orchestrator token permissions'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
        self::assertStringNotContainsString('#3', $out);
    }

    public function testChoreReleaseAtStartOfTitleIsDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'chore: release 8.2.1'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
    }

    public function testDependabotNoOpVersionBumpIsDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'chore(deps): bump vendor/foo from 1.2.3 to 1.2.3', author: 'dependabot[bot]'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
    }

    public function testDependabotDockerBumpByPathIsDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'chore(deps): bump openemr/openemr from flex-3.17 to flex-3.18 in /docker/development-insane', author: 'dependabot[bot]'),
            $this->pr(3, 'chore(deps): bump mariadb from 10.6.0 to 10.6.1 in /ci/php82_mariadb1011', author: 'dependabot[bot]'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
        self::assertStringNotContainsString('#3', $out);
    }

    public function testDependabotDockerBumpByGroupIsDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'bump the openemr-images group across 1 directory with 2 updates', author: 'dependabot[bot]'),
            $this->pr(3, 'bump the mariadb group across 3 directories', author: 'dependabot[bot]'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
        self::assertStringNotContainsString('#3', $out);
    }

    public function testDependabotComposerBumpIsKept(): void
    {
        // `chore(deps)` parses as chore + scope=deps → Changed category
        // (the mapping keys on the top-level type, so grouped-scope
        // dependabot PRs land in Changed alongside other maintenance).
        // Explicit `deps: …` typed PRs land in Dependencies; see
        // testDepsTypedPrLandsInDependenciesSection.
        $out = $this->generate([
            $this->pr(1, 'chore(deps): bump symfony/console from 6.4.0 to 6.4.1', author: 'dependabot[bot]'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringContainsString('### Changed', $out);
    }

    public function testDepsTypedPrLandsInDependenciesSection(): void
    {
        $out = $this->generate([
            $this->pr(1, 'deps: bump symfony/console from 6.4.0 to 6.5.0'),
        ]);
        self::assertStringContainsString('### Dependencies', $out);
        self::assertStringContainsString('#1', $out);
    }

    public function testSectionOrderIsFixedAddedChangedDependencies(): void
    {
        $out = $this->generate([
            $this->pr(1, 'deps: bump symfony/console from 6.4.0 to 6.5.0'),
            $this->pr(2, 'chore: refactor auth flow'),
            $this->pr(3, 'feat: add PATCH endpoint'),
            $this->pr(4, 'fix: date parsing bug'),
        ]);

        $fixedPos = strpos($out, '### Fixed');
        $addedPos = strpos($out, '### Added');
        $changedPos = strpos($out, '### Changed');
        $depsPos = strpos($out, '### Dependencies');

        self::assertIsInt($fixedPos);
        self::assertIsInt($addedPos);
        self::assertIsInt($changedPos);
        self::assertIsInt($depsPos);
        self::assertLessThan($addedPos, $fixedPos);
        self::assertLessThan($changedPos, $addedPos);
        self::assertLessThan($depsPos, $changedPos);
    }

    public function testDevelopersLabeledPrsSeparateIntoDeveloperChangesBucket(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: user-facing'),
            $this->pr(2, 'feat: internal refactor', labels: ['developers']),
        ]);

        self::assertStringContainsString('### OpenEMR Developer Changes', $out);
        $devPos = strpos($out, '### OpenEMR Developer Changes');
        $userPos = strpos($out, '#1');
        self::assertIsInt($devPos);
        self::assertIsInt($userPos);
        self::assertLessThan($devPos, $userPos, 'user-facing section renders before developer bucket');
    }

    public function testAreaSubHeadingRendersForLabelsBeyondSkipList(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: new billing rule', labels: ['Billing']),
        ]);
        self::assertMatchesRegularExpression('/#### Billing/', $out);
    }

    public function testCompareLinkOverrideRendersVnewUrlEvenWhenHeadIsRelBranch(): void
    {
        $out = $this->generate(
            [$this->pr(1, 'feat: aspirational-tag URL check')],
            title: '8.2.1',
            base: 'v8_2_0',
            head: 'rel-820',
            compareLinkOverride: 'v8_2_1',
        );

        self::assertStringContainsString(
            'https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1',
            $out,
        );
        self::assertStringNotContainsString('compare/v8_2_0...rel-820', $out);
    }

    public function testCompareLinkFallsBackToHeadWhenOverrideOmitted(): void
    {
        $out = $this->generate(
            [$this->pr(1, 'feat: preserves prior behaviour')],
            title: '8.2.1',
            base: 'v8_2_0',
            head: 'v8_2_1',
        );

        self::assertStringContainsString(
            'https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1',
            $out,
        );
    }

    public function testNoTitleRendersBodyWithoutHeading(): void
    {
        $out = $this->generate([$this->pr(1, 'feat: something')], title: null);
        // Section sub-headings (`### Added`) are fine; the top-level
        // `## [<version>]…` release heading is what must be absent.
        self::assertStringNotContainsString('## [', $out);
        self::assertStringContainsString('#1', $out);
    }

    public function testBracketsInPrTitlesEscapedToPreventLinkInjection(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: shiny thing [click here](https://evil.example.com)'),
        ]);
        self::assertStringContainsString('\\[click here\\](https://evil.example.com)', $out);
        self::assertStringNotContainsString('[click here](https://evil.example.com)', $out);
    }

    public function testBracketsInAreaLabelsEscaped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: something', labels: ['[EVIL](https://x)']),
        ]);
        self::assertStringContainsString('\\[EVIL\\](https://x)', $out);
        self::assertStringNotContainsString('#### [EVIL](https://x)', $out);
    }

    public function testPrUrlOutsideOpenemrOrgReplacedWithSafePlaceholder(): void
    {
        $prs = [[
            'number' => 1,
            'title' => 'feat: hijacked url',
            'labels' => [],
            'url' => 'https://attacker.example.com/pull/1',
            'author' => 'someone',
        ]];
        $shas = ['0000000000000000000000000000000000000001'];
        $api = new FakeGitHubApi(shas: $shas, prs: $prs, advisories: []);
        $out = (new ChangelogGenerator($api))->generate('v8_2_0', 'rel-820', '8.2.1', includeGhsa: false);

        self::assertStringNotContainsString('attacker.example.com', $out);
        self::assertStringContainsString('https://github.com/openemr/openemr', $out);
    }

    public function testAdvisorySummaryAndUrlSanitized(): void
    {
        $prs = [$this->pr(1, 'feat: baseline PR')];
        $shas = ['0000000000000000000000000000000000000001'];
        $advisories = [[
            'ghsa_id' => 'GHSA-abcd-abcd-abcd',
            'severity' => 'high',
            'summary' => 'nasty [phish](https://evil.example.com)',
            'html_url' => 'https://attacker.example.com/advisory/1',
            'references' => [['url' => 'https://github.com/openemr/openemr/pull/1']],
        ]];
        $api = new FakeGitHubApi(shas: $shas, prs: $prs, advisories: $advisories);
        $out = (new ChangelogGenerator($api))->generate('v8_2_0', 'rel-820', '8.2.1', includeGhsa: true);

        self::assertStringContainsString('\\[phish\\](https://evil.example.com)', $out);
        self::assertStringNotContainsString('] nasty [phish](https://evil.example.com)', $out);
        self::assertStringNotContainsString('attacker.example.com', $out);
    }

    public function testHeadingDateComesFromInjectedClockForRerunIdempotence(): void
    {
        $prs = [$this->pr(1, 'feat: something')];
        $shas = ['0000000000000000000000000000000000000001'];
        $api = new FakeGitHubApi(shas: $shas, prs: $prs, advisories: []);
        $frozen = new FrozenClock(new DateTimeImmutable('2026-01-15T00:00:00Z', new DateTimeZone('UTC')));

        $out = (new ChangelogGenerator($api, 'openemr/openemr', $frozen))->generate(
            'v8_2_0',
            'rel-820',
            '8.2.1',
            includeGhsa: false,
        );

        self::assertStringContainsString('## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...rel-820) - 2026-01-15', $out);
    }
}

/**
 * Test-only override of GitHubApi that returns injected data instead of
 * shelling out to `gh`. Kept alongside the tests since it has no other
 * consumers.
 */
final class FakeGitHubApi extends GitHubApi
{
    /**
     * @param list<string> $shas
     * @param list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}> $prs
     * @param list<array<string, mixed>> $advisories
     */
    public function __construct(
        private readonly array $shas,
        private readonly array $prs,
        private readonly array $advisories,
    ) {
        parent::__construct('openemr/openemr');
    }

    /**
     * @return list<string>
     */
    public function commitsBetweenRefs(string $base, string $head): array
    {
        return $this->shas;
    }

    /**
     * @param list<string> $shas
     * @return list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}>
     */
    public function prsForCommits(array $shas): array
    {
        return $this->prs;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function publishedAdvisories(): array
    {
        return $this->advisories;
    }
}
