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
}
