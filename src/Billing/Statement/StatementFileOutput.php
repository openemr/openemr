<?php

/**
 * Statement batches downloaded as text or PDF, or printed with the site's print command.
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
use OpenEMR\Pdf\Config_Mpdf;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use Symfony\Component\Process\Process;

final readonly class StatementFileOutput implements StatementOutput
{
    /**
     * @param string $pdfFile where a PDF batch is written ($STMT_TEMP_FILE_PDF in the site's statement.inc.php)
     * @param string $temporaryDir the temporary_files_dir global, for a PDF named after its patient
     * @param bool $htmlStatements statement_appearance is 1: the batch is HTML, made into a PDF with mPDF;
     *     otherwise it's fixed-width text, made into one with Cezpdf
     * @param string $printCommand OPENEMR_PRINT_COMMAND from the site's config.php
     */
    public function __construct(
        private string $pdfFile,
        private string $temporaryDir,
        private bool $htmlStatements,
        private bool $rightToLeft,
        private string $printCommand,
        private LoggerInterface $logger,
    ) {
    }

    public function download(string $batchFile): never
    {
        self::send($batchFile);
    }

    public function downloadPdf(string $batchFile, ?array $onlyPatient): never
    {
        $safeName = $onlyPatient === null ? '' : convert_safe_file_dir_name($onlyPatient['firstName']);
        $pdfFile = $onlyPatient === null
            ? $this->pdfFile
            : $this->temporaryDir . '/Stmt_' . (is_string($safeName) ? $safeName : '') . '_' . $onlyPatient['pid'] . '.pdf';
        $batch = file_get_contents($batchFile);
        $batch = $batch === false ? '' : $batch;
        if ($this->htmlStatements) {
            $pdf = new Mpdf(Config_Mpdf::getConfigMpdf());
            if ($this->rightToLeft) {
                $pdf->SetDirectionality('rtl');
            }
            $pdf->WriteHTML($batch);
            $pdf->Output($pdfFile, 'F');
        } else {
            file_put_contents($pdfFile, self::textPdf($batch));
        }
        self::send($pdfFile);
    }

    /**
     * The print command is a command line, such as 'lpr -P HPLaserjet6P -o cpi=10': its
     * words become the program and its arguments, and the batch file is the last argument.
     * No shell runs it, so the file name can't be read as shell syntax.
     */
    public function print(string $batchFile): bool
    {
        $words = preg_split('/\s+/', trim($this->printCommand), -1, PREG_SPLIT_NO_EMPTY);
        if ($words === false || $words === []) {
            $this->logger->error('Statements not printed: no print command is set (OPENEMR_PRINT_COMMAND in the site\'s config.php)');
            return false;
        }
        $process = new Process([...$words, $batchFile]);
        try {
            $process->run();
        } catch (ProcessException $e) {
            $this->logger->error('Statements not printed: the print command could not run', ['command' => $words[0], 'exception' => $e]);
            return false;
        }
        if ($process->isSuccessful()) {
            return true;
        }
        $this->logger->error('Statements not printed: the print command failed', [
            'command' => $words[0],
            'exitCode' => $process->getExitCode(),
            'stderr' => trim($process->getErrorOutput()),
        ]);
        return false;
    }

    /**
     * The text batch on letter pages in Courier: a form feed starts a page, and the
     * remit-to, visit-date, appointment and current-balance lines are bold.
     */
    private static function textPdf(string $batch): string
    {
        $pdf = new \Cezpdf('LETTER');
        $pdf->ezSetMargins(45, 9, 36, 10);
        $pdf->selectFont('Courier');
        $pdf->ezSetY(self::pageTop($pdf));
        $lines = preg_split('/(?<=\n)/', $batch);
        $lines = $lines === false ? [] : $lines;
        $last = count($lines) - 1;
        foreach ($lines as $i => $line) {
            if (str_contains($line, "\014") && $i < $last) {
                $pdf->ezNewPage();
                $pdf->ezSetY(self::pageTop($pdf));
                $line = str_replace("\014", '', $line);
            }
            $bold = stristr($line, 'REMIT TO') !== false
                || stristr($line, 'Visit Date') !== false
                || stristr($line, 'Future Appointments') !== false
                || stristr($line, 'Current') !== false;
            $pdf->ezText($bold ? '<b>' . $line . '</b>' : $line, 12, ['justification' => 'left', 'leading' => 6]);
        }
        return $pdf->ezOutput();
    }

    /**
     * Where text starts on a page: its height less the top margin.
     */
    private static function pageTop(\Cezpdf $pdf): float
    {
        $page = $pdf->ez;
        $height = is_array($page) && is_numeric($page['pageHeight'] ?? null) ? (float) $page['pageHeight'] : 0.0;
        $margin = is_array($page) && is_numeric($page['topMargin'] ?? null) ? (float) $page['topMargin'] : 0.0;
        return $height - $margin;
    }

    private static function send(string $file): never
    {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Description: File Transfer');
        readfile($file);
        flush();
        exit();
    }
}
