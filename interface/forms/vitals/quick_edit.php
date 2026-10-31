<?php
require_once("../../globals.php");

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Csrf\CsrfUtils;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$pid = $_GET['pid'] ?? null;
$encounter = $_GET['encounter'] ?? null;

if (!$pid || !$encounter) {
    die("Missing patient or encounter context.");
}
?>
<div class="p-3">
    <h4 class="mb-3">Quick Vitals Entry</h4>
    <form id="quickVitalsForm">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">
        <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">
        <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">

        <div class="row">
            <div class="col-6 mb-2">
                <label>Weight (lbs)</label>
                <input type="text" name="weight" class="form-control">
            </div>
            <div class="col-6 mb-2">
                <label>Height (in)</label>
                <input type="text" name="height" class="form-control">
            </div>
            <div class="col-6 mb-2">
                <label>Pulse</label>
                <input type="text" name="pulse" class="form-control">
            </div>
            <div class="col-6 mb-2">
                <label>BP (Systolic/Diastolic)</label>
                <div class="input-group">
                    <input type="text" name="bps" placeholder="Sys" class="form-control">
                    <input type="text" name="bpd" placeholder="Dia" class="form-control">
                </div>
            </div>
        </div>

        <div class="mt-4 text-right">
            <button type="button" class="btn btn-secondary btn-cancel-vitals">Cancel</button>
            <button type="button" id="saveQuickVitals" class="btn btn-primary">Save Vitals</button>
        </div>
    </form>
</div>