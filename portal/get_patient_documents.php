<?php

/**
 * Download documents from OpenEMR to the patient portal in a zip file(get_patient_documents.php)
 * This program is used to download patient documents in a zip file in the Patient Portal.
 * Added parse and show documents for selection instead of all by default
 * The original author did not pursue this but I thought it would be a good addition to
 * the patient portal
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Giorgos Vasilakos <giorg.vasilakos@gmail.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2012 Giorgos Vasilakos <giorg.vasilakos@gmail.com>
 * @copyright Copyright (c) 2015-2017 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2017-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("./verify_session.php");
require_once("$srcdir/documents.php");
require_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Get all the documents of the patient
$sql = "SELECT url, id, mimetype, `name` FROM `documents` WHERE `foreign_id` = ? AND `deleted` = 0";
$fres = sqlStatement($sql, array($pid));

$documents = [];
while ($file = sqlFetchArray($fres)) {
    // Find the document category
    $sql = "SELECT name, lft, rght FROM `categories`, `categories_to_documents`
            WHERE `categories_to_documents`.`category_id` = `categories`.`id`
            AND `categories_to_documents`.`document_id` = ?";
    $catres = sqlStatement($sql, array($file['id']));
    $cat = sqlFetchArray($catres);

    // Find the tree of the document's category
    $sql = "SELECT name FROM categories WHERE lft < ? AND rght > ? ORDER BY lft ASC";
    $pathres = sqlStatement($sql, array($cat['lft'], $cat['rght']));

    // Create the tree of the categories
    $path = "";
    while ($parent = sqlFetchArray($pathres)) {
        $path .= convert_safe_file_dir_name($parent['name']) . "/";
    }

    $path .= convert_safe_file_dir_name($cat['name']) . "/";

    // Store documents under their categories
    $category = trim($path, "/");
    if (!isset($documents[$category])) {
        $documents[$category] = [];
    }
    $documents[$category][] = [
        'id' => $file['id'],
        'name' => $file['name']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo text("Download On File Documents"); ?></title>
    <?php Header::setupHeader(['no_main-theme', 'portal-theme']); ?>
    <script>
        // Function to toggle all checkboxes
        function toggleCheckboxes(className, sourceCheckbox) {
            let checkboxes = document.querySelectorAll('.' + className);
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = sourceCheckbox.checked;
            }
        }

        // Function to toggle all checkboxes in the form
        function toggleAllCheckboxes(sourceCheckbox) {
            let checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = sourceCheckbox.checked;
            }
        }

        // Function to normalize category names for use as class names
        function normalizeClassName(category) {
            return category.replace(/[^a-zA-Z0-9]/g, '-');
        }

        // Function to validate form submission
        function validateForm(event) {
            let checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
            if (checkboxes.length === 0) {
                alert("Please select at least one document to download.");
                event.preventDefault(); // Prevent form submission
            }
        }
    </script>
</head>
<body>
    <div class="container-fluid">
        <h4><?php echo text("Select Documents to Download"); ?></h4>
        <form id="download-form" action="report/document_downloads_action.php" method="post" onsubmit="validateForm(event)">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)">
                <label class="form-check-label" for="selectAll"><?php echo text("Select All Toggle"); ?></label>
            </div>
            <div class="row">
                <?php foreach ($documents as $category => $docs) {
                    $title = str_replace("Categories/", "", $category);
                    $normalizedTitle = preg_replace('/[^a-zA-Z0-9]/', '-', $title);
                    ?>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo text($normalizedTitle); ?></h5>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll<?php echo attr($normalizedTitle); ?>" onclick="toggleCheckboxes('category-<?php echo attr($normalizedTitle); ?>', this)">
                                    <label class="form-check-label" for="selectAll<?php echo attr($normalizedTitle); ?>"><?php echo text("Select All"); ?></label>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php foreach ($docs as $doc) { ?>
                                    <div class="form-check">
                                        <input class="form-check-input category-<?php echo attr($normalizedTitle); ?>" type="checkbox" name="documents[]" value="<?php echo attr($doc['id']); ?>" id="doc<?php echo text($doc['id']); ?>">
                                        <label class="form-check-label" for="doc<?php echo attr($doc['id']); ?>">
                                            <?php echo text($doc['name']); ?>
                                        </label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <button type="submit" class="btn btn-primary float-right mt-1"><?php echo text("Download Selected Documents"); ?></button>
        </form>
    </div>
</body>
</html>
