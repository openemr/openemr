<?php

/**
 * Provider List Type represents a user selector widget for displaying local users that can be used in the LBF forms or independently in the system.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms\Types;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Layouts\LayoutsUtils;

class LocalProviderListType
{
    const OPTIONS_TYPE_INDEX = 11;

    private string $providerListQuery = "";

    private ?array $providerList;

    public function __construct()
    {
        $this->providerListQuery = "SELECT id, fname, lname, specialty FROM users " .
            "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
            "AND ( authorized = 1 OR ((username = '' OR username IS NULL) AND npi != '' )) " .
            "ORDER BY lname, fname";
        $this->providerList = null;
    }


    public function getProviderListQuery()
    {
        return $this->providerList;
    }

    public function getProviderList()
    {
        return $this->providerList;
    }

    public function setProviderListQuery(string $query)
    {
        $this->providerListQuery = $query;
    }
    public function setProviderList(array $providerList)
    {
        $this->providerList = $providerList;
    }

    private function getProviderFromId($id)
    {
        $urow = null;
        if ($this->providerList != null) {
            $index = array_search($this->providerList, fn($item) => $item['id'] == $id);
            if ($index !== false) {
                $urow = $this->providerList[$index];
            }
        } else {
            $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
                "WHERE id = ?", array($id));
        }
        return $urow;
    }
    public function buildPrintView($frow, $currvalue, $value_allowed = true)
    {
        $tmp = '';
        if ($currvalue) {
            $urow = $this->getProviderFromId($currvalue);
            $tmp = ucwords($urow['fname'] . " " . $urow['lname']);
            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
        }
        if ($tmp === '') {
            $tmp = '&nbsp;';
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

        echo $tmp;
    }

    public function buildPlaintextView($frow, $currvalue)
    {
        $urow = $this->getProviderFromId($currvalue);
        $s = ucwords($urow['fname'] . " " . $urow['lname']);
        return $s;
    }
    public function buildDisplayView($frow, $currvalue): string
    {
        $urow = $this->getProviderFromId($currvalue);
        $s = text(ucwords(($urow['fname'] ?? '') . " " . ($urow['lname'] ?? '')));
        return $s;
    }

    public function buildFormView($frow, $currvalue): string
    {
        $currescaped = htmlspecialchars($currvalue ?? '', ENT_QUOTES);
        $field_id = $frow['field_id'];
        $list_id = $frow['list_id'] ?? null;
        $edit_options = $frow['edit_options'] ?? null;
        $form_id = $frow['form_id'] ?? null;

        // 'smallform' can be 'true' if we want a smaller form field, otherwise
        // can be used to assign arbitrary CSS classes to data entry fields.
        $smallform = $frow['smallform'] ?? null;
        if ($smallform === 'true') {
            $smallform = ' form-control-sm';
        }

        // historically we've used smallform to append classes if the value is NOT true
        // to make it EXPLICIT what we are doing and to aid maintainability we are supporting
        // an actual 'classNames' attribute for assigning arbitrary CSS classes to data entry fields
        $classesToAppend = $frow['classNames'] ?? '';
        if (!empty($classesToAppend)) {
            $smallform = isset($smallform) ? $smallform . ' ' . $classesToAppend : $classesToAppend;
        }

        // escaped variables to use in html
        $field_id_esc = htmlspecialchars($field_id, ENT_QUOTES);

        $disabled = LayoutsUtils::isOption($edit_options, '0') === false ? '' : 'disabled';

        $lbfchange = (
            !empty($form_id) &&
            (
                strpos($form_id, 'LBF') === 0 ||
                strpos($form_id, 'LBT') === 0 ||
                strpos($form_id, 'DEM') === 0 ||
                strpos($form_id, 'HIS') === 0
            )
        ) ? "checkSkipConditions();" : "";
        $lbfonchange = $lbfchange ? "onchange='$lbfchange'" : "";

        // Added 5-09 by BM - Translate description if applicable
        $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');
        if (!empty($this->providerList)) {
            $urest = $this->providerList;
        } else {
            $urest = QueryUtils::fetchRecords($this->providerListQuery, []);
        }
        $result = [];
        $result[] = "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control$smallform'";
        $result[] = " $lbfonchange $disabled>";
        $result[] = "<option value=''>" . xlt('Unassigned') . "</option>";
        $got_selected = false;
        foreach ($urest as $urow) {
            $uname = text($urow['fname'] . ' ' . $urow['lname']);
            $optionId = attr($urow['id']);
            $result[] = "<option value='$optionId'";
            if ($urow['id'] == $currvalue) {
                $result[] = " selected";
                $got_selected = true;
            }

            $result[] = ">$uname</option>";
        }

        if (!$got_selected && $currvalue) {
            $result[] = "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            $result[] = "</select>";
            $result[] = " <span class='text-danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
        } else {
            $result[] = "</select>";
        }

        return implode("", $result);
    }
}
