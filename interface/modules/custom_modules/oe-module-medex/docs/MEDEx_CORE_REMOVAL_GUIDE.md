# MedEx Core Removal Guide

This guide provides step-by-step instructions for removing all MedEx references from the OpenEMR core codebase and ensuring the module handles all functionality.

## Overview

The goal is to move all MedEx functionality from the OpenEMR core to the module, creating a clean separation and allowing the module to work independently.

## Files to Modify

### 1. interface/main/messages/messages.php

**Remove the following lines:**

```php
// Line 27: Remove MedEx API include
require_once "$srcdir/MedEx/API.php";

// Lines 39-48: Remove MedEx initialization and login
$MedEx = new MedExApi\MedEx('MedExBank.com');

if ($GLOBALS['medex_enable'] == '1') {
    if ($_REQUEST['SMS_bot']) {
        $result = $MedEx->login('');
        $MedEx->display->SMS_bot($result);
        exit();
    }
    $logged_in = $MedEx->login();
} else {
    $logged_in = null;
}

// Lines 103-106: Remove MedEx navigation
if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu'])) && ($GLOBALS['disable_rcb'] != '1')) {
    $MedEx->display->navigation($logged_in);
    echo "<br /><br /><br />";
}

// Lines 110-137: Remove MedEx page handlers
if (($_REQUEST['go'] == "setup") && (!$logged_in)) {
    echo "<title>" . xlt('MedEx Setup') . "</title>";
    $stage = $_REQUEST['stage'];
    if (!is_numeric($stage)) {
        echo "<br /><span class='title'>" . text($stage) . " " . xlt('Warning') . ": " . xlt('This is not a valid request') . ".</span>";
    } else {
        $MedEx->setup->MedExBank($stage);
    }
} elseif ($_REQUEST['go'] == "addRecall") {
    echo "<title>" . xlt('New Recall') . "</title>";
    $MedEx->display->display_add_recall();
} elseif ($_REQUEST['go'] == 'Recalls') {
    echo "<title>" . xlt('Recall Board') . "</title>";
    $MedEx->display->display_recalls($logged_in);
} elseif ((($_REQUEST['go'] == "setup") || ($_REQUEST['go'] == 'Preferences')) && ($logged_in)) {
    echo "<title>MedEx: " . xlt('Preferences') . "</title>";
    $MedEx->display->preferences();
} elseif ($_REQUEST['go'] == 'icons') {
    echo "<title>MedEx: " . xlt('Icons') . "&#x24B8;</title>";
    $MedEx->display->icon_template();
} elseif ($_REQUEST['go'] == 'SMS_bot') {
    echo "<title>MedEx: SMS Bot&#x24B8;</title>";
    $MedEx->display->SMS_bot($logged_in);
    exit;
} else {
    echo "<title>" . xlt('MedEx Setup') . "</title>";
    echo xlt('Warning: Navigation error. Please refresh this page.');
}

// Lines 192-196: Remove SMS Zone tab
<?php if ($logged_in) { ?>
<li class="nav-item" id='li-sms' role="presentation">
    <a href='#sms-div' id='sms-li' class="nav-link" data-toggle="pill"  role="tab" aria-controls="<?php echo xla("SMS Zone");?>" aria-selected="true"><?php echo xlt('SMS Zone'); ?></a>
</li>
<?php }?>

// Lines 788-801: Remove SMS Zone content
<div class="row tab-pane" role="tabpanel" id="sms-div">
    <div class="col-sm-4 col-md-4 col-lg-4">
        <?php if ($logged_in) { ?>
        <h4><?php echo xlt('SMS Zone'); ?></h4>
        <form id="smsForm" class="input-group">
            <select id="SMS_patient" type="text" class="form-control m-0 w-100" placeholder="<?php echo xla("Patient Name"); ?>"></select>
            <span class="input-group-addon" onclick="SMS_direct();">&nbsp;&nbsp;<i id='open-sms-tooltip' class="fas fa-2x fa-phone"></i></span>
            <input type="hidden" id="sms_pid" />
            <input type="hidden" id="sms_mobile" value="" />
            <input type="hidden" id="sms_allow" value="" />
        </form>
        <?php } ?>
    </div>
</div>

// Lines 1056-1074: Remove SMS_direct function
function SMS_direct() {
    var pid = $("#sms_pid").val();
    var m = $("#sms_mobile").val();
    var allow = $("#sms_allow").val();
    if ((pid === '') || (m === '')) {
        alert(<?php echo xlj("MedEx needs a valid mobile number to send SMS messages..."); ?>);
    } else if (allow === 'NO') {
        alert(<?php echo xlj("This patient does not allow SMS messaging!"); ?>);
    } else {
        top.restoreSession();
        const params = new URLSearchParams({
            go: 'SMS_bot',
            m: m,
            nomenu: '1',
            pid: pid
        });
        window.open('messages.php?' + params, 'SMS_bot', 'width=370,height=600,resizable=0');
    }
}

// Lines 865-874: Remove SMS Zone JavaScript
$(function () {
    $("#SMS_patient").select2({
        ajax: {
            url: "save.php",
            dataType: 'json',
            data: function(params) {
                return {
                go: "sms_search",
                term: params.term
                };
            },
            processResults: function(data) {
                return  {
                    results: data.items
                };
            }
        },
        dropdownAutoWidth: true,
        placeholder: xl('Search for patient...'),
        theme: 'bootstrap4'
    })

    $('#SMS_patient').on('select2:select', function (e) {
                e.preventDefault();
                $("#SMS_patient").val(e.params.data.value);
                $("#sms_pid").val(e.params.data.pid);
                $("#sms_mobile").val(e.params.data.mobile);
                $("#sms_allow").val(e.params.data.allow);
            });
        })

// Lines 77-78: Remove MedEx meta tags
<meta name="description" content="MedEx Bank" />
<meta name="author" content="OpenEMR: MedExBank" />

// Line 1061: Remove MedEx alert text
alert(<?php echo xlj("MedEx needs a valid mobile number to send SMS messages..."); ?>);
```

**Add the following at the end of the file (before closing PHP tag):**

```php
// Include MedEx module SMS Zone functionality
if ($GLOBALS['medex_enable'] == '1') {
    require_once __DIR__ . '/../modules/custom_modules/oe-module-medex/public/sms_zone_module.php';
}
```

### 2. interface/patient_tracker/patient_tracker.php

**Remove similar MedEx references** (if present)

### 3. library/MedEx/API.php

**This entire file can be removed** as it's now in the module

### 4. Other Core Files

Search for and remove any remaining MedEx references:
```bash
grep -r "medex\|MedEx" interface/ --exclude-dir=modules --exclude-dir=custom_modules
```

## Module Configuration

### 1. Enable Event System

In the module's `openemr.bootstrap.php`, ensure the MessagesPageListener is registered:

```php
// Register MessagesPageListener
$msgListener = new Listeners\MessagesPageListener();
$eventDispatcher->addListener(\OpenEMR\Modules\MedEx\Events\MessagesPageRenderEvent::EVENT_RENDER, [$msgListener, 'onPageRender']);
```

### 2. Update Module Settings

Ensure the module's settings are configured to handle all MedEx functionality.

## Testing

### 1. Verify SMS Zone Functionality

- Navigate to Messages page
- Click on SMS Zone tab
- Search for a patient
- Click the phone icon
- Verify SMS bot opens in resizable window

### 2. Verify Other MedEx Features

- Recall Board
- Preferences
- Icons
- Setup

### 3. Verify No Core Dependencies

- Disable the module
- Verify no MedEx references remain functional
- Verify no errors in logs

## Migration Checklist

- [ ] Remove all MedEx includes from core files
- [ ] Remove all MedEx object instantiations
- [ ] Remove all MedEx function calls
- [ ] Remove SMS Zone HTML from core
- [ ] Remove SMS Zone JavaScript from core
- [ ] Add module include to messages.php
- [ ] Test all MedEx functionality through module
- [ ] Verify no core dependencies remain
- [ ] Test with module disabled

## Notes

1. **Backup First**: Always backup the core files before making changes
2. **Test Thoroughly**: Test all MedEx functionality after removal
3. **Gradual Migration**: Consider migrating one feature at a time
4. **Document Changes**: Keep track of all modifications for future reference

## Benefits

- Clean separation of concerns
- Easier module maintenance
- No core dependencies
- Simplified OpenEMR core
- Better module isolation
