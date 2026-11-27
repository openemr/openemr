<?php

/**
 * Keep every claim status a 277 reports in the index, with its source and claim numbers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use RuntimeException;

/**
 * Feeds ClaimStatusReader's reports to the index (#15530 step 2). Nothing is inferred:
 * dates that are not dates stay null, amounts stay as written, and pid/encounter are
 * filled only when the claim reference is exactly pid-encounter, as OpenEMR's 837 sends it.
 */
final class ClaimStatusIndexer
{
    public function __construct(private readonly ClaimStatusIndexStore $store)
    {
    }

    /**
     * @return bool true when the file's claims were newly kept
     * @throws RuntimeException when the file cannot be read or the store fails
     */
    public function indexFile(string $path): bool
    {
        $text = @file_get_contents($path);
        if ($text === false) {
            throw new RuntimeException('The 277 file could not be read: ' . $path);
        }
        return $this->indexText($text, basename($path));
    }

    /**
     * @param string $text     the whole content of an X12 file
     * @param string $fileName the name it is stored under in EDI History
     * @return bool false when this content is already indexed, or holds no 277 claim
     */
    public function indexText(string $text, string $fileName): bool
    {
        $sha = hash('sha256', $text);
        if ($this->store->isIndexed($sha)) {
            return false;
        }
        $reports = ClaimStatusReader::read($text);
        if ($reports === []) {
            return false;
        }
        $claims = [];
        foreach ($reports as $ordinal => $report) {
            $claims[] = self::claim($report, $fileName, $sha, $ordinal);
        }
        $this->store->recordFile($claims);
        return true;
    }

    /**
     * Index every file in an EDI History 277 folder that is not indexed yet, including
     * ones a site already had. One file failing does not stop the rest.
     *
     * @return array{indexed: int, skipped: int, failed: int}
     */
    public function indexDirectory(string $directory): array
    {
        $summary = ['indexed' => 0, 'skipped' => 0, 'failed' => 0];
        if (!is_dir($directory)) {
            return $summary;
        }
        $names = scandir($directory) ?: [];
        foreach ($names as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            if (!is_file($path)) {
                continue;
            }
            try {
                if ($this->indexFile($path)) {
                    $summary['indexed']++;
                } else {
                    $summary['skipped']++;
                }
            } catch (RuntimeException) {
                $summary['failed']++;
            }
        }
        return $summary;
    }

    /**
     * @return array{ack: array<string, mixed>, statuses: list<array<string, mixed>>, references: list<array{qualifier: string, ref_value: string}>}
     */
    private static function claim(ClaimStatusReport $report, string $fileName, string $sha, int $ordinal): array
    {
        $pid = 0;
        $encounter = 0;
        if (preg_match('/^(\d+)-(\d+)$/', trim($report->claimReference), $m) === 1) {
            $pid = (int) $m[1];
            $encounter = (int) $m[2];
        }
        $ack = [
            'file_sha256' => $sha,
            'file_name' => $fileName,
            'block_ordinal' => $ordinal,
            'interchange_control_number' => $report->interchangeControlNumber,
            'transaction_control_number' => $report->transactionControlNumber,
            'interchange_date' => $report->interchangeDate?->format('Y-m-d'),
            'claim_reference' => $report->claimReference,
            'pid' => $pid,
            'encounter' => $encounter,
            'patient_last_name' => $report->patientLastName,
            'patient_first_name' => $report->patientFirstName,
            'service_date_from' => $report->serviceDateFrom?->format('Y-m-d'),
            'service_date_to' => $report->serviceDateTo?->format('Y-m-d'),
            'source_entity_code' => $report->source->entityCode,
            'source_kind' => $report->source->kind()->name,
            'source_name' => $report->source->name,
            'source_id_qualifier' => $report->source->identifierQualifier,
            'source_id' => $report->source->identifier,
            'source_received_date' => $report->sourceReceivedDate?->format('Y-m-d'),
        ];
        $statuses = [];
        foreach ($report->statuses as $position => $status) {
            $statuses[] = [
                'position' => $position,
                'category' => $status->category,
                'code' => $status->code,
                'entity_code' => $status->entityCode,
                'effective_date' => $status->effectiveDate?->format('Y-m-d'),
                'action_code' => $status->actionCode,
                'amount' => $status->amount,
                'status_text' => $status->text,
                'service_line' => $status->serviceLine,
                'rejected' => (int) $status->isRejected(),
                'accepted' => (int) $status->isAccepted(),
            ];
        }
        $references = [];
        foreach ($report->references as $qualifier => $values) {
            foreach ($values as $value) {
                $references[] = ['qualifier' => (string) $qualifier, 'ref_value' => $value];
            }
        }
        return ['ack' => $ack, 'statuses' => $statuses, 'references' => $references];
    }
}