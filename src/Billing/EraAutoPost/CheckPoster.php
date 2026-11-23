<?php

/**
 * Posts the claim payments of one check; the part of an unattended run that touches the invoices.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

interface CheckPoster
{
    /**
     * The insurance company to post the check for: the one most of its claims have at the
     * level the payer processed them at, or '' when no claim could be matched to one.
     */
    public function payerFor(RemittanceCheck $check): string;

    /**
     * Whether a payment session for this check already exists under another insurance
     * company, as when a person posted it choosing a different one. Posting it again for
     * $payerId would not see those postings and would repeat them.
     */
    public function postedForAnotherPayer(RemittanceCheck $check, string $payerId): bool;

    /**
     * Run every claim payment of the check through the posting engine, on the payment
     * session an earlier posting of the check created or else a new one.
     *
     * @param int $postUser the user to post as
     */
    public function post(RemittanceCheck $check, string $payerId, int $postUser, \DateTimeImmutable $today): CheckRecord;
}
