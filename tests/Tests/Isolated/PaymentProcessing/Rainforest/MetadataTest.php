<?php

/**
 * Isolated tests for Rainforest Metadata and EncounterData DTOs
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PaymentProcessing\Rainforest;

use Money\Currency;
use Money\Money;
use OpenEMR\PaymentProcessing\Rainforest\EncounterData;
use OpenEMR\PaymentProcessing\Rainforest\Metadata;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class MetadataTest extends TestCase
{
    private function makeEncounterData(): EncounterData
    {
        return new EncounterData(
            id: '101',
            code: '99213',
            codeType: 'CPT4',
            amount: new Money('5000', new Currency('USD')),
        );
    }

    public function testConstructorSetsPublicProperties(): void
    {
        $encounter = $this->makeEncounterData();
        $metadata = new Metadata(patientId: '42', encounters: [$encounter]);

        $this->assertSame('42', $metadata->patientId);
        $this->assertCount(1, $metadata->encounters);
        $this->assertSame($encounter, $metadata->encounters[0]);
    }

    public function testJsonSerializeIncludesFormatVersion(): void
    {
        $encounter = $this->makeEncounterData();
        $metadata = new Metadata(patientId: '42', encounters: [$encounter]);
        $serialized = $metadata->jsonSerialize();

        $this->assertSame('42', $serialized['patientId']);
        $this->assertSame(1, $serialized['formatVersion']);
        $this->assertCount(1, $serialized['encounters']);
    }

    public function testFromParsedJsonRoundTrips(): void
    {
        $encounter = $this->makeEncounterData();
        $original = new Metadata(patientId: '42', encounters: [$encounter]);

        // Simulate JSON round-trip
        $json = json_encode($original);
        $this->assertIsString($json);
        /** @var array{formatVersion: int, patientId: string, encounters: array<array<string, mixed>>} $parsed */
        $parsed = json_decode($json, true);
        $restored = Metadata::fromParsedJson($parsed);

        $this->assertSame('42', $restored->patientId);
        $this->assertCount(1, $restored->encounters);
        $this->assertSame('101', $restored->encounters[0]->id);
        $this->assertSame('99213', $restored->encounters[0]->code);
        $this->assertSame('CPT4', $restored->encounters[0]->codeType);
        $this->assertTrue($restored->encounters[0]->amount->equals(new Money('5000', new Currency('USD'))));
    }

    public function testFromParsedJsonThrowsOnUnknownFormatVersion(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown format version');

        Metadata::fromParsedJson([
            'formatVersion' => 2,
            'patientId' => '42',
            'encounters' => [],
        ]);
    }

    public function testEncounterDataConstructorSetsProperties(): void
    {
        $amount = new Money('1500', new Currency('USD'));
        $encounter = new EncounterData(
            id: '200',
            code: '99214',
            codeType: 'CPT4',
            amount: $amount,
        );

        $this->assertSame('200', $encounter->id);
        $this->assertSame('99214', $encounter->code);
        $this->assertSame('CPT4', $encounter->codeType);
        $this->assertSame($amount, $encounter->amount);
    }

    public function testEncounterDataJsonSerialize(): void
    {
        $amount = new Money('1500', new Currency('USD'));
        $encounter = new EncounterData(
            id: '200',
            code: '99214',
            codeType: 'CPT4',
            amount: $amount,
        );
        $serialized = $encounter->jsonSerialize();

        $this->assertSame('200', $serialized['id']);
        $this->assertSame('99214', $serialized['code']);
        $this->assertSame('CPT4', $serialized['codeType']);
        $this->assertSame($amount, $serialized['amount']);
    }

    public function testEncounterDataFromParsedJson(): void
    {
        $data = [
            'id' => '300',
            'code' => '99215',
            'codeType' => 'CPT4',
            'amount' => ['amount' => '7500', 'currency' => 'USD'],
        ];
        $encounter = EncounterData::fromParsedJson($data);

        $this->assertSame('300', $encounter->id);
        $this->assertSame('99215', $encounter->code);
        $this->assertSame('CPT4', $encounter->codeType);
        $this->assertTrue($encounter->amount->equals(new Money('7500', new Currency('USD'))));
    }

    public function testMultipleEncountersRoundTrip(): void
    {
        $enc1 = new EncounterData('1', '99213', 'CPT4', new Money('5000', new Currency('USD')));
        $enc2 = new EncounterData('2', '99214', 'CPT4', new Money('7500', new Currency('USD')));
        $original = new Metadata(patientId: '99', encounters: [$enc1, $enc2]);

        $json = json_encode($original);
        $this->assertIsString($json);
        /** @var array{formatVersion: int, patientId: string, encounters: array<array<string, mixed>>} $parsed */
        $parsed = json_decode($json, true);
        $restored = Metadata::fromParsedJson($parsed);

        $this->assertSame('99', $restored->patientId);
        $this->assertCount(2, $restored->encounters);
        $this->assertSame('1', $restored->encounters[0]->id);
        $this->assertSame('2', $restored->encounters[1]->id);
    }
}
