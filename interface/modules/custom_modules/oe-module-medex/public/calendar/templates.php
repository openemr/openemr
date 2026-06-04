<?php
/**
 * PROPRIETARY AND CONFIDENTIAL
 * Copyright (c) 2024-2026 MedEx <support@MedExBank.com>
 * All Rights Reserved.
 *
 * This file is part of the MedEx SaaS platform and is NOT open-source software.
 * Unauthorized copying, distribution, modification, or use of this file, via any
 * medium, is strictly prohibited without the express written permission of MedEx.
 *
 * @package   MedEx
 * @copyright Copyright (c) 2024-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once(__DIR__ . "/../../../../../globals.php");
require_once(__DIR__ . "/../../src/Services/TemplateService.php");
require_once(__DIR__ . "/../../src/Services/CalendarService.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\Services\{TemplateService, CalendarService};

if (!AclMain::aclCheckCore('patients', 'appt', '', 'write')) {
    die('Access denied');
}

$templateService = new TemplateService();
$calendarService = new CalendarService();
$providers = $calendarService->getProviders();
$selectedProvider = $_GET['provider_id'] ?? $_SESSION['authUserID'];
$templates = $templateService->getTemplates((int)$selectedProvider);

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Schedule Templates'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .templates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .template-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .template-card h3 { margin-top: 0; color: #333; }
        .template-info { font-size: 14px; color: #666; margin: 5px 0; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; max-width: 600px; margin: 50px auto; padding: 30px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>📋 Schedule Templates</h1>
        <select id="provider-select" onchange="changeProvider(this.value)">
            <?php foreach ($providers as $provider): ?>
                <option value="<?php echo attr($provider['id']); ?>"
                    <?php echo ($provider['id'] == $selectedProvider) ? 'selected' : ''; ?>>
                    <?php echo text($provider['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" onclick="showCreateModal()">+ Create Template</button>
    </div>

    <div class="templates-grid">
        <?php foreach ($templates as $template): ?>
            <div class="template-card">
                <h3><?php echo text($template['template_name']); ?></h3>
                <div class="template-info">
                    <strong>Day:</strong> <?php echo ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$template['day_of_week']]; ?>
                </div>
                <div class="template-info">
                    <strong>Time:</strong> <?php echo text($template['start_time'] . ' - ' . $template['end_time']); ?>
                </div>
                <div class="template-info">
                    <strong>Category:</strong> <?php echo text($template['category_name'] ?? 'Any'); ?>
                </div>
                <div class="template-info">
                    <strong>Slot Duration:</strong> <?php echo text($template['slot_duration']); ?> min
                </div>
                <button class="btn btn-success" onclick="applyTemplate(<?php echo $template['template_id']; ?>)">
                    ⚡ Apply Template
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="applyModal" class="modal">
    <div class="modal-content">
        <h2>Apply Template</h2>
        <div class="form-group">
            <label>Start Date:</label>
            <input type="date" id="apply-start-date" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label>End Date:</label>
            <input type="date" id="apply-end-date" value="<?php echo date('Y-m-d', strtotime('+4 weeks')); ?>">
        </div>
        <button class="btn btn-success" onclick="confirmApply()">Apply</button>
        <button class="btn" onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
let currentTemplateId = null;

function changeProvider(id) {
    window.location.href = '?provider_id=' + id;
}

function applyTemplate(templateId) {
    currentTemplateId = templateId;
    document.getElementById('applyModal').style.display = 'block';
}

function confirmApply() {
    const startDate = document.getElementById('apply-start-date').value;
    const endDate = document.getElementById('apply-end-date').value;

    fetch('/interface/modules/custom_modules/oe-module-medex/public/calendar/api/templates.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'apply',
            template_id: currentTemplateId,
            start_date: startDate,
            end_date: endDate
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(`Template applied!\nCreated: ${data.created} blocks\nConflicts skipped: ${data.conflicts}`);
            closeModal();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function closeModal() {
    document.getElementById('applyModal').style.display = 'none';
}
</script>

</body>
</html>
