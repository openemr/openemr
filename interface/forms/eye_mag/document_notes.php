<?php

/** Document-linked clinical notes with Eye Form My Phrases support. */

require_once(__DIR__ . '/../../globals.php');
$srcdir = \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir();
require_once($srcdir . '/pnotes.inc.php');
require_once($srcdir . '/patient.inc.php');
require_once($srcdir . '/gprelations.inc.php');

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Forms\EyeMag\EyeFormData;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();
$posted = $request->request->all();
$input = array_replace($request->query->all(), $posted);
$pid = PatientSessionUtil::getPid();
$docid = EyeFormData::integer($input['docid'] ?? 0);
$document = $docid > 0 ? QueryUtils::querySingleRow('SELECT id,foreign_id,url FROM documents WHERE id=?', [$docid]) : false;
if ($document === false || $document === []) {
    http_response_code(404);
    die(xlt('Document not found'));
}

$patientId = EyeFormData::integer($document['foreign_id']);
if ($patientId !== $pid) {
    AccessDeniedHelper::deny('Document does not belong to the active patient');
}
if (!AclMain::aclCheckCore('patients', 'notes')) {
    AccessDeniedHelper::deny('Not authorized to view patient notes');
}
$patient = EyeFormData::textRow(EyeFormData::row(getPatientData($patientId, 'fname,lname,DOB,pronoun,squad')));
if (($patient['squad'] ?? '') !== '' && !AclMain::aclCheckCore('squads', $patient['squad'])) {
    AccessDeniedHelper::deny('Not authorized for patient squad');
}

$canAdd = AclMain::aclCheckCore('patients', 'notes', '', ['write', 'addonly']);
if ($request->isMethod('POST')) {
    if (!$canAdd) {
        AccessDeniedHelper::deny('Not authorized to add patient notes');
    }
    if (!CsrfUtils::verifyCsrfToken(EyeFormData::text($posted['csrf_token_form'] ?? ''), session: $session)) {
        CsrfUtils::csrfNotVerified();
    }
    $body = trim(EyeFormData::text($posted['note'] ?? ''));
    if ($body !== '') {
        $noteId = addPnote($patientId, $body, EyeFormData::integer($session->get('userauthorized')), 1, 'Clinical', EyeFormData::text($session->get('authUser')));
        setGpRelation(1, $docid, 6, $noteId);
    }
    header('Location: document_notes.php?docid=' . urlencode((string) $docid));
    exit;
}

$notesResult = getPnotesByDate(
    '',
    'all',
    'id,date,body,user,title',
    $patientId,
    50,
    0,
    '',
    $docid
);
$notes = is_array($notesResult) ? $notesResult : [];

$authUserId = EyeFormData::integer($session->get('authUserID'));
$phraseRows = QueryUtils::fetchRecords(
    "SELECT phrase_key,name,content,owner_uid
     FROM form_eye_dotphrases
     WHERE content_type='text' AND (owner_uid=? OR (visibility='shared' AND active=1))
     ORDER BY CASE WHEN owner_uid=? THEN 1 ELSE 0 END,name,id",
    [$authUserId, $authUserId]
);
$phrases = [];
foreach ($phraseRows as $phrase) {
    $phrases[strtolower(EyeFormData::text($phrase['phrase_key']))] = [
        'name' => EyeFormData::text($phrase['name']),
        'content' => EyeFormData::text($phrase['content']),
    ];
}

$provider = EyeFormData::textRow(EyeFormData::row(QueryUtils::querySingleRow('SELECT fname,lname FROM users WHERE id=?', [$authUserId])));
$age = '';
if (($patient['DOB'] ?? '') !== '' && $patient['DOB'] !== '0000-00-00') {
    try {
        $age = (string) (new DateTime($patient['DOB']))->diff(new DateTime())->y;
    } catch (DateMalformedStringException) {
        $age = '';
    }
}
$substitutions = [
    'PATIENT_NAME' => trim(($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '')),
    'FIRST_NAME' => $patient['fname'] ?? '',
    'LAST_NAME' => $patient['lname'] ?? '',
    'DOB' => ($patient['DOB'] ?? '') !== '' && $patient['DOB'] !== '0000-00-00' ? oeFormatShortDate($patient['DOB']) : '',
    'AGE' => $age,
    'PRONOUN' => $patient['pronoun'] ?? '',
    'PROVIDER' => trim(($provider['fname'] ?? '') . ' ' . ($provider['lname'] ?? '')),
    'VISIT_DATE' => oeFormatShortDate(date('Y-m-d')),
];
?>
<!doctype html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Document Notes'); ?></title>
</head>
<body class="body_top">
<main class="container-fluid p-3">
    <h3><?php echo xlt('Notes for'); ?> <?php echo text(basename(EyeFormData::text($document['url']))); ?></h3>

    <?php if ($canAdd) : ?>
    <form method="post" id="document-note-form" class="card mb-3">
        <div class="card-body">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
            <div class="form-row align-items-end mb-2">
                <div class="col-md-8">
                    <label for="my-phrase" class="font-weight-bold"><?php echo xlt('My Phrases'); ?></label>
                    <select id="my-phrase" class="form-control">
                        <option value=""><?php echo xlt('Select a phrase to insert'); ?></option>
                        <?php foreach ($phrases as $key => $phrase) : ?>
                            <option value="<?php echo attr($key); ?>"><?php echo text($key . ' — ' . $phrase['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" id="insert-phrase" class="btn btn-secondary"><?php echo xlt('Insert'); ?></button>
                </div>
            </div>
            <label for="note" class="font-weight-bold"><?php echo xlt('New Note'); ?></label>
            <textarea id="note" name="note" class="form-control" rows="10" required></textarea>
            <button type="submit" class="btn btn-primary mt-2"><?php echo xlt('Save Note'); ?></button>
        </div>
    </form>
    <?php endif; ?>

    <section class="card">
        <div class="card-header font-weight-bold"><?php echo xlt('Linked Notes'); ?></div>
        <div class="card-body">
            <?php if ($notes === []) : ?>
                <p class="text-muted mb-0"><?php echo xlt('No notes have been added to this document.'); ?></p>
            <?php else : ?>
                <?php foreach ($notes as $noteValue) :
                    $note = EyeFormData::textRow(EyeFormData::row($noteValue)); ?>
                    <article class="border-bottom mb-3 pb-2">
                        <div class="font-weight-bold"><?php echo text($note['user']); ?> · <?php echo text($note['date']); ?></div>
                        <div><?php echo nl2br(text($note['body'])); ?></div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<script>
(function () {
    var phrases = <?php echo json_encode($phrases, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var substitutions = <?php echo json_encode($substitutions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var select = document.getElementById('my-phrase');
    var textarea = document.getElementById('note');
    var button = document.getElementById('insert-phrase');
    if (!select || !textarea || !button) return;
    function expand(value) {
        return String(value || '').replace(/\{\{([A-Z_]+)\}\}/g, function (match, key) {
            return Object.prototype.hasOwnProperty.call(substitutions, key) ? substitutions[key] : '';
        });
    }
    button.addEventListener('click', function () {
        var phrase = phrases[select.value];
        if (!phrase) return;
        var text = expand(phrase.content);
        var start = textarea.selectionStart || 0;
        var end = textarea.selectionEnd || start;
        textarea.value = textarea.value.slice(0, start) + text + textarea.value.slice(end);
        textarea.focus();
        textarea.setSelectionRange(start + text.length, start + text.length);
    });
    select.addEventListener('dblclick', function () { button.click(); });
    document.getElementById('document-note-form').addEventListener('submit', function () {
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    });
})();
</script>
</body>
</html>
