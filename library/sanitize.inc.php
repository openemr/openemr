<?php

/**
 * Function to check and/or sanitize things for security such as
 * directories names, file names, etc.
 * Also including csrf token management functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2012-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Events\Core\Sanitize\IsAcceptedFileFilterEvent;

// Function to collect ip address(es)
function collectIpAddresses()
{
    $mainIp = $_SERVER['REMOTE_ADDR'];
    $stringIp = $mainIp;

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwardIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        $stringIp .= " (" . $forwardIp . ")";
    }

    return array(
        'ip_string' => $stringIp,
        'ip' => $mainIp,
        'forward_ip' => $forwardIp ?? ''
    );
}

// Sanitize a json encoded entry.
function json_sanitize($json)
{
    if (json_decode($json)) {
        return json_encode(json_decode($json, true));
    } else {
        error_log("OPENEMR ERROR: " . errorLogEscape($json) . " is not a valid json ");
        return false;
    }
}

// If the label contains any illegal characters, then the script will die.
function check_file_dir_name($label)
{
    if (empty($label) || preg_match('/[^A-Za-z0-9_.-]/', $label)) {
        error_log("ERROR: The following variable contains invalid characters:" . errorLogEscape($label));
        die(xlt("ERROR: The following variable contains invalid characters") . ": " . attr($label));
    } else {
        return $label;
    }
}

// Convert all illegal characters to _
function convert_safe_file_dir_name($label)
{
    return preg_replace('/[^A-Za-z0-9_.-]/', '_', $label);
}

// Convert all non A-Z a-z 0-9 characters to _
function convert_very_strict_label($label)
{
    return preg_replace('/[^A-Za-z0-9]/', '_', $label);
}

// Check integer
function check_integer($value)
{
    return (empty(preg_match('/[^0-9]/', $value)));
}

//Basename functionality for nonenglish languages (without this, basename function omits nonenglish characters).
function basename_international($path)
{
    $parts = preg_split('~[\\\\/]~', $path);
    foreach ($parts as $key => $value) {
        $encoded = urlencode($value);
        $parts[$key] = $encoded;
    }

    $encoded_path = implode("/", $parts);
    $encoded_file_name = basename($encoded_path);
    $decoded_file_name = urldecode($encoded_file_name);

    return $decoded_file_name;
}


/**
 * This function detects a MIME type for a file and check if it in the white list of the allowed mime types.
 * @param string $file - file location.
 * @param array|null $whiteList - array of mime types that allowed to upload.
 */
// Regarding the variable below. In the case of multiple file upload the isWhiteList function will run multiple
// times, therefore, storing the white list in the variable below to prevent multiple requests from database.
$white_list = null;
function isWhiteFile($file)
{
    global $white_list;
    if (is_null($white_list)) {
        $white_list = array();
        $lres = sqlStatement("SELECT option_id FROM list_options WHERE list_id = 'files_white_list' AND activity = 1");
        while ($lrow = sqlFetchArray($lres)) {
            $white_list[] = $lrow['option_id'];
        }
        // allow module writers to modify the white list... this only gets executed the first time this function runs
        $event = new IsAcceptedFileFilterEvent($file, $white_list);
        $resultEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch(IsAcceptedFileFilterEvent::EVENT_GET_ACCEPTED_LIST, $event);
        $white_list = $resultEvent->getAcceptedList();
    }

    $mimetype  = mime_content_type($file);
    $isAllowedFile = false;
    if (in_array($mimetype, $white_list)) {
        $isAllowedFile = true;
    } else {
        $splitMimeType = explode('/', $mimetype);
        $categoryType = $splitMimeType[0];
        if (in_array($categoryType . '/*', $white_list)) {
            $isAllowedFile = true;
        } else if (isset($GLOBALS['kernel'])) {
            // we can fire off an event
            // allow module writers to modify the isWhiteFile on the fly.
            $event = new IsAcceptedFileFilterEvent($file, $white_list);
            $event->setAllowedFile(false);
            $event->setMimeType($mimetype);
            $resultEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch(IsAcceptedFileFilterEvent::EVENT_FILTER_IS_ACCEPTED_FILE, $event);
            $isAllowedFile = $resultEvent->isAllowedFile();
        }
    }

    return $isAllowedFile;
}

// Sanitize a value to ensure it is a number.
function sanitizeNumber($number)
{
    $clean_number = $number + 0 ;

    if ($clean_number == $number) {
        return $clean_number;
    } else {
        error_log('Custom validation error: Parameter contains non-numeric value (A numeric value expected)');
        return $clean_number;
    }
}

/**
 * Function to get sql statement for empty datetime check.
 *
 * @param  string  $sqlColumn     SQL column/field name
 * @param  boolean  $time         flag used to determine if it's a datetime or a date
 * @param  boolean  $rev          flag used to reverse the condition
 * @return string                 SQL statement checking if passed column is empty
 */

function dateEmptySql($sqlColumn, $time = false, $rev = false)
{
    if (!$rev) {
        if ($time) {
            $stat = " (`"  .  $sqlColumn . "` IS NULL OR `" .  $sqlColumn . "`= '0000-00-00 00:00:00') ";
        } else {
            $stat = " (`"  .  $sqlColumn . "` IS NULL OR `" .  $sqlColumn . "`= '0000-00-00') ";
        }
    } else {
        if ($time) {
            $stat = " (`"  .  $sqlColumn . "` IS NOT NULL AND `" .  $sqlColumn . "`!= '0000-00-00 00:00:00') ";
        } else {
            $stat = " (`"  .  $sqlColumn . "` IS NOT NULL AND `" .  $sqlColumn . "`!= '0000-00-00') ";
        }
    }

    return $stat;
}
