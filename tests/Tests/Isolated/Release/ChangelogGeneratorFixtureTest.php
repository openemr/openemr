<?php

/**
 * Fixture-based regression: replay 8.2.0's real inputs
 * (v8_1_0...v8_2_0 range, 653 commits, 647 PRs) through the
 * current ChangelogGenerator and assert the output matches the
 * locked expected section.
 *
 * Two scenarios are exercised via twin fixture subdirs:
 *   * release-time/    -- simulates release-prep merge time, when the
 *                         release's own GHSAs are still in draft and no
 *                         published advisory patches this release.
 *                         Rendered output has no Security section.
 *   * post-ghsa/       -- simulates the post-GHSA-publish amendment
 *                         dispatch, when the release's GHSAs have been
 *                         published (each with `Patched versions` set
 *                         to the target release string -- see
 *                         RELEASE_PROCESS.md). Rendered output has a
 *                         Security Fixes section listing every matching
 *                         advisory.
 * Both share commits.json + prs.json in the parent dir; only
 * advisories.json + expected.md differ.
 *
 * The fixtures are captured via
 * `tools/release/bin/capture-changelog-fixture.php` from live gh
 * api state. Advisories are filtered at capture time to only entries
 * ChangelogGenerator would render at replay (patched_versions exact
 * match on --target-version, or SHA/PR reference match). That keeps
 * the fixture stable as unrelated GHSAs get published upstream. When
 * the generator's filter, categorization, section ordering, or
 * advisory rendering legitimately shifts, rerun the capture script
 * AND regenerate expected.md by running this test with
 * `UPDATE_FIXTURE=1` -- it writes the current output to expected.md
 * instead of asserting. Review the diff, commit fixtures +
 * expected.md together, done.
 *
 * Why this test complements ChangelogGeneratorTest:
 *   ChangelogGeneratorTest exercises each filter/categorization
 *   branch discretely with synthetic PR shapes -- fast, targeted,
 *   good at localizing a regression to a specific method.
 *   This test replays a realistic ~650-PR range end-to-end,
 *   catching interactions between filter + categorization + area
 *   grouping + advisory matching that synthetic cases miss.
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
use PHPUnit\Framework\TestCase;

final class ChangelogGeneratorFixtureTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/fixtures/8_2_0';

    public function testRegeneratesEightPointTwoZeroAtReleaseTime(): void
    {
        $this->assertScenarioRendersExpected('release-time');
    }

    public function testRegeneratesEightPointTwoZeroPostGhsaAmendment(): void
    {
        $this->assertScenarioRendersExpected('post-ghsa');
    }

    private function assertScenarioRendersExpected(string $scenario): void
    {
        $commits = self::loadJson('commits.json');
        $prs = self::loadJson('prs.json');
        $advisories = self::loadJson($scenario . '/advisories.json');

        /** @var list<string> $commits */
        /** @var list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}> $prs */
        /** @var list<array<string, mixed>> $advisories */
        $api = new FakeGitHubApi(shas: $commits, prs: $prs, advisories: $advisories);
        // v8_2_0 was tagged on 2026-07-08; freeze the clock there so
        // the rendered heading date matches the CHANGELOG entry that
        // shipped for 8.2.0.
        $clock = new FrozenClock(new DateTimeImmutable('2026-07-08T00:00:00+0000', new DateTimeZone('UTC')));
        $generator = new ChangelogGenerator($api, 'openemr/openemr', $clock);

        $actual = $generator->generate('v8_1_0', 'v8_2_0', '8.2.0', includeGhsa: true);

        $expectedPath = self::FIXTURE_DIR . '/' . $scenario . '/expected.md';

        // UPDATE_FIXTURE=1 mode: overwrite expected.md instead of
        // asserting. Use this after intentional changes to the
        // generator (filter, categorization, section ordering); review
        // the resulting expected.md diff before committing.
        if (getenv('UPDATE_FIXTURE') === '1') {
            file_put_contents($expectedPath, $actual);
            self::markTestSkipped(
                'UPDATE_FIXTURE=1 mode: rewrote ' . $scenario . '/expected.md;'
                . ' rerun without the env var to assert',
            );
        }

        $expected = (string) file_get_contents($expectedPath);
        self::assertSame(
            $expected,
            $actual,
            'ChangelogGenerator output for 8.2.0 (' . $scenario . ') drifted from expected.md.'
            . ' If the change is intentional, rerun with UPDATE_FIXTURE=1'
            . ' and commit both the code change and expected.md together.',
        );
    }

    /**
     * @return array<mixed>
     */
    private static function loadJson(string $filename): array
    {
        $path = self::FIXTURE_DIR . '/' . $filename;
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException('Cannot read fixture: ' . $path);
        }
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Fixture is not a JSON array: ' . $path);
        }
        return $decoded;
    }
}
