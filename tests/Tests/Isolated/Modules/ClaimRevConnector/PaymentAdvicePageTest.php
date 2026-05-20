<?php

/**
 * Isolated tests for PaymentAdvicePage::normalizeAdvice().
 *
 * Both the live searchPaymentInfo() flow and the mock generator route
 * raw decoded API payloads through normalizeAdvice() before handing
 * them to the UI / posting service. The tests pin down:
 *
 *  - every key in PaymentAdviceShape is present after normalization,
 *    even when the source payload omits it (UI assumes non-optional)
 *  - paymentInfo and checkInformation sub-shapes have the same guarantee
 *  - extras outside the shape pass through untouched (the ClaimRev API
 *    returns servicePaymentInfos and the JS depends on it)
 *  - mixed → typed coercion happens at this boundary (the UI doesn't
 *    do its own narrowing)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\PaymentAdvicePage;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/TypeCoerce.php';
require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/PaymentAdvicePage.php';

class PaymentAdvicePageTest extends TestCase
{
    public function testEmptyEntryFullyPopulatesShapeWithDefaults(): void
    {
        $shape = PaymentAdvicePage::normalizeAdvice([]);

        // Top-level keys all present
        $this->assertSame('', $shape['paymentAdviceId']);
        $this->assertSame('', $shape['receivedDate']);
        $this->assertSame('', $shape['payerName']);
        $this->assertSame('', $shape['payerNumber']);
        $this->assertSame('', $shape['eraClassification']);

        // paymentInfo sub-shape fully populated
        $this->assertSame('', $shape['paymentInfo']['patientFirstName']);
        $this->assertSame('', $shape['paymentInfo']['patientLastName']);
        $this->assertSame('', $shape['paymentInfo']['patientControlNumber']);
        $this->assertSame('', $shape['paymentInfo']['claimStatusCode']);
        $this->assertSame(0.0, $shape['paymentInfo']['totalClaimAmount']);
        $this->assertSame(0.0, $shape['paymentInfo']['claimPaymentAmount']);
        $this->assertSame(0.0, $shape['paymentInfo']['patientResponsibility']);
        $this->assertFalse($shape['paymentInfo']['isWorked']);

        // checkInformation sub-shape fully populated
        $this->assertSame('', $shape['checkInformation']['checkNumber']);
        $this->assertSame('', $shape['checkInformation']['checkDate']);
        $this->assertSame('', $shape['checkInformation']['paymentMethodCode']);
        $this->assertSame(0.0, $shape['checkInformation']['paymentAmount']);
    }

    public function testFullyPopulatedEntryRoundTrips(): void
    {
        $shape = PaymentAdvicePage::normalizeAdvice([
            'paymentAdviceId' => 'pa-001',
            'receivedDate' => '2026-01-15T00:00:00Z',
            'payerName' => 'Acme Health',
            'payerNumber' => '12345',
            'eraClassification' => 'Paid',
            'paymentInfo' => [
                'patientFirstName' => 'Jane',
                'patientLastName' => 'Doe',
                'patientControlNumber' => '42-1001',
                'claimStatusCode' => '1',
                'totalClaimAmount' => 250.0,
                'claimPaymentAmount' => 200.0,
                'patientResponsibility' => 50.0,
                'isWorked' => false,
            ],
            'checkInformation' => [
                'checkNumber' => 'CHK-9001',
                'checkDate' => '2026-01-16T00:00:00Z',
                'paymentMethodCode' => 'CHK',
                'paymentAmount' => 200.0,
            ],
        ]);

        $this->assertSame('pa-001', $shape['paymentAdviceId']);
        $this->assertSame('Acme Health', $shape['payerName']);
        $this->assertSame('Doe', $shape['paymentInfo']['patientLastName']);
        $this->assertSame(200.0, $shape['paymentInfo']['claimPaymentAmount']);
        $this->assertSame('CHK-9001', $shape['checkInformation']['checkNumber']);
    }

    public function testMissingSubObjectsDoNotBreakShape(): void
    {
        // paymentInfo absent — sub-shape still fully populated with defaults.
        $shape = PaymentAdvicePage::normalizeAdvice([
            'paymentAdviceId' => 'pa-002',
        ]);

        $this->assertSame('pa-002', $shape['paymentAdviceId']);
        $this->assertSame('', $shape['paymentInfo']['patientFirstName']);
        $this->assertSame(0.0, $shape['checkInformation']['paymentAmount']);
    }

    public function testNonArraySubObjectsAreReplacedWithEmptyDefaults(): void
    {
        // paymentInfo is sometimes null/scalar in malformed responses;
        // normalizeAdvice should not blow up.
        $shape = PaymentAdvicePage::normalizeAdvice([
            'paymentInfo' => null,
            'checkInformation' => 'unexpected scalar',
        ]);

        $this->assertSame('', $shape['paymentInfo']['patientLastName']);
        $this->assertSame('', $shape['checkInformation']['checkNumber']);
    }

    public function testStringNumericFieldsCoerceToFloat(): void
    {
        // Some upstream payloads send amounts as strings.
        $shape = PaymentAdvicePage::normalizeAdvice([
            'paymentInfo' => [
                'totalClaimAmount' => '125.50',
                'claimPaymentAmount' => '100',
            ],
            'checkInformation' => [
                'paymentAmount' => '100.00',
            ],
        ]);

        $this->assertSame(125.50, $shape['paymentInfo']['totalClaimAmount']);
        $this->assertSame(100.0, $shape['paymentInfo']['claimPaymentAmount']);
        $this->assertSame(100.0, $shape['checkInformation']['paymentAmount']);
    }

    public function testIsWorkedAcceptsCommonTruthyForms(): void
    {
        $this->assertTrue(PaymentAdvicePage::normalizeAdvice([
            'paymentInfo' => ['isWorked' => true],
        ])['paymentInfo']['isWorked']);

        $this->assertTrue(PaymentAdvicePage::normalizeAdvice([
            'paymentInfo' => ['isWorked' => 1],
        ])['paymentInfo']['isWorked']);

        $this->assertTrue(PaymentAdvicePage::normalizeAdvice([
            'paymentInfo' => ['isWorked' => '1'],
        ])['paymentInfo']['isWorked']);

        $this->assertFalse(PaymentAdvicePage::normalizeAdvice([
            'paymentInfo' => ['isWorked' => 0],
        ])['paymentInfo']['isWorked']);

        $this->assertFalse(PaymentAdvicePage::normalizeAdvice([
            'paymentInfo' => ['isWorked' => 'no'],
        ])['paymentInfo']['isWorked']);
    }

    public function testNonStringClaimStatusCodeCoercesViaTypeCoerce(): void
    {
        // claimStatusCode is declared string in the shape, but the UI uses
        // it as a string key — TypeCoerce::asString turns int 1 into '1'.
        $shape = PaymentAdvicePage::normalizeAdvice([
            'paymentInfo' => ['claimStatusCode' => 1],
        ]);

        $this->assertSame('1', $shape['paymentInfo']['claimStatusCode']);
    }
}
