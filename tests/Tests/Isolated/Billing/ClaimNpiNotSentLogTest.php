<?php

/**
 * An NPI that was not sent is logged as missing only when the field is empty.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClaimNpiNotSentLogTest extends TestCase
{
    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function npiNotSentProvider(): array
    {
        return [
            'empty' => ['', "*** Referring provider has no NPI.\n"],
            'null' => [null, "*** Referring provider has no NPI.\n"],
            'zero string' => ['0', "*** Referring provider has no NPI.\n"],
            'zero integer' => [0, "*** Referring provider has no NPI.\n"],
            'whitespace' => ['   ', "*** Referring provider has no NPI.\n"],
            'letter' => ['123456789A', "*** Referring provider NPI \"123456789A\" is not valid.\n"],
            'qualifier stored as npi' => ['XX', "*** Referring provider NPI \"XX\" is not valid.\n"],
            'padded invalid' => [' 123456789A ', "*** Referring provider NPI \"123456789A\" is not valid.\n"],
        ];
    }

    /**
     * A rejected stored NPI is named in the log; an empty field reads as missing.
     */
    #[DataProvider('npiNotSentProvider')]
    public function testNpiNotSentLog(string|null $raw, string $expected): void
    {
        $this->assertSame($expected, Claim::npiNotSentLog('Referring provider', $raw));
    }
}
