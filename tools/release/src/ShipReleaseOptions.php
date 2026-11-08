<?php

/**
 * CLI option coercion helper for bin/ship-release.php. Lives in src/ rather
 * than the bin file because PSR1 forbids combining symbol declarations with
 * side effects in the same file (the bin file's job is the side effect of
 * running the SingleCommandApplication).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Console\Input\InputInterface;

final readonly class ShipReleaseOptions
{
    public static function asString(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);
        return is_string($value) ? $value : '';
    }
}
