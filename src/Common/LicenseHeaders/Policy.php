<?php

/**
 * Which license values are allowed where, and which paths are exempt
 *
 * Loaded from `.license-headers.yaml` at the project root. The standard value
 * is allowed everywhere; each other value is allowed only under its paths.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final readonly class Policy
{
    /**
     * @param array<string, list<PathGroup>> $licenses normalized value to the paths it is allowed under
     * @param list<PathGroup> $exemptions
     */
    public function __construct(
        public string $standardLicense,
        private array $licenses,
        private array $exemptions,
    ) {
    }

    public static function fromFile(string $path): self
    {
        return self::fromYaml((new Filesystem())->readFile($path));
    }

    public static function fromYaml(string $yaml): self
    {
        $data = Yaml::parse($yaml);
        if (!is_array($data)) {
            throw new PolicyException('The license header policy must be a YAML mapping.');
        }
        $standard = $data['standard'] ?? null;
        if (!is_string($standard) || trim($standard) === '') {
            throw new PolicyException('The license header policy needs a non-empty `standard` value.');
        }

        $licenses = [];
        foreach (self::entries($data, 'licenses') as $index => $entry) {
            $value = $entry['value'] ?? null;
            if (!is_string($value) || trim($value) === '') {
                throw new PolicyException(sprintf('licenses[%d] needs a non-empty `value`.', $index));
            }
            $licenses[HeaderTag::normalizeValue($value)][] = self::pathGroup($entry, sprintf('licenses[%d]', $index));
        }

        $exemptions = [];
        foreach (self::entries($data, 'exempt') as $index => $entry) {
            $exemptions[] = self::pathGroup($entry, sprintf('exempt[%d]', $index));
        }

        return new self(HeaderTag::normalizeValue($standard), $licenses, $exemptions);
    }

    public function exemptionReason(string $path): ?string
    {
        foreach ($this->exemptions as $group) {
            if ($group->matches($path)) {
                return $group->reason;
            }
        }
        return null;
    }

    public function allowsLicense(string $path, string $value): bool
    {
        $normalized = HeaderTag::normalizeValue($value);
        if ($normalized === $this->standardLicense) {
            return true;
        }
        foreach ($this->licenses[$normalized] ?? [] as $group) {
            if ($group->matches($path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<mixed> $data
     * @return list<array<mixed>>
     */
    private static function entries(array $data, string $key): array
    {
        $entries = $data[$key] ?? [];
        if (!is_array($entries) || !array_is_list($entries)) {
            throw new PolicyException(sprintf('`%s` must be a list.', $key));
        }
        $result = [];
        foreach ($entries as $index => $entry) {
            if (!is_array($entry)) {
                throw new PolicyException(sprintf('%s[%d] must be a mapping.', $key, $index));
            }
            $result[] = $entry;
        }
        return $result;
    }

    /**
     * @param array<mixed> $entry
     */
    private static function pathGroup(array $entry, string $where): PathGroup
    {
        $reason = $entry['reason'] ?? null;
        if (!is_string($reason) || trim($reason) === '') {
            throw new PolicyException($where . ' needs a non-empty `reason`.');
        }
        $paths = $entry['paths'] ?? null;
        if (!is_array($paths) || !array_is_list($paths) || $paths === []) {
            throw new PolicyException($where . ' needs a non-empty `paths` list.');
        }
        $patterns = [];
        foreach ($paths as $pattern) {
            if (!is_string($pattern) || $pattern === '') {
                throw new PolicyException($where . ' has a path that is not a non-empty string.');
            }
            $patterns[] = $pattern;
        }
        return new PathGroup(trim($reason), $patterns);
    }
}
