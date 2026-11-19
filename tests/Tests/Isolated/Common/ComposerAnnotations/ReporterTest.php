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
use OpenEMR\Common\Utils\Json;
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
        self::assertSame(
            implode(PHP_EOL, [
                '## Composer Require Checker failed',
                '',
                'It did not produce a report of unknown symbols; see the job log.',
                '',
                '```text',
                rtrim($output, PHP_EOL),
                '```',
                '',
            ]),
            $report->summary,
        );
    }

    public function testRequireCheckerWithNoOutputSummarizesWithoutAnEmptyBlock(): void
    {
        $report = $this->report(ComposerCheck::RequireChecker, '');

        self::assertSame(
            implode(PHP_EOL, ['## Composer Require Checker failed', '', 'It did not produce a report of unknown symbols; see the job log.', '']),
            $report->summary,
        );
    }

    public function testRequireCheckerIgnoresGuessesThatAreNotAList(): void
    {
        $report = $this->report(ComposerCheck::RequireChecker, '{"unknown-symbols": {"Foo": "ext-foo"}}');

        self::assertSame('Foo is used but no package or extension in composer.json provides it.', $report->annotations[0]->message);
    }

    public function testAnnotationsPastGitHubsLimitFoldIntoOne(): void
    {
        $symbols = [];
        for ($i = 1; $i <= 12; $i++) {
            $symbols['Symbol' . $i] = [];
        }

        $report = $this->report(ComposerCheck::RequireChecker, Json::encode(['unknown-symbols' => $symbols]));

        self::assertCount(10, $report->annotations);
        self::assertSame('Symbol9 is used but no package or extension in composer.json provides it.', $report->annotations[8]->message);
        self::assertEquals(
            new Annotation('composer.json', 'Unknown symbol', '3 more findings are in the job summary.'),
            $report->annotations[9],
        );
        self::assertStringContainsString('| `Symbol12` | none |', $report->summary);
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

    public function testNormalizeAnnotatesWhereTheChangeStartsAndSummarizesOnlyTheDiff(): void
    {
        $output = <<<'TEXT'
            Running ergebnis/composer-normalize 2.56.0 by Andreas Möller and contributors.

            ./composer.json is not normalized.

            ---------- begin diff ----------
            --- original
            +++ normalized
            @@ -1,5 +1,5 @@
             {
            -    "b": 1,
            -    "a": 2
            +    "a": 2,
            +    "b": 1
             }

            ----------- end diff -----------


            TEXT;

        $report = $this->report(ComposerCheck::Normalize, $output);

        self::assertSame($output, $report->log);
        self::assertEquals(
            [new Annotation('composer.json', 'composer normalize', 'composer.json is not normalized. Run `composer normalize` and commit the result.', 2)],
            $report->annotations,
        );
        self::assertSame(<<<'MARKDOWN'
            ## composer.json is not normalized

            Run `composer normalize` and commit the result. The changes it would make:

            ```diff
            --- original
            +++ normalized
            @@ -1,5 +1,5 @@
             {
            -    "b": 1,
            -    "a": 2
            +    "a": 2,
            +    "b": 1
             }
            ```

            MARKDOWN, $report->summary);
    }

    public function testNormalizeAnnotatesEachRunOfChangesOnItsOriginalLine(): void
    {
        $report = $this->report(ComposerCheck::Normalize, <<<'DIFF'
            --- original
            +++ normalized
            @@ -10,7 +10,7 @@
                 "a": 1,
            -    "c": 3,
                 "b": 2,
            +    "c": 3,
                 "d": 4,
            @@ -357 +357,2 @@
                 "z": 26
            +    ,"added": true
            \ No newline at end of file

            DIFF);

        self::assertSame([11, 13, 358], array_map(static fn(Annotation $annotation): ?int => $annotation->line, $report->annotations));
    }

    public function testNormalizeDiffWithoutHunkHeadersGetsOneFileAnnotation(): void
    {
        $report = $this->report(ComposerCheck::Normalize, '--- original' . PHP_EOL . '+++ normalized' . PHP_EOL . '-x' . PHP_EOL . '+y' . PHP_EOL);

        self::assertEquals(
            [new Annotation('composer.json', 'composer normalize', 'composer.json is not normalized. Run `composer normalize` and commit the result.')],
            $report->annotations,
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function normalizeFailureProvider(): array
    {
        return [
            'missing plugin' => ['Command "normalize" is not defined.' . PHP_EOL],
            'outdated lock file' => [<<<'TEXT'
                Running ergebnis/composer-normalize 2.56.0 by Andreas Möller and contributors.

                The lock file is not up to date with the latest changes in composer.json, it is recommended that you run `composer update --lock`.

                TEXT],
        ];
    }

    #[DataProvider('normalizeFailureProvider')]
    public function testNormalizeOutputThatIsNotADiffIsReportedAsAFailedRun(string $output): void
    {
        $report = $this->report(ComposerCheck::Normalize, $output);

        self::assertSame($output, $report->log);
        self::assertEquals(
            [new Annotation('composer.json', 'composer normalize', 'composer normalize failed without a diff; see the job log.')],
            $report->annotations,
        );
        self::assertSame(
            implode(PHP_EOL, ['## composer normalize failed', '', '```text', rtrim($output, PHP_EOL), '```', '']),
            $report->summary,
        );
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
