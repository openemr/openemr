<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\CdaVitalsReimport;
use PHPUnit\Framework\TestCase;

class CdaVitalsReimportTest extends TestCase
{
    private const STORED = [
        'id' => '5',
        'pid' => '1',
        'date' => '2026-03-01 09:30:00',
        'bps' => '120',
        'bpd' => '80',
        'height' => '65.000000',
        'weight' => '150.000000',
        'temperature' => '98.600000',
        'pulse' => '70.000000',
        'respiration' => '16.000000',
        'head_circ' => null,
        'oxygen_saturation' => '98.00',
        'BMI' => '25.000000',
        'external_id' => 'vitals-1',
    ];

    public function testAReadingTheDocumentCarriesIsKept(): void
    {
        $vitals = ['extension' => 'vitals-1', 'date' => '20260401', 'bps' => '130', 'weight' => '151', 'pulse' => '0'];

        $merged = CdaVitalsReimport::withStoredReadings($vitals, self::STORED);

        $this->assertSame('130', $merged['bps']);
        $this->assertSame('151', $merged['weight']);
        $this->assertSame('0', $merged['pulse'], 'a reading of 0 is a reading');
    }

    public function testAReadingTheDocumentLacksKeepsTheStoredOne(): void
    {
        // As the direct import hands it over: null for a reading the document doesn't carry.
        $vitals = ['extension' => 'vitals-1', 'date' => '20260401', 'bps' => '130', 'bpd' => '85', 'weight' => null, 'temperature' => null];
        // As the review-and-approve form sends it: blank fields, and no BMI at all.
        $approved = ['extension' => 'vitals-1', 'date' => '01/04/2026', 'bps' => '130', 'bpd' => '85', 'weight' => '', 'temperature' => ' '];

        foreach ([$vitals, $approved] as $entry) {
            $merged = CdaVitalsReimport::withStoredReadings($entry, self::STORED);
            $this->assertSame('130', $merged['bps']);
            $this->assertSame('150.000000', $merged['weight']);
            $this->assertSame('98.600000', $merged['temperature']);
            $this->assertSame('65.000000', $merged['height']);
            $this->assertSame('25.000000', $merged['BMI']);
            $this->assertNull($merged['head_circ'], 'stored as null, still null');
        }
    }

    public function testWithNoStoredRowTheEntryIsUnchanged(): void
    {
        $vitals = ['extension' => 'vitals-1', 'weight' => null];

        $this->assertSame($vitals, CdaVitalsReimport::withStoredReadings($vitals, false));
    }

    public function testAnEntryMustBeAnArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CdaVitalsReimport::withStoredReadings('weight', self::STORED);
    }

    public function testOnlyTheReadingsAreFilledIn(): void
    {
        $vitals = ['extension' => 'vitals-1', 'date' => null, 'reason_code' => null];

        $merged = CdaVitalsReimport::withStoredReadings($vitals, self::STORED);

        $this->assertNull($merged['date'], "the entry's date is the import's to decide");
        $this->assertNull($merged['reason_code']);
        $this->assertArrayNotHasKey('id', $merged);
        $this->assertArrayNotHasKey('pid', $merged);
        $this->assertArrayNotHasKey('external_id', $merged);
    }
}
