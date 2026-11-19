<?php

/**
 * Turns the output of a failed Composer check into a report
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\ComposerAnnotations;

use OpenEMR\Common\Utils\Json;

final class Reporter
{
    private const COMPOSER_JSON = 'composer.json';
    private const COMPOSER_LOCK = 'composer.lock';

    /**
     * GitHub shows at most this many error annotations from one step and drops the rest.
     *
     * @see https://docs.github.com/en/actions/reference/workflows-and-actions/workflow-commands#setting-an-error-message
     */
    private const MAX_ANNOTATIONS = 10;

    public function report(ComposerCheck $check, string $output): Report
    {
        $report = match ($check) {
            ComposerCheck::RequireChecker => $this->requireChecker($output),
            ComposerCheck::Validate => $this->validate($output),
            ComposerCheck::Normalize => $this->normalize($output),
        };
        return new Report($report->log, self::capped($report->annotations), $report->summary);
    }

    /**
     * Fold the annotations GitHub would drop into one that says how many more the job summary holds.
     *
     * @param non-empty-list<Annotation> $annotations
     * @return non-empty-list<Annotation>
     */
    private static function capped(array $annotations): array
    {
        if (count($annotations) <= self::MAX_ANNOTATIONS) {
            return $annotations;
        }
        $shown = array_slice($annotations, 0, self::MAX_ANNOTATIONS - 1);
        $shown[] = new Annotation(
            $annotations[0]->file,
            $annotations[0]->title,
            sprintf('%d more findings are in the job summary.', count($annotations) - count($shown)),
        );
        return $shown;
    }

    /**
     * Each unknown symbol becomes an error on composer.json, with the dependency the tool guessed.
     */
    private function requireChecker(string $output): Report
    {
        $symbols = $this->unknownSymbols($output);
        if ($symbols === []) {
            // No symbols to report: the tool itself failed. Show what it printed.
            $summary = ['## Composer Require Checker failed', '', 'It did not produce a report of unknown symbols; see the job log.'];
            if (trim($output) !== '') {
                array_push($summary, '', self::fenced('text', $output));
            }
            return new Report(
                $output,
                [new Annotation(self::COMPOSER_JSON, 'Composer Require Checker', 'Composer Require Checker did not produce a report; see the job log.')],
                self::lines($summary),
            );
        }

        $log = ['Symbols used in the code that no declared dependency provides:', ''];
        $annotations = [];
        $summary = [
            '## Composer Require Checker: unknown symbols',
            '',
            'Declare the dependency in `composer.json`, or, for a symbol that is not a dependency, add it to `.composer-require-checker.json`.',
            '',
            '| Symbol | Guessed dependency |',
            '| - | - |',
        ];
        foreach ($symbols as $symbol => $guess) {
            $message = $symbol . ' is used but no package or extension in composer.json provides it.';
            if ($guess === '') {
                $log[] = '  ' . $symbol;
            } else {
                $message .= ' Guessed dependency: ' . $guess . '.';
                $log[] = '  ' . $symbol . ' (guessed: ' . $guess . ')';
            }
            $annotations[] = new Annotation(self::COMPOSER_JSON, 'Unknown symbol', $message);
            $summary[] = sprintf('| `%s` | %s |', $symbol, $guess === '' ? 'none' : $guess);
        }
        return new Report(self::lines($log), $annotations, self::lines($summary));
    }

    /**
     * Read the unknown symbols from the JSON report, each with its guessed dependencies joined by commas.
     *
     * @return array<string, string> empty when the output is not a report or names no symbols
     */
    private function unknownSymbols(string $output): array
    {
        try {
            $report = Json::decode($output);
        } catch (\JsonException) {
            return [];
        }
        if (!is_array($report) || !is_array($report['unknown-symbols'] ?? null)) {
            return [];
        }
        $symbols = [];
        foreach ($report['unknown-symbols'] as $symbol => $guesses) {
            $guesses = is_array($guesses) ? array_filter($guesses, is_string(...)) : [];
            $symbols[strval($symbol)] = implode(', ', $guesses);
        }
        return $symbols;
    }

    /**
     * Each "- " finding becomes an error on composer.json, or on composer.lock under a lock-file heading.
     */
    private function validate(string $output): Report
    {
        $annotations = [];
        $file = self::COMPOSER_JSON;
        foreach (explode(chr(10), $output) as $line) {
            if (str_starts_with($line, '# Lock file')) {
                $file = self::COMPOSER_LOCK;
            } elseif (str_starts_with($line, '# ')) {
                $file = self::COMPOSER_JSON;
            } elseif (str_starts_with($line, '- ')) {
                $annotations[] = new Annotation($file, 'composer validate', substr($line, 2));
            }
        }
        if ($annotations === []) {
            $annotations[] = new Annotation(self::COMPOSER_JSON, 'composer validate', 'composer.json failed strict validation; see the job log.');
        }
        return new Report(
            $output,
            $annotations,
            self::lines(['## composer validate --strict failed', '', self::fenced('text', $output)]),
        );
    }

    /**
     * Each run of changed lines in the diff becomes an error on the composer.json line where it starts, and
     * the diff goes in the summary. Output that is not a diff means normalize itself failed, for example
     * from a missing plugin or an outdated lock file.
     */
    private function normalize(string $output): Report
    {
        $diff = self::diffIn($output);
        if ($diff === '') {
            return new Report(
                $output,
                [new Annotation(self::COMPOSER_JSON, 'composer normalize', 'composer normalize failed without a diff; see the job log.')],
                self::lines(['## composer normalize failed', '', self::fenced('text', $output)]),
            );
        }
        $message = 'composer.json is not normalized. Run `composer normalize` and commit the result.';
        $annotations = array_map(
            static fn(int $line): Annotation => new Annotation(self::COMPOSER_JSON, 'composer normalize', $message, $line),
            self::changedLines($diff),
        );
        return new Report(
            $output,
            $annotations === [] ? [new Annotation(self::COMPOSER_JSON, 'composer normalize', $message)] : $annotations,
            self::lines([
                '## composer.json is not normalized',
                '',
                'Run `composer normalize` and commit the result. The changes it would make:',
                '',
                self::fenced('diff', $diff),
            ]),
        );
    }

    /**
     * The diff normalize prints, from its first file header to the marker it prints after the diff.
     *
     * @return string empty when the output holds no diff
     */
    private static function diffIn(string $output): string
    {
        $diff = [];
        foreach (explode(chr(10), $output) as $line) {
            if ($diff === [] && !str_starts_with($line, '--- ') && !str_starts_with($line, '+++ ')) {
                continue;
            }
            if (str_starts_with($line, '----------- end diff')) {
                break;
            }
            $diff[] = $line;
        }
        return implode(chr(10), $diff);
    }

    /**
     * The line in the original file where each run of changed lines in a unified diff starts.
     *
     * @return list<positive-int>
     */
    private static function changedLines(string $diff): array
    {
        $starts = [];
        $line = null;
        $inChange = false;
        foreach (explode(chr(10), $diff) as $text) {
            if (str_starts_with($text, '@@ -')) {
                $line = self::hunkStart($text);
                $inChange = false;
                continue;
            }
            if ($line === null || str_starts_with($text, chr(92))) {
                // Before the first hunk (the file headers), or a "\ No newline at end of file" marker.
                continue;
            }
            $removed = str_starts_with($text, '-');
            if (!$removed && !str_starts_with($text, '+')) {
                $inChange = false;
                $line++;
                continue;
            }
            if (!$inChange) {
                $starts[] = max(1, $line);
                $inChange = true;
            }
            if ($removed) {
                $line++;
            }
        }
        return $starts;
    }

    /**
     * The original file's first line from a hunk header such as `@@ -357,6 +358,5 @@`.
     */
    private static function hunkStart(string $header): ?int
    {
        $range = explode(' ', substr($header, strlen('@@ -')), 2)[0];
        $start = filter_var(explode(',', $range, 2)[0], FILTER_VALIDATE_INT);
        return is_int($start) ? $start : null;
    }

    /**
     * Fence the content with more backticks than any run inside it, so the content cannot close the block early.
     */
    private static function fenced(string $lang, string $content): string
    {
        $fence = '```';
        while (str_contains($content, $fence)) {
            $fence .= '`';
        }
        return implode(PHP_EOL, [$fence . $lang, rtrim($content, chr(10)), $fence]);
    }

    /**
     * @param list<string> $lines
     */
    private static function lines(array $lines): string
    {
        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
