<?php

/**
 * The two sex columns UDS Table 3A reports: Male (column a) and Female
 * (column b).
 *
 * Table 3A has no other column, so a patient's administrative sex must be
 * resolved to one of these at the data boundary before the report is built
 * (parse, don't validate). Backed because it is reported and exchanged with the
 * UDS submission. Matched exhaustively.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

enum UdsSex: string
{
    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
        };
    }
}
