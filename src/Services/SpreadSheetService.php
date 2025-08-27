<?php

/**
 * Spreadsheet service
 *
 * Takes a key -> value array where the keys are the column names
 * and downloads a csv spreadsheet to the browser. Optionally send in
 * a fields array to select only those columns would like to export.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2021-2022  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Logging\SystemLogger;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Spreadsheet,
};

class SpreadSheetService extends Spreadsheet
{
    private array $arrayData;
    private string $fileName;
    private array $header;
    private array $row;
    private array $fields;
    private SystemLogger $logger;

    /**
     * SpreadSheetService constructor.
     *
     * @param array $arrayData
     * @param array $fields
     * @param string $fileName
     */
    public function __construct(array $arrayData, array $fields, string $fileName = 'report')
    {
        $this->logger = new SystemLogger();
        if ($this->isCli()) {
            // FIXME: this returns, aborting the constructor,
            // but doesn't prevent the caller from continuing.
            // That may lead to a confusing user experience.
            $this->logError('This should only be run from a Web browser');
            return;
        }
        parent::__construct();

        $this->fileName = $fileName;
        $this->arrayData = $arrayData;
        $this->fields = $fields ?? [];
    }

    /**
     * Check if the script is running in CLI mode
     */
    protected function isCli(): bool
    {
        return (php_sapi_name() === 'cli');
    }

    /**
     * Log an error
     *
     * @param string $message
     * @return void
     */
    protected function logError(string $message): void
    {
        $this->logger->error($message);
    }

    /**
     * Build the spreadsheet
     *
     * @return bool
     */
    public function buildSpreadsheet(): bool
    {
        if (empty($this->arrayData)) {
            return false;
        }

        if (empty($this->fields)) {
            $this->fields = array_keys($this->arrayData[0]);
        }

        // FIXME: array_filter's callback must return a boolean
        // but these callbacks return ?string.
        // It still works, but the result of csvEscape is not
        // being used.
        $this->header = array_filter(array_keys($this->arrayData[0]), function ($v) {
            if (in_array($v, $this->fields)) {
                return csvEscape($v);
            }
        });

        foreach ($this->arrayData as $item) {
            $this->row[] = array_filter($item, function ($v, $k) {
                if (in_array($k, $this->fields)) {
                    return csvEscape($v);
                }
            }, ARRAY_FILTER_USE_BOTH);
        }

        return true;
    }

    /**
     * Download the spreadsheet
     *
     * @param string $format
     * @return void
     */
    public function downloadSpreadsheet(string $format = 'Csv'): void
    {
        $sheet = $this->getActiveSheet();
        $sheet->fromArray($this->header, null, 'A1');
        $sheet->fromArray($this->row, null, 'A2');
        $fileName = basename($this->fileName . "." . $format);
        $this->setHeaders(
            'Content-Description: File Transfer',
            "Content-Type: application/{$format}",
            "Content-Disposition: attachment; filename={$fileName}",
            'Expires: 0',
            'Cache-Control: must-revalidate',
            'Pragma: public'
        );
        $this->writeOutput($format);
    }

    /**
     * Set one or more HTTP headers
     *
     * This method sets multiple HTTP headers using PHP's header() function.
     * Headers are sent to the browser/client as part of the HTTP response.
     *
     * @param string ...$headers Variable number of header strings to set
     * @return void
     */
    protected function setHeaders(string ...$headers): void
    {
        foreach ($headers as $header) {
            header($header);
        }
    }

    protected function writeOutput(string $format): void
    {
        $writer = IOFactory::createWriter($this, $format);
        $writer->save("php://output");
    }
}
