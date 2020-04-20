<?php

/**
 * library to simplify processing code_types
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function diag_code_types($format = 'json', $sqlEscape = false)
{
    global $code_types;
    $diagCodes = array();
    foreach ($code_types as $key => $ct) {
        if ($ct['active'] && $ct['diag']) {
            if ($format == 'json') {
                $entry = array("key" => $key,"id" => $ct['id']);
            } elseif ($format == 'keylist') {
                $entry = "'";
                $entry .= $sqlEscape ? add_escape_custom($key) : $key;
                $entry .= "'";
            }

            array_push($diagCodes, $entry);
        }
    }

    if ($format == 'json') {
        return json_encode($diagCodes);
    }

    if ($format == 'keylist') {
        return implode(",", $diagCodes);
    }
}
