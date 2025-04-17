<?php

/**
 * Misc. BatchCom convenience functions
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Andres Paglayan <andres@paglayan.com>
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2005 Andres Paglayan <andres@paglayan.com>
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//validation functions
function check_date_format($date)
{
    if (($date == '') || ($date == '0000-00-00')) {
        return true;
    }
    $ymd = explode('-', $date);
    return (count($ymd) == 3) && ($ymd[0] > 1900) && checkdate($ymd[1], $ymd[2], $ymd[0]);
}

function check_age($age)
{
    $age = trim($age);
    $pat = "/^([0-9]+)$/";
    return preg_match($pat, $age) or $age == '';
}

function check_select($select, $array)
{
    return array_search($select, $array) or 0 === array_search($select, $array);
}

function where_or_and($and)
{
    if ($and == '') {
        $and = 'WHERE ';
    } elseif ($and == 'WHERE ') {
        $and = 'AND ';
    } else {
        $and = 'AND ';
    }

    return $and;
}

function register_email($patient_id, $sent_by, $msg_type, $msg_subject, $msg_text)
{

    $sql = "INSERT INTO batchcom SET patient_id=?, sent_by=?,
                                     msg_type=?, msg_subject=?,
                                     msg_text=?, msg_date_sent=NOW()";

    echo $sql;

    $res = sqlStatement($sql, array($patient_id, $sent_by, $msg_type, $msg_subject, $msg_text));
}

function generate_csv($sql_result)
{
    /*  batch CSV processor, included from batchcom */
    // create file header.
    // menu for fields could be added in the future

    while ($row = sqlFetchArray($sql_result)) {
        if (!$flag_on) {
            $flag_on = true;
            foreach ($row as $key => $value) {
                $file .= csvEscape($key) . ",";
            }

            $file = substr($file, 0, -1);
            $file .= "\n";
            reset($row);
        }

        foreach ($row as $key => $value) {
            $line .= csvEscape($value) . ",";
        }

        $line = substr($line, 0, -1);
        $line .= "\n";
        $file .= $line;
        $line = '';
    }

    //download
    $today = date('Y-m-d:H:i:s');
    $filename = "CSVdata-" . $today . ".csv";
    header('Pragma: private');
    header('Cache-control: private, must-revalidate');
    header("Content-type: text/comma-separated-values");
    header("Content-Disposition: attachment; filename=" . $filename);
    print $file;
    exit();
}
