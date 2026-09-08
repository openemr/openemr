<?php

/**
 * Isolated tests for OpenEMR\Release\CompatibilityNotesRenderer,
 * focused on the inject() idempotence fix introduced alongside
 * CompatibilityMutator (PR 8). CompatibilityMutator is the first live
 * consumer of inject(); a rerun with the same section must be a
 * no-op rather than duplicating the "### Minimum supported versions"
 * block.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\CompatibilityNotesRenderer;
use PHPUnit\Framework\TestCase;

final class CompatibilityNotesRendererTest extends TestCase
{
    private CompatibilityNotesRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new CompatibilityNotesRenderer();
    }

    public function testRenderProducesExpectedSectionShape(): void
    {
        $out = $this->renderer->render(
            ['php' => '8.2', 'mariadb' => '10.6', 'mysql' => '5.7'],
            'https://github.com/openemr/openemr/tree/rel-820/ci',
        );

        self::assertStringStartsWith("### Minimum supported versions\n", $out);
        self::assertStringContainsString('- **PHP** 8.2+', $out);
        self::assertStringContainsString('- **MariaDB** 10.6+', $out);
        self::assertStringContainsString('- **MySQL** 5.7+', $out);
        self::assertStringContainsString(
            '[tested CI matrix](https://github.com/openemr/openemr/tree/rel-820/ci)',
            $out,
        );
    }

    public function testInjectAfterFirstVersionHeadingPreservesTrailingSections(): void
    {
        $notes = <<<'MD'
## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Fixed

  - some bugfix ([#123](https://github.com/openemr/openemr/pull/123))

MD;
        $section = $this->renderer->render(
            ['php' => '8.2'],
            'https://github.com/openemr/openemr/tree/rel-820/ci',
        );

        $out = $this->renderer->inject($notes, $section);

        self::assertStringContainsString('### Minimum supported versions', $out);
        self::assertStringContainsString('### Fixed', $out);
        self::assertLessThan(
            strpos($out, '### Fixed'),
            strpos($out, '### Minimum supported versions'),
            'compat section appears before Fixed',
        );
        // Preserve the version-heading + blank-line prefix
        self::assertStringStartsWith("## [8.2.1]", $out);
    }

    public function testInjectPrependsWhenNoVersionHeadingExists(): void
    {
        $notes = "just body content\n\nno heading\n";
        $section = $this->renderer->render(
            ['php' => '8.2'],
            'https://github.com/openemr/openemr/tree/rel-820/ci',
        );

        $out = $this->renderer->inject($notes, $section);
        self::assertStringStartsWith('### Minimum supported versions', $out);
    }

    public function testInjectIsIdempotentWhenSectionAlreadyPresent(): void
    {
        $notes = <<<'MD'
## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Minimum supported versions

- **PHP** 8.2+

See the [tested CI matrix](https://github.com/openemr/openemr/tree/rel-820/ci) for all tested version combinations.

### Fixed

  - some bugfix ([#123](https://github.com/openemr/openemr/pull/123))

MD;
        $section = $this->renderer->render(
            ['php' => '8.2'],
            'https://github.com/openemr/openemr/tree/rel-820/ci',
        );

        $out = $this->renderer->inject($notes, $section);

        // Exactly one instance of the heading
        self::assertSame(
            1,
            substr_count($out, '### Minimum supported versions'),
            'existing block was stripped before reinjection — no duplicate',
        );
        // Fixed section still present
        self::assertStringContainsString('### Fixed', $out);
        self::assertStringContainsString('#123', $out);
    }

    public function testInjectDoesNotStripCompatBlocksFromOlderReleaseSections(): void
    {
        // CHANGELOG with two prior release entries -- the OLDER one has a
        // compat block from its own release-prep run. When we inject into
        // the NEW top section, the older release's compat block must be
        // preserved.
        $notes = <<<'MD'
## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Fixed

  - new bugfix ([#200](https://github.com/openemr/openemr/pull/200))

## [8.2.0](https://github.com/openemr/openemr/compare/v8_1_0...v8_2_0) - 2026-07-08

### Minimum supported versions

- **PHP** 8.2+
- **MariaDB** 10.6+

See the [tested CI matrix](https://github.com/openemr/openemr/tree/rel-820/ci) for all tested version combinations.

### Fixed

  - old bugfix ([#100](https://github.com/openemr/openemr/pull/100))

MD;
        $section = $this->renderer->render(
            ['php' => '8.3', 'mariadb' => '11.4'],
            'https://github.com/openemr/openemr/tree/rel-821/ci',
        );

        $out = $this->renderer->inject($notes, $section);

        // The new 8.2.1 compat block is added
        self::assertStringContainsString('- **PHP** 8.3+', $out);
        // The older 8.2.0 compat block is preserved
        self::assertStringContainsString('- **PHP** 8.2+', $out);
        self::assertStringContainsString('- **MariaDB** 10.6+', $out);
        // Exactly two headings now (one per release)
        self::assertSame(
            2,
            substr_count($out, '### Minimum supported versions'),
            'top section gets a new compat block, older section keeps its own',
        );
    }

    public function testInjectReplacesStaleSectionWithNewValues(): void
    {
        $notes = <<<'MD'
## [8.2.1](https://github.com/openemr/openemr/compare/v8_2_0...v8_2_1) - 2026-08-01

### Minimum supported versions

- **PHP** 7.4+

See the [tested CI matrix](https://github.com/openemr/openemr/tree/rel-820/ci) for all tested version combinations.

### Fixed

  - a bug ([#1](https://github.com/openemr/openemr/pull/1))

MD;
        // Rerender with updated minimums
        $section = $this->renderer->render(
            ['php' => '8.2', 'mariadb' => '10.6'],
            'https://github.com/openemr/openemr/tree/rel-820/ci',
        );

        $out = $this->renderer->inject($notes, $section);

        self::assertStringContainsString('- **PHP** 8.2+', $out);
        self::assertStringContainsString('- **MariaDB** 10.6+', $out);
        self::assertStringNotContainsString('- **PHP** 7.4+', $out);
        self::assertStringContainsString('### Fixed', $out);
    }
}
