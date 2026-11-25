<?php

/**
 * Tests for the read-only EDI X12 277 / 277CA claim-status reader.
 *
 * The reader and these de-identified fixtures need no database and no
 * OpenEMR bootstrap: X12File parses the fixtures entirely from disk.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Community
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\Claim277Reader;
use OpenEMR\Billing\EdiHistory\X12File;
use PHPUnit\Framework\TestCase;

if (!class_exists(X12File::class)) {
    require __DIR__ . '/../../../../../src/Billing/EdiHistory/X12File.php';
}

require_once __DIR__ . '/text_shim.php';

class Claim277ReaderTest extends TestCase
{
    private function reader(string $fixture): Claim277Reader
    {
        return new Claim277Reader(new X12File(__DIR__ . '/fixtures/' . $fixture));
    }

    public function testClearinghouseOnlyAcknowledgmentKeepsEveryStatusAndSourceEntityCode(): void
    {
        $claims = $this->reader('clearinghouse-acknowledgment.txt')->claims();

        $this->assertCount(1, $claims);
        $claim = $claims[0];
        $this->assertSame('CLR-CLAIM-1', $claim->reference);

        // Source entity code preserved verbatim (clearinghouse = AY here).
        $this->assertSame('AY', $claim->informationSource->entityCode);
        $this->assertSame('CLEARINGHOUSE HUB', $claim->informationSource->name);
        $this->assertSame('XX', $claim->informationSource->idCode);
        $this->assertSame('9999999999', $claim->informationSource->id);
        $this->assertSame('20260102', $claim->informationSource->receivedDate);

        // Every status survives; nothing is overwritten.
        $this->assertCount(2, $claim->statuses);
        $this->assertSame('A1', $claim->statuses[0]->category);
        $this->assertSame('19', $claim->statuses[0]->code);
        $this->assertSame('AB', $claim->statuses[0]->entity);
        $this->assertSame('20260103', $claim->statuses[0]->statusDate);
        $this->assertSame('2', $claim->statuses[0]->actionCode);
        $this->assertSame('0', $claim->statuses[0]->amount);
        $this->assertSame('CLAIM ACCEPTED', $claim->statuses[0]->payerText);
        $this->assertSame('STC*A1:19:AB*20260103*2*0***CLAIM ACCEPTED', $claim->statuses[0]->rawSegment);
        $this->assertNull($claim->statuses[0]->svcSegment);

        $this->assertSame('P3', $claim->statuses[1]->category);
        $this->assertSame('237', $claim->statuses[1]->code);
        $this->assertSame('PENDING', $claim->statuses[1]->payerText);

        $this->assertSame([], $claim->serviceLineStatuses);
    }

    public function testPayerAcknowledgmentCapturesPayerClaimNumberAndClearinghouseTrace(): void
    {
        $claim = $this->reader('payer-acknowledgment-ref1k.txt')->claims()[0];

        $this->assertSame('PAY-CLAIM-7', $claim->reference);
        $this->assertSame('PR', $claim->informationSource->entityCode);
        $this->assertSame('PAYER INSURANCE CO', $claim->informationSource->name);

        $this->assertSame('PAYER-CLAIM-5555', $claim->payerClaimNumber);
        $this->assertSame('CH-TRACE-8888', $claim->clearinghouseTrace);
        $this->assertSame('ACCT-42', $claim->patientAccountNumber);
        $this->assertSame('20260105', $claim->serviceDate);
        // The QC patient at claim level replaces the IL subscriber seed (QC preferred).
        $this->assertSame('JANE DOE', $claim->patientName);

        $this->assertCount(1, $claim->statuses);
        $this->assertSame('CLAIM ACCEPTED', $claim->statuses[0]->payerText);
    }

    public function testRejectionKeepsEveryStatusWithoutOverwriting(): void
    {
        $claim = $this->reader('rejection.txt')->claims()[0];

        $this->assertSame('REJ-CLAIM-3', $claim->reference);
        $this->assertCount(2, $claim->statuses);

        // Two distinct rejection statuses both survive.
        $this->assertSame('A7', $claim->statuses[0]->category);
        $this->assertSame('64', $claim->statuses[0]->code);
        $this->assertSame('CLAIM REJECTED', $claim->statuses[0]->payerText);

        $this->assertSame('A7', $claim->statuses[1]->category);
        $this->assertSame('18', $claim->statuses[1]->code);
        $this->assertSame('INVALID MEMBER ID', $claim->statuses[1]->payerText);
    }

    public function testServiceLineStatusesAttachToTheirClaimWithPrecedingSvc(): void
    {
        $claim = $this->reader('service-line-statuses.txt')->claims()[0];

        $this->assertSame('SV-CLAIM-4', $claim->reference);

        // Claim-level status is separate from service-line statuses.
        $this->assertCount(1, $claim->statuses);
        $this->assertSame('CLAIM ACCEPTED', $claim->statuses[0]->payerText);

        $this->assertCount(1, $claim->serviceLineStatuses);
        $line = $claim->serviceLineStatuses[0];
        $this->assertSame('A1', $line->category);
        $this->assertSame('19', $line->code);
        $this->assertSame('45.00', $line->amount);
        $this->assertSame('SERVICE LINE PAID', $line->payerText);
        $this->assertSame('STC*A1:19:AB*20260105*2*45.00***SERVICE LINE PAID', $line->rawSegment);
        $this->assertSame('SVC*HC:99213:11', $line->svcSegment);
    }

    public function testSubscriberOnlyResponseUsesSubscriberName(): void
    {
        $claim = $this->reader('subscriber-only-277-response.txt')->claims()[0];

        $this->assertSame('SUB-CLAIM-6', $claim->reference);
        // NM1*IL at level 22 precedes TRN*2, so the subscriber name seeds patientName.
        $this->assertSame('JANE DOE', $claim->patientName);
        $this->assertCount(1, $claim->statuses);
        $this->assertSame('CLAIM ACCEPTED', $claim->statuses[0]->payerText);
    }

    public function testTwoClaimsUnderOnePatientComeBackSeparately(): void
    {
        $claims = $this->reader('two-claims-one-patient.txt')->claims();

        $this->assertCount(2, $claims);
        $this->assertNotSame($claims[0], $claims[1]);

        $this->assertSame('CLAIM-A', $claims[0]->reference);
        $this->assertCount(1, $claims[0]->statuses);
        $this->assertSame('CLAIM ACCEPTED', $claims[0]->statuses[0]->payerText);

        $this->assertSame('CLAIM-B', $claims[1]->reference);
        $this->assertCount(1, $claims[1]->statuses);
        $this->assertSame('PENDING', $claims[1]->statuses[0]->payerText);

        // Both claims attach to the same nearest ancestor information source.
        $this->assertSame('AY', $claims[0]->informationSource->entityCode);
        $this->assertSame('AY', $claims[1]->informationSource->entityCode);
        $this->assertSame('CLEARINGHOUSE HUB', $claims[0]->informationSource->name);
        $this->assertSame('CLEARINGHOUSE HUB', $claims[1]->informationSource->name);
    }

    public function testReferenceLookupIsExactAndNeverSubstring(): void
    {
        $reader = $this->reader('two-claims-one-patient.txt');

        $foundA = $reader->findClaim('CLAIM-A');
        $this->assertNotNull($foundA);
        $this->assertSame('CLAIM-A', $foundA->reference);

        $foundB = $reader->findClaim('CLAIM-B');
        $this->assertNotNull($foundB);
        $this->assertSame('CLAIM-B', $foundB->reference);

        // Prefix / substring lookups must not match.
        $this->assertNull($reader->findClaim('CLAIM'));
        $this->assertNull($reader->findClaim('CLAIM-'));
        $this->assertNull($reader->findClaim('CLAIM-AX'));
        $this->assertNull($reader->findClaim('CLAIM-A '));
        $this->assertNull($reader->findClaim('NONEXISTENT'));
    }
}
