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
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Logging\SystemLogger;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Spreadsheet,
};

class SpreadSheetService extends Spreadsheet
{
    private array $header;
    private array $row;

    /**
     * SpreadSheetService constructor.
     *
     * @param array $arrayData
     * @param array $fields
     * @param string $fileName
     */
    public function __construct(private array $arrayData, private array $fields, private readonly string $fileName = 'report')
    {
        if ($this->isCli()) {
            throw new \RuntimeException(self::class . ' should only be run from a Web browser');
        }
        parent::__construct();
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
     * Check if the script is running in CLI mode
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    protected function isCli(): bool
    {
        return (php_sapi_name() === 'cli');
    }

    /**
     * Set one or more HTTP headers
     *
     * This method sets multiple HTTP headers using PHP's header() function.
     * Headers are sent to the browser/client as part of the HTTP response.
     *
     * @codeCoverageIgnore
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

    /**
     * Write the spreadsheet output
     *
     * @codeCoverageIgnore
     *
     * @param string $format The format to write the spreadsheet in (e.g., 'Csv', 'Xlsx')
     * @return void
     */
    protected function writeOutput(string $format): void
    {
        $writer = IOFactory::createWriter($this, $format);
        $writer->save("php://output");
    }
}
