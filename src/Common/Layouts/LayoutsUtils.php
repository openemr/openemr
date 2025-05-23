<?php

/**
 * LayoutsUtils class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2023 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Layouts;

class LayoutsUtils
{
    public static function getListItemTitle($list, $option)
    {
        $row = sqlQuery("SELECT `title` FROM `list_options` WHERE `list_id` = ? AND `option_id` = ? AND activity = 1", [$list, $option]);
        if (empty($row['title'])) {
            return $option;
        }
        return xl_list_label($row['title']);
    }

    /**
     * Test if modifier($test) is in array of options for data type.
     *
     * @param json array $options ["G","P","T"], ["G"] or could be legacy string with form "GPT", "G", "012"
     * @param string $test
     * @return boolean
     */
    public static function isOption($options, string $test): bool
    {
        if (empty($options) || !isset($test) || $options == "null") {
            return false; // why bother?
        }
        if (strpos($options, ',') === false) { // not json array of modifiers.
            // could be string of char's or single element of json ["RO"] or "TP" or "P" e.t.c.
            json_decode($options, true); // test if options json. json_last_error() will return JSON_ERROR_SYNTAX if not.
            // if of form ["RO"] (single modifier) means not legacy so continue on.
            if (is_string($options) && (json_last_error() !== JSON_ERROR_NONE)) { // nope, it's string.
                $t = str_split(trim($options)); // very good chance it's legacy modifier string.
                $options = json_encode($t); // make it json array to convert from legacy to new modifier json schema.
            }
        }

        $options = json_decode($options, true); // all should now be json

        return is_array($options) && in_array($test, $options, true); // finally the truth!
    }
}
