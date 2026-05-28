<?php

/**
 * Section bucket for a single CHANGELOG entry. Mirrors the H3/H4
 * structure of the existing 8.0.0.3 entry: Fixed/Security,
 * Fixed/BugFixes, Added, Changed.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\ReleaseNotes;

enum Section: string
{
    case Security = 'security';
    case BugFixes = 'bug_fixes';
    case Added = 'added';
    case Changed = 'changed';
}
