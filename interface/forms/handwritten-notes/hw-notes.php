<?php

/**
 * Handwritten Notes Form
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
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!empty($_POST['hw-notes'])) {
    // Create new document to spill the data in
    $document = new Document();

    // Replace the unneccessary data in the beginning of the uri
    $formatted = str_replace("data:application/pdf;filename=generated.pdf;base64,", "", $_POST['hw-notes']);

    // Set the document to the encounter id
    $document->set_encounter_id($_SESSION['encounter']);


    $doc_id = sqlQuery("SELECT `value` FROM `form_handwritten` WHERE `name` = 'doc_category'");


    $doc = $document->createDocument($pid, $doc_id['value'], 'handwrittennotes-pid' . $pid . '-' . date('m-d-Y-g:ia') . '.pdf', 'application/pdf', base64_decode($formatted), '', '', $_SESSION['authUserID'], null);
    ?>

<!DOCTYPE html>
<html>

<head>
    <?php Header::setupHeader(['common']); ?>
    <title><?php echo xlt("Handwritten Notes"); ?></title>
    <style>
        .text-white:active {
            color: #fff;
        }
        .text-white:visited {
            color: #fff;
        }
    </style>
</head>
<body class="body_top">
    <div class="text-center pt-5">
        <p class="lead"><?php echo xlt("The document has been saved to the patient's document tree."); ?></p>
        <a href="#" class="btn btn-primary text-white" onclick="window.close()"><?php echo xlt("Close Window"); ?></a>
    </div>

</body>
</html>
<?php } else { ?>
<!DOCTYPE html>
<html>

<head>
    <?php Header::setupHeader(['common', 'jspdf']); ?>
    <!-- TODO: Find a way to deprecate literallycanvas !-->
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/literallycanvas/css/literallycanvas.css" />
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/react/build/react-with-addons.min.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/react/build/react-dom.min.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/literallycanvas/js/literallycanvas.min.js"></script>

    <title><?php echo xlt("Handwritten Notes") . "-" . xlt("New"); ?></title>

    <script>
        $(function() {
            var lc = LC.init(document.getElementById('literally'), {
                imageURLPrefix: '<?php echo $GLOBALS['assets_static_relative']; ?>/literallycanvas/img',
                imageSize: {width: 2550, height: 3300},
                backgroundColor: '#ffffff'
            });

            lc.setZoom(0.1);

            $("#fileform").submit(function(e) {
                var img = lc.getImage();

                // Convert to PDF using JavaScript jsPDF
                var pdf = new jsPDF({
                    orientation: 'p',
                    unit: 'in',
                    format: 'letter'
                });

                pdf.addImage(img, 'jpeg', 0, 0, 8.5, 11);
                var uri = pdf.output('datauristring');

                $('#hw-notes').val(uri);
            });
        });

    </script>
    <style>
        .literally {
            min-height: 95vh !important;
        }
    </style>
</head>

<body>
    <div id="error"></div>
    <div id="literally"></div>
    <div class="clearfix"></div>
    <form method="post" id="fileform" action="hw-notes.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <input type="hidden" name="hw-notes" id="hw-notes" value="" />
        <input type="submit" class="btn btn-primary btn-sm btn-block" id="submitnotes" value="<?php echo xla('Submit'); ?>" />
    </form>
</body>

</html>
<?php } ?>
