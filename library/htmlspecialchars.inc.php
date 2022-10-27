<?php

/**
 * Escaping Functions
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Boyd Stephen Smith Jr.
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Boyd Stephen Smith Jr.
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Escape a javascript literal.
 */
function js_escape($text)
{
    return json_encode($text);
}

/**
 * Escape a javascript literal within html onclick attribute.
 */
function attr_js($text)
{
    return attr(json_encode($text));
}

/**
 * Escape html and url encode a url item.
 */
function attr_url($text)
{
    return attr(urlencode($text ?? ''));
}

/**
 * Escape js and url encode a url item.
 */
function js_url($text)
{
    return js_escape(urlencode($text));
}

/**
 * Escape variables that are outputted into the php error log.
 */
function errorLogEscape($text)
{
    return attr($text);
}

/**
 * Escape variables that are outputted into csv and spreadsheet files.
 * See here: https://www.owasp.org/index.php/CSV_Injection
 * Based mitigation strategy on this report: https://asecurityz.blogspot.com/2017/12/csv-injection-mitigations.html
 *  1. Remove all the following characters:  = + " |
 *  2. Only remove leading - characters (since need in dates)
 *  3. Only remove leading @ characters (since need in email addresses)
 *  4. Surround with double quotes (no reference link, but seems very reasonable, which will prevent commas from breaking things).
 * If needed in future, will add a second parameter called 'options' which will be an array of option tokens that will allow
 * less stringent (or more stringent) mechanisms to escape for csv.
 */
function csvEscape($text)
{
    // 1. Remove all the following characters:  = + " |
    $text = preg_replace('/[=+"|]/', '', $text);

    // 2. Only remove leading - characters (since need in dates)
    // 3. Only remove leading @ characters (since need in email addresses)
    $text = preg_replace('/^[\-@]+/', '', $text);

    // 4. Surround with double quotes (no reference link, but seems very reasonable, which will prevent commas from breaking things).
    return '"' . $text . '"';
}

/**
 *
 * references: https://stackoverflow.com/questions/3426090/how-do-you-make-strings-xml-safe
 *             https://www.php.net/htmlspecialchars
 *             https://www.php.net/XMLWriter
 *
 *
 * Escapes & < > ' "
 * TODO: not sure if need to escape ' and ", which are escaping for now (via the ENT_QUOTES flag)
 */
function xmlEscape($text)
{
    return htmlspecialchars(($text ?? ''), ENT_XML1 | ENT_QUOTES);
}

/**
 * Special function to remove the 'javascript' string (case insensitive) for when including a variable within a html link
 */
function javascriptStringRemove($text)
{
    return str_ireplace('javascript', '', $text ?? '');
}

/**
 * Escape a PHP string for use as (part of) an HTML / XML text node.
 *
 * It only escapes a few special chars: the ampersand (&) and both the left-
 * pointing angle bracket (<) and the right-pointing angle bracket (>), since
 * these are the only characters that are special in a text node.  Minimal
 * quoting is preferred because it produces smaller and more easily human-
 * readable output.
 *
 * Some characters simply cannot appear in valid XML documents, even
 * as entities but, this function does not attempt to handle them.
 *
 * NOTE: Attribute values are NOT text nodes, and require additional escaping.
 *
 * @param string $text The string to escape, possibly including "&", "<",
 *                     or ">".
 * @return string The string, with "&", "<", and ">" escaped.
 */
function text($text)
{
    return htmlspecialchars(($text ?? ''), ENT_NOQUOTES);
}

/**
 * Given an array of properties run through both the keys and values of the array and
 * escape each PHP string for use as (part of) an HTML / XML text node.
 *
 * It only escapes a few special chars: the ampersand (&) and both the left-
 * pointing angle bracket (<) and the right-pointing angle bracket (>), since
 * these are the only characters that are special in a text node.  Minimal
 * quoting is preferred because it produces smaller and more easily human-
 * readable output.
 *
 * Some characters simply cannot appear in valid XML documents, even
 * as entities but, this function does not attempt to handle them.
 *
 * NOTE: Attribute values are NOT text nodes, and require additional escaping.
 *
 * @param string $arr The array of strings to escape, possibly including "&", "<",
 *                     or ">".
 * @param int $depth The current recursive depth of the escaping function.  Defaults to 0 for initial call
 * @return array The array that has each key and property escaped.
 */
function textArray(array $arr, $depth = 0)
{
    if ($depth > 50) {
        throw new \InvalidArgumentException("array was nested too deep for escaping.  Max limit reached");
    }

    $newArray = [];
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            $newArray[text($key)] = textArray($value, $depth + 1);
        } else {
            $newArray[text($key)] = text($value);
        }
    }
    return $newArray;
}

/**
 * Escape a PHP string for use as (part of) an HTML / XML attribute value.
 *
 * It escapes several special chars: the ampersand (&), the double quote
 * ("), the singlequote ('), and both the left-pointing angle bracket (<)
 * and the right-pointing angle bracket (>), since these are the characters
 * that are special in an attribute value.
 *
 * Some characters simply cannot appear in valid XML documents, even
 * as entities but, this function does not attempt to handle them.
 *
 * NOTE: This can be used as a "generic" HTML escape since it does maximal
 * quoting.  However, some HTML and XML contexts (CDATA) don't provide
 * escape mechanisms.  Also, further pre- or post-escaping might need to
 * be done when embdedded other languages (like JavaScript) inside HTML /
 * XML documents.
 *
 * @param string $text The string to escape, possibly including (&), (<),
 *                     (>), ('), and (").
 * @return string The string, with (&), (<), (>), ("), and (') escaped.
 */
function attr($text)
{
    return htmlspecialchars(($text ?? ''), ENT_QUOTES);
}

/**
 * Don't call this function.  You don't see this function.  This function
 * doesn't exist.
 *
 * TODO: Hide this function so it can be called from this file but not from
 * PHP that includes / requires this file.  Either that, or write reasonable
 * documentation and clean up the name.
 */
function hsc_private_xl_or_warn($key)
{
    if (function_exists('xl')) {
        return xl($key);
    } else {
        trigger_error(
            'Translation via xl() was requested, but the xl()'
            . ' function is not defined, yet.',
            E_USER_WARNING
        );
        return $key;
    }
}

/**
 * Translate via xl() and then escape via text().
 *
 * @param string $key The string to escape, possibly including "&", "<",
 *                    or ">".
 * @return string The string, with "&", "<", and ">" escaped.
 */
function xlt($key)
{
    return text(hsc_private_xl_or_warn($key));
}

/**
 * Translate via xl() and then escape via attr().
 *
 * @param string $key The string to escape, possibly including (&), (<),
 *                    (>), ('), and (").
 * @return string The string, with (&), (<), (>), ("), and (') escaped.
 */
function xla($key)
{
    return attr(hsc_private_xl_or_warn($key));
}

/*
 * Translate via xl() and then escape via js_escape for use with javascript literals
 */
function xlj($key)
{
    return js_escape(hsc_private_xl_or_warn($key));
}

/*
 * Deprecated
 *Translate via xl() and then escape via addslashes for use with javascript literals
 */
function xls($key)
{
    return addslashes(hsc_private_xl_or_warn($key));
}
