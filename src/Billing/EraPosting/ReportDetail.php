<?php

/**
 * A charge, payment or adjustment line in the ERA posting report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

final readonly class ReportDetail
{
    /**
     * @param string       $date    yyyy-mm-dd
     * @param float        $amount  0 shows blank
     * @param float|string $balance the invoice balance after the line: a float, blank
     *                              when 0 or not shown (''), or a formatted amount
     *                              ('%.2f') that shows even when 0.00
     */
    public function __construct(
        public ReportStyle $style,
        public string $patientName,
        public string $invoiceNumber,
        public string $code,
        public string $date,
        public string $description,
        public float $amount,
        public float|string $balance,
    ) {
    }
}
