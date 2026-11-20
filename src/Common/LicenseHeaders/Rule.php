<?php

/**
 * The header checks; each value is the rule ID reported in SARIF
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

enum Rule: string
{
    case MissingLicense = 'missing-license';
    case UnlistedLicense = 'unlisted-license';
    case EmptyTag = 'empty-tag';
    case CopyrightYear = 'copyright-year';

    public function title(): string
    {
        return match ($this) {
            self::MissingLicense => 'File header has no @license tag',
            self::UnlistedLicense => 'File header has a @license value not allowed for its path',
            self::EmptyTag => 'File header has an empty tag',
            self::CopyrightYear => 'File header has an impossible copyright year',
        };
    }

    public function help(): string
    {
        return match ($this) {
            self::MissingLicense => 'Add the standard file header described under "File Headers" in AGENTS.md. If the file is third-party or generated code, add its path to the exemptions in .license-headers.yaml instead; never add a GPL tag to code the project does not own.',
            self::UnlistedLicense => 'Use the standard @license value. If the file is legitimately under another license, list that value with the path of the file under licenses in .license-headers.yaml.',
            self::EmptyTag => 'Give the tag a value or remove it.',
            self::CopyrightYear => 'Correct the year: it is in the future, or a range runs backward.',
        };
    }
}
