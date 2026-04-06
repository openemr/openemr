<?php

/**
 * PortalPaymentInputTest - Unit tests for filter_input() usage in portal_payment.php.
 *
 * AI-Generated Code Notice: This file contains code generated with
 * assistance from Claude Code (Anthropic). The code has been reviewed
 * and tested by the contributor.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Allen <craigrallen@gmail.com>
 * @copyright Copyright (c) 2026 Craig Allen <craigrallen@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Portal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the input-validation logic extracted from portal_payment.php.
 *
 * Because portal_payment.php is a legacy script (not a class), we mirror the
 * prepayment-validation logic inline so we can unit-test it without bootstrapping
 * the full OpenEMR stack.
 */
class PortalPaymentInputTest extends TestCase
{
    /**
     * Mirrors the form_prepayment validation logic from portal_payment.php.
     *
     * @param mixed $raw Raw value as received from filter_input()
     * @return float Sanitized, non-negative float
     */
    private function validatePrepayment(mixed $raw): float
    {
        $filtered = filter_var($raw, FILTER_VALIDATE_FLOAT);
        if ($filtered === false || $filtered === null || $filtered < 0.0) {
            return 0.0;
        }
        return $filtered;
    }

    // -------------------------------------------------------------------------
    // form_prepayment validation (#11393)
    // -------------------------------------------------------------------------

    #[Test]
    public function testValidPositivePrepaymentPassesThrough(): void
    {
        $this->assertSame(12.50, $this->validatePrepayment('12.50'));
    }

    #[Test]
    public function testZeroPrepaymentPassesThrough(): void
    {
        $this->assertSame(0.0, $this->validatePrepayment('0'));
    }

    #[Test]
    public function testNegativePrepaymentFallsBackToZero(): void
    {
        $this->assertSame(0.0, $this->validatePrepayment('-5.00'));
    }

    #[Test]
    public function testNonNumericPrepaymentFallsBackToZero(): void
    {
        $this->assertSame(0.0, $this->validatePrepayment('not-a-number'));
    }

    #[Test]
    public function testEmptyStringPrepaymentFallsBackToZero(): void
    {
        $this->assertSame(0.0, $this->validatePrepayment(''));
    }

    #[Test]
    public function testNullPrepaymentFallsBackToZero(): void
    {
        $this->assertSame(0.0, $this->validatePrepayment(null));
    }

    #[Test]
    public function testFalsePrepaymentFallsBackToZero(): void
    {
        $this->assertSame(0.0, $this->validatePrepayment(false));
    }

    /**
     * @return array<string, array{string, float}>
     */
    public static function validPrepaymentProvider(): array
    {
        return [
            'integer string'    => ['100', 100.0],
            'decimal string'    => ['9.99', 9.99],
            'large amount'      => ['9999.99', 9999.99],
            'zero decimal'      => ['0.00', 0.0],
        ];
    }

    #[Test]
    #[DataProvider('validPrepaymentProvider')]
    public function testValidPrepaymentValues(string $input, float $expected): void
    {
        $this->assertSame($expected, $this->validatePrepayment($input));
    }

    // -------------------------------------------------------------------------
    // filter_input() patterns (#11392) — verify PHP built-in behaviour
    // -------------------------------------------------------------------------

    #[Test]
    public function testFilterInputSanitizeSpecialCharsStripsHtml(): void
    {
        // FILTER_SANITIZE_SPECIAL_CHARS converts < > " ' to HTML entities;
        // the result must not contain raw angle brackets.
        $unsafe = '<script>alert(1)</script>';
        $sanitized = filter_var($unsafe, FILTER_SANITIZE_SPECIAL_CHARS);
        $this->assertStringNotContainsString('<script>', (string) $sanitized);
    }

    #[Test]
    public function testFilterInputValidateIntRejectsFloat(): void
    {
        $result = filter_var('3.14', FILTER_VALIDATE_INT);
        $this->assertFalse($result, 'FILTER_VALIDATE_INT must reject float strings');
    }

    #[Test]
    public function testFilterInputValidateIntAcceptsIntegerString(): void
    {
        $result = filter_var('42', FILTER_VALIDATE_INT);
        $this->assertSame(42, $result);
    }

    #[Test]
    public function testFilterInputValidateIntRejectsNonNumeric(): void
    {
        $result = filter_var('abc', FILTER_VALIDATE_INT);
        $this->assertFalse($result);
    }
}
