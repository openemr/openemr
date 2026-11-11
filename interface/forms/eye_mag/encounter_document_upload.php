<?php

/**
 * Upload an Eye Form study to the active patient's encounter.
 *
 * @package OpenEMR
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../../globals.php';
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/patient.inc.php';
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/sanitize.inc.php';

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\EyeFormData;
use Symfony\Component\HttpFoundation\File\UploadedFile;

$request = CurrentRequest::get();
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$pid = PatientSessionUtil::getPid();
$encounter = EyeFormData::identifier($request->query->get('encounter'));
$categoryId = EyeFormData::identifier($request->query->get('category'));
if ($pid <= 0 || $encounter <= 0 || $categoryId <= 0
    || !AclMain::aclCheckCore('patients', 'med')
    || !AclMain::aclCheckCore('patients', 'docs', '', ['write', 'addonly'])) {
    AccessDeniedHelper::deny('Not authorized to upload encounter documents');
}
$patient = EyeFormData::row(getPatientData($pid, 'squad'));
$squad = EyeFormData::text($patient['squad'] ?? '');
if ($squad !== '' && !AclMain::aclCheckCore('squads', $squad)) {
    AccessDeniedHelper::deny('Not authorized for patient squad');
}
$visit = QueryUtils::querySingleRow(
    "SELECT f.form_id FROM forms f JOIN form_encounter e ON e.pid=f.pid AND e.encounter=f.encounter
      WHERE f.pid=? AND f.encounter=? AND f.formdir='eye_mag' AND f.deleted=0 LIMIT 1",
    [$pid, $encounter]
);
$category = QueryUtils::querySingleRow(
    "SELECT name,aco_spec FROM categories WHERE id=? AND value IN ('EXT','ANTSEG','POSTSEG','RETINA','NEURO')",
    [$categoryId]
);
if (!$visit || !$category || !AclMain::aclCheckAcoSpec(EyeFormData::text($category['aco_spec'] ?? ''))) {
    AccessDeniedHelper::deny('Invalid encounter or document category');
}
$categoryName = EyeFormData::text($category['name'] ?? '');

$uploaded = [];
$error = '';
if ($request->isMethod('POST')) {
    if (!CsrfUtils::verifyCsrfToken(EyeFormData::text($request->request->get('csrf_token_form')), session: $session)) {
        CsrfUtils::csrfNotVerified();
    }
    $files = $request->files->all()['files'] ?? [];
    if (!is_array($files) || $files === []) {
        $error = xl('Choose at least one PDF or image.');
    } else {
        /** @var list<array{file: UploadedFile, mime: string}> $validatedFiles */
        $validatedFiles = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid() || !$file->getSize()
                || (OEGlobalsBag::getInstance()->getBoolean('secure_upload') && !isWhiteFile($file->getPathname()))) {
                $error = xl('One or more files could not be uploaded.');
                break;
            }
            $mime = mime_content_type($file->getPathname());
            if (!is_string($mime) || !in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'], true)) {
                $error = xl('Choose PDF, JPEG, PNG, or GIF files.');
                break;
            }
            $validatedFiles[] = ['file' => $file, 'mime' => $mime];
        }
        if ($error === '') {
            foreach ($validatedFiles as $validatedFile) {
                $uploadFile = $validatedFile['file'];
                $uploadMime = $validatedFile['mime'];
                $pathname = $uploadFile->getPathname();
                $bytes = file_get_contents($pathname);
                $name = basename(str_replace('\\', '/', (string) $uploadFile->getClientOriginalName()));
                if (!is_string($bytes) || $bytes === '' || $name === '') {
                    $error = xl('One or more files could not be uploaded.');
                    break;
                }
                try {
                    $document = new Document();
                    $result = $document->createDocument(
                        (string) $pid, $categoryId, $name, $uploadMime, $bytes,
                        '', 1, 0, $pathname, null, null, null, (string) $encounter
                    );
                    if ($result !== '') {
                        $error = xl('One or more files could not be uploaded.');
                        break;
                    }
                    $uploaded[] = $name;
                } catch (\Exception $exception) {
                    ServiceContainer::getLogger()->error(
                        'Eye Form encounter document upload failed',
                        ['exception' => $exception::class]
                    );
                    $error = xl('One or more files could not be uploaded.');
                    break;
                }
            }
        }
    }
}
?>
<!doctype html>
<html><head><?php Header::setupHeader(); ?><title><?php echo xlt('Upload encounter documents'); ?></title></head>
<body class="body_top"><main class="container-fluid p-3">
    <h3><?php echo xlt('Upload encounter documents'); ?> — <?php echo text($categoryName); ?></h3>
    <p><?php echo xlt('Files uploaded here are linked to this encounter and included in its Eye Form report.'); ?></p>
    <?php if ($error !== '') {
        ?><p class="text-danger" role="alert"><?php echo text($error); ?></p><?php } ?>
    <?php foreach ($uploaded as $name) {
        ?><p><?php echo xlt('Uploaded') . ': ' . text($name); ?></p><?php } ?>
    <form method="post" enctype="multipart/form-data" onsubmit="if (typeof top.restoreSession === 'function') top.restoreSession();">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
        <label for="eye-document-files"><?php echo xlt('PDF or image files'); ?></label>
        <input class="form-control-file mb-3" id="eye-document-files" type="file" name="files[]" accept=".pdf,.jpg,.jpeg,.png,.gif" multiple required>
        <button type="submit" class="btn btn-primary"><?php echo xlt('Upload'); ?></button>
    </form>
    <p class="mt-3"><a href="<?php echo attr(OEGlobalsBag::getInstance()->getWebRoot() . '/controller.php?document&upload&patient_id=' . $pid . '&parent_id=' . $categoryId); ?>"><?php echo xlt('Open Documents for other upload options'); ?></a></p>
</main></body></html>
