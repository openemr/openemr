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

        $message = $result->emailFailures === [] ? '' : implode('; ', $result->emailFailures);
        if ($result->portalError === StatementRunResult::PORTAL_NOT_AUTHORIZED) {
            $message = xlt('Notification FAILED: Not Portal Authorized');
        } elseif ($result->portalError === StatementRunResult::PORTAL_FAILED) {
            $message = xlt('Notification FAILED');
        }
        // Patients who got no statement: nothing due after their credits, or under the print minimum.
        $skipped = $result->skippedNothingDue === [] ? '' : xl('No statement for') . ' ' . implode(', ', $result->skippedNothingDue)
            . ': ' . xl('nothing is due after their credits.');
        if ($result->skippedBelowMinimum !== []) {
            $skipped = trim($skipped . ' ' . xl('No statement for') . ' ' . implode(', ', $result->skippedBelowMinimum)
                . ': ' . xl('the amount due does not meet the minimum amount to print set in Globals.'));
        }

        if ($result->statementsWritten === 0 && $skipped !== '' && $delivery !== StatementDelivery::Portal) {
            // Every checked patient was skipped: say so rather than hand back an empty file.
            return $skipped;
        }
        return match ($delivery) {
            StatementDelivery::Download => $this->download($message),
            StatementDelivery::Pdf => $this->downloadPdf($message, $result->onlyPatient),
            StatementDelivery::Portal => $message === '' ? xl('Sending Invoice to Patient Portal Completed') : $message,
            // An email run is sent to the printer too, as it always has been.
            StatementDelivery::Print, StatementDelivery::Email => $this->print($result->statementsWritten, $withoutUpdate, $test, $skipped),
        };
    }

    private function download(string $message): string
    {
        $this->output->download($this->batchFile);
        return $message;
    }

    /**
     * @param ?array{firstName: string, pid: int} $onlyPatient
     */
    private function downloadPdf(string $message, ?array $onlyPatient): string
    {
        $this->output->downloadPdf($this->batchFile, $onlyPatient);
        return $message;
    }

    private function print(int $written, bool $withoutUpdate, bool $test, string $skipped): string
    {
        if ($test) {
            return xl('Printing skipped; see test output in') . ' ' . $this->batchFile;
        }
        $error = $this->output->print($this->batchFile);
        if ($error !== null) {
            // The run already wrote the batch, and updated the invoices unless told not to.
            $message = xl('Printing failed') . ': ' . $error . ' ' . xl('The statements are in') . ' ' . $this->batchFile . '.'
                . ($withoutUpdate ? '' : ' ' . xl('The invoices were updated.'));
            return $skipped === '' ? $message : $message . ' ' . $skipped;
        }
        $message = xl('Now printing') . ' ' . $written . ' '
            . ($withoutUpdate ? xl('statements; invoices will not be updated.') : xl('statements and updating invoices.'));
        return $skipped === '' ? $message : $message . ' ' . $skipped;
    }
}
