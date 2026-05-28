<?php

/**
 * Typed payload the ChangelogMutator renders into CHANGELOG.md. The
 * conductor workflow gathers PR data via `gh`, the collector script
 * normalises it into the JSON shape consumed by fromJsonFile(), and the
 * mutator never touches the network. Keeping gathering and rendering
 * separate mirrors how DockerComposeProductionMutator handles the image
 * digest input.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\ReleaseNotes;

final readonly class Manifest
{
    /**
     * @param array<value-of<Section>, list<Entry>> $sections
     */
    public function __construct(
        public string $version,
        public int $milestoneNumber,
        public string $milestoneUrl,
        public string $date,
        public array $sections,
    ) {
        if (preg_match('/^\d+\.\d+\.\d+(?:\.\d+)?$/', $version) !== 1) {
            throw new \InvalidArgumentException('Manifest version must be MAJOR.MINOR.PATCH[.N]; got: ' . $version);
        }
        if ($milestoneNumber <= 0) {
            throw new \InvalidArgumentException('Manifest milestoneNumber must be positive');
        }
        if ($milestoneUrl === '') {
            throw new \InvalidArgumentException('Manifest milestoneUrl cannot be empty');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            throw new \InvalidArgumentException('Manifest date must be YYYY-MM-DD; got: ' . $date);
        }
    }

    /**
     * @return list<Entry>
     */
    public function entriesFor(Section $section): array
    {
        return $this->sections[$section->value] ?? [];
    }

    public function isEmpty(): bool
    {
        foreach ($this->sections as $entries) {
            if ($entries !== []) {
                return false;
            }
        }
        return true;
    }

    public static function fromJsonFile(string $path): self
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException('Cannot read release-notes JSON at ' . $path);
        }
        try {
            $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Invalid release-notes JSON at ' . $path, 0, $e);
        }
        if (!is_array($decoded)) {
            throw new \RuntimeException('Release-notes JSON must decode to an object');
        }
        $normalised = [];
        foreach ($decoded as $key => $value) {
            if (!is_string($key)) {
                throw new \RuntimeException('Release-notes JSON must decode to a string-keyed object');
            }
            $normalised[$key] = $value;
        }
        return self::fromArray($normalised);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $version = $data['version'] ?? null;
        if (!is_string($version)) {
            throw new \InvalidArgumentException('Release-notes manifest is missing "version" string');
        }
        $date = $data['date'] ?? null;
        if (!is_string($date)) {
            throw new \InvalidArgumentException('Release-notes manifest is missing "date" string');
        }
        $milestone = $data['milestone'] ?? null;
        if (!is_array($milestone)) {
            throw new \InvalidArgumentException('Release-notes manifest is missing "milestone" object');
        }
        $milestoneNumber = $milestone['number'] ?? null;
        if (!is_int($milestoneNumber)) {
            throw new \InvalidArgumentException('Release-notes manifest milestone.number must be an int');
        }
        $milestoneUrl = $milestone['url'] ?? null;
        if (!is_string($milestoneUrl)) {
            throw new \InvalidArgumentException('Release-notes manifest milestone.url must be a string');
        }
        $rawSections = $data['sections'] ?? [];
        if (!is_array($rawSections)) {
            throw new \InvalidArgumentException('Release-notes manifest "sections" must be an object');
        }
        $sections = [];
        foreach ($rawSections as $key => $entries) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException('Release-notes manifest section keys must be strings');
            }
            $section = Section::tryFrom($key);
            if ($section === null) {
                throw new \InvalidArgumentException('Release-notes manifest has unknown section: ' . $key);
            }
            if (!is_array($entries)) {
                throw new \InvalidArgumentException('Release-notes manifest section "' . $key . '" must be a list');
            }
            $sections[$section->value] = self::parseEntries($key, $entries);
        }
        return new self($version, $milestoneNumber, $milestoneUrl, $date, $sections);
    }

    /**
     * @param array<int|string, mixed> $entries
     * @return list<Entry>
     */
    private static function parseEntries(string $sectionKey, array $entries): array
    {
        $parsed = [];
        foreach ($entries as $row) {
            if (!is_array($row)) {
                throw new \InvalidArgumentException(
                    'Release-notes section "' . $sectionKey . '" contains a non-object entry',
                );
            }
            $title = $row['title'] ?? null;
            if (!is_string($title) || $title === '') {
                throw new \InvalidArgumentException(
                    'Release-notes section "' . $sectionKey . '" entry is missing "title"',
                );
            }
            $prNumber = $row['number'] ?? null;
            $prUrl = $row['url'] ?? null;
            if ($prNumber !== null && !is_int($prNumber)) {
                throw new \InvalidArgumentException(
                    'Release-notes section "' . $sectionKey . '" entry "number" must be int or omitted',
                );
            }
            if ($prUrl !== null && !is_string($prUrl)) {
                throw new \InvalidArgumentException(
                    'Release-notes section "' . $sectionKey . '" entry "url" must be string or omitted',
                );
            }
            $parsed[] = new Entry($title, $prNumber, $prUrl);
        }
        return $parsed;
    }
}
