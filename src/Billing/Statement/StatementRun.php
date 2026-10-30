<?php

/**
 * A patient statement run: the checked invoices, one statement per patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

/**
 * Takes the checked invoices (StatementInvoices::forRun(), ordered so each
 * patient's invoices are together) and builds one statement per patient:
 * every invoice's lines, then the patient's credits on other encounters so the
 * amount due nets them. A Due Pt run sends nothing to a patient left with
 * nothing due. Statements go to the portal, or into one batch to print or
 * download, each also saved to the patient's documents and, for an email run,
 * emailed. Invoices are counted as statemented only for statements that went
 * out, and only when the run records (not Without Update).
 */
final readonly class StatementRun
{
    public function __construct(private StatementRunSteps $steps)
    {
    }

    /**
     * @param list<array<string, mixed>> $invoices
     * @param bool $recordRun count the invoices as statemented (not Without Update)
     * @param bool $skipNothingDue send nothing to a patient whose credits leave nothing due (Due Pt)
     * @param string $today Y-m-d
     */
    public function run(
        array $invoices,
        StatementDelivery $delivery,
        bool $recordRun,
        bool $skipNothingDue,
        string $today,
    ): StatementRunResult {
        $checkedIds = [];
        foreach ($invoices as $invoice) {
            $checkedIds[] = self::int($invoice, 'id');
        }

        $output = '';
        $written = 0;
        $skipped = [];
        $emailFailures = [];

        foreach (self::byPatient($invoices) as $pid => $patientInvoices) {
            [$stmt, $encounterIds, $snapshots] = $this->buildPatientStatement(
                $pid,
                $patientInvoices,
                $checkedIds,
                $today,
                $delivery === StatementDelivery::Portal
            );
            $patient = is_string($stmt['patient'] ?? null) ? $stmt['patient'] : '';
            $toRecord = $recordRun ? $encounterIds : [];

            if ($delivery === StatementDelivery::Portal) {
                if (!$this->steps->isPortalAuthorized($pid)) {
                    return $this->result($output, $written, $skipped, $emailFailures, StatementRunResult::PORTAL_NOT_AUTHORIZED, $invoices);
                }
                $statement = $this->steps->buildStatement($stmt);
                $invoiceId = $pid . '-' . self::string($stmt, 'encounter');
                if (!$this->steps->notifyPortal($pid, $snapshots, $statement, $invoiceId)) {
                    return $this->result($output, $written, $skipped, $emailFailures, StatementRunResult::PORTAL_FAILED, $invoices);
                }
                // Count the invoices as statemented only once the portal has the statement.
                if ($toRecord !== []) {
                    $this->steps->recordStatementRun($toRecord);
                }
                continue;
            }

            $amountDue = is_numeric($stmt['amount'] ?? null) ? (float) $stmt['amount'] : 0.0;
            if ($skipNothingDue && $amountDue <= 0) {
                // Nothing is due once the credits are netted, so no statement goes
                // out and the invoices aren't counted as statemented.
                $skipped[] = $patient;
                continue;
            }

            if ($toRecord !== []) {
                $this->steps->recordStatementRun($toRecord);
            }
            $statement = $this->steps->buildStatement($stmt);
            if ($delivery === StatementDelivery::Email && $statement !== '') {
                try {
                    $this->steps->email($pid, $statement);
                } catch (\RuntimeException | \InvalidArgumentException $e) {
                    $emailFailures[] = $patient . ': ' . $e->getMessage();
                }
            }
            if ($statement === '') {
                $statement = $this->steps->emptyStatementNotice($pid, self::string($stmt, 'encounter'));
            }
            $output .= $statement;
            $written++;
            $this->steps->saveDocument($pid, $statement);
        }

        return $this->result($output, $written, $skipped, $emailFailures, null, $invoices);
    }

    /**
     * One patient's statement: header from the first invoice, every invoice's
     * lines, then the credits on the patient's other encounters.
     *
     * @param non-empty-list<array<string, mixed>> $invoices
     * @param list<int> $checkedIds
     * @return array{array<string, mixed>, list<int>, list<array<string, mixed>>} the statement, the
     *     invoices it covers (form_encounter.id) and, for the portal, the statement after each invoice
     */
    private function buildPatientStatement(int $pid, array $invoices, array $checkedIds, string $today, bool $keepSnapshots): array
    {
        $stmt = [];
        $encounterIds = [];
        $snapshots = [];
        $last = count($invoices) - 1;
        foreach ($invoices as $i => $invoice) {
            $serviceDate = substr(self::string($invoice, 'date'), 0, 10);
            if ($i === 0) {
                $stmt = [
                    'fid' => $invoice['id'] ?? null,
                    'cid' => $invoice['pid'] ?? null,
                    'pid' => $invoice['pid'] ?? null,
                    'dun_count' => $invoice['stmt_count'] ?? null,
                    'bill_note' => $invoice['pat_billing_note'] ?? null,
                    'enc_bill_note' => $invoice['enc_billing_note'] ?? null,
                    'bill_level' => $invoice['last_level_billed'] ?? null,
                    'level_closed' => $invoice['last_level_closed'] ?? null,
                    'patient' => self::string($invoice, 'fname') . ' ' . self::string($invoice, 'lname'),
                    'encounter' => $invoice['encounter'] ?? null,
                    'provider_id' => $invoice['provider_id'] ?? null,
                    // To the guardian (guarantor) in the patient's demographics when there is one,
                    // at their address when it is filled in; otherwise to the patient.
                    'to' => StatementRecipient::lines($invoice),
                    'lines' => [],
                    'amount' => '0.00',
                    'ins_paid' => 0,
                    'today' => $today,
                    'duedate' => $serviceDate,
                ];
            } elseif ($serviceDate < self::string($stmt, 'duedate')) {
                // Report the oldest due date.
                $stmt['duedate'] = $serviceDate;
            }
            $stmt['age'] = round((strtotime($today) - strtotime(self::string($stmt, 'duedate'))) / (24 * 60 * 60));

            $this->steps->appendInvoice($stmt, $pid, self::int($invoice, 'encounter'), $serviceDate, self::int($invoice, 'stmt_count') + 1);
            $encounterIds[] = self::int($invoice, 'id');

            if ($i === $last) {
                // The patient's other encounters that hold a credit (overpaid, never
                // listed as due, so never checked) go on as their own dated lines, so
                // the amount due nets them instead of billing the open encounters in
                // full. Their statement counts are left alone.
                foreach ($this->steps->creditEncounters($pid, $checkedIds) as $credit) {
                    $this->steps->appendInvoice($stmt, $pid, $credit['encounter'], substr($credit['date'], 0, 10), $credit['stmt_count'] + 1);
                }
            }
            if ($keepSnapshots) {
                $snapshots[] = $stmt;
            }
        }
        return [$stmt, $encounterIds, $snapshots];
    }

    /**
     * The invoices grouped by patient, in the order they came (each patient's
     * invoices are together in StatementInvoices::forRun()).
     *
     * @param list<array<string, mixed>> $invoices
     * @return array<int, non-empty-list<array<string, mixed>>>
     */
    private static function byPatient(array $invoices): array
    {
        $groups = [];
        foreach ($invoices as $invoice) {
            $groups[self::int($invoice, 'pid')][] = $invoice;
        }
        return $groups;
    }

    /**
     * @param list<string> $skipped
     * @param list<string> $emailFailures
     * @param list<array<string, mixed>> $invoices
     */
    private function result(string $output, int $written, array $skipped, array $emailFailures, ?string $portalError, array $invoices): StatementRunResult
    {
        $pids = array_unique(array_map(static fn(array $invoice): int => self::int($invoice, 'pid'), $invoices));
        $onlyPatient = null;
        if (count($pids) === 1 && $invoices !== []) {
            $onlyPatient = ['firstName' => self::string($invoices[0], 'fname'), 'pid' => self::int($invoices[0], 'pid')];
        }
        return new StatementRunResult($output, $written, $skipped, $emailFailures, $portalError, $onlyPatient);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function int(array $row, string $key): int
    {
        return is_numeric($row[$key] ?? null) ? (int) $row[$key] : 0;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function string(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '';
    }
}
