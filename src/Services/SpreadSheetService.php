<?php

/**
 * Spreadsheet service
 *
 * Takes a key -> value array where the keys are the column names
 * and downloads a csv spreadsheet to the browser. Optionally send in
 * a fields array to select only those columns would like to export.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author  Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2021-2022  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Logging\SystemLogger;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Helper\Sample,
    Spreadsheet,
};

class SpreadSheetService extends Spreadsheet
{
    private array $arrayData;
    private string $fileName;
    private array $header;
    private array $row;
    private array $fields;

    public function __construct(
        $arrayData,
        $fields,
        $fileName = 'report'
    ) {
        if ((new Sample())->isCli()) {
            (new SystemLogger())->error('This should only be run from a Web Browser' . PHP_EOL);
            return;
        }
        parent::__construct();

        $this->fileName = $fileName;
        $this->arrayData = $arrayData;
        $this->fields = $fields ?? [];
    }

    public function buildSpreadsheet()
    {

        if (empty($this->arrayData)) {
            return false;
        }

        if (empty($this->fields)) {
            $this->fields = array_keys($this->arrayData[0]);
        }

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

    public function downloadSpreadsheet($format = 'Csv')
    {
        $sheet = $this->getActiveSheet();
        $sheet->fromArray($this->header, null, 'A1');
        $sheet->fromArray($this->row, null, 'A2');
        header('Content-Description: File Transfer');
        header('Content-Type: application/' . $format);
        header('Content-Disposition: attachment; filename=' . basename($this->fileName . "." . $format));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $writer = IOFactory::createWriter($this, $format);
        $writer->save("php://output");
    }
}
