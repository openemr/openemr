<?php

/**
 * validate_core.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dror Golan <drorgo@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


/**get all the validation on the page
 * @param $title
 * @return array of validation rules and forms names
 */
function collectValidationPageRules($title, $active = true)
{

    // Note from Rod: Not sure what the purpose is of $active because nothing calls it with a false value.

    if ($active) {
        $sql = sqlStatement("SELECT * " .
            "FROM `list_options` WHERE list_id=? AND activity=?  AND title = ?", array('page_validation',1,$title));
    } else {
        $sql = sqlStatement("SELECT * " .
            "FROM `list_options` WHERE list_id=? AND title=?", array('page_validation', $title));
    }

    $dataArray = array();
    while ($row = sqlFetchArray($sql)) {
        $formPageNameArray = explode('#', $row['option_id']);
        $dataArray[$formPageNameArray[1]] = array('page_name' => $formPageNameArray[0] ,'rules' => $row['notes']);
    }

    return $dataArray;
}

/**this function creates client side validation rules for each <form> declared in list : Patient Validation - patient_validation
 * @param $fileNamePath
 * @output a generated javascript tag with the validation
 */
function validateUsingPageRules($fileNamePath)
{

    $path = '';

    if ($GLOBALS['webroot'] != '') {
        $path = str_replace($GLOBALS['webroot'], '', $fileNamePath);
    } else {
        $path = $fileNamePath;
    }

    print '<!--Page Form Validations-->';
//if we would like to get all the page forms rules we need to call collectValidationPageRules($title) this way there is a
    $collectThis = collectValidationPageRules($path);
    if ($collectThis) {
        print '<!---Start of page  form validation-->';
        print '<!--//include new rules of submitme functionallity-->';
        echo("\r\n");
        //Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation
        $use_validate_js = 1;
        require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php");
        echo("\r\n");
        print '<script>';
        echo ("$(function () {");
        echo("\r\n");
        foreach ($collectThis as $key => $value) {
            echo("try{");
            echo("\r\n");
            echo('if(document.getElementsByName(' . js_escape($key) . ').length>0)');
            echo("\r\n");
            echo('{');
            echo("\r\n");
            echo('var form = document.getElementsByName(' . js_escape($key) . ');');
            echo("\r\n");
            echo('form[0].setAttribute("id",' . js_escape($key) . ');');
            echo("\r\n");

            echo('//Use validation script js Validations-');
            echo("\r\n");

            echo('$(' . js_escape("#" . $key) . ').on("submit", function(event){');
            echo("\r\n");

            echo("\r\n");
            echo ('var submitvalue = submitme(' . js_escape($use_validate_js) . ',event,' . js_escape($key) . ',' . json_sanitize($collectThis[$key]['rules']) . ');');
            echo("\r\n");
            echo(' if(submitvalue){');
            echo("\r\n");
            echo(" ");
            echo("\r\n");
            echo('}');
            echo("\r\n");
            echo('else{');
            echo("\r\n");
            echo (" event.preventDefault();");
            echo("\r\n");
            echo('}');
            echo("\r\n");
            echo('});}}');
            //echo('$("#'.$key.'").prop("onclick", \'return ');

            echo("\r\n");
            echo('catch(err)');
            echo("\r\n");
            echo('{');
            echo("\r\n");
            echo('//log err - console.log(err)');
            echo("\r\n");
            echo('}');
        }

        echo ("});");
        echo("\r\n");
        echo('</script>');
        print '<!---End of page  form validation-->';
    }
}
