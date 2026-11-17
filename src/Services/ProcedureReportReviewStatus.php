<?php

/**
 * Whether a clinician has signed a procedure report (procedure_report.review_status).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

/**
 * The two values the results screen writes and reads (interface/orders/orders_results.php):
 * a received report waits in Pending Review; a reviewed one has been signed.
 */
enum ProcedureReportReviewStatus: string
{
    case Received = 'received';
    case Reviewed = 'reviewed';
}
