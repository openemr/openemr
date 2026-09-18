<?php

/**
 * Isolated tests for the Rainforest webhook amount-reconciliation invariant.
 *
 * The `RecordPayment` processor writes rows to `ar_session` / `ar_activity`
 * from a webhook whose metadata originally came from a client-supplied JSON
 * body at payin-config creation time. Rainforest signs the whole webhook
 * envelope, but that signature does not distinguish caller-chosen metadata
 * from server-derived metadata — it only proves the envelope was not tampered
 * with in transit. The only authoritative amount is the top-level `amount`
 * the gateway actually charged the card. These tests lock the invariant that
 * per-encounter metadata amounts must sum to the authorized amount before the
 * processor writes any AR rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PaymentProcessing\Rainforest\Webhooks;

use Money\Currency;
use Money\Money;
use OpenEMR\PaymentProcessing\Rainforest\EncounterData;
use OpenEMR\PaymentProcessing\Rainforest\Metadata;
use OpenEMR\PaymentProcessing\Rainforest\Webhooks\RecordPayment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RainforestWebhookAuthorityIsolatedTest extends TestCase
{
    private const USD = 'USD';

    // ------- Reconciliation invariant: match = accept -------

    public function testAcceptsWhenSingleEncounterMatchesAuthorizedAmount(): void
    {
        $metadata = new Metadata(
            patientId: '42',
            encounters: [
                $this->makeEncounter('e1', '5000'),
            ],
        );
        $authorized = new Money('5000', new Currency(self::USD));

        $this->assertTrue(
            RecordPayment::metadataMatchesAuthorizedAmount($metadata, $authorized),
        );
    }

    public function testAcceptsWhenMultipleEncountersSumToAuthorizedAmount(): void
    {
        $metadata = new Metadata(
            patientId: '42',
            encounters: [
                $this->makeEncounter('e1', '2500'),
                $this->makeEncounter('e2', '2500'),
            ],
        );
        $authorized = new Money('5000', new Currency(self::USD));

        $this->assertTrue(
            RecordPayment::metadataMatchesAuthorizedAmount($metadata, $authorized),
        );
    }

    // ------- Reconciliation invariant: mismatch = reject -------

    public function testRejectsWhenMetadataTotalExceedsAuthorizedAmount(): void
    {
        // A caller overstates the per-encounter amount to credit an
        // unowned encounter for more than the gateway actually charged.
        $metadata = new Metadata(
            patientId: '42',
            encounters: [
                $this->makeEncounter('e1', '10000'),
            ],
        );
        $authorized = new Money('5000', new Currency(self::USD));

        $this->assertFalse(
            RecordPayment::metadataMatchesAuthorizedAmount($metadata, $authorized),
            'Metadata declaring $100 credit for a $50 charge must be rejected',
        );
    }

    public function testRejectsWhenMetadataTotalIsLessThanAuthorizedAmount(): void
    {
        // Also-invalid: partial credit paths that would let the DB drift
        // out of sync with the gateway. Reject symmetrically.
        $metadata = new Metadata(
            patientId: '42',
            encounters: [
                $this->makeEncounter('e1', '2500'),
            ],
        );
        $authorized = new Money('5000', new Currency(self::USD));

        $this->assertFalse(
            RecordPayment::metadataMatchesAuthorizedAmount($metadata, $authorized),
        );
    }

    public function testRejectsWhenMetadataHasNoEncountersButAuthorizedAmountIsNonZero(): void
    {
        $metadata = new Metadata(
            patientId: '42',
            encounters: [],
        );
        $authorized = new Money('5000', new Currency(self::USD));

        $this->assertFalse(
            RecordPayment::metadataMatchesAuthorizedAmount($metadata, $authorized),
        );
    }

    public function testRejectsWhenOneOfManyEncountersIsInflated(): void
    {
        // A single encounter having an inflated amount is enough to
        // reject the whole webhook — even if the other encounters look
        // legitimate.
        $metadata = new Metadata(
            patientId: '42',
            encounters: [
                $this->makeEncounter('e1', '2500'),
                $this->makeEncounter('e2', '10000'),
                $this->makeEncounter('e3', '2500'),
            ],
        );
        $authorized = new Money('5000', new Currency(self::USD));

        $this->assertFalse(
            RecordPayment::metadataMatchesAuthorizedAmount($metadata, $authorized),
        );
    }

    public function testRejectsByOneCent(): void
    {
        // Money::equals is exact — one cent off should fail. Locks that
        // we're not tolerating a fuzzy comparison.
        $metadata = new Metadata(
            patientId: '42',
            encounters: [
                $this->makeEncounter('e1', '5001'),
            ],
        );
        $authorized = new Money('5000', new Currency(self::USD));

        $this->assertFalse(
            RecordPayment::metadataMatchesAuthorizedAmount($metadata, $authorized),
        );
    }

    // ------- Data provider driven parametric check -------

    /**
     * @param list<numeric-string> $encounterAmounts
     * @param numeric-string $authorized
     */
    #[DataProvider('reconciliationScenarios')]
    public function testReconciliationParametric(
        array $encounterAmounts,
        string $authorized,
        bool $expected,
    ): void {
        $metadata = new Metadata(
            patientId: '42',
            encounters: array_map(
                fn(string $amt, int $i): EncounterData => $this->makeEncounter('enc_' . $i, $amt),
                $encounterAmounts,
                array_keys($encounterAmounts),
            ),
        );
        $authorizedMoney = new Money($authorized, new Currency(self::USD));

        $this->assertSame(
            $expected,
            RecordPayment::metadataMatchesAuthorizedAmount($metadata, $authorizedMoney),
        );
    }

    /**
     * @return array<string, array{list<numeric-string>, numeric-string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function reconciliationScenarios(): array
    {
        return [
            'zero-encounters-zero-authorized' => [[], '0', true],
            'single-exact-match' => [['1000'], '1000', true],
            'single-inflated' => [['2000'], '1000', false],
            'single-deflated' => [['500'], '1000', false],
            'two-encounters-sum-match' => [['500', '500'], '1000', true],
            'two-encounters-inflated' => [['500', '600'], '1000', false],
            'many-encounters-exact' => [['100', '200', '300', '400'], '1000', true],
            'many-encounters-drift-cent' => [['100', '200', '300', '401'], '1000', false],
        ];
    }

    // ------- Helpers -------

    /**
     * @param numeric-string $amount
     */
    private function makeEncounter(string $id, string $amount): EncounterData
    {
        return new EncounterData(
            id: $id,
            code: '99213',
            codeType: 'CPT4',
            amount: new Money($amount, new Currency(self::USD)),
        );
    }
}
