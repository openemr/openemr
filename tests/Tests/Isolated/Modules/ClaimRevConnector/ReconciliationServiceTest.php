<?php

/**
 * Isolated tests for ReconciliationService::computeDiscrepancy().
 *
 * The reconcile() entry point is database- and API-bound, but the
 * discrepancy classifier itself is now a pure function: it takes the
 * already-merged encounter row plus a pre-computed "OE has payments?"
 * boolean and returns a description + level. These tests pin down the
 * truth-oracle the reviewer flagged as missing — what counts as a real
 * mismatch and what doesn't — so any future change to the rules has to
 * update an explicit assertion rather than silently shift behavior.
 *
 * The reviewer also asked to pin down two scenarios specifically:
 *
 *   - False negative (real discrepancy missed): OE encounter shows
 *     billed=$100 paid=$80 adj=$20 balance=$0; ClaimRev reports
 *     paid=$80 adj=$10 w/o=$10 balance=$0. The current classifier does
 *     not look at adjustment composition, so it returns "no
 *     discrepancy". testCompositionMismatchIsCurrentlyNotDetected()
 *     pins that gap down so a future caller can tighten the rule
 *     without quietly regressing the existing rules.
 *   - False positive (timezone-naive freshness check): the current
 *     classifier doesn't consult an updated_at vs snapshot_at filter,
 *     so the timezone-edge scenario produces no false positive today.
 *     testTimezoneEdgeProducesNoFalsePositive() asserts that.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\ReconciliationService;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/TypeCoerce.php';
// ReconciliationService imports ClaimRevApi/ClaimSearchModel/etc., but those
// are only used in reconcile() / lookupClaimRev() — computeDiscrepancy() is
// self-contained. The classes still need to *exist* for the file to load.
require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/ClaimRevException.php';
require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/ReconciliationService.php';

/**
 * @phpstan-import-type ReconcileRow from ReconciliationService
 */
class ReconciliationServiceTest extends TestCase
{
    /**
     * Build a ReconcileRow with sensible defaults, then merge any caller
     * overrides on top. Tests only set the fields they care about.
     *
     * @param array<string, mixed> $overrides
     * @return ReconcileRow
     */
    private static function row(array $overrides = []): array
    {
        $base = [
            'pid' => 1,
            'encounter' => 1,
            'pcn' => '1-1',
            'encounterDate' => '2026-01-15',
            'patientName' => 'Doe, Jane',
            'patientDob' => '1980-01-01',
            'payerName' => 'Acme Health',
            'payerNumber' => '12345',
            'totalCharges' => 100.00,
            'billTime' => '2026-01-16 10:00:00',
            'oeStatus' => 2, // Billed
            'oeStatusLabel' => 'Billed',
            'oeProcessFile' => '',
            'crFound' => false,
            'crStatusName' => '',
            'crStatusId' => 0,
            'crPayerAcceptance' => '',
            'crPayerAcceptanceStatusId' => 0,
            'crEraClassification' => '',
            'crPayerPaidAmount' => 0.0,
            'crObjectId' => '',
            'crIsWorked' => false,
            'discrepancy' => '',
            'discrepancyLevel' => '',
        ];
        /** @var ReconcileRow $row */
        $row = array_merge($base, $overrides);
        return $row;
    }

    public function testBilledInOpenEmrButNotFoundInClaimRevIsDanger(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row(['oeStatus' => 2, 'crFound' => false]),
            oeHasPayments: false,
        );
        $this->assertSame('Billed in OpenEMR but not found in ClaimRev', $verdict['description']);
        $this->assertSame('danger', $verdict['level']);
    }

    public function testNotFoundInClaimRevAndNotBilledIsNoDiscrepancy(): void
    {
        // status 1 = Unbilled, not in ClaimRev — that's the normal pre-bill state.
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row(['oeStatus' => 1, 'crFound' => false]),
            oeHasPayments: false,
        );
        $this->assertSame('', $verdict['description']);
        $this->assertSame('', $verdict['level']);
    }

    /**
     * crStatusId in {10, 16, 17} OR crPayerAcceptanceStatusId === 3 means
     * ClaimRev marked the claim rejected; if OE still shows status=2 (Billed),
     * the operator is unaware and needs to act.
     */
    public function testRejectedInClaimRevButStillBilledInOpenEmrIsDanger(): void
    {
        foreach ([10, 16, 17] as $rejectStatus) {
            $verdict = ReconciliationService::computeDiscrepancy(
                self::row(['oeStatus' => 2, 'crFound' => true, 'crStatusId' => $rejectStatus]),
                oeHasPayments: false,
            );
            $this->assertSame('Rejected in ClaimRev but still Billed in OpenEMR', $verdict['description']);
            $this->assertSame('danger', $verdict['level']);
        }
    }

    public function testPayerAcceptanceRejectedAlsoTriggersDanger(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row([
                'oeStatus' => 2,
                'crFound' => true,
                'crStatusId' => 0,
                'crPayerAcceptanceStatusId' => 3,
            ]),
            oeHasPayments: false,
        );
        $this->assertSame('Rejected in ClaimRev but still Billed in OpenEMR', $verdict['description']);
    }

    public function testDeniedInOpenEmrButAcceptedInClaimRevIsWarning(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row([
                'oeStatus' => 7, // Denied
                'crFound' => true,
                'crPayerAcceptanceStatusId' => 4, // Accepted
            ]),
            oeHasPayments: false,
        );
        $this->assertSame('Denied in OpenEMR but Accepted in ClaimRev', $verdict['description']);
        $this->assertSame('warning', $verdict['level']);
    }

    public function testEraPaidWithoutPostingIsWarning(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row([
                'oeStatus' => 2,
                'crFound' => true,
                'crEraClassification' => 'Paid',
            ]),
            oeHasPayments: false,
        );
        $this->assertSame('ERA shows paid but no payment posted in OpenEMR', $verdict['description']);
        $this->assertSame('warning', $verdict['level']);
    }

    public function testEraPaidWithPostingIsResolved(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row([
                'oeStatus' => 2,
                'crFound' => true,
                'crEraClassification' => 'Paid',
            ]),
            oeHasPayments: true,
        );
        $this->assertSame('', $verdict['description']);
        $this->assertSame('', $verdict['level']);
    }

    public function testEraDeniedButOpenEmrNotMarkedDeniedIsWarning(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row([
                'oeStatus' => 2, // not 7 (Denied)
                'crFound' => true,
                'crEraClassification' => 'Denied',
            ]),
            oeHasPayments: false,
        );
        $this->assertSame('ERA shows denied but OpenEMR not marked as denied', $verdict['description']);
        $this->assertSame('warning', $verdict['level']);
    }

    public function testEraDeniedAndOpenEmrAlsoDeniedIsResolved(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row([
                'oeStatus' => 7, // Denied
                'crFound' => true,
                'crEraClassification' => 'Denied',
            ]),
            oeHasPayments: false,
        );
        $this->assertSame('', $verdict['description']);
    }

    /**
     * The reviewer's "false negative" example: OE has billed=$100,
     * paid=$80, adj=$20, balance=$0. ClaimRev reports paid=$80,
     * adj=$10, w/o=$10, balance=$0. Net balances tie; adjustment
     * composition differs. A balance-only check would miss it.
     *
     * The current classifier does not inspect adjustment composition
     * at all — it returns "no discrepancy". This test pins that gap
     * down so the team has to make an explicit decision before
     * tightening (or accepting) the rule.
     */
    public function testCompositionMismatchIsCurrentlyNotDetected(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row([
                'oeStatus' => 2,
                'totalCharges' => 100.00,
                'crFound' => true,
                'crEraClassification' => '', // not "paid" or "denied"
                'crPayerPaidAmount' => 80.00,
                // crPayerAcceptanceStatusId left at default 0 (not rejected,
                // not accepted/4 in the OE-denied branch), so none of the
                // existing rules fire.
            ]),
            oeHasPayments: true, // assume operator did post; net balances tie
        );
        $this->assertSame(
            '',
            $verdict['description'],
            'Composition-mismatch detection is a known gap; if this assertion '
            . 'starts failing, update both the classifier and this test.'
        );
    }

    /**
     * The reviewer's "false positive" example: OE row updated at 23:59:59
     * local, ClaimRev snapshot at 00:00:01 UTC the next day. A timezone-naive
     * `updated_at > snapshot_at` filter would flag this as out-of-sync.
     *
     * The current classifier doesn't consult any time-based freshness signal,
     * so the scenario produces no discrepancy at all. This test pins that
     * down: if a freshness check is added later, it must not regress this
     * specific case.
     */
    public function testTimezoneEdgeProducesNoFalsePositive(): void
    {
        $verdict = ReconciliationService::computeDiscrepancy(
            self::row([
                'oeStatus' => 2,
                'billTime' => '2026-04-30 23:59:59', // local
                'crFound' => true,
                'crEraClassification' => '', // no paid/denied signal
                // billTime / snapshot timestamps are not consulted by the
                // current classifier; if that ever changes, the assertion
                // below should keep failing on this exact case.
            ]),
            oeHasPayments: true,
        );
        $this->assertSame('', $verdict['description']);
    }
}
