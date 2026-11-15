<?php

/**
 * The statement run's steps as the statements pages take them: the site's
 * statement layout, the document store, email and the portal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use Mpdf\Mpdf;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Pdf\Config_Mpdf;

final readonly class PageStatementRunSteps implements StatementRunSteps
{
    /**
     * @param \Closure(array<string, mixed>): string $buildStatement make_statement() from the site's statement.inc.php
     * @param bool $htmlStatements statement_appearance is 1: documents are saved as PDFs made from the HTML
     * @param string $today Y-m-d
     */
    public function __construct(
        private \Closure $buildStatement,
        private StatementEmail $emailer,
        private StatementPortal $portal,
        private bool $htmlStatements,
        private bool $rightToLeft,
        private string $today,
    ) {
    }

    public function appendInvoice(array &$stmt, int $pid, int $encounter, string $serviceDate, int $notice): void
    {
        StatementLines::append($stmt, $pid, $encounter, $serviceDate, $notice);
    }

    public function creditEncounters(int $pid, array $excludedEncounterIds): array
    {
        return PatientCreditEncounters::find($pid, $excludedEncounterIds);
    }

    public function buildStatement(array $stmt): string
    {
        return ($this->buildStatement)($stmt);
    }

    public function saveDocument(int $pid, string $statement): void
    {
        $category = QueryUtils::querySingleRow('SELECT id FROM categories WHERE name = ?', ['Invoices']);
        $categoryId = is_array($category) && is_numeric($category['id'] ?? null) ? (int) $category['id'] : 0;
        // The batch is HTML or text; with HTML statements the document is its PDF.
        if ($this->htmlStatements) {
            $pdf = new Mpdf(Config_Mpdf::getConfigMpdf());
            if ($this->rightToLeft) {
                $pdf->SetDirectionality('rtl');
            }
            $pdf->WriteHTML($statement);
            $pdfContent = $pdf->Output('', 'S');
            $statement = is_string($pdfContent) ? $pdfContent : '';
        }
        (new \Document())->createDocument(
            (string) $pid,
            $categoryId,
            'Invoice-' . date('Y-m-d-H:i:s') . ($this->htmlStatements ? '.pdf' : '.txt'),
            $this->htmlStatements ? 'pdf' : 'text/plain',
            $statement
        );
    }

    public function email(int $pid, string $statement): void
    {
        $this->emailer->send($pid, $statement);
    }

    public function isPortalAuthorized(int $pid): bool
    {
        return $this->portal->isAuthorized($pid);
    }

    public function notifyPortal(int $pid, array $invoices, string $statement, string $invoiceId): bool
    {
        return $this->portal->notify($pid, $invoices, $statement, $invoiceId);
    }

    public function recordStatementRun(array $encounterIds): void
    {
        StatementInvoices::recordStatementRun($encounterIds, $this->today);
    }
}
