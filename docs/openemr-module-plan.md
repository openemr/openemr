# OpenEMR Module Integration Plan: Safety Sentinel Tab

**Prepared:** 2026-02-26
**Scope:** 3–4 focused hours, demo-quality
**Goal:** "Clinician opens a patient chart, clicks Safety Check tab, runs an ad-hoc safety check without leaving OpenEMR."

---

## Architecture Decision Record

### Decision: Custom Module Tab + Iframe Embedding

The Safety Sentinel UI is embedded as a new patient chart tab via an OpenEMR custom module. The tab page is a minimal PHP file that reads the active patient from `$_SESSION['pid']`, looks up the patient UUID, and renders an `<iframe>` pointing at the existing `static/index.html` with `?patient_id=UUID` pre-loaded. The iframe communicates exclusively with the Safety Sentinel FastAPI backend at `localhost:8001` — same origin for all API calls, so no CORS configuration is required.

```
┌─────────────────────────────────────────────────────────────┐
│  OpenEMR (localhost:8300)                                    │
│                                                              │
│  Patient Chart                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  [Summary] [Encounters] [Documents] [Safety Check]◄──┼───┼── PatientMenuEvent::MENU_UPDATE
│  └──────────────────────────────────────────────────────┘   │      oe-module-safety-sentinel
│                                                              │      openemr.bootstrap.php
│  ┌──────────────────────────────────────────────────────┐   │
│  │  public/index.php                                    │   │
│  │   1. reads $_SESSION['pid']                          │   │
│  │   2. SQL: HEX(uuid), fname, lname → UUID string      │   │
│  │   3. <iframe src="http://localhost:8001/static/       │   │
│  │       index.html?patient_id=UUID&patient_name=Name"> │   │
│  └──────────────────────────────────────────────────────┘   │
│                        │ iframe                              │
└────────────────────────┼────────────────────────────────────┘
                         │
         ┌───────────────▼──────────────────────────────┐
         │  Safety Sentinel (localhost:8001)             │
         │                                               │
         │  static/index.html                            │
         │   - reads ?patient_id from URL params         │
         │   - hides patient dropdown                    │
         │   - calls /api/v1/patient/{id} (same origin)  │
         │   - calls /api/v1/safety-check (same origin)  │
         │                                               │
         │  FastAPI backend                              │
         │   - OpenEMR REST API (localhost:8300)         │
         │   - OpenFDA API                               │
         │   - Claude claude-sonnet-4-6                       │
         └───────────────────────────────────────────────┘
```

### Alternatives Rejected

| Approach | Why Rejected |
|---|---|
| Injected sidebar on existing pages | Risk of breaking existing OpenEMR pages; not achievable in 3-4 hours |
| PHP proxy layer | Unnecessary — iframe + same-origin API calls avoid all CORS issues |
| Full native PHP UI | Duplicates existing working frontend; 10x more work |
| Prescription save hook | Stretch goal only; complex Laminas event wiring not needed for demo |
| postMessage cross-frame communication | Not needed — iframe is self-contained; no parent↔child JS needed |

---

## Module Structure

```
interface/modules/custom_modules/oe-module-safety-sentinel/
├── openemr.bootstrap.php        # Register PatientMenuEvent::MENU_UPDATE listener
├── info.txt                     # One-line description shown in Admin > Modules
├── version.php                  # Version: 1.0.0
├── ModuleManagerListener.php    # Required lifecycle hooks (enable/disable/unregister)
└── public/
    └── index.php                # Tab content: read pid → UUID → render iframe
```

No `src/` directory, no `composer.json`, no Laminas routing, no database migrations. Five files total.

---

## Implementation Plan

**Estimated total: 2.5–3 hours**

---

### Step 1: Modify `static/index.html` to Accept URL Params (30 min)

**File:** `agents/safety-sentinel/static/index.html`

The only change is replacing the last line of the `<script>` block. The existing `initPatientDropdown()` function stays untouched. Add a new DOMContentLoaded wrapper that checks URL params first:

**Replace** (line 809):
```javascript
document.addEventListener('DOMContentLoaded', initPatientDropdown);
```

**With:**
```javascript
document.addEventListener('DOMContentLoaded', async () => {
    const params  = new URLSearchParams(window.location.search);
    const preId   = params.get('patient_id');
    const preName = params.get('patient_name');

    if (preId) {
        // Loaded from OpenEMR — hide the patient selector, auto-load the chart patient
        const sidebarTop = patientSel.closest('.sidebar-top');
        if (sidebarTop) sidebarTop.style.display = 'none';

        patientNames[preId] = preName || preId;
        switchPatient(preId);

        // Still show the connectivity badge
        try {
            const s = await fetch('/api/v1/status').then(r => r.json());
            dsBadge.textContent = s.openemr_available ? '🟢 OpenEMR' : '🟡 Demo data';
            dsBadge.className   = s.openemr_available ? 'ds-badge ds-connected' : 'ds-badge ds-mock';
        } catch { /* non-blocking */ }
    } else {
        // Standalone mode — normal dropdown initialization
        initPatientDropdown();
    }
});
```

**Why this works:** `switchPatient(preId)` already calls `loadPatientProfile(preId)` which triggers `GET /api/v1/patient/{id}` — a relative URL that resolves correctly inside the iframe to `localhost:8001`. The patient profile loads immediately without the dropdown.

**Backward compatibility:** When `?patient_id` is absent (standalone usage), `initPatientDropdown()` is called exactly as before. Zero behavior change for existing standalone users.

---

### Step 2: Create Module Files (75 min)

#### `info.txt`
```
Safety Sentinel — AI-powered clinical safety checks for prescriptions
```

#### `version.php`
```php
<?php
$v_major    = '1';
$v_minor    = '0';
$v_patch    = '0';
$v_tag      = '';
$v_database = 0;
```

#### `ModuleManagerListener.php`
```php
<?php

use OpenEMR\Core\AbstractModuleActionListener;

class ModuleManagerListener extends AbstractModuleActionListener
{
    public function __construct()
    {
        parent::__construct();
    }

    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        if (method_exists(self::class, $methodName)) {
            return self::$methodName($modId, $currentActionStatus);
        }
        return $currentActionStatus;
    }

    public static function getModuleNamespace(): string
    {
        return 'OpenEMR\\Modules\\SafetySentinel\\';
    }

    public static function initListenerSelf(): self
    {
        return new self();
    }

    private function enable($modId, string $currentActionStatus): string
    {
        return $currentActionStatus;
    }

    private function disable($modId, string $currentActionStatus): string
    {
        return $currentActionStatus;
    }

    private function unregister($modId, string $currentActionStatus): string
    {
        return $currentActionStatus;
    }
}
```

#### `openemr.bootstrap.php`
```php
<?php

/**
 * Safety Sentinel Module Bootstrap
 *
 * Registers a new "Safety Check" tab in the patient chart navigation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Menu\PatientMenuEvent;

/**
 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
 */
function oe_module_safety_sentinel_add_tab(PatientMenuEvent $menuEvent)
{
    $existingMenu = $menuEvent->getMenu();

    $menuItem              = new stdClass();
    $menuItem->label       = xlt("Safety Check");
    $menuItem->url         = $GLOBALS['webroot']
        . "/interface/modules/custom_modules/oe-module-safety-sentinel/public/index.php";
    $menuItem->menu_id     = "mod_safety_sentinel";
    $menuItem->target      = "main";
    $menuItem->on_click    = "top.restoreSession()";
    $menuItem->pid         = "false";   // We read $_SESSION['pid'] ourselves
    $menuItem->children    = [];
    $menuItem->requirement = 0;

    $existingMenu[] = $menuItem;
    $menuEvent->setMenu($existingMenu);

    return $menuEvent;
}

$eventDispatcher->addListener(
    PatientMenuEvent::MENU_UPDATE,
    'oe_module_safety_sentinel_add_tab'
);
```

#### `public/index.php`
```php
<?php

/**
 * Safety Sentinel Tab Content
 *
 * Reads the active patient from session, looks up their UUID and name,
 * then renders an iframe to the Safety Sentinel FastAPI frontend
 * pre-populated with the current patient context.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Path depth: public/ → oe-module-safety-sentinel/ → custom_modules/ → modules/ → interface/ → openemr root
require_once dirname(__FILE__, 5) . "/globals.php";

use OpenEMR\Core\Header;

// ── Safety Sentinel backend URL ──────────────────────────────────────────────
// Change this to your Railway URL for deployed usage:
// https://safety-sentinel-production.up.railway.app
// Or set $GLOBALS['safety_sentinel_url'] in your environment config.
$sentinelUrl = $GLOBALS['safety_sentinel_url'] ?? 'http://localhost:8001';

// ── Current patient from session ─────────────────────────────────────────────
$pid = (int)($_SESSION['pid'] ?? 0);

if (empty($pid)) {
    ?>
    <div style="padding:40px;text-align:center;color:#6b7280;font-family:system-ui,sans-serif;">
        <p><?php echo xlt("Please select a patient from the patient list first."); ?></p>
    </div>
    <?php
    exit;
}

// ── Look up UUID and name via SQL ─────────────────────────────────────────────
// UUIDs are stored as binary in OpenEMR; HEX() converts to a 32-char hex string
// which we reassemble into standard UUID format (8-4-4-4-12).
$pt = sqlQuery(
    "SELECT HEX(uuid) AS uuid_hex, fname, lname FROM patient_data WHERE pid = ?",
    [$pid]
);

$puuid       = '';
$patientName = '';

if ($pt) {
    $hex = strtolower($pt['uuid_hex'] ?? '');
    if (strlen($hex) === 32) {
        $puuid = implode('-', [
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ]);
    }
    $patientName = trim(($pt['fname'] ?? '') . ' ' . ($pt['lname'] ?? ''));
}

if (empty($puuid)) {
    ?>
    <div style="padding:40px;text-align:center;color:#ef4444;font-family:system-ui,sans-serif;">
        <p><?php echo xlt("Could not resolve patient UUID. Please try reloading the chart."); ?></p>
    </div>
    <?php
    exit;
}

// ── Build iframe URL ──────────────────────────────────────────────────────────
$iframeUrl = $sentinelUrl . '/static/index.html?' . http_build_query([
    'patient_id'   => $puuid,
    'patient_name' => $patientName,
]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php Header::setupHeader(); ?>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
        #safety-sentinel-frame {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
    </style>
</head>
<body>
<iframe
    id="safety-sentinel-frame"
    src="<?php echo attr($iframeUrl); ?>"
    title="<?php echo xla('Safety Sentinel Clinical Safety Check'); ?>"
    sandbox="allow-scripts allow-same-origin allow-forms"
></iframe>
</body>
</html>
```

**Security note on `sandbox`:** The `allow-scripts allow-same-origin allow-forms` attributes are required for the iframe to make fetch() calls. `allow-same-origin` here refers to the iframe's own origin (localhost:8001), not OpenEMR's origin.

---

### Step 3: Enable the Module in OpenEMR (15 min)

1. Go to **Admin > Modules > Manage Modules** (http://localhost:8300/interface/modules/zend_modules/public/Modules/list)
2. Find `oe-module-safety-sentinel` in the list
3. Click **Install**, then **Enable**
4. Open any patient chart — the "Safety Check" tab should appear in the navigation

If the module doesn't appear: check that the directory exists at the correct path and that `openemr.bootstrap.php` has no PHP syntax errors.

---

### Step 4: Test the Full Flow (30 min)

Smoke test checklist:

- [ ] Open OpenEMR, navigate to a patient chart (e.g., Bob Jones)
- [ ] Click the "Safety Check" tab — it should appear after existing tabs
- [ ] The iframe loads Safety Sentinel with the correct patient pre-selected (no manual dropdown needed)
- [ ] The patient's medications and allergies load in the left sidebar
- [ ] Type "Is it safe to prescribe amoxicillin for this patient?" and submit
- [ ] Bob Jones (penicillin allergy) should return `severity=contraindicated, blocked=true`
- [ ] Try a safe patient (Carol White) with a safe drug — should return `severity=safe`
- [ ] Verify the patient selector dropdown is hidden in the embedded view

---

## Risk Assessment

### Risk 1: Module doesn't appear in Admin > Modules list

**Cause:** OpenEMR scans `interface/modules/custom_modules/` for directories with `openemr.bootstrap.php`. If the directory wasn't there when the page was last loaded, it may need a forced refresh.

**Mitigation:** After creating the files, clear any opcode cache inside Docker:
```bash
docker compose exec openemr php -r "opcache_reset();" 2>/dev/null || true
```
Then refresh the modules page. If still missing, verify the path:
```bash
ls interface/modules/custom_modules/oe-module-safety-sentinel/
```

### Risk 2: `globals.php` path depth is wrong

**Cause:** The `dirname(__FILE__, 5)` call assumes the file is exactly 5 directory levels below the OpenEMR root. If the module directory nesting changes, this breaks.

**Mitigation:** The path `interface/modules/custom_modules/oe-module-safety-sentinel/public/index.php` is 5 levels deep (interface → modules → custom_modules → oe-module-safety-sentinel → public → index.php, counting from the openemr root). Verify by checking that `dirname(__FILE__, 5) . "/globals.php"` resolves correctly if you see PHP errors about undefined functions.

**Fallback:** Use an absolute path:
```php
require_once '/var/www/localhost/htdocs/openemr/globals.php';
```

### Risk 3: iframe fails to load (Mixed content, X-Frame-Options)

**Cause:** OpenEMR may set `X-Frame-Options: SAMEORIGIN` or CSP headers that block external iframes. Since both services are on `localhost`, this shouldn't apply — but if OpenEMR is accessed via HTTPS (port 9300) and Safety Sentinel is HTTP (port 8001), browsers will block mixed content.

**Mitigation:** For the demo, use **HTTP** OpenEMR (`http://localhost:8300`) not HTTPS (`https://localhost:9300`). Both sides are HTTP, no mixed content issue. If you must use HTTPS, start Safety Sentinel with TLS or use a localhost tunnel.

**Check:** If the iframe shows a blank white area instead of Safety Sentinel, open the browser DevTools console — it will show the exact error.

### Risk 4: `$_SESSION['pid']` is empty in the tab context

**Cause:** OpenEMR's patient chart navigation loads tabs in an inner frame. The PHP session is shared via cookie, but if the request context is somehow different, `$_SESSION['pid']` may be 0.

**Mitigation:** Add the graceful fallback (already in the plan) that shows "Please select a patient." as a user-friendly message rather than a PHP error. Then check that you're navigating to the tab from within an active patient chart (not from the main calendar or admin panel).

### Risk 5: UUID format mismatch

**Cause:** The Safety Sentinel backend expects UUIDs in the format `a127903f-6859-4921-8910-bd1872393103`. If the HEX() + reassembly produces a different format (e.g., uppercase, no hyphens), the OpenEMR client's API calls will fail with 400/404.

**Mitigation:** The `strtolower()` call and the explicit 8-4-4-4-12 split handles this. To verify, add a temporary `var_dump($puuid); exit;` in `public/index.php` after the UUID assembly to confirm format before wiring up the iframe.

### Fallback Plan: If module integration fails

If OpenEMR module registration proves intractable within the time budget, fall back to the **standalone UI** at `http://localhost:8001` and open it side-by-side with an OpenEMR patient chart in split-screen. For the demo video, this still demonstrates Safety Sentinel's capabilities — the OpenEMR integration is a differentiator, not a rubric requirement.

---

## Demo Script

**Target duration:** 90 seconds of recording

### Scene 1: Context (15 sec)
- Show OpenEMR's patient list — "This is OpenEMR, an open-source EHR running locally with 103 synthetic patients"
- Click a patient name (Bob Jones) to open the patient chart

### Scene 2: The Safety Check Tab (10 sec)
- Point to the tab bar — "I built a custom OpenEMR module that adds a Safety Check tab directly in the patient chart"
- Click the "Safety Check" tab

### Scene 3: Patient Pre-populated (10 sec)
- Pause on the loaded UI — "The panel already knows which patient we're looking at — it pulled the patient context from OpenEMR's session automatically, no re-selection needed"
- Show Bob's allergies visible in the sidebar (penicillin allergy)

### Scene 4: The Safety Check (30 sec)
- Type: "Is it safe to prescribe amoxicillin 500mg?"
- Hit submit
- Wait for response
- Show the result: `CONTRAINDICATED — BLOCKED` banner
- Briefly show the reasoning panel: "It found the penicillin allergy, identified amoxicillin as a beta-lactam, and hard-blocked the prescription"

### Scene 5: Safe Check (20 sec)
- Clear the input, type: "What about metformin?"
- Show `severity=safe` result
- "Safe drugs pass through without noise"

### Scene 6: Closing (5 sec)
- "Clinical safety checks without leaving the patient chart — that's Safety Sentinel embedded in OpenEMR"

---

## File Checklist

Files to create:
- [ ] `interface/modules/custom_modules/oe-module-safety-sentinel/openemr.bootstrap.php`
- [ ] `interface/modules/custom_modules/oe-module-safety-sentinel/info.txt`
- [ ] `interface/modules/custom_modules/oe-module-safety-sentinel/version.php`
- [ ] `interface/modules/custom_modules/oe-module-safety-sentinel/ModuleManagerListener.php`
- [ ] `interface/modules/custom_modules/oe-module-safety-sentinel/public/index.php`

Files to modify:
- [ ] `agents/safety-sentinel/static/index.html` — replace last `addEventListener` line

Files to verify still work after changes:
- [ ] Standalone Safety Sentinel UI at `http://localhost:8001` (no URL params → normal dropdown)
- [ ] Evals still pass: `python evals/runner.py`
