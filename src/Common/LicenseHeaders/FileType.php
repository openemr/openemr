<?php

/**
 * Comment syntax a scanned file uses for its header
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

enum FileType
{
    case Php;
    /** JS, CSS, and SCSS: slash-star block comments and slash-slash line comments. */
    case CStyle;
    case Twig;

    /**
     * Null means the checker does not scan files with this extension.
     */
    public static function fromPath(string $path): ?self
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'php' => self::Php,
            'js', 'mjs', 'css', 'scss' => self::CStyle,
            'twig' => self::Twig,
            default => null,
        };
    }
}
