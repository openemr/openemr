<?php

/** @package    verysimple::Phreeze */

/**
 * ExportUtility Class
 *
 * This contains various utility functions for exporting Phreezable objects into other formats
 * such as Excel, CSV, tab-delimited, XML, etc
 *
 * @package verysimple::Phreeze
 * @author VerySimple Inc. <noreply@verysimple.com>
 * @copyright 1997-2005 VerySimple Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class ExportUtility
{
    /**
     * Streams to the browser the provided array of objects as a basic Excel document
     * with headers.
     * if the objects have an associated Map class, then footers will be
     * added to sum any numeric fields. otherwise no footers are added
     *
     * Note that PEAR PHPExcel must be installed
     *
     * @link https://phpexcel.codeplex.com/
     *
     * @param
     *          Array an array of Phreezable objects, obtained for example, using DataSet->ToObjectArray
     * @param Phreezer $phreezer
     *          is needed to get field maps
     * @param
     *          string (optional) The title of the report
     */
    static function OutputAsExcel(array $objects, Phreezer $phreezer, $reportTitle = "Data Export", $fileName = "export.xls", $creator = "Phreeze Library")
    {
        require_once("PEAR/PHPExcel.php");

        // create the workbook and worksheet
        $workbook = new PHPExcel();

        // set workbook properties
        $workbook->getProperties()->setCreator($creator)->setTitle($fileName);

        $workbook->setActiveSheetIndex(0);
        $worksheet = $workbook->getActiveSheet();

        $current_column = "A";
        $current_row = 1;

        $worksheet->setCellValue($current_column . $current_row, $reportTitle);
        $worksheet->getStyle($current_column . $current_row)->getFont()->setBold(true)->setSize(16);
        $worksheet->getStyle($current_column . $current_row)->getFont()->setName('Arial');

        // default to no columns
        $fields = array ();
        $columns = array ();
        $is_numeric = array ();
        $fieldmap_exists = false;

        $current_row = 3;

        // print the headers
        // while we're looping, also parse the fields so we don't have to do
        // it repeatedly when looping through data
        if (isset($objects [0])) {
            try {
                // see if there is a fieldmap for this object
                $fields = $phreezer->GetFieldMaps(get_class($objects [0]));
                $fieldmap_exists = true;

                // these are the columns we'll use for enumeration from here on
                $columns = array_keys($fields);
            } catch (Exception $ex) {
                // no fieldmaps exist, so use the reflection class instead
                $reflect = new ReflectionClass($objects [0]);
                $publicAttributes = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
                $staticAttributes = $reflect->getStaticProperties();
                // only include non-static public properties
                $props = array_diff($publicAttributes, $staticAttributes);

                foreach ($props as $prop) {
                    $column = $prop->getName();
                    $columns [] = $column;
                }
            }

            foreach ($columns as $column) {
                // save this so we don't check it every time when looping through data
                $is_numeric [$column] = $fieldmap_exists ? $fields [$column]->IsNumeric() : false;

                $worksheet->setCellValue($current_column . $current_row, $column);
                $worksheet->getStyle($current_column . $current_row)->getFont()->setBold(true)->setSize(11);
                $worksheet->getStyle($current_column . $current_row)->getFont()->setName('Arial');

                $current_column++;
            }
        }

        $current_row = 4;

        // loop through all of the data
        foreach ($objects as $object) {
            $current_column = "A";
            foreach ($columns as $column) {
                if ($fieldmap_exists == false || $is_numeric [$column] == true) {
                    $worksheet->setCellValue($current_column . $current_row, $object->$column);
                } else {
                    $worksheet->setCellValue($current_column . $current_row, $object->$column);
                }

                $current_column++;
            }

            $current_row++;
        }

        // lastly write to the footer to sum the numeric columns
        $current_column = "A";
        foreach ($columns as $column) {
            if ($is_numeric [$column]) {
                $columnLetter = ExportUtility::GetColumnLetter($current_column);
                $formula = "=SUM(" . $columnLetter . "3:" . $columnLetter . ($current_row - 1) . ")";

                // notice the @ sign in front because this will fire a deprecated warning due to use of "split"
                @$worksheet->setCellValue($current_column . $current_row, $formula);
                $worksheet->getStyle($current_column . $current_row)->getFont()->setBold(true);
                $worksheet->getStyle($current_column . $current_row)->getFont()->setName('Arial');
            }

            $current_column++;
        }

        $workbook->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

        $workbook->setActiveSheetIndex(0);
        $writer = PHPExcel_IOFactory::createWriter($workbook, 'Excel5');

        // set headers to excel:
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }

    /**
     * Given a zero-based column number, the approriate Excel column letter is
     * returned, ie A, B, AB, CJ, etc.
     * max supported is ZZ, higher than that will
     * throw an exception.
     *
     * @param int $columnNumber
     */
    static function GetColumnLetter($columnNumber)
    {
        // work with 1-based number
        $colNum = $columnNumber + 1;
        $code = "";

        if ($colNum > 26) {
            // greater than 26 means the column will be AA, AB, AC, etc.
            $left = floor($columnNumber / 26);
            $right = 1 + ($columnNumber % 26);

            if ($left > 26) {
                throw new Exception("Columns exceed supported amount");
            }

            $code = chr($left + 64) . chr($right + 64);
        } else {
            $code = chr($colNum + 64);
        }

        return $code;
    }
}
