<?php

/**
 * UB04 physician NPIs are digits before they are written to 837I.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\X125010837I;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class X125010837IPhysicianNpiTest extends TestCase
{
    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function physicianNpiProvider(): array
    {
        return [
            "hyphen" => ["1234-567893", "1234567893"],
            "spaces" => ["123 456 7893", "1234567893"],
            "integer" => [1234567893, "1234567893"],
            "qualifier is not an npi" => ["XX", ""],
            "letter" => ["123456789A", ""],
            "zero string" => ["0", ""],
            "zero integer" => [0, ""],
            "padded zero" => [" 0 ", ""],
            "empty" => ["", ""],
            "missing" => [null, ""],
        ];
    }

    /**
     * A physician NPI is digits. A qualifier or any other character is blank.
     */
    #[DataProvider("physicianNpiProvider")]
    public function testPhysicianNpi(mixed $value, string $digits): void
    {
        $this->assertSame($digits, X125010837I::physicianNpi($value));
    }

    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function physicianQualifierProvider(): array
    {
        return [
            "xx" => ["XX", "XX"],
            "zero string" => ["0", ""],
            "zero integer" => [0, ""],
            "empty" => ["", ""],
            "missing" => [null, ""],
        ];
    }

    /**
     * A physician qualifier is kept. A zero placeholder is absent.
     */
    #[DataProvider("physicianQualifierProvider")]
    public function testPhysicianQualifier(mixed $value, string $qualifier): void
    {
        $this->assertSame($qualifier, X125010837I::physicianQualifier($value));
    }

    /**
     * The 837I generator writes a hyphenated physician NPI as digits in NM109.
     */
    public function testHyphenatedPhysicianNpiIsDigitsInTheSegment(): void
    {
        $this->assertSame('*XX*1234567893', X125010837I::physicianIdentifier('XX', '1234-567893'));
        $this->assertSame('**', X125010837I::physicianIdentifier('', '1234-567893'));
        $this->assertSame('**', X125010837I::physicianIdentifier('XX', '0'));

        $institutional = file_get_contents(__DIR__ . '/../../../../src/Billing/X125010837I.php');
        $batch = file_get_contents(
            __DIR__ . '/../../../../src/Billing/BillingProcessor/Tasks/GeneratorUB04X12.php'
        );
        $this->assertIsString($institutional);
        $this->assertIsString($batch);
        $this->assertStringContainsString('generateX12837I(', $batch);
        $this->assertStringContainsString("physicianIdentifier('XX', \$ub04id[379]", $institutional);
        $this->assertStringContainsString("physicianIdentifier('XX', \$ub04id[390]", $institutional);
        $this->assertStringContainsString('physicianQualifier($ub04id[405]', $institutional);
        $this->assertStringContainsString('$ub04id[406]', $institutional);
        $this->assertStringContainsString('physicianQualifier($ub04id[419]', $institutional);
        $this->assertStringContainsString('$ub04id[420]', $institutional);
    }
}
