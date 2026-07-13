<?php

/**
 * Generate a changelog from the commit range between two git refs.
 *
 * Walk commits between base and head, resolve to merged PRs, parse
 * conventional commit prefixes from PR titles, and match published
 * GHSAs whose fix commits fall in the range.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Psr\Clock\ClockInterface;

/**
 * @phpstan-type CategorizedPr array{
 *     number: int, category: string, area: string,
 *     title: string, url: string, is_dev: bool,
 * }
 * @phpstan-type Advisory array{ghsa_id: string, severity: string, summary: string, url: string}
 */
class ChangelogGenerator
{
    private const CATEGORY_MAP = [
        'feat' => 'Added',
        'fix' => 'Fixed',
        'deps' => 'Dependencies',
    ];

    /** @var list<string> */
    private const SECTION_ORDER = ['Fixed', 'Added', 'Changed', 'Dependencies'];

    private const DEFAULT_CATEGORY = 'Changed';

    /** @var string */
    private const CC_PATTERN = '/^(feat|fix|deps|chore|refactor|docs|perf|test|ci|build|style)(\(.+?\))?!?:\s*(.+)$/i';

    /**
     * Labels that are process/meta labels rather than functional areas.
     * PRs with only these labels get no area sub-group.
     *
     * @var list<string>
     */
    private const SKIP_LABELS = [
        'backport',
        'bleeding',
        'BUMP-NEXT-PATCH',
        'can\'t fix it all in 1 PR',
        'Deprecated',
        'developers',
        'Double Check',
        'For Prior Version',
        'Fund',
        'good first issue',
        'help-wanted',
        'MERGE CONFLICT',
        'Mentored Item',
        'needs gifs',
        'parent issue',
        'patching',
        'Priority: Blocking',
        'Reported Forum Issue',
        'Resolved. Awaiting authors approval.',
        'RESPOND NOW OR SUFFER',
        'Sad State of Affairs',
        'SPECIAL INSTRUCTIONS',
        'Stale',
        'stalebot-ignore',
        'Status: Can\'t Reproduce',
        'Status: Needs Issue',
        'Status: Needs Release Merge',
        'Status: Needs Review',
        'Status: Needs Work',
        'Status: Pending Removal',
        'Status: Ready for Integration',
        'Status: Reviewed',
        'up for grabs demo',
        'WaitingForInfo',

        // Meta labels handled separately
        'AI Code Assistant',
        'Best PR Title of the Year Finalist',
        'dependencies',
        'github-actions',
        'Clinician Input Requested',
    ];

    /**
     * Bot login for the release automation account. Its PRs are pure
     * release machinery (version bumps, tag creation, sync PRs) and never
     * user-facing.
     */
    private const RELEASE_BOT = 'openemr-release-bot[bot]';

    /**
     * Dependabot login. Its docker-image bumps + same-version re-pins get
     * dropped as noise; composer/npm bumps pass through as Dependencies.
     */
    private const DEPENDABOT = 'dependabot[bot]';

    /**
     * Conventional-Commits scopes that mark a PR as release automation
     * rather than a user-facing change.
     *
     * @var list<string>
     */
    private const MACHINERY_SCOPES = ['release', 'release-prep'];

    /**
     * Dependabot docker-compose group names from openemr/openemr
     * `.github/dependabot.yml`. Grouped-update PR titles look like
     * `bump the <group> group across N directories with M updates`;
     * matching the group name identifies the bump as a docker-image
     * update rather than a composer/npm package update. The source-of-
     * truth file to check when this list feels stale is
     * `.github/dependabot.yml` — look for the `package-ecosystem:
     * docker-compose` blocks and their `groups:` maps.
     *
     * @var list<string>
     */
    private const DEPENDABOT_DOCKER_GROUPS = [
        'couchdb',
        'infrastructure',
        'mailpit',
        'mariadb',
        'mysql',
        'openemr-images',
        'phpmyadmin',
        'redis',
        'selenium',
        'selenium-updates',
    ];

    private readonly ClockInterface $clock;

    public function __construct(
        private readonly GitHubApi $api,
        private readonly string $repo = 'openemr/openemr',
        ?ClockInterface $clock = null,
    ) {
        // Construct SystemClock inline (with the system timezone) so
        // repeated calls to `generate()` on the same day yield an
        // identical heading date and the mutator's rerun-idempotence
        // guarantee holds. Tests inject a frozen clock for
        // deterministic output.
        $this->clock = $clock ?? new SystemClock(new DateTimeZone(date_default_timezone_get()));
    }

    /**
     * Generate a changelog from the commit range between two refs.
     *
     * @param string $base Base ref (tag)
     * @param string $head Head ref (tag or branch) — used for both the
     *                     compare API call AND the compare-link URL when
     *                     $compareLinkOverride is null.
     * @param ?string $title Version string for the heading (omit for body only)
     * @param bool $includeGhsa Include security advisories section
     * @param ?string $compareLinkOverride When non-null, overrides the head
     *                                     ref in the rendered compare-link
     *                                     URL only (not the git-range API
     *                                     call). Used by ChangelogMutator
     *                                     to render an aspirational
     *                                     `vPREV...vNEW` tag-to-tag URL at
     *                                     release-prep time when the target
     *                                     tag does not exist yet, while
     *                                     still enumerating commits from a
     *                                     ref that does exist (rel branch).
     */
    public function generate(
        string $base,
        string $head,
        ?string $title = null,
        bool $includeGhsa = true,
        ?string $compareLinkOverride = null,
    ): string {
        $shas = $this->api->commitsBetweenRefs($base, $head);
        $prs = self::filterNoise($this->api->prsForCommits($shas));

        $categorized = array_map($this->categorize(...), $prs);
        usort($categorized, static fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));

        $standard = array_values(array_filter($categorized, static fn(array $i): bool => !$i['is_dev']));
        $developer = array_values(array_filter($categorized, static fn(array $i): bool => $i['is_dev']));

        /** @var list<Advisory> $advisories */
        $advisories = [];
        if ($includeGhsa) {
            $prNumbers = array_map(static fn(array $pr): int => $pr['number'], $categorized);
            $advisories = $this->matchAdvisories($shas, $prNumbers);
        }

        $lines = [];
        if ($title !== null) {
            $encodedBase = rawurlencode($base);
            $encodedHead = rawurlencode($compareLinkOverride ?? $head);
            $compareUrl = "https://github.com/{$this->repo}/compare/{$encodedBase}...{$encodedHead}";
            $lines[] = "## [{$title}]({$compareUrl}) - " . $this->clock->now()->format('Y-m-d');
            $lines[] = '';
        }

        if (count($advisories) > 0) {
            $lines = array_merge($lines, $this->formatAdvisories($advisories));
        }

        $lines = array_merge($lines, $this->formatPrs($standard, 3));

        if (count($developer) > 0) {
            $devLines = $this->formatPrs($developer, 4);
            if (count($devLines) > 0) {
                $lines[] = '### OpenEMR Developer Changes';
                $lines[] = '';
                $lines = array_merge($lines, $devLines);
            }
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Drop PRs that are release automation, dependabot re-pins, or
     * docker-image bumps. Composer/npm dependabot bumps stay — those
     * are actual user-facing package changes. Ports website-openemr's
     * `ReleaseNotesGenerator::filterNoise()` behaviour so the two
     * surfaces (this codebase's CHANGELOG.md + GitHub Release body,
     * and the website's per-version release-notes page) apply the
     * same filter.
     *
     * @param list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}> $prs
     * @return list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}>
     */
    private static function filterNoise(array $prs): array
    {
        return array_values(array_filter($prs, static fn (array $pr): bool => !self::isNoise($pr)));
    }

    /**
     * @param array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string} $pr
     */
    private static function isNoise(array $pr): bool
    {
        $title = $pr['title'];
        if ($pr['author'] === self::RELEASE_BOT) {
            return true;
        }
        if (stripos($title, '[TEST]') !== false) {
            return true;
        }
        if (stripos($title, 'backport') !== false) {
            return true;
        }
        if (in_array(self::scopeOf($title), self::MACHINERY_SCOPES, true)) {
            return true;
        }
        if (preg_match('/^chore(?:\([^)]*\))?:\s*release\b/i', $title) === 1) {
            return true;
        }

        if ($pr['author'] === self::DEPENDABOT) {
            if (self::isNoOpVersionBump($title)) {
                return true;
            }
            if (self::isDockerBump($title)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True for dependabot titles that describe a docker-image or CI
     * infrastructure bump rather than a composer/npm package update.
     * Two signals:
     *
     * - Path signal: dependabot embeds the ecosystem's target directory
     *   in the title as `in /path/...`; docker-compose lives under
     *   `/docker/...` or `/ci/...` in openemr/openemr.
     * - Group signal: grouped bumps look like
     *   `bump the <group> group ...`; the docker-compose groups from
     *   `.github/dependabot.yml` are enumerated in
     *   DEPENDABOT_DOCKER_GROUPS.
     */
    private static function isDockerBump(string $title): bool
    {
        if (preg_match('#\bin /(?:docker|ci)/#', $title) === 1) {
            return true;
        }
        $groups = implode('|', array_map(
            static fn (string $g): string => preg_quote($g, '/'),
            self::DEPENDABOT_DOCKER_GROUPS,
        ));
        return preg_match("/\\bbump the ($groups) group\\b/", $title) === 1;
    }

    /**
     * Lowercased Conventional-Commits scope, or null when the title has
     * no parseable `type(scope):` prefix.
     */
    private static function scopeOf(string $title): ?string
    {
        if (preg_match('/^\w+\(([^)]*)\)!?:/', $title, $matches) !== 1) {
            return null;
        }

        return strtolower($matches[1]);
    }

    /**
     * True for a dependabot `bump <dep> from <v> to <v>` title where the
     * two versions are identical — a re-pin that changes nothing.
     */
    private static function isNoOpVersionBump(string $title): bool
    {
        if (preg_match('/\bfrom\s+(\S+)\s+to\s+(\S+)/', $title, $matches) !== 1) {
            return false;
        }

        return $matches[1] === $matches[2];
    }

    /**
     * @param array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string} $pr
     * @return CategorizedPr
     */
    private function categorize(array $pr): array
    {
        $title = $pr['title'];
        $category = self::DEFAULT_CATEGORY;

        if (preg_match(self::CC_PATTERN, $title, $matches) === 1) {
            $type = strtolower($matches[1]);
            $category = self::CATEGORY_MAP[$type] ?? self::DEFAULT_CATEGORY;
            $title = trim($matches[3]);
        }

        $isDev = false;
        $area = '';
        $skipSet = array_flip(self::SKIP_LABELS);

        foreach ($pr['labels'] as $label) {
            if ($label['name'] === 'developers') {
                $isDev = true;
                continue;
            }
            if ($area === '' && !isset($skipSet[$label['name']])) {
                $area = $label['name'];
            }
        }

        return [
            'number' => $pr['number'],
            'category' => $category,
            'area' => $area,
            'title' => $title,
            'url' => $pr['url'],
            'is_dev' => $isDev,
        ];
    }

    /**
     * Match published GHSAs whose fix commits or PRs overlap with this release.
     *
     * @param list<string> $shas Commit SHAs in the release range
     * @param list<int> $prNumbers PR numbers in the release
     * @return list<Advisory>
     */
    private function matchAdvisories(array $shas, array $prNumbers): array
    {
        $allAdvisories = $this->api->publishedAdvisories();
        $shaLookup = array_flip($shas);
        $prLookup = array_flip($prNumbers);

        $severityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];

        /** @var list<Advisory> $matched */
        $matched = [];

        foreach ($allAdvisories as $advisory) {
            if (!$this->advisoryMatchesRange($advisory, $shaLookup, $prLookup)) {
                continue;
            }

            $matched[] = [
                'ghsa_id' => is_string($advisory['ghsa_id'] ?? null) ? $advisory['ghsa_id'] : '',
                'severity' => is_string($advisory['severity'] ?? null) ? $advisory['severity'] : 'unknown',
                'summary' => is_string($advisory['summary'] ?? null) ? $advisory['summary'] : '',
                'url' => is_string($advisory['html_url'] ?? null) ? $advisory['html_url'] : '',
            ];
        }

        usort($matched, static function (array $a, array $b) use ($severityOrder): int {
            $aOrder = $severityOrder[$a['severity']] ?? 99;
            $bOrder = $severityOrder[$b['severity']] ?? 99;
            $cmp = $aOrder <=> $bOrder;
            return $cmp !== 0 ? $cmp : strcasecmp($a['summary'], $b['summary']);
        });

        return $matched;
    }

    /**
     * Check if an advisory's references overlap with the commit/PR set.
     *
     * @param array<string, mixed> $advisory
     * @param array<string, int> $shaLookup Flipped SHA array for O(1) lookup
     * @param array<int, int> $prLookup Flipped PR-number array for O(1) lookup
     */
    private function advisoryMatchesRange(array $advisory, array $shaLookup, array $prLookup): bool
    {
        /** @var list<array<string, mixed>> $references */
        $references = is_array($advisory['references'] ?? null) ? $advisory['references'] : [];

        foreach ($references as $ref) {
            $url = is_string($ref['url'] ?? null) ? $ref['url'] : '';

            // Match commit SHA references
            if (preg_match('#/commit/([0-9a-f]{40})#i', $url, $matches) === 1 && isset($shaLookup[$matches[1]])) {
                return true;
            }

            // Match PR number references
            if (preg_match('#/pull/(\d+)#', $url, $matches) === 1 && isset($prLookup[(int) $matches[1]])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format advisories as a Security Fixes section.
     *
     * @param list<Advisory> $advisories
     * @return list<string>
     */
    private function formatAdvisories(array $advisories): array
    {
        $lines = ['### Security Fixes', ''];

        foreach ($advisories as $advisory) {
            $severity = self::escapeMarkdown(ucfirst($advisory['severity']));
            $summary = self::escapeMarkdown($advisory['summary']);
            $ghsaId = self::escapeMarkdown($advisory['ghsa_id']);
            $url = self::sanitizeGitHubUrl($advisory['url']);
            $lines[] = "  - [{$severity}] {$summary} ([{$ghsaId}]({$url}))";
        }

        $lines[] = '';
        return $lines;
    }

    /**
     * Format PRs grouped by category and area label.
     *
     * @param list<CategorizedPr> $prs
     * @param int $depth Markdown heading depth for category headings (3 = ###)
     * @return list<string>
     */
    private function formatPrs(array $prs, int $depth = 3): array
    {
        $lines = [];

        foreach (self::SECTION_ORDER as $section) {
            $sectionLines = $this->formatByCategory($prs, $section, $depth);
            $lines = array_merge($lines, $sectionLines);
        }

        return $lines;
    }

    /**
     * Format PRs for a single category, sub-grouped by area label.
     *
     * @param list<CategorizedPr> $prs
     * @param int $depth Markdown heading depth for the category heading
     * @return list<string>
     */
    private function formatByCategory(array $prs, string $category, int $depth = 3): array
    {
        $matches = array_values(array_filter($prs, static fn(array $i): bool => $i['category'] === $category));
        if (count($matches) === 0) {
            return [];
        }

        // Group by area label
        /** @var array<string, list<CategorizedPr>> $byArea */
        $byArea = [];
        foreach ($matches as $pr) {
            $byArea[$pr['area']][] = $pr;
        }
        ksort($byArea);

        $heading = str_repeat('#', $depth);
        $lines = ["{$heading} {$category}", ''];

        // Unlabeled PRs first (empty area key)
        if (isset($byArea[''])) {
            foreach ($byArea[''] as $pr) {
                $lines[] = self::formatPrLine($pr);
            }
            $lines[] = '';
            unset($byArea['']);
        }

        // Area sub-groups
        $subHeading = str_repeat('#', $depth + 1);
        foreach ($byArea as $area => $areaPrs) {
            $lines[] = "{$subHeading} " . self::escapeMarkdown($area);
            $lines[] = '';
            foreach ($areaPrs as $pr) {
                $lines[] = self::formatPrLine($pr);
            }
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param CategorizedPr $pr
     */
    private static function formatPrLine(array $pr): string
    {
        $title = self::escapeMarkdown($pr['title']);
        $url = self::sanitizeGitHubUrl($pr['url']);
        return "  - {$title} ([#{$pr['number']}]({$url}))";
    }

    /**
     * Escape bracket characters so contributor-controlled strings (PR
     * titles, area labels, advisory summaries) cannot inject Markdown
     * link syntax into the rendered CHANGELOG. `[text](url)` is the
     * primary spoofing vector; escaping just `[` and `]` neutralizes it
     * while preserving legitimate uses of backticks (code identifiers),
     * asterisks, and other Markdown that survives in PR titles today.
     */
    private static function escapeMarkdown(string $value): string
    {
        return str_replace(['[', ']'], ['\\[', '\\]'], $value);
    }

    /**
     * Only emit URLs that point at the openemr GitHub org (PR + GHSA
     * origins). Anything else — an advisory referencing an external
     * mirror, a PR URL that has been rewritten upstream — becomes a
     * neutral placeholder, so the rendered CHANGELOG cannot smuggle an
     * off-origin link past readers who trust the surrounding context.
     */
    private static function sanitizeGitHubUrl(string $url): string
    {
        if (str_starts_with($url, 'https://github.com/openemr/')) {
            return $url;
        }
        return 'https://github.com/openemr/openemr';
    }
}
