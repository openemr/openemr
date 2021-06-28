<?php

/**
 * Administration Lists Module.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2007-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Below allows the list to default to the first item on the list
//   when list_id is blank.
$blank_list_id = '';
if (empty($_REQUEST['list_id'])) {
    $list_id = 'language';
    $blank_list_id = true;
} else {
    $list_id = $_REQUEST['list_id'];
}

// Check authorization.
$thisauth = AclMain::aclCheckCore('admin', 'super');
if (!$thisauth) {
    die(xlt('Not authorized'));
}

//Limit variables for filter
$records_per_page = 40;
$list_from = ( isset($_REQUEST["list_from"]) ? intval($_REQUEST["list_from"]) : 1 );
$list_to   = ( isset($_REQUEST["list_to"])   ? intval($_REQUEST["list_to"]) : 0);

// If we are saving, then save.
//
if (!empty($_POST['formaction']) && ($_POST['formaction'] == 'save') && $list_id) {
    $opt = $_POST['opt'];
    if ($list_id == 'feesheet') {
        // special case for the feesheet list
        sqlStatement("DELETE FROM fee_sheet_options");
        for ($lino = 1; isset($opt["$lino"]['category']); ++$lino) {
            $iter = $opt["$lino"];
            $category = trim($iter['category']);
            $option = trim($iter['option']);
            $codes = trim($iter['codes']);
            if (strlen($category) > 0 && strlen($option) > 0) {
                sqlStatement("INSERT INTO fee_sheet_options ( " .
                    "fs_category, fs_option, fs_codes " .
                    ") VALUES ( ?,?,? )", array($category, $option, $codes));
            }
        }
    } elseif ($list_id == 'code_types') {
        // special case for code types
        sqlStatement("DELETE FROM code_types");
        for ($lino = 1; isset($opt["$lino"]['ct_key']); ++$lino) {
            $iter = $opt["$lino"];
            $ct_key = trim($iter['ct_key']);
            $ct_id = (int)trim($iter['ct_id']);
            $ct_seq = (int)trim($iter['ct_seq']);
            $ct_mod = (int)trim($iter['ct_mod']);
            $ct_just = trim($iter['ct_just']);
            $ct_mask = trim($iter['ct_mask']);
            $ct_fee = empty($iter['ct_fee']) ? 0 : 1;
            $ct_rel = empty($iter['ct_rel']) ? 0 : 1;
            $ct_nofs = empty($iter['ct_nofs']) ? 0 : 1;
            $ct_diag = empty($iter['ct_diag']) ? 0 : 1;
            $ct_active = empty($iter['ct_active']) ? 0 : 1;
            $ct_label = trim($iter['ct_label']);
            $ct_external = (int)trim($iter['ct_external']);
            $ct_claim = empty($iter['ct_claim']) ? 0 : 1;
            $ct_proc = empty($iter['ct_proc']) ? 0 : 1;
            $ct_term = empty($iter['ct_term']) ? 0 : 1;
            $ct_problem = empty($iter['ct_problem']) ? 0 : 1;
            $ct_drug = empty($iter['ct_drug']) ? 0 : 1;
            if (strlen($ct_key) > 0 && $ct_id > 0) {
                sqlStatement(
                    "INSERT INTO code_types ( " .
                    "ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_mask, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external, ct_claim, ct_proc, ct_term, ct_problem, ct_drug " .
                    ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    array(
                        $ct_key,
                        $ct_id,
                        $ct_seq,
                        $ct_mod,
                        $ct_just,
                        $ct_mask,
                        $ct_fee,
                        $ct_rel,
                        $ct_nofs,
                        $ct_diag,
                        $ct_active,
                        $ct_label,
                        $ct_external,
                        $ct_claim,
                        $ct_proc,
                        $ct_term,
                        $ct_problem,
                        $ct_drug
                    )
                );
            }
        }
    } elseif ($list_id == 'issue_types') {
        // special case for issue_types
        sqlStatement("DELETE FROM issue_types");
        for ($lino = 1; isset($opt["$lino"]['category']); ++$lino) {
            $iter = $opt["$lino"];
            $it_category = trim($iter['category']);
            $it_type = trim($iter['type']);
            if ((strlen($it_category) > 0) && (strlen($it_type) > 0)) {
                sqlStatement("INSERT INTO issue_types (" .
                    "`active`,`category`,`ordering`, `type`, `plural`, `singular`, `abbreviation`, `style`, " .
                    "`force_show`, `aco_spec`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
                    trim($iter['active']),
                    $it_category,
                    trim($iter['ordering']),
                    $it_type,
                    trim($iter['plural']),
                    trim($iter['singular']),
                    trim($iter['abbreviation']),
                    trim($iter['style']),
                    trim($iter['force_show']),
                    trim($iter['aco_spec']),
                ));
            }
        }
    } else {
        // all other lists
        //
        // collect the option toggle if using the 'immunizations' list
        if ($list_id == 'immunizations') {
            $ok_map_cvx_codes = isset($_POST['ok_map_cvx_codes']) ? $_POST['ok_map_cvx_codes'] : 0;
        }

        for ($lino = 1; isset($opt["$lino"]['id']); ++$lino) {
            $iter = $opt["$lino"];
            $value = empty($iter['value']) ? 0 : (trim($iter['value']));
            $id = trim($iter['id']);
            $real_id = trim($iter['real_id']);

            if (strlen($real_id) > 0 || strlen($id) > 0) {
                // Special processing for the immunizations list
                // Map the entered cvx codes into the immunizations table cvx_code
                // Ensure the following conditions are met to do this:
                //   $list_id == 'immunizations'
                //   $value is an integer and greater than 0
                //   $id is set, not empty and not equal to 0
                //    (note that all these filters are important. Not allowing $id
                //     of zero here is extremely important; never remove this conditional
                //     or you risk corrupting your current immunizations database entries)
                //   $ok_map_cvx_codes is equal to 1
                if (
                    $list_id == 'immunizations' &&
                    is_int($value) &&
                    $value > 0 &&
                    isset($id) &&
                    !empty($id) &&
                    $id != 0 &&
                    $ok_map_cvx_codes == 1
                ) {
                    sqlStatement("UPDATE `immunizations` " .
                        "SET `cvx_code`= ? " .
                        "WHERE `immunization_id`= ? ", array($value, $id));
                }

                // Force List Based Form names to start with LBF.
                if ($list_id == 'lbfnames' && substr($id, 0, 3) != 'LBF') {
                    $id = "LBF$id";
                    $real_id = "LBF$real_id";
                }

                // Force Transaction Form names to start with LBT.
                if ($list_id == 'transactions' && substr($id, 0, 3) != 'LBT') {
                    $id = "LBT$id";
                    $real_id = "LBT$real_id";
                }

                if ($list_id == 'apptstat' || $list_id == 'groupstat') {
                    $notes = trim($iter['apptstat_color']) . '|' . trim($iter['apptstat_timealert']);
                } else {
                    $notes = trim($iter['notes']);
                }

                if (preg_match("/Eye_QP_/", $list_id)) {
                    if (preg_match("/^[BLR]/", $id)) {
                        $stuff = explode("_", $id)[0];
                        $iter['mapping'] = substr($stuff, 1);
                        $iter['subtype'] = substr($stuff, 0, 1);
                    } else {
                        $stuff = explode("_", $id)[0];
                        $iter['mapping'] = substr($stuff, 2);
                        $iter['subtype'] = substr($stuff, 0, 2);
                    }
                }

                // Delete the list item
                sqlStatement("DELETE FROM list_options WHERE list_id = ? AND option_id = ?", array($list_id, $real_id));
                if (strlen($id) <= 0 && strlen(trim($iter['title'])) <= 0 && empty($id) && empty($iter['title'])) {
                    continue;
                }
                // Insert the list item
                sqlStatement(
                    "INSERT INTO list_options ( " .
                    "list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype " .
                    ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    array(
                        $list_id,
                        $id,
                        trim($iter['title']),
                        trim($iter['seq']),
                        trim($iter['default'] ?? 0),
                        $value,
                        trim($iter['mapping'] ?? ''),
                        $notes,
                        trim($iter['codes']),
                        trim($iter['toggle_setting_1'] ?? 0),
                        trim($iter['toggle_setting_2'] ?? 0),
                        trim($iter['activity'] ?? 0),
                        trim($iter['subtype'] ?? '')
                    )
                );
            }
        }
    }
} elseif (!empty($_POST['formaction']) && ($_POST['formaction'] == 'addlist')) {
    // make a new list ID from the new list name
    $newlistID = $_POST['newlistname'];
    $newlistID = preg_replace("/\W/", "_", $newlistID);

    // determine the position of this new list
    $row = sqlQuery("SELECT max(seq) AS maxseq FROM list_options WHERE list_id= 'lists'");
    $dup_cnt = sqlQuery("SELECT count(seq) as validate FROM list_options WHERE list_id= 'lists' AND option_id = ?", array($newlistID))['validate'];
    if ((int)$dup_cnt === 0) {
        // add the new list to the list-of-lists
        sqlStatement("INSERT INTO list_options ( " .
            "list_id, option_id, title, seq, is_default, option_value " .
            ") VALUES ( 'lists', ?, ?, ?, '1', '0')", array($newlistID, $_POST['newlistname'], ($row['maxseq'] + 1)));
        $list_id = $newlistID;
    } else {
        // send error and continue.
        echo "<script>let error=" . js_escape(xlt("The new list") . " [" . $_POST['newlistname'] . "] " . xlt("already exists! Please try again.")) . ";</script>";
    }
} elseif (!empty($_POST['formaction']) && ($_POST['formaction'] == 'deletelist')) {
    // delete the lists options
    sqlStatement("DELETE FROM list_options WHERE list_id = ?", array($_POST['list_id']));
    // delete the list from the master list-of-lists
    sqlStatement("DELETE FROM list_options WHERE list_id = 'lists' AND option_id=?", array($_POST['list_id']));
}

$opt_line_no = 0;

// Given a string of multiple instances of code_type|code|selector,
// make a description for each.
// @TODO Instead should use a function from custom/code_types.inc.php and need to remove casing functions
function getCodeDescriptions($codes)
{
    global $code_types;
    $arrcodes = explode('~', $codes);
    $s = '';
    foreach ($arrcodes as $codestring) {
        if ($codestring === '') {
            continue;
        }
        $arrcode = explode('|', $codestring);
        $code_type = $arrcode[0];
        $code = $arrcode[1];
        $selector = $arrcode[2];
        $desc = '';
        if ($code_type == 'PROD') {
            $row = sqlQuery("SELECT name FROM drugs WHERE drug_id = ?", array($code));
            $desc = "$code:$selector " . $row['name'];
        } else {
            $row = sqlQuery("SELECT code_text FROM codes WHERE " .
                "code_type = ? AND " .
                "code = ? ORDER BY modifier LIMIT 1", array($code_types[$code_type]['id'], $code ));
            $desc = "$code_type:$code " . ucfirst(strtolower($row['code_text']));
        }
        $desc = str_replace('~', ' ', $desc);
        if ($s) {
            $s .= '~';
        }
        $s .= $desc;
    }
    return $s;
}

// Write one option line to the form.
//
function writeOptionLine(
    $option_id,
    $title,
    $seq,
    $default,
    $value,
    $mapping = '',
    $notes = '',
    $codes = '',
    $tog1 = '',
    $tog2 = '',
    $active = '1',
    $subtype = ''
) {

    global $opt_line_no, $list_id;
    ++$opt_line_no;
    $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");
    $checked = $default ? " checked" : "";
    $checked_tog1 = $tog1 ? " checked" : "";
    $checked_tog2 = $tog2 ? " checked" : "";
    $checked_active = $active ? " checked" : "";

    echo " <tr>\n";

    echo "  <td>";
    //New line for hidden input, for update items
    echo "<input type='hidden' name='opt[" . attr($opt_line_no) . "][real_id]' value='" .
        attr($option_id) . "' size='12' maxlength='127' class='optin' />";
    echo "<input type='text' name='opt[" . attr($opt_line_no) . "][id]' value='" .
        attr($option_id) . "' size='12' maxlength='127' class='optin' />";
    echo "</td>\n";
    echo "  <td>";
    echo "<input type='text' name='opt[" . attr($opt_line_no) . "][title]' value='" .
        attr($title) . "' size='20' maxlength='127' class='optin' />";
    echo "</td>\n";

    // if not english and translating lists then show the translation
    if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
        echo "  <td align='center' class='translation'>" . xlt($title) . "</td>\n";
    }
    echo "  <td>";
    echo "<input type='text' name='opt[" . attr($opt_line_no) . "][seq]' value='" .
        attr($seq) . "' size='4' maxlength='10' class='optin' />";
    echo "</td>\n";

    echo "  <td>";
    echo "<input type='checkbox' name='opt[" . attr($opt_line_no) . "][default]' value='1' " .
        "onclick='defClicked(" . attr($opt_line_no) . ")' class='optin'$checked />";
    echo "</td>\n";

    if (preg_match('/Eye_QP_/', $list_id)) {
        echo "  <td>";
        echo "<select name='opt[" . attr($opt_line_no) . "][activity]' class='optin'>";
        foreach (
            array(
                     1 => xl('Replace'),
                     2 => xl('Append')
                 ) as $key => $desc
        ) {
            echo "<option value='" . attr($key) . "'";
            if ($key == $active) {
                echo " selected";
            }
            echo ">" . text($desc) . "</option>";
        }
        echo "</select>";
        echo "</td>";
    } else {
        echo "  <td>";
        echo "<input type='checkbox' name='opt[" . attr($opt_line_no) . "][activity]' value='1' " . " class='optin'$checked_active />";
        echo "</td>\n";
    }
    // Tax rates, contraceptive methods and LBF names have an additional attribute.
    //
    if ($list_id == 'taxrate' || $list_id == 'contrameth' || $list_id == 'lbfnames' || $list_id == 'transactions') {
        echo "  <td>";
        echo "<input type='text' name='opt[" . attr($opt_line_no) . "][value]' value='" .
            attr($value) . "' size='8' maxlength='15' class='optin' />";
        echo "</td>\n";
    } elseif ($list_id == 'adjreason') { // Adjustment reasons use option_value as a reason category.  This is
        // needed to distinguish between adjustments that change the invoice
        // balance and those that just shift responsibility of payment or
        // are used as comments.
        echo "  <td>";
        echo "<select name='opt[" . attr($opt_line_no) . "][value]' class='optin'>";
        foreach (
            array(
                     1 => xl('Charge adjustment'),
                     2 => xl('Coinsurance'),
                     3 => xl('Deductible'),
                     4 => xl('Other pt resp'),
                     5 => xl('Comment'),
                 ) as $key => $desc
        ) {
            echo "<option value='" . attr($key) . "'";
            if ($key == $value) {
                echo " selected";
            }
            echo ">" . text($desc) . "</option>";
        }
        echo "</select>";
        echo "</td>\n";
    } elseif ($list_id == 'abook_type') { // Address book categories use option_value to flag category as a
        // person-centric vs company-centric vs indifferent.
        echo "  <td>";
        echo "<select name='opt[" . attr($opt_line_no) . "][value]' class='optin'>";
        foreach (
            array(
                     1 => xl('Unassigned'),
                     2 => xl('Person'),
                     3 => xl('Company'),
                 ) as $key => $desc
        ) {
            echo "<option value='" . attr($key) . "'";
            if ($key == $value) {
                echo " selected";
            }
            echo ">" . text($desc) . "</option>";
        }
        echo "</select>";
        echo "</td>\n";
    } elseif ($list_id == 'immunizations') { // Immunization categories use option_value to map list items to CVX codes.
        echo "  <td>";
        echo "<input type='text' size='10' name='opt[" . attr($opt_line_no) . "][value]' " .
            "value='" . attr($value) . "' onclick='sel_cvxcode(this)' " .
            "title='" . xla('Click to select or change CVX code') . "'/>";
        echo "</td>\n";
    } elseif ($list_id == 'ptlistcols') {
        echo "  <td>";
        echo generate_select_list("opt[$opt_line_no][toggle_setting_1]", 'Sort_Direction', $tog1, 'Sort Direction', null, 'option');
        echo "</td>\n";
    }

    // IPPF includes the ability to map each list item to a "master" identifier.
    // Sports teams use this for some extra info for fitness levels.
    //
    if ($GLOBALS['ippf_specific'] || $list_id == 'fitness') {
        echo "  <td>";
        echo "<input type='text' name='opt[" . attr($opt_line_no) . "][mapping]' value='" .
            attr($mapping) . "' size='12' maxlength='15' class='optin' />";
        echo "</td>\n";
    } elseif ($list_id == 'apptstat' || $list_id == 'groupstat') {
        list($apptstat_color, $apptstat_timealert) = explode("|", $notes);
        echo "  <td>";
        echo "<input type='text' class='jscolor' name='opt[" . attr($opt_line_no) . "][apptstat_color]' value='" .
            attr($apptstat_color) . "' size='6' maxlength='6' class='optin' />";
        echo "</td>\n";
        echo "  <td>";
        echo "<input type='text' name='opt[" . attr($opt_line_no) . "][apptstat_timealert]' value='" .
            attr($apptstat_timealert) . "' size='2' maxlength='2' class='optin' />";
        echo "</td>\n";
    } else {
        echo "  <td>";
        echo "<input type='text' name='opt[" . attr($opt_line_no) . "][notes]' value='" .
            attr($notes) . "' size='25' maxlength='255' class='optin' ";
        echo "/>";
        echo "</td>\n";
    }
    if ($list_id == 'apptstat' || $list_id == 'groupstat') {
        echo "  <td>";
        echo "<input type='checkbox' name='opt[" . attr($opt_line_no) . "][toggle_setting_1]' value='1' " .
            "onclick='defClicked(" . attr($opt_line_no) . ")' class='optin'$checked_tog1 />";
        echo "</td>\n";
        echo "  <td>";
        echo "<input type='checkbox' name='opt[" . attr($opt_line_no) . "][toggle_setting_2]' value='1' " .
            "onclick='defClicked(" . attr($opt_line_no) . ")' class='optin'$checked_tog2 />";
        echo "</td>\n";
    }
    echo "  <td>";
    echo "<input type='text' name='opt[" . attr($opt_line_no) . "][codes]' title='" .
        xla('Clinical Term Code(s)') . "' value='" .
        attr($codes) . "' onclick='select_clin_term_code(this)' size='25' maxlength='255' class='optin' />";
    echo "</td>\n";

    if (preg_match('/_issue_list$/', $list_id)) {
        echo "  <td>";
        echo generate_select_list("opt[$opt_line_no][subtype]", 'issue_subtypes', $subtype, 'Subtype', ' ', 'optin');
        echo "</td>\n";
    }
    if (preg_match('/Eye_QP_/', $list_id)) {
        echo "<input type='hidden' name='opt[" . attr($opt_line_no) . "][subtype]' value='" . attr($subtype) . "' />";
        echo "<input type='hidden' name='opt[" . attr($opt_line_no) . "][mapping]' value='" . attr($mapping) . "' />";
    }
    echo " </tr>\n";
}

// Write a form line as above but for the special case of the Fee Sheet.
//
function writeFSLine($category, $option, $codes)
{
    global $opt_line_no;

    ++$opt_line_no;
    $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");

    $descs = getCodeDescriptions($codes);

    echo " <tr>\n";

    echo "  <td>";
    echo "<input type='text' name='opt[" . attr($opt_line_no) . "][category]' value='" .
        attr($category) . "' size='20' maxlength='63' class='optin' />";
    echo "</td>\n";

    echo "  <td>";
    echo "<input type='text' name='opt[" . attr($opt_line_no) . "][option]' value='" .
        attr($option) . "' size='20' maxlength='63' class='optin' />";
    echo "</td>\n";

    echo "  <td align='left' class='optcell'>";
    echo "   <div id='codelist_" . attr($opt_line_no) . "'>";
    if (strlen($descs)) {
        $arrdescs = explode('~', $descs);
        $i = 0;
        foreach ($arrdescs as $desc) {
            echo "<a href='' onclick='return delete_code(" . attr($opt_line_no) . ",$i)' title='" . xla('Delete') . "'>";
            echo "[x]&nbsp;</a>" . text($desc) . "<br />";
            ++$i;
        }
    }
    echo "</div>";
    echo "<a href='' onclick='return select_code(" . attr($opt_line_no) . ")'>";
    echo "[" . xlt('Add') . "]</a>";

    echo "<input type='hidden' name='opt[" . attr($opt_line_no) . "][codes]' value='" .
        attr($codes) . "' />";
    echo "<input type='hidden' name='opt[" . attr($opt_line_no) . "][descs]' value='" .
        attr($descs) . "' />";
    echo "</td>\n";

    echo " </tr>\n";
}


/**
 * Helper functions for writeITLine() and writeCTLine().
 */
function ctGenCell($opt_line_no, $data_array, $name, $size, $maxlength, $title = '')
{
    $value = isset($data_array[$name]) ? $data_array[$name] : '';
    $s = "  <td";
    if ($title) {
        $s .= " title='" . attr($title) . "'";
    }
    $s .= ">";
    $s .= "<input type='text' name='opt[" . attr($opt_line_no) . "][" . attr($name) . "]' value='";
    $s .= attr($value);
    $s .= "' size='" . attr($size) . "' maxlength='" . attr($maxlength) . "' class='optin' />";
    $s .= "</td>\n";
    return $s;
}

function ctGenCbox($opt_line_no, $data_array, $name, $title = '')
{
    $checked = empty($data_array[$name]) ? '' : 'checked ';
    $s = "  <td";
    if ($title) {
        $s .= " title='" . attr($title) . "'";
    }
    $s .= ">";
    $s .= "<input type='checkbox' name='opt[" . attr($opt_line_no) . "][" . attr($name) . "]' value='1' ";
    $s .= "$checked/>";
    $s .= "</td>\n";
    return $s;
}

function ctSelector($opt_line_no, $data_array, $name, $option_array, $title = '')
{
    $value = isset($data_array[$name]) ? $data_array[$name] : '';
    $s = "  <td title='" . attr($title) . "'>";
    $s .= "<select name='opt[" . attr($opt_line_no) . "][" . attr($name) . "]' class='optin'>";
    foreach ($option_array as $key => $desc) {
        $s .= "<option value='" . attr($key) . "'";
        if ($key == $value) {
            $s .= " selected";
        }
        $s .= ">" . text($desc) . "</option>";
    }
    $s .= "</select>";
    $s .= "</td>\n";
    return $s;
}

// Write a form line as above but for the special case of Code Types.
//
function writeCTLine($ct_array)
{
    global $opt_line_no, $ct_external_options;

    ++$opt_line_no;
    $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");

    echo " <tr>\n";

    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_active',
        xl('Is this code type active?')
    );
    echo ctGenCell(
        $opt_line_no,
        $ct_array,
        'ct_key',
        6,
        15,
        xl('Unique human-readable identifier for this type')
    );
    echo ctGenCell(
        $opt_line_no,
        $ct_array,
        'ct_id',
        2,
        11,
        xl('Unique numeric identifier for this type')
    );
    echo ctGenCell(
        $opt_line_no,
        $ct_array,
        'ct_label',
        6,
        30,
        xl('Label for this type')
    );
    // if not english and translating lists then show the translation
    if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
        echo "  <td align='center' class='translation'>" . xlt($ct_array['ct_label']) . "</td>\n";
    }
    echo ctGenCell(
        $opt_line_no,
        $ct_array,
        'ct_seq',
        2,
        3,
        xl('Numeric display order')
    );
    echo ctGenCell(
        $opt_line_no,
        $ct_array,
        'ct_mod',
        1,
        2,
        xl('Length of modifier, 0 if none')
    );
    echo ctGenCell(
        $opt_line_no,
        $ct_array,
        'ct_just',
        4,
        15,
        xl('If billing justification is used enter the name of the diagnosis code type.')
    );
    echo ctGenCell(
        $opt_line_no,
        $ct_array,
        'ct_mask',
        6,
        9,
        xl('Specifies formatting for codes. # = digit, @ = alpha, * = any character. Empty if not used.')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_claim',
        xl('Is this code type used in claims?')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_fee',
        xl('Are fees charged for this type?')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_rel',
        xl('Does this type allow related codes?')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_nofs',
        xl('Is this type hidden in the fee sheet?')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_proc',
        xl('Is this a procedure/service type?')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_diag',
        xl('Is this a diagnosis type?')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_term',
        xl('Is this a Clinical Term code type?')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_problem',
        xl('Is this a Medical Problem code type?')
    );
    echo ctGenCBox(
        $opt_line_no,
        $ct_array,
        'ct_drug',
        xl('Is this a Medication type?')
    );
    echo ctSelector(
        $opt_line_no,
        $ct_array,
        'ct_external',
        $ct_external_options,
        xl('Is this using external sql tables? If it is, then choose the format.')
    );
    echo " </tr>\n";
}

/**
 * Special case of Issue Types
 */
function writeITLine($it_array)
{
    global $opt_line_no, $ISSUE_TYPE_CATEGORIES, $ISSUE_TYPE_STYLES;
    ++$opt_line_no;
    $bgcolor = "#" . (($opt_line_no & 1) ? "ddddff" : "ffdddd");
    echo " <tr>\n";
    echo ctSelector($opt_line_no, $it_array, 'category', $ISSUE_TYPE_CATEGORIES, xl('OpenEMR Application Category'));
    echo ctGenCBox($opt_line_no, $it_array, 'active', xl('Is this active?'));
    echo ctGenCell($opt_line_no, $it_array, 'ordering', 4, 10, xl('Order{{Sequence}}'));
    echo ctGenCell($opt_line_no, $it_array, 'type', 15, 75, xl('Issue Type'));
    echo ctGenCell($opt_line_no, $it_array, 'plural', 15, 75, xl('Plural'));
    // if not english and translating lists then show the translation
    if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
        echo "  <td align='center' class='translation'>" . xlt($it_array['plural']) . "</td>\n";
    }
    echo ctGenCell($opt_line_no, $it_array, 'singular', 15, 75, xl('Singular'));
    // if not english and translating lists then show the translation
    if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
        echo "  <td align='center' class='translation'>" . xlt($it_array['singular']) . "</td>\n";
    }
    echo ctGenCell($opt_line_no, $it_array, 'abbreviation', 5, 10, xl('Abbreviation'));
    // if not english and translating lists then show the translation
    if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
        echo "  <td align='center' class='translation'>" . xlt($it_array['abbreviation']) . "</td>\n";
    }
    echo ctSelector($opt_line_no, $it_array, 'style', $ISSUE_TYPE_STYLES, xl('Standard; Simplified: only title, start date, comments and an Active checkbox;no diagnosis, occurrence, end date, referred-by or sports fields. ; Football Injury'));
    echo ctGenCBox($opt_line_no, $it_array, 'force_show', xl('Show this category on the patient summary screen even if no issues have been entered for this category.'));

    echo "<td>";
    echo "<select name='opt[" . attr($opt_line_no) . "][aco_spec]' class='optin'>";
    echo "<option value=''></option>";
    echo AclExtended::genAcoHtmlOptions($it_array['aco_spec']);
    echo "</select>";
    echo "</td>";

    echo " </tr>\n";
}

?>
<html>

<head>
    <?php echo Header::setupHeader(['select2', 'jscolor']); ?>
    <title><?php echo xlt('List Editor'); ?></title>
    <style>
        .optin {
            background-color: transparent;
        }

        .help {
            cursor: help;
        }

        .translation {
            color: var(--success);
        }
        #theform input[type=text],
        .optin {
            color: var(--black);
        }
    </style>
    <script>
        $(function () {
            $(".select-dropdown").select2({
                theme: "bootstrap4",
                <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
            });
            if (typeof error !== 'undefined') {
                if (error) {
                    alertMsg(error);
                }
            }
        });

        // Keeping track of code picker requests.
        var current_lino = 0;
        var current_sel_name = '';
        var current_sel_clin_term = '';

        // Helper function to set the contents of a div.
        // This is for Fee Sheet administration.
        function setDivContent(id, content) {
            if (document.getElementById) {
                var x = document.getElementById(id);
                x.innerHTML = '';
                x.innerHTML = content;
            }
            else if (document.all) {
                var x = document.all[id];
                x.innerHTML = content;
            }
        }

        // Given a line number, redisplay its descriptive list of codes.
        // This is for Fee Sheet administration.
        function displayCodes(lino) {
            var f = document.forms[0];
            var s = '';
            var descs = f['opt[' + lino + '][descs]'].value;
            if (descs.length) {
                var arrdescs = descs.split('~');
                for (var i = 0; i < arrdescs.length; ++i) {
                    s += "<a href='' onclick='return delete_code(" + lino + "," + i + ")' title='<?php echo xla('Delete'); ?>'>";
                    s += "[x]&nbsp;</a>" + arrdescs[i] + "<br />";
                }
            }
            setDivContent('codelist_' + lino, s);
        }

        // Helper function to remove a Fee Sheet code.
        function dc_substring(s, i) {
            var r = '';
            var j = s.indexOf('~', i);
            if (j < 0) { // deleting last segment
                if (i > 0) r = s.substring(0, i - 1); // omits trailing ~
            }
            else { // not last segment
                r = s.substring(0, i) + s.substring(j + 1);
            }
            return r;
        }

        // Remove a generated Fee Sheet code.
        function delete_code(lino, seqno) {
            var f = document.forms[0];
            var celem = f['opt[' + lino + '][codes]'];
            var delem = f['opt[' + lino + '][descs]'];
            var ci = 0;
            var di = 0;
            for (var i = 0; i < seqno; ++i) {
                ci = celem.value.indexOf('~', ci) + 1;
                di = delem.value.indexOf('~', di) + 1;
            }
            celem.value = dc_substring(celem.value, ci);
            delem.value = dc_substring(delem.value, di);
            displayCodes(lino);
            return false;
        }

        // This invokes the find-code popup.
        // For Fee Sheet administration.
        function select_code(lino) {
            current_sel_name = '';
            current_sel_clin_term = '';
            current_lino = lino;
            dlgopen('../patient_file/encounter/find_code_dynamic.php', '_blank', 900, 600);
            return false;
        }

        // This invokes the find-code popup.
        // For CVX/immunization code administration.
        function sel_cvxcode(e) {
            current_sel_clin_term = '';
            current_sel_name = e.name;
            dlgopen('../patient_file/encounter/find_code_dynamic.php?codetype=CVX', '_blank', 900, 600);
        }

        // This invokes the find-code popup.
        // For CVX/immunization code administration.
        function select_clin_term_code(e) {
            current_sel_name = '';
            current_sel_clin_term = e.name;
            dlgopen('../patient_file/encounter/find_code_dynamic.php?codetype=' + <?php echo js_url(collect_codetypes("clinical_term", "csv")); ?>, '_blank', 900, 600);
        }

        // This is for callback by the find-code popup.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            if (current_sel_clin_term) {
                // Coming from the Clinical Terms Code(s) edit
                var e = f[current_sel_clin_term];
                var s = e.value;
                if (code) {
                    if (s.length > 0) s += ';';
                    s += codetype + ':' + code;
                }
                else {
                    s = '';
                }
                e.value = s;
            }
            else if (current_sel_name) {
                // Coming from Immunizations edit
                var e = f[current_sel_name];
                var s = e.value;
                if (code) {
                    s = code;
                }
                else {
                    s = '0';
                }
                e.value = s;
            }
            else {
                // Coming from Fee Sheet edit
                var celem = f['opt[' + current_lino + '][codes]'];
                var delem = f['opt[' + current_lino + '][descs]'];
                var i = 0;
                while ((i = codedesc.indexOf('~')) >= 0) {
                    codedesc = codedesc.substring(0, i) + ' ' + codedesc.substring(i+1);
                }
                if (code) {
                    if (celem.value) {
                        celem.value += '~';
                        delem.value += '~';
                    }
                    celem.value += codetype + '|' + code + '|' + selector;
                    if (codetype == 'PROD') {
                        delem.value += code + ':' + selector + ' ' + codedesc;
                    } else {
                        delem.value += codetype + ':' + code + ' ' + codedesc;
                    }
                } else {
                    celem.value = '';
                    delem.value = '';
                }
                displayCodes(current_lino);
            }
        }

        // This is for callback by the find-code popup.
        // Deletes the specified codetype:code from the currently selected list.
        function del_related(s) {
            var f = document.forms[0];
            if (current_sel_clin_term) {
                // Coming from the Clinical Terms Code(s) edit
                my_del_related(s, f[current_sel_clin_term], false);
            }
            else if (current_sel_name) {
                // Coming from Immunizations edit
                f[current_sel_name].value = '0';
            }
            else {
                // Coming from Fee Sheet edit
                f['opt[' + current_lino + '][codes]'].value = '';
                f['opt[' + current_lino + '][descs]'].value = '';
                displayCodes(current_lino);
            }
        }

        // This is for callback by the find-code popup.
        // Returns the array of currently selected codes with each element in codetype:code format.
        function get_related() {
            var f = document.forms[0];
            if (current_sel_clin_term) {
                return f[current_sel_clin_term].value.split(';');
            }
            return [];
        }

        // Called when a "default" checkbox is clicked.  Clears all the others.
        function defClicked(lino) {
            var f = document.forms[0];
            for (var i = 1; f['opt[' + i + '][default]']; ++i) {
                if (i != lino) f['opt[' + i + '][default]'].checked = false;
            }
        }

        // Form validation and submission.
        // This needs more validation.
        function mysubmit() {
            var f = document.forms[0];
            if (f.list_id.value == 'code_types') {
                for (var i = 1; f['opt[' + i + '][ct_key]'].value; ++i) {
                    var ikey = 'opt[' + i + ']';
                    for (var j = i + 1; f['opt[' + j + '][ct_key]'].value; ++j) {
                        var jkey = 'opt[' + j + ']';
                        if (f[ikey + '[ct_key]'].value == f[jkey + '[ct_key]'].value) {
                            alert(<?php echo xlj('Error: duplicated name on line') ?> + ' ' + j);
                            return;
                        }
                        if (parseInt(f[ikey + '[ct_id]'].value) == parseInt(f[jkey + '[ct_id]'].value)) {
                            alert(<?php echo xlj('Error: duplicated ID on line') ?> + ' ' + j);
                            return;
                        }
                    }
                }
            }
            else if (f['opt[1][id]']) {
                // Check for duplicate IDs.
                for (var i = 1; f['opt[' + i + '][id]']; ++i) {
                    var ikey = 'opt[' + i + '][id]';
                    if (f[ikey].value == '') continue;
                    for (var j = i+1; f['opt[' + j + '][id]']; ++j) {
                        var jkey = 'opt[' + j + '][id]';
                        if (f[ikey].value.toUpperCase() == f[jkey].value.toUpperCase()) {
                            alert(<?php echo xlj('Error: duplicated ID') ?> + ': ' + f[jkey].value);
                            f[jkey].scrollIntoView();
                            f[jkey].focus();
                            f[jkey].select();
                            return;
                        }
                    }
                }
            }
            f.submit();
        }

    </script>

</head>

<body class="body_top">
<form method='post' name='theform' id='theform' action='edit_list.php'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <input type="hidden" id="list_from" name="list_from" value="<?php echo attr($list_from);?>"/>
    <input type="hidden" id="list_to" name="list_to" value="<?php echo attr($list_to);?>"/>
    <nav class="navbar navbar-light bg-light navbar-expand-md fixed-top">
        <div class="container-fluid">
              <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-list" aria-controls="navbar-list" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <a class="navbar-brand" href="#"><?php echo xlt('Manage Lists'); ?></a>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="navbar-list">
                <ul class="nav navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#" data-toggle="modal" data-target="#modal-new-list"><i class="fa fa-plus"></i>&nbsp;<?php echo xlt('New List'); ?></a></li>
                    <li class="nav-item"><a class="nav-link deletelist" href="#" id="<?php echo attr($list_id); ?>"><i class="fa fa-trash"></i>&nbsp;<?php echo xlt('Delete List'); ?></a></li>
                </ul>
                <input type="hidden" name="formaction" id="formaction" />
                <div class="form-inline my-2 my-lg-0 navbar-left">
                    <select name='list_id' class="form-control select-dropdown" id="list_id">
                        <?php
                        /*
                         * Keep proper list name (otherwise list name changes according to
                         * the options shown on the screen).
                         */
                        $list_id_container = $_GET["list_id_container"] ?? null;
                        if (isset($_GET["list_id_container"]) && strlen($list_id_container) > 0) {
                            $list_id = $list_id_container;
                        }

                        // List order depends on language translation options.
                        $lang_id = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];

                        if (!$GLOBALS['translate_lists']) {
                            $res = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
                                "list_id = 'lists' ORDER BY title, seq");
                        } else {
                            // Use and sort by the translated list name.
                            $res = sqlStatement("SELECT lo.option_id, " .
                                "IF(LENGTH(ld.definition),ld.definition,lo.title) AS title " .
                                "FROM list_options AS lo " .
                                "LEFT JOIN lang_constants AS lc ON lc.constant_name = lo.title " .
                                "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
                                "ld.lang_id = ? " .
                                "WHERE lo.list_id = 'lists' AND lo.edit_options = 1 " .
                                "ORDER BY IF(LENGTH(ld.definition),ld.definition,lo.title), lo.seq", array($lang_id));
                        }

                        while ($row = sqlFetchArray($res)) {
                            // This allows the list to default to the first item on the list
                            //   when the list_id request parameter is blank.
                            if (($blank_list_id) && ($list_id == 'language')) {
                                $list_id = $row['option_id'];
                                $blank_list_id = false;
                            }

                            $key = $row['option_id'];
                            echo "<option value='" . attr($key) . "'";
                            if ($key == $list_id) {
                                echo " selected";
                            }
                            echo ">" . text($row['title']) . "</option>\n";
                        }

                        ?>
                    </select>
                </div>

                <!--Added filter-->
                <script>
                    function lister() {
                        var queryParams = getQueryStringAsObject();
                        var list_from = parseInt($("#list-from").val());
                        var list_to   = parseInt($("#list-to").val());
                        var list_id_container = $("#list_id").val();

                        if( list_from > list_to ){
                            alert(<?php echo xlj("Please enter a enter valid range"); ?>);
                            return false;
                        }
                        if( list_from >= 0 ){
                            queryParams['list_from'] = list_from;
                        }

                        if( list_to >= 0 ){
                            queryParams['list_to'] = list_to;
                        }
                        if( list_id_container.length > 0 ){
                            queryParams['list_id_container'] = list_id_container;
                        }
                        var urlParts = document.URL.split('?');
                        var newUrl = urlParts[0] + '?' + $.param(queryParams);
                        window.location.replace(newUrl);
                    }
                </script>
                <?php
                $urlFrom   = ($list_from > 0 ? $list_from : 1);
                $urlTo     = ($list_to > 0 ? $list_to : $records_per_page);
                ?>
                <div class="blck-filter float-left w-auto my-2 my-lg-0" style="display: none;">
                    <div id="input-type-from" class="float-left"><?php echo xlt("From"); ?>&nbsp;<input autocomplete="off" id="list-from" value="<?php echo attr($urlFrom);?>" style="margin-right: 10px; width: 40px;">
                        <?php echo xlt("To{{Range}}"); ?>&nbsp;<input autocomplete="off" id="list-to" value="<?php echo attr($urlTo); ?>" style=" margin-right: 10px; width: 40px;">
                    </div>
                    <div class="float-left"><input type="button" value="<?php echo xla('Show records'); ?>" onclick="lister()"></div>
                </div>
                <!--Happy end-->
                <div class="float-left ml-2 my-2 my-lg-0" id="total-record"></div>
        </div><!-- /.navbar-collapse -->
    </div>
</nav>

<table class="table table-striped table-sm" style="margin-top: 55px;">
    <thead>
    <tr>
        <?php if ($list_id == 'feesheet') : ?>
            <td class="font-weight-bold"><?php echo xlt('Group'); ?></td>
            <td class="font-weight-bold"><?php echo xlt('Option'); ?></td>
            <td class="font-weight-bold"><?php echo xlt('Generates'); ?></td>
        <?php elseif ($list_id == 'code_types') : ?>
            <th class="font-weight-bold"><?php echo xlt('Active{{Code}}'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Key'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('ID'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Label'); ?></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th class='font-weight-bold'>" . xlt('Translation') . "<span class='help' title='" . xla('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th class="font-weight-bold"><?php echo xlt('Seq'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('ModLength'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Justify'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Mask'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Claims'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Fees'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Relations'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Hide'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Procedure'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Diagnosis'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Clinical Term'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Medical Problem'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Drug'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('External'); ?></th>
        <?php elseif ($list_id == 'apptstat' || $list_id == 'groupstat') : ?>
            <th class="font-weight-bold"><?php echo xlt('ID'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Title'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Order{{Sequence}}'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Default'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Active{{Appointment}}'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Color'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Alert Time'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Check In'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
            <th class="font-weight-bold"><?php echo xlt('Check Out'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Code(s)'); ?></th>
        <?php elseif ($list_id == 'issue_types') : ?>
            <th class="font-weight-bold"><?php echo xlt('OpenEMR Application Category'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Active{{Issue}}'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Order{{Sequence}}'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Type'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Plural'); ?></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th class='font-weight-bold'>" . xlt('Translation') . "<span class='help' title='" . xla('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th class="font-weight-bold"><?php echo xlt('Singular'); ?></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th class='font-weight-bold'>" . xlt('Translation') . "<span class='help' title='" . xla('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th class="font-weight-bold"><?php echo xlt('Mini'); ?></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th class='font-weight-bold'>" . xlt('Translation') . "<span class='help' title='" . xla('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th class="font-weight-bold"><?php echo xlt('Style'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Force Show'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Access Control'); ?></th>
        <?php else : ?>
            <th title='<?php echo xla('Click to edit'); ?>' class="font-weight-bold"><?php echo xlt('ID'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Title'); ?></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th class='font-weight-bold'>" . xlt('Translation') . "<span class='help' title='" . xla('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th class="font-weight-bold"><?php echo xlt('Order{{Sequence}}'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Default'); ?></th>
            <th class="font-weight-bold"><?php echo xlt('Active'); ?></th>
            <?php if ($list_id == 'taxrate') { ?>
                <th class="font-weight-bold"><?php echo xlt('Rate'); ?></th>
            <?php } elseif ($list_id == 'contrameth') { ?>
                <th class="font-weight-bold"><?php echo xlt('Effectiveness'); ?></th>
            <?php } elseif ($list_id == 'lbfnames' || $list_id == 'transactions') { ?>
                <th title='<?php echo xla('Number of past history columns'); ?>' class="font-weight-bold"><?php echo xlt('Repeats'); ?></th>
            <?php } elseif ($list_id == 'fitness') { ?>
                <th class="font-weight-bold"><?php echo xlt('Color:Abbr'); ?></th>
            <?php } elseif ($list_id == 'adjreason' || $list_id == 'abook_type') { ?>
                <th class="font-weight-bold"><?php echo xlt('Type'); ?></th>
            <?php } elseif ($list_id == 'immunizations') { ?>
                <th class="font-weight-bold">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('CVX Code Mapping'); ?></th>
            <?php } elseif ($list_id == 'ptlistcols') { ?>
                <th class="font-weight-bold">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('Default Sort Direction'); ?></th>
            <?php }
            if ($GLOBALS['ippf_specific']) { ?>
    <th class="font-weight-bold"><?php echo xlt('Global ID'); ?></th>
            <?php } ?>
            <th class="font-weight-bold"><?php
            if ($list_id == 'language') {
                echo xlt('ISO 639-2 Code');
            } elseif ($list_id == 'personal_relationship' || $list_id == 'religious_affiliation' || $list_id == 'ethnicity' || $list_id == 'race' || $list_id == 'drug_route') {
                echo xlt('HL7-V3 Concept Code');
            } elseif ($list_id == 'Immunization_Completion_Status') {
                echo xlt('Treatment Completion Status');
            } elseif ($list_id == 'race') {
                echo xlt('CDC Code');
            } elseif ($list_id == 'Immunization_Manufacturer') {
                echo xlt('MVX Code');
            } elseif ($list_id == 'marital') {
                echo xlt('Marital Status');
            } elseif ($list_id == 'county') {
                echo xlt('INCITS Code'); //International Committee for Information Technology Standards
            } elseif ($list_id == 'immunization_registry_status' || $list_id == 'imm_vac_eligibility_results') {
                echo xlt('IIS Code');
            } elseif ($list_id == 'publicity_code') {
                echo xlt('CDC Code');
            } elseif ($list_id == 'immunization_refusal_reason' || $list_id == 'immunization_informationsource') {
                echo xlt('CDC-NIP Code');
            } elseif ($list_id == 'next_of_kin_relationship' || $list_id == 'immunization_administered_site') {
                echo xlt('HL7 Code');
            } elseif ($list_id == 'immunization_observation') {
                echo xlt('LOINC Code');
            } elseif ($list_id == 'page_validation') {
                echo xlt('Page Validation');
            } elseif ($list_id == 'lbfnames') {
                echo xlt('Attributes');
            } else {
                echo xlt('Notes');
            } ?></th>

            <th class="font-weight-bold"><?php echo xlt('Code(s)'); ?></th>
            <?php
            if (preg_match('/_issue_list$/', $list_id)) { ?>
                <th class="font-weight-bold"><?php echo xlt('Subtype'); ?></th>
                <?php
            }
        endif; // end not fee sheet ?>
    </tr>
    </thead>
    <tbody>
    <?php
    // Get the selected list's elements.
    if ($list_id) {
        $sql_limits = 'ASC LIMIT 0, ' . escape_limit($records_per_page);
        $total_rows = 0;
        if ($list_from > 0) {
            $list_from--;
        }
        if ($list_to > 0) {
            $sql_limits = " ASC LIMIT " . escape_limit($list_from) . (intval($list_to) > 0 ? ", " . escape_limit($list_to - $list_from) : "");
        }

        if ($list_id == 'feesheet') {
            $res = sqlStatement("SELECT count(*) as total_rows FROM fee_sheet_options ORDER BY fs_category, fs_option");
            $total_rows = sqlFetchArray($res)["total_rows"];

            $res = sqlStatement("SELECT * FROM fee_sheet_options " .
                "ORDER BY fs_category, fs_option " . $sql_limits);
            while ($row = sqlFetchArray($res)) {
                writeFSLine($row['fs_category'], $row['fs_option'], $row['fs_codes']);
            }
            for ($i = 0; $i < 3; ++$i) {
                writeFSLine('', '', '');
            }
        } elseif ($list_id == 'code_types') {
            $res = sqlStatement("SELECT count(*) as total_rows FROM code_types ORDER BY ct_seq, ct_key");
            $total_rows = sqlFetchArray($res)["total_rows"];

            $res = sqlStatement("SELECT * FROM code_types " .
                "ORDER BY ct_seq, ct_key " . $sql_limits);
            while ($row = sqlFetchArray($res)) {
                writeCTLine($row);
            }
            for ($i = 0; $i < 3; ++$i) {
                writeCTLine(array());
            }
        } elseif ($list_id == 'issue_types') {
            $res = sqlStatement("SELECT count(*) as total_rows FROM issue_types ORDER BY category, ordering");
            $total_rows = sqlFetchArray($res)["total_rows"];

            $res = sqlStatement("SELECT * FROM issue_types " .
                "ORDER BY category, ordering " . $sql_limits);
            while ($row = sqlFetchArray($res)) {
                writeITLine($row);
            }
            for ($i = 0; $i < 3; ++$i) {
                writeITLine(array());
            }
        } else {
            $res = sqlStatement("SELECT count(*) as total_rows
                         FROM list_options AS lo
                         RIGHT JOIN list_options as lo2 on lo2.option_id = lo.list_id AND lo2.list_id = 'lists' AND lo2.edit_options = 1
                         WHERE lo.list_id = ? AND lo.edit_options = 1", array($list_id));
            $total_rows = sqlFetchArray($res)["total_rows"];


            $res = sqlStatement("SELECT lo.*
                         FROM list_options AS lo
                         RIGHT JOIN list_options as lo2 on lo2.option_id = lo.list_id AND lo2.list_id = 'lists' AND lo2.edit_options = 1
                         WHERE lo.list_id = ? AND lo.edit_options = 1
                         ORDER BY seq,title " . $sql_limits, array($list_id));

            while ($row = sqlFetchArray($res)) {
                writeOptionLine(
                    $row['option_id'],
                    $row['title'],
                    $row['seq'],
                    $row['is_default'],
                    $row['option_value'],
                    $row['mapping'],
                    $row['notes'],
                    $row['codes'],
                    $row['toggle_setting_1'],
                    $row['toggle_setting_2'],
                    $row['activity'],
                    $row['subtype']
                );
            }
            for ($i = 0; $i < 3; ++$i) {
                writeOptionLine('', '', '', '', 0);
            }
        }
    }
    ?>
    </tbody>
</table>

<?php if ($list_id == 'immunizations') { ?>
    <p> <?php echo xlt('Is it ok to map these CVX codes to already existent immunizations?') ?>
        <input type='checkbox' name='ok_map_cvx_codes' id='ok_map_cvx_codes' value='1'/>
    </p>
<?php } // end if($list_id == 'immunizations') ?>

<p>
    <button type="submit" name='form_save' id='form_save' class="btn btn-secondary btn-save"><?php echo xlt('Save'); ?></button>
</p>

</form>

<div class="modal fade" id="modal-new-list" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="edit_list.php" method="post" class="form">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo xlt('New List'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo xla('Close'); ?>"><i class="fa fa-times" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body">
                    <label for="newlistname" class="control-label"><?php echo xlt('List Name'); ?></label>
                    <input type="text" size="20" class="form-control" maxlength="100" name="newlistname" id="newlistname" />
                    <input type="hidden" name="formaction" value="addlist">

                </div>
                <div class="modal-footer text-right">
                    <button type="submit" class="btn btn-secondary btn-save"><?php echo xlt('Save'); ?></button>
                    <button type="button" class="btn btn-link btn-cancel" data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal #modal-new-list -->

<script>
    // jQuery stuff to make the page a little easier to use

    $(function () {
        $("#form_save").click(function (e) {
            e.preventDefault();
            SaveChanges();
        });
        $("#list_id").change(function () {
            $("#list_from").val(1);
            $("#list_to").val('');

            $('#theform').submit();
        });

        $(".newlist").click(function () {
            NewList(this);
        });
        $(".savenewlist").click(function () {
            SaveNewList(this);
        });
        $(".deletelist").click(function () {
            DeleteList(this);
        });

        var totalRecords = '<?php echo attr($res->_numOfRows);?>';
        var totalRecordDiv = $('#total-record');
        if( totalRecordDiv ){
            totalRecordDiv.text("<?php echo xlt("Showing items"); ?>: <?php echo ( $list_to > 0 ? attr($list_from + 1) . " - " . attr($list_to) : attr($res->_numOfRows) );?> of <?php echo attr($total_rows);?>");
        }

        var queryParams = getQueryStringAsObject();
        var listIdCont = null;
        if (typeof queryParams['list_id_container'] !== 'undefined') {
            listIdCont = queryParams['list_id_container'];
        }


        if( totalRecords >= <?php echo attr($records_per_page);?> || listIdCont != null || $("#list_to").val() > 0) {
            $(".blck-filter").show();
        }

        //prevent Enter button press on filter
        $('.blck-filter').on('keyup keypress', function(e)
        {
            var keyCode = e.keyCode || e.which;
            if(keyCode == 13)
            {
                return false;
            }
        });

        var SaveChanges = function () {
            $("#formaction").val("save");
            // $('#theform').submit();
            mysubmit();
        };

        // show the DIV to create a new list
        var NewList = function (btnObj) {
            // show the field details DIV
            $('#newlistdetail').css('visibility', 'visible');
            $('#newlistdetail').css('display', 'block');
            $(btnObj).parent().append($("#newlistdetail"));
            $('#newlistdetail > #newlistname').focus();
        };
        // save the new list
        var SaveNewList = function () {
            // the list name can only have letters, numbers, spaces and underscores
            // AND it cannot start with a number
            if ($("#newlistname").val().match(/^\d+/)) {
                alert(<?php echo xlj('List names cannot start with numbers.'); ?>);
                return false;
            }
            var validname = $("#newlistname").val().replace(/[^A-za-z0-9 -]/g, "_"); // match any non-word characters and replace them
            if (validname != $("#newlistname").val()) {
                if (!confirm(<?php echo xlj('Your list name has been changed to meet naming requirements.'); ?> + '\n' + <?php echo xlj('Please compare the new name'); ?> + ', \'' + validname + '\' ' + <?php echo xlj('with the old name'); ?> + ', \'' + $("#newlistname").val() + '\'.\n' + <?php echo xlj('Do you wish to continue with the new name?'); ?>)) {
                    return false;
                }
            }
            $("#newlistname").val(validname);

            // submit the form to add a new field to a specific group
            $("#formaction").val("addlist");
            $("#theform").submit();
        };
        // actually delete an entire list from the database
        var DeleteList = function (btnObj) {
            var listid = $(btnObj).attr("id");
            if (confirm(<?php echo xlj('WARNING'); ?> + ' - ' + <?php echo xlj('This action cannot be undone.'); ?> + '\n' + <?php echo xlj('Are you sure you wish to delete the entire list'); ?> + '(' + listid + ")?")) {
                // submit the form to add a new field to a specific group
                $("#formaction").val("deletelist");
                $("#deletelistname").val(listid);
                $("#theform").submit();
            }
        };
    });

    function getQueryStringAsObject() {
        var paramsString = document.URL.split('?');
        var paramsFull = (paramsString.length > 1) ? paramsString[1].split('&') : [];
        var listIdCont = null;
        var queryParameter;
        var resObject = {};
        for (var i = 0; i < paramsFull.length; i++ ) {
            queryParameter = paramsFull[i].split('=');
            resObject[queryParameter[0]] = queryParameter[1];
        }
        return resObject;
    }

</script>
</body>
</html>
