<?php

/**
 * Interface for HL7 result parsing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Orders;

interface Hl7ResultParserInterface
{
    /**
     * Parse an HL7 result message and store results in the database.
     *
     * By-reference parameters are preserved because the procedural implementation
     * normalizes $hl7 in-place and accumulates state in $matchReq.
     *
     * @param string      $hl7       The raw HL7 message text (normalized in-place)
     * @param array<mixed> $matchReq  Accumulates patient matching requests
     * @param int         $labId     Procedure provider ID (0 = all)
     * @param string      $direction Direction: 'B' = both, 'R' = results only
     * @param bool        $dryRun    If true, parse without saving
     * @param array<mixed>|null $matchResp Previously submitted match responses
     * @return Hl7ResultParseResult Parsed result with messages and status flags
     */
    public function receiveResults(
        string &$hl7,
        array &$matchReq,
        int $labId = 0,
        string $direction = 'B',
        bool $dryRun = false,
        ?array $matchResp = null
    ): Hl7ResultParseResult;

    /**
     * Poll all eligible labs for new results and store them in the database.
     *
     * By-reference $info is preserved because the procedural implementation
     * uses it bidirectionally (reads match responses, writes match requests).
     *
     * @param array<mixed> $info Conveys information to and from the caller
     * @param int          $labs Procedure provider ID to filter (0 = all)
     * @return string Error message, or empty string on success
     */
    public function pollResults(array &$info, int $labs = 0): string;
}
