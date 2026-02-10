<?php

/**
 * Tests for duplicate patient detection logic
 *
 * Verifies that updateDupScore() correctly detects duplicates using symmetric comparison.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../library/patient.inc.php';

class DuplicatePatientDetectionTest extends TestCase
{
    /** @var list<int> PIDs to clean up after tests */
    private array $createdPids = [];

    protected function tearDown(): void
    {
        // Clean up patients and their UUID registry entries
        foreach ($this->createdPids as $pid) {
            $row = QueryUtils::querySingleRow("SELECT uuid FROM patient_data WHERE pid = ?", [$pid]);
            if (is_array($row) && isset($row['uuid'])) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM uuid_registry WHERE table_name = 'patient_data' AND uuid = ?",
                    [$row['uuid']]
                );
            }
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$pid]);
        }
        $this->createdPids = [];
    }

    /**
     * Get the next available PID.
     */
    private function getNextPid(): int
    {
        /** @var array{next_pid: int|string}|null $result */
        $result = QueryUtils::querySingleRow("SELECT IFNULL(MAX(pid), 0) + 1 AS next_pid FROM patient_data");
        self::assertIsArray($result);
        return (int) $result['next_pid'];
    }

    /**
     * Create a patient with the given data and track for cleanup.
     *
     * @param array<string, mixed> $data Patient data (fname, lname, DOB, sex, email)
     * @return int The created patient's PID
     */
    private function createPatient(array $data): int
    {
        $pid = $this->getNextPid();
        $uuid = (new UuidRegistry(['table_name' => 'patient_data']))->createUuid();

        $defaults = [
            'pid' => $pid,
            'uuid' => $uuid,
            'pubpid' => 'test-dup-' . uniqid(),
            'fname' => 'Test',
            'lname' => 'Patient',
            'DOB' => '1980-01-01',
            'sex' => 'Male',
            'email' => 'test@example.com',
            'dupscore' => 0,
        ];
        $patientData = array_merge($defaults, $data);

        $columns = array_keys($patientData);
        $placeholders = array_fill(0, count($columns), '?');
        $sql = sprintf(
            "INSERT INTO patient_data (%s) VALUES (%s)",
            implode(', ', array_map(fn($c) => "`$c`", $columns)),
            implode(', ', $placeholders)
        );
        QueryUtils::sqlStatementThrowException($sql, array_values($patientData));

        $this->createdPids[] = $pid;
        return $pid;
    }

    /**
     * Test that updateDupScore detects a match with a higher-PID patient.
     *
     * This was the original bug: when patient A (low PID) was edited to match
     * patient B (high PID), the asymmetric comparison (p2.pid < p1.pid) missed
     * the match because B's PID is greater than A's.
     */
    #[Test]
    public function testUpdateDupScoreDetectsHigherPidMatch(): void
    {
        // Create patient A first (will have lower PID)
        $pidA = $this->createPatient([
            'fname' => 'UniqueA',
            'lname' => 'TestPatient',
            'DOB' => '1985-03-15',
            'sex' => 'Female',
            'email' => 'uniquea@example.com',
        ]);

        // Create patient B second (will have higher PID)
        $pidB = $this->createPatient([
            'fname' => 'John',
            'lname' => 'Duplicate',
            'DOB' => '1990-06-20',
            'sex' => 'Male',
            'email' => 'john.duplicate@example.com',
        ]);

        // Verify A < B (precondition for the test)
        $this->assertLessThan($pidB, $pidA, 'Test requires patient A to have lower PID than B');

        // Edit patient A to exactly match patient B's demographics
        QueryUtils::sqlStatementThrowException(
            "UPDATE patient_data SET fname = ?, lname = ?, DOB = ?, sex = ?, email = ? WHERE pid = ?",
            ['John', 'Duplicate', '1990-06-20', 'Male', 'john.duplicate@example.com', $pidA]
        );

        // Now update A's dupscore - with symmetric comparison, it should detect B
        $score = updateDupScore($pidA);

        // Score should be > 0 because A now matches B
        $this->assertGreaterThan(
            0,
            $score,
            "updateDupScore should detect match with higher-PID patient (A=$pidA matches B=$pidB)"
        );
    }

    /**
     * Test that two identical patients have symmetric (equal) scores.
     *
     * When two patients have matching demographics, both should have the same
     * dupscore regardless of which PID is higher.
     */
    #[Test]
    public function testUpdateDupScoreSymmetricScoring(): void
    {
        // Create two patients with identical demographics
        $sharedData = [
            'fname' => 'Symmetric',
            'lname' => 'TestPatient',
            'DOB' => '1975-12-25',
            'sex' => 'Female',
            'email' => 'symmetric@example.com',
        ];

        $pid1 = $this->createPatient($sharedData);
        $pid2 = $this->createPatient($sharedData);

        // Update both patients' dupscores
        $score1 = updateDupScore($pid1);
        $score2 = updateDupScore($pid2);

        // Both should have non-zero scores
        $this->assertGreaterThan(0, $score1, "Patient 1 should detect duplicate");
        $this->assertGreaterThan(0, $score2, "Patient 2 should detect duplicate");

        // Both should have equal scores (symmetric)
        $this->assertEquals(
            $score1,
            $score2,
            "Both patients should have equal dupscores for symmetric detection"
        );
    }

    /**
     * Test that patients marked as unique (dupscore=-1) are excluded from matching.
     *
     * When a patient has dupscore=-1, they've been explicitly marked as not a duplicate.
     * The updateDupScore function should not consider them as potential matches.
     */
    #[Test]
    public function testUpdateDupScoreRespectsUniqueFlag(): void
    {
        // Create a patient marked as unique
        $pidUnique = $this->createPatient([
            'fname' => 'Marked',
            'lname' => 'AsUnique',
            'DOB' => '1960-01-01',
            'sex' => 'Male',
            'email' => 'unique@example.com',
            'dupscore' => -1,  // Marked as unique/not a duplicate
        ]);

        // Create another patient with identical demographics
        $pidNew = $this->createPatient([
            'fname' => 'Marked',
            'lname' => 'AsUnique',
            'DOB' => '1960-01-01',
            'sex' => 'Male',
            'email' => 'unique@example.com',
        ]);

        // Update the new patient's dupscore
        $score = updateDupScore($pidNew);

        // Score should be 0 because the only potential match is marked as unique
        $this->assertEquals(
            0,
            $score,
            "updateDupScore should not match against patients with dupscore=-1"
        );
    }
}
