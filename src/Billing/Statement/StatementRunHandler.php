<?php

/**
 * A statement run as a page asks for it: run, write the batch, send it on, and
 * say what happened.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

final readonly class StatementRunHandler
{
    /**
     * @param string $batchFile where the batch is written ($STMT_TEMP_FILE in the site's statement.inc.php)
     */
    public function __construct(private StatementRun $run, private StatementOutput $output, private string $batchFile)
    {
    }

    /**
     * A download hands the file to the browser and ends the request; otherwise
     * this returns the message for the biller ('' for none).
     *
     * @param list<array<string, mixed>> $invoices StatementInvoices::forRun() for the checked invoices
     * @param bool $withoutUpdate don't count the invoices as statemented
     * @param bool $test the page's test mode: nothing is recorded or printed
     * @param bool $skipNothingDue a Due Pt run: send nothing to a patient whose credits leave nothing due
     * @param string $today Y-m-d
     */
    public function handle(array $invoices, StatementDelivery $delivery, bool $withoutUpdate, bool $test, bool $skipNothingDue, string $today): string
    {
        $result = $this->run->run($invoices, $delivery, !$test && !$withoutUpdate, $skipNothingDue, $today);
        file_put_contents($this->batchFile, $result->output);

        // Patients who got no statement: nothing due after their credits, or under the print minimum.
        $skipped = $result->skippedNothingDue === [] ? '' : xl('No statement for') . ' ' . implode(', ', $result->skippedNothingDue)
            . ': ' . xl('nothing is due after their credits.');
        if ($result->skippedBelowMinimum !== []) {
            $skipped = self::join($skipped, xl('No statement for') . ' ' . implode(', ', $result->skippedBelowMinimum)
                . ': ' . xl('the amount due does not meet the minimum amount to print set in Globals.'));
        }

        if ($delivery === StatementDelivery::Portal) {
            return match ($result->portalError) {
                StatementRunResult::PORTAL_NOT_AUTHORIZED => xl('Notification FAILED: Not Portal Authorized'),
                StatementRunResult::PORTAL_FAILED => xl('Notification FAILED'),
                default => xl('Sending Invoice to Patient Portal Completed'),
            };
        }
        if ($delivery === StatementDelivery::Email) {
            // An email run only emails; nothing goes to the printer.
            $notEmailed = $result->emailFailures === [] ? '' : xl('Not emailed') . ': ' . implode('; ', $result->emailFailures) . '.';
            $emailed = $result->statementsWritten === 0 && ($notEmailed !== '' || $skipped !== '') ? '' : xl('Emailed') . ' '
                . $result->statementsWritten . ' ' . self::invoiceUpdate($withoutUpdate, $test);
            return self::join($emailed, $notEmailed, $skipped);
        }
        if ($result->statementsWritten === 0 && $skipped !== '') {
            // Every checked patient was skipped: say so rather than hand back an empty file.
            return $skipped;
        }
        return match ($delivery) {
            StatementDelivery::Download => $this->output->download($this->batchFile),
            StatementDelivery::Pdf => $this->output->downloadPdf($this->batchFile, $result->onlyPatient),
            StatementDelivery::Print => $this->print($result->statementsWritten, $withoutUpdate, $test, $skipped),
        };
    }

    private function print(int $written, bool $withoutUpdate, bool $test, string $skipped): string
    {
        if ($test) {
            return self::join(xl('Printing skipped; see test output in') . ' ' . $this->batchFile, $skipped);
        }
        if (!$this->output->print($this->batchFile)) {
            // The run already wrote the batch, and updated the invoices unless told not to.
            return self::join(
                xl('Printing failed; the details are in the error log.') . ' ' . xl('The statements are in') . ' ' . $this->batchFile . '.'
                . ($withoutUpdate ? '' : ' ' . xl('The invoices were updated.')),
                $skipped
            );
        }
        return self::join(xl('Now printing') . ' ' . $written . ' ' . self::invoiceUpdate($withoutUpdate, $test), $skipped);
    }

    private static function invoiceUpdate(bool $withoutUpdate, bool $test): string
    {
        return $withoutUpdate || $test ? xl('statements; invoices will not be updated.') : xl('statements and updating invoices.');
    }

    private static function join(string ...$parts): string
    {
        return implode(' ', array_filter($parts, static fn(string $part): bool => $part !== ''));
    }
}
