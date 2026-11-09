<?php

/**
 * Applies the header rules to one file
 *
 * Only `@license` is required. `@author`, `@copyright`, and `@link` may name
 * anyone and repeat freely; the checker flags them only when empty, or when a
 * copyright year is in the future or a range runs backward.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final readonly class HeaderChecker
{
    private const NON_EMPTY_TAGS = ['license', 'author', 'copyright', 'link'];
    private const DIGITS = '0123456789';

    public function __construct(
        private Policy $policy,
        private HeaderExtractor $extractor,
        private int $currentYear,
    ) {
    }

    public function check(string $path, FileType $type, string $source): FileResult
    {
        $lines = substr_count($source, "\n") + (str_ends_with($source, "\n") || $source === '' ? 0 : 1);
        if ($this->policy->exemptionReason($path) !== null) {
            return FileResult::exempt($path, $lines);
        }

        $tags = $this->extractor->extract($type, $source);
        $findings = [];
        $hasLicense = false;
        foreach ($tags as $tag) {
            if (in_array($tag->name, self::NON_EMPTY_TAGS, true) && $tag->value === '') {
                $findings[] = new Finding($path, $tag->line, Rule::EmptyTag, "The @{$tag->name} tag is empty.");
                continue;
            }
            if ($tag->name === 'license') {
                $hasLicense = true;
                if (!$this->policy->allowsLicense($path, $tag->value)) {
                    $findings[] = new Finding($path, $tag->line, Rule::UnlistedLicense, sprintf('The @license value "%s" is not allowed for this path.', $tag->value));
                }
            }
            if ($tag->name === 'copyright') {
                foreach ($this->yearProblems($tag->value) as $problem) {
                    $findings[] = new Finding($path, $tag->line, Rule::CopyrightYear, $problem);
                }
            }
        }
        if (!$hasLicense) {
            array_unshift($findings, new Finding($path, 1, Rule::MissingLicense, 'The file header has no @license tag.'));
        }
        return FileResult::checked($path, $lines, $findings);
    }

    /**
     * @return list<string>
     */
    private function yearProblems(string $copyright): array
    {
        $problems = [];
        $previousYear = null;
        $previousEnd = 0;
        $offset = strcspn($copyright, self::DIGITS);
        $length = strlen($copyright);
        while ($offset < $length) {
            $run = strspn($copyright, self::DIGITS, $offset);
            if ($run === 4 && $this->standsAlone($copyright, $offset, $run)) {
                $year = (int) substr($copyright, $offset, 4);
                if ($year > $this->currentYear) {
                    $problems[] = "Copyright year {$year} is in the future.";
                }
                $between = trim(substr($copyright, $previousEnd, $offset - $previousEnd));
                if ($previousYear !== null && $between === '-' && $year < $previousYear) {
                    $problems[] = "Copyright range {$previousYear}-{$year} runs backward.";
                }
                $previousYear = $year;
            } else {
                $previousYear = null;
            }
            $previousEnd = $offset + $run;
            $offset = $previousEnd + strcspn($copyright, self::DIGITS, $previousEnd);
        }
        return $problems;
    }

    /**
     * Digits inside a word, such as the 2310 in tonick2310@example.com, are not a year.
     */
    private function standsAlone(string $text, int $offset, int $length): bool
    {
        $before = $offset === 0 ? '' : $text[$offset - 1];
        $after = $text[$offset + $length] ?? '';
        return !ctype_alnum($before) && !ctype_alnum($after);
    }
}
