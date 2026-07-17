<?php

/**
 * Translate openemr/openemr-devops#664 release-branch and tag names to
 * MAJOR.MINOR.PATCH versions, and derive a `prev_release` for the
 * dispatch envelope by reading the latest annotated v* tag from a
 * local git checkout.
 *
 * Centralised here so the conductor workflow doesn't have to duplicate
 * the regex parsing in shell.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// Non-final so ChangelogMutator's isolated tests can subclass with a
// stub `previousRelease()` — same pattern used by StubChangelogGenerator
// against ChangelogGenerator. Production code should always construct
// this class directly, not subclass it.
readonly class BranchVersionResolver
{
    /**
     * Canonical release manifest fetched to distinguish tags that actually
     * shipped from tags that were cut and then skipped. Any version entry
     * with `status: FINAL` in this file is a released version; a
     * v<MAJOR>_<MINOR>_<PATCH> tag whose version isn't in the manifest was
     * skipped (see: 8.1.0 was cut as v8_1_0 but never released) and must
     * not be reported as a "previous release" for any subsequent version.
     */
    public const DEFAULT_MANIFEST_URL =
        'https://raw.githubusercontent.com/openemr/website-openemr/master/data/releases.json';

    public function __construct(
        private string $repoDir,
        private ?HttpClientInterface $httpClient = null,
        private string $manifestUrl = self::DEFAULT_MANIFEST_URL,
    ) {
    }

    /**
     * Translate a rel-<MAJOR><MINOR>0 branch to the version it should
     * cut next. The trailing 0 is a structural marker — there is one
     * release branch per minor line, so rel-810 is the home for all of
     * 8.1.x — not the patch digit. The patch is derived from tags:
     * the highest existing v<MAJOR>_<MINOR>_* patch plus one, or 0 when
     * no release has been cut on that minor line yet.
     */
    public function branchToVersion(string $branch): string
    {
        if (preg_match('/^rel-(\d+)(\d)0$/', $branch, $m) !== 1) {
            throw new \InvalidArgumentException(
                'branch must match rel-<MAJOR><MINOR>0; got: ' . $branch,
            );
        }
        $major = (int) $m[1];
        $minor = (int) $m[2];
        $highestPatch = $this->highestPatchOnMinorLine($major, $minor);
        $nextPatch = $highestPatch === null ? 0 : $highestPatch + 1;
        return sprintf('%d.%d.%d', $major, $minor, $nextPatch);
    }

    /**
     * Returns the latest v<MAJOR>_<MINOR>_<PATCH> tag whose version is
     * strictly less than the target, or synthesises one based on the
     * target version when no qualifying tag exists yet (initial run on
     * a fresh repo, or a target older than every tag in the repo).
     *
     * Filtering by target catches the case where a higher-numbered
     * release was tagged out-of-order: cutting 8.1.0 should not report
     * 8.2.0 as the previous release.
     */
    public function previousRelease(string $targetVersion): string
    {
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $targetVersion, $tm) !== 1) {
            throw new \InvalidArgumentException(
                'target version must be MAJOR.MINOR.PATCH; got: ' . $targetVersion,
            );
        }
        $latestTag = $this->latestVersionTagBelow($targetVersion);
        if ($latestTag !== null) {
            return $latestTag;
        }
        $prevMinor = max(0, ((int) $tm[2]) - 1);
        return sprintf('%s.%d.0', $tm[1], $prevMinor);
    }

    private function latestVersionTagBelow(string $targetVersion): ?string
    {
        // When the shipped-versions manifest is available it's the source
        // of truth: iterate its entries directly. The annotated-tag walk
        // below silently drops lightweight tags — and historic SourceForge-
        // era releases like v8_0_0 are lightweight. Filtering annotated
        // tags through the manifest would incorrectly discard v8_0_0 as
        // "unshipped," even though data/releases.json marks 8.0.0 FINAL.
        // Keying on the manifest directly avoids that conflation.
        $shipped = $this->fetchShippedVersions();
        if ($shipped !== null) {
            usort($shipped, static fn (string $a, string $b): int => version_compare($b, $a));
            foreach ($shipped as $candidate) {
                if (version_compare($candidate, $targetVersion, '<')) {
                    return $candidate;
                }
            }
            return null;
        }
        // Manifest fetch/parse failed. Fall back to the pre-manifest
        // annotated-tag walk — imperfect for the skipped-release edge
        // case (a cut-then-skipped tag will win over the actually-shipped
        // predecessor) but the safest available floor when we have no
        // better source of truth.
        foreach ($this->releaseTags() as [$major, $minor, $patch]) {
            $candidate = sprintf('%d.%d.%d', $major, $minor, $patch);
            if (version_compare($candidate, $targetVersion, '<')) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Fetch the openemr/website-openemr `data/releases.json` manifest and
     * return the set of versions with `status: FINAL` — the source of
     * truth for what actually shipped. Returns null when no HTTP client
     * was supplied, or when the fetch/parse fails for any reason; callers
     * treat null as "fall back to tag-only".
     *
     * @return ?list<string>
     */
    private function fetchShippedVersions(): ?array
    {
        if ($this->httpClient === null) {
            return null;
        }
        try {
            // Both timeout (idle) and max_duration (total wall-clock) are
            // capped so a slow or hung raw.githubusercontent response
            // can't stall the conductor workflow for a full 60s (PHP's
            // default_socket_timeout) before falling back to tag-only.
            $response = $this->httpClient->request('GET', $this->manifestUrl, [
                'timeout' => 5,
                'max_duration' => 10,
            ]);
            $body = $response->getContent();
        } catch (HttpClientException) {
            return null;
        }
        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
        if (!is_array($decoded)) {
            return null;
        }
        $shipped = [];
        foreach ($decoded as $version => $entry) {
            if (!is_string($version)) {
                continue;
            }
            if (preg_match('/^\d+\.\d+\.\d+$/', $version) !== 1) {
                continue;
            }
            if (!is_array($entry)) {
                continue;
            }
            if (($entry['status'] ?? null) !== 'FINAL') {
                continue;
            }
            $shipped[] = $version;
        }
        return $shipped;
    }

    /**
     * Highest PATCH among v<MAJOR>_<MINOR>_* tags on the given minor
     * line, or null when no release has been cut on that line yet.
     */
    private function highestPatchOnMinorLine(int $major, int $minor): ?int
    {
        $highest = null;
        foreach ($this->releaseTags() as [$tagMajor, $tagMinor, $tagPatch]) {
            if ($tagMajor !== $major || $tagMinor !== $minor) {
                continue;
            }
            if ($highest === null || $tagPatch > $highest) {
                $highest = $tagPatch;
            }
        }
        return $highest;
    }

    /**
     * All v<MAJOR>_<MINOR>_<PATCH> release tags in the repo, parsed into
     * [major, minor, patch] tuples and sorted descending by version
     * (highest first). Non-release v* tags are skipped.
     *
     * Only annotated tags count: the release spec requires `git tag -a`
     * (see TagVerifier), so a lightweight tag whose name happens to look
     * like a release must not influence the derived version.
     *
     * @return list<array{int, int, int}>
     */
    private function releaseTags(): array
    {
        $process = new Process(
            ['git', 'tag', '--list', 'v*', '--sort=-v:refname', '--format=%(objecttype) %(refname:short)'],
            $this->repoDir,
        );
        $process->mustRun();
        $output = trim($process->getOutput());
        if ($output === '') {
            return [];
        }
        $split = preg_split('/\R/', $output);
        $lines = $split === false ? [] : $split;
        $tags = [];
        foreach ($lines as $line) {
            $parts = explode(' ', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            [$objectType, $tag] = $parts;
            if ($objectType !== 'tag') {
                continue;
            }
            if (preg_match('/^v(\d+)_(\d+)_(\d+)$/', $tag, $m) !== 1) {
                continue;
            }
            $tags[] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        }
        return $tags;
    }
}
