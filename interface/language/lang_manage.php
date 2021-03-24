<?php

/**
 * lang_manage.php script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Ensure this script is not called separately
if ($langModuleFlag !== true) {
    die(function_exists('xlt') ? xlt('Authentication Error') : 'Authentication Error');
}

// gacl control
$thisauth = AclMain::aclCheckCore('admin', 'language');
if (!$thisauth) {
    echo "<html>\n<body>\n";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

if (!empty($_POST['check']) || !empty($_POST['synchronize'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

  // set up flag if only checking for changes (ie not performing synchronization)
    $checkOnly = 0;
    if (!empty($_POST['check'])) {
        $checkOnly = 1;
    }

  // set up the mysql collation string to ensure case is sensitive in the mysql queries
    if (!$disable_utf8_flag) {
        if (!empty($sqlconf["db_encoding"]) && ($sqlconf["db_encoding"] == "utf8mb4")) {
            $case_sensitive_collation = "COLLATE utf8mb4_bin";
        } else {
            $case_sensitive_collation = "COLLATE utf8_bin";
        }
    } else {
        $case_sensitive_collation = "COLLATE latin1_bin";
    }

    $difference = 0; //flag

  //
  // collect and display(synchronize) new custom languages
  //
    $sql = "SELECT lang_description FROM lang_languages";
    $res = SqlStatement($sql);
    $row_main = array();
    while ($row = SqlFetchArray($res)) {
        $row_main[] = $row['lang_description'];
    }

    $sql = "SELECT lang_description FROM lang_custom";
    $res = SqlStatement($sql);
    $row_custom = array();
    while ($row = SqlFetchArray($res)) {
        $row_custom[] = $row['lang_description'];
    }

    $custom_languages = array_diff(array_unique($row_custom), array_unique($row_main));
    foreach ($custom_languages as $var) {
        if ($var == '') {
            continue;
        }

        echo xlt('Following is a new custom language:') . " " . text($var) . "<br>";
        if (!$checkOnly) {
            // add the new language (first collect the language code)
            $sql = "SELECT lang_code FROM lang_custom WHERE constant_name='' AND lang_description=? " . $case_sensitive_collation . " LIMIT 1";
            $res = SqlStatement($sql, array($var));
            $row = SqlFetchArray($res);
            $sql = "INSERT INTO lang_languages SET lang_code=?, lang_description=?";
            SqlStatement($sql, array($row['lang_code'], $var));
            echo xlt('Synchronized new custom language:') . " " . text($var) . "<br><br>";
        }

        $difference = 1;
    }

  //
  // collect and display(synchronize) new custom constants
  //
    $sql = "SELECT constant_name FROM lang_constants";
    $res = SqlStatement($sql);
    $row_main = array();
    while ($row = SqlFetchArray($res)) {
        $row_main[] = $row['constant_name'];
    }

    $sql = "SELECT constant_name FROM lang_custom";
    $res = SqlStatement($sql);
    $row_custom = array();
    while ($row = SqlFetchArray($res)) {
        $row_custom[] = $row['constant_name'];
    }

    $custom_constants = array_diff(array_unique($row_custom), array_unique($row_main));
    foreach ($custom_constants as $var) {
        if ($var == '') {
            continue;
        }

        echo xlt('Following is a new custom constant:') . " " . text($var) . "<br>";
        if (!$checkOnly) {
            // add the new constant
            $sql = "INSERT INTO lang_constants SET constant_name=?";
            SqlStatement($sql, array($var));
            echo xlt('Synchronized new custom constant:') . " " . text($var) . "<br><br>";
        }

        $difference = 1;
    }

  //
  // collect and display(synchronize) custom definitions
  //
    $sql = "SELECT lang_description, lang_code, constant_name, definition FROM lang_custom WHERE lang_description != '' AND constant_name != ''";
    $res = SqlStatement($sql);
    while ($row = SqlFetchArray($res)) {
        // collect language id
        $sql = "SELECT lang_id FROM lang_languages WHERE lang_description=? " . $case_sensitive_collation . " LIMIT 1";
        $res2 = SqlStatement($sql, array($row['lang_description']));
        $row2 = SqlFetchArray($res2);
        $language_id = $row2['lang_id'];

        // collect constant id
        $sql = "SELECT cons_id FROM lang_constants WHERE constant_name=? " . $case_sensitive_collation . " LIMIT 1";
        $res2 = SqlStatement($sql, array($row['constant_name']));
        $row2 = SqlFetchArray($res2);
        $constant_id = $row2['cons_id'];

        // collect definition id (if it exists)
        $sql = "SELECT def_id FROM lang_definitions WHERE cons_id=? AND lang_id=? LIMIT 1";
        $res2 = SqlStatement($sql, array($constant_id, $language_id));
        $row2 = SqlFetchArray($res2);
        $def_id = $row2['def_id'];

        if ($def_id) {
            //definition exist, so check to see if different
            $sql = "SELECT * FROM lang_definitions WHERE def_id=? AND definition=? " . $case_sensitive_collation;
            $res_test = SqlStatement($sql, array($def_id, $row['definition']));
            if (SqlFetchArray($res_test)) {
            //definition not different
                continue;
            } else {
                //definition is different
                echo xlt('Following is a new definition (Language, Constant, Definition):') .
                " " . text($row['lang_description']) .
                " " . text($row['constant_name']) .
                " " . text($row['definition']) . "<br>";
                if (!$checkOnly) {
                    //add new definition
                    $sql = "UPDATE `lang_definitions` SET `definition`=? WHERE `def_id`=? LIMIT 1";
                    SqlStatement($sql, array($row['definition'], $def_id));
                    echo xlt('Synchronized new definition (Language, Constant, Definition):') .
                    " " . text($row['lang_description']) .
                    " " . text($row['constant_name']) .
                    " " . text($row['definition']) . "<br><br>";
                }

                $difference = 1;
            }
        } else {
            echo xlt('Following is a new definition (Language, Constant, Definition):') .
            " " . text($row['lang_description']) .
            " " . text($row['constant_name']) .
            " " . text($row['definition']) . "<br>";
            if (!$checkOnly) {
                //add new definition
                $sql = "INSERT INTO lang_definitions (cons_id,lang_id,definition) VALUES (?,?,?)";
                SqlStatement($sql, array($constant_id, $language_id, $row['definition']));
                echo xlt('Synchronized new definition (Language, Constant, Definition):') .
                " " . text($row['lang_description']) .
                " " . text($row['constant_name']) .
                " " . text($row['definition']) . "<br><br>";
            }

            $difference = 1;
        }
    }

    if (!$difference) {
        echo xlt('The translation tables are synchronized.');
    }
}
?>

<form class="form-inline" name="manage_form" method="post" action="?m=manage&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onsubmit="return top.restoreSession()">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <div class="container">
        <div class="row">
            <div class="col-2">
                <input type="submit" class="btn btn-primary" name="check" value="<?php echo xla('Check'); ?>">
            </div>
            <div class="col-10">
                <p class="text">(<?php echo xlt('Check for differences of translations with custom language table.'); ?>)</p>
            </div>
            <div class="col-2">
                <input type="submit" class="btn btn-primary" name="synchronize" value="<?php echo xla('Synchronize'); ?>">
            </div>
            <div class="col-10">
                <p class="text">(<?php echo xlt('Synchronize translations with custom language table.'); ?>)</p>
            </div>
        </div>
    </div>
</form>

<script>
    $("#manage-link").addClass("active");
    $("#definition-link").removeClass("active");
    $("#language-link").removeClass("active");
    $("#constant-link").removeClass("active");
</script>
