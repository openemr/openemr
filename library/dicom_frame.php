<?php
/**
 * Dicom viewer wrapper script for documents
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Jerry Padgett <sjpadgett@gmail.com> 'Viewer wrapper'
 * @author  Victor Kofia <https://kofiav.com> 'Viewer'
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017-2018 Victor Kofia <https://kofiav.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

/* Warning: This script wraps the Dicom viewer which is HTML5 compatible only and bootstrap styling
*  should not be used inside this script due to style conflicts with viewer, namely, hidden class.
*/

require_once('../interface/globals.php');

$web_path = $_REQUEST['web_path'];
$patid = $_REQUEST['patient_id'];
$docid = isset($_REQUEST['document_id']) ? $_REQUEST['document_id'] : $_REQUEST['doc_id'];
$d = new Document(attr($docid));
$type = '.dcm';
if ($d->get_mimetype() == 'application/dicom+zip') {
    $type = '.zip';
}

$web_path .= '&retrieve&patient_id=' . attr($patid) . '&document_id=' . attr($docid) . '&as_file=false&type=' . attr($type);

?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/modernizr-3-5-0/dist/modernizr-build.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/i18next-9-0-1/i18next.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/i18next-xhr-backend-1-4-3/i18nextXHRBackend.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/i18next-browser-languagedetector-2-0-0/i18nextBrowserLanguageDetector.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/konva-1-6-8/konva.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/magic-wand-js/js/magic-wand-min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jszip-3-1-5/dist/jszip.min.js"></script>
    <!-- Third party (viewer) -->
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/flot-0-8-3/jquery.flot.js"></script>
    <!-- decoders -->
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/dwv-0-21-0/decoders/pdfjs/jpx.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/dwv-0-21-0/decoders/pdfjs/util.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/dwv-0-21-0/decoders/pdfjs/arithmetic_decoder.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/dwv-0-21-0/decoders/pdfjs/jpg.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/dwv-0-21-0/decoders/rii-mango/lossless-min.js"></script>
    <!-- Local (dwv) -->
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/dwv-0-21-0/dist/dwv.min.js"></script>
    <!-- i18n dwv wrapper -->
    <script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/library/js/dwv/dwv_i18n.js"></script>
</head>
<style type="text/css">
    .warn_diagnostic {
        margin: 10px auto 10px auto;
        color: rgb(255, 0, 0);
        font-size: 1.5em;
    }

    .ui-autocomplete {
        position: absolute;
        top: 0;
        left: 0;
        min-width: 200px;
        cursor: default;
    }

    .ui-menu-item {
        min-width: 200px;
    }

    .fixed-height {
        min-width: 200px;
        padding: 1px;
        max-height: 35%;
        overflow: auto;
    }

    .loader {
        position: absolute;
        left: 25%;
        top: 15%;
        z-index: 1;
        border: 12px solid #f3f3f3;
        border-radius: 50%;
        border-top: 12px solid #3498db;
        width: 60px;
        height: 60px;
        -webkit-animation: spin 2s linear infinite; /* Safari */
        animation: spin 2s linear infinite;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
<body>
<!-- DWV -->
<div id="dwv" src='<?php echo $web_path ?>'>
    <!-- Toolbar -->
    <div class="toolbar"></div>
    <div class="warn_diagnostic"><?php echo xlt('Not For Diagnostic Use') ?>
        <!-- Layer Container -->
        <div class="layerContainer">
            <span class="loader"></span>
            <canvas id="dwvimg" class="imageLayer"><?php echo xlt('Only for HTML5 compatible browsers.') ?></canvas>
        </div>
        <!-- /layerContainer -->
    </div><!-- /dwv -->
    <!-- Main -->
    <script type="text/javascript" src="<?php echo $GLOBALS['web_root'] ?>/library/js/dwv/dicom_gui.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['web_root'] ?>/library/js/dwv/dicom_launcher.js"></script>
    <script>
        var msg = '<?php echo xlt("Still Loading...") ?>';
        var canvas = document.getElementById("dwvimg");
        var ctx = canvas.getContext("2d");
        ctx.font = "22px Arial";
        ctx.fillStyle = "red";
        $(window).on("load", function () {
            ctx.fillText(msg, 10, 100);
            $('.loader').toggle();
        });
    </script>
</body>
</html>
