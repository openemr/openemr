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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadSheetServices
{
    private $arrayData;
    private $fileName;

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @param mixed $arrayData
     */
    public function setArrayData($arrayData): void
    {
        $this->arrayData = $arrayData;
    }

    public function makeXlsReport()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($this->arrayData);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $this->fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($this->fileName));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($this->fileName));
        readfile($this->fileName);
    }
}
