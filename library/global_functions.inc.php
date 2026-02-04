<?php

/**
 * Global functions shared across multiple files in OpenEMR.
 *
 * This file contains functions that were previously duplicated across multiple
 * files in the codebase. They have been consolidated here to reduce code
 * duplication and ensure consistent behavior.
 *
 * No new code should be authored into this file; use it only for consolidating
 * and refactoring existing functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\FacilityService;

/**
 * Reads $_POST and trims the value. New code should NOT use this function.
 */
function trimPost(string $key): string
{
    return \trim($_POST[$key] ?? '');
}

// ============================================================================
// XML Export Functions (used by ippf_export.php, export_xml.php)
// ============================================================================

/**
 * Open an XML tag with proper indentation.
 *
 * @param string $tag The tag name to open
 * @return void
 * @global string $out The output buffer
 * @global int $indent The current indentation level
 */
function OpenTag($tag): void
{
    global $out, $indent;
    for ($i = 0; $i < $indent; ++$i) {
        $out .= "\t";
    }

    ++$indent;
    $out .= "<$tag>\n";
}

/**
 * Close an XML tag with proper indentation.
 *
 * @param string $tag The tag name to close
 * @return void
 * @global string $out The output buffer
 * @global int $indent The current indentation level
 */
function CloseTag($tag): void
{
    global $out, $indent;
    --$indent;
    for ($i = 0; $i < $indent; ++$i) {
        $out .= "\t";
    }

    $out .= "</$tag>\n";
}

// ============================================================================
// String/Number Utility Functions
// ============================================================================

/**
 * Remove all non-digits from a string.
 *
 * @param mixed $field The input to process
 * @return string The string with only digits remaining
 */
function Digits($field)
{
    return preg_replace("/\D/", "", (string) $field);
}

/**
 * Put dashes, colons, etc. back into a timestamp based on a format string.
 * The format uses '.' as a placeholder for each character from the input.
 *
 * @param string $fmt The format string (e.g., '....-..-..' for dates)
 * @param string $str The input string to decorate
 * @return string The decorated string
 */
function decorateString($fmt, $str)
{
    $res = '';
    while ($fmt) {
        $fc = substr((string) $fmt, 0, 1);
        $fmt = substr((string) $fmt, 1);
        if ($fc == '.') {
            $res .= substr((string) $str, 0, 1);
            $str = substr((string) $str, 1);
        } else {
            $res .= $fc;
        }
    }

    return $res;
}

/**
 * Extract description from a string, removing any prefix before colon.
 *
 * @param string $desc The description string
 * @return string The cleaned description
 */
function display_desc($desc)
{
    if (preg_match('/^\S*?:(.+)$/', (string) $desc, $matches)) {
        $desc = $matches[1];
    }

    return $desc;
}

/**
 * Format a money amount with decimals but no other decoration.
 *
 * @param float $value The value to format
 * @param int $extradecimals Extra decimal places beyond currency standard
 * @return string The formatted number
 */
function formatMoneyNumber($value, $extradecimals = 0)
{
    return sprintf('%01.' . ($GLOBALS['currency_decimals'] + $extradecimals) . 'f', $value);
}

/**
 * Parse note content to extract items within {| and |} markers.
 *
 * @param string $note The note content
 * @return string JSON-encoded array of matches
 */
function parse_note($note)
{
    $result = preg_match_all("/\{\|([^\]]*)\|\}/", (string) $note, $matches);
    return json_encode($matches[1]);
}

// ============================================================================
// User/Patient Lookup Functions
// ============================================================================

/**
 * Look up a user's full name by ID.
 *
 * @param int|string $thisField The user ID
 * @return string The user's name in "Last, First Middle" format
 */
function User_Id_Look($thisField)
{
    if (!$thisField) {
        return '';
    }

    $ret = '';
    $rlist = sqlStatement("SELECT lname, fname, mname FROM users WHERE id=?", [$thisField]);
    $rrow = sqlFetchArray($rlist);
    if ($rrow) {
        $ret = $rrow['lname'] . ', ' . $rrow['fname'] . ' ' . $rrow['mname'];
    }

    return $ret;
}

// ============================================================================
// Report Display Functions
// ============================================================================

/**
 * Print an encounter header row for patient ledger reports.
 *
 * @param string $dt The encounter date
 * @param string $rsn The reason for visit
 * @param int|string $dr The provider ID
 * @return void
 * @global string $bgcolor The current background color
 * @global int $orow The row counter
 */
function PrintEncHeader($dt, $rsn, $dr): void
{
    global $bgcolor, $orow;
    $bgcolor = (($bgcolor == "#FFFFDD") ? "#FFDDDD" : "#FFFFDD");
    echo "<tr class='bg-white'>";
    if (strlen((string) $rsn) > 50) {
        $rsn = substr((string) $rsn, 0, 50) . '...';
    }

    echo "<td colspan='4'><span class='font-weight-bold'>" . xlt('Encounter Dt / Rsn') . ": </span><span class='detail'>" . text(substr((string) $dt, 0, 10)) . " / " . text($rsn) . "</span></td>";
    echo "<td colspan='5'><span class='font-weight-bold'>" . xlt('Provider') . ": </span><span class='detail'>" . text(User_Id_Look($dr)) . "</span></td>";
    echo "</tr>\n";
    $orow++;
}

/**
 * Start an HTML table row for statistics reports.
 *
 * @param string $att HTML attributes for the row
 * @return void
 * @global int $cellcount Cell counter
 * @global int $form_output Output format (3 = CSV)
 */
function genStartRow($att): void
{
    global $cellcount, $form_output;
    if ($form_output != 3) {
        echo " <tr $att>\n";
    }

    $cellcount = 0;
}

/**
 * End an HTML table row for statistics reports.
 *
 * @return void
 * @global int $form_output Output format (3 = CSV)
 */
function genEndRow(): void
{
    global $form_output;
    if ($form_output == 3) {
        echo "\n";
    } else {
        echo " </tr>\n";
    }
}

// ============================================================================
// Age Calculation Functions
// ============================================================================

/**
 * Compute age in years given a DOB and "as of" date.
 *
 * @param string $dob Date of birth (YYYY-MM-DD format)
 * @param string $asof The date to calculate age as of (defaults to today)
 * @return int The age in years
 */
function getAge($dob, $asof = '')
{
    if (empty($asof)) {
        $asof = date('Y-m-d');
    }

    $a1 = explode('-', substr((string) $dob, 0, 10));
    $a2 = explode('-', substr((string) $asof, 0, 10));
    $age = $a2[0] - $a1[0];
    if ($a2[1] < $a1[1] || ($a2[1] == $a1[1] && $a2[2] < $a1[2])) {
        --$age;
    }

    return $age;
}

// ============================================================================
// HL7 Helper Functions
// ============================================================================

/**
 * Convert a date string to HL7 format (digits only).
 *
 * @param string $s The date string
 * @return string The date with only digits
 */
function hl7Date($s)
{
    return preg_replace('/[^\d]/', '', (string) $s);
}

/**
 * @param string
 * @return string
 */
function hl7Priority($s)
{
    return strtoupper(substr((string) $s, 0, 1)) === 'H' ? 'S' : 'R';
}

// ============================================================================
// Cron/Notification Functions
// ============================================================================

/**
 * Get patient data for phone alert notifications.
 *
 * @param string $type The notification type ('Phone')
 * @param int $trigger_hours Hours before appointment to trigger
 * @return array Array of patient appointment data
 */
function cron_getPhoneAlertpatientData($type, $trigger_hours)
{
    $ssql = '';
    $check_date = '';

    // Added by Yijin 1/12/10 to handle phone reminders.
    // Patient needs to have hipaa Voice flag set to yes and a home phone
    if ($type == 'Phone') {
        $ssql = " and pd.hipaa_voice='YES' and pd.phone_home<>'' and ope.pc_sendalertsms='NO' and ope.pc_apptstatus != '*' ";

        $check_date = date("Y-m-d", mktime(date("H") + $trigger_hours, 0, 0, date("m"), date("d"), date("Y")));
    }

    $patient_field = "pd.pid,pd.title,pd.fname,pd.lname,pd.mname,pd.phone_cell,pd.email,pd.hipaa_allowsms,pd.hipaa_allowemail,pd.phone_home,pd.hipaa_voice,";
    $ssql .= " and (ope.pc_eventDate=?)";

    $query = "select $patient_field pd.pid,ope.pc_eid,ope.pc_pid,ope.pc_title,
            ope.pc_hometext,ope.pc_eventDate,ope.pc_endDate,
            ope.pc_duration,ope.pc_alldayevent,ope.pc_startTime,ope.pc_endTime,ope.pc_facility
        from
            openemr_postcalendar_events as ope ,patient_data as pd
        where
            ope.pc_pid=pd.pid $ssql
        order by
            ope.pc_eventDate,ope.pc_endDate,pd.pid";

    $db_patient = (sqlStatement($query, [$check_date]));
    $patient_array = [];
    $cnt = 0;
    while ($prow = sqlFetchArray($db_patient)) {
        $patient_array[$cnt] = $prow;
        $cnt++;
    }

    return $patient_array;
}

// ============================================================================
// File/Report Functions
// ============================================================================

/**
 * Generate a base filename for patient reports.
 *
 * @param int $pid The patient ID
 * @return array Contains 'base' filename, 'fname', and 'lname'
 */
function report_basename($pid)
{
    $ptd = getPatientData($pid, "fname,lname");
    // escape names for pesky periods hyphen etc.
    $esc = $ptd['fname'] . '_' . $ptd['lname'];
    $esc = str_replace(['.', ',', ' '], '', $esc);
    $fn = basename_international(strtolower($esc . '_' . $pid . '_' . xl('report')));

    return ['base' => $fn, 'fname' => $ptd['fname'], 'lname' => $ptd['lname']];
}

/**
 * Create a ZIP file with content.
 *
 * @param string $source The source file path or filename for the entry
 * @param string $destination The destination ZIP file path
 * @param string $content Optional content to add instead of reading from source
 * @param bool $create Whether to create new (true) or overwrite existing (false)
 * @return bool True on success, false on failure
 */
function zip_content($source, $destination, $content = '', $create = true)
{
    if (!extension_loaded('zip')) {
        return false;
    }

    $zip = new ZipArchive();
    if ($create) {
        if (!$zip->open($destination, ZipArchive::CREATE)) {
            return false;
        }
    } else {
        if (!$zip->open($destination, ZipArchive::OVERWRITE)) {
            return false;
        }
    }

    if (is_file($source) === true) {
        $zip->addFromString(basename((string) $source), file_get_contents($source));
    } elseif (!empty($content)) {
        $zip->addFromString(basename((string) $source), $content);
    }

    return $zip->close();
}

// ============================================================================
// Simple String Utility Functions
// ============================================================================


/**
 * Properly capitalize a name handling hyphens and apostrophes.
 *
 * @param string $string The name to capitalize
 * @return string The properly capitalized name
 */
function ucname($string): string
{
    $string = ucwords(strtolower((string) $string));
    foreach (['-', '\''] as $delimiter) {
        if (str_contains($string, $delimiter)) {
            $string = implode($delimiter, array_map(ucfirst(...), explode($delimiter, $string)));
        }
    }
    return $string;
}

/**
 * Given an issue type as a string, compute its index.
 *
 * @param string $tstr The issue type string
 * @return int The index of the issue type
 * @global array $ISSUE_TYPES The array of issue types
 */
function issueTypeIndex($tstr)
{
    global $ISSUE_TYPES;
    $i = 0;
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($key == $tstr) {
            break;
        }
        ++$i;
    }
    return $i;
}

// ============================================================================
// HL7 Order Generation Helper Functions
// ============================================================================

/**
 * Escape special characters for HL7 text fields.
 *
 * @param string $s The input string
 * @return string The escaped string
 * @see http://www.interfaceware.com/hl7_escape_protocol.html
 */
function hl7Text($s)
{
    $s = str_replace('\\', '\\E\\', $s);
    $s = str_replace('^', '\\S\\', $s);
    $s = str_replace('|', '\\F\\', $s);
    $s = str_replace('~', '\\R\\', $s);
    $s = str_replace('&', '\\T\\', $s);
    $s = str_replace("\r", '\\X0d\\', $s);
    return $s;
}

/**
 * Format a ZIP code for HL7, removing spaces and dashes.
 *
 * @param string $s The input ZIP code
 * @return string The formatted ZIP code
 */
function hl7Zip($s)
{
    return hl7Text(preg_replace('/[-\s]*/', '', (string) $s));
}

/**
 * Convert sex/gender to HL7 format (M, F, or U).
 *
 * @param string $s The input sex value
 * @return string M, F, or U
 */
function hl7Sex($s)
{
    $s = strtoupper(substr((string) $s, 0, 1));
    if ($s !== 'M' && $s !== 'F') {
        $s = 'U';
    }
    return $s;
}

/**
 * Convert a datetime string to HL7 timestamp format.
 *
 * @param string $s The datetime string
 * @param bool $withSeconds Whether to include seconds (YmdHis) or not (YmdHi)
 * @return string The formatted timestamp, or empty string if input is empty
 */
function hl7Time($s, bool $withSeconds)
{
    if (empty($s)) {
        return '';
    }
    $format = $withSeconds ? 'YmdHis' : 'YmdHi';
    return date($format, strtotime((string) $s));
}

/**
 * Format a phone number for HL7.
 *
 * @param string $s The phone number string
 * @param bool $formatted Whether to include formatting like (555)123-4567 or just digits
 * @return string The formatted phone number, or empty string if invalid
 */
function hl7Phone($s, bool $formatted)
{
    if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)\D*$/", (string) $s, $tmp)) {
        return $formatted
            ? '(' . $tmp[1] . ')' . $tmp[2] . '-' . $tmp[3]
            : $tmp[1] . $tmp[2] . $tmp[3];
    }
    if (preg_match("/(\d\d\d)\D*(\d\d\d\d)\D*$/", (string) $s, $tmp)) {
        return $formatted
            ? $tmp[1] . '-' . $tmp[2]
            : $tmp[1] . $tmp[2];
    }
    return '';
}

/**
 * Format an SSN for HL7.
 *
 * @param string $s The SSN string
 * @param bool $withDashes Whether to include dashes (123-45-6789) or just digits
 * @return string The formatted SSN, or empty string if invalid
 */
function hl7SSN($s, bool $withDashes)
{
    if (preg_match("/(\d\d\d)\D*(\d\d)\D*(\d\d\d\d)\D*$/", (string) $s, $tmp)) {
        return $withDashes
            ? $tmp[1] . '-' . $tmp[2] . '-' . $tmp[3]
            : $tmp[1] . $tmp[2] . $tmp[3];
    }
    return '';
}

/**
 * Convert a relationship string to its word form for HL7.
 *
 * @param string $s The relationship string (e.g., 'self', 'spouse', 'child', 'other')
 * @return string The normalized relationship word, or the original value if unrecognized
 */
function hl7RelationWord(string $s): string
{
    return match (strtolower($s)) {
        '', 'self' => 'self',
        'spouse' => 'spouse',
        'child' => 'child',
        'other' => 'other',
        default => $s,
    };
}

/**
 * Convert a relationship string to an HL7 Table 0063 relationship code.
 *
 * @param string $s The relationship string (e.g., 'self', 'spouse', 'child', 'other')
 * @param bool $childAsOther Whether to treat 'child' as 'other' (code 8) instead of code 3
 * @return string The HL7 relationship code, or the original value if unrecognized
 */
function hl7RelationCode(string $s, bool $childAsOther): string
{
    return match (strtolower($s)) {
        '', 'self' => '1',
        'spouse' => '2',
        'child' => $childAsOther ? '8' : '3',
        'other' => '8',
        default => $s,
    };
}

function rbvalue($rbname): string
{
    $tmp = $_POST[$rbname];
    if (! $tmp) {
        $tmp = '0';
    }

    return "$tmp";
}

function cbvalue($cbname): string
{
    return $_POST[$cbname] ? '1' : '0';
}

function rbinput($name, $value, $desc, $colname): string
{
    global $row;
    $ret  = "<input type='radio' name='" . attr($name) . "' value='" . attr($value) . "'";
    if ($row[$colname] == $value) {
        $ret .= " checked";
    }

    $ret .= " />" . text($desc);
    return $ret;
}

function rbcell($name, $value, $desc, $colname): string
{
    return "<td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

function cbinput($name, $colname): string
{
    global $row;
    $ret  = "<input type='checkbox' name='" . attr($name) . "' value='1'";
    if ($row[$colname]) {
        $ret .= " checked";
    }

    $ret .= " />";
    return $ret;
}

function cbcell($name, $desc, $colname): string
{
    return "<td width='25%' nowrap>" . cbinput($name, $colname) . text($desc) . "</td>\n";
}

/**
 * Adapt text to be suitable as the contents of a table cell.
 *
 * @param  string $s Input text.
 * @return string  Output text.
 */
function myCellText($s)
{
    $s = trim($s ?? '');
    if ($s === '') {
        return '&nbsp;';
    }

    return text($s);
}

/**
 * Get a list item title, translating if required.
 *
 * @param  string $listid List identifier.
 * @param  string $value List item identifier.
 * @return string  The item's title.
 */
function getListItem($listid, $value)
{
    $title = QueryUtils::fetchSingleValue(
        <<<'SQL'
        SELECT title
        FROM list_options
        WHERE list_id = ? AND option_id = ? AND activity = 1
        LIMIT 1
        SQL,
        'title',
        [$listid, $value]
    );
    $tmp = xl_list_label($title);
    if (empty($tmp)) {
        $tmp = (($value === '') ? '' : "($value)");
    }

    return $tmp;
}

// ============================================================================
// Tax Calculation Functions
// ============================================================================

/**
 * Compute taxes from a tax rate string and a possibly taxable amount.
 *
 * @param array $row Row containing 'taxrates' field (colon-separated rate IDs)
 * @param float $amount The taxable amount
 * @return float The total tax amount
 */
function calcTaxes($row, $amount)
{
    $total = 0;
    if (empty($row['taxrates'])) {
        return $total;
    }

    $arates = explode(':', (string) $row['taxrates']);
    if (empty($arates)) {
        return $total;
    }

    foreach ($arates as $value) {
        if (empty($value)) {
            continue;
        }

        $trow = sqlQuery(
            "SELECT option_value FROM list_options WHERE " .
            "list_id = 'taxrate' AND option_id = ? AND activity = 1 LIMIT 1",
            [$value]
        );
        if (empty($trow['option_value'])) {
            echo "<!-- Missing tax rate '" . text($value) . "'! -->\n";
            continue;
        }

        $tax = sprintf("%01.2f", $amount * $trow['option_value']);
        $total += $tax;
    }

    return $total;
}

/**
 * Mark the tax rates that are referenced in an invoice.
 *
 * @param string $taxrates Colon-separated tax rate IDs
 * @return void
 * @global array $taxes The taxes array (key=tax id, value=[description, rate, indicator])
 */
function markTaxes($taxrates): void
{
    global $taxes;
    $arates = explode(':', (string) $taxrates);
    if (empty($arates)) {
        return;
    }
    foreach ($arates as $value) {
        if (!empty($taxes[$value])) {
            $taxes[$value][2] = '1';
        }
    }
}

/**
 * Get facilities data and build facility-to-message and facility-to-phone maps.
 *
 * @return array{msg_map: array<int, string>, phone_map: array<int, string>}
 */
function cron_getFacilitiesMap(FacilityService $facilityService)
{
    $message_map = $GLOBALS['phone_appt_message'];
    $facility_msg_map = [];
    $facility_phone_map = [];

    $facilities = $facilityService->getAllFacility();
    foreach ($facilities as $row) {
        $facility_msg_map[$row['id']] = $message_map[$row['name']];
        $facility_phone_map[$row['id']] = $row['phone'];
    }

    return [
        'msg_map' => $facility_msg_map,
        'phone_map' => $facility_phone_map
    ];
}

/**
 * Get notification settings from the database.
 *
 * @return array|false The notification settings row, or false if not found
 */
function cron_GetNotificationSettings(): array|false
{
    return sqlFetchArray(sqlStatement(
        "SELECT * FROM notification_settings WHERE type = 'SMS/Email Settings'"
    ));
}

/**
 * Update calendar event to mark that an alert was sent to the patient.
 *
 * @param string $type The notification type ('SMS', 'Email', or 'Phone')
 * @param int|string $pid The patient ID
 * @param int|string $pc_eid The calendar event ID
 */
function cron_updateentry(string $type, $pid, $pc_eid): void
{
    $query = "UPDATE openemr_postcalendar_events SET ";

    if ($type === 'SMS' || $type === 'Phone') {
        $query .= "pc_sendalertsms = 'YES'";
    } elseif ($type === 'Email') {
        $query .= "pc_sendalertemail = 'YES'";
    }

    $query .= " WHERE pc_pid = ? AND pc_eid = ?";
    sqlStatement($query, [$pid, $pc_eid]);
}

// ============================================================================
// Layout Options Functions
// ============================================================================

/**
 * Get layout options for the demographics form.
 *
 * @param bool $shortForm Whether to filter for short form mode (only required fields or those with 'N' edit option)
 * @return ADORecordSet_mysqli
 */
function getLayoutRes(bool $shortForm)
{
    $sql = "SELECT * FROM layout_options WHERE form_id = 'DEM' AND uor > 0 AND field_id != ''";
    if ($shortForm) {
        $sql .= " AND (uor > 1 OR edit_options LIKE '%N%')";
    }
    $sql .= " ORDER BY group_id, seq";
    return sqlStatement($sql);
}

/**
 * Get the UOR (Use, Optional, Required) value for a layout field.
 *
 * @param string $form_id The form ID
 * @param string $field_id The field ID
 * @return int The UOR value (0=unused, 1=optional, 2=required)
 */
function getLayoutUOR($form_id, $field_id)
{
    $crow = sqlQuery("SELECT uor FROM layout_options WHERE " .
        "form_id = ? AND field_id = ? LIMIT 1", [$form_id, $field_id]);
    return 0 + $crow['uor'];
}
