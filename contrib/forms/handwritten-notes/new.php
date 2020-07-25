<?php

/**
 * Create new handwritten notes area
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/

require_once("../../globals.php");
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!$encounter) {
    die(xlt("Internal Error: We do not seem to be in an encounter!"));
}

if ($_POST) {
    if (!empty($_POST['docid'])) {
        $setdocid = "UPDATE `form_handwritten` SET `value` = ? WHERE `name` = 'doc_category'";
        sqlStatement($setdocid, [$_POST['docid']]);
        echo xlt("Your data has been saved.");
    }
}

function getDocCats()
{
    $output = "";
    // Get document categories
    $sql = "SELECT `id`, `name` FROM `categories`";
    $categories = sqlStatement($sql);

    while ($cat = sqlFetchArray($categories)) {
        $output .= '<option value="' . attr($cat['id']) . '">' . text($cat['name']) . '</option>\n';
    }

    return $output;
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['common']); ?>
    <title><?php echo xlt("Handwritten Notes"); ?></title>
</head>
<body class="body_top">
    <div class="container-fluid">
        <!-- This method opens the handwritten notes area in a new tab/window so that we can use all of the iPad's screen real estate !-->
        <form class="pt-5 mx-auto" method="post" action="<?php echo $GLOBALS['rootdir']; ?>/forms/handwritten-notes/hw-notes.php" target="_blank">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="submit" class="btn btn-primary btn-lg" value="<?php echo xla('Open Handwritten Notes Area'); ?>" />
        </form>

        <!-- Setup area !-->
        <div class="collapse" id="setup">
            <form method="post" action="<?php echo $GLOBALS['rootdir']; ?>/forms/handwritten-notes/new.php">
                <div class="form-group">
                    <label for="docid"><?php echo xlt("Enter the document category ID that you would like to save the notes to:"); ?></label>
                    <select class="form-control" name="docid" id="docid">
                        <?php echo getDocCats(); ?>
                    </select>
                </div>

                <input type="submit" class="btn btn-primary" value="<?php echo xla('Submit'); ?>" />
            </form>
        </div>
        <div class="pt-5">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#setup" aria-expanded="false" aria-controls="setup"><?php echo xlt("Setup"); ?></button>
        </div>
    </div>
</body>
</html>
