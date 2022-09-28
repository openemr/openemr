<?php

/**
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c)  2022 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Services;

class ImmunizationSpreadsheet extends SpreadSheetServices
{
    public function generateSpreadsheetArray($res, $filename)
    {
        if (!empty($res)) {
            $sheet = [];
            $sheet[] = ['Patient ID', 'Language', 'Code', 'Name', 'Immunization Date', 'Immunization ID', 'Immunization'];
            $i = 1;
            while ($row = sqlFetchArray($res)) {
                $rowcount = ["Q$i"];
                $sheet[] = $row;
                $rowcount[] = $sheet;
                ++$i;
            }
        }

        if (!empty($filename)) {
            $ss = new SpreadSheetServices();
            $ss->setArrayData($sheet);
            $ss->setFileName($filename);
            $ss->makeXlsReport();
        }
    }
}
