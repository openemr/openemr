<?php

/**
 * Gender filter for a Synthea run (Synthea's -g option).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

/**
 * Backed because the value is passed to Synthea on its command line.
 */
enum Gender: string
{
    case Male = 'M';
    case Female = 'F';
}
