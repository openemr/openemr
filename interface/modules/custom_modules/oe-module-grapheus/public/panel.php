<?php

/**
 * The Grapheus tab inside an encounter: connect, record, review, apply.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once dirname(__FILE__, 5) . "/globals.php";
require_once dirname(__DIR__) . '/src/Compat.php';
(new \OpenEMR\Core\ModulesClassLoader(\Exetazo\Grapheus\Compat::fileroot()))->registerNamespaceIfNotExists('Exetazo\\Grapheus\\', dirname(__DIR__) . '/src');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('encounters', 'notes', '', 'write') && !AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) {
    echo xlt('Not authorized');
    exit;
}
$base = \Exetazo\Grapheus\Compat::moduleUrl();
?>
<!doctype html>
<html>
<head>
    <title><?php echo xlt('Grapheus'); ?></title>
    <?php Header::setupHeader(); ?>
    <link rel="stylesheet" href="<?php echo attr($base); ?>/assets/grapheus.css?v=1">
</head>
<body class="body_top">
<div id="grapheus" class="container-fluid py-2"
     data-api="<?php echo attr($base . '/api.php'); ?>"
     data-recorder="<?php echo attr($base . '/recorder.php'); ?>"
     data-csrf="<?php echo attr(\Exetazo\Grapheus\Compat::csrfToken()); ?>"
     data-encounter-url="<?php echo attr(\Exetazo\Grapheus\Compat::webroot() . '/interface/patient_file/encounter/forms.php'); ?>">
    <div class="d-flex align-items-center mb-2">
        <h4 class="mb-0 mr-2">Grapheus</h4><small class="text-muted"><?php echo xlt('AI scribe by Exetazo'); ?></small>
        <span class="ml-auto small" id="g-account"></span>
    </div>
    <div id="g-msg" class="alert d-none" role="status"></div>

    <section id="g-connect" class="d-none">
        <p><?php echo xlt('Connect your Grapheus account to record visits and have the note, problems, allergies, prescriptions and fee-sheet codes entered for your review.'); ?></p>
        <button class="btn btn-primary" id="g-connect-btn"><?php echo xlt('Connect Grapheus'); ?></button>
        <p class="small text-muted mt-2"><?php echo xlt('A Grapheus subscription is required. Prescriptions are entered but never sent; billing codes are entered but never finalized.'); ?></p>
    </section>

    <section id="g-home" class="d-none">
        <div class="card mb-3"><div class="card-body">
            <h5 class="card-title"><?php echo xlt('Record this visit'); ?> — <span id="g-patient"></span></h5>
            <div class="form-row">
                <div class="col-sm-3 mb-2"><label class="small mb-0"><?php echo xlt('Visit'); ?></label>
                    <select id="g-mode" class="form-control form-control-sm"><option value="in_person"><?php echo xlt('In person'); ?></option><option value="telehealth"><?php echo xlt('Telehealth (speaker on)'); ?></option></select></div>
                <div class="col-sm-3 mb-2"><label class="small mb-0"><?php echo xlt('Patient'); ?></label>
                    <select id="g-ptype" class="form-control form-control-sm"><option value="established"><?php echo xlt('Established'); ?></option><option value="new"><?php echo xlt('New'); ?></option></select></div>
                <div class="col-sm-3 mb-2"><label class="small mb-0"><?php echo xlt('Prep minutes'); ?></label>
                    <input id="g-prep" type="number" min="0" max="240" class="form-control form-control-sm" placeholder="0"></div>
            </div>
            <div class="form-check mb-2"><input type="checkbox" class="form-check-input" id="g-consent"><label class="form-check-label small" for="g-consent"><?php echo xlt('The patient knows this visit is being recorded to help write the note.'); ?></label></div>
            <button class="btn btn-danger" id="g-record"><?php echo xlt('Start recording'); ?></button>
            <span class="small text-muted ml-2"><?php echo xlt('Opens a small recorder window. Keep it open during the visit.'); ?></span>
        </div></div>
        <div class="card"><div class="card-body">
            <div class="d-flex"><h5 class="card-title"><?php echo xlt('Drafts'); ?></h5><button class="btn btn-sm btn-link ml-auto" id="g-refresh"><?php echo xlt('Refresh'); ?></button></div>
            <p class="small text-muted"><?php echo xlt('Recordings from this encounter, plus anything you recorded with the Grapheus extension or phone app in the last 48 hours.'); ?></p>
            <ul class="list-group" id="g-list"></ul>
            <button class="btn btn-sm btn-link px-0 mt-2" id="g-disconnect"><?php echo xlt('Disconnect Grapheus'); ?></button>
        </div></div>
    </section>

    <section id="g-review" class="d-none">
        <button class="btn btn-sm btn-link px-0" id="g-back">&larr; <?php echo xlt('Back'); ?></button>
        <h5 id="g-r-title"></h5>
        <div id="g-r-progress" class="alert alert-info d-none"></div>
        <div id="g-r-body" class="d-none">
            <p class="small text-muted"><?php echo xlt('Review and edit. Only checked items are added. Nothing already in the chart is changed.'); ?></p>
            <div class="card mb-3"><div class="card-body">
                <h6><?php echo xlt('SOAP note'); ?> <label class="small font-weight-normal ml-2"><input type="checkbox" id="g-add-note" checked> <?php echo xlt('Add'); ?></label></h6>
                <label class="small mb-0">S</label><textarea class="form-control mb-2" rows="7" id="g-s"></textarea>
                <label class="small mb-0">O</label><textarea class="form-control mb-2" rows="3" id="g-o"></textarea>
                <label class="small mb-0">A</label><textarea class="form-control mb-2" rows="4" id="g-a"></textarea>
                <label class="small mb-0">P</label><textarea class="form-control" rows="6" id="g-p"></textarea>
            </div></div>
            <div class="card mb-3"><div class="card-body">
                <h6><?php echo xlt('Time'); ?></h6>
                <div class="form-row align-items-end">
                    <div class="col-3"><div class="small text-muted"><?php echo xlt('Face-to-face'); ?></div><b id="g-t-face"></b></div>
                    <div class="col-3"><label class="small mb-0"><?php echo xlt('Prep'); ?></label><input id="g-t-prep" type="number" min="0" class="form-control form-control-sm"></div>
                    <div class="col-3"><label class="small mb-0"><?php echo xlt('Documentation'); ?></label><input id="g-t-doc" type="number" min="0" class="form-control form-control-sm"></div>
                    <div class="col-3"><div class="small text-muted"><?php echo xlt('Total'); ?></div><b id="g-t-total"></b></div>
                </div>
                <p class="small mt-2 mb-1" id="g-t-code"></p>
                <label class="small"><input type="checkbox" id="g-t-add"> <?php echo xlt('Add the time statement to the Plan'); ?></label>
            </div></div>
            <div class="card mb-3"><div class="card-body"><h6><?php echo xlt('Problem list'); ?></h6><div id="g-problems"></div></div></div>
            <div class="card mb-3"><div class="card-body"><h6><?php echo xlt('Allergies'); ?></h6><div id="g-allergies"></div></div></div>
            <div class="card mb-3"><div class="card-body"><h6><?php echo xlt('Prescriptions'); ?> <small class="text-muted"><?php echo xlt('entered for you to review and send; never transmitted'); ?></small></h6><div id="g-rx"></div></div></div>
            <div class="card mb-3"><div class="card-body"><h6><?php echo xlt('Fee sheet'); ?> <small class="text-muted"><?php echo xlt('entered unbilled; you or your biller finalize'); ?></small></h6><p class="small text-muted" id="g-basis"></p><div id="g-billing"></div></div></div>
            <div class="card mb-3 d-none" id="g-unclear-card"><div class="card-body"><h6><?php echo xlt('Check these'); ?></h6><ul class="small mb-0" id="g-unclear"></ul></div></div>
            <button class="btn btn-primary btn-lg" id="g-apply"><?php echo xlt('Add checked items to this encounter'); ?></button>
        </div>
    </section>
</div>
<script src="<?php echo attr($base); ?>/assets/grapheus-panel.js?v=1"></script>
</body>
</html>
