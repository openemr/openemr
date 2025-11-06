<?php

/**
 * EncounterReportFormatterTest.php
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Reports\Encounter;

use OpenEMR\Reports\Encounter\EncounterReportFormatter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EncounterReportFormatter::class)]
class EncounterReportFormatterTest extends TestCase
{
    private EncounterReportFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new EncounterReportFormatter();
    }

    #[Test]
    public function testFormatEncountersTransformsArrayCorrectly(): void
    {
        $encounters = [
            [
                'id' => 1,
                'date' => '2025-01-15 10:30:00',
                'encounter' => 1001,
                'pid' => 10,
                'provider' => 'Doe, John',
                'patient' => 'Smith, Jane',
                'category' => 'Office Visit',
                'encounter_nr' => 'ENC001',
                'form_id' => 5,
                'forms' => 'History and Physical',
                'coding' => '99213, 99000',
                'encounter_signer' => 'Doe, John',
                'form_signer' => null,
            ],
            [
                'id' => 2,
                'date' => '2025-01-16 14:00:00',
                'encounter' => 1002,
                'pid' => 11,
                'provider' => 'Smith, Sarah',
                'patient' => 'Johnson, Bob',
                'category' => 'Established Patient',
                'encounter_nr' => 'ENC002',
                'form_id' => 6,
                'forms' => 'Progress Note',
                'coding' => '99214',
                'encounter_signer' => null,
                'form_signer' => 'Smith, Sarah',
            ],
        ];

        $result = $this->formatter->formatEncounters($encounters);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        $this->assertArrayHasKey('provider', $result[0]);
        $this->assertArrayHasKey('date', $result[0]);
        $this->assertArrayHasKey('patient', $result[0]);
        
        $this->assertSame('Doe, John', $result[0]['provider']);
        $this->assertSame('2025-01-15', $result[0]['date']);
        $this->assertSame('Smith, Jane', $result[0]['patient']);
    }

    #[Test]
    public function testFormatEncountersWithEmptyArray(): void
    {
        $result = $this->formatter->formatEncounters([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function testFormatEncounterRowAllFieldsPresent(): void
    {
        $encounter = [
            'id' => 1,
            'date' => '2025-01-15 10:30:00',
            'encounter' => 1001,
            'pid' => 10,
            'provider' => 'Doe, John',
            'patient' => 'Smith, Jane',
            'category' => 'Office Visit',
            'encounter_nr' => 'ENC001',
            'form_id' => 5,
            'forms' => 'History and Physical',
            'coding' => '99213, 99000',
            'encounter_signer' => 'Doe, John',
            'form_signer' => 'Smith, Sarah',
        ];

        $result = $this->formatter->formatEncounterRow($encounter);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('provider', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('patient', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('pid', $result);
        $this->assertArrayHasKey('encounter', $result);
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('forms', $result);
        $this->assertArrayHasKey('coding', $result);
        $this->assertArrayHasKey('encounter_signer', $result);
        $this->assertArrayHasKey('form_signer', $result);
        
        $this->assertSame('Doe, John', $result['provider']);
        $this->assertSame('Smith, Jane', $result['patient']);
        $this->assertSame(1, $result['id']);
        $this->assertSame(10, $result['pid']);
        $this->assertSame(1001, $result['encounter']);
        $this->assertSame('Doe, John', $result['encounter_signer']);
        $this->assertSame('Smith, Sarah', $result['form_signer']);
    }

    #[Test]
    public function testFormatEncounterRowWithMissingOptionalFields(): void
    {
        $encounter = [
            'id' => 1,
            'date' => '2025-01-15 10:30:00',
            'encounter' => 1001,
            'pid' => 10,
            'provider' => 'Doe, John',
            'patient' => 'Smith, Jane',
            'category' => 'Office Visit',
            'encounter_nr' => 'ENC001',
            'form_id' => 5,
            'forms' => 'History and Physical',
            'coding' => '99213',
            'encounter_signer' => null,
            'form_signer' => null,
        ];

        $result = $this->formatter->formatEncounterRow($encounter);

        $this->assertIsArray($result);
        $this->assertNull($result['encounter_signer']);
        $this->assertNull($result['form_signer']);
    }

    #[Test]
    public function testFormatEncounterRowDateFormatting(): void
    {
        $encounter = [
            'id' => 1,
            'date' => '2025-01-15 10:30:45',
            'encounter' => 1001,
            'pid' => 10,
            'provider' => 'Doe, John',
            'patient' => 'Smith, Jane',
            'category' => 'Office Visit',
            'encounter_nr' => 'ENC001',
            'form_id' => 5,
            'forms' => 'History and Physical',
            'coding' => '99213',
            'encounter_signer' => null,
            'form_signer' => null,
        ];

        $result = $this->formatter->formatEncounterRow($encounter);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('date', $result);
        $this->assertSame('2025-01-15', $result['date']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result['date']);
    }

    #[Test]
    public function testFormatSummaryTotals(): void
    {
        $summaryData = [
            ['provider_id' => 1, 'provider_name' => 'Doe, John', 'encounter_count' => 5],
            ['provider_id' => 2, 'provider_name' => 'Smith, Sarah', 'encounter_count' => 3],
            ['provider_id' => 3, 'provider_name' => 'Johnson, Mike', 'encounter_count' => 7],
        ];

        $result = $this->formatter->formatSummary($summaryData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('providers', $result);
        $this->assertArrayHasKey('total_encounters', $result);
        $this->assertCount(3, $result['providers']);
        $this->assertSame(15, $result['total_encounters']);
    }

    #[Test]
    public function testFormatSummaryProviderBreakdown(): void
    {
        $summaryData = [
            ['provider_id' => 1, 'provider_name' => 'Doe, John', 'encounter_count' => 5],
            ['provider_id' => 2, 'provider_name' => 'Smith, Sarah', 'encounter_count' => 3],
        ];

        $result = $this->formatter->formatSummary($summaryData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('providers', $result);
        
        $providers = $result['providers'];
        $this->assertCount(2, $providers);
        
        $this->assertSame(1, $providers[0]['provider_id']);
        $this->assertSame('Doe, John', $providers[0]['provider_name']);
        $this->assertSame(5, $providers[0]['encounter_count']);
        
        $this->assertSame(2, $providers[1]['provider_id']);
        $this->assertSame('Smith, Sarah', $providers[1]['provider_name']);
        $this->assertSame(3, $providers[1]['encounter_count']);
    }

    #[Test]
    public function testFormatSummaryWithEmptyData(): void
    {
        $result = $this->formatter->formatSummary([]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('providers', $result);
        $this->assertArrayHasKey('total_encounters', $result);
        $this->assertEmpty($result['providers']);
        $this->assertSame(0, $result['total_encounters']);
    }
}
