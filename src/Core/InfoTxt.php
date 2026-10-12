<?php

/**
 * The info.txt that names a custom module or an encounter form: its first
 * line is the display name, and for forms the second line is the category.
 *
 * Lines are trimmed. Reading them with file() kept each line's newline, so
 * names and categories were stored as "Clinical\n", which an exact match
 * (for example a second "Clinical" group in a menu) treats as different.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core;

final readonly class InfoTxt
{
    /**
     * @param ?non-empty-string $name     first line, or null when it is blank
     * @param ?non-empty-string $category second line, or null when it is blank or missing
     */
    private function __construct(
        public ?string $name,
        public ?string $category,
    ) {
    }

    /** The info.txt at $path, or null when there is none or it can't be read. */
    public static function read(string $path): ?self
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }
        $contents = file_get_contents($path);
        return $contents === false ? null : self::parse($contents);
    }

    public static function parse(string $contents): self
    {
        $lines = preg_split('/\R/', $contents) ?: [];
        $name = trim($lines[0] ?? '');
        $category = trim($lines[1] ?? '');
        return new self($name === '' ? null : $name, $category === '' ? null : $category);
    }
}
