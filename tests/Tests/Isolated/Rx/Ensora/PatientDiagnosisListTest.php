<?php

/**
 * Isolated tests for the diagnoses sent to Ensora on eRx launch.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Ensora;

use OpenEMR\Rx\Ensora\PatientDiagnosis;
use OpenEMR\Rx\Ensora\PatientDiagnosisList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientDiagnosisListTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function row(string $diagnosis, string $begdate = '', ?string $enddate = null, string $title = '', string $date = ''): array
    {
        return ['diagnosis' => $diagnosis, 'begdate' => $begdate, 'enddate' => $enddate, 'title' => $title, 'date' => $date];
    }

    /**
     * @param list<PatientDiagnosis> $diagnoses
     * @return list<string>
     */
    private static function codes(array $diagnoses): array
    {
        return array_map(static fn(PatientDiagnosis $diagnosis): string => $diagnosis->code, $diagnoses);
    }

    public function testRepeatedCodeIsSentOnceKeepingTheFirstRow(): void
    {
        $diagnoses = PatientDiagnosisList::fromProblemRows([
            self::row('ICD10:E11.9', '2026-03-01 00:00:00', title: 'Newest visit'),
            self::row('ICD10:I10', '2025-06-01 00:00:00'),
            self::row('ICD10:E11.9', '2024-01-01 00:00:00', title: 'Older visit'),
        ]);

        $this->assertSame(['E11.9', 'I10'], self::codes($diagnoses));
        $this->assertSame('20260301', $diagnoses[0]->onsetDate);
        $this->assertSame('Newest visit', $diagnoses[0]->name);
    }

    public function testStopsAtTheSchemaLimit(): void
    {
        $rows = [];
        for ($i = 0; $i < PatientDiagnosisList::MAX_DIAGNOSES + 20; $i++) {
            $rows[] = self::row(sprintf('ICD10:Z%03d', $i));
        }

        $diagnoses = PatientDiagnosisList::fromProblemRows($rows);

        $this->assertCount(PatientDiagnosisList::MAX_DIAGNOSES, $diagnoses);
        $this->assertSame('Z000', $diagnoses[0]->code);
        $this->assertSame('Z099', $diagnoses[PatientDiagnosisList::MAX_DIAGNOSES - 1]->code);
    }

    public function testDuplicatesDoNotCountTowardTheLimit(): void
    {
        $rows = array_fill(0, PatientDiagnosisList::MAX_DIAGNOSES, self::row('ICD10:E11.9'));
        $rows[] = self::row('ICD10:I10');

        $this->assertSame(['E11.9', 'I10'], self::codes(PatientDiagnosisList::fromProblemRows($rows)));
    }

    public function testMultiCodeRowsSplitAndKeepOnlyIcd10(): void
    {
        $diagnoses = PatientDiagnosisList::fromProblemRows([
            self::row('ICD10:E11.9; SNOMED-CT:44054006;ICD10:I10'),
            self::row('ICD9:250.00'),
        ]);

        $this->assertSame(['E11.9', 'I10'], self::codes($diagnoses));
    }

    /**
     * @return array<string, array{?string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function endDateProvider(): array
    {
        return [
            'no end date' => [null, true],
            'empty end date' => ['', true],
            'zero end date' => ['0000-00-00 00:00:00', true],
            'resolved' => ['2025-01-01 00:00:00', false],
        ];
    }

    #[DataProvider('endDateProvider')]
    public function testOnlyActiveProblemsAreSent(?string $enddate, bool $sent): void
    {
        $diagnoses = PatientDiagnosisList::fromProblemRows([self::row('ICD10:E11.9', enddate: $enddate)]);

        $this->assertSame($sent ? ['E11.9'] : [], self::codes($diagnoses));
    }

    public function testResolvedRowDoesNotHideAnActiveRowForTheSameCode(): void
    {
        $diagnoses = PatientDiagnosisList::fromProblemRows([
            self::row('ICD10:E11.9', enddate: '2025-01-01 00:00:00'),
            self::row('ICD10:E11.9', '2020-01-01'),
        ]);

        $this->assertSame(['E11.9'], self::codes($diagnoses));
        $this->assertSame('20200101', $diagnoses[0]->onsetDate);
    }

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function titleProvider(): array
    {
        return [
            'plain' => ['Type 2 diabetes', 'Type 2 diabetes'],
            'code prefix' => ['E11.9 - Type 2 diabetes', 'Type 2 diabetes'],
            'line breaks' => ['Type 2' . chr(13) . chr(10) . 'diabetes' . chr(10), 'Type 2 diabetes'],
            'prefix of another code kept' => ['I10 - Hypertension', 'I10 - Hypertension'],
            'empty' => ['', null],
            'only the prefix' => ['E11.9 - ', null],
            'too long' => [str_repeat('x', 300), str_repeat('x', 255)],
        ];
    }

    #[DataProvider('titleProvider')]
    public function testNameIsCleaned(string $title, ?string $expected): void
    {
        $diagnoses = PatientDiagnosisList::fromProblemRows([self::row('ICD10:E11.9', title: $title)]);

        $this->assertSame($expected, $diagnoses[0]->name);
    }

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dateProvider(): array
    {
        return [
            'datetime' => ['2026-09-24 10:59:00', '20260924'],
            'date' => ['2026-09-24', '20260924'],
            'empty' => ['', null],
            'zero' => ['0000-00-00 00:00:00', null],
            'not a date' => ['unknown', null],
        ];
    }

    #[DataProvider('dateProvider')]
    public function testDatesAreCompacted(string $value, ?string $expected): void
    {
        $diagnoses = PatientDiagnosisList::fromProblemRows([self::row('ICD10:E11.9', $value, date: $value)]);

        $this->assertSame($expected, $diagnoses[0]->onsetDate);
        $this->assertSame($expected, $diagnoses[0]->recordedDate);
    }

    public function testRowsWithoutAUsableDiagnosisAreSkipped(): void
    {
        $diagnoses = PatientDiagnosisList::fromProblemRows([
            false,
            ['diagnosis' => null, 'enddate' => null],
            self::row(''),
            self::row('ICD10:'),
            self::row('ICD10:I10'),
        ]);

        $this->assertSame(['I10'], self::codes($diagnoses));
    }
}
