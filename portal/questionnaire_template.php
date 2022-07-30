<?php

/**
 * Questionnaire Template
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../src/Common/Forms/CoreFormToPortalUtility.php");

use OpenEMR\Common\Forms\CoreFormToPortalUtility;

// block of code to securely support use by the patient portal
$patientPortalSession = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($patientPortalSession) {
    $ignoreAuth_onsite_portal = true;
}

require_once(__DIR__ . "/../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\QuestionnaireService;

$q = $_REQUEST['qId'] ?? null;
$q_type = $_REQUEST['type'] ?? null;
$q_url = $_REQUEST['url'] ?? null;
$q_name = $_REQUEST['name'] ?? null;
$q_form_code = $_REQUEST['form_code'] ?? null;

if (!empty($q) && empty($url)) {
    $templateService = new QuestionnaireService();
    $resource = $templateService->fetchQuestionnaireResource($q, $q);
    $q_json = $resource['questionnaire'];
}
?>
<head>
    <title><?php echo xlt('Portal'); ?> | <?php echo xlt('Questionnaire'); ?></title>
    <?php Header::setupHeader([]); ?>
    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/lforms/webcomponent/styles.css" media="screen" rel="stylesheet" />
    <script>
        var fhirQ = <?php echo js_escape($q_json); ?>;
        function saveQR() {
            let qr = LForms.Util.getFormFHIRData('QuestionnaireResponse', 'R4');
            let formElement = document.getElementById("formContainer");
            let data = LForms.Util.getUserData(formElement, true, true, true);
            window.alert(JSON.stringify(data, null, 2));
            window.alert(JSON.stringify(qr, null, 2));
        }
        <?php if ($q_type != 'loinc_form') { ?>
        window.onload = function () {
            LForms.Util.addFormToPage(fhirQ, 'formContainer');
        }
        <?php } ?>
        <?php if ($q_type == 'loinc_form') { ?>
        let url = <?php echo js_escape($q_url); ?> +  '?loinc_num=' + encodeURIComponent(<?php echo js_escape($q_form_code); ?>);
        fetch(url).then((response) => {
            return response.json()
        }).then((data) => {
            LForms.Util.addFormToPage(data, 'formContainer');
        })
        <?php } ?>
    </script>
</head>
<body>
    <div class="container-xl mt-2">
        <div>
            <button class="btn btn-sm btn-primary btn-save" onclick="saveQR()"><?php echo xlt("Save FHIR QuestionnaireResponse"); ?></button>
        </div>
        <div id=formContainer></div>
        <div>
            <button class="btn btn-sm btn-primary btn-save" onclick="saveQR()"><?php echo xlt("Save FHIR QuestionnaireResponse"); ?></button>
        </div>
    </div>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/lforms/webcomponent/assets/lib/zone.min.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/lforms/webcomponent/scripts.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/lforms/webcomponent/runtime-es2015.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/lforms/webcomponent/polyfills-es2015.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/lforms/webcomponent/main-es2015.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/lforms/fhir/R4/lformsFHIR.min.js"></script>
</body>
