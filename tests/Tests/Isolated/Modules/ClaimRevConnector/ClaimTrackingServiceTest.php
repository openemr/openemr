<?php

/**
 * Isolated tests for ClaimTrackingService::parsePcn().
 *
 * The parser is the boundary where ClaimRev's patient control number
 * string ("pid-encounter") is split into the (int, int) tuple every
 * downstream tracking call needs. Bad parsing posts to the wrong patient
 * or encounter — so the rules around delimiter handling, leading-zero
 * preservation, and rejection of malformed inputs are pinned here.
 *
 * Mirrors the equivalent test in PaymentAdvicePostingServiceTest::
 * testParsePatientControlNumber() because the two methods implement
 * the same contract on independent code paths; if either drifts,
 * cross-service status sync silently misroutes records.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\ClaimTrackingService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/TypeCoerce.php';
require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/ClaimTrackingService.php';

class ClaimTrackingServiceTest extends TestCase
{
    /**
     * @return array<string, array{string, array{pid: int, encounter: int}|null}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pcnProvider(): array
    {
        return [
            'hyphen separator'   => ['1-101',  ['pid' => 1,   'encounter' => 101]],
            'space separator'    => ['1 101',  ['pid' => 1,   'encounter' => 101]],
            'multi-digit pid'    => ['12345-67890', ['pid' => 12345, 'encounter' => 67890]],
            'extra trailing parts ignored' => ['1-101-extra', ['pid' => 1, 'encounter' => 101]],
            // Rejection cases — all need a non-null guard at the call site.
            'no separator'       => ['1101',  null],
            'empty string'       => ['',      null],
            'whitespace only'    => ['   ',   null],
            'zero pid'           => ['0-101', null],
            'zero encounter'     => ['1-0',   null],
            'negative pid'       => ['-1-101', null],
            'non-numeric pid'    => ['abc-101', null], // (int)'abc' === 0
        ];
    }

    /**
     * @param array{pid: int, encounter: int}|null $expected
     */
    #[DataProvider('pcnProvider')]
    public function testParsePcn(string $input, ?array $expected): void
    {
        $this->assertSame($expected, ClaimTrackingService::parsePcn($input));
    }
}
