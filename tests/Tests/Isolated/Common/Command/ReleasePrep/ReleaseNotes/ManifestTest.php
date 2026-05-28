<?php

/**
 * Validation tests for the release-notes Manifest loader. The
 * conductor workflow trusts the collector script to emit
 * well-formed JSON, so the loader's job is to fail loudly when an
 * upstream change breaks the contract rather than silently shipping
 * a malformed changelog.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\ReleaseNotes;

use OpenEMR\Common\Command\ReleasePrep\ReleaseNotes\Manifest;
use OpenEMR\Common\Command\ReleasePrep\ReleaseNotes\Section;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('release-prep')]
final class ManifestTest extends TestCase
{
    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-manifest-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->tmpDir);
    }

    public function testFromArrayParsesValidPayload(): void
    {
        $manifest = Manifest::fromArray([
            'version' => '8.1.0',
            'milestone' => ['number' => 25, 'url' => 'https://github.com/openemr/openemr/milestone/25?closed=1'],
            'date' => '2026-05-28',
            'sections' => [
                'security' => [['title' => 'fix XSS', 'number' => 1, 'url' => 'https://example/1']],
                'bug_fixes' => [['title' => 'fix calendar', 'number' => 2, 'url' => 'https://example/2']],
            ],
        ]);

        self::assertSame('8.1.0', $manifest->version);
        self::assertSame(25, $manifest->milestoneNumber);
        self::assertSame('2026-05-28', $manifest->date);
        self::assertCount(1, $manifest->entriesFor(Section::Security));
        self::assertCount(1, $manifest->entriesFor(Section::BugFixes));
        self::assertSame([], $manifest->entriesFor(Section::Added));
        self::assertFalse($manifest->isEmpty());
    }

    public function testIsEmptyWhenNoEntries(): void
    {
        $manifest = Manifest::fromArray([
            'version' => '8.1.0',
            'milestone' => ['number' => 1, 'url' => 'https://example/1'],
            'date' => '2026-05-28',
            'sections' => [],
        ]);
        self::assertTrue($manifest->isEmpty());
    }

    public function testFromJsonFileRejectsMalformedJson(): void
    {
        $path = $this->tmpDir . '/broken.json';
        file_put_contents($path, '{ not valid json');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Invalid release-notes JSON/');
        Manifest::fromJsonFile($path);
    }

    public function testFromJsonFileRejectsMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cannot read release-notes JSON/');
        Manifest::fromJsonFile($this->tmpDir . '/does-not-exist.json');
    }

    public function testRejectsMissingVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/missing "version"/');
        Manifest::fromArray([
            'milestone' => ['number' => 1, 'url' => 'https://example/1'],
            'date' => '2026-05-28',
            'sections' => [],
        ]);
    }

    public function testRejectsBadDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/date must be YYYY-MM-DD/');
        Manifest::fromArray([
            'version' => '8.1.0',
            'milestone' => ['number' => 1, 'url' => 'https://example/1'],
            'date' => '5/28/2026',
            'sections' => [],
        ]);
    }

    public function testRejectsUnknownSectionKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/unknown section: removed/');
        Manifest::fromArray([
            'version' => '8.1.0',
            'milestone' => ['number' => 1, 'url' => 'https://example/1'],
            'date' => '2026-05-28',
            'sections' => ['removed' => []],
        ]);
    }

    public function testRejectsEntryMissingTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is missing "title"/');
        Manifest::fromArray([
            'version' => '8.1.0',
            'milestone' => ['number' => 1, 'url' => 'https://example/1'],
            'date' => '2026-05-28',
            'sections' => ['changed' => [['number' => 1, 'url' => 'https://example/1']]],
        ]);
    }
}
