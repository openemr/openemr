<?php

/**
 * A made-up identifier like "{CMS}{8 digits}" or "GRP{5 digits}": text
 * with `{CMS}` (the company's cms_id) and `{N digits}` parts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class NumberFormat
{
    private const CMS = "\0CMS";

    /**
     * @param list<string|int> $parts text, the CMS marker, or a digit count
     */
    private function __construct(private array $parts)
    {
    }

    public static function parse(string $format, string $path): self
    {
        $pieces = preg_split('/(\{[^}]*\})/', $format, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if ($pieces === false) {
            throw SeedFileException::at($path, 'could not read the format');
        }
        $parts = [];
        $hasDigits = false;
        foreach ($pieces as $piece) {
            if (!str_starts_with($piece, '{')) {
                $parts[] = $piece;
            } elseif ($piece === '{CMS}') {
                $parts[] = self::CMS;
            } elseif (preg_match('/^\{(\d{1,2}) digits?\}$/', $piece, $matches) === 1 && (int) $matches[1] > 0) {
                $parts[] = (int) $matches[1];
                $hasDigits = true;
            } else {
                throw SeedFileException::at($path, 'unknown part ' . $piece . '; use {CMS} or {N digits}');
            }
        }
        if (!$hasDigits) {
            throw SeedFileException::at($path, 'needs a {N digits} part');
        }
        return new self($parts);
    }

    /**
     * @param callable(int, int): int $between random whole number in a range
     */
    public function render(string $cmsPrefix, callable $between): string
    {
        $out = '';
        foreach ($this->parts as $part) {
            if ($part === self::CMS) {
                $out .= $cmsPrefix;
            } elseif (is_int($part)) {
                for ($i = 0; $i < $part; $i++) {
                    $out .= (string) $between(0, 9);
                }
            } else {
                $out .= $part;
            }
        }
        return $out;
    }
}
