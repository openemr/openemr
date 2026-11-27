<?php

/**
 * Rows for the ERA worklist page's render tests: every label the template has.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Billing;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraAutoPost\ClaimOutcome;
use OpenEMR\Billing\EraAutoPost\HoldReason;
use OpenEMR\Billing\EraWorklist\HeldCheck;
use OpenEMR\Billing\EraWorklist\OpenCounts;
use OpenEMR\Billing\EraWorklist\PostedCheck;
use OpenEMR\Billing\EraWorklist\PostedClaim;
use OpenEMR\Billing\EraWorklist\PostedLine;
use OpenEMR\Billing\EraWorklist\ReceivedFile;
use OpenEMR\Billing\EraWorklist\ReviewClaim;
use OpenEMR\Billing\EraWorklist\WorkedMark;
use OpenEMR\Billing\EraWorklist\WorklistFilter;

final class EraWorklistRows
{
    /**
     * What the controller hands the template, for one view.
     *
     * @param list<object>          $rows
     * @param array<string, string> $shown the view's filter, as the query string carries it
     * @return array<string, mixed>
     */
    public static function page(string $view, array $rows, ?int $total = null, array $shown = [], int $review = 0, int $files = 0): array
    {
        $shown = ['view' => $view, ...$shown];
        return [
            'view' => $view,
            'shown' => $shown,
            'selfQuery' => http_build_query($shown),
            'rows' => $rows,
            'total' => $total ?? count($rows),
            'limit' => 500,
            'counts' => new OpenCounts($review, $files),
            'payers' => $view === 'files' ? [] : ['ACME HEALTH', 'PLAN <B>'],
            'reasons' => WorklistFilter::reviewReasons(),
            'today' => '2026-11-27',
            'csrfToken' => 'test-csrf-token',
        ];
    }

    /**
     * One claim payment per reason and claim status; the last is marked worked, and one was
     * not found.
     *
     * @return list<ReviewClaim>
     */
    public static function reviewClaims(): array
    {
        $claims = [];
        $cases = [
            ['denied', '4'],
            ['claim_not_found', '1'],
            ['reversal', '22'],
            ['charge_mismatch,lines_pended', '2'],
            ['code_not_on_invoice', '3'],
            ['unpaid_adjustment', '19'],
            ['a_reason_from_a_later_version', '20'],
            ['denied', '21'],
            ['denied', '23'],
            ['denied', '99'],
        ];
        foreach ($cases as $index => [$reasons, $status]) {
            $found = $reasons !== 'claim_not_found';
            $claims[] = new ReviewClaim(
                id: 31 + $index,
                checkNumber: 'EFT5001',
                checkDate: $index === 0 ? '' : '2026-10-20',
                payerName: 'PLAN <B>',
                processedAt: new \DateTimeImmutable('2026-11-26 02:15:00'),
                patientName: 'ANNE "A" O\'POSTING',
                pid: $found ? 9101 : 0,
                encounter: $found ? 9201 + $index : 0,
                // An encounter that has since been deleted has no invoice to open.
                invoiceId: $found && $index !== 2 ? 11 + $index : 0,
                ourClaimId: '9101-' . (9201 + $index),
                payerClaimId: 'ICN<' . $index . '>',
                payerLevel: 1,
                claimStatusCode: $status,
                reasons: explode(',', $reasons),
                claimPayment: 80.0,
                paymentsPosted: $index === 3 ? 45.0 : 0.0,
                adjustmentsPosted: $index === 3 ? 20.5 : 0.0,
                worked: $index === 9 ? new WorkedMark('Billie <Biller>', new \DateTimeImmutable('2026-11-27 09:30:00')) : null,
            );
        }
        return $claims;
    }

    /**
     * A check with a claim payment of each outcome and a provider adjustment, and one that
     * posted in full with no check date.
     *
     * @return list<PostedCheck>
     */
    public static function postedChecks(): array
    {
        $lines = [
            new PostedLine('99213', '25', 45.0, 0.0, ''),
            new PostedLine('99213', '', 0.0, 55.0, 'Adjust code <45>'),
            new PostedLine('', '', 0.0, 1.25, 'Ins1 interest'),
        ];
        return [
            new PostedCheck(21, 'EFT5001', '2026-10-20', 'PLAN <B>', 115.0, 125.0, 10.0, 'WO,L6', 301, new \DateTimeImmutable('2026-11-26 02:15:00'), '20261020_41_60054.edi', [
                self::postedClaim('ANNE "A" O\'POSTING', ClaimOutcome::Posted, [], 45.0, 45.0, 56.25, $lines),
                self::postedClaim('BOB <DENIED>', ClaimOutcome::Review, ['denied', 'lines_pended'], 0.0, 0.0, 0.0, []),
                self::postedClaim('CARA NOTFOUND', ClaimOutcome::Review, ['claim_not_found'], 80.0, 0.0, 0.0, [], pid: 0),
                self::postedClaim('EVE SKIPPED', ClaimOutcome::Skipped, ['already_posted'], 0.0, 0.0, 0.0, []),
            ]),
            new PostedCheck(22, 'EFT5002', '', 'ACME HEALTH', 45.0, 45.0, 0.0, '', 302, new \DateTimeImmutable('2026-11-25 02:15:00'), '20261019_40_60054.edi', [
                self::postedClaim('DAN PAID', ClaimOutcome::Posted, [], 45.0, 45.0, 0.0, []),
            ]),
        ];
    }

    /**
     * One file per state unattended posting can leave it in, one holding a check for each
     * hold reason, and one marked worked.
     *
     * @return list<ReceivedFile>
     */
    public static function receivedFiles(): array
    {
        return [
            self::file(41, null),
            self::file(42, AutoPostFileStatus::Pending),
            self::file(43, AutoPostFileStatus::Running),
            self::file(44, AutoPostFileStatus::Unreadable),
            self::file(45, AutoPostFileStatus::Interrupted, [self::held(HoldReason::NoPayer, '')]),
            self::file(46, AutoPostFileStatus::Done, [self::held(HoldReason::OutOfBalance), self::held(HoldReason::PostedForAnotherPayer), self::held(null)]),
            self::file(47, null, worked: new WorkedMark('Billie <Biller>', new \DateTimeImmutable('2026-11-27 09:30:00'))),
        ];
    }

    /**
     * @param list<string>     $reasons
     * @param list<PostedLine> $lines
     */
    private static function postedClaim(
        string $name,
        ClaimOutcome $outcome,
        array $reasons,
        float $paid,
        float $posted,
        float $adjusted,
        array $lines,
        int $pid = 9101,
    ): PostedClaim {
        return new PostedClaim(
            patientName: $name,
            pid: $pid,
            encounter: $pid > 0 ? 9201 : 0,
            invoiceId: $pid > 0 ? 11 : 0,
            payerClaimId: 'ICN<1>',
            payerLevel: 1,
            claimStatusCode: '1',
            outcome: $outcome,
            reasons: $reasons,
            claimPayment: $paid,
            paymentsPosted: $posted,
            adjustmentsPosted: $adjusted,
            lines: $lines,
        );
    }

    private static function held(?HoldReason $reason, string $date = '2026-10-20'): HeldCheck
    {
        return new HeldCheck('EFT<9>', $date, 'PLAN <B>', 200.0, 150.0, 10.0, $reason, 2);
    }

    /**
     * @param list<HeldCheck> $heldChecks
     */
    private static function file(int $id, ?AutoPostFileStatus $status, array $heldChecks = [], ?WorkedMark $worked = null): ReceivedFile
    {
        return new ReceivedFile(
            id: $id,
            partnerName: 'Demo <Clearinghouse>',
            remoteName: 'REMIT<' . $id . '>.RMT',
            storedName: '20261020_' . $id . '_60054.edi',
            receivedAt: new \DateTimeImmutable('2026-11-26 03:00:00'),
            autoPostStatus: $status,
            heldChecks: $heldChecks,
            worked: $worked,
        );
    }
}
