<?php

/**
 * Renders a compliance summary as Markdown for a GitHub job summary
 *
 * Percentages count only checked files; exempt files are reported but left
 * out. Compliance is shown by file count and by line count, so a short stub
 * and a long legacy script are not weighted the same.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final class MarkdownReport
{
    private const DIRECTORY_DEPTH = 2;
    private const DIRECTORY_ROWS = 40;

    public function render(ScanResult $result): string
    {
        $out = ['## License headers', ''];
        $checked = array_values(array_filter($result->files, static fn(FileResult $file): bool => !$file->exempt));
        $exemptCount = count($result->files) - count($checked);
        if ($checked === []) {
            $out[] = "No files to check ({$exemptCount} exempt).";
            return implode(PHP_EOL, $out) . PHP_EOL;
        }

        $compliant = array_values(array_filter($checked, static fn(FileResult $file): bool => $file->isCompliant()));
        $allLines = $this->lineCount($checked);
        $compliantLines = $this->lineCount($compliant);
        $out[] = '| | Files | Lines |';
        $out[] = '| --- | ---: | ---: |';
        $out[] = sprintf('| Compliant | %d (%s) | %d (%s) |', count($compliant), $this->percent(count($compliant), count($checked)), $compliantLines, $this->percent($compliantLines, $allLines));
        $out[] = sprintf('| Not compliant | %d | %d |', count($checked) - count($compliant), $allLines - $compliantLines);
        $out[] = sprintf('| Exempt (not counted) | %d | |', $exemptCount);

        array_push($out, '', '### Findings by rule', '', '| Rule | Findings | Files |', '| --- | ---: | ---: |');
        foreach (Rule::cases() as $rule) {
            $findings = array_filter($result->findings(), static fn(Finding $finding): bool => $finding->rule === $rule);
            $files = array_unique(array_map(static fn(Finding $finding): string => $finding->path, $findings));
            $out[] = sprintf('| `%s` | %d | %d |', $rule->value, count($findings), count($files));
        }

        $directories = $this->group($checked, $this->directoryOf(...));
        $names = array_map(strval(...), array_keys($directories));
        $failing = static fn(string $name): int => $directories[$name][0] - $directories[$name][1];
        usort($names, static fn(string $a, string $b): int => [$failing($b), $a] <=> [$failing($a), $b]);
        $shown = [];
        foreach (array_slice($names, 0, self::DIRECTORY_ROWS) as $name) {
            $shown[$name] = $directories[$name];
        }
        array_push($out, '', '### By directory, most non-compliant files first', '');
        array_push($out, ...$this->table('Directory', $shown));
        if (count($directories) > count($shown)) {
            $out[] = sprintf('%d more directories not shown.', count($directories) - count($shown));
        }

        $types = $this->group($checked, static fn(string $path): string => strtolower(pathinfo($path, PATHINFO_EXTENSION)));
        ksort($types);
        array_push($out, '', '### By file type', '');
        array_push($out, ...$this->table('Type', $types));

        return implode(PHP_EOL, $out) . PHP_EOL;
    }

    /**
     * @param list<FileResult> $files
     * @param callable(string): string $key
     * @return array<string, array{int, int}> checked and compliant counts per key
     */
    private function group(array $files, callable $key): array
    {
        $groups = [];
        foreach ($files as $file) {
            $name = $key($file->path);
            $groups[$name] ??= [0, 0];
            $groups[$name][0]++;
            if ($file->isCompliant()) {
                $groups[$name][1]++;
            }
        }
        return $groups;
    }

    /**
     * @param array<string, array{int, int}> $groups
     * @return list<string>
     */
    private function table(string $heading, array $groups): array
    {
        $rows = ["| {$heading} | Checked | Compliant | % |", '| --- | ---: | ---: | ---: |'];
        foreach ($groups as $name => [$checked, $compliant]) {
            $rows[] = sprintf('| `%s` | %d | %d | %s |', $name, $checked, $compliant, $this->percent($compliant, $checked));
        }
        return $rows;
    }

    private function directoryOf(string $path): string
    {
        $segments = explode('/', $path);
        array_pop($segments);
        if ($segments === []) {
            return '.';
        }
        return implode('/', array_slice($segments, 0, self::DIRECTORY_DEPTH));
    }

    /**
     * @param list<FileResult> $files
     */
    private function lineCount(array $files): int
    {
        return array_sum(array_map(static fn(FileResult $file): int => $file->lines, $files));
    }

    private function percent(int $part, int $whole): string
    {
        return $whole === 0 ? 'n/a' : sprintf('%.1f%%', 100 * $part / $whole);
    }
}
