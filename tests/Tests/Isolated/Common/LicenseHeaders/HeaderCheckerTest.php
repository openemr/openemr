<?php

/**
 * Tests the header rules applied to a single file
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\LicenseHeaders;

use OpenEMR\Common\LicenseHeaders\FileType;
use OpenEMR\Common\LicenseHeaders\Finding;
use OpenEMR\Common\LicenseHeaders\HeaderChecker;
use OpenEMR\Common\LicenseHeaders\HeaderExtractor;
use OpenEMR\Common\LicenseHeaders\Policy;
use OpenEMR\Common\LicenseHeaders\Rule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class HeaderCheckerTest extends TestCase
{
    private const POLICY = <<<'YAML'
        standard: GPL
        licenses:
        - value: LGPL
          reason: Library
          paths: [lib/*]
        exempt:
        - reason: Vendored
          paths: [vendored/*]
        YAML;

    /**
     * @param list<array{Rule, int}> $expected rule and line of each finding
     */
    #[DataProvider('headerProvider')]
    public function testFindings(string $path, string $header, array $expected): void
    {
        $result = $this->checker()->check($path, FileType::Php, "<?php\n/**\n{$header}\n */\n");
        $actual = array_map(static fn(Finding $finding): array => [$finding->rule, $finding->line], $result->findings);
        self::assertSame($expected, $actual);
        self::assertFalse($result->exempt);
        self::assertSame($expected === [], $result->isCompliant());
    }

    /**
     * @return array<string, array{string, string, list<array{Rule, int}>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function headerProvider(): array
    {
        return [
            'standard license' => ['src/a.php', ' * @license GPL', []],
            'any authors, links, and years' => ['src/a.php', " * @author Anyone\n * @link https://x.test\n * @copyright Copyright (c) 1999, 2004-2026 Someone <someone2310@x.test>\n * @license GPL", []],
            'no license' => ['src/a.php', ' * @author A', [[Rule::MissingLicense, 1]]],
            'scoped license in scope' => ['lib/a.php', ' * @license LGPL', []],
            'scoped license out of scope' => ['src/a.php', ' * @license LGPL', [[Rule::UnlistedLicense, 3]]],
            'second license must also be allowed' => ['src/a.php', " * @license GPL\n * @license MIT", [[Rule::UnlistedLicense, 4]]],
            'empty license counts as missing too' => ['src/a.php', ' * @license', [[Rule::MissingLicense, 1], [Rule::EmptyTag, 3]]],
            'empty author' => ['src/a.php', " * @author\n * @license GPL", [[Rule::EmptyTag, 3]]],
            'empty unrelated tag is fine' => ['src/a.php', " * @see\n * @license GPL", []],
            'future year' => ['src/a.php', " * @copyright 2027 A\n * @license GPL", [[Rule::CopyrightYear, 3]]],
            'backward range' => ['src/a.php', " * @copyright 2020 - 2019 A\n * @license GPL", [[Rule::CopyrightYear, 3]]],
            'comma list is not a range' => ['src/a.php', " * @copyright 2020, 2019 A\n * @license GPL", []],
            'year after a five-digit number' => ['src/a.php', " * @copyright 12345-2019 A\n * @license GPL", []],
        ];
    }

    public function testFutureYearInBackwardRangeReportsBoth(): void
    {
        $result = $this->checker()->check('a.php', FileType::Php, "<?php\n/** @copyright 2030-2028 A\n @license GPL */\n");
        self::assertSame(
            ['Copyright year 2030 is in the future.', 'Copyright year 2028 is in the future.', 'Copyright range 2030-2028 runs backward.'],
            array_map(static fn(Finding $finding): string => $finding->message, $result->findings),
        );
    }

    public function testExemptFileIsNotChecked(): void
    {
        $result = $this->checker()->check('vendored/x.php', FileType::Php, "<?php\necho 1;\n");
        self::assertTrue($result->exempt);
        self::assertSame([], $result->findings);
        self::assertFalse($result->isCompliant());
        self::assertSame(2, $result->lines);
    }

    public function testCountsLastLineWithoutNewline(): void
    {
        self::assertSame(2, $this->checker()->check('a.php', FileType::Php, "<?php\n/** @license GPL */")->lines);
        self::assertSame(0, $this->checker()->check('a.php', FileType::Php, '')->lines);
    }

    private function checker(): HeaderChecker
    {
        return new HeaderChecker(Policy::fromYaml(self::POLICY), new HeaderExtractor(), 2026);
    }
}
