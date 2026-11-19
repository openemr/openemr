<?php

/**
 * Isolated tests for turning a failed Composer check's output into a report
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\ComposerAnnotations;

use OpenEMR\Common\ComposerAnnotations\Annotation;
use OpenEMR\Common\ComposerAnnotations\ComposerCheck;
use OpenEMR\Common\ComposerAnnotations\Report;
use OpenEMR\Common\ComposerAnnotations\Reporter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class ReporterTest extends TestCase
{
    private const REQUIRE_CHECKER_REPORT = <<<'JSON'
        {
          "_meta": {"composer-require-checker": {"version": "4.17.0"}, "date": "2026-11-18T16:32:35+00:00"},
          "unknown-symbols": {
            "gmp_add": ["ext-gmp"],
            "Nonexistent\\Vendor\\Thing": []
          }
        }

        JSON;

    private const VALIDATE_OUTPUT = <<<'TEXT'
        ./composer.json is valid, but with a few warnings
        See https://getcomposer.org/doc/04-schema.md for details on the schema
        # General warnings
        - require.foo/bar : unbound version constraints (*) should be avoided
        # Lock file warnings
        - Required package "foo/bar" is not present in the lock file.

        TEXT;

    public function testRequireCheckerAnnotatesEachUnknownSymbolOnComposerJson(): void
    {
        $report = $this->report(ComposerCheck::RequireChecker, self::REQUIRE_CHECKER_REPORT);

        self::assertEquals([
            new Annotation('composer.json', 'Unknown symbol', 'gmp_add is used but no package or extension in composer.json provides it. Guessed dependency: ext-gmp.'),
            new Annotation('composer.json', 'Unknown symbol', 'Nonexistent\Vendor\Thing is used but no package or extension in composer.json provides it.'),
        ], $report->annotations);
    }

    public function testRequireCheckerListsTheSymbolsInTheLog(): void
    {
        $report = $this->report(ComposerCheck::RequireChecker, self::REQUIRE_CHECKER_REPORT);

        self::assertSame(<<<'TEXT'
            Symbols used in the code that no declared dependency provides:

              gmp_add (guessed: ext-gmp)
              Nonexistent\Vendor\Thing

            TEXT, $report->log);
    }

    public function testRequireCheckerSummarizesTheSymbolsInATable(): void
    {
        $report = $this->report(ComposerCheck::RequireChecker, self::REQUIRE_CHECKER_REPORT);

        self::assertSame(<<<'MARKDOWN'
            ## Composer Require Checker: unknown symbols

            Declare the dependency in `composer.json`, or, for a symbol that is not a dependency, add it to `.composer-require-checker.json`.

            | Symbol | Guessed dependency |
            | - | - |
            | `gmp_add` | ext-gmp |
            | `Nonexistent\Vendor\Thing` | none |

            MARKDOWN, $report->summary);
    }

    public function testRequireCheckerJoinsSeveralGuessesWithCommas(): void
    {
        $report = $this->report(ComposerCheck::RequireChecker, '{"unknown-symbols": {"Foo": ["a/b", "c/d"]}}');

        self::assertSame('Foo is used but no package or extension in composer.json provides it. Guessed dependency: a/b, c/d.', $report->annotations[0]->message);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function requireCheckerFailureProvider(): array
    {
        return [
            'not JSON' => ['PHP Fatal error: Allowed memory size exhausted' . PHP_EOL],
            'no unknown symbols' => ['{"unknown-symbols": {}}' . PHP_EOL],
            'no unknown-symbols key' => ['{"_meta": {}}' . PHP_EOL],
            'a JSON list' => ['[1, 2]' . PHP_EOL],
        ];
    }

    #[DataProvider('requireCheckerFailureProvider')]
    public function testRequireCheckerOutputWithoutSymbolsIsReportedAsAToolFailure(string $output): void
    {
        $report = $this->report(ComposerCheck::RequireChecker, $output);

        self::assertSame($output, $report->log);
        self::assertEquals(
            [new Annotation('composer.json', 'Composer Require Checker', 'Composer Require Checker did not produce a report; see the job log.')],
            $report->annotations,
        );
        self::assertSame('', $report->summary);
    }

    public function testValidateAnnotatesEachFindingAndPutsLockFileFindingsOnComposerLock(): void
    {
        $report = $this->report(ComposerCheck::Validate, self::VALIDATE_OUTPUT);

        self::assertSame(self::VALIDATE_OUTPUT, $report->log);
        self::assertEquals([
            new Annotation('composer.json', 'composer validate', 'require.foo/bar : unbound version constraints (*) should be avoided'),
            new Annotation('composer.lock', 'composer validate', 'Required package "foo/bar" is not present in the lock file.'),
        ], $report->annotations);
    }

    public function testValidateFollowsLockErrorsThenGeneralWarningsToTheirOwnFiles(): void
    {
        $report = $this->report(ComposerCheck::Validate, <<<'TEXT'
            ./composer.json is valid but your composer.lock has some errors
            # Lock file errors
            - The lock file is not up to date with the latest changes in composer.json.
            # General warnings
            - require.foo/bar : unbound version constraints (*) should be avoided

            TEXT);

        self::assertEquals([
            new Annotation('composer.lock', 'composer validate', 'The lock file is not up to date with the latest changes in composer.json.'),
            new Annotation('composer.json', 'composer validate', 'require.foo/bar : unbound version constraints (*) should be avoided'),
        ], $report->annotations);
    }

    public function testValidateOutputWithNoFindingsStillGetsOneAnnotation(): void
    {
        $report = $this->report(ComposerCheck::Validate, './composer.json does not contain valid JSON' . PHP_EOL);

        self::assertEquals(
            [new Annotation('composer.json', 'composer validate', 'composer.json failed strict validation; see the job log.')],
            $report->annotations,
        );
    }

    public function testValidateSummaryHoldsTheOutputInAFencedBlock(): void
    {
        $report = $this->report(ComposerCheck::Validate, self::VALIDATE_OUTPUT);

        self::assertSame(<<<'MARKDOWN'
            ## composer validate --strict failed

            ```text
            ./composer.json is valid, but with a few warnings
            See https://getcomposer.org/doc/04-schema.md for details on the schema
            # General warnings
            - require.foo/bar : unbound version constraints (*) should be avoided
            # Lock file warnings
            - Required package "foo/bar" is not present in the lock file.
            ```

            MARKDOWN, $report->summary);
    }

    public function testValidateKeepsTheCarriageReturnOfACrlfLine(): void
    {
        $report = $this->report(ComposerCheck::Validate, '- finding from a CRLF line' . chr(13) . chr(10));

        self::assertSame('finding from a CRLF line' . chr(13), $report->annotations[0]->message);
    }

    public function testNormalizeReportsOneAnnotationAndTheDiff(): void
    {
        $diff = <<<'DIFF'
            --- original
            +++ normalized
            -  "b": 1,
            +  "a": 2,

            DIFF;

        $report = $this->report(ComposerCheck::Normalize, $diff);

        self::assertSame($diff, $report->log);
        self::assertEquals(
            [new Annotation('composer.json', 'composer normalize', 'composer.json is not normalized. Run `composer normalize` and commit the result.')],
            $report->annotations,
        );
        self::assertSame(<<<'MARKDOWN'
            ## composer.json is not normalized

            Run `composer normalize` and commit the result. The changes it would make:

            ```diff
            --- original
            +++ normalized
            -  "b": 1,
            +  "a": 2,
            ```

            MARKDOWN, $report->summary);
    }

    public function testNormalizeOutputThatIsNotADiffIsReportedAsAFailedRun(): void
    {
        $output = 'Command "normalize" is not defined.' . PHP_EOL;

        $report = $this->report(ComposerCheck::Normalize, $output);

        self::assertSame($output, $report->log);
        self::assertEquals(
            [new Annotation('composer.json', 'composer normalize', 'composer normalize failed without a diff; see the job log.')],
            $report->annotations,
        );
        self::assertSame(<<<'MARKDOWN'
            ## composer normalize failed

            ```text
            Command "normalize" is not defined.
            ```

            MARKDOWN, $report->summary);
    }

    public function testTheSummaryFenceOutgrowsBackticksInTheContent(): void
    {
        $report = $this->report(ComposerCheck::Normalize, <<<'DIFF'
            --- original
            +++ normalized
            +  "a": "``` b"

            DIFF);

        self::assertStringEndsWith(<<<'MARKDOWN'
            ````diff
            --- original
            +++ normalized
            +  "a": "``` b"
            ````

            MARKDOWN, $report->summary);
    }

    private function report(ComposerCheck $check, string $output): Report
    {
        return (new Reporter())->report($check, $output);
    }
}
