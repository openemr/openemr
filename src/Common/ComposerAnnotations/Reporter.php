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

    public function report(ComposerCheck $check, string $output): Report
    {
        return match ($check) {
            ComposerCheck::RequireChecker => $this->requireChecker($output),
            ComposerCheck::Validate => $this->validate($output),
            ComposerCheck::Normalize => $this->normalize($output),
        };
    }

    /**
     * Each unknown symbol becomes an error on composer.json, with the dependency the tool guessed.
     */
    private function requireChecker(string $output): Report
    {
        $symbols = $this->unknownSymbols($output);
        if ($symbols === []) {
            // No symbols to report: the tool itself failed. Show what it printed.
            return new Report(
                $output,
                [new Annotation(self::COMPOSER_JSON, 'Composer Require Checker', 'Composer Require Checker did not produce a report; see the job log.')],
                '',
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
     * The diff goes in the summary under one error on composer.json. Output that is not a diff means
     * normalize itself failed, from a missing plugin or a parse error.
     */
    private function normalize(string $output): Report
    {
        $isDiff = false;
        foreach (explode(chr(10), $output) as $line) {
            if (str_starts_with($line, '--- ') || str_starts_with($line, '+++ ')) {
                $isDiff = true;
                break;
            }
        }
        if (!$isDiff) {
            return new Report(
                $output,
                [new Annotation(self::COMPOSER_JSON, 'composer normalize', 'composer normalize failed without a diff; see the job log.')],
                self::lines(['## composer normalize failed', '', self::fenced('text', $output)]),
            );
        }
        return new Report(
            $output,
            [new Annotation(self::COMPOSER_JSON, 'composer normalize', 'composer.json is not normalized. Run `composer normalize` and commit the result.')],
            self::lines([
                '## composer.json is not normalized',
                '',
                'Run `composer normalize` and commit the result. The changes it would make:',
                '',
                self::fenced('diff', $output),
            ]),
        );
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
