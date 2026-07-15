<?php

/**
 * Isolated tests for OpenEMR\Release\ChangelogGenerator.
 *
 * Focus areas: the ported noise filter (isNoise/isDockerBump/
 * isNoOpVersionBump), section ordering, area sub-grouping,
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

    public function testBackportInTitleIsKept(): void
    {
        // Backports on a rel branch ARE the PRs that shipped in that
        // release. The earlier filter (ported from website's stricter
        // rules) dropped them, but that swallowed user-visible fixes
        // like openemr/openemr#12827 / #12832 from the 8.2.0 CHANGELOG.
        // Now kept.
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'fix(sql-upgrade): audit-logging fix (rel-820 backport for 8.2.0)'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringContainsString('#2', $out);
    }

    public function testReleaseMachineryScopedCommitsAreKept(): void
    {
        // `fix(release):` / `ci(release-prep):` are internal-tooling PRs
        // but CHANGELOG readers (devs + release engineers) benefit from
        // seeing what changed in the release-mechanism during a given
        // release. Docker auto-bump noise, which is what the earlier
        // filter was actually intended to remove, is handled specifically
        // by the DEPENDABOT branch below (isDockerBump + isNoOpVersionBump).
        // A separately-covered `chore: release X.Y.Z` PR still gets
        // filtered by the chore-release-cut rule tested below.
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'fix(release): scope App token to dispatch target repos'),
            $this->pr(3, 'ci(release-prep): tweak orchestrator token permissions'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringContainsString('#2', $out);
        self::assertStringContainsString('#3', $out);
    }

    public function testChoreReleaseAtStartOfTitleIsDropped(): void
    {
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'chore: release 8.2.1'),
            $this->pr(3, 'chore(release): release v8_2_2'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringNotContainsString('#2', $out);
        self::assertStringNotContainsString('#3', $out);
    }

    public function testChoreReleaseWithoutVersionIsKept(): void
    {
        // The chore-release-cut filter requires a version number after
        // "release" (see the `\s+v?\d` in the regex). Titles that use
        // "release" as an English word rather than as the release-cut
        // marker -- like `chore(docs): release notes update` or
        // `chore(build): release artifacts to S3` -- must not be
        // false-matched.
        $out = $this->generate([
            $this->pr(1, 'feat: real change'),
            $this->pr(2, 'chore(docs): release notes update'),
            $this->pr(3, 'chore(build): release artifacts to S3'),
        ]);
        self::assertStringContainsString('#1', $out);
        self::assertStringContainsString('#2', $out);
        self::assertStringContainsString('#3', $out);
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
        // Target the PR bullet line specifically -- the release heading
        // trivially contains `https://github.com/openemr/openemr` (compare
        // URL), so a broader assertion would pass even if the PR-line
        // sanitizer regressed. Match on the `[#1](...)` shape which only
        // renders for the PR entry.
        self::assertStringContainsString('([#1](https://github.com/openemr/openemr)', $out);
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

    public function testAdvisoryMatchedByPatchedVersionsExactString(): void
    {
        $prs = [$this->pr(1, 'feat: baseline PR')];
        $shas = ['0000000000000000000000000000000000000001'];
        // No references linking commits/PRs; the only match signal is
        // the exact patched_versions string. This mirrors how openemr
        // GHSAs are published in practice (References field left empty,
        // Patched versions set to the exact release string).
        $advisories = [[
            'ghsa_id' => 'GHSA-vv5j-6gjw-ffx9',
            'severity' => 'high',
            'summary' => 'staging of decrypted patient documents in webroot',
            'html_url' => 'https://github.com/openemr/openemr/security/advisories/GHSA-vv5j-6gjw-ffx9',
            'references' => [],
            'vulnerabilities' => [['patched_versions' => '8.2.1']],
        ]];
        $api = new FakeGitHubApi(shas: $shas, prs: $prs, advisories: $advisories);
        $out = (new ChangelogGenerator($api))->generate('v8_2_0', 'rel-820', '8.2.1', includeGhsa: true);

        self::assertStringContainsString('### Security Fixes', $out);
        self::assertStringContainsString('GHSA-vv5j-6gjw-ffx9', $out);
    }

    public function testAdvisoryNotMatchedWhenPatchedVersionsMismatch(): void
    {
        $prs = [$this->pr(1, 'feat: baseline PR')];
        $shas = ['0000000000000000000000000000000000000001'];
        // Advisory patches a different release; must not appear in the
        // 8.2.1 CHANGELOG entry (matches are exact-string, not a range).
        $advisories = [[
            'ghsa_id' => 'GHSA-aaaa-bbbb-cccc',
            'severity' => 'high',
            'summary' => 'landed on a different release',
            'html_url' => 'https://github.com/openemr/openemr/security/advisories/GHSA-aaaa-bbbb-cccc',
            'references' => [],
            'vulnerabilities' => [['patched_versions' => '8.1.0']],
        ]];
        $api = new FakeGitHubApi(shas: $shas, prs: $prs, advisories: $advisories);
        $out = (new ChangelogGenerator($api))->generate('v8_2_0', 'rel-820', '8.2.1', includeGhsa: true);

        self::assertStringNotContainsString('### Security Fixes', $out);
        self::assertStringNotContainsString('GHSA-aaaa-bbbb-cccc', $out);
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
