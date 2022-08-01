<?php

/**
 * find_code_popup.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/patient.inc');
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$info_msg = "";
$codetype = $_REQUEST['codetype'] ?? '';
if (!empty($codetype)) {
    $allowed_codes = split_csv_line($codetype);
} else {
    $allowed_codes = array_keys($code_types);
}

$form_code_type = $_POST['form_code_type'] ?? '';

// Determine which code type will be selected by default.
$default = '';
if (!empty($form_code_type)) {
    $default = $form_code_type;
    // if they've submitted a code type we only want to use those.
    $allowed_codes = [$default];
} elseif (!empty($allowed_codes) && count($allowed_codes) == 1) {
    $default = $allowed_codes[0];
} elseif (!empty($_REQUEST['default'])) {
    $default = $_REQUEST['default'];
    $codetype = $default;
}

// This variable is used to store the html element
// of the target script where the selected code
// will be stored in.
$target_element = $_GET['target_element'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Code Finder'); ?></title>
    <?php Header::setupHeader('opener'); ?>
    <script>
        // Standard function
        function selcode(codetype, code, selector, codedesc) {
            if (opener.closed || !opener.set_related) {
                alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
            } else {
                var msg = opener.set_related(codetype, code, selector, codedesc);
                if (msg) alert(msg);
                dlgclose();
                return false;
            }
        }

        // TBD: The following function is not necessary. See
        // interface/forms/LBF/new.php for an alternative method that does not require it.
        // Rod 2014-04-15

        // Standard function with additional parameter to select which
        // element on the target page to place the selected code into.
        function selcode_target(codetype, code, selector, codedesc, target_element) {
            if (opener.closed || !opener.set_related_target) {
                alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
            } else {
                // opener.set_related_target(codetype, code, selector, codedesc, target_element);
               var msg = opener.set_related(codetype, code, selector, codedesc);
               if (msg) alert(msg);
            }
            dlgclose();
            return false;
        }

    </script>
</head>
<?php
$focus = "document.theform.search_term.select();";
?>
<body onload="<?php echo $focus; ?>">
    <div class="container-fluid">
        <?php
        $string_target_element = "";
        if (!empty($target_element)) {
            $string_target_element = "?target_element=" . attr_url($target_element) . "&";
        } else {
            $string_target_element = "?";
        }
        ?>
        <?php if (!empty($allowed_codes)) { ?>
        <form class="form-inline" method='post' name='theform' 
            action='find_code_popup.php<?php echo $string_target_element ?>codetype=<?php echo attr_url($codetype) ?>'>
        <?php } else { ?>
        <form class="form-inline" method='post' name='theform' 
            action='find_code_popup.php<?php echo $string_target_element ?>'>
        <?php } ?>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="form-group">
                <div class="input-group mt-1">
                <?php
                if (!empty($allowed_codes)) { ?>
                    <select class='form-control' name='form_code_type'>
                        <?php
                        foreach (array_keys($code_types) as $code) {
                            if (empty($code_types[$code]['label'])) {
                                continue;
                            }
                            $selected_attr = ($default == $code) ? " selected='selected'" : '';
                            ?>
                        <option value='<?php echo attr($code) ?>'<?php
                            echo $selected_attr ?>><?php echo xlt($code_types[$code]['label']) ?></option>
                        <?php } ?>
                    </select>
                    <?php
                } else {
                    // No allowed types were specified, so show all.
                    echo "<select class='form-control' name='form_code_type'";
                    echo ">\n";
                    foreach ($code_types as $key => $value) {
                        if (empty($value['label'])) {
                            continue;
                        }
                        echo "<option value='" . attr($key) . "'";
                        if ($default == $key) {
                            echo " selected";
                        }
                        echo ">" . xlt($value['label']) . "</option>\n";
                    }
                    echo "<option value='PROD'";
                    if ($default == 'PROD') {
                        echo " selected";
                    }
                    echo ">" . xlt("Product") . "</option>\n";
                    echo "</select>\n";
                }
                ?>
                </div>
                <div class="input-group mt-1">
                    <input type='text' class='form-control' name='search_term' id="searchTerm"
                        value='<?php echo attr($_REQUEST['search_term'] ?? ''); ?>'
                        title='<?php echo xla('Any part of the desired code or its description'); ?>'
                        placeholder="<?php echo xla('Search for'); ?>" />
                    <div class="input-group-append">
                        <button type='submit' class='btn btn-primary btn-search' 
                            name='bn_search' value='Search'></button>
                        <?php if (!empty($target_element)) { ?>
                        <button type='button' class='btn btn-primary btn-delete' value=''
                            onclick="selcode_target('', '', '', '', 
                                <?php echo attr_js($target_element); ?>)"></button>
                        <?php } else { ?>
                        <button type='button' class='btn btn-danger btn-delete' value='' 
                            onclick="selcode('', '', '', '')"></button>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php
            if (!empty($_REQUEST['bn_search']) || !empty($_REQUEST['search_term'])) {
                if (!$form_code_type) {
                    $form_code_type = $codetype;
                }
                ?>
                <div class="table-responsive">
                <table class='table table-striped table-responsive-sm'>
                    <thead>
                        <th class='font-weight-bold'><?php echo xlt('Code'); ?></th>
                        <th class='font-weight-bold'><?php echo xlt('Description'); ?></th>
                    </thead>
                    <tbody>
                    <?php
                    $search_term = $_REQUEST['search_term'];
                    $res = main_code_set_search($allowed_codes, $search_term);
                    if ($form_code_type == 'PROD') {
                        // Special case that displays search for products/drugs
                        while ($row = sqlFetchArray($res)) {
                            $drug_id = $row['drug_id'];
                            $selector = $row['selector'];
                            $desc = $row['name'];
                            $anchor = "<a href='' " .
                                "onclick='return selcode(\"PROD\", " .
                                attr_js($drug_id) . ", " . attr_js($selector) . ", " . attr_js($desc) . ")'>";
                            echo "<tr>";
                            echo "<td>$anchor" . text($drug_id . ":" . $selector) . "</a></td>\n";
                            echo "<td>$anchor" . text($desc) . "</a></td>\n";
                            echo "</tr>";
                        }
                    } else {
                        while ($row = sqlFetchArray($res)) { // Display normal search
                            $itercode = $row['code'];
                            $itertext = ucfirst(strtolower(trim($row['code_text'])));
                            $dynCodeType = $form_code_type ?: $codetype;
                            if (stripos($dynCodeType, 'VALUESET') !== false) {
                                $dynCodeType = $row['valueset_code_type'] ?? 'VALUESET';
                            }
                            if (!empty($target_element)) {
                                // add a 5th parameter to function to select the target element
                                // on the form for placing the code.
                                $anchor = "<a href='' " .
                                    "onclick='return selcode_target(" . attr_js($dynCodeType) .
                                    ", " . attr_js($itercode) . ", \"\", " . attr_js($itertext) .
                                    ", " . attr_js($target_element) . ")'>";
                            } else {
                                $anchor = "<a href='' " .
                                    "onclick='return selcode(" . attr_js($dynCodeType) .
                                    ", " . attr_js($itercode) . ", \"\", " . attr_js($itertext) . ")'>";
                            }
                            echo " <tr>";
                            echo "  <td>$anchor" . text($itercode) . "</a></td>\n";
                            echo "  <td>$anchor" . text($itertext) . "</a></td>\n";
                            echo " </tr>";
                        }
                    }
                    ?>
                    </tbody>
                </table>
                </div>
            <?php } ?>
        </form>
    </div>
</body>
</html>
