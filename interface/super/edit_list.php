<?php
/**
 * Administration Lists Module.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Teny <teny@zhservices.com>
 * @copyright Copyright (c) 2007-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$phpgacl_location/gacl_api.class.php");
require_once("$srcdir/lists.inc");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

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
$thisauth = acl_check('admin', 'super');
if (!$thisauth) {
    die(xl('Not authorized'));
}

// If we are saving, then save.
//
if ($_POST['formaction'] == 'save' && $list_id) {
    $opt = $_POST['opt'];
    if ($list_id == 'feesheet') {
        // special case for the feesheet list
        sqlStatement("DELETE FROM fee_sheet_options");
        for ($lino = 1; isset($opt["$lino"]['category']); ++$lino) {
            $iter = $opt["$lino"];
            $category = formTrim($iter['category']);
            $option = formTrim($iter['option']);
            $codes = formTrim($iter['codes']);
            if (strlen($category) > 0 && strlen($option) > 0) {
                sqlInsert("INSERT INTO fee_sheet_options ( " .
                    "fs_category, fs_option, fs_codes " .
                    ") VALUES ( " .
                    "'$category', " .
                    "'$option', " .
                    "'$codes' " .
                    ")");
            }
        }
    } elseif ($list_id == 'code_types') {
        // special case for code types
        sqlStatement("DELETE FROM code_types");
        for ($lino = 1; isset($opt["$lino"]['ct_key']); ++$lino) {
            $iter = $opt["$lino"];
            $ct_key = formTrim($iter['ct_key']);
            $ct_id = formTrim($iter['ct_id']) + 0;
            $ct_seq = formTrim($iter['ct_seq']) + 0;
            $ct_mod = formTrim($iter['ct_mod']) + 0;
            $ct_just = formTrim($iter['ct_just']);
            $ct_mask = formTrim($iter['ct_mask']);
            $ct_fee = empty($iter['ct_fee']) ? 0 : 1;
            $ct_rel = empty($iter['ct_rel']) ? 0 : 1;
            $ct_nofs = empty($iter['ct_nofs']) ? 0 : 1;
            $ct_diag = empty($iter['ct_diag']) ? 0 : 1;
            $ct_active = empty($iter['ct_active']) ? 0 : 1;
            $ct_label = formTrim($iter['ct_label']);
            $ct_external = formTrim($iter['ct_external']) + 0;
            $ct_claim = empty($iter['ct_claim']) ? 0 : 1;
            $ct_proc = empty($iter['ct_proc']) ? 0 : 1;
            $ct_term = empty($iter['ct_term']) ? 0 : 1;
            $ct_problem = empty($iter['ct_problem']) ? 0 : 1;
            $ct_drug = empty($iter['ct_drug']) ? 0 : 1;
            if (strlen($ct_key) > 0 && $ct_id > 0) {
                sqlInsert("INSERT INTO code_types ( " .
                    "ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_mask, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external, ct_claim, ct_proc, ct_term, ct_problem, ct_drug " .
                    ") VALUES ( " .
                    "'$ct_key' , " .
                    "'$ct_id'  , " .
                    "'$ct_seq' , " .
                    "'$ct_mod' , " .
                    "'$ct_just', " .
                    "'$ct_mask', " .
                    "'$ct_fee' , " .
                    "'$ct_rel' , " .
                    "'$ct_nofs', " .
                    "'$ct_diag', " .
                    "'$ct_active', " .
                    "'$ct_label', " .
                    "'$ct_external', " .
                    "'$ct_claim', " .
                    "'$ct_proc', " .
                    "'$ct_term', " .
                    "'$ct_problem', " .
                    "'$ct_drug' " .
                    ")");
            }
        }
    } elseif ($list_id == 'issue_types') {
        // special case for issue_types
        sqlStatement("DELETE FROM issue_types");
        for ($lino = 1; isset($opt["$lino"]['category']); ++$lino) {
            $iter = $opt["$lino"];
            $it_category = formTrim($iter['category']);
            $it_type = formTrim($iter['type']);
            if ((strlen($it_category) > 0) && (strlen($it_type) > 0)) {
                sqlInsert("INSERT INTO issue_types (" .
                    "`active`,`category`,`ordering`, `type`, `plural`, `singular`, `abbreviation`, `style`, " .
                    "`force_show`, `aco_spec`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
                    formTrim($iter['active']),
                    $it_category,
                    formTrim($iter['ordering']),
                    $it_type,
                    formTrim($iter['plural']),
                    formTrim($iter['singular']),
                    formTrim($iter['abbreviation']),
                    formTrim($iter['style']),
                    formTrim($iter['force_show']),
                    formTrim($iter['aco_spec']),
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
        // erase lists options and recreate them from the submitted form data
        sqlStatement("DELETE FROM list_options WHERE list_id = '$list_id'");
        for ($lino = 1; isset($opt["$lino"]['id']); ++$lino) {
            $iter = $opt["$lino"];
            $value = empty($iter['value']) ? 0 : (formTrim($iter['value']) + 0);
            $id = formTrim($iter['id']);
            if (strlen($id) > 0) {
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
                if ($list_id == 'immunizations' &&
                    is_int($value) &&
                    $value > 0 &&
                    isset($id) &&
                    !empty($id) &&
                    $id != 0 &&
                    $ok_map_cvx_codes == 1
                ) {
                    sqlStatement("UPDATE `immunizations` " .
                        "SET `cvx_code`='" . $value . "' " .
                        "WHERE `immunization_id`='" . $id . "'");
                }

                // Force List Based Form names to start with LBF.
                if ($list_id == 'lbfnames' && substr($id, 0, 3) != 'LBF') {
                    $id = "LBF$id";
                }

                // Force Transaction Form names to start with LBT.
                if ($list_id == 'transactions' && substr($id, 0, 3) != 'LBT') {
                    $id = "LBT$id";
                }

                if ($list_id == 'apptstat' || $list_id == 'groupstat') {
                    $notes = formTrim($iter['apptstat_color']) . '|' . formTrim($iter['apptstat_timealert']);
                } else {
                    $notes = formTrim($iter['notes']);
                }
                // Insert the list item
                sqlInsert("INSERT INTO list_options ( " .
                    "list_id, option_id, title, seq, is_default, option_value, mapping, notes, codes, toggle_setting_1, toggle_setting_2, activity, subtype " .
                    ") VALUES ( " .
                    "'$list_id', " .
                    "'" . $id . "', " .
                    "'" . formTrim($iter['title']) . "', " .
                    "'" . formTrim($iter['seq']) . "', " .
                    "'" . formTrim($iter['default']) . "', " .
                    "'" . $value . "', " .
                    "'" . formTrim($iter['mapping']) . "', " .
                    "'" . $notes . "', " .
                    "'" . formTrim($iter['codes']) . "', " .
                    "'" . formTrim($iter['toggle_setting_1']) . "', " .
                    "'" . formTrim($iter['toggle_setting_2']) . "', " .
                    "'" . formTrim($iter['activity']) . "', " .
                    "'" . formTrim($iter['subtype']) . "'  " .
                    ")");
            }
        }
    }
} elseif ($_POST['formaction'] == 'addlist') {
    // make a new list ID from the new list name
    $newlistID = $_POST['newlistname'];
    $newlistID = preg_replace("/\W/", "_", $newlistID);

    // determine the position of this new list
    $row = sqlQuery("SELECT max(seq) AS maxseq FROM list_options WHERE list_id= 'lists'");

    // add the new list to the list-of-lists
    sqlInsert("INSERT INTO list_options ( " .
        "list_id, option_id, title, seq, is_default, option_value " .
        ") VALUES ( 'lists', ?, ?, ?, '1', '0')", array($newlistID, $_POST['newlistname'], ($row['maxseq'] + 1)));
    $list_id = $newlistID;
} elseif ($_POST['formaction'] == 'deletelist') {
    // delete the lists options
    sqlStatement("DELETE FROM list_options WHERE list_id = '" . $_POST['list_id'] . "'");
    // delete the list from the master list-of-lists
    sqlStatement("DELETE FROM list_options WHERE list_id = 'lists' AND option_id='" . $_POST['list_id'] . "'");
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
            $row = sqlQuery("SELECT name FROM drugs WHERE drug_id = '$code' ");
            $desc = "$code:$selector " . $row['name'];
        } else {
            $row = sqlQuery("SELECT code_text FROM codes WHERE " .
                "code_type = '" . $code_types[$code_type]['id'] . "' AND " .
                "code = '$code' ORDER BY modifier LIMIT 1");
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
    echo "<input type='text' name='opt[$opt_line_no][id]' value='" .
        htmlspecialchars($option_id, ENT_QUOTES) . "' size='12' maxlength='63' class='optin' />";
    echo "</td>\n";
    echo "  <td>";
    echo "<input type='text' name='opt[$opt_line_no][title]' value='" .
        htmlspecialchars($title, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
    echo "</td>\n";

    // if not english and translating lists then show the translation
    if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
        echo "  <td align='center' class='translation'>" . (htmlspecialchars(xl($title), ENT_QUOTES)) . "</td>\n";
    }
    echo "  <td>";
    echo "<input type='text' name='opt[$opt_line_no][seq]' value='" .
        htmlspecialchars($seq, ENT_QUOTES) . "' size='4' maxlength='10' class='optin' />";
    echo "</td>\n";

    echo "  <td>";
    echo "<input type='checkbox' name='opt[$opt_line_no][default]' value='1' " .
        "onclick='defClicked($opt_line_no)' class='optin'$checked />";
    echo "</td>\n";

    if (preg_match('/Eye_QP_/', $list_id)) {
        echo "  <td>";
        echo "<select name='opt[$opt_line_no][activity]' class='optin'>";
        foreach (array(
                     1 => xl('Replace'),
                     2 => xl('Append')
                 ) as $key => $desc) {
            echo "<option value='$key'";
            if ($key == $active) {
                echo " selected";
            }
            echo ">" . htmlspecialchars($desc) . "</option>";
        }
        echo "</select>";
        echo "</td>";
    } else {
        echo "  <td>";
        echo "<input type='checkbox' name='opt[$opt_line_no][activity]' value='1' " . " class='optin'$checked_active />";
        echo "</td>\n";
    }
    // Tax rates, contraceptive methods and LBF names have an additional attribute.
    //
    if ($list_id == 'taxrate' || $list_id == 'contrameth' || $list_id == 'lbfnames' || $list_id == 'transactions') {
        echo "  <td>";
        echo "<input type='text' name='opt[$opt_line_no][value]' value='" .
            htmlspecialchars($value, ENT_QUOTES) . "' size='8' maxlength='15' class='optin' />";
        echo "</td>\n";
    } // Adjustment reasons use option_value as a reason category.  This is
    // needed to distinguish between adjustments that change the invoice
    // balance and those that just shift responsibility of payment or
    // are used as comments.
    //
    elseif ($list_id == 'adjreason') {
        echo "  <td>";
        echo "<select name='opt[$opt_line_no][value]' class='optin'>";
        foreach (array(
                     1 => xl('Charge adjustment'),
                     2 => xl('Coinsurance'),
                     3 => xl('Deductible'),
                     4 => xl('Other pt resp'),
                     5 => xl('Comment'),
                 ) as $key => $desc) {
            echo "<option value='$key'";
            if ($key == $value) {
                echo " selected";
            }
            echo ">" . htmlspecialchars($desc) . "</option>";
        }
        echo "</select>";
        echo "</td>\n";
    } // Address book categories use option_value to flag category as a
    // person-centric vs company-centric vs indifferent.
    //
    elseif ($list_id == 'abook_type') {
        echo "  <td>";
        echo "<select name='opt[$opt_line_no][value]' class='optin'>";
        foreach (array(
                     1 => xl('Unassigned'),
                     2 => xl('Person'),
                     3 => xl('Company'),
                 ) as $key => $desc) {
            echo "<option value='$key'";
            if ($key == $value) {
                echo " selected";
            }
            echo ">" . htmlspecialchars($desc) . "</option>";
        }
        echo "</select>";
        echo "</td>\n";
    } // Immunization categories use option_value to map list items
    // to CVX codes.
    //
    elseif ($list_id == 'immunizations') {
        echo "  <td>";
        echo "<input type='text' size='10' name='opt[$opt_line_no][value]' " .
            "value='" . htmlspecialchars($value, ENT_QUOTES) . "' onclick='sel_cvxcode(this)' " .
            "title='" . htmlspecialchars(xl('Click to select or change CVX code'), ENT_QUOTES) . "'/>";
        echo "</td>\n";
    }

    // IPPF includes the ability to map each list item to a "master" identifier.
    // Sports teams use this for some extra info for fitness levels.
    //
    if ($GLOBALS['ippf_specific'] || $list_id == 'fitness') {
        echo "  <td>";
        echo "<input type='text' name='opt[$opt_line_no][mapping]' value='" .
            htmlspecialchars($mapping, ENT_QUOTES) . "' size='12' maxlength='15' class='optin' />";
        echo "</td>\n";
    } elseif ($list_id == 'apptstat' || $list_id == 'groupstat') {
        list($apptstat_color, $apptstat_timealert) = explode("|", $notes);
        echo "  <td>";
        echo "<input type='text' class='jscolor' name='opt[$opt_line_no][apptstat_color]' value='" .
            htmlspecialchars($apptstat_color, ENT_QUOTES) . "' size='6' maxlength='6' class='optin' />";
        echo "</td>\n";
        echo "  <td>";
        echo "<input type='text' name='opt[$opt_line_no][apptstat_timealert]' value='" .
            htmlspecialchars($apptstat_timealert, ENT_QUOTES) . "' size='2' maxlength='2' class='optin' />";
        echo "</td>\n";
    } else {
        echo "  <td>";
        echo "<input type='text' name='opt[$opt_line_no][notes]' value='" .
            attr($notes) . "' size='25' maxlength='255' class='optin' ";
        echo "/>";
        echo "</td>\n";
    }
    if ($list_id == 'apptstat' || $list_id == 'groupstat') {
        echo "  <td>";
        echo "<input type='checkbox' name='opt[$opt_line_no][toggle_setting_1]' value='1' " .
            "onclick='defClicked($opt_line_no)' class='optin'$checked_tog1 />";
        echo "</td>\n";
        echo "  <td>";
        echo "<input type='checkbox' name='opt[$opt_line_no][toggle_setting_2]' value='1' " .
            "onclick='defClicked($opt_line_no)' class='optin'$checked_tog2 />";
        echo "</td>\n";
    }
    echo "  <td>";
    echo "<input type='text' name='opt[$opt_line_no][codes]' title='" .
        xla('Clinical Term Code(s)') . "' value='" .
        htmlspecialchars($codes, ENT_QUOTES) . "' onclick='select_clin_term_code(this)' size='25' maxlength='255' class='optin' />";
    echo "</td>\n";

    if (preg_match('/_issue_list$/', $list_id)) {
        echo "  <td>";
        echo generate_select_list("opt[$opt_line_no][subtype]", 'issue_subtypes', $subtype, 'Subtype', ' ', 'optin');
        echo "</td>\n";
    }
    if (preg_match('/Eye_QP_/', $list_id)) {
        echo "<input type='hidden' name='opt[$opt_line_no][subtype]' value='" . htmlspecialchars($subtype, ENT_QUOTES) . "' />";
        echo "<input type='hidden' name='opt[$opt_line_no][mapping]' value='" . htmlspecialchars($mapping, ENT_QUOTES) . "' />";
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
    echo "<input type='text' name='opt[$opt_line_no][category]' value='" .
        htmlspecialchars($category, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
    echo "</td>\n";

    echo "  <td>";
    echo "<input type='text' name='opt[$opt_line_no][option]' value='" .
        htmlspecialchars($option, ENT_QUOTES) . "' size='20' maxlength='63' class='optin' />";
    echo "</td>\n";

    echo "  <td align='left' class='optcell'>";
    echo "   <div id='codelist_$opt_line_no'>";
    if (strlen($descs)) {
        $arrdescs = explode('~', $descs);
        $i = 0;
        foreach ($arrdescs as $desc) {
            echo "<a href='' onclick='return delete_code($opt_line_no,$i)' title='" . xl('Delete') . "'>";
            echo "[x]&nbsp;</a>$desc<br />";
            ++$i;
        }
    }
    echo "</div>";
    echo "<a href='' onclick='return select_code($opt_line_no)'>";
    echo "[" . xl('Add') . "]</a>";

    echo "<input type='hidden' name='opt[$opt_line_no][codes]' value='" .
        htmlspecialchars($codes, ENT_QUOTES) . "' />";
    echo "<input type='hidden' name='opt[$opt_line_no][descs]' value='" .
        htmlspecialchars($descs, ENT_QUOTES) . "' />";
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
    $s .= "<input type='text' name='opt[$opt_line_no][$name]' value='";
    $s .= attr($value);
    $s .= "' size='$size' maxlength='$maxlength' class='optin' />";
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
    $s .= "<input type='checkbox' name='opt[$opt_line_no][$name]' value='1' ";
    $s .= "$checked/>";
    $s .= "</td>\n";
    return $s;
}

function ctSelector($opt_line_no, $data_array, $name, $option_array, $title = '')
{
    $value = isset($data_array[$name]) ? $data_array[$name] : '';
    $s = "  <td title='" . attr($title) . "'>";
    $s .= "<select name='opt[$opt_line_no][$name]' class='optin'>";
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
    global $opt_line_no, $cd_external_options;

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
        $cd_external_options,
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
    echo ctGenCell($opt_line_no, $it_array, 'ordering', 4, 10, xl('Order'));
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
    echo "<select name='opt[$opt_line_no][aco_spec]' class='optin'>";
    echo "<option value=''></option>";
    echo gen_aco_html_options($it_array['aco_spec']);
    echo "</select>";
    echo "</td>";

    echo " </tr>\n";
}

?>
<html>

<head>
    <?php echo Header::setupHeader(['select2', 'jscolor']); ?>
    <title><?php xl('List Editor', 'e'); ?></title>
    <style>
        .optcell {
        }

        .optin {
            background-color: transparent;
        }

        .help {
            cursor: help;
        }

        .translation {
            color: green;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".select-dropdown").select2({
                theme: "bootstrap"
            });
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
                    s += "<a href='' onclick='return delete_code(" + lino + "," + i + ")' title='<?php xl('Delete', 'e'); ?>'>";
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
            dlgopen('../patient_file/encounter/find_code_dynamic.php?codetype=<?php echo attr(collect_codetypes("clinical_term", "csv")); ?>', '_blank', 900, 600);
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
            return new Array();
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
                            alert('<?php echo xl('Error: duplicated name on line') ?>' + ' ' + j);
                            return;
                        }
                        if (parseInt(f[ikey + '[ct_id]'].value) == parseInt(f[jkey + '[ct_id]'].value)) {
                            alert('<?php echo xl('Error: duplicated ID on line') ?>' + ' ' + j);
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
                            alert('<?php echo xls('Error: duplicated ID') ?>' + ': ' + f[jkey].value);
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
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed"
                    data-toggle="collapse" data-target="#navbar-list"
                    aria-expanded="false">
                <span class="sr-only"><?php xl('Toggle navigation', 'e'); ?></span>
                <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand"
               href="#"><?php xl('Manage Lists', 'e'); ?></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="navbar-list">
            <ul class="nav navbar-nav">
                <li><a href="#" data-toggle="modal"
                       data-target="#modal-new-list"><i class="fa fa-plus"></i>&nbsp;<?php xl('New List', 'e'); ?>
                    </a>
                </li>
                <li>
                    <a href="#" class="deletelist" id="<?php echo $list_id; ?>">
                        <i class="fa fa-trash"></i>&nbsp;<?php xl('Delete List', 'e'); ?>
                    </a>
                </li>
            </ul>
                <input type="hidden" name="formaction" id="formaction">
                <div class="form-group navbar-left">
                    <select name='list_id' class="form-control select-dropdown"
                            id="list_id">
                        <?php

                        // List order depends on language translation options.
                        $lang_id = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];

                        if (($lang_id == '1' && !empty($GLOBALS['skip_english_translation'])) ||
                            !$GLOBALS['translate_lists']
                        ) {
                            $res = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
                                "list_id = 'lists' ORDER BY title, seq");
                        } else {
                            // Use and sort by the translated list name.
                            $res = sqlStatement("SELECT lo.option_id, " .
                                "IF(LENGTH(ld.definition),ld.definition,lo.title) AS title " .
                                "FROM list_options AS lo " .
                                "LEFT JOIN lang_constants AS lc ON lc.constant_name = lo.title " .
                                "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
                                "ld.lang_id = '$lang_id' " .
                                "WHERE lo.list_id = 'lists' AND lo.edit_options = 1 " .
                                "ORDER BY IF(LENGTH(ld.definition),ld.definition,lo.title), lo.seq");
                        }

                        while ($row = sqlFetchArray($res)) {
                            // This allows the list to default to the first item on the list
                            //   when the list_id request parameter is blank.
                            if (($blank_list_id) && ($list_id == 'language')) {
                                $list_id = $row['option_id'];
                                $blank_list_id = false;
                            }

                            $key = $row['option_id'];
                            echo "<option value='$key'";
                            if ($key == $list_id) {
                                echo " selected";
                            }
                            echo ">" . $row['title'] . "</option>\n";
                        }

                        ?>
                    </select>
                </div>

        </div><!-- /.navbar-collapse -->
    </div>
</nav>

<table class="table table-striped table-condensed" style="margin-top:55px;">
    <thead>
    <tr>
        <?php if ($list_id == 'feesheet') : ?>
            <td><b><?php xl('Group', 'e'); ?></b></td>
            <td><b><?php xl('Option', 'e'); ?></b></td>
            <td><b><?php xl('Generates', 'e'); ?></b></td>
        <?php elseif ($list_id == 'code_types') : ?>
            <th><b><?php xl('Active', 'e'); ?></b></th>
            <th><b><?php xl('Key', 'e'); ?></b></th>
            <th><b><?php xl('ID', 'e'); ?></b></th>
            <th><b><?php xl('Label', 'e'); ?></b></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th><b>" . xl('Translation') . "</b><span class='help' title='" . xl('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th><b><?php xl('Seq', 'e'); ?></b></th>
            <th><b><?php xl('ModLength', 'e'); ?></b></th>
            <th><b><?php xl('Justify', 'e'); ?></b></th>
            <th><b><?php xl('Mask', 'e'); ?></b></th>
            <th><b><?php xl('Claims', 'e'); ?></b></th>
            <th><b><?php xl('Fees', 'e'); ?></b></th>
            <th><b><?php xl('Relations', 'e'); ?></b></th>
            <th><b><?php xl('Hide', 'e'); ?></b></th>
            <th><b><?php xl('Procedure', 'e'); ?></b></th>
            <th><b><?php xl('Diagnosis', 'e'); ?></b></th>
            <th><b><?php xl('Clinical Term', 'e'); ?></b></th>
            <th><b><?php xl('Medical Problem', 'e'); ?></b></th>
            <th><b><?php xl('Drug', 'e'); ?></b></th>
            <th><b><?php xl('External', 'e'); ?></b></th>
        <?php elseif ($list_id == 'apptstat' || $list_id == 'groupstat') : ?>
            <th><b><?php xl('ID', 'e'); ?></b></th>
            <th><b><?php xl('Title', 'e'); ?></b></th>
            <th><b><?php xl('Order', 'e'); ?></b></th>
            <th><b><?php xl('Default', 'e'); ?></b></th>
            <th><b><?php xl('Active', 'e'); ?></b></th>
            <th><b><?php xl('Color', 'e'); ?></b></th>
            <th><b><?php xl('Alert Time', 'e'); ?></b></th>
            <th><b><?php xl('Check In', 'e'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</b>
            </th>
            <th><b><?php xl('Check Out', 'e'); ?></b></th>
            <th><b><?php xl('Code(s)', 'e'); ?></b></th>
        <?php elseif ($list_id == 'issue_types') : ?>
            <th><b><?php echo xlt('OpenEMR Application Category'); ?></b></th>
            <th><b><?php echo xlt('Active'); ?></b></th>
            <th><b><?php echo xlt('Order'); ?></b></th>
            <th><b><?php echo xlt('Type'); ?></b></th>
            <th><b><?php echo xlt('Plural'); ?></b></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th><b>" . xl('Translation') . "</b><span class='help' title='" . xl('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th><b><?php echo xlt('Singular'); ?></b></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th><b>" . xl('Translation') . "</b><span class='help' title='" . xl('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th><b><?php echo xlt('Mini'); ?></b></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th><b>" . xl('Translation') . "</b><span class='help' title='" . xl('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th><b><?php echo xlt('Style'); ?></b></th>
            <th><b><?php echo xlt('Force Show'); ?></b></th>
            <th><b><?php echo xlt('Access Control'); ?></b></th>
        <?php else : ?>
            <th title=<?php xl('Click to edit', 'e', '\'', '\''); ?>>
                <b><?php xl('ID', 'e'); ?></b></th>
            <th><b><?php xl('Title', 'e'); ?></b></th>
            <?php //show translation column if not english and the translation lists flag is set
            if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
                echo "<th><b>" . xl('Translation') . "</b><span class='help' title='" . xl('The translated Title that will appear in current language') . "'> (?)</span></th>";
            } ?>
            <th><b><?php xl('Order', 'e'); ?></b></th>
            <th><b><?php xl('Default', 'e'); ?></b></th>
            <th><b><?php xl('Active', 'e'); ?></b></th>
            <?php if ($list_id == 'taxrate') { ?>
                <th><b><?php xl('Rate', 'e'); ?></b></th>
            <?php } elseif ($list_id == 'contrameth') { ?>
                <th><b><?php xl('Effectiveness', 'e'); ?></b></th>
            <?php } elseif ($list_id == 'lbfnames' || $list_id == 'transactions') { ?>
                <th title='<?php xl('Number of past history columns', 'e'); ?>'>
                    <b><?php xl('Repeats', 'e'); ?></b></th>
            <?php } elseif ($list_id == 'fitness') { ?>
                <th><b><?php xl('Color:Abbr', 'e'); ?></b></th>
            <?php } elseif ($list_id == 'adjreason' || $list_id == 'abook_type') { ?>
                <th><b><?php xl('Type', 'e'); ?></b></th>
            <?php } elseif ($list_id == 'immunizations') { ?>
                <th>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;<?php xl('CVX Code Mapping', 'e'); ?></b>
                </th>
            <?php }
if ($GLOBALS['ippf_specific']) { ?>
                            <th><b><?php xl('Global ID', 'e'); ?></b></th>
<?php } ?>
            <th><b><?php
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
            } ?></b></th>

            <th><b><?php xl('Code(s)', 'e'); ?></b></th>
            <?php
            if (preg_match('/_issue_list$/', $list_id)) { ?>
                <th><b><?php echo xlt('Subtype'); ?></b></th>
            <?php
            }
        endif; // end not fee sheet ?>
    </tr>
    </thead>
    <tbody>
    <?php
    // Get the selected list's elements.
    if ($list_id) {
        if ($list_id == 'feesheet') {
            $res = sqlStatement("SELECT * FROM fee_sheet_options " .
                "ORDER BY fs_category, fs_option");
            while ($row = sqlFetchArray($res)) {
                writeFSLine($row['fs_category'], $row['fs_option'], $row['fs_codes']);
            }
            for ($i = 0; $i < 3; ++$i) {
                writeFSLine('', '', '');
            }
        } elseif ($list_id == 'code_types') {
            $res = sqlStatement("SELECT * FROM code_types " .
                "ORDER BY ct_seq, ct_key");
            while ($row = sqlFetchArray($res)) {
                writeCTLine($row);
            }
            for ($i = 0; $i < 3; ++$i) {
                writeCTLine(array());
            }
        } elseif ($list_id == 'issue_types') {
            $res = sqlStatement("SELECT * FROM issue_types " .
                "ORDER BY category, ordering ASC");
            while ($row = sqlFetchArray($res)) {
                writeITLine($row);
            }
            for ($i = 0; $i < 3; ++$i) {
                writeITLine(array());
            }
        } else {
            /*
             *  Add edit options to show or hide in list management
             *   If the edit_options setting of the main list entry is set to 0,
             *    then none of the list items will show.
             *   If the edit_options setting of the main list entry is set to 1,
             *    then the list items with edit_options set to 1 will show.
             */
            $res = sqlStatement("SELECT lo.*
                         FROM list_options as lo
                         right join list_options as lo2 on lo2.option_id = lo.list_id AND lo2.edit_options = 1
                         WHERE lo.list_id = '{$list_id}' AND lo.edit_options = 1
                         ORDER BY seq,title");
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
        <input type='checkbox' name='ok_map_cvx_codes' id='ok_map_cvx_codes'
               value='1'/>
    </p>
<?php } // end if($list_id == 'immunizations') ?>

<p>
    <button type="submit" name='form_save' id='form_save'
            class="btn btn-default btn-save"><?php xl('Save', 'e'); ?></button>
</p>

</form>

<div class="modal fade" id="modal-new-list" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="edit_list.php" method="post" class="form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-label="<?php echo xl('Close'); ?>"><i
                                class="fa fa-times"
                                aria-hidden="true"></i>
                    </button>
                    <h4 class="modal-title"><?php xl('New List', 'e'); ?></h4>
                </div>
                <div class="modal-body">
                    <label for="newlistname"
                           class="control-label"><?php xl('List Name', 'e'); ?></label>
                    <input type="text" size="20" class="form-control"
                           maxlength="100" name="newlistname" id="newlistname">
                    <input type="hidden" name="formaction" value="addlist">

                </div>
                <div class="modal-footer text-right">
                    <button type="submit"
                            class="btn btn-default btn-save"><?php xl('Save', 'e'); ?></button>
                    <button type="button" class="btn btn-link btn-cancel"
                            data-dismiss="modal"><?php xl('Cancel', 'e'); ?></button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal #modal-new-list -->

</body>
<script type="text/javascript">
    // jQuery stuff to make the page a little easier to use

    $(document).ready(function () {
        $("#form_save").click(function () {
            SaveChanges();
        });
        $("#list_id").change(function () {
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
                alert("<?php xl('List names cannot start with numbers.', 'e'); ?>");
                return false;
            }
            var validname = $("#newlistname").val().replace(/[^A-za-z0-9 -]/g, "_"); // match any non-word characters and replace them
            if (validname != $("#newlistname").val()) {
                if (!confirm("<?php xl('Your list name has been changed to meet naming requirements.', 'e', '', '\n') . xl('Please compare the new name', 'e', '', ', \''); ?>" + validname + "<?php xl('with the old name', 'e', '\' ', ', \''); ?>" + $("#newlistname").val() + "<?php xl('Do you wish to continue with the new name?', 'e', '\'.\n', ''); ?>")) {
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
            if (confirm("<?php xl('WARNING', 'e', '', ' - ') . xl('This action cannot be undone.', 'e', '', '\n') . xl('Are you sure you wish to delete the entire list', 'e', ' ', '('); ?>" + listid + ")?")) {
                // submit the form to add a new field to a specific group
                $("#formaction").val("deletelist");
                $("#deletelistname").val(listid);
                $("#theform").submit();
            }
        };
    });

</script>
</html>
