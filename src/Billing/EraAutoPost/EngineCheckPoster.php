<?php

/**
 * CheckPoster on the ERA posting engine and the payment tables.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

use OpenEMR\Billing\EraCheckPosting;
use OpenEMR\Billing\EraPosting\ClaimPosting;
use OpenEMR\Billing\EraReversal;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\PaymentProcessing\Recorder;

final readonly class EngineCheckPoster implements CheckPoster
{
    /**
     * @param bool $addUnmatchedCodes the global add_unmatched_code_from_ins_co_era_to_billing
     */
    public function __construct(private bool $addUnmatchedCodes)
    {
    }

    public function payerFor(RemittanceCheck $check): string
    {
        $votes = [];
        foreach ($check->claims as $claim) {
            // A reversal's status does not say which level it was paid at.
            if ($claim->claimStatusCode === '22') {
                continue;
            }
            $key = [
                'our_claim_id' => $claim->ourClaimId,
                'patient_lname' => $claim->patientLastName,
                'patient_fname' => $claim->patientFirstName,
            ];
            [$pid, $encounter] = SLEOB::slInvoiceNumber($key);
            if (!is_numeric($pid) || (int) $pid <= 0 || !is_numeric($encounter) || (int) $encounter <= 0) {
                continue;
            }
            $serviceDate = ClaimPosting::parseDate($claim->serviceDate);
            if ($serviceDate === '') {
                $encounterDate = QueryUtils::fetchSingleValue(
                    'SELECT `date` FROM `form_encounter` WHERE `pid` = ? AND `encounter` = ?',
                    'date',
                    [(int) $pid, (int) $encounter]
                );
                $serviceDate = is_string($encounterDate) ? substr($encounterDate, 0, 10) : '';
            }
            $payer = SLEOB::arGetPayerID((int) $pid, $serviceDate, ClaimPosting::levelFor($claim->claimStatusCode));
            if (is_numeric($payer) && (int) $payer > 0) {
                $votes[(int) $payer] = ($votes[(int) $payer] ?? 0) + 1;
            }
        }
        if ($votes === []) {
            return '';
        }
        // Most claims first; among equals the lowest id, so a second look gives the same answer.
        ksort($votes);
        arsort($votes);
        return (string) array_key_first($votes);
    }

    public function postedForAnotherPayer(RemittanceCheck $check, string $payerId): bool
    {
        [$bare, $era] = EraCheckPosting::references($check->checkNumber);
        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS n FROM `ar_session` WHERE `reference` IN (?, ?) AND `payer_id` != ?'
            . " AND `payment_type` = 'insurance' AND (? = '' OR `check_date` = ?)",
            'n',
            [$bare, $era, $payerId, $check->checkDate, $check->checkDate]
        );
        return is_numeric($count) && (int) $count > 0;
    }

    public function post(RemittanceCheck $check, string $payerId, int $postUser, \DateTimeImmutable $today): CheckRecord
    {
        $run = new EraCheckPosting($payerId);
        $sessionId = $run->existingSession($check->checkNumber, $check->checkDate);
        if ($sessionId === 0) {
            // As SLEOB::arPostSession() creates it for the posting screen, with today as the
            // post-to and deposit dates the screen defaults to.
            $sessionId = QueryUtils::sqlInsert(
                'INSERT INTO `ar_session` (`payer_id`, `user_id`, `closed`, `reference`, `check_date`, `pay_total`,'
                . ' `post_to_date`, `deposit_date`, `patient_id`, `payment_type`, `adjustment_code`, `payment_method`)'
                . " VALUES (?, ?, 0, ?, ?, ?, ?, ?, 0, 'insurance', 'insurance_payment', 'electronic')",
                [
                    $payerId,
                    $postUser,
                    'ePay - ' . $check->checkNumber,
                    $check->checkDate,
                    $check->amount,
                    $today->format('Y-m-d'),
                    $today->format('Y-m-d'),
                ]
            );
        }
        $posting = new ClaimPosting(
            checkPosting: $run,
            reversal: new EraReversal($payerId),
            recorder: new Recorder(),
            postUser: $postUser,
            payerId: $payerId,
            withoutUpdate: false,
            payDate: '',
            addUnmatchedCodes: $this->addUnmatchedCodes,
        );
        $claims = [];
        foreach ($check->claims as $claim) {
            $claims[] = ClaimRecord::fromResult($claim, $posting->post($claim, $sessionId));
        }
        return new CheckRecord($check, CheckStatus::Posted, null, $payerId, $sessionId, $claims);
    }
}
