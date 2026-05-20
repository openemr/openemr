<?php

/**
 * Isolated tests for ValueMapping::mapPayerResponsibility().
 *
 * The function is called from every eligibility flow to translate the
 * 'primary'/'secondary'/'tertiary' string the UI sends into the single-letter
 * 'p'/'s'/'t' code OpenEMR's insurance_data table stores. If the mapping
 * shifts (e.g. drops a case branch or changes a letter) every eligibility
 * lookup silently misses the right insurance record. These tests pin the
 * contract.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\ValueMapping;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/ValueMapping.php';

class ValueMappingTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function payerResponsibilityProvider(): array
    {
        return [
            'primary lower'      => ['primary', 'p'],
            'secondary lower'    => ['secondary', 's'],
            'tertiary lower'     => ['tertiary', 't'],
            // Case insensitivity matters because the AJAX endpoints
            // sometimes pass capitalized values from the UI.
            'PRIMARY upper'      => ['PRIMARY', 'p'],
            'Secondary mixed'    => ['Secondary', 's'],
            'TERTIARY upper'     => ['TERTIARY', 't'],
            // Unknown values fall through to the first character so an
            // already-coded value like 'p' or 'q' round-trips unchanged.
            'already coded p'    => ['p', 'p'],
            'already coded s'    => ['s', 's'],
            'unknown letter'     => ['q', 'q'],
            'multi-char unknown' => ['quaternary', 'q'],
        ];
    }

    #[DataProvider('payerResponsibilityProvider')]
    public function testMapPayerResponsibility(string $input, string $expected): void
    {
        $this->assertSame($expected, ValueMapping::mapPayerResponsibility($input));
    }

    public function testEmptyStringReturnsEmptyString(): void
    {
        // Edge: empty input → substr returns '' (the regression guard
        // for callers that filter on $pr !== '' before using it).
        $this->assertSame('', ValueMapping::mapPayerResponsibility(''));
    }
}
