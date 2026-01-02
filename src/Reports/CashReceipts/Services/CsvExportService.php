<?php

/**
 * CsvExportService - Export cash receipts to CSV format
 *
 * Uses Symfony StreamedResponse for memory-efficient export
 * Testable and reusable across CLI, API, and WebUI contexts
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Services;

use OpenEMR\Reports\CashReceipts\Model\ProviderSummary;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Service for exporting cash receipts data to CSV format
 */
class CsvExportService
{
    /**
     * Generate CSV export as Symfony StreamedResponse
     *
     * This approach:
     * - Streams data instead of buffering entire file in memory
     * - Returns Response object (testable, framework-agnostic)
     * - Can be used in CLI, API endpoints, or web controllers
     *
     * @param ProviderSummary[] $providerSummaries
     * @param array $filters Report filters for metadata
     * @param array $options Export options
     * @return StreamedResponse
     */
    public function exportToResponse(
        array $providerSummaries,
        array $filters = [],
        array $options = []
    ): StreamedResponse {
        $filename = $this->generateFilename($filters);
        
        $response = new StreamedResponse(function () use ($providerSummaries, $filters, $options): void {
            $output = fopen('php://output', 'w');
            
            // Write headers
            $this->writeHeaders($output, $filters, $options);
            
            // Write data rows
            $this->writeData($output, $providerSummaries, $options);
            
            fclose($output);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Generate CSV content as string (for testing or API responses)
     *
     * @param ProviderSummary[] $providerSummaries
     * @param array $filters
     * @param array $options
     * @return string
     */
    public function exportToString(
        array $providerSummaries,
        array $filters = [],
        array $options = []
    ): string {
        $output = fopen('php://temp', 'r+');
        
        $this->writeHeaders($output, $filters, $options);
        $this->writeData($output, $providerSummaries, $options);
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Write CSV headers
     *
     * @param resource $output
     * @param array $filters
     * @param array $options
     */
    private function writeHeaders($output, array $filters, array $options): void
    {
        $showProcedures = $options['show_procedures'] ?? false;
        $showProcCode = $options['show_proc_code'] ?? false;
        
        $headers = ['Provider', 'Date'];
        
        if ($showProcedures) {
            $headers[] = $options['invoice_display_mode'] ?? false ? 'Patient Name' : 'Invoice';
        }
        
        if ($showProcCode) {
            $headers[] = 'Invoice Amount';
            $headers[] = 'Insurance';
        }
        
        if ($showProcedures) {
            $headers[] = 'Procedure';
            $headers[] = 'Professional';
            $headers[] = 'Clinic';
        } else {
            $headers[] = 'Received';
        }
        
        fputcsv($output, $headers);
    }

    /**
     * Write data rows to CSV
     *
     * @param resource $output
     * @param ProviderSummary[] $providerSummaries
     * @param array $options
     */
    private function writeData($output, array $providerSummaries, array $options): void
    {
        $showDetails = $options['show_details'] ?? true;
        $showProcedures = $options['show_procedures'] ?? false;
        $showProcCode = $options['show_proc_code'] ?? false;
        
        foreach ($providerSummaries as $provider) {
            // Write detail rows if requested
            if ($showDetails) {
                foreach ($provider->getReceipts() as $receipt) {
                    $this->writeReceiptRow($output, $provider, $receipt, $options);
                }
            }
            
            // Write provider subtotal
            $this->writeProviderSubtotal($output, $provider, $showProcedures);
        }
        
        // Write grand totals
        $this->writeGrandTotals($output, $providerSummaries, $showProcedures, $showProcCode);
    }

    /**
     * Write a single receipt row
     *
     * @param resource $output
     * @param ProviderSummary $provider
     * @param object $receipt
     * @param array $options
     */
    private function writeReceiptRow($output, ProviderSummary $provider, $receipt, array $options): void
    {
        $showProcedures = $options['show_procedures'] ?? false;
        $showProcCode = $options['show_proc_code'] ?? false;
        $invoiceDisplayMode = $options['invoice_display_mode'] ?? false;
        
        $row = [
            $provider->getProviderName(),
            $receipt->getTransactionDate(),
        ];
        
        if ($showProcedures) {
            $row[] = $invoiceDisplayMode ? $receipt->getPatientName() : $receipt->getInvoiceDisplay();
        }
        
        if ($showProcCode) {
            $row[] = $options['invoice_amounts'][$receipt->getPatientId() . '.' . $receipt->getEncounterId()] ?? '';
            $row[] = $options['insurance_names'][$receipt->getPayerId()] ?? '';
        }
        
        if ($showProcedures) {
            $row[] = $receipt->getProcedureCode() ?? '';
            $row[] = $receipt->isClinicReceipt() ? '' : number_format($receipt->getAmount(), 2);
            $row[] = $receipt->isClinicReceipt() ? number_format($receipt->getAmount(), 2) : '';
        } else {
            $row[] = number_format($receipt->getAmount(), 2);
        }
        
        fputcsv($output, $row);
    }

    /**
     * Write provider subtotal row
     *
     * @param resource $output
     * @param ProviderSummary $provider
     * @param bool $showProcedures
     */
    private function writeProviderSubtotal($output, ProviderSummary $provider, bool $showProcedures): void
    {
        $row = ["Totals for {$provider->getProviderName()}", ''];
        
        if ($showProcedures) {
            $row[] = ''; // Empty columns for invoice, procedure
            $row[] = ''; 
            $row[] = '';
            $row[] = number_format($provider->getProfessionalTotal(), 2);
            $row[] = number_format($provider->getClinicTotal(), 2);
        } else {
            $row[] = number_format($provider->getGrandTotal(), 2);
        }
        
        fputcsv($output, $row);
    }

    /**
     * Write grand totals row
     *
     * @param resource $output
     * @param ProviderSummary[] $providerSummaries
     * @param bool $showProcedures
     * @param bool $showProcCode
     */
    private function writeGrandTotals($output, array $providerSummaries, bool $showProcedures, bool $showProcCode): void
    {
        $totalsService = new TotalsService();
        $grandTotals = $totalsService->calculateGrandTotals($providerSummaries);
        
        $row = ['Grand Totals', ''];
        
        // Add empty columns based on display mode
        if ($showProcCode) {
            $row[] = '';
            $row[] = '';
        }
        
        if ($showProcedures) {
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = number_format($grandTotals['professional'], 2);
            $row[] = number_format($grandTotals['clinic'], 2);
        } else {
            $row[] = number_format($grandTotals['grand'], 2);
        }
        
        fputcsv($output, $row);
    }

    /**
     * Generate filename based on filters
     *
     * @param array $filters
     * @return string
     */
    private function generateFilename(array $filters): string
    {
        $from = $filters['from_date'] ?? date('Y-m-d');
        $to = $filters['to_date'] ?? date('Y-m-d');
        
        return "cash_receipts_{$from}_to_{$to}.csv";
    }
}
