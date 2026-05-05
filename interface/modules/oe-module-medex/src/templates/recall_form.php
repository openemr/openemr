<?php
/**
 * Add / Edit Recall Template
 *
 * Variables available from DisplayService::display_add_recall():
 *   $recall     array  Existing recall row (empty for new)
 *   $patient    array  Patient row (empty when pid='new')
 *   $providers  array  Rows: id, fname, lname
 *   $facilities array  Rows: id, name
 *   $pid        string Patient ID or 'new'
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   Proprietary - All Rights Reserved
 */

use OpenEMR\Common\Csrf\CsrfUtils;

$isNew      = ($pid === 'new');
$patName    = $isNew ? '' : (($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? ''));
$recallDate = $recall['r_eventDate'] ?? '';   // YYYY-MM-DD for type="date"
$reason     = $recall['r_reason']    ?? '';
$curProv    = $recall['r_provider']  ?? '';
$curFac     = $recall['r_facility']  ?? '';
$rID        = $recall['r_ID']        ?? '';
?>
<div class="container-fluid recall-form-wrap py-2">
    <h5 class="mb-3">
        <?php echo $isNew ? xlt('Add Recall') : xlt('Edit Recall'); ?>
        <?php if (!$isNew): ?>
            <small class="text-muted ml-2"><?php echo text($patName); ?></small>
        <?php endif; ?>
    </h5>

    <form id="addRecallForm" method="post"
          action="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/save.php">
        <?php echo CsrfUtils::collectCsrfToken(); ?>
        <input type="hidden" name="action"  value="addRecall" />
        <input type="hidden" name="new_pid" value="<?php echo attr($pid === 'new' ? '' : $pid); ?>" id="recall_pid" />
        <input type="hidden" name="r_ID"    value="<?php echo attr($rID); ?>" />

        <div class="row">
            <?php if ($isNew): ?>
            <!-- Patient search (new recall — no pid yet) -->
            <div class="col-md-6 form-group">
                <label for="rcl_patient_search"><?php echo xlt('Patient'); ?></label>
                <input type="text" id="rcl_patient_search" class="form-control"
                       placeholder="<?php echo xla('Search by name or ID'); ?>" autocomplete="off" />
                <input type="hidden" id="rcl_patient_name" name="new_patient_name" value="" />
                <small class="form-text text-muted"><?php echo xlt('Type to search, then click to select'); ?></small>
            </div>
            <?php else: ?>
            <div class="col-md-6 form-group">
                <label><?php echo xlt('Patient'); ?></label>
                <p class="form-control-plaintext font-weight-bold"><?php echo text($patName); ?></p>
            </div>
            <?php endif; ?>

            <div class="col-md-6 form-group">
                <label for="form_recall_date"><?php echo xlt('Recall Date'); ?></label>
                <input type="date" id="form_recall_date" name="form_recall_date"
                       class="form-control"
                       value="<?php echo attr($recallDate); ?>"
                       required />
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 form-group">
                <label for="new_reason"><?php echo xlt('Reason / Note'); ?></label>
                <input type="text" id="new_reason" name="new_reason"
                       class="form-control"
                       value="<?php echo attr($reason); ?>"
                       maxlength="255"
                       placeholder="<?php echo xla('e.g. Annual exam, Follow-up, Glaucoma check'); ?>" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label for="new_provider"><?php echo xlt('Provider'); ?></label>
                <select id="new_provider" name="new_provider" class="form-control select2">
                    <option value=""><?php echo xlt('-- Select Provider --'); ?></option>
                    <?php foreach ($providers as $prov): ?>
                        <option value="<?php echo attr($prov['id']); ?>"
                            <?php echo ($prov['id'] == $curProv) ? 'selected' : ''; ?>>
                            <?php echo text($prov['lname'] . ', ' . $prov['fname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="new_facility"><?php echo xlt('Facility'); ?></label>
                <select id="new_facility" name="new_facility" class="form-control select2">
                    <option value=""><?php echo xlt('-- Select Facility --'); ?></option>
                    <?php foreach ($facilities as $fac): ?>
                        <option value="<?php echo attr($fac['id']); ?>"
                            <?php echo ($fac['id'] == $curFac) ? 'selected' : ''; ?>>
                            <?php echo text($fac['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (!$isNew && !empty($patient)): ?>
        <!-- Contact info update panel -->
        <div class="card mt-2 mb-3">
            <div class="card-header py-1">
                <a class="text-body" data-toggle="collapse" href="#rcl_contact_panel" role="button">
                    <i class="fas fa-address-card mr-1"></i><?php echo xlt('Update Contact Information'); ?>
                    <small class="text-muted">(<?php echo xlt('optional'); ?>)</small>
                </a>
            </div>
            <div id="rcl_contact_panel" class="collapse">
                <div class="card-body pb-2">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="new_phone_home"><?php echo xlt('Home Phone'); ?></label>
                            <input type="text" id="new_phone_home" name="new_phone_home"
                                   class="form-control form-control-sm"
                                   value="<?php echo attr($patient['phone_home'] ?? ''); ?>" />
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="new_phone_cell"><?php echo xlt('Cell / SMS'); ?></label>
                            <input type="text" id="new_phone_cell" name="new_phone_cell"
                                   class="form-control form-control-sm"
                                   value="<?php echo attr($patient['phone_cell'] ?? ''); ?>" />
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="new_email"><?php echo xlt('Email'); ?></label>
                            <input type="email" id="new_email" name="new_email"
                                   class="form-control form-control-sm"
                                   value="<?php echo attr($patient['email'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="new_address"><?php echo xlt('Street'); ?></label>
                            <input type="text" id="new_address" name="new_address"
                                   class="form-control form-control-sm"
                                   value="<?php echo attr($patient['street'] ?? ''); ?>" />
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="new_city"><?php echo xlt('City'); ?></label>
                            <input type="text" id="new_city" name="new_city"
                                   class="form-control form-control-sm"
                                   value="<?php echo attr($patient['city'] ?? ''); ?>" />
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="new_state"><?php echo xlt('State'); ?></label>
                            <input type="text" id="new_state" name="new_state"
                                   class="form-control form-control-sm"
                                   value="<?php echo attr($patient['state'] ?? ''); ?>" />
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="new_postal_code"><?php echo xlt('Postal Code'); ?></label>
                            <input type="text" id="new_postal_code" name="new_postal_code"
                                   class="form-control form-control-sm"
                                   value="<?php echo attr($patient['postal_code'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label><?php echo xlt('Allow Email'); ?></label>
                            <select name="new_email_allow" class="form-control form-control-sm">
                                <option value="YES" <?php echo (($patient['hipaa_allowemail'] ?? '') === 'YES') ? 'selected' : ''; ?>>
                                    <?php echo xlt('Yes'); ?>
                                </option>
                                <option value="NO"  <?php echo (($patient['hipaa_allowemail'] ?? '') !== 'YES') ? 'selected' : ''; ?>>
                                    <?php echo xlt('No'); ?>
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label><?php echo xlt('Allow Voice'); ?></label>
                            <select name="new_voice" class="form-control form-control-sm">
                                <option value="YES" <?php echo (($patient['hipaa_voice'] ?? '') === 'YES') ? 'selected' : ''; ?>>
                                    <?php echo xlt('Yes'); ?>
                                </option>
                                <option value="NO"  <?php echo (($patient['hipaa_voice'] ?? '') !== 'YES') ? 'selected' : ''; ?>>
                                    <?php echo xlt('No'); ?>
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label><?php echo xlt('Allow SMS'); ?></label>
                            <select name="new_allowsms" class="form-control form-control-sm">
                                <option value="YES" <?php echo (($patient['hipaa_allowsms'] ?? '') === 'YES') ? 'selected' : ''; ?>>
                                    <?php echo xlt('Yes'); ?>
                                </option>
                                <option value="NO"  <?php echo (($patient['hipaa_allowsms'] ?? '') !== 'YES') ? 'selected' : ''; ?>>
                                    <?php echo xlt('No'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i><?php echo xlt('Save Recall'); ?>
            </button>
            <a href="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/messages.php?go=Recalls"
               class="btn btn-secondary ml-2">
                <i class="fas fa-ban mr-1"></i><?php echo xlt('Cancel'); ?>
            </a>
            <?php if (!$isNew && !empty($rID)): ?>
            <button type="button" class="btn btn-danger ml-4" id="rcl_delete_btn">
                <i class="fas fa-trash mr-1"></i><?php echo xlt('Delete Recall'); ?>
            </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($isNew): ?>
<script>
// Patient search autocomplete for new recalls
(function () {
    var searchInput  = document.getElementById('rcl_patient_search');
    var pidInput     = document.getElementById('recall_pid');
    var nameInput    = document.getElementById('rcl_patient_name');
    var resultsBox   = null;
    var searchTimer  = null;

    function clearResults() {
        if (resultsBox) { resultsBox.remove(); resultsBox = null; }
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        var q = this.value.trim();
        if (q.length < 2) { clearResults(); return; }
        searchTimer = setTimeout(function () {
            fetch(<?php echo js_escape($GLOBALS['web_root']); ?> +
                '/interface/main/findpatient.php?query=' + encodeURIComponent(q) +
                '&ajax=1', { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    clearResults();
                    if (!data || !data.length) return;
                    resultsBox = document.createElement('ul');
                    resultsBox.className = 'list-group position-absolute w-100 shadow-sm';
                    resultsBox.style.cssText = 'z-index:9999;max-height:200px;overflow-y:auto';
                    data.forEach(function (p) {
                        var li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action py-1 px-2';
                        li.textContent = p.name + ' (ID: ' + p.pid + ')';
                        li.setAttribute('data-pid', p.pid);
                        li.addEventListener('click', function () {
                            searchInput.value = p.name;
                            pidInput.value    = p.pid;
                            nameInput.value   = p.name;
                            clearResults();
                        });
                        resultsBox.appendChild(li);
                    });
                    searchInput.parentNode.style.position = 'relative';
                    searchInput.parentNode.appendChild(resultsBox);
                })
                .catch(function () { clearResults(); });
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (e.target !== searchInput) clearResults();
    });
})();
</script>
<?php endif; ?>

<?php if (!$isNew && !empty($rID)): ?>
<script>
document.getElementById('rcl_delete_btn').addEventListener('click', function () {
    if (!confirm(<?php echo xlj('Delete this recall? This cannot be undone.'); ?>)) return;
    var f = document.getElementById('addRecallForm');
    var inp = document.createElement('input');
    inp.type  = 'hidden';
    inp.name  = 'action';
    inp.value = 'deleteRecall';
    f.appendChild(inp);
    f.submit();
});
</script>
<?php endif; ?>
