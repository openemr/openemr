<?php

/**
 * Default HL7 result parser delegating to existing procedural functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Orders;

class DefaultHl7ResultParser implements Hl7ResultParserInterface
{
    /**
     * @inheritDoc
     */
    public function receiveResults(
        string &$hl7,
        array &$matchReq,
        int $labId = 0,
        string $direction = 'B',
        bool $dryRun = false,
        ?array $matchResp = null
    ): Hl7ResultParseResult {
        // @phpstan-ignore argument.type, argument.type, parameterByRef.type (procedural function PHPDoc is mistyped)
        $result = receive_hl7_results($hl7, $matchReq, $labId, $direction, $dryRun, $matchResp);
        /** @var array<string, mixed> $result */
        return Hl7ResultParseResult::fromLegacyArray($result);
    }

    /**
     * @inheritDoc
     */
    public function pollResults(array &$info, int $labs = 0): string
    {
        return poll_hl7_results($info, $labs);
    }
}
