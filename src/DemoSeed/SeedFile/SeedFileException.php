<?php

/**
 * A seed file that can't be loaded: bad YAML, an unknown key, or a value of
 * the wrong type or out of range. The message names the path in the file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final class SeedFileException extends \InvalidArgumentException
{
    public static function at(string $path, string $problem): self
    {
        return new self($path . ': ' . $problem);
    }
}
