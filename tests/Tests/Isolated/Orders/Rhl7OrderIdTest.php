<?php

/**
 * Isolated tests for HL7 placer order number parsing logic.
 *
 * Verifies that compound placer order numbers (ORC-2 / OBR-2) are correctly
 * parsed to extract the procedure_order_id. The expression under test is:
 *
 *     str_contains($s, '-')
 *         ? intval(substr($s, strrpos($s, '-') + 1))
 *         : intval($s);
 *
 * This replaces the previous intval($a[2]) which truncated compound IDs
 * at the first non-numeric character.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @see https://github.com/openemr/openemr/issues/8130
 * @see https://github.com/openemr/openemr/issues/7762
 */

namespace OpenEMR\Tests\Isolated\Orders;

use PHPUnit\Framework\TestCase;

class Rhl7OrderIdTest extends TestCase
{
    /**
     * Replicate the inline expression used in receive_hl7_results.inc.php
     * for extracting procedure_order_id from compound placer order numbers.
     */
    private function extractOrderId(string $s): int
    {
        return str_contains($s, '-')
            ? intval(substr($s, strrpos($s, '-') + 1))
            : intval($s);
    }

    /**
     * Plain numeric string — backward compatible with previous intval() behavior.
     */
    public function testPlainNumericId(): void
    {
        $this->assertSame(175, $this->extractOrderId('175'));
    }

    /**
     * Zero-padded numeric string — intval strips leading zeros correctly.
     */
    public function testZeroPaddedNumericId(): void
    {
        $this->assertSame(175, $this->extractOrderId('0175'));
    }

    /**
     * Quest compound format: control_number-order_id.
     * Previously intval('11545596-0175') returned 11545596 (wrong).
     *
     * @see https://github.com/openemr/openemr/issues/8130
     */
    public function testQuestCompoundId(): void
    {
        $this->assertSame(175, $this->extractOrderId('11545596-0175'));
    }

    /**
     * Universal HL7 generator format: facility_id-order_id.
     * Previously intval('ACME-0042') returned 0 (wrong).
     *
     * @see https://github.com/openemr/openemr/issues/7762
     */
    public function testFacilityPrefixedId(): void
    {
        $this->assertSame(42, $this->extractOrderId('ACME-0042'));
    }

    /**
     * Multiple dashes — extract after the last dash.
     */
    public function testMultipleDashes(): void
    {
        $this->assertSame(99, $this->extractOrderId('LAB-QUEST-0099'));
    }

    /**
     * Empty string returns 0, consistent with intval('').
     */
    public function testEmptyString(): void
    {
        $this->assertSame(0, $this->extractOrderId(''));
    }

    /**
     * Non-numeric string without dash returns 0, consistent with intval('abc').
     */
    public function testNonNumericNoDash(): void
    {
        $this->assertSame(0, $this->extractOrderId('abc'));
    }

    /**
     * Dash with non-numeric suffix returns 0.
     */
    public function testDashWithNonNumericSuffix(): void
    {
        $this->assertSame(0, $this->extractOrderId('123-abc'));
    }

    /**
     * Single digit after dash.
     */
    public function testSingleDigitAfterDash(): void
    {
        $this->assertSame(5, $this->extractOrderId('PREFIX-5'));
    }
}
