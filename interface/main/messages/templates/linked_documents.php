<?php

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;

// Get the related document IDs if any.
$linkedDocsSql = "SELECT id1 FROM gprelations WHERE " .
    "type1 = ? AND type2 = ? AND id2 = ?";
$tmp = QueryUtils::fetchRecords($linkedDocsSql, ['1','6',$noteid]);
if (empty($tmp)) {
    return;
}

//$enc_list = [];
if (!empty($prow)) {
    $enc_list = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe " .
        " LEFT JOIN openemr_postcalendar_categories ON fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? ORDER BY fe.date DESC", array($prow['pid']));
}

// if we have phimail enabled, we are going to find out if our document is an xml file, if so, we are going to do a validation
// report on the document.  This would be a great thing for caching if we wanted to do that, we'll just compute on the fly for now.
$records = [];
$prow = $prow ?? null;
$cdaDocumentValidator = new \OpenEMR\Services\Cda\CdaValidateDocumentObject();
foreach ($tmp as $record) {
    $d = new Document($record['id1']);
    $docInformation = [
        'documentId' => $d->get_id(),
        'title' => $d->get_name() . "-" . $d->get_id(),
        'isCda' => false,
        'validationErrors' => [],
        'hasPatient' => false
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
        $docInformation['validationErrors'] = $cdaDocumentValidator->getValidationErrorsForDocument($d);
    }
    $records[] = $docInformation;
}

foreach ($records as $record) : ?>
<div class="row">
    <div class="col-12">
    <span class='font-weight-bold'><?php echo xlt('Linked document'); ?>:</span>
    <?php if ($record['hasPatient']) : ?>
        <a href='javascript:void(0);' onClick="previewDocument(" . <?php echo attr_js($record['documentId']); ?>");>
        <?php echo text($record['title']); ?>
        </a>
    <?php else : ?>
        <a href='javascript:void(0);' onClick="gotoReport(<?php echo attr_js($record['documentId']); ?>, '<?php echo attr_js($record['pname']); ?>', '<?php echo attr_js($record['pid']); ?>','<?php echo attr_js($record['pubpid'] ?? $record['pid']); ?>','<?php echo attr_js($record['DOB']); ?>');">
            <?php echo text($record['title']); ?>
        </a>
    <?php endif; ?>
    <?php if ($record['isCda']) : ?>
        <details>
            <summary><?php echo xlt("Validation report"); ?></summary>
            <pre>
            <?php if (!empty($record['validationErrors']['errorCount'])) : ?>
                <?php var_dump($record['validationErrors']); ?>
            <?php else : ?>
                <?php echo xlt("No errors found, Document(s) passed Import Validation"); ?>
            <?php endif; ?>
            </pre>
        </details>
    <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<script>
    // this originaly came from the messages class... but it makes no sense to have it there when we only use it here.
    function gotoReport(doc_id, pname, pid, pubpid, str_dob) {
        EncounterDateArray = [];
        CalendarCategoryArray = [];
        EncounterIdArray = [];
        Count = 0;
        <?php
        if (isset($enc_list) && sqlNumRows($enc_list) > 0) {
            while ($row = sqlFetchArray($enc_list)) {
                ?>
        EncounterIdArray[Count] = '<?php echo attr($row['encounter']); ?>';
        EncounterDateArray[Count] = '<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($row['date'])))); ?>';
        CalendarCategoryArray[Count] = '<?php echo attr(xl_appt_category($row['pc_catname'])); ?>';
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
