<?php

/*
 * These are a handful of useful utilities
 * Functions that don't belong anyplace else at
 * the moment end up here
 */


/*
 * Pad the end of a string with &nbsp; up to 
 * the max length of the string
 */

function NBSPPadSuffix ($strInfo, $intMaxLength)
{
    $intN = $intMaxLength - strlen($strInfo);

    while ($intN > 0)
    {
        $strInfo = sprintf("%s&nbsp;", $strInfo);
        $intN--;
    }

    return $strInfo;
}


/*
 * properly quote the passed value
 * or return NULL if there is no value at all
 */
function SQLQuote ($strValue) 
{
    /* are we quoting a number or string? */

    if (is_string($strValue) == true) {
        /* It's a string */

        if (strlen($strValue) == 0) { return "NULL"; }
        if ($strValue == NULL) { return "NULL"; }
        /* remove any '\' values */
        $strValue = preg_replace("/\\\/", '', $strValue);
        return "'". preg_replace("/\'/", "''", $strValue) ."'";
    }
    else {
        /* It's a number */

        if (is_null($strValue)) { return "NULL"; }
        if ($strValue == 0) { return "0"; }
        else { return $strValue; }
    }
}


/*
 * Get the HTML (GET or POST) parameters
 */

function GetParameters ()
{
    if ($_SERVER["REQUEST_METHOD"]=="POST") {
        foreach ($_POST as $key => $value) {
//            echo $key."=".$value."<br>\n";
            $parameters[$key] = $value;
        }
    }
    else if ($_SERVER["REQUEST_METHOD"]=="GET") {
        foreach ($_GET as $key => $value) {
            $parameters[$key] = $value;
        }
    }
    return $parameters;
}

?>
