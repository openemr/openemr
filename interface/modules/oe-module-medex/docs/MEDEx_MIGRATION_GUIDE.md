# MedEx Module Migration Guide

## Goal
Retain core OpenEMR functionality for non-MedEx users while moving all MedEx-specific code to the module, then delete `library/MedEx/` entirely.

## Core Files to Modify (Keep Non-MedEx Functionality)

### 1. interface/main/messages/messages.php

**KEEP:**
- Basic Messages functionality
- Core messaging system
- Non-MedEx navigation

**REMOVE (MedEx-specific only):**
```php
// Line 27: Remove MedEx API include
require_once "$srcdir/MedEx/API.php";

// Lines 39-48: Remove MedEx initialization
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

// Lines 110-137: Remove MedEx page handlers (keep basic structure)
if (($_REQUEST['go'] == "setup") && (!$logged_in)) {
    echo "<title>" . xlt('Setup') . "</title>";
    // Keep basic setup, remove MedEx-specific
} elseif ($_REQUEST['go'] == "addRecall") {
    echo "<title>" . xlt('New Recall') . "</title>";
    // Keep basic recall, remove MedEx-specific
} elseif ($_REQUEST['go'] == 'Recalls') {
    echo "<title>" . xlt('Recall Board') . "</title>";
    // Keep basic recall board, remove MedEx-specific
} elseif ($_REQUEST['go'] == 'Preferences') {
    echo "<title>" . xlt('Preferences') . "</title>";
    // Keep basic preferences, remove MedEx-specific
} elseif ($_REQUEST['go'] == 'icons') {
    echo "<title>" . xlt('Icons') . "</title>";
    // Keep basic icons, remove MedEx-specific
} elseif ($_REQUEST['go'] == 'SMS_bot') {
    // Remove entirely - MedEx-specific
}

// Lines 192-196: Remove SMS Zone tab (MedEx-specific)
<?php if ($logged_in) { ?>
<li class="nav-item" id='li-sms' role="presentation">
    <a href='#sms-div' id='sms-li' class="nav-link" data-toggle="pill"  role="tab" aria-controls="<?php echo xla("SMS Zone");?>" aria-selected="true"><?php echo xlt('SMS Zone'); ?></a>
</li>
<?php }?>

// Lines 788-801: Remove SMS Zone content (MedEx-specific)
<div class="row tab-pane" role="tabpanel" id="sms-div">
    // Remove entire SMS Zone
</div>

// Lines 1056-1074: Remove SMS_direct function (MedEx-specific)
function SMS_direct() {
    // Remove entirely - MedEx-specific
}

// Lines 865-874: Remove SMS Zone JavaScript (MedEx-specific)
// Remove SMS Zone specific JavaScript

// Lines 77-78: Remove MedEx meta tags (MedEx-specific)
<meta name="description" content="MedEx Bank" />
<meta name="author" content="OpenEMR: MedExBank" />
```

**ADD at end:**
```php
// Include MedEx module functionality (if enabled)
if ($GLOBALS['medex_enable'] == '1') {
    require_once __DIR__ . '/../modules/custom_modules/oe-module-medex/public/medex_integration.php';
}
```

### 2. interface/patient_tracker/patient_tracker.php

**KEEP:** Basic patient tracking functionality
**REMOVE:** MedEx-specific code only

### 3. library/MedEx/API.php

**DELETE ENTIRELY** - All functionality moved to module

## Module Implementation

### 1. MedEx Integration File
**File:** `public/medex_integration.php`

```php
<?php
/**
 * MedEx Module Integration for Core Files
 * Injects MedEx-specific functionality into core pages
 */

// Only proceed if MedEx module is enabled
if ($GLOBALS['medex_enable'] == '1') {
    
    // Inject MedEx navigation
    function injectMedExNavigation($logged_in) {
        // Include MedEx navigation template
        include __DIR__ . '/../templates/navigation.php';
    }
    
    // Handle MedEx-specific pages
    function handleMedExPage($go, $logged_in) {
        switch ($go) {
            case 'SMS_bot':
                // Redirect to module SMS bot
                header('Location: ' . $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php?' . http_build_query($_REQUEST));
                exit;
                
            case 'Recalls':
                // Use MedEx recall board
                include __DIR__ . '/recall_board.php';
                exit;
                
            case 'Preferences':
                // Use MedEx preferences
                include __DIR__ . '/../admin/settings.php';
                exit;
                
            case 'icons':
                // Use MedEx icons
                include __DIR__ . '/../templates/icons.php';
                exit;
        }
    }
    
    // Inject SMS Zone (MedEx-specific)
    function injectSMSZone() {
        echo '
        <li class="nav-item" id="li-sms" role="presentation">
            <a href="#sms-div" id="sms-li" class="nav-link" data-toggle="pill" role="tab" aria-controls="SMS Zone" aria-selected="true">' . xlt('SMS Zone') . '</a>
        </li>';
    }
    
    // Inject SMS Zone content
    function injectSMSZoneContent() {
        echo '
        <div class="row tab-pane" role="tabpanel" id="sms-div">
            <div class="col-sm-4 col-md-4 col-lg-4">
                <h4>' . xlt('SMS Zone') . '</h4>
                <form id="smsForm" class="input-group">
                    <select id="SMS_patient" type="text" class="form-control m-0 w-100" placeholder="' . xla('Patient Name') . '"></select>
                    <span class="input-group-addon" onclick="SMS_direct();">&nbsp;&nbsp;<i id="open-sms-tooltip" class="fas fa-2x fa-phone"></i></span>
                    <input type="hidden" id="sms_pid" />
                    <input type="hidden" id="sms_mobile" value="" />
                    <input type="hidden" id="sms_allow" value="" />
                </form>
            </div>
        </div>';
    }
    
    // Handle page routing
    $go = $_REQUEST['go'] ?? '';
    if (in_array($go, ['SMS_bot', 'Recalls', 'Preferences', 'icons'])) {
        handleMedExPage($go, $logged_in ?? null);
    }
}
?>
```

### 2. Core Compatibility Layer

**File:** `src/CoreCompatibility.php`

```php
<?php
/**
 * Core Compatibility Layer
 * Provides compatibility functions for core OpenEMR functionality
 */

namespace OpenEMR\Modules\MedEx\Core;

class CoreCompatibility
{
    /**
     * Check if MedEx is enabled
     */
    public static function isEnabled(): bool
    {
        return ($GLOBALS['medex_enable'] ?? '0') == '1';
    }
    
    /**
     * Get MedEx login status
     */
    public static function getLoginStatus(): ?array
    {
        if (!self::isEnabled()) {
            return null;
        }
        
        try {
            $api = new \OpenEMR\Modules\MedEx\MedExAPI();
            return $api->login();
        } catch (\Exception $e) {
            error_log('MedEx login error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Inject MedEx content into core pages
     */
    public static function injectContent(string $injectionPoint, array $context): void
    {
        if (!self::isEnabled()) {
            return;
        }
        
        switch ($injectionPoint) {
            case 'navigation':
                self::injectNavigation($context['logged_in'] ?? null);
                break;
            case 'sms_tab':
                self::injectSMSTab();
                break;
            case 'sms_content':
                self::injectSMSContent();
                break;
            case 'scripts':
                self::injectScripts();
                break;
        }
    }
    
    private static function injectNavigation(?array $loggedIn): void
    {
        if ($loggedIn) {
            include __DIR__ . '/../templates/navigation.php';
        }
    }
    
    private static function injectSMSTab(): void
    {
        echo '<li class="nav-item" id="li-sms" role="presentation">
            <a href="#sms-div" id="sms-li" class="nav-link" data-toggle="pill" role="tab" aria-controls="SMS Zone" aria-selected="true">' . xlt('SMS Zone') . '</a>
        </li>';
    }
    
    private static function injectSMSContent(): void
    {
        // SMS Zone content injection
    }
    
    private static function injectScripts(): void
    {
        // SMS Zone JavaScript injection
    }
}
?>
```

## Migration Steps

### Phase 1: Prepare Core Files
1. Backup core files
2. Remove MedEx-specific code from messages.php
3. Keep core messaging/recall functionality
4. Add module integration include

### Phase 2: Module Enhancement
1. Create integration file for core compatibility
2. Move all MedEx-specific functionality to module
3. Test core functionality without module
4. Test MedEx functionality with module

### Phase 3: Library Removal
1. Delete `library/MedEx/` entirely
2. Test core functionality (should work)
3. Test MedEx functionality (should work via module)
4. Verify no dependencies remain

### Phase 4: Final Testing
1. Test with module disabled (core only)
2. Test with module enabled (core + MedEx)
3. Verify all functionality works
4. Submit PR

## Benefits

- ✅ Core OpenEMR works without MedEx
- ✅ MedEx functionality preserved in module
- ✅ Clean separation of concerns
- ✅ No library/MedEx dependency
- ✅ Backward compatibility maintained
- ✅ New features can be added to module

## Files to Delete

- `library/MedEx/API.php` (entire directory)
- Any other MedEx files in library/

## Files to Keep (Core)

- Basic messaging functionality
- Basic recall board (non-MedEx)
- Patient tracking
- Flow board functionality

This approach ensures that OpenEMR works perfectly for non-MedEx users while providing full MedEx functionality through the module.
