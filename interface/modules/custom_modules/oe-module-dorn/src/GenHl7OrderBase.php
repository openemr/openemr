<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn;

use InsuranceCompany;

class GenHl7OrderBase
{
    protected string $lineBreakChar = "\r";
    protected string $fieldSeparator = '|';
    protected string  $componentSeparator = '^';
    protected string  $repetitionSeparator = '~';
    protected string  $escapeSeparator = '\\';
    protected string  $subComponentSeparator = '&';

    public function buildHL7Field($data)
    {
        if (is_array($data) && count($data) > 1) {
            // Run each element through $this->hl7Text()
            $data = array_map($this->hl7Text(...), $data);
            return implode($this->componentSeparator, $data);
        } else {
            // Run the single element through $this->hl7Text()
            $data = $this->hl7Text($data);
            return $data;
        }
    }

    public function buildHl7Segment($segmentName, $fields)
    {
        foreach ($fields as $field) {
            $segment .= $this->fieldSeparator . $field;
        }
        // Remove trailing '|' characters
        //$segment = rtrim($segment, $this->fieldSeparator);
        $segment = $segmentName . $segment . $this->lineBreakChar;
        return $segment;
    }

    public function replaceNewLine($s): string
    {
        $s = str_replace("\r", ' ', $s);
        $s = str_replace("\n", ' ', $s);
        return trim($s);
    }

    public function hl7Text($s)
    {
        // See http://www.interfaceware.com/hl7_escape_protocol.html:
        $s = str_replace('\\', '\\E\\', $s);
        $s = str_replace('^', '\\S\\', $s);
        $s = str_replace('|', '\\F\\', $s);
        $s = str_replace('~', '\\R\\', $s);
        $s = str_replace('&', '\\T\\', $s);
        $s = str_replace("\r", '\\X0d\\', $s);
        $s = str_replace("\n", '', $s);
        return $s;
    }

    public function hl7Zip($s)
    {
        return $this->hl7Text(preg_replace('/[-\s]*/', '', (string) $s));
    }

    public function hl7DateTime($s)
    {
        // Attempt to create a DateTime object from the input value
        $date = date_create($s);
        // Check if the input is a valid date
        if ($date !== false) {
            // Format the date as "YYYYMMDD"
            return date_format($date, 'YmdHisO');
        } else {
            return "";
        }
    }

    public function formatTime($t)
    {
        // Attempt to create a DateTime object from the input value
        $time = date_create($t);
        // Check if the input is a valid time
        if ($time !== false) {
            // Format the time as "HHmm" without seconds
            return date_format($time, 'Hi');
        } else {
            return "";
        }
    }

    public function formatDate($d)
    {
        // Attempt to create a DateTime object from the input value
        $date = date_create($d);
        // Check if the input is a valid date
        if ($date !== false) {
            // Format the date as "YYYYMMDD"
            return date_format($date, 'Ymd');
        } else {
            return "";
        }
    }

    public function hl7Date($s)
    {
        return preg_replace('/[^\d]/', '', (string) $s);
    }

    public function hl7Time($s)
    {
        if (empty($s)) {
            return '';
        }
        return date('YmdHi', strtotime((string) $s));
    }

    public function hl7Sex($s)
    {
        $s = strtoupper(substr((string) $s, 0, 1));
        if ($s !== 'M' && $s !== 'F') {
            $s = 'U';
        }
        return $s;
    }

    public function hl7Phone($s)
    {
        if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)\D*$/", (string) $s, $tmp)) {
            return $tmp[1] . $tmp[2] . $tmp[3];
        }
        if (preg_match("/(\d\d\d)\D*(\d\d\d\d)\D*$/", (string) $s, $tmp)) {
            return $tmp[1] . $tmp[2];
        }
        return '';
    }

    public function hl7SSN($s)
    {
        if (preg_match("/(\d\d\d)\D*(\d\d)\D*(\d\d\d\d)\D*$/", (string) $s, $tmp)) {
            return $tmp[1] . $tmp[2] . $tmp[3];
        }
        return '';
    }

    public function hl7Priority($s)
    {
        return strtoupper(substr((string) $s, 0, 1)) === 'H' ? 'S' : 'R';
    }

    public function hl7Relation($s)
    {
        $tmp = strtolower((string) $s);
        if ($tmp == 'self' || $tmp == '') {
            return '1';
        }

        if ($tmp == 'spouse') {
            return '2';
        }

        if ($tmp == 'child') {
            return '3';
        }

        if ($tmp == 'other') {
            return '8';
        }
        // Should not get here so this will probably get noticed if we do.
        return $s;
    }

    public function hl7Race($s)
    {
        $tmp = strtolower((string) $s);
        if ($tmp == '') {
            return '';
        } elseif ($tmp == 'asian') {
            return '2028-9';
        } elseif ($tmp == 'black_or_afri_amer') {
            return '2054-5';
        } elseif ($tmp == 'white') {
            return '2106-3';
        } elseif ($tmp == 'hispanic') {
            return '2131-1';
        } elseif ($tmp == 'amer_ind_or_alaska_native') {
            return '1002-5';
        } elseif ($tmp == 'other') {
            return '2131-1';
        } elseif ($tmp == 'ashkenazi_jewish') {
            return '2131-1';
        } elseif ($tmp == 'sephardic_jewish') {
            return '2131-1';
        }
        // Should not get here so this will probably get noticed if we do.
        return $s;
    }

    public function hl7Workman($s)
    {
        // $tmp = strtolower($s);
        if ($s == 15) {
            return 'Y';
        } else {
            return 'N';
        }
    }

    /**
     * Get array of insurance payers for the specified patient as of the specified
     * date. If no date is passed then the current date is used.
     *
     * @param integer $pid Patient ID.
     * @param string  $date
     * @return array   Array containing an array of data for each payer.
     */
    public function loadPayerInfo($pid, $date = '')
    {
        if (empty($date)) {
            $date = date('Y-m-d');
        }

        $payers = [];
        $dres = sqlStatement("SELECT * FROM insurance_data WHERE pid = ? AND (date <= ? OR date IS NULL) ORDER BY type ASC, date DESC", [$pid, $date]);
        $prevtype = '';
        // type is primary, secondary or tertiary
        while ($drow = sqlFetchArray($dres)) {
            if (strcmp($prevtype, (string) $drow['type']) == 0) {
                continue;
            }

            $prevtype = $drow['type'] ?? '';
            // Very important to check for a missing provider because
            // that indicates no insurance as of the given date.
            if (empty($drow['provider'] ?? '')) {
                continue;
            }

            $ins = count($payers);
            $crow = sqlQuery("SELECT * FROM insurance_companies WHERE id = ?", [$drow['provider']]);
            $orow = new InsuranceCompany($drow['provider']);
            $payers[$ins] = [];
            $payers[$ins]['data'] = $drow;
            $payers[$ins]['company'] = $crow;
            $payers[$ins]['object'] = $orow;
        }

        return $payers;
    }

    public function loadGuarantorInfo($pid, $date = '')
    {
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $guarantors = [];
        $gres = sqlStatement("SELECT * FROM insurance_data WHERE pid = ? AND date <= ? ORDER BY type ASC, date DESC LIMIT 1", [$pid, $date]);
        // type is primary, secondary or tertiary
        while ($drow = sqlFetchArray($gres)) {
            $gnt = count($guarantors);
            $guarantors[$gnt] = [];
            $guarantors[$gnt]['data'] = $drow;
        }
        return $guarantors;
    }
}
