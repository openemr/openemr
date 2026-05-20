<?php

/**
 * Isolated tests for the ClaimSearchResult DTO factory.
 *
 * fromApi() is the single boundary where a raw decoded API result is
 * coerced into the typed DTO that templates and services consume. The
 * factory accepts both associative arrays (json_decode assoc=true) and
 * stdClass objects (json_decode default), and silently ignores unknown
 * shapes — these tests pin down each input mode plus per-field type
 * coercion.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\Dto\ClaimSearchResult;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Dto/ClaimSearchResult.php';

class ClaimSearchResultTest extends TestCase
{
    public function testFromApiAcceptsAssociativeArray(): void
    {
        $result = ClaimSearchResult::fromApi([
            'statusName' => 'Accepted',
            'statusId' => 7,
            'patientControlNumber' => '42-1001',
            'isWorked' => true,
            'billedAmount' => 250.75,
        ]);

        $this->assertSame('Accepted', $result->statusName);
        $this->assertSame(7, $result->statusId);
        $this->assertSame('42-1001', $result->patientControlNumber);
        $this->assertTrue($result->isWorked);
        $this->assertSame(250.75, $result->billedAmount);
    }

    public function testFromApiAcceptsStdClass(): void
    {
        $obj = new \stdClass();
        $obj->statusName = 'Rejected';
        $obj->statusId = 10;
        $obj->payerName = 'Acme';
        $obj->payerPaidAmount = 0.0;

        $result = ClaimSearchResult::fromApi($obj);

        $this->assertSame('Rejected', $result->statusName);
        $this->assertSame(10, $result->statusId);
        $this->assertSame('Acme', $result->payerName);
        $this->assertSame(0.0, $result->payerPaidAmount);
    }

    public function testFromApiUnknownShapeReturnsAllNull(): void
    {
        // json_decode of a numeric/string root produces neither array nor object.
        $result = ClaimSearchResult::fromApi('not-an-object');

        $this->assertNull($result->statusName);
        $this->assertNull($result->statusId);
        $this->assertNull($result->patientControlNumber);
        $this->assertNull($result->isWorked);
        $this->assertNull($result->billedAmount);
    }

    public function testFromApiNullReturnsAllNull(): void
    {
        $result = ClaimSearchResult::fromApi(null);

        $this->assertNull($result->statusName);
        $this->assertNull($result->statusId);
    }

    public function testFromApiEmptyArrayReturnsAllNull(): void
    {
        $result = ClaimSearchResult::fromApi([]);

        $this->assertNull($result->statusName);
        $this->assertNull($result->payerName);
        $this->assertNull($result->billedAmount);
    }

    public function testStringIntCoerces(): void
    {
        // Some upstream payloads send numeric IDs as strings.
        $result = ClaimSearchResult::fromApi([
            'statusId' => '42',
            'objectId' => '12345',
            'errorCount' => '3',
        ]);

        $this->assertSame(42, $result->statusId);
        $this->assertSame(12345, $result->objectId);
        $this->assertSame(3, $result->errorCount);
    }

    public function testNonNumericIntFieldReturnsNull(): void
    {
        // 'abc' must not silently coerce to 0 — that would mask data drift.
        $result = ClaimSearchResult::fromApi([
            'statusId' => 'not-a-number',
            'objectId' => null,
        ]);

        $this->assertNull($result->statusId);
        $this->assertNull($result->objectId);
    }

    public function testFloatFieldAcceptsIntegerInput(): void
    {
        // A whole-dollar payment may arrive as an int.
        $result = ClaimSearchResult::fromApi([
            'billedAmount' => 100,
            'payerPaidAmount' => 80.5,
        ]);

        $this->assertSame(100.0, $result->billedAmount);
        $this->assertSame(80.5, $result->payerPaidAmount);
    }

    public function testFloatFieldAcceptsNumericString(): void
    {
        $result = ClaimSearchResult::fromApi([
            'billedAmount' => '250.50',
        ]);

        $this->assertSame(250.50, $result->billedAmount);
    }

    public function testNonStringStringFieldReturnsNull(): void
    {
        // statusName declared as string — an int input is not coerced
        // (would mask data drift); the field comes back null instead.
        $result = ClaimSearchResult::fromApi([
            'statusName' => 42,
            'patientControlNumber' => ['nested', 'array'],
        ]);

        $this->assertNull($result->statusName);
        $this->assertNull($result->patientControlNumber);
    }

    public function testNonBoolBoolFieldReturnsNull(): void
    {
        // isWorked declared as bool — string '1' is not coerced.
        // Callers that need string→bool coercion should use TypeCoerce.
        $result = ClaimSearchResult::fromApi([
            'isWorked' => '1',
        ]);

        $this->assertNull($result->isWorked);
    }

    public function testFullResponseParse(): void
    {
        // Realistic shape mirroring what the ClaimRev API returns.
        $result = ClaimSearchResult::fromApi([
            'statusName' => 'Accepted',
            'statusId' => 7,
            'payerFileStatusId' => 1,
            'payerFileStatusName' => 'Submitted',
            'payerAcceptanceStatusId' => 4,
            'payerAcceptanceStatusName' => 'Accepted by Payer',
            'paymentAdviceStatusId' => 2,
            'paymentAdviceStatusName' => 'Paid',
            'eraClassification' => 'Paid',
            'patientControlNumber' => '42-1001',
            'isWorked' => false,
            'objectId' => 9999,
            'claimTypeId' => 1,
            'claimType' => 'Professional',
            'errorCount' => 0,
            'pFirstName' => 'Jane',
            'pLastName' => 'Doe',
            'birthDate' => '1980-01-01',
            'payerName' => 'Acme Health',
            'payerNumber' => '12345',
            'providerFirstName' => 'Bob',
            'providerLastName' => 'Smith',
            'providerNpi' => '1234567890',
            'serviceDate' => '2026-01-15',
            'serviceDateEnd' => '2026-01-15',
            'receivedDate' => '2026-01-16',
            'billedAmount' => 250.0,
            'payerPaidAmount' => 200.0,
            'payerControlNumber' => 'PCN-99',
            'memberNumber' => 'MEM-12345',
            'traceNumber' => 'TR-001',
        ]);

        $this->assertSame('Accepted', $result->statusName);
        $this->assertSame('Doe', $result->pLastName);
        $this->assertSame('1234567890', $result->providerNpi);
        $this->assertSame(250.0, $result->billedAmount);
        $this->assertFalse($result->isWorked);
    }
}
