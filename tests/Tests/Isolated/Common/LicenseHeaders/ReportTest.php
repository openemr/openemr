<?php

/**
 * Tests the SARIF and Markdown renderings of a scan
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\LicenseHeaders;

use OpenEMR\Common\LicenseHeaders\FileResult;
use OpenEMR\Common\LicenseHeaders\Finding;
use OpenEMR\Common\LicenseHeaders\MarkdownReport;
use OpenEMR\Common\LicenseHeaders\Rule;
use OpenEMR\Common\LicenseHeaders\SarifReport;
use OpenEMR\Common\LicenseHeaders\ScanResult;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class ReportTest extends TestCase
{
    public function testSarifListsEveryRuleAndOneResultPerFinding(): void
    {
        $sarif = json_decode((new SarifReport())->render($this->scan()), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('2.1.0', $this->dig($sarif, 'version'));
        $run = $this->dig($sarif, 'runs', 0);
        self::assertSame(SarifReport::TOOL_NAME, $this->dig($run, 'tool', 'driver', 'name'));
        $rules = $this->dig($run, 'tool', 'driver', 'rules');
        self::assertIsArray($rules);
        self::assertSame(
            array_map(static fn(Rule $rule): string => $rule->value, Rule::cases()),
            array_map(fn(mixed $rule): mixed => $this->dig($rule, 'id'), $rules),
        );
        $results = $this->dig($run, 'results');
        self::assertIsArray($results);
        self::assertCount(2, $results);
        self::assertSame('missing-license', $this->dig($results, 0, 'ruleId'));
        self::assertSame('warning', $this->dig($results, 0, 'level'));
        $location = $this->dig($results, 0, 'locations', 0, 'physicalLocation');
        self::assertSame('src/bad.php', $this->dig($location, 'artifactLocation', 'uri'));
        self::assertSame(1, $this->dig($location, 'region', 'startLine'));
        $message = $this->dig($results, 0, 'message', 'text');
        self::assertIsString($message);
        self::assertStringStartsWith('No license.', $message);
    }

    public function testMarkdownSummarizesCompliance(): void
    {
        $markdown = (new MarkdownReport())->render($this->scan());
        self::assertStringContainsString('| Compliant | 1 (33.3%) | 10 (12.5%) |', $markdown);
        self::assertStringContainsString('| Not compliant | 2 | 70 |', $markdown);
        self::assertStringContainsString('| Exempt (not counted) | 1 | |', $markdown);
        self::assertStringContainsString('| `missing-license` | 1 | 1 |', $markdown);
        self::assertStringContainsString('| `unlisted-license` | 1 | 1 |', $markdown);
        self::assertStringContainsString('| `src/a` | 1 | 0 | 0.0% |', $markdown);
        self::assertStringContainsString('| `.` | 1 | 1 | 100.0% |', $markdown);
        self::assertStringContainsString('| `js` | 1 | 0 | 0.0% |', $markdown);
        $srcA = strpos($markdown, '`src/a`');
        $root = strpos($markdown, '| `.` |');
        self::assertIsInt($srcA);
        self::assertIsInt($root);
        self::assertLessThan($root, $srcA, 'Directories with more failing files come first.');
    }

    public function testMarkdownWithNothingToCheck(): void
    {
        $markdown = (new MarkdownReport())->render(new ScanResult([FileResult::exempt('x.php', 3)]));
        self::assertStringContainsString('No files to check (1 exempt).', $markdown);
    }

    public function testMarkdownCapsTheDirectoryTable(): void
    {
        $files = [];
        for ($i = 0; $i < 45; $i++) {
            $files[] = FileResult::checked("dir{$i}/x.php", 1, []);
        }
        $markdown = (new MarkdownReport())->render(new ScanResult($files));
        self::assertStringContainsString('5 more directories not shown.', $markdown);
    }

    /**
     * Walk nested arrays decoded from JSON, failing the test if a level is missing.
     */
    private function dig(mixed $data, string|int ...$keys): mixed
    {
        foreach ($keys as $key) {
            self::assertIsArray($data);
            self::assertArrayHasKey($key, $data);
            $data = $data[$key];
        }
        return $data;
    }

    private function scan(): ScanResult
    {
        return new ScanResult([
            FileResult::checked('src/bad.php', 20, [new Finding('src/bad.php', 1, Rule::MissingLicense, 'No license.')]),
            FileResult::checked('src/a/b/c.js', 50, [new Finding('src/a/b/c.js', 4, Rule::UnlistedLicense, 'Unlisted.')]),
            FileResult::checked('good.php', 10, []),
            FileResult::exempt('vendored/x.php', 100),
        ]);
    }
}
