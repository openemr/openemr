<?php

/**
 * What a stored remittance file adds to the history: its content, and its adjustments.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use OpenEMR\Billing\ParseERA;
use RuntimeException;

/**
 * The sha256 lets the same remittance stored under two names be listed once; the
 * adjustments are keyed by file and our own claim reference (CLP01), so they attach to
 * the claim they belong to (#15530 step 3). A file that cannot be parsed still gets its
 * hash, and the CSV columns still show.
 */
final class RemittanceDetail
{
    /**
     * @param list<string>  $fileNames names as claims_f835.csv holds them
     * @param ?callable   $parser    fn(string $path, callable $onClaim): void; defaults to ParseERA
     * @return array{sha: array<string, string>, adjustments: array<string, string>}
     */
    public static function forFiles(string $directory, array $fileNames, ?callable $parser = null): array
    {
        $parser ??= static function (string $path, callable $onClaim): void {
            // ParseERA writes its complaints; the history only wants the parsed claims.
            ob_start();
            try {
                ParseERA::parseERA($path, static function (array $out) use ($onClaim): void {
                    $onClaim($out);
                });
            } finally {
                ob_end_clean();
            }
        };

        $sha = [];
        $adjustments = [];
        foreach ($fileNames as $name) {
            if ($name === '' || isset($sha[$name])) {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            if (!is_file($path)) {
                continue;
            }
            $hash = @hash_file('sha256', $path);
            if ($hash === false) {
                continue;
            }
            $sha[$name] = $hash;
            try {
                $parser($path, static function (array $out) use (&$adjustments, $name): void {
                    $reference = trim((string) ($out['our_claim_id'] ?? ''));
                    if ($reference === '') {
                        return;
                    }
                    $text = EncounterHistorySources::adjustmentsAndRemarks($out);
                    if ($text !== '') {
                        $adjustments[$name . "\0" . $reference] = $text;
                    }
                });
            } catch (RuntimeException) {
                // Keep the hash; the claim still appears from the CSV row.
            }
        }
        return ['sha' => $sha, 'adjustments' => $adjustments];
    }
}