<?php

/**
 * Clinical Notes form save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\ClinicalNotesService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (!$encounter) { // comes from globals.php
    die(xlt("Internal error: we do not seem to be in an encounter!"));
}

// TODO: This should all be rolled into a transaction

$form_id = (int) (isset($_GET['id']) ? $_GET['id'] : '');
$code = $_POST["code"];
$code_text = $_POST["codetext"];
$code_date = $_POST["code_date"];
$code_des = $_POST["description"];
$ids = $_POST['id'] ?? [];
$count = $_POST["count"];
$clinical_notes_type = $_POST['clinical_notes_type'];
$clinical_notes_category = $_POST['clinical_notes_category'];
$note_relations = "";

$clinicalNotesService = new ClinicalNotesService();

if (!empty($form_id)) {
    $existingIds  = $clinicalNotesService->getClinicalNoteIdsForPatientForm($form_id, $_SESSION['pid'], $_SESSION['encounter']);

    // in order to find the ids that are unique we have to operate on the same type system, we'll convert everything into
    // an integer
    // the database BIGINT(20).  Its very, very unlikely we will run into overflow problems here.
    $existingIdInts = array_map('intval', $existingIds);
    $submittedIdInts = array_map('intval', array_filter($ids, 'is_numeric'));

    // now grab all of the ids that exist that were not submitted so we can mark them as inactive.  This does a
    // mathmatical set substraction.  We don't really delete the records as we need an audit trail here.
    $recordsIdsToDelete = array_diff($existingIdInts, $submittedIdInts);
    foreach ($recordsIdsToDelete as $recordId) {
        $clinicalNotesService->setActivityForClinicalRecord(
            $recordId,
            $_SESSION['pid'],
            $_SESSION['encounter'],
            ClinicalNotesService::ACTIVITY_INACTIVE
        );
    }
} else {
    $form_id = $clinicalNotesService->createClinicalNotesParentForm($_SESSION['pid'], $_SESSION['encounter'], $userauthorized);
}

// create our records let the underlying service fix everything
$note_records = [];

$count = array_filter($count);
if (!empty($count)) {
    foreach ($count as $key => $codeval) {
        $record = [];
        $record['id'] = $ids[$key] ?? null; // new records we don't set an id
        $record['form_id'] = $form_id;
        $record['code'] = $code[$key] ?: '';
        $record['codetext'] = $code_text[$key] ?: null;
        $record['description'] = $code_des[$key] ?: null;
        $record['clinical_notes_type'] = $clinical_notes_type[$key] ?: null;
        $record['clinical_notes_category'] = $clinical_notes_category[$key] ?: null;
        if (empty($record['id'])) {
            // we only populate this on an insert as we don't want someone tampering with the user that created this
            // record, this avoids issues where the record can be impersonated by someone else (IE falsifying who entered
            // the note).
            $record['user'] = $_SESSION["authUser"];
        }
        // this is for related issues to the note
        $record['note_related_to'] = parse_note($record['description']);
        //$record['note_related_to'] = $record['description'];
        // note this is the form_id from the forms table and is NOT a unique record id

        $record['pid'] = $_SESSION['pid'];
        $record['encounter'] = $_SESSION['encounter'];
        $record['authorized'] = $userauthorized;
        $record['date'] = $code_date[$key];
        $record['groupname'] = $_SESSION["authProvider"];
        $record['activity'] = ClinicalNotesService::ACTIVITY_ACTIVE;
        $clinicalNotesService->saveArray($record);
    }
}

formHeader("Redirecting....");
formJump();
formFooter();
function parse_note($note)
{
    $result = preg_match_all("/\{\|([^\]]*)\|}/", $note, $matches);
    return json_encode($matches[1]);
}
