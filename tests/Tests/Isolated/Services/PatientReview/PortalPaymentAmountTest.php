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

        $this->assertSame(7550, PortalPaymentAmount::invoiceCents($invoice), 'form_bpay is not posted, so it is not counted');
        $this->assertTrue(PortalPaymentAmount::matchesInvoice('75.50', $invoice));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('84.50', $invoice), 'a charge that counts form_bpay');
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $invoice), 'a charge for a different amount');
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('999.00', $invoice), 'a prepayment field is ignored unless it is a prepayment');
    }

    public function testAPrepaymentTotalIsThePrepayment(): void
    {
        $invoice = json_encode([
            'radio_type_of_payment' => 'pre_payment',
            'form_prepayment' => ' 50.00 ',
            'form_upay[1687]' => '75.00',
        ], JSON_THROW_ON_ERROR);

        $this->assertSame(5000, PortalPaymentAmount::invoiceCents($invoice));
        $this->assertTrue(PortalPaymentAmount::matchesInvoice('50.00', $invoice));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $invoice));
    }

    public function testAPaymentTypeStaffCannotPostMatchesNothing(): void
    {
        $other = json_encode(['radio_type_of_payment' => 'other', 'form_upay[1687]' => '75.00'], JSON_THROW_ON_ERROR);
        $none = json_encode(['form_upay[1687]' => '75.00'], JSON_THROW_ON_ERROR);

        $this->assertNull(PortalPaymentAmount::invoiceCents($other));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $other));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('75.00', $none));
        foreach (['copay', 'cash', 'invoice_balance'] as $type) {
            $invoice = json_encode(['radio_type_of_payment' => $type, 'form_upay[1687]' => '75.00'], JSON_THROW_ON_ERROR);
            $this->assertTrue(PortalPaymentAmount::matchesInvoice('75.00', $invoice), $type);
        }
    }

    public function testAnUnusableSelectionMatchesNothing(): void
    {
        $badLine = json_encode(['radio_type_of_payment' => 'invoice_balance', 'form_upay[1]' => '20abc'], JSON_THROW_ON_ERROR);
        $badPrepay = json_encode(['radio_type_of_payment' => 'pre_payment', 'form_prepayment' => '1.005'], JSON_THROW_ON_ERROR);

        $this->assertNull(PortalPaymentAmount::invoiceCents('not json'));
        $this->assertNull(PortalPaymentAmount::invoiceCents($badLine));
        $this->assertNull(PortalPaymentAmount::invoiceCents($badPrepay));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('1.00', $badPrepay));
        $this->assertFalse(PortalPaymentAmount::matchesInvoice('0.00', '{}'), 'nothing to charge');
    }
}
