<?php

/**
 * linked_documents.php is a sub section template for the messages.php script.  It is primarily used for displaying attached
 * documents (usually received from the phimail-server process. It handles the preview and display of documents as well
 * as validation of CDA types of documents if direct mail is turned on in the globals.   validation is an async process
 * that will hit the server and validate the xsd and schematron of the CDA.  The errors are then populated and displayed
 * on the screen with associated error counts being displayed if they are available.  Each document is handled separately
 * as an async validation process.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\Cda\CdaValidateDocumentObject;

if (empty($noteid)) {
    $twig = new TwigContainer(null, $GLOBALS['kernel']);
    echo $twig->render('core/unauthorized.html.twig', ['pageTitle' => xl("Linked Documents")]);
    exit;
}

// Get the related document IDs if any.
$linkedDocsSql = "SELECT id1 FROM gprelations WHERE " .
    "type1 = ? AND type2 = ? AND id2 = ?";
$tmp = QueryUtils::fetchRecords($linkedDocsSql, ['1','6',$noteid]);
if (empty($tmp)) {
    return;
}

$enc_list = [];
if (!empty($prow)) {
    $results = QueryUtils::fetchRecords("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe " .
        " LEFT JOIN openemr_postcalendar_categories ON fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? ORDER BY fe.date DESC", array($prow['pid']));
    foreach ($enc_list as $row) {
        $enc_list[] = [
            'encounter' => $row['encounter'],
            'pc_catname' => xl_appt_category($row['pc_catname']),
            'date' => oeFormatShortDate(date("Y-m-d", strtotime($row['date'])))
        ];
    }
}

// if we have phimail enabled, we are going to find out if our document is an xml file, if so, we are going to do a validation
// report on the document.  This would be a great thing for caching if we wanted to do that, we'll just compute on the fly for now.
$records = [];
$prow = $prow ?? null;
$cdaDocumentValidator = new CdaValidateDocumentObject();
foreach ($tmp as $record) {
    $d = new Document($record['id1']);
    $docInformation = [
        'documentId' => $d->get_id(),
        'title' => $d->get_name() . "-" . $d->get_id(),
        'isCda' => false,
        'hasPatient' => false,
        'requiresValidation' => false
    ];
    if (!empty($prow)) {
        $docInformation['hasPatient'] = true;
        $docInformation['pname'] = $prow['fname'] . " " . $prow['lname'];
        $docInformation['pid'] = $prow['pid'];
        $docInformation['pubpid'] = $prow['pubpid'];
        $docInformation['DOB'] = $prow['DOB'];
    }
    if ($cdaDocumentValidator->isCdaDocument($d)) {
        $docInformation['isCda'] = true;
        $docInformation['requiresValidation'] = true;
    }
    $records[] = $docInformation;
}

try {
    foreach ($records as $record) : ?>
<div class="row mt-2 mb-2 messages-document-row <?php echo $record['requiresValidation'] ? "messages-document-validate" : ""; ?>"
     data-doc="<?php echo attr($record['documentId']); ?>">
    <div class="col-12">
    <span class='font-weight-bold'><?php echo xlt('Linked document'); ?>:</span>
        <?php if (!$record['hasPatient']) : ?>
        <a class='messages-document-link<?php echo $record['requiresValidation'] ? " d-none" : ""; ?>'
           href='javascript:void(0);' onClick='previewDocument(<?php echo attr_js($record['documentId']); ?>);'

        >
            <?php echo text($record['title']); ?>
        </a>
        <?php else : ?>
            <a class='messages-document-link <?php echo $record['requiresValidation'] ? "d-none" : ""; ?>'
               href='javascript:void(0);'
               onClick="gotoReport(<?php echo attr_js($record['documentId']); ?>, <?php echo attr_js($record['pname']); ?>, <?php echo attr_js($record['pid']); ?>,<?php echo attr_js($record['pubpid'] ?? $record['pid']); ?>,<?php echo attr_js($record['DOB']); ?>);">
                <?php echo text($record['title']); ?>
            </a>
        <?php endif; ?>
        <?php if ($record['requiresValidation']) : ?>
            <span class="validation-line">
                <?php echo text($record['title']); ?>
                <span class="text-info"><?php echo xlt("Validating document for errors"); ?>...</span>
                <span class="spinner-border-sm spinner-border" role="status">
                    <span class="sr-only"><?php echo xlt("Validating document for errors"); ?>...</span>
                </span>
            </span>
            <span class="validation-failed text-danger d-none">
                <?php echo text($record['title']); ?> -
                <?php echo xlt("Failed to retrieve validation results from server"); ?>
            </span>
        <span class="validation-totals validation-totals-success badge badge-pill badge-success p-2 d-none">
            <?php echo xlt("Errors"); ?> <span class="validation-totals-count">0</span>
        </span>
            <span class="validation-totals validation-totals-failed badge badge-pill badge-danger p-2 d-none">
            <?php echo xlt("Errors"); ?> <span class="validation-totals-count">0</span>
        </span>
        <?php endif; ?>
        <?php if ($record['isCda']) : ?>
        <details class="validation-report-errors d-none">
            <summary><?php echo xlt("Validation report"); ?></summary>
            <div class="validation-report-errors-container">
            </div>
            <a href="javascript:void(0)" onClick='previewCCDADocument(event,<?php echo attr_js($record['documentId']);?>);'
                ><?php echo xlt("View Processed Document"); ?></a>
        </details>
    <?php endif; ?>
    </div>
</div>
    <?php endforeach;
} catch (Exception $exception) {
    // if twig throws any exceptions we want to log it.
    (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
}
?>

<script>

    function previewCCDADocument(event, documentId) {
        event.preventDefault();
        let url = "<?php echo $GLOBALS['webroot']; ?>" + "/interface/modules/zend_modules/public/encountermanager/previewDocument?docId=" + documentId;
        try {
            window.open(url);
        }
        catch (error)
        {
            console.error(error);
            alert(window.xl("Failed to preview document"));
        }
    }
    function startDocumentValidation() {
        top.restoreSession();
        // document validation can take a long time especially if it is happening externally...
        // we are going to do an async process to go out and validate the document before we allow the document to be
        // loaded.
        let records = document.querySelectorAll(".messages-document-validate");
        records.forEach(function(validateRecord) {

            // now we need to make an ajax async request to the server with the document id
            let docId = validateRecord.dataset['doc'];
            let url = "<?php echo $GLOBALS['webroot'] . "/library/ajax/messages/validate_messages_document_ajax.php?csrf=\" + " . js_url(CsrfUtils::collectCsrfToken()); ?>

            window.fetch(url + "&doc=" + encodeURIComponent(docId) )
                .then(function(result) {
                    if (!result.ok) {
                        throw new Error("Failed to get valid response from server");
                    }
                    return result.text();
                })
                .then(function (resultHtml) {
                    validateRecord.querySelector(".messages-document-link").classList.remove("d-none");
                    validateRecord.querySelector(".validation-report-errors-container").innerHTML = resultHtml;
                    validateRecord.querySelector(".validation-report-errors").classList.remove("d-none");
                    validateRecord.querySelector(".validation-line").classList.add("d-none");

                    // cda-validate-results comes from the html, still need to double check in case its not there
                    let node = validateRecord.querySelector(".cda-validate-results[data-error-count]");
                    if (!node) {
                        console.error("Failed to find node with .cda-validate-results[data-error-count] to populate error count");
                        return;
                    }
                    let count = +(node.dataset['errorCount'] || 0);
                    if (isNaN(count)) {
                        console.error("node with .cda-validate-results[data-error-count] had error count that was not a number");
                        return;
                    }

                    let totalsNode;
                    if (count > 0) {
                        totalsNode = validateRecord.querySelector(".validation-totals-failed > .validation-totals-count");
                    } else {
                        totalsNode = validateRecord.querySelector(".validation-totals-success > .validation-totals-count");
                    }
                    totalsNode.innerText = count;
                    totalsNode.parentNode.classList.remove("d-none");
                })
                .catch(function(error) {
                    console.error(error);
                    validateRecord.querySelector(".validation-line").classList.add("d-none");
                    validateRecord.querySelector(".validation-failed").classList.remove("d-none");
                });
        });
    }

    window.addEventListener("DOMContentLoaded", startDocumentValidation);

    // this originaly came from the messages class... but it makes no sense to have it there when we only use it here.
    function gotoReport(doc_id, pname, pid, pubpid, str_dob) {
        EncounterDateArray = [];
        CalendarCategoryArray = [];
        EncounterIdArray = [];
        Count = 0;
        <?php
        if (!empty($enc_list)) {
            foreach ($row as $enc_list) {
                ?>
        EncounterIdArray[Count] = '<?php echo attr($row['encounter']); ?>';
        EncounterDateArray[Count] = '<?php echo attr($row['date']); ?>';
        CalendarCategoryArray[Count] = '<?php echo attr($row['pc_catname']); ?>';
        Count++;
                <?php
            }
        }
        ?>
        top.restoreSession();
        $.ajax({
            type: 'get',
            url: '<?php echo $GLOBALS['webroot'] . "/library/ajax/set_pt.php";?>',
            data: {
                set_pid: pid,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            async: false
        });
        parent.left_nav.setPatient(pname, pid, pubpid, '', str_dob);
        parent.left_nav.setPatientEncounter(EncounterIdArray, EncounterDateArray, CalendarCategoryArray);
        var docurl = '../controller.php?document&view' + "&patient_id=" + encodeURIComponent(pid) + "&document_id=" + encodeURIComponent(doc_id) + "&";
        var paturl = 'patient_file/summary/demographics.php?pid=' + encodeURIComponent(pid);
        parent.left_nav.loadFrame('dem1', 'pat', paturl);
        parent.left_nav.loadFrame('doc0', 'enc', docurl);
        top.activateTabByName('enc', true);
    }


    /**
     * Given a document that we don't know what patient to attach the document to we need to look at a preview of
     * the document.  This loads up the document from the Miscellaneous -> New Documents page.  To access the page
     * the user must have the following ACLs:  "patients","docs","write","addonly"
     * @param doc_id The id of the document we want to preview in OpenEMR
     */
    function previewDocument(doc_id) {
        top.restoreSession();
        var docurl = '../controller.php?document&view' + "&patient_id=0&document_id=" + encodeURIComponent(doc_id) + "&";
        parent.left_nav.loadFrame('adm0', 'msc', docurl);
    }


</script>
