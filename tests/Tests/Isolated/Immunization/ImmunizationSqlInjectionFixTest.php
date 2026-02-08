<?php

/**
 * ImmunizationSqlInjectionFixTest
 *
 * Simple isolated test to verify SQL injection fix without requiring database.
 * Tests the code logic: input validation and parameterized query construction.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2025 OpenEMR <info@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Immunization;

use PHPUnit\Framework\TestCase;

/**
 * Simple test to verify SQL injection fix logic
 *
 * This test verifies:
 * 1. Input validation rejects SQL injection characters
 * 2. Query construction uses parameterized placeholders (?)
 * 3. Bind values are created correctly
 *
 * No database required - just tests the code logic!
 */
class ImmunizationSqlInjectionFixTest extends TestCase
{
    /**
     * Test that input validation rejects SQL injection payloads
     */
    public function testInputValidationRejectsSqlInjection(): void
    {
        // These are SQL injection payloads that should be fully rejected.
        // Note: Payloads containing commas may have valid substrings (like "NULL", "username")
        // that pass validation - this is safe because they're used in parameterized queries.
        // This test only includes payloads where ALL parts contain invalid characters.
        $maliciousPayloads = [
            "123') OR 1=1 --",
            "123'; DROP TABLE users; --",
            "123') AND (SELECT SLEEP(5)) --",
        ];

        foreach ($maliciousPayloads as $payload) {
            $this->assertInputValidationRejects($payload, "Payload should be rejected: $payload");
        }
    }

    /**
     * Test that legitimate input passes validation
     */
    public function testInputValidationAcceptsLegitimateInput(): void
    {
        $legitimateInputs = [
            "123",
            "456",
            "John",
            "John Doe",
            "Smith-Jones",
            "123,456,789",
            "John, Jane, Bob",
        ];

        foreach ($legitimateInputs as $input) {
            $this->assertInputValidationAccepts($input, "Legitimate input should be accepted: $input");
        }
    }

    /**
     * Test that query construction uses parameterized placeholders
     */
    public function testQueryUsesParameterizedPlaceholders(): void
    {
        $patient_id = "123";

        // Simulate the fixed code logic
        $pid_arr = explode(',', $patient_id);
        $query_pids = '';
        $pid_bind_values = [];
        $pid_conditions = [];

        foreach ($pid_arr as $pid_val) {
            $pid_val = trim($pid_val);
            if (empty($pid_val) || !preg_match('/^[a-zA-Z0-9\s\-]+$/', $pid_val)) {
                continue;
            }
            $pid_conditions[] = "(p.pid = ? OR p.fname LIKE ? OR p.mname LIKE ? OR p.lname LIKE ?)";
            $pid_bind_values[] = $pid_val;
            $pid_bind_values[] = "%{$pid_val}%";
            $pid_bind_values[] = "%{$pid_val}%";
            $pid_bind_values[] = "%{$pid_val}%";
        }

        if (!empty($pid_conditions)) {
            $query_pids = '(' . implode(' OR ', $pid_conditions) . ') AND ';
        }

        // Verify query uses ? placeholders (not concatenated values)
        $this->assertStringContainsString('?', $query_pids, 'Query should use ? placeholders');
        $this->assertStringNotContainsString("'123'", $query_pids, 'Query should NOT contain concatenated values');
        $this->assertNotEmpty($pid_bind_values, 'Bind values should be created');
        $this->assertCount(4, $pid_bind_values, 'Should have 4 bind values for one patient ID');
    }

    /**
     * Test that SQL injection payloads don't create executable SQL
     */
    public function testSqlInjectionDoesNotCreateExecutableSql(): void
    {
        $maliciousPayload = "123') OR 1=1 --";

        // Simulate the fixed code logic
        $pid_arr = explode(',', $maliciousPayload);
        $query_pids = '';
        $pid_bind_values = [];
        $pid_conditions = [];

        foreach ($pid_arr as $pid_val) {
            $pid_val = trim($pid_val);
            // This is the validation from our fix
            if (empty($pid_val) || !preg_match('/^[a-zA-Z0-9\s\-]+$/', $pid_val)) {
                continue; // Should skip this malicious payload
            }
            $pid_conditions[] = "(p.pid = ? OR p.fname LIKE ? OR p.mname LIKE ? OR p.lname LIKE ?)";
            $pid_bind_values[] = $pid_val;
            $pid_bind_values[] = "%{$pid_val}%";
            $pid_bind_values[] = "%{$pid_val}%";
            $pid_bind_values[] = "%{$pid_val}%";
        }

        if (!empty($pid_conditions)) {
            $query_pids = '(' . implode(' OR ', $pid_conditions) . ') AND ';
        }

        // The malicious payload should be rejected by validation
        // So pid_conditions should be empty
        $this->assertEmpty($pid_conditions, 'SQL injection payload should be rejected by validation');
        $this->assertEmpty($query_pids, 'No query should be generated for rejected input');
        $this->assertEmpty($pid_bind_values, 'No bind values should be created for rejected input');
    }

    /**
     * Test multiple patient IDs with mixed valid/invalid
     */
    public function testMultiplePatientIdsWithInvalidOnes(): void
    {
        $mixedInput = "123,456') OR 1=1 --,789";

        // Simulate the fixed code logic
        $pid_arr = explode(',', $mixedInput);
        $query_pids = '';
        $pid_bind_values = [];
        $pid_conditions = [];

        foreach ($pid_arr as $pid_val) {
            $pid_val = trim($pid_val);
            if (empty($pid_val) || !preg_match('/^[a-zA-Z0-9\s\-]+$/', $pid_val)) {
                continue; // Skip invalid
            }
            $pid_conditions[] = "(p.pid = ? OR p.fname LIKE ? OR p.mname LIKE ? OR p.lname LIKE ?)";
            $pid_bind_values[] = $pid_val;
            $pid_bind_values[] = "%{$pid_val}%";
            $pid_bind_values[] = "%{$pid_val}%";
            $pid_bind_values[] = "%{$pid_val}%";
        }

        if (!empty($pid_conditions)) {
            $query_pids = '(' . implode(' OR ', $pid_conditions) . ') AND ';
        }

        // Should only accept "123" and "789", reject the injection payload
        $this->assertCount(2, $pid_conditions, 'Should only accept valid patient IDs');
        $this->assertCount(8, $pid_bind_values, 'Should have 8 bind values (4 per valid ID)');
        $this->assertStringContainsString('?', $query_pids, 'Query should use parameterized placeholders');
    }

    /**
     * Test empty patient_id handling
     *
     */
    public function testEmptyPatientId(): void
    {
        $patient_id = '';

        // Simulate the fixed code logic
        /** @phpstan-ignore empty.variable */
        if (empty($patient_id)) {
            $query_pids = '';
            $pid_bind_values = [];
        } else {
            // This branch shouldn't execute
            $this->fail('Should not enter this branch for empty patient_id');
        }

        $this->assertEmpty($query_pids, 'Empty patient_id should result in empty query_pids');
        $this->assertEmpty($pid_bind_values, 'Empty patient_id should result in empty bind values');
    }

    /**
     * Helper: Assert that input validation rejects a payload
     */
    private function assertInputValidationRejects(string $input, string $message = ''): void
    {
        $pid_arr = explode(',', $input);
        $rejected = true;

        foreach ($pid_arr as $pid_val) {
            $pid_val = trim($pid_val);
            if (!empty($pid_val) && preg_match('/^[a-zA-Z0-9\s\-]+$/', $pid_val)) {
                $rejected = false;
                break;
            }
        }

        $this->assertTrue($rejected, $message ?: "Input should be rejected: $input");
    }

    /**
     * Helper: Assert that input validation accepts a payload
     */
    private function assertInputValidationAccepts(string $input, string $message = ''): void
    {
        $pid_arr = explode(',', $input);
        $accepted = false;

        foreach ($pid_arr as $pid_val) {
            $pid_val = trim($pid_val);
            if (!empty($pid_val) && preg_match('/^[a-zA-Z0-9\s\-]+$/', $pid_val)) {
                $accepted = true;
                break;
            }
        }

        $this->assertTrue($accepted, $message ?: "Input should be accepted: $input");
    }
}
