<?php

/**
 * A history_data row names the user who saved it (created_by). The smoking status Observation names that user as its
 * performer, by the user's uuid as text, so the resource encodes as JSON. Seeds a synthetic user, patient and history
 * row, and removes them afterwards.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR\Observation;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FHIR\Observation\FhirObservationSocialHistoryService;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\SocialHistoryService;
use PHPUnit\Framework\TestCase;

class FhirObservationSocialHistoryServiceTest extends TestCase
{
    private const PID = 993921;

    private const USERNAME = 'fhir-social-history-test';

    private string $patientUuid = '';

    private string $userUuid = '';

    protected function setUp(): void
    {
        $this->clean();
        $userUuid = UuidRegistry::getRegistryForTable('users')->createUuid();
        $this->userUuid = UuidRegistry::uuidToString($userUuid);
        $userId = QueryUtils::sqlInsert(
            "INSERT INTO users (uuid, username, fname, lname, npi, authorized, active) VALUES (?, ?, 'Social', 'Recorder', '1234567893', 1, 1)",
            [$userUuid, self::USERNAME]
        );
        $patientUuid = UuidRegistry::getRegistryForTable('patient_data')->createUuid();
        $this->patientUuid = UuidRegistry::uuidToString($patientUuid);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (uuid, pid, pubpid, fname, lname, DOB, sex) VALUES (?, ?, 'SH1', 'Social', 'History', '1980-01-01', 'Female')",
            [$patientUuid, self::PID]
        );
        $historyUuid = UuidRegistry::getRegistryForTable('history_data')->createUuid();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO history_data (uuid, pid, date, tobacco, created_by) VALUES (?, ?, NOW(), 'never smoked|nevertobacco|20251003|4|0', ?)",
            [$historyUuid, self::PID, $userId]
        );
        $fhir = new FhirObservationSocialHistoryService();
        $paths = array_map($fhir->getResourcePathForCode(...), array_column(FhirObservationSocialHistoryService::COLUMN_MAPPINGS, 'code'));
        UuidMapping::createMappingRecordForResourcePaths($historyUuid, 'Observation', 'history_data', $paths);
    }

    protected function tearDown(): void
    {
        $this->clean();
    }

    private function clean(): void
    {
        $history = 'SELECT uuid FROM history_data WHERE pid = ?';
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM uuid_mapping WHERE target_uuid IN ($history))", [self::PID]);
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_mapping WHERE target_uuid IN ($history)", [self::PID]);
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid IN ($history)", [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM history_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM patient_data WHERE pid = ?)', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM users WHERE username = ?)', [self::USERNAME]);
        QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE username = ?', [self::USERNAME]);
    }

    public function testTheSearchGivesTheSavingUsersUuidAsText(): void
    {
        $rows = (new SocialHistoryService())->search(['puuid' => new TokenSearchField('puuid', [$this->patientUuid], true)])->getData();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $row = $rows[0] ?? null;
        $this->assertIsArray($row);
        $this->assertSame($this->userUuid, $row['provider_uuid'] ?? null);
    }

    public function testTheSmokingStatusPerformerIsTheUserAndTheResourceEncodes(): void
    {
        $observations = (new FhirObservationSocialHistoryService())->getAll(['patient' => $this->patientUuid])->getData();
        $this->assertIsArray($observations);
        $this->assertNotEmpty($observations);
        foreach ($observations as $observation) {
            $json = json_encode($observation, JSON_THROW_ON_ERROR);
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $this->assertIsArray($decoded);
            $performers = $decoded['performer'] ?? null;
            $this->assertIsArray($performers);
            $performer = $performers[0] ?? null;
            $this->assertIsArray($performer);
            $this->assertSame('Practitioner/' . $this->userUuid, $performer['reference'] ?? null);
        }
    }
}
