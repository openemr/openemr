<?php

/**
 * The set of pins that identify one openemr-static-binary-forge release
 * line for the binary Docker image: the OpenEMR version, the forge build
 * date, and the PHP version the binaries were compiled against.
 *
 * These three values appear as ARG defaults in docker/binary/Dockerfile
 * and are woven into the forge download URLs. Keeping the URL/tag/asset
 * naming here (rather than in the resolver) means the naming scheme is
 * stated once and can be falsified by a test without any network access.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class BinaryForgePin implements \Stringable
{
    /** OpenEMR version in forge/tag form, e.g. `8_3_0` or `7_0_3_4`. */
    private const VERSION_PATTERN = '/^\d+(?:_\d+)+$/';

    /** Forge build date, MMDDYYYY. */
    private const DATE_PATTERN = '/^\d{8}$/';

    /** PHP version as written in the Dockerfile ARG, e.g. `8.5`. */
    private const PHP_PATTERN = '/^\d+\.\d+$/';

    /**
     * Linux architectures the binary image is built for. Both must be
     * fully published before a pin is safe to adopt — a manifest that
     * bumped on amd64 alone would break every arm64 build until the
     * second arch landed.
     *
     * @var list<string>
     */
    public const ARCHES = ['amd64', 'arm64'];

    public function __construct(
        public string $openemrVersion,
        public string $releaseDate,
        public string $phpVersion,
    ) {
        if (preg_match(self::VERSION_PATTERN, $openemrVersion) !== 1) {
            throw new \InvalidArgumentException("Not a forge OpenEMR version: {$openemrVersion}");
        }
        if (preg_match(self::DATE_PATTERN, $releaseDate) !== 1) {
            throw new \InvalidArgumentException("Not an MMDDYYYY forge release date: {$releaseDate}");
        }
        if (preg_match(self::PHP_PATTERN, $phpVersion) !== 1) {
            throw new \InvalidArgumentException("Not a MAJOR.MINOR PHP version: {$phpVersion}");
        }
    }

    /**
     * The forge's PHP selector segment, e.g. `8.5` becomes `php85`.
     * docker/binary/Dockerfile derives the same segment with `tr -d '.'`.
     */
    public function phpSelector(): string
    {
        return 'php' . str_replace('.', '', $this->phpVersion);
    }

    /**
     * The forge release tag for one architecture, e.g.
     * `linux_amd64-php85-openemr-v8_3_0-amd64-08232026`.
     */
    public function forgeTag(string $arch): string
    {
        return sprintf(
            'linux_%s-%s-openemr-v%s-%s-%s',
            $arch,
            $this->phpSelector(),
            $this->openemrVersion,
            $arch,
            $this->releaseDate,
        );
    }

    /**
     * Assets docker/binary/Dockerfile downloads from one arch's forge
     * release. A release that exists but is missing any of these is a
     * half-published build, not a bumpable target.
     *
     * @return list<string>
     */
    public function requiredAssets(string $arch): array
    {
        return [
            "php-fpm-v{$this->openemrVersion}-linux-{$arch}",
            "php-cli-v{$this->openemrVersion}-linux-{$arch}",
            'openemr.phar',
        ];
    }

    /** The openemr/openemr git tag the forge release was cut from. */
    public function openemrTag(): string
    {
        return "v{$this->openemrVersion}";
    }

    /** Dotted form for version_compare(), e.g. `7_0_3_4` becomes `7.0.3.4`. */
    public function dottedVersion(): string
    {
        return str_replace('_', '.', $this->openemrVersion);
    }

    /**
     * True when $candidate is a pin this one should give way to.
     *
     * Version dominates date. A forge re-cut of an older line can carry a
     * build date later than the current pin's — v7_0_4 was rebuilt three
     * times, and nothing stops a future rebuild landing after a newer line
     * shipped — so comparing dates alone would accept it and walk the
     * image backwards. Within one version line a later build date does win,
     * which is how a rebuild with corrected binaries gets adopted.
     */
    public function isSupersededBy(self $candidate): bool
    {
        $versions = version_compare($candidate->dottedVersion(), $this->dottedVersion());
        if ($versions !== 0) {
            return $versions > 0;
        }

        return $candidate->chronologicalDate() > $this->chronologicalDate();
    }

    /**
     * MMDDYYYY reordered to YYYYMMDD so plain string comparison orders
     * releases chronologically. The forge's own `created_at` cannot be
     * used for this: the mac_os v8_3_0 release carries an 08232026 build
     * date but a 2026-03-11 creation timestamp.
     */
    public function chronologicalDate(): string
    {
        return substr($this->releaseDate, 4, 4) . substr($this->releaseDate, 0, 4);
    }

    /** Wire format consumed by the updatecli manifest's two targets. */
    public function __toString(): string
    {
        return "{$this->openemrVersion}/{$this->releaseDate}";
    }
}
