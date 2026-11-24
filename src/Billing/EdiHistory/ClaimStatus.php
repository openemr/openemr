<?php

/**
 * One status a 277 reports for a claim or for one of its service lines (an STC composite).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use DateTimeImmutable;

final readonly class ClaimStatus
{
    /**
     * Action code (STC03) a 277CA gives a claim it accepted.
     */
    private const ACTION_ACCEPT = 'WQ';

    /**
     * Action code (STC03) a 277CA gives a claim it rejected.
     */
    private const ACTION_REJECT = 'U';

    /**
     * Status categories that mean the claim was turned away: returned as
     * unprocessable, or rejected for missing, invalid or inconsistent information.
     */
    private const REJECT_CATEGORIES = ['A3', 'A6', 'A7', 'A8'];

    /**
     * @param string             $category      claim status category code, e.g. A1 (received) or A2 (accepted)
     * @param string             $code          claim status code, e.g. 19 or 20
     * @param string             $entityCode    the entity the status code is about, when the code needs one
     * @param ?DateTimeImmutable $effectiveDate STC02, null when absent or not a date
     * @param string             $actionCode    STC03
     * @param string             $amount        STC04, the charge the status applies to, as written in the file
     * @param string             $text          STC12, the source's free text
     * @param string             $serviceLine   the procedure (SVC01) for a service line status, '' for a claim status
     */
    public function __construct(
        public string $category,
        public string $code,
        public string $entityCode,
        public ?DateTimeImmutable $effectiveDate,
        public string $actionCode,
        public string $amount,
        public string $text,
        public string $serviceLine,
    ) {
    }

    public function isRejected(): bool
    {
        return $this->actionCode === self::ACTION_REJECT
            || in_array($this->category, self::REJECT_CATEGORIES, true);
    }

    public function isAccepted(): bool
    {
        return $this->actionCode === self::ACTION_ACCEPT && !$this->isRejected();
    }

    public function isForServiceLine(): bool
    {
        return $this->serviceLine !== '';
    }
}
