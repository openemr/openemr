<?php

/**
 * The portal card payment amount: a charge must equal the invoice selection it is saved with.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use OpenEMR\Services\PatientReview\Portal\PortalPaymentAmount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PortalPaymentAmountTest extends TestCase
{
    /** The invoice lines (encounters) the patient was offered. */
    private const LINES = [1, 1687, 1688, 1690];

    /**
     * @return array<string, array{string, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function amountProvider(): array
    {
        return [
            'whole' => ['50', 5000],
            'one decimal' => ['50.5', 5050],
            'two decimals' => ['50.05', 5005],
            'zero' => ['0.00', 0],
            'three decimals' => ['1.005', null],
            'comma' => ['50,00', null],
            'negative' => ['-5.00', null],
            'text' => ['20abc', null],
            'empty' => ['', null],
            'trailing dot' => ['50.', null],
            'trailing newline' => ["50.00\n", null],
            'cents only' => ['.50', 50],
            'one cent digit only' => ['.5', 50],
            'point only' => ['.', null],
            'three cent digits only' => ['.505', null],
            'more dollars than integer cents hold' => ['999999999999999999999999', null],
            'sixteen dollar digits' => ['1000000000000000', null],
            'fifteen dollar digits' => ['999999999999999.99', 99999999999999999],
            'leading zeros do not count' => ['0000000000000000001.00', 100],
        ];
    }

    #[DataProvider('amountProvider')]
    public function testCents(string $amount, ?int $cents): void
    {
        $this->assertSame($cents, PortalPaymentAmount::cents($amount));
    }

    public function testAnInvoiceTotalIsTheSumOfItsLines(): void
    {
        $invoice = json_encode([
            'radio_type_of_payment' => 'invoice_balance',
            'form_prepayment' => '999.00',
            'form_upay[1687]' => '75.00',
            'form_upay[1688]' => '',
            'form_upay[1690]' => '0.5',
            'form_bpay[12]' => '9.00',
            'form_paytotal' => '75.50',
        ], JSON_THROW_ON_ERROR);

        $this->assertSame(7550, PortalPaymentAmount::invoiceCents($invoice, self::LINES), 'form_bpay is not posted, so it is not counted');
        $this->assertTrue(PortalPaymentAmount::matchesInvoice('75.50', $invoice, self::LINES));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('84.50', $invoice, self::LINES), 'a charge that counts form_bpay');
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $invoice, self::LINES), 'a charge for a different amount');
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('999.00', $invoice, self::LINES), 'a prepayment field is ignored unless it is a prepayment');
    }

    public function testAPrepaymentTotalIsThePrepayment(): void
    {
        $invoice = json_encode([
            'radio_type_of_payment' => 'pre_payment',
            'form_prepayment' => ' 50.00 ',
            'form_upay[1687]' => '75.00',
        ], JSON_THROW_ON_ERROR);

        $this->assertSame(5000, PortalPaymentAmount::invoiceCents($invoice, self::LINES));
        $this->assertTrue(PortalPaymentAmount::matchesInvoice('50.00', $invoice, self::LINES));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $invoice, self::LINES));
    }

    public function testAPaymentTypeStaffCannotPostMatchesNothing(): void
    {
        $other = json_encode(['radio_type_of_payment' => 'other', 'form_upay[1687]' => '75.00'], JSON_THROW_ON_ERROR);
        $none = json_encode(['form_upay[1687]' => '75.00'], JSON_THROW_ON_ERROR);

        $this->assertNull(PortalPaymentAmount::invoiceCents($other, self::LINES));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $other, self::LINES));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $none, self::LINES));
        foreach (['copay', 'cash', 'invoice_balance'] as $type) {
            $invoice = json_encode(['radio_type_of_payment' => $type, 'form_upay[1687]' => '75.00'], JSON_THROW_ON_ERROR);
            $this->assertTrue(PortalPaymentAmount::matchesInvoice('75.00', $invoice, self::LINES), $type);
        }
    }

    /**
     * A line the patient was not offered would be charged but dropped when staff post, so the
     * selection matches nothing.
     */
    public function testALineThePatientWasNotOfferedMatchesNothing(): void
    {
        foreach (['form_upay[9999]', 'form_upay[not-a-line]', 'form_upay[]', 'form_upay[1687][x]', 'form_upay[01688]', 'form_upay[+1688]', 'form_upay[ 1688]', "form_upay[1688]\n", 'form_upay[99999999999999999999]', 'form_upay[9223372036854775807]', 'form_upay[0]'] as $extra) {
            $invoice = json_encode(['radio_type_of_payment' => 'invoice_balance', 'form_upay[1687]' => '75.00', $extra => '25.00'], JSON_THROW_ON_ERROR);
            $this->assertNull(PortalPaymentAmount::invoiceCents($invoice, self::LINES), $extra);
            $this->assertFalse(PortalPaymentAmount::matchesInvoice('100.00', $invoice, self::LINES), $extra);
        }
        $invoice = json_encode(['radio_type_of_payment' => 'invoice_balance', 'form_upay[1687]' => '75.00'], JSON_THROW_ON_ERROR);
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $invoice, []), 'another patient has none of these lines');
    }

    /**
     * Encounter 0, when the patient has one, is a line like any other.
     */
    public function testAnEncounterZeroLineIsALine(): void
    {
        $invoice = json_encode(['radio_type_of_payment' => 'invoice_balance', 'form_upay[0]' => '5.00', 'form_upay[1687]' => '75.00'], JSON_THROW_ON_ERROR);

        $this->assertSame(8000, PortalPaymentAmount::invoiceCents($invoice, [0, 1687]));
    }

    /**
     * A negative line is not an amount, so it cannot lower the total charged.
     */
    public function testANegativeLineMatchesNothing(): void
    {
        $invoice = json_encode(['radio_type_of_payment' => 'invoice_balance', 'form_upay[1687]' => '100.00', 'form_upay[1688]' => '-50.00'], JSON_THROW_ON_ERROR);

        $this->assertNull(PortalPaymentAmount::invoiceCents($invoice, self::LINES));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('50.00', $invoice, self::LINES));
    }

    public function testAnUnusableSelectionMatchesNothing(): void
    {
        $badLine = json_encode(['radio_type_of_payment' => 'invoice_balance', 'form_upay[1]' => '20abc'], JSON_THROW_ON_ERROR);
        $badPrepay = json_encode(['radio_type_of_payment' => 'pre_payment', 'form_prepayment' => '1.005'], JSON_THROW_ON_ERROR);

        $this->assertNull(PortalPaymentAmount::invoiceCents('not json', self::LINES));
        $this->assertNull(PortalPaymentAmount::invoiceCents($badLine, self::LINES));
        $this->assertNull(PortalPaymentAmount::invoiceCents($badPrepay, self::LINES));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('1.00', $badPrepay, self::LINES));
        $centsOnlyPrepay = json_encode(['radio_type_of_payment' => 'pre_payment', 'form_prepayment' => '.50'], JSON_THROW_ON_ERROR);
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('0.50', $centsOnlyPrepay, self::LINES), 'staff posting refuses ".50" as a prepayment');
        $centsOnlyLine = json_encode(['radio_type_of_payment' => 'invoice_balance', 'form_upay[1687]' => '.50'], JSON_THROW_ON_ERROR);
        $this->assertTrue(PortalPaymentAmount::matchesInvoice('0.50', $centsOnlyLine, self::LINES), 'but accepts it on a line');
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('0.00', '{}', self::LINES), 'nothing to charge');
    }
}
