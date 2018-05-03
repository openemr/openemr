<?php
// Function to generate a drop-list.
// this version modified by dh for the appointment statuses to block access to normal users
// to Record Complete, Soap Overdue, Attendence Overdue.  Uses ACL check to allow 
// admin users to select these values.

function generate_select_list_dh(
    $tag_name,
    $list_id,
    $currvalue,
    $title
    //$empty_name = ' ',
    //$class = '',
    //$onchange = '',
    //$tag_id = '',
    //$custom_attributes = null,
    //$multiple = false,
    //$backup_list = ''
) 
{
    $s = '';

    $tag_name_esc = attr($tag_name);

    //if ($multiple) {
    //    $tag_name_esc = $tag_name_esc . "[]";
    //}

    $s .= "<select name='$tag_name_esc'";

    //if ($multiple) {
    //    $s .= " multiple='multiple'";
    //}

    $tag_id_esc = attr($tag_name);

    if ($tag_id != '') {
        $tag_id_esc = attr($tag_id);
    }

    $s .= " id='$tag_id_esc'";

    //if (!empty($class)) {
    //    $class_esc = attr($class);
    //    $s .= " class='form-control $class_esc'";
    $s .= " class='form-control'";
    

    //if ($onchange) {
    //    $s .= " onchange='$onchange'";
    //}

    //if ($custom_attributes != null && is_array($custom_attributes)) {
    //    foreach ($custom_attributes as $attr => $val) {
    //        if (isset($custom_attributes [$attr])) {
    //           $s .= " " . attr($attr) . "='" . attr($val) . "'";
    //        }
    //    }
    //}

    $selectTitle = attr($title);
    $s .= " title='$selectTitle'>";
    $selectEmptyName = xlt($empty_name);
    //if ($empty_name) {
    //    $s .= "<option value=''>" . $selectEmptyName . "</option>";
    //}

    // List order depends on language translation options.
    //  (Note we do not need to worry about the list order in the algorithm
    //   after the below code block since that is where searches for exceptions
    //   are done which include inactive items or items from a backup
    //   list; note these will always be shown at the bottom of the list no matter the
    //   chosen order.)
    $lang_id = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
    // sort by title
    if (($lang_id == '1' && !empty($GLOBALS['skip_english_translation'])) || !$GLOBALS['translate_lists']) {
        // do not translate
        if ($GLOBALS['gb_how_sort_list'] == '0') {
            // order by seq
            $order_by_sql = "seq, title";
        } else { //$GLOBALS['gb_how_sort_list'] == '1'
            // order by title
            $order_by_sql = "title, seq";
        }

        $lres = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity=1 ORDER BY " . $order_by_sql, array($list_id));
    } else {
        // do translate
        if ($GLOBALS['gb_how_sort_list'] == '0') {
            // order by seq
            $order_by_sql = "lo.seq, IF(LENGTH(ld.definition),ld.definition,lo.title)";
        } else { //$GLOBALS['gb_how_sort_list'] == '1'
            // order by title
            $order_by_sql = "IF(LENGTH(ld.definition),ld.definition,lo.title), lo.seq";
        }

        $lres = sqlStatement("SELECT lo.option_id, lo.is_default, " .
        "IF(LENGTH(ld.definition),ld.definition,lo.title) AS title " .
        "FROM list_options AS lo " .
        "LEFT JOIN lang_constants AS lc ON lc.constant_name = lo.title " .
        "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
        "ld.lang_id = ? " .
        "WHERE lo.list_id = ?  AND lo.activity=1 " .
        "ORDER BY " . $order_by_sql, array($lang_id, $list_id));
    }

    $got_selected = false;

    while ($lrow = sqlFetchArray($lres)) {
        $selectedValues = explode("|", $currvalue);

        $optionValue = attr($lrow ['option_id']);
        $s .= "<option value='$optionValue'";

        if ((strlen($currvalue) == 0 && $lrow ['is_default']) || (strlen($currvalue) > 0 && in_array($lrow ['option_id'], $selectedValues))) {
            $s .= " selected";
            $got_selected = true;
        }
        // dh added this for the items only allowed to be changed by admin and system    
        if (!acl_check('admin', 'super') && (($lrow ['option_id']=="RC") || ($lrow ['option_id']=="SO") || ($lrow ['option_id']=="AO"))) {
            $s .= ' disabled';
        } 

        // Already has been translated above (if applicable), so do not need to use
        // the xl_list_label() function here
        $optionLabel = text($lrow ['title']);
        $s .= ">$optionLabel</option>\n";
    }

    /*
      To show the inactive item in the list if the value is saved to database
      */
    if (!$got_selected && strlen($currvalue) > 0) {
        $lres_inactive = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 0 AND option_id = ? ORDER BY seq, title", array($list_id, $currvalue));
        $lrow_inactive = sqlFetchArray($lres_inactive);
        if ($lrow_inactive['option_id']) {
            $optionValue = htmlspecialchars($lrow_inactive['option_id'], ENT_QUOTES);
            $s .= "<option value='$optionValue' selected>" . htmlspecialchars(xl_list_label($lrow_inactive['title']), ENT_NOQUOTES) . "</option>\n";
            $got_selected = true;
        }
    }

    if (!$got_selected && strlen($currvalue) > 0 && !$multiple) {
        $list_id = $backup_list;
        $lrow = sqlQuery("SELECT title FROM list_options WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue));

        if ($lrow > 0 && !empty($backup_list)) {
            $selected = text(xl_list_label($lrow ['title']));
            $s .= "<option value='$currescaped' selected> $selected </option>";
            $s .= "</select>";
        } else {
            $s .= "<option value='$currescaped' selected>* $currescaped *</option>";
            $s .= "</select>";
            $fontTitle = xlt('Please choose a valid selection from the list.');
            $fontText = xlt('Fix this');
            $s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
        }
    } elseif (!$got_selected && strlen($currvalue) > 0 && $multiple) {
        //if not found in main list, display all selected values that exist in backup list
        $list_id = $backup_list;

        $got_selected_backup = false;
        if (!empty($backup_list)) {
            $lres_backup = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            while ($lrow_backup = sqlFetchArray($lres_backup)) {
                $selectedValues = explode("|", $currvalue);

                $optionValue = attr($lrow_backup['option_id']);

                if (in_array($lrow_backup ['option_id'], $selectedValues)) {
                    $s .= "<option value='$optionValue'";
                    $s .= " selected";
                    $optionLabel = text(xl_list_label($lrow_backup ['title']));
                    $s .= ">$optionLabel</option>\n";
                    $got_selected_backup = true;
                }
            }
        }

        if (!$got_selected_backup) {
            $selectedValues = explode("|", $currvalue);
            foreach ($selectedValues as $selectedValue) {
                $s .= "<option value='" . attr($selectedValue) . "'";
                $s .= " selected";
                $s .= ">* " . text($selectedValue) . " *</option>\n";
            }

            $s .= "</select>";
            $fontTitle = xlt('Please choose a valid selection from the list.');
            $fontText = xlt('Fix this');
            $s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
        }
    } else {
        $s .= "</select>";
    }

    return $s;
}

?>