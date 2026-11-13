<?php

/**
 * How an ERA posting report line is shown.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

enum ReportStyle
{
    /** Information (blue). */
    case Info;
    /** Not posted, or a problem to follow up (red). */
    case Error;
    /** Already on the invoice before this run. */
    case Existing;
    /** Posted by this run (green). */
    case Posted;

    /**
     * The CSS class sl_eob_process.php styles the line with.
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::Info => 'infdetail',
            self::Error => 'errdetail',
            self::Existing => 'olddetail',
            self::Posted => 'newdetail',
        };
    }
}
