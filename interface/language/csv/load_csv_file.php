<?php

/*
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2014 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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

//default to language ID 3 (should be spanish)
$defaultLangID = 3;

$sqlLanguages = "SELECT *, lang_description as trans_lang_description FROM lang_languages ORDER BY lang_id";
$resLanguages = SqlStatement($sqlLanguages);
$languages = array();
while ($row = sqlFetchArray($resLanguages)) {
    array_push($languages, $row);
}

?>
<form name="process_csv" method="post" enctype="multipart/form-data"
    action="?m=csvval&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>"
    onsubmit="return top.restoreSession()">

    <!-- Select Language. Cloned from lang_definition.php. -->
    <div class="form-group">
        <label for="selectLanguage"><?php echo xlt('Select Language') . ":"; ?></label>
        <select class="form-control" name='language_id' id="selectLanguage">
            <?php
            // sorting order of language titles depends on language translation options.
            $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
            // Use and sort by the translated language name.
            $sql = "SELECT ll.lang_id, " .
                "IF(LENGTH(ld.definition),ld.definition,ll.lang_description) AS lang_description " .
                "FROM lang_languages AS ll " .
                "LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description " .
                "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
                "ld.lang_id = ? " .
                "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
            $res = SqlStatement($sql, array($mainLangID));
            // collect the default selected language id, and then display list
            $tempLangID = isset($_POST['language_id']) ? $_POST['language_id'] : $mainLangID;
            while ($row = SqlFetchArray($res)) {
                if ($tempLangID == $row['lang_id']) {
                    echo "<option value='" . attr($row['lang_id']) . "' selected>" .
                        text($row['lang_description']) . "</option>";
                } else {
                      echo "<option value='" . attr($row['lang_id']) . "'>" .
                          text($row['lang_description']) . "</option>";
                }
            }
            ?>
        </select>
    </div>

    <!-- File Upload Control -->
    <div class="form-group">
        <p><?php echo xlt('Select a CSV file with translation information to review.'); ?>
        <?php echo xlt('It should be UTF-8 encoded with comma separated values.'); ?></p>
        <input type="file" name="language_file" id="language_file"></input>
    </div>

    <!-- Submit Button -->
    <div class="form-group">
        <input type="submit" class="btn btn-primary" name="submit" value="<?php echo xla('Submit'); ?>">
    </div>

</form>

<?php echo activate_lang_tab('csv-link'); ?>
