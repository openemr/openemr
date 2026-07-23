<?php

/**
 * Parsed `openemr-tag` repository_dispatch payload.
 *
 * The generic value object for the `openemr-tag` envelope: any consumer
 * (announcements, build-release) reuses it to normalize the dispatch into
 * {version, tag, branch}.
 *
 * Validates each field against the canonical pattern from
 * tools/release/contracts/dispatch.schema.json so a malformed envelope
 * fails loudly at parse time instead of producing artifacts that
 * reference "null" or empty strings further down the pipeline.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class TagDispatchPayload
{
    // Patterns mirror dispatch.schema.json's tag/branch/version definitions.
    private const VERSION_PATTERN = '/^\d+\.\d+\.\d+$/';
    private const TAG_PATTERN = '/^v\d+_\d+_\d+(-test\.[0-9a-f]{7})?$/';
    private const BRANCH_PATTERN = '/^rel-[A-Za-z0-9]+$/';

    public function __construct(
        public string $version,
        public string $tag,
        public string $branch,
    ) {
        $this->assertMatches('version', $version, self::VERSION_PATTERN);
        $this->assertMatches('tag', $tag, self::TAG_PATTERN);
        $this->assertMatches('branch', $branch, self::BRANCH_PATTERN);
    }

    /**
     * @throws \JsonException|\RuntimeException
     */
    public static function fromPayloadFile(string $path): self
    {
        if ($path === '-') {
            // Same false-return check as regular files: stdin can fail to
            // read (broken pipe, closed fd) and casting the false to '' would
            // then throw the misleading empty-payload error below instead of
            // the accurate unreadable-source error.
            $contents = file_get_contents('php://stdin');
            if ($contents === false) {
                throw new \RuntimeException('Payload file unreadable: php://stdin');
            }
            $raw = $contents;
        } else {
            if (!is_file($path)) {
                throw new \RuntimeException(sprintf('Payload file not found: %s', $path));
            }
            $contents = file_get_contents($path);
            if ($contents === false) {
                throw new \RuntimeException(sprintf('Payload file unreadable: %s', $path));
            }
            $raw = $contents;
        }
        if ($raw === '') {
            throw new \RuntimeException(sprintf('Empty payload from: %s', $path));
        }
        return self::fromEnvelope(json_decode($raw, true, 512, JSON_THROW_ON_ERROR));
    }

    public static function fromEnvelope(mixed $envelope): self
    {
        if (!is_array($envelope)) {
            throw new \RuntimeException('Dispatch envelope is not a JSON object');
        }
        $event = $envelope['event'] ?? null;
        if ($event !== 'openemr-tag') {
            throw new \RuntimeException(sprintf(
                'Expected event=openemr-tag, got: %s',
                is_string($event) ? $event : '(missing)',
            ));
        }
        $data = $envelope['data'] ?? null;
        if (!is_array($data)) {
            throw new \RuntimeException('Dispatch envelope missing data object');
        }
        $normalized = [];
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }
        return new self(
            self::stringField($normalized, 'version'),
            self::stringField($normalized, 'tag'),
            self::stringField($normalized, 'branch'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function stringField(array $data, string $name): string
    {
        $value = $data[$name] ?? null;
        if (!is_string($value) || $value === '') {
            throw new \RuntimeException(sprintf('Dispatch payload missing or empty field: data.%s', $name));
        }
        return $value;
    }

    private function assertMatches(string $field, string $value, string $pattern): void
    {
        if (preg_match($pattern, $value) !== 1) {
            throw new \RuntimeException(sprintf(
                'Dispatch payload field %s does not match expected shape: %s',
                $field,
                $value,
            ));
        }
    }
}
