# MedEx Campaign Controls - UX Mockup

## Current State (Core Only)
```
| Patient | Recall Info | Contact | Postcards | Labels | Office: Phone Outreach | Notes | Status | Actions |
|---------|-------------|---------|-----------|--------|------------------------|-------|--------|---------|
| John    | 2026-01-25  | C: 555  | [ ]       | [ ]    | [ ] 📅                 | ...   | ...    | ✏️ 🗑️  |
```

## With MedEx Module Injection (Proposed)
```
| Patient | Recall Info | Contact | Postcards | Labels | MedEx Campaigns            | Office | Notes | Status | Actions |
|---------|-------------|---------|-----------|--------|----------------------------|--------|-------|--------|---------|
| John    | 2026-01-25  | C: 555  | ☐ 🖨️     | ☐ 🖨️  | [SMS: Off] [EMAIL: Off]    | [ ] 📅 | ...   | ...    | ✏️ 🗑️  |
|         |             | SMS ✓   |           |        | [AVM: On ] 📝 🗑️ 📁       |        |       |        |         |
|         |             | EMAIL ✓ |           |        |                            |        |       |        |         |
|         |             | AVM ✗   |           |        | [x] Always send            |        |       |        |         |
```

## Injection Point

### Core provides:
```php
// In DisplayService.php - after Labels column, before Office column
echo '<div class="divTableCell medex-campaigns" id="campaigns_' . attr($recall['r_pid']) . '">';
echo '&nbsp;'; // Placeholder - MedEx injects here if enabled
echo '</div>';
```

### MedEx module injects:
```javascript
// In recall_board_injection.js
function injectCampaignControls(pid, modalities, campaigns) {
    var $cell = $('#campaigns_' + pid);
    if (!$cell.length) return;

    var html = '<div class="medex-campaign-controls">';

    // Toggle switches for each modality
    ['SMS', 'EMAIL', 'AVM'].forEach(function(type) {
        var enabled = campaigns[type] ? campaigns[type].enabled : false;
        var allowed = modalities.ALLOWED[type] === 'YES';
        var disabled = !allowed ? ' disabled' : '';
        var checkedAttr = enabled ? ' checked' : '';

        html += '<div class="campaign-toggle">';
        html += '<label class="switch' + disabled + '">';
        html += '<input type="checkbox" class="campaign-enable" data-pid="' + pid + '" data-type="' + type + '"' + checkedAttr + disabled + '>';
        html += '<span class="slider"></span>';
        html += '</label>';
        html += '<span class="modality-label">' + type + '</span>';

        // Action buttons (edit campaign, delete, view history)
        if (enabled) {
            html += '<i class="fa fa-pencil btn-sm btn-primary" title="Edit campaign"></i>';
            html += '<i class="fa fa-trash btn-sm btn-danger" title="Delete campaign"></i>';
            html += '<i class="fa fa-folder btn-sm btn-info" title="View history"></i>';
        }
        html += '</div>';
    });

    // "Always send" checkbox
    html += '<div class="always-send">';
    html += '<input type="checkbox" id="always_send_' + pid + '"> Always send';
    html += '</div>';

    html += '</div>';

    $cell.html(html);
}
```

### CSS (MedEx module provides)
```css
.medex-campaign-controls {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 5px;
}

.campaign-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Toggle switch styling (from medexbank.com) */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "Off";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
    font-size: 8px;
    line-height: 18px;
    text-align: center;
    color: #999;
}

input:checked + .slider {
    background-color: #4CAF50; /* Green when on */
}

input:checked + .slider:before {
    transform: translateX(26px);
    content: "On";
    color: #4CAF50;
}

input:disabled + .slider {
    background-color: #ddd;
    cursor: not-allowed;
}

.modality-label {
    font-weight: bold;
    min-width: 50px;
}

.always-send {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #ddd;
    font-size: 0.9em;
}

/* Action buttons */
.medex-campaign-controls .btn-sm {
    font-size: 14px;
    padding: 4px;
    margin: 0 2px;
    cursor: pointer;
}

.btn-primary { color: #007bff; }
.btn-danger { color: #dc3545; }
.btn-info { color: #17a2b8; }
```

## Visual Layout

```
┌─────────────────────────────────────────┐
│         MedEx Campaigns                 │
├─────────────────────────────────────────┤
│ [Off]  SMS    ✏️ 🗑️ 📁                   │
│ [Off]  EMAIL  ✏️ 🗑️ 📁                   │
│ [On ]  AVM    ✏️ 🗑️ 📁                   │
├─────────────────────────────────────────┤
│ ☑ Always send                           │
└─────────────────────────────────────────┘
```

Where:
- **Toggle**: Green/On when campaign active, Gray/Off when inactive
- **Disabled**: If modality not allowed (no HIPAA permission or missing contact info)
- **Edit** (✏️): Configure campaign settings (timing, message template)
- **Delete** (🗑️): Remove campaign for this recall
- **History** (📁): View campaign event log (SENT/READ/FAILED)
- **Always send**: Auto-enable campaigns for future recalls

## Implementation Steps

1. **Core: Add injection point column**
   ```php
   // library/RecallBoard/DisplayService.php
   // Add new column header
   echo '<div class="divTableCell text-center">' . xlt('Campaigns') . '</div>';

   // Add cell with injection point ID
   echo '<div class="divTableCell medex-campaigns" id="campaigns_' . attr($pid) . '">&nbsp;</div>';
   ```

2. **Module: Inject controls via JavaScript**
   ```javascript
   // recall_board_injection.js
   // After modality icons injection, add campaign controls
   injectCampaignControls(pid, campaign.modalities, campaign.campaigns);
   ```

3. **Module: Handle toggle events**
   ```javascript
   $(document).on('change', '.campaign-enable', function() {
       var pid = $(this).data('pid');
       var type = $(this).data('type');
       var enabled = $(this).is(':checked');

       $.ajax({
           url: ajaxUrl,
           type: 'POST',
           data: {
               action: 'toggle_campaign',
               pid: pid,
               type: type,
               enabled: enabled
           },
           success: function(response) {
               // Reload campaign data
           }
       });
   });
   ```

4. **Module: AJAX endpoint for campaign management**
   ```php
   // ajax.php
   if ($action === 'toggle_campaign') {
       $pid = (int)$_POST['pid'];
       $type = $_POST['type']; // SMS/EMAIL/AVM
       $enabled = $_POST['enabled'] === 'true';

       if ($enabled) {
           // Create/enable campaign
           $sql = "INSERT INTO medex_outgoing (msg_pid, msg_type, msg_reply)
                   VALUES (?, ?, 'SCHEDULED')";
           sqlStatement($sql, [$pid, $type]);
       } else {
           // Disable campaign
           $sql = "DELETE FROM medex_outgoing WHERE msg_pid = ? AND msg_type = ?";
           sqlStatement($sql, [$pid, $type]);
       }

       echo json_encode(['success' => true]);
   }
   ```

## Benefits

1. **Clear visual state**: Toggle switches show at-a-glance which campaigns are active
2. **Inline management**: Edit/delete campaigns without leaving the recall board
3. **Respects HIPAA**: Toggles disabled when modality not allowed
4. **MedEx UX consistency**: Matches medexbank.com interface styling
5. **Core independence**: Column only appears when MedEx module enabled

## Alternative: Modal Dialog

Instead of inline controls, clicking a "Campaigns" button could open a modal:

```javascript
<button onclick="manageCampaigns(123)">📧 Campaigns</button>

// Opens modal with full campaign management interface
function manageCampaigns(pid) {
    // Load modal with toggle switches, message templates, timing settings
    $('#campaignModal').modal('show');
}
```

This keeps the recall board table cleaner but requires extra clicks.
