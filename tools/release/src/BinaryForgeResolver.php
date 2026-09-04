<?php

/**
 * Pick the newest openemr-static-binary-forge release that the binary
 * Docker image can actually build against.
 *
 * The forge publishes one GitHub release per (platform, arch) rather than
 * one per build, so "there is a newer release" is not the same question as
 * "there is a newer release we can adopt". A bump is only safe once every
 * linux arch the image builds for has its release, each release carries
 * every asset the Dockerfile downloads, and the matching openemr/openemr
 * tag exists with the tests/ tree the image overlays onto the PHAR. Any
 * candidate failing one of those falls through to the next-newest, and a
 * run with no qualifying candidate returns the current pin unchanged so
 * the caller writes nothing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class BinaryForgeResolver
{
    public const FORGE_REPO = 'Jmevorach/openemr-static-binary-forge';

    /**
     * Forge release tags for the linux platform, e.g.
     * `linux_amd64-php85-openemr-v8_3_0-amd64-08232026`. The `\1`
     * backreference requires both arch segments to agree, so a
     * mismatched tag is ignored rather than half-parsed.
     */
    private const TAG_PATTERN = '/^linux_(\w+)-(php\d+)-openemr-v(\d+(?:_\d+)+)-\1-(\d{8})$/';

    public function __construct(
        private GitHubApi $forge,
        private GitHubApi $openemr,
    ) {
    }

    /**
     * The newest adoptable pin, or $current when nothing newer qualifies.
     *
     * Only candidates strictly newer than $current are considered, so the
     * resolver can never propose a downgrade — the forge re-cuts older
     * version lines (v7_0_4 was rebuilt three times), and those rebuilds
     * must not drag the image backwards.
     */
    public function resolve(BinaryForgePin $current): BinaryForgePin
    {
        foreach ($this->candidates($current) as [$pin, $assets]) {
            if ($this->isFullyPublished($pin, $assets)) {
                return $pin;
            }
        }

        return $current;
    }

    /**
     * Candidate pins newer than $current, newest first, each paired with
     * the asset names published per arch.
     *
     * @return list<array{0: BinaryForgePin, 1: array<string, list<string>>}>
     */
    private function candidates(BinaryForgePin $current): array
    {
        /** @var array<string, array{0: BinaryForgePin, 1: array<string, list<string>>}> $grouped */
        $grouped = [];

        foreach ($this->forge->paginate('/releases?per_page=100') as $release) {
            $tag = $release['tag_name'] ?? null;
            if (!is_string($tag) || ($release['draft'] ?? false) === true) {
                continue;
            }
            if (preg_match(self::TAG_PATTERN, $tag, $matches) !== 1) {
                continue;
            }
            [, $arch, $phpSelector, $version, $date] = $matches;
            if (!in_array($arch, BinaryForgePin::ARCHES, true)) {
                continue;
            }

            $pin = new BinaryForgePin($version, $date, $current->phpVersion);
            if ($phpSelector !== $pin->phpSelector()) {
                continue;
            }
            if ($pin->chronologicalDate() <= $current->chronologicalDate()) {
                continue;
            }

            $key = (string)$pin;
            $grouped[$key] ??= [$pin, []];
            $grouped[$key][1][$arch] = $this->assetNames($release);
        }

        $candidates = array_values($grouped);
        usort(
            $candidates,
            static fn(array $a, array $b): int
                => $b[0]->chronologicalDate() <=> $a[0]->chronologicalDate(),
        );

        return $candidates;
    }

    /**
     * @param array<string, mixed> $release
     * @return list<string>
     */
    private function assetNames(array $release): array
    {
        $assets = $release['assets'] ?? [];
        if (!is_array($assets)) {
            return [];
        }

        $names = [];
        foreach ($assets as $asset) {
            $name = is_array($asset) ? ($asset['name'] ?? null) : null;
            if (is_string($name)) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @param array<string, list<string>> $assetsByArch
     */
    private function isFullyPublished(BinaryForgePin $pin, array $assetsByArch): bool
    {
        foreach (BinaryForgePin::ARCHES as $arch) {
            $published = $assetsByArch[$arch] ?? null;
            if ($published === null) {
                return false;
            }
            foreach ($pin->requiredAssets($arch) as $required) {
                if (!in_array($required, $published, true)) {
                    return false;
                }
            }
        }

        // The image overlays tests/ from the matching openemr/openemr tag
        // onto the PHAR extract, so the tag has to exist AND still carry a
        // tests/ tree. Checking the tree (not just the ref) is deliberate:
        // tests/ is `export-ignore` in .gitattributes, which is what broke
        // the v8_3_0 bump when the Dockerfile still fetched tag tarballs.
        return $this->openemr->exists('/contents/tests?ref=' . $pin->openemrTag());
    }
}
