<?php

/**
 * interface/super/rules/include/ui.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/options.inc.php");

function getLabel($value, $list_id)
{
    // get from list_options
    $result = generate_display_field(array('data_type' => '1','list_id' => $list_id), $value);
    // trap for fa-exclamation-circle used to indicate empty input from layouts options.
    if ($result != '' && stripos($result, 'fa-exclamation-circle') === false) {
        return $result;
    }

    // if not found, default to the passed-in value
    return $value;
}

function getLayoutLabel($value, $form_id)
{
    // get from layout_options
    $sql = sqlStatement(
        "SELECT title from layout_options WHERE form_id = ? and field_id = ?",
        array($form_id, $value)
    );
    if (sqlNumRows($sql) > 0) {
        $result = sqlFetchArray($sql);
        return xl($result['title']);
    }

// if not found, default to the passed-in value
    return $value;
}

function getListOptions($list_id)
{
    $options = array();
    $sql = sqlStatement(
        "SELECT option_id, title from list_options WHERE list_id = ? AND activity = 1",
        array($list_id)
    );
    for ($iter = 0; $row = sqlFetchArray($sql); $iter++) {
        $options[] = new Option(
            $row['option_id'],            // id
            xl_list_label($row['title'])  // label
        );
    }

    return $options;
}

function getListOptionsArray($list_id)
{
    $optionsArray = array();
    foreach (getListOptions($list_id) as $option) {
        $optionsArray[] = array( "id" => $option->id, "label" => $option->label );
    }

    return $optionsArray;
}
