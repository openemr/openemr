<?php

/**
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2021 - 2022  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * This middleware is to allow a uniform use of the spreadsheet library.
 * By passing an array values, a spreadsheet can be dropped to the client from anywhere
 * To use this service send the file name and an array like this
 * The first row is the headers that can be dynamcally generated or static
 * $sheet_array = [
 *     [NULL, 2010, 2011, 2012],
 *     ['Q1',   12,   15,   21],
 *     ['Q2',   56,   73,   86],
 *     ['Q3',   52,   61,   69],
 *     ['Q4',   30,   32, 'Harry'],
 *     ];
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Logging\SystemLogger;
use PhpOffice\PhpSpreadsheet\{
    Cell\AdvancedValueBinder,
    Cell\Cell,
    IOFactory,
    Helper\Sample,
    Spreadsheet,
    Style\NumberFormat
};

class SpreadSheetService extends Spreadsheet
{
    private array $arrayData;
    private string $fileName;

    public function __construct(
        $arrayData,
        $fileName = 'report'
    ) {
        if ((new Sample())->isCli()) {
            (new SystemLogger())->error('This should only be run from a Web Browser' . PHP_EOL);
            return;
        }
        parent::__construct();

        $this->fileName = $fileName;
        $this->arrayData = $arrayData;
    }

    public function buildSpreadsheet()
    {

        if (empty($this->arrayData)) {
            return false;
        }

        $this->header = array_filter(array_keys($this->arrayData[0]), function ($k) {
            return empty($k) ? $k : csvEscape($k);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($this->arrayData as $item) {
            $this->row[] = array_filter(array_values($item), function ($v) {
                return empty($v) ? $v : csvEscape($v);
            });
        }

        return true;
    }

    public function downloadSpreadsheet($format = 'Csv')
    {
        $sheet = $this->getActiveSheet();
        $sheet->fromArray($this->header, null, 'A1');
        $sheet->fromArray($this->row, null, 'A2');
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=' . basename($this->fileName . "." . $format));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $writer = IOFactory::createWriter($this, $format);
        $writer->save("php://output");
    }
}
