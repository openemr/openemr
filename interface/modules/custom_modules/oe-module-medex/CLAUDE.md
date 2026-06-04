# MedEx Module - Comprehensive AI Agent Guide

---
## ⛔⛔⛔ MANDATORY FOR ALL AI AGENTS — THIS FILE MUST BE READ IN FULL ⛔⛔⛔

**DO NOT SKIP SECTIONS. DO NOT COMPLAIN ABOUT LENGTH. READ EVERY LINE.**

This file is the single authoritative guide for all AI agents working in this codebase.
It was created by merging 4 separate CLAUDE.md files from across the workspace to prevent
agents from missing critical rules. Previous agents missed rules because they were buried
or in separate files — resulting in broken deployments, lost work, and wrong-repo edits.

**The three other CLAUDE.md files in this workspace now redirect here:**
- `/Users/ray/projects/openemr/CLAUDE.md`
- `/Users/ray/projects/medex-local/CLAUDE.md`
- `/Users/ray/projects/medex-local/web_full/cart/upload/CLAUDE.md`

**YOU ARE REQUIRED TO READ THIS ENTIRE FILE BEFORE TOUCHING ANY CODE.**

### ⚠️ GitHub Copilot / Inline AI Agents Do NOT Auto-Read This File

The `claude` CLI tool reads CLAUDE.md automatically. **GitHub Copilot Chat and similar
agents do NOT.** The user must explicitly tell them: "Read CLAUDE.md first" or paste it in.
If you have not been told to read this file, you are probably missing critical context.
Ask the user: *"Should I read CLAUDE.md before proceeding?"*

---

## 🚨 PRIMARY TARGETS ARE K8s DEPLOYMENTS — NOT LOCAL DOCKER 🚨

**All work runs on Linode LKE (production k8s cluster). There is no separate staging.**

| System | URL | How to Deploy |
|--------|-----|---------------|
| OpenEMR | `https://emr.hipaabank.net` | `deploy_to_pod.sh --module` (see k8s ops section) |
| MedEx API | `https://api.hipaabank.net` | `docker buildx build --platform linux/amd64` + `kubectl rollout restart` |

**Local Mac = code editing + git storage only.** You do not run or test against localhost.
Changes are edited on Mac, committed to git, then deployed to k8s. Period.

## ⛔ CRITICAL: NEVER REBUILD DOCKER FOR SINGLE FILE CHANGES ⛔

**For iterative `web_full/` changes during development, use the hotpatch script — NOT Docker rebuild:**

```bash
cd /Users/ray/projects/medex-local/k8s
bash hotpatch-live.sh push
```

Docker image rebuild + k8s rollout restart is ONLY for:
- Shipping a finished release to production
- Changes to `Dockerfile`, startup scripts, or system packages

**Why this matters:** Rebuilding is slow, expensive, and burns build minutes.
All remote (non-k8s) customers will receive fixes via the release image, so use
hotpatch for rapid dev iteration and reserve rebuild for release day.

**`web_full/` hotpatch does NOT work for ConfigMap-managed files.** For those,
see the medex-api ConfigMap Policy section below.

---

---

## 🚨 RULE #1: MEDEX CODE LIVES IN `medex-local/web_full/` ONLY 🚨

**NEVER touch `medex-local/medex-core/`.** It is an archived reference copy.
All MedEx SaaS edits go to: `/Users/ray/projects/medex-local/web_full/cart/upload/`
A pre-commit hook in `medex-core/.git/hooks/pre-commit` will block accidental commits there.

---

## 🚨 IP & LICENSING NOTICE FOR AI AGENTS 🚨

- **OpenEMR**: Open Source (GNU GPL v3+)
- **MedEx Module**: **PROPRIETARY / CLOSED SOURCE** - All Rights Reserved
- **Owner**: MedEx <support@MedExBank.com>
- **License**: Proprietary - All Rights Reserved (NOT GPL)
- **AI Agents**: Protect MedEx IP - Do NOT suggest copying, open-sourcing, or redistributing

This module is **commercial intellectual property** of MedEx, distinct from OpenEMR's open source codebase.

---

## 🚨 COMMIT YOUR WORK CONSTANTLY 🚨

**Previous AI sessions ERASED WORK by not committing. DO NOT REPEAT THIS.**

### Mandatory Git Workflow
```bash
# After EVERY significant change, commit to BOTH repos:

# OpenEMR module changes:
cd /Users/ray/projects/openemr
git add -A && git commit -m "WIP: [brief description]"

# MedEx SaaS changes:
cd /Users/ray/projects/medex-local
git add -A && git commit -m "WIP: [brief description]"
```

### When to Commit
- ✅ After completing any file edit
- ✅ Before switching to a different task
- ✅ Before reading large amounts of context (may lose prior work)
- ✅ Every 10-15 minutes of active work
- ✅ Before asking user to test anything

### Why This Matters
- AI context can be lost mid-session
- Previous sessions erased work **3 times** by not committing
- Commits are cheap - losing hours of work is expensive
- User can always `git reset` if needed

---

## 🚨 CRITICAL: `top.restoreSession()` BEFORE EVERY `fetch()` CALL 🚨

**Every single `fetch()` call in this module MUST be preceded by `top.restoreSession()`.**
Forgetting this causes OpenEMR to silently expire the PHP session between requests, which
fails CSRF token validation and **redirects the user to the login page mid-workflow**.
This is not obvious — the error manifests as a redirect, not a JS error.

### The Rule
> **If you write or modify a `fetch()` call anywhere in this module, the line immediately
> before it MUST call `top.restoreSession()`.**

### Pattern A — Inline guard (use in all files except `admin/index.php`)
```javascript
if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
fetch('...', { method: 'POST', body: formData })
```

### Pattern B — Wrapper function (already defined in `admin/index.php`)
```javascript
// medexFetch() is defined at the top of admin/index.php's <script> block.
// Use it as a drop-in replacement for fetch() inside that file only.
medexFetch('...', { method: 'POST', body: formData })
```

### Files audited and fixed (commit 7c916a24c, 2026-02-23)
All fetch() calls in these files now have guards — **do not remove or bypass them**:

| File | Fetch calls guarded |
|---|---|
| `admin/index.php` | 5 (via `medexFetch()` wrapper) |
| `admin/api/get_settings.php` | 2 |
| `admin/api/get_subscriptions.php` | 8 |
| `public/pdf.php` | 1 |
| `public/calendar/index.php` | 1 |
| `public/secure_chat.php` | 3 |
| `public/calendar_feeds.php` | 2 |

### What `top.restoreSession()` does
It's an OpenEMR JS function defined in `library/js/utility.js` that pings
`library/ajax/php_echo.php?reqstr=1` to reset the PHP session timeout. OpenEMR's
CSRF middleware will reject any POST whose session has already expired — there is no
graceful failure, it just redirects to login.

### What caused the bug in the first place
**None** of the original fetch() calls had this guard. The module was written without
awareness of OpenEMR's session keepalive requirement. The problem only appeared after the
module was running long enough for sessions to expire between clicks.

---

## ⚠️ CRITICAL: READ THIS ENTIRE FILE BEFORE DOING ANYTHING

**DO NOT make assumptions. SEARCH the codebase first. ASK if unclear.**

This document consolidates ALL project documentation from 25+ MD files. **Read it completely.**

---

## 🎯 PROJECT STATUS

**CURRENT STATE: BROKEN - Previous AI sessions damaged the codebase**

### Reality Check ❌
- Phase 1: **INCOMPLETE** - Was done, then erased by AI 3x
- Phase 2: **INCOMPLETE** - Event architecture started but inconsistent
- Phase 3: **NOT STARTED** - Core extraction

### Known Problems
- ❌ Authentication is INCONSISTENT (legacy login vs token-based vs nothing) **FIXED 2026-02-14**
- ❌ Some pages lose session entirely **FIXED 2026-02-14**
- ❌ Many unfinished implementations
- ❌ Work was erased multiple times due to not committing

### What Should Exist (But May Be Broken)
- RecallService - needs verification
- Event system - needs verification
- Module modernization - needs verification

### Fixes Applied (2026-02-14)
- ✅ Fixed `getApiByToken()` in MedEx SaaS - was using broken JOIN with oc_api table
- ✅ Fixed PDF filler authentication - was using wrong lookup method
- ✅ Session token validation now works consistently

### Next Step
**AUDIT CURRENT STATE** before doing anything else.

---

## � ROADMAP & TODO LIST

### Messaging Channels Integration (2026-02)

#### ✅ DONE
- ✅ SMS (existing, via Telnyx)
- ✅ Email (existing)
- ✅ Voice/AVM (existing)
- ✅ Secure Chat (patient ↔ provider)
- ✅ Patient Preferences UI (SMS, Email, AVM, WhatsApp)

#### 🔄 IN PROGRESS
- 🔄 **WhatsApp Integration** (PRIORITY - Plivo Implementation Complete)
  - [x] WhatsApp provider selection (Plivo - simplest integration, existing vendor)
  - [x] API credentials management in admin (WhatsAppService, admin controller/view/language)
  - [x] Message sending logic (WhatsAppService.php with sendMessage using Plivo API)
  - [x] Inbound webhook handling for replies (inbound_whatsapp.php with Plivo signature validation)
  - [x] Plivo WhatsApp API integration (uses existing Plivo credentials)
  - [x] Database schema support (uses hipaa_outgoing with msg_carrier_type_id='WHATSAPP')
  - [x] Patient WhatsApp number field (p_whatsapp_number in hipaa_cal_people)
  - [ ] Campaign reminders via WhatsApp (integrate with campaign engine)
  - [ ] AI Rescheduler response handling (WhatsApp interactive responses)
  - [ ] Patient preferences integration (WhatsApp channel selection)

#### ⏳ BACKLOG
- **Signal Integration**
  - [ ] Signal Bot Token setup
  - [ ] Message sending logic
  - [ ] Inbound webhook handling
  - [ ] Campaign reminders via Signal
  - [ ] Patient preferences (UI + storage)
  - [ ] AI Rescheduler support

- **Multi-Channel Campaign Reminders**
  - [ ] Campaign builder updates (WhatsApp/Signal channels)
  - [ ] Apply patient preference order to campaigns
  - [ ] Fallback channel logic (if WhatsApp fails, try SMS)
  - [ ] Campaign delivery status tracking
  - [ ] Analytics by channel

- **AI Rescheduler Multi-Channel**
  - [ ] Parse responses from WhatsApp/Signal
  - [ ] Voice call + WhatsApp conversation flow
  - [ ] Response buttons/quick replies (WhatsApp)
  - [ ] Conversation context preservation

---

## �🔐 Authentication Flow (CRITICAL)

### How OpenEMR Module Authenticates with MedEx SaaS

```
1. OpenEMR Module calls MedExAPI->login()
2. MedExAPI POSTs to: /index.php?route=api/oemr/login
   - site = practice_id (customer_id from oc_customer)
   - token = api_key (stored in oc_customer.api_key)
3. MedEx SaaS validates credentials
4. MedEx creates session in oc_api_session table:
   - api_id = customer_id
   - token = 32-char hex session token
   - date_modified = NOW()
5. Returns JSON: {success, token, enabled_services, ...}
6. OpenEMR caches session token in medex_prefs table
7. Subsequent requests pass token via URL: ?token=xxx
8. MedEx validates token using getApiByToken()
```

### Session Token Validation (model/account/api.php::getApiByToken)

**CORRECT Implementation (Fixed 2026-02-14):**
```php
public function getApiByToken($token) {
    // api_id in oc_api_session IS the customer_id (set by login endpoint)
    $query = "SELECT s.api_id, c.customer_id, c.email, c.firstname
              FROM oc_api_session s
              LEFT JOIN oc_customer c ON s.api_id = c.customer_id
              WHERE s.token = '{escaped_token}'
              AND s.date_modified > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    // Returns row with api_id = customer_id
}
```

**BROKEN Implementation (Before Fix):**
```php
// WRONG - oc_api table has different IDs than customer_id
$query = "SELECT s.*, a.name, c.customer_id
          FROM oc_api_session s
          LEFT JOIN oc_api a ON s.api_id = a.api_id  // WRONG JOIN
          LEFT JOIN oc_customer c ON a.name = c.email";  // Fails
```

### Key Files for Authentication
- **OpenEMR Module:** `src/MedExAPI.php` - login(), getSessionToken()
- **MedEx SaaS Login:** `catalog/controller/api/oemr.php` - login()
- **MedEx SaaS Token Lookup:** `catalog/model/account/api.php` - getApiByToken()
- **MedEx Auth Helper:** `api/auth_helper.php` - medex_authenticate()

### Controllers That Use Token Auth
- `catalog/controller/information/TM.php` - isAuthenticated() ✅
- `catalog/controller/information/SmsHub.php` - isAuthenticated() ✅
- `catalog/controller/pdf/filler.php` - authenticate() ✅ (Fixed 2026-02-14)

---

## Architecture Overview

```
OpenEMR (EHR)                        MedEx SaaS (medexbank.com)
├── oe-module-medex/                 ├── cart/upload/ (OpenCart 2.x)
│   ├── src/MedExAPI.php             │   ├── api/           (REST APIs)
│   ├── admin/                       │   ├── sabre/         (CalDAV Server)
│   └── public/                      │   └── catalog/       (Frontend)
│                                    │
└── Makes API calls to ─────────────►└── Returns data
```

**KEY INSIGHT:** These are TWO SEPARATE SYSTEMS. The OpenEMR module is a client that calls the MedEx SaaS API. Don't confuse them.

---

## 🌐 Development URL Configuration

### The Problem
Secure chat links, PDF routes, and other generated URLs were using production domain (`https://medexbank.com`) even during local development, making it impossible to test against localhost.

### The Solution: Module Bootstrap Initialization

**Location:** `openemr.bootstrap.php` (lines 17-28)

The module's bootstrap now automatically initializes `$GLOBALS['medex_base_url']` on module load:

```php
// Initialize MedEx base URL for development/testing
if (!isset($GLOBALS['medex_base_url'])) {
    $GLOBALS['medex_base_url'] = 'http://localhost';
    error_log('[MedEx] Initialized medex_base_url to: ' . $GLOBALS['medex_base_url']);
}
```

### Configuration Priority (First Wins)
1. **Pre-set global** - Set `$GLOBALS['medex_base_url']` before module loads (production override)
2. **Bootstrap initialization** - Module sets to `http://localhost` if not already set
3. **Code fallback** - Individual files use `$GLOBALS['medex_base_url'] ?? 'https://medexbank.com'`

### Where URLs Are Generated
All files now use `$GLOBALS['medex_base_url']` consistently:
- **Secure Chat Links:** `secure_chat.php` line 66 → Line 143 builds `http://localhost/chat/secure/{token}`
- **PDF Routes:** `pdf.php` line 69 → Builds `http://localhost/cart/upload/` for PDF generation
- **TeleHealth URLs:** `telehealth.php` line 67 → Builds `http://localhost/cart/upload/` for telehealth
- **Calendar APIs:** `calendar_feeds.php` lines 208, 314 → Builds `http://localhost/cart/upload/api/calendar_feeds.php`

### For Development
**No configuration needed.** Just run the module and URLs default to `http://localhost`.

### For Production Override
Set the global before module bootstrap:
```php
// In a pre-bootstrap file or configuration
$GLOBALS['medex_base_url'] = 'https://your-production-domain.com';
```

### Variable Naming Consistency
**IMPORTANT:** Do NOT use `$GLOBALS['medex_bank_url']` - this is deprecated.

Always use `$GLOBALS['medex_base_url']` with proper fallback:
```php
$medexUrl = $GLOBALS['medex_base_url'] ?? 'https://medexbank.com';
```

All public/*.php files have been standardized to use only the new variable name. If you need to add URL generation code, follow this pattern.

---

## 📡 MEDEX REAL-TIME ARCHITECTURE (CRITICAL)

### How MedEx Achieves < 1 Minute SMS/Email Delivery

**MedEx achieves < 1 minute delivery through event-driven CalendarSync, NOT polling.**

### The Real-Time Flow
```
0:00 - Secretary creates appointment
0:01 - OpenEMR fires AppointmentSetEvent
0:02 - MedEx CalendarListener catches event
0:03 - CalendarSync sends to MedEx server
0:04 - MedEx server receives appointment
0:05 - Campaign engine processes immediately
0:06 - SMS/Email generated and sent
0:30 - Patient receives message
```

### Key Components
- **CalendarSync Service** (`src/CalendarSync.php`) - Real-time appointment sync
- **CalendarListeners** (`src/CalendarListeners.php`) - Event subscribers
- **MedEx Server Campaign Engine** - Processes campaigns in real-time

### Event Listeners Registered
```php
// CalendarListeners.php - Registered in openemr.bootstrap.php
public static function getSubscribedEvents() {
    return [
        'appointment.update' => 'onAppointmentUpdate',
        'appointment.create' => 'onAppointmentCreate',
        'appointment.delete' => 'onAppointmentDelete'
    ];
}
```

### Common Misconceptions (CORRECTED)
- ❌ "MedEx polls every few minutes" → ✅ "MedEx receives appointments immediately via CalendarSync"
- ❌ "Background service handles campaigns" → ✅ "Background service DEPRECATED - MedEx server processes in real-time"
- ❌ "1-minute delivery is impossible" → ✅ "< 1 minute delivery is standard with CalendarSync"

---

## Event-Based Injection Architecture

### Pattern: Loose Coupling
- **Core doesn't know about MedEx** - MedEx knows about core
- **Event-Driven** - All injections happen via Symfony EventDispatcher
- **Graceful Degradation** - Core pages work 100% without module installed

### MessagesPageRenderEvent
**File:** `src/Events/MessagesPageRenderEvent.php`

**Injection Points:**
- `INJECT_NAVIGATION` - Navigation bar after `<head>`
- `INJECT_CONTENT` - Main content for MedEx-specific pages
- `INJECT_SMS_TAB` - SMS Zone tab enhancements
- `INJECT_SCRIPTS` - JavaScript functions
- `INJECT_STYLES` - CSS stylesheets

### PatientTrackerPageRenderEvent
**File:** `src/Events/PatientTrackerPageRenderEvent.php`

**Injection Points:**
- `INJECT_NAVIGATION` - Navigation bar
- `INJECT_STATUS_ICONS` - Reminder status icons in flow board
- `INJECT_MODALITIES` - Communication method indicators
- `INJECT_SCRIPTS` - JavaScript functions (SMS_bot, etc.)
- `INJECT_ONLINE_STATUS` - MedEx online/offline status

### Event Flow
```
Core Page (messages.php) → Dispatch Event → Module Listener → Inject UI
         ↓                                                        ↓
    No MedEx = No Error                               Set HTML Content
         ↓                                                        ↓
    Normal OpenEMR UI                              Return Enhanced UI
```

---

## Calendar Export / CalDAV

### What Calendar Export Actually Is

**Calendar Export is NOT a manual file download feature.**

It uses **CalDAV protocol** with **HTTP Basic Authentication** - the industry standard for calendar sync used by:
- Apple Calendar (iCal)
- Google Calendar  
- Yahoo Calendar
- Outlook
- Thunderbird

### How External Calendars Authenticate

1. User subscribes to CalDAV URL in their calendar app
2. Calendar app prompts for **username/password**
3. Credentials sent via **HTTP Basic Auth over HTTPS** with each request
4. **SabreDAV** validates against database (no PHP session required)
5. Calendar app polls URL periodically for updates

### MedEx CalDAV Server Location
```
/cart/sabre/server.php
```

Uses SabreDAV library with PDO authentication backend:
```php
$authBackend = new Auth\Backend\PDO($pdo);
$authBackend->setRealm('MedExDAV');
```

### DO NOT confuse with:
- `calendar_export_saas.php` - OpenEMR-side endpoint that queries MedEx API (requires OpenEMR session)
- Manual `.ics` file downloads
- Session-based authentication

---

## Subscription Model (Three-Gate System)

### Gate Architecture
```
User Action
    ↓
Gate 1: Is MedEx enabled for this practice?
    ↓ (No → Use OpenEMR built-in feature or show nothing)
Gate 2: Does practice have active subscription?
    ↓ (No → Show upgrade prompt, fallback to free tier)
Gate 3: Is specific feature included in their plan?
    ↓ (No → Show "Upgrade for this feature")
    ↓ (Yes → Show full feature)
```

### Subscription Services

Pricing is served by `api/oemr/pricing` on api.hipaabank.net, cached in `medex_prefs.status`
for 7 days (`pricing_cache_ts` / `pricing_cache` keys). **Source of truth is OpenCart `oc_product`.**

| Service ID | Description | Price (group=1) | Price (group=3 DEMO) | Scope | OC product_id | Recurring plan |
|------------|-------------|-----------------|----------------------|-------|---------------|----------------|
| `appointment_reminders` | SMS/Email/Voice reminders | $9.95/mo | $0.00 | Per provider | 54 | recurring_id=122 |
| `secure_chat` | HIPAA-compliant messaging | $4.95/mo | $0.00 | Per practice | 75 | recurring_id=125 |
| `calendar_export` | CalDAV/iCal calendar export | $4.95/mo *(base price only — no group=1 plan)* | $0.00 | Per practice | 69 | recurring_id=126 (DEMO only) |
| `calendar_ai` | AI Scheduling Assistant | $4.95/mo *(base price only — no group=1 plan)* | $0.00 | Per practice | 70 | recurring_id=126 (DEMO only) |
| `calendar_full` | Full embedded calendar view | $4.95/mo | — | Per practice | — | — |
| `calendar_ai` | AI scheduling assistant | $14.95/mo | Per provider | 70 |
| `pdf_management` | PDF forms & document mgmt | $9.95/mo | Per practice | 76 |

**⚠️ WRONG PRICES BUG HISTORY:** OpenCart had placeholder products P8/P9/P14/P15 all priced at $49.
Fixed Feb 2026 by updating `oc_product.price` for product_ids 69/70/75/76 via kubectl exec PHP.
If prices ever show as $49 again, the placeholder prices came back — check OpenCart DB.

**Cache bust command** (run in OpenEMR DB when prices change on server):
```sql
UPDATE medex_prefs SET status=JSON_SET(IFNULL(status,'{}'), '$.pricing_cache_ts', 0);
```

**Note:** `calendar_export` and `calendar_view` refer to the same service — the service key
used by the login API is `calendar_export`.

### Fallback Behavior (CRITICAL)
- **Subscription fails** → Return to OpenEMR calendar (always works)
- **API timeout** → Use cached subscription data (30-minute cache)
- **Network error** → Gracefully degrade, never block user

### Login API Returns Enabled Services
```json
{
  "success": true,
  "token": "abc123...",
  "practice": { "P_PID": 10421, "P_name": "Demo Clinic" },
  "enabled_services": ["appointment_reminders", "calendar_view", "pdf_management"]
}
```

---

## SSO Implementation

### Token Generation (MedEx SaaS)
```php
// In catalog/controller/api/sso.php
$data = [
    'practice_id' => $practice_id,
    'user_id' => $user_id,
    'timestamp' => time(),
    'nonce' => bin2hex(random_bytes(16))
];
$signature = hash_hmac('sha256', json_encode($data), $secret_key);
$token = base64_encode(json_encode(['data' => $data, 'sig' => $signature]));
```

### Token Validation (MedEx SaaS)
```php
// In any authenticated page
$decoded = json_decode(base64_decode($token), true);
$expected_sig = hash_hmac('sha256', json_encode($decoded['data']), $secret_key);
if (!hash_equals($expected_sig, $decoded['sig'])) {
    throw new InvalidTokenException('Invalid signature');
}
if (time() - $decoded['data']['timestamp'] > 3600) {
    throw new ExpiredTokenException('Token expired');
}
```

### SSO Flow for Iframe Embedding
```
OpenEMR Module           MedEx SaaS
     ↓
Request SSO Token  ──────────►  Generate signed token
     ↓                                  ↓
Receive token      ◄──────────  Return token
     ↓
Load iframe with token ─────►  Validate & auto-login
```

---

## Socket-Based Calendar (High Performance)

### Why Socket Instead of REST
- **< 100ms response times** vs 200-500ms for HTTP
- **No HTTP overhead** - direct PHP communication
- **Connection pooling** - reuse connections
- **Ideal for high-frequency operations** like calendar queries

### Socket Server Location
```
/cart/upload/socket_server.php
```

### Key Components
- `SocketServer.php` - Main server handling connections
- `CalendarSocketHandler.php` - Calendar-specific operations
- Unix socket at `/tmp/medex_calendar.sock`

### Usage Pattern
```php
$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
socket_connect($socket, '/tmp/medex_calendar.sock');
socket_write($socket, json_encode(['action' => 'get_appointments', 'date' => '2026-02-01']));
$response = socket_read($socket, 65536);
socket_close($socket);
```

---

## Key Files

### OpenEMR Module (`oe-module-medex/`)
- `src/MedExAPI.php` - Main API client, handles authentication
- `admin/api/get_subscriptions.php` - Subscription management UI
- `admin/process_subscription.php` - Handles subscription changes
- `public/calendar_export_saas.php` - Queries MedEx for calendar data

### MedEx SaaS (`cart/upload/`)
- `api/subscriptions/process.php` - Subscription processing
- `catalog/controller/api/login.php` - Login API, returns `enabled_services`
- `sabre/server.php` - **CalDAV server endpoint**
- `catalog/controller/information/TM.php` - Communication Hub / SMS Bot
- `catalog/controller/information/clinicalreminders.php` - Clinical Reminders
- `catalog/model/catalog/campaigns.php` - Campaign processing logic

---

## Database Tables

### Module Tables (OpenEMR)
```sql
-- Patient recalls (ALL users, not just MedEx)
medex_recalls (r_ID, r_PRACTID, r_pid, r_eventDate, r_facility, r_provider, r_reason, r_created)

-- Outgoing messages/actions (ALL users)
medex_outgoing (msg_uid, msg_pid, msg_pc_eid, campaign_uid, msg_date, msg_type, msg_reply, msg_extra_text, medex_uid)

-- MedEx-specific preferences
medex_prefs (MedEx_id, ME_username, ME_api_key, ME_facilities, ME_providers, ME_hipaa_default_override...)

-- Custom icons
medex_icons (campaign_uid, msg_type, msg_reply)
```

### Table Rename Plan (Phase 3)
```sql
-- Planned renames for vendor-neutral naming:
RENAME TABLE `medex_recalls` TO `patient_recalls`;
RENAME TABLE `medex_outgoing` TO `recall_board_actions`;
-- medex_prefs stays (module-specific)
-- medex_icons stays (module-specific)
```

---

## Modernized Module Structure

### Service Classes (PHPStan Level 6)
```
src/API/
├── API.php (facade for backward compatibility)
├── MedExClass.php (main coordinator)
├── Client/
│   └── HttpClient.php (95 lines)
├── Services/
│   ├── BaseService.php (29 lines)
│   ├── PracticeService.php (258 lines) - Practice sync
│   ├── CampaignService.php (412 lines) - Campaign/event types
│   ├── EventsService.php (1,089 lines) - Message generation
│   ├── DisplayService.php (459 lines) - UI rendering
│   ├── CallbackService.php (387 lines) - Incoming callbacks
│   ├── LoggingService.php (165 lines) - Debug logging
│   └── SetupService.php (362 lines) - Setup wizard
└── Exceptions/
    └── InvalidDataException.php
```

### All SQL Modernized
| Legacy Function | Modern Replacement |
|----------------|-------------------|
| `sqlQuery()` | `QueryUtils::fetchRecords()[0]` |
| `sqlStatement()` | `QueryUtils::sqlStatementThrowException()` |
| `sqlFetchArray()` | `QueryUtils::fetchRecords()` |

---

## MedExBank AI API Endpoints

### No-Show Prediction
```
POST /api/ai/predict-noshow
Request: { appointments: [...], history: [...] }
Response: { predictions: [{ appointment_id, risk_score, factors: [...] }] }
```

### Template Suggestions
```
POST /api/ai/suggest-templates
Request: { campaign_type, patient_demographics, practice_preferences }
Response: { suggestions: [{ template, effectiveness_score, reasoning }] }
```

### Reschedule Suggestions
```
POST /api/ai/suggest-reschedule
Request: { cancelled_appointment, patient_preferences, available_slots }
Response: { suggestions: [{ slot, match_score, reasoning }] }
```

### Revenue Insights
```
GET /api/ai/revenue-insights?practice_id={id}&period={period}
Response: { insights: [...], recommendations: [...], projected_impact }
```

**KEY:** All AI intelligence lives on MedExBank. OpenEMR module only receives predictions.

---

## Development Environment

### Production K8s (Primary Targets)
- **OpenEMR:** `https://emr.hipaabank.net` — Linode LKE, namespace `openemr`
- **MedEx API:** `https://api.hipaabank.net` — Linode LKE, namespace `medex`
- **Cluster:** `lke87588-ctx` (ALL kubectl commands = production, no staging)

### Local Mac (Code Editing + Git Only)
- Files are edited locally at `/Users/ray/projects/openemr/` and `/Users/ray/projects/medex-local/web_full/`
- Local Docker containers (`openemr-8-0-1-dev-openemr-1`, `medex-localhost-80-app-1`) exist but are **NOT the primary test target**
- Local Docker URL if needed: OpenEMR `http://localhost:8300/`, MedEx `http://localhost/cart/upload/`

### Deploy Workflow
```
Edit on Mac → git commit → deploy to k8s
```
- **OpenEMR module files:** `cd /Users/ray/projects/openemr && ./deploy_to_pod.sh --module`
- **MedEx API (web_full changes) — HOTPATCH FIRST:**
  ```bash
  cd /Users/ray/projects/medex-local/k8s
  bash hotpatch-live.sh push
  ```
  This syncs changed PHP/TPL files directly to the running pod **without a Docker rebuild**.
  Use this for ALL iterative development changes.

- **Docker rebuild is ONLY for finished releases** — not for individual file changes during development:
  ```bash
  docker buildx build --platform linux/amd64 -t ophthal/latest:medex-api-v1 -f Dockerfile . --push
  kubectl rollout restart deployment/medex-api -n medex
  ```
  Rebuild when: shipping a versioned release, adding system packages, or changing the Dockerfile.

- **Remote customers** are NOT in k8s. Any code change must work for remote OpenEMR instances
  (external URLs). The internal k8s service URL only applies to hipaabank.net hostnames.

### Database Access
- **MedEx DB:** `127.0.0.1:3306` (via SSH tunnel in medex-api pod), user=`webserver`, pass=`Budd2833a`, DB=`HIPAA`
- **OpenEMR DB:** `openemr-mysql` service, user=`openemr`, pass=`0p3nEmr!DbPw2026`, DB=`openemr`
- **SSH Key (medexbank.com ISPConfig):** `/Users/ray/.ssh/id_rsa_Sftp`

---

## Phase 3 Implementation Guide (CRITICAL)

### Step 1: Create CommunicationService.php
**Location:** `/library/PatientCommunication/CommunicationService.php`

**Purpose:** Determine available communication modalities for patients (used by Flow Board)

```php
<?php
namespace OpenEMR\Services\PatientCommunication;

class CommunicationService
{
    public static function getAvailableModalities(array $patient): array
    {
        $modalities = [
            'SMS' => false,
            'AVM' => false,
            'EMAIL' => false,
            'SMS_icon' => 'fa-comment-slash',
            'AVM_icon' => 'fa-phone-slash',
            'EMAIL_icon' => 'fa-envelope-slash'
        ];

        // Check SMS
        if (!empty($patient['phone_cell']) && ($patient['hipaa_allowsms'] ?? '') != 'NO') {
            $modalities['SMS'] = true;
            $modalities['SMS_icon'] = 'fa-comment';
        }

        // Check AVM (voice)
        if ((!empty($patient['phone_home']) || !empty($patient['phone_cell'])) &&
            ($patient['hipaa_voice'] ?? '') != 'NO') {
            $modalities['AVM'] = true;
            $modalities['AVM_icon'] = 'fa-phone';
        }

        // Check EMAIL
        if (!empty($patient['email']) && ($patient['hipaa_allowemail'] ?? '') != 'NO') {
            $modalities['EMAIL'] = true;
            $modalities['EMAIL_icon'] = 'fa-envelope';
        }

        return $modalities;
    }
}
```

### Step 2: Modify messages.php

**Remove these MedEx-specific lines:**
- Line 27: `require_once "$srcdir/MedEx/API.php";`
- Lines 39-50: MedEx initialization and login
- Lines 103-106: MedEx navigation
- Lines 110-137: MedEx page handlers
- Lines 192-196: SMS Zone tab
- Lines 788-801: SMS Zone content
- Lines 1056-1074: SMS_direct function
- Lines 865-874: SMS Zone JavaScript

**Add event dispatch:**
```php
// Dispatch event for module injection
$event = new MessagesPageRenderEvent(MessagesPageRenderEvent::INJECT_NAVIGATION, $_REQUEST);
$GLOBALS['kernel']->getEventDispatcher()->dispatch($event, 'messages.page.render');
echo $event->getContent();
```

### Step 3: Modify patient_tracker.php

**Remove MedEx require:**
- Line 26: `require_once "$srcdir/MedEx/API.php";`

**Add event dispatch for status icons:**
```php
// Get module-injected status icons
$event = new PatientTrackerPageRenderEvent(
    PatientTrackerPageRenderEvent::INJECT_STATUS_ICONS,
    $appointment
);
$GLOBALS['kernel']->getEventDispatcher()->dispatch($event, 'patient_tracker.page.render');
$reminder_icons = $event->getContent();
```

### Step 4: Delete /library/MedEx/

After core files use services/events:
```bash
rm -rf library/MedEx/
```

---

## Core Files Using library/MedEx/API.php

### 1. `/interface/main/messages/save.php`
**MedEx Usage:**
- Line 161: `$MedEx->events->getAge($result['DOB'])` - **Used even without MedEx**
- Line 205: `$MedEx->events->save_recall($_REQUEST)` - **Core recall functionality**
- Line 211: `$MedEx->events->delete_recall()` - **Core recall functionality**

**Fix:** Use `RecallService::getAge()`, `RecallService::saveRecall()`, `RecallService::deleteRecall()`

### 2. `/interface/main/messages/messages.php`
**MedEx Usage:**
- Lines 41-50: Conditional MedEx login
- Line 44: `$MedEx->display->SMS_bot($result)` - MedEx SMS bot UI

**Fix:** Event-based injection, remove conditional MedEx code

### 3. `/interface/patient_tracker/patient_tracker.php`
**MedEx Usage:**
- Uses `possibleModalities()` for communication icons

**Fix:** Use `CommunicationService::getAvailableModalities()`

---

## PHP 8.4 Compatibility Notes

MedEx SaaS runs on **PHP 8.4**. Key compatibility fixes already applied:

### Database Driver (`system/library/db/mysqli.php`)
- Check if `$query` is `\mysqli_result` before fetching rows

### Deprecated Functions Replaced:
- `money_format()` → `NumberFormatter`
- `create_function()` → anonymous functions
- `strftime()` → `date()`
- `int($var)` → `(int)$var` type casting

### Debugging Blank Pages
The system defaults to `error_reporting(0)` in `system/startup.php`. If you encounter a blank page:
1. Temporarily enable `E_ALL` and `display_errors` in root `index.php`
2. Check `/tmp/` for log output (not `/var/log/medex/` which may not exist)

---

## Deprecated Admin Pages (SaaS-First)

### Deprecated (Hosted on MedExBank.com now)
- `admin/splash.php` - Marketing page → `MedExAPI::getSaaSUrl('register')`
- `admin/onboarding.php` - Setup wizard → SaaS dashboard
- `admin/register.php` - Account registration → SaaS registration

### What Stays Local
- `public/status.php` - Connection status display in modal
- `public/help.php` - Quick help popup
- `public/callback.php` - API callbacks from SaaS
- `public/recall_board.php` - Recall Board integration
- `public/ajax*.php` - AJAX handlers for local data
- `src/` - API and service classes

---

## Communication Hub (TM Module)

### Key Files
- `catalog/controller/information/TM.php` - Main controller
- `catalog/view/theme/default/template/information/TM.tpl` - Dashboard view
- `catalog/view/theme/default/template/information/TM_large.tpl` - Full hub view
- `catalog/view/theme/default/template/information/TM_hub.tpl` - Chat hub

### Channels
- **SMS** - Standard text messaging via Plivo/Telnyx
- **Secure Local Chat** - HIPAA-compliant local messaging (`msg_carrier_type_id = 'LOCAL'`)
- **WhatsApp/Signal** - UI placeholders ready, needs API integration

### Patient Portal Chat
- `catalog/controller/information/chat_patient.php` - Token-based patient access
- `catalog/view/theme/default/template/information/chat_patient.tpl` - Patient chat UI
- Uses `p_access_token` in `hipaa_cal_people` table for auth

---

## Clinical Reminders / Campaigns

### Key Tables
- `hipaa_campaigns` - Campaign events (has E_MID linking to messages)
- `hipaa_messages` - Message content (M_body field for TTS text)
- `hipaa_campaigns_view` - View joining campaigns + messages + customers

### TTS (Text-to-Speech)
- TTS text saved to `M_body` field in `hipaa_messages`
- Edit page: CKEditor must be conditionally disabled for AVM+TTS (not EMAIL)
- Check `edit_CR.tpl` line ~359 for conditional class, line ~1193 for conditional CKEDITOR.replace()

### GoGreen Campaign First Sync
- `first_sync:` prefix in `E_instructions` prevents spam on activation
- `loadAppts()` marks existing appointments as "Done" instead of queuing

---

## Provider Service Flags

### New Table: `hipaabank_provider_services`
```sql
CREATE TABLE `hipaabank_provider_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `P_PID` int(11) NOT NULL COMMENT 'Practice ID',
  `P_PSID` varchar(50) NOT NULL COMMENT 'Provider ID from OpenEMR',
  `service_type` varchar(50) NOT NULL COMMENT 'appointment_reminders, recall, etc',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_provider_service` (`P_PID`, `P_PSID`, `service_type`)
);
```

### Customer Types
- `legacy` - Old customers, use global `P_active` flag
- `module` - New customers, use per-service provider flags

---

## PDF Template Manager (iframe Integration)

When loaded inside OpenEMR iframe:
- Use `postMessage` for navigation, not direct `window.location`
- Message format: `{ action: 'navigate', page: 'editor', template_id: 123 }`
- Add `return false` to onclick handlers to prevent default navigation
- Hide standalone navigation when `window.self !== window.top`

---

## Testing Requirements

### Test 1: Core Without Module
- Disable MedEx module
- Navigate to Messages → Recalls
- Create/edit/delete recalls ✅
- Print postcards/labels ✅
- Navigate to Patient Tracker
- Verify communication icons display ✅
- Verify no PHP errors ✅

### Test 2: Core With Module
- Enable MedEx module
- Verify all core functionality still works ✅
- Verify MedEx navigation appears ✅
- Verify SMS Zone tab appears ✅
- Test MedEx-specific features ✅

### Test 3: Data Migration
- Backup database
- Run migration script
- Verify all data preserved ✅
- Verify core functionality works ✅
- Verify module functionality works ✅

---

## Debugging Tips

### Check MedEx API / OpenEMR PHP errors (k8s — primary target)
```bash
# OpenEMR pod PHP errors:
kubectl logs -n openemr -l app=openemr --tail=100 -f

# MedEx API pod PHP errors:
MX_POD=$(kubectl get pods -n medex -l app=medex-api --no-headers -o custom-columns=':metadata.name' | head -1)
kubectl exec -n medex "$MX_POD" -c medex-api -- tail -100 /var/log/apache2/error.log
```

### Check subscription status (k8s)
```bash
curl "https://api.hipaabank.net/api/subscriptions.php?practice_id=10421"
```

### Database queries
```sql
-- Check subscriptions
SELECT * FROM medex_subscriptions WHERE practice_id = 10421;

-- Check TTS campaign content
SELECT c.C_UID, c.E_MID, m.M_body FROM hipaa_campaigns c
JOIN hipaa_messages m ON c.E_MID = m.MID WHERE c.C_UID = 2692;

-- Check recalls
SELECT * FROM medex_recalls WHERE r_pid = 123;

-- Check CalendarSync status
SELECT * FROM medex_prefs WHERE MedEx_id = 10421;
```

---

## ⛔ COMMON AI MISTAKES - DO NOT REPEAT

### 1. MAKING ASSUMPTIONS WITHOUT CODE EVIDENCE

- **WRONG:** "MedEx uses webhooks and real-time streaming to handle thousands of customers efficiently"
- **RIGHT:** "I don't know - let me search for the actual implementation"

**RULE:** If you don't know, say "I don't know" rather than making up an answer.

### 2. Authentication Pattern Assumptions
- **WRONG:** Assuming session-based auth for external calendar apps
- **RIGHT:** CalDAV uses HTTP Basic Auth, OAuth, or API tokens depending on use case
- **ACTION:** SEARCH for existing auth implementations before suggesting new ones

### 3. Not Checking Existing Code
- **WRONG:** Proposing new implementations without searching codebase
- **RIGHT:** SabreDAV CalDAV server already exists at `/cart/sabre/server.php`
- **ACTION:** Always `grep_search` or `file_search` FIRST

### 4. Confusing Service Keys
- **WRONG:** Treating `calendar_export` and `calendar_view` as different services
- **RIGHT:** They're aliases for the same service

### 5. Two-System Confusion
- **WRONG:** Editing MedEx SaaS code expecting it to affect OpenEMR
- **RIGHT:** They're separate codebases communicating via REST API

### 6. Using `sed` on Remote Servers
- **WRONG:** Using `sed -i` to edit PHP files remotely (creates syntax errors)
- **RIGHT:** Download file → edit locally with Python → upload once verified

### 7. Hanging SSH Commands
- **WRONG:** Running background SSH with grep/tail that never completes
- **RIGHT:** Use direct queries, download files, or set timeouts

### 8. Over-Simplifying Controllers
- **WRONG:** Removing database connectivity to "fix" issues
- **RIGHT:** Keep original controller logic, fix specific compatibility issues

### 9. Not Testing Before Asking User
- **WRONG:** Making changes and immediately asking user to verify
- **RIGHT:** Query database, check file uploads, test endpoints FIRST

### 10. Inventing Real-Time Architectures
- **WRONG:** Assuming "polling", "webhooks", "websockets" without code evidence
- **RIGHT:** MedEx uses event-driven CalendarSync (documented in this file)

### 11. Not Committing Work
- **WRONG:** Making many changes without git commits, then losing context
- **RIGHT:** Commit after EVERY significant change to BOTH repos
- **RESULT OF FAILURE:** Work erased 3 times in previous sessions

### 12. Inconsistent Authentication
- **WRONG:** Some pages use legacy login, some use token-based, some have nothing
- **RIGHT:** Follow ONE consistent pattern across ALL pages
- **CURRENT STATE:** Authentication is BROKEN and INCONSISTENT - needs audit

### 13. Working Fast Without Structure
- **WRONG:** Writing code quickly without understanding the big picture
- **RIGHT:** Read CLAUDE.md, understand architecture, THEN implement
- **RULE:** Slower, correct work > fast, broken work

---

## ⚠️ CRITICAL LESSON: Evidence-Based Development

### DO NOT MAKE ASSUMPTIONS
- ❌ **NEVER** speculate about how systems work without code evidence
- ❌ **NEVER** invent architectures (webhooks, websockets, real-time) without finding actual implementation
- ❌ **NEVER** assume efficiency patterns without seeing the actual code

### ALWAYS VERIFY WITH CODE
- ✅ **ALWAYS** grep/search for actual implementation before describing how something works
- ✅ **ALWAYS** read the actual code files before making architectural claims
- ✅ **ALWAYS** look for evidence in comments, function names, and actual implementations

### CONSEQUENCES OF MAKING ASSUMPTIONS
- Leads to incorrect implementation suggestions
- Wastes development time on non-existent features
- Creates technical debt based on fantasy architectures
- Undermines trust in AI recommendations

**⚠️ STICK TO WHAT THE CODE ACTUALLY SHOWS.**

---

## Live Deployment Information

### Live Site Path
```
/var/www/clients/client5/web89/web/cart/upload/
```

### SSH Access
```bash
ssh -i /Users/ray/.ssh/id_rsa_Sftp rmagauran@medexbank.com
```

### Config Paths
- **Local Docker:** `/var/www/cart/upload/`
- **Live ISPConfig:** `/var/www/clients/client5/web89/web/cart/upload/`

---

## Reference Documents (For Deep Dives)

All information is consolidated here, but for detailed implementation steps, see:

| Document | Purpose |
|----------|---------|
| `MASTER_PROJECT_GUIDE.md` | Complete project context (546 lines) |
| `FINAL_PR_PLAN.md` | Implementation details (648 lines) |
| `ARCHITECTURE.md` | Event-based architecture design (417 lines) |
| `PHASE2_SUMMARY.md` | Phase 2 implementation details (807 lines) |
| `IMPLEMENTATION_GUIDE.md` | Step-by-step core file changes (595 lines) |
| `LIBRARY_INTEGRATION_ANALYSIS.md` | Core dependency analysis (412 lines) |
| `MODERNIZATION_COMPLETE.md` | Module modernization status (398 lines) |
| `TABLE_RENAME_PLAN.md` | Database rename strategy (274 lines) |
| `MIGRATION_AUDIT.md` | API migration audit (219 lines) |
| `SUBSCRIPTION_MODEL.md` | Three-gate licensing (316 lines) |
| `SSO_IMPLEMENTATION_SPEC.md` | Token auth details (326 lines) |
| `MEDEXBANK_API_SPEC.md` | AI API endpoints (353 lines) |
| `SOCKET_CALENDAR_IMPLEMENTATION.md` | High-performance calendar (245 lines) |
| `CALENDAR_README.md` | Calendar features/API (222 lines) |
| `docs/MEDEx_MIGRATION_GUIDE.md` | Core file modifications (322 lines) |
| `docs/MEDEx_CORE_REMOVAL_GUIDE.md` | Step-by-step removal (242 lines) |
| `sql/README.md` | Database scripts/HIPAA compliance |
| `admin/DEPRECATED_PAGES.md` | Deprecated local pages |
| `WORK_LOG.md` | Change tracking, lessons learned |
| `PROJECT-STATUS.md` | Current phase status |

---

## For Future AI Agents

**Before answering questions about authentication or external integrations:**

1. **SEARCH the codebase** for existing implementations
2. **READ related files** completely, not just snippets
3. **READ THIS ENTIRE FILE** before doing anything
4. **ASK for clarification** if the architecture isn't clear
5. **DON'T assume** standard web patterns apply to all scenarios
6. **TEST changes** before asking the user to verify
7. **Document failures** in this file for future agents

CalDAV, OAuth, API tokens, and HTTP Basic Auth are all valid authentication methods depending on the use case. Don't default to session-based thinking.

---

## Session Cost Tracking

Failed AI sessions have cost the user significant money. Track your token usage and avoid:
- Hanging commands that never complete
- Repeated failed attempts at the same approach
- Making the same mistake multiple times in one session

If something isn't working after 2-3 attempts, **STOP and ASK** for guidance.

---

## Quick Start Checklist for New AI Agents

1. ✅ Read this entire CLAUDE.md file (GitHub Copilot: you must be told to do this — it does NOT happen automatically)
2. ✅ Understand: **Production = k8s on Linode LKE. Local Mac = git storage + code editing only.**
3. ✅ Understand: `emr.hipaabank.net` = OpenEMR, `api.hipaabank.net` = MedEx API — these are THE deployment targets
4. ✅ Understand: `web_full/` changes require Docker image rebuild + k8s rollout restart — NOT instantly live
5. ✅ Understand: Phase 3 NOT STARTED - need CommunicationService.php
6. ✅ Understand: Two separate systems (OpenEMR module + MedEx SaaS)
7. ✅ Understand: Event-based injection (core doesn't know about MedEx)
8. ✅ Understand: CalendarSync for real-time delivery (not polling)
9. ✅ Understand: CalDAV for calendar export (not session auth)
10. ✅ Understand: Three-gate subscription model with fallback
11. ✅ **NEVER ASSUME** - always search code first
12. ✅ Core must work 100% without module installed
13. ✅ MedEx module is PROPRIETARY (not GPL)
14. ✅ **NEVER edit `medex-core`** — only edit `medex-local/web_full/`
15. ✅ **ALWAYS use Braintree's embedded interface** for card entry/update flows. Do NOT build or keep custom card-entry UX when Braintree's embedded UI is available.

---

## Fix Log 2026-03-02 — Subscription Flow for DEMO Accounts

**Problem**: `process_subscription.php` → HTTP 500 empty body when adding `calendar_full`
for DEMO customer (customer_id=10423, customer_group_id=3, multiplier=0).

**All fixes deployed to k8s OpenEMR pod (get current name with `kubectl get pods -n openemr -l app=openemr`) and committed.**

1. **`create_cart.php`** — corrected service keys: `calendar_ai`, `secure_chat`, `pdf_management`
2. **`process_payment.php`** — $0 bypass (skip nonce when `cart_total <= 0`); `bustServicesCache()` on success
3. **`web_full: oemr.php checkout()`** — deferred nonce, server-side $0 re-verify, `medex_subscriptions` inserts, `FREE-` IDs
4. **`web_full: oemr.php subscribe()`** — `$isFreeCustomer` via `multiplier=0` or group `[3,7]`, skip Braintree, `FREE-` IDs
5. **`src/MedExAPI.php makeRequest()`** — public endpoints load cached DB token and attach `?token=...`
6. **`admin/process_subscription.php`** — `$isFreeCustomer` check from `medex_prefs.status.pricing_cache.pricing_tier` bypasses payment guard
7. **`src/Services/PracticeService.php:57`** — `sqlStatementThrowException($sql, [])` — was missing `[]` second arg → `ArgumentCountError`

### CRITICAL: QueryUtils Always Needs 2 Args
```php
QueryUtils::sqlStatementThrowException($sql, []);
QueryUtils::querySingleRow($sql, []);
QueryUtils::fetchRecords($sql, []);
```

### `medex_prefs.status` JSON Paths
- `status.pricing_cache.pricing_tier.customer_group_id` = `"3"` (DEMO)
- `status.pricing_cache.pricing_tier.multiplier` = `0`
- `status.pricing_cache.pricing_tier.name` = `"DEMO"`
- `status.pricing_cache_group` = `1` ← **NOT** the customer group, misleading field name

### Apache Error Log on Production Pod
```bash
# CORRECT: Use kubectl logs (stdout/stderr) for live PHP errors in OpenEMR pod:
kubectl logs -n openemr -l app=openemr --tail=100

# /var/log/apache2/error.log inside the pod is STALE (only March 2025 entries).
# /var/log/apache2/error.log.1 is also stale — do NOT use these for debugging.
```

---

## Merged From: openemr/CLAUDE.md

---

## Custom Include Paths — The "5-Up Rule" (CRITICAL)

**ISSUE:** AI frequently miscalculates relative paths from `admin/api/` scripts, causing fatal 500 errors.
**LOCATION:** `interface/modules/custom_modules/oe-module-medex/admin/api/`

**REQUIRED PATHS from `admin/api/`:**

| Target File | Relative Path | Depth |
|-------------|---------------|-------|
| `interface/globals.php` | `../../../../../globals.php` | **5 levels up** |
| `library/` (e.g. `options.inc.php`) | `../../../../../../library/options.inc.php` | **6 levels up** |
| `vendor/autoload.php` | `../../../../../../vendor/autoload.php` | **6 levels up** |

**RULE:** When creating or editing files in `admin/api/`, ALWAYS use these exact relative paths. Do NOT guess.

### Standalone Script Requirements

Scripts accessed directly (via AJAX/Fetch) must initialize the OpenEMR session correctly:

```php
// Correct include order and depth
require_once(__DIR__ . '/../../../../../globals.php');

// Security Check — OpenEMR uses 'authUserID', NOT 'authId'
if (empty($_SESSION['authUserID'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}
```

---

## OpenEMR Module Lifecycle Requirements

Custom modules must be **completely self-contained** and install/uninstall cleanly.

### On Install/Enable
- Run migrations in `migrations/` directory
- Create database tables prefixed with module name (`medex_*`)
- Register event listeners via `ModuleManagerListener`
- Add menu items dynamically (not by modifying core files)

### On Disable
- Deactivate any LBF forms created: `UPDATE layout_group_properties SET grp_activity = 0 WHERE grp_form_id = 'LBFmedex_telehealth'`
- Unregister event listeners
- Leave data tables intact (user may re-enable)

### On Uninstall
- Remove all module-created database tables
- Remove any LBF form definitions
- Clean up module-specific globals or registry entries

**Key Principles:** No core file modifications. All changes reversible. Prefixed tables. Clean removal.

---

## OpenEMR Coding Standards

- **PHP:** 8.2+ required. No `declare(strict_types=1)` project-wide.
- **Namespaces:** PSR-4 with `OpenEMR\` prefix for `/src/`. New code goes in `/src/`.
- **Indentation:** 4 spaces. **Line endings:** LF (Unix).
- **Templates:** Twig 3.x (modern), Smarty 4.5 (legacy). Check extension: `.twig`, `.html`, `.php`.

### Commit Messages — Conventional Commits
```
<type>(<scope>): <description>
```
Types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`

### Service Layer Pattern
New services should extend `BaseService`:
```php
namespace OpenEMR\Services;
class ExampleService extends BaseService
{
    public const TABLE_NAME = "table_name";
    public function __construct() { parent::__construct(self::TABLE_NAME); }
}
```

### Running Tests (inside Docker)
```bash
cd docker/development-easy
docker compose exec openemr /root/devtools clean-sweep-tests   # all tests
docker compose exec openemr /root/devtools unit-test
docker compose exec openemr /root/devtools api-test
docker compose exec openemr /root/devtools php-log             # PHP error log
```

### Isolated Tests (no Docker required)
```bash
composer phpunit-isolated        # Run all isolated tests
```

### Code Quality
```bash
composer phpstan          # Static analysis
composer phpcs            # PHP code style check
composer phpcbf           # PHP code style auto-fix
composer code-quality     # All PHP quality checks
npm run lint:js           # ESLint check
```

---

## Merged From: medex-local/CLAUDE.md

---

## ⚠️ kubectl cp BREAKS OpenEMR — Slow Login / 20-Second Delay (RECURRENT)

**Problem:** After `kubectl cp` to the OpenEMR pod, the login page takes 15–20 seconds,
or the MedEx menu disappears / behaves strangely.

**Cause:** `kubectl cp` stamps copied files with the Mac user's UID (502) and mode `600`.
Apache (`apache:apache`) cannot read them. OpenEMR's `ModulesApplication::isFileReadableWithRetry()`
retries loading the unreadable `bootstrap.php` 3× at 5-second intervals → **15-second stall**,
then force-disables the module (`mod_active = 0` in the `modules` table).

**⚡ QUICK DIAGNOSIS:** If login is slow, time it first:
```bash
time curl -sk -o /dev/null -w "TTFB: %{time_starttransfer}s\n" \
  "https://emr.hipaabank.net/interface/login/login.php?site=default"
# ~15s = chmod problem (most likely)
# ~5-6s = last_services_check_ts is NULL (getEnabledServices() network timeout)
# < 2s  = all good
```

**Fix — ALWAYS run immediately after every `kubectl cp` to the OpenEMR pod:**
```bash
# Get current pod name (changes on every rollout/restart — NEVER hardcode):
POD=$(kubectl get pods -n openemr -l app=openemr --no-headers \
  -o custom-columns=':metadata.name' | head -1)

# Fix a specific file:
kubectl exec -n openemr "$POD" -- chmod 644 /path/to/file

# Fix the entire MedEx module directory at once:
kubectl exec -n openemr "$POD" -- chown -R apache:apache \
  /var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex
kubectl exec -n openemr "$POD" -- chmod -R u=rwX,go=rX \
  /var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex
```

**Prevention:** Use `deploy_to_pod.sh` instead of raw `kubectl cp` — it has `chmod 644` built in:
```bash
cd /Users/ray/projects/openemr

./deploy_to_pod.sh --module                    # deploy ALL module PHP files
./deploy_to_pod.sh src/MedExAPI.php /var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex/src/MedExAPI.php
```
Never use bare `kubectl cp` to the OpenEMR pod without following it with `chmod 644`.

**If module was force-disabled (menu gone after deploy):**
```bash
# Check
kubectl exec -n openemr deployment/openemr-mysql -- \
  mysql -h localhost -u openemr -p'0p3nEmr!DbPw2026' openemr \
  -e "SELECT mod_active FROM modules WHERE mod_directory='oe-module-medex';"

# Re-enable
kubectl exec -n openemr deployment/openemr-mysql -- \
  mysql -h localhost -u openemr -p'0p3nEmr!DbPw2026' openemr \
  -e "UPDATE modules SET mod_active=1 WHERE mod_directory='oe-module-medex';"
```

**Verify no broken files remain:**
```bash
POD=$(kubectl get pods -n openemr -l app=openemr --no-headers \
  -o custom-columns=':metadata.name' | head -1)
kubectl exec -n openemr "$POD" -- find \
  /var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex \
  -not -user apache 2>/dev/null
# Should print nothing. If any files appear, chown them.
```

---

## Production MedEx API on K8s — Deploy Process

**Cluster:** Linode LKE `lke87588-ctx`. **Namespace:** `medex`.
**Image:** `ophthal/latest:medex-api-v1` (Docker Hub). **Source:** `web_full/Dockerfile`.

### CRITICAL: Always Build for `linux/amd64`

Mac is `arm64`. K8s nodes are `amd64`. If you `docker build` without `buildx`, image crashes with:
```
exec /usr/local/bin/docker-php-entrypoint: exec format error
```

### Deploy Command
```bash
# 1. Rebuild image for linux/amd64
docker buildx build --platform linux/amd64 \
  -t ophthal/latest:medex-api-v1 \
  -f ~/projects/medex-local/web_full/Dockerfile \
  ~/projects/medex-local/web_full/ \
  --push   # pushes directly to Docker Hub

# 2. Restart deployment
kubectl rollout restart deployment/medex-api -n medex
kubectl rollout status deployment/medex-api -n medex --timeout=180s
```

Or use `cd ~/projects/medex-local/k8s && ./deploy.sh all`

### Production DB Access

The MedEx API pod has TWO containers: `medex-api` (web) + `ssh-tunnel` (DB tunnel).
The SSH tunnel connects `127.0.0.1:3306` inside the pod to `192.168.170.98` (remote MySQL).
**There is no `mysql` CLI in the web container.** Use PHP:

```bash
POD=$(kubectl get pods -n medex -l app=medex-api -o jsonpath='{.items[0].metadata.name}')
kubectl exec -n medex "$POD" -c medex-api -- php -r "
\$db = new mysqli('127.0.0.1', 'webserver', 'Budd2833a', 'HIPAA', 3306);
if (\$db->connect_error) die('Error: ' . \$db->connect_error);
\$r = \$db->query('SELECT customer_id, email, customer_group_id FROM oc_customer WHERE customer_id=10423');
if (\$row = \$r->fetch_assoc()) print_r(\$row);
\$db->close();
"
```

### Docker Container Mounts (local Docker — secondary/optional)

> **Note:** The primary deployment target is k8s (`emr.hipaabank.net` / `api.hipaabank.net`).
> Local Docker is optional convenience only. `web_full/` changes require a full Docker image
> rebuild (`docker buildx`) + `kubectl rollout restart` to be live on k8s.

| Component | Host Path | Container Path |
|-----------|-----------|----------------|
| MedEx SaaS code | `/Users/ray/projects/medex-local/web_full` | `/var/www` in `medex-localhost-80-app-1` (local only) |
| OpenEMR code | `/Users/ray/projects/openemr` | Application root in `openemr-8-0-1-dev-openemr-1` (local only) |
| medex-core | NOT mounted | Changes there are **invisible** everywhere — do not touch |

---

## Service Pricing — OpenCart Product IDs

Pricing shown to users comes from OpenCart `oc_product` table, served by `api/oemr/pricing`.
Cached in `medex_prefs.status` JSON for 7 days (`pricing_cache_ts` + `pricing_cache` keys).

| Service | `product_id` | Group 1 Price | Group 3 (DEMO) | Unit | Recurring Plan |
|---------|-------------|---------------|----------------|------|----------------|
| `appointment_reminders` | 54 | $9.95 | $0.00 | /mo per provider | recurring_id=122 |
| `calendar_export` | 69 | $4.95 *(base only)* | $0.00 | /mo (practice) | recurring_id=126 DEMO only |
| `calendar_ai` | 70 | $4.95 *(base only)* | $0.00 | /mo (practice) | recurring_id=126 DEMO only |
| `secure_chat` | 75 | $4.95 | $0.00 | /mo (practice) | recurring_id=125 |
| `pdf_management` | 76 | $49.00 | $0.00 | /mo (practice) | recurring_id=68 "Base: 15 Providers" |

**DEMO / Group 3 pricing:** All services $0.00 via "Demo Free Plan" `oc_recurring.recurring_id=126`, linked through `oc_product_recurring`. Pricing is NOT from `oc_product_special` (which is empty for these products).

**To verify DEMO group recurring plans:**
```bash
# In medex-api pod:
kubectl exec -n medex $(kubectl get pods -n medex -l app=medex-api --no-headers -o custom-columns=':metadata.name' | head -1) -c medex-api -- \
  php -r "\$db=new mysqli('127.0.0.1','webserver','Budd2833a','HIPAA',3306); \$r=\$db->query('SELECT pr.product_id, r.price, r.recurring_id FROM oc_product_recurring pr JOIN oc_recurring r ON pr.recurring_id=r.recurring_id WHERE pr.customer_group_id=3 ORDER BY pr.product_id'); while(\$row=\$r->fetch_assoc()) echo \$row[\"product_id\"].\" \$\".\$row[\"price\"].\" plan=\".\$row[\"recurring_id\"].PHP_EOL; \$db->close();"
```

**To bust OpenEMR pricing cache:**
```sql
UPDATE medex_prefs SET status=JSON_SET(IFNULL(status,'{}'), '$.pricing_cache_ts', 0);
```

---

## Merged From: medex-local/web_full/cart/upload/CLAUDE.md

---

## ⛔ FAILED SESSION LESSONS — NEVER REPEAT

These are things previous AI agents did that broke the system. Do not do them again.

### NEVER Disable `db_autostart`

Setting `db_autostart = false` in OpenCart config breaks **all** pages silently. The database
connection is required before any controller runs. If you see "why is OpenCart's DB not connected",
DO NOT touch `db_autostart` — this is not the problem. Find the actual query.

### NEVER Use `sed` on Remote PHP Files

`sed -i` on a live PHP file in a running container:
- Corrupts the file if the pattern partially matches
- Leaves the file in a broken state while requests are still serving it
- Has no rollback path (unlike `kubectl cp` which you can undo)

**Always use `kubectl cp` to copy a locally-edited file, followed by `chown/chmod`.**

### NEVER Hardcode MedEx URLs

```php
// ❌ WRONG — breaks dev/staging environments
$url = 'https://MedExBank.com/cart/upload/...';

// ✅ CORRECT
$url = ($GLOBALS['medex_base_url'] ?? 'https://medexbank.com') . '/cart/upload/...';
```

### CalDAV Authentication is HTTP Basic Auth — NOT OpenCart Session

The CalDAV endpoint at `cart/sabre/server.php` uses HTTP Basic Auth against OpenCart's `oc_customer` table.
It does NOT use the standard OpenCart session/cookie auth. Never add session checks to CalDAV controllers.

```php
// The user/password in the calendar URL IS the OpenCart customer email + password
// e.g., webcal://username:password@medexbank.com/cart/sabre/server.php/calendar/
```

### OpenCart Local Dev URL

- **Local:** `http://localhost/cart/upload/`
- **Container:** `medex-localhost-80-app-1`
- **Live:** `/var/www/clients/client5/web89/web/cart/upload/`

### Check Logs After Every Change

```bash
# Local OpenCart errors:
docker exec medex-localhost-80-app-1 tail -100 /var/www/cart/upload/system/storage/logs/error.log

# OpenEMR pod — LIVE PHP errors (use kubectl logs, NOT exec into the pod):
kubectl logs -n openemr -l app=openemr --tail=100
# /var/log/apache2/error.log inside the pod is STALE — only has old entries from March 2025.
# Recent PHP errors appear in kubectl logs stdout/stderr, not in the file.

# Production MedEx API:
POD=$(kubectl get pods -n medex -l app=medex-api -o jsonpath='{.items[0].metadata.name}')
kubectl exec -n medex "$POD" -c medex-api -- tail -100 /var/log/apache2/error.log
# (MedEx API pod error.log IS live unlike OpenEMR pod)
```

---

## ⚠️ getEnabledServices() — NULL Timestamp Causes Page Hang (RECURRENT)

**Problem:** Login page takes 5–6 seconds (not 15 — that's the chmod problem, this is different).

**Cause:** `medex_prefs.status.last_services_check_ts` is `NULL`.
`getEnabledServices()` has a 6-hour TTL check. When `NULL`, TTL always expires → calls
`login(true)` on EVERY page load. `login()` makes a network request to MedExBank with a
5-second timeout. Result: 5+ second delay on every page.

**Quick check:**
```bash
kubectl exec -n openemr deployment/openemr-mysql -- \
  mysql -h localhost -u openemr -p'0p3nEmr!DbPw2026' openemr \
  -e "SELECT JSON_EXTRACT(status,'$.last_services_check_ts') as ts, \
           JSON_EXTRACT(status,'$.enabled_services') as svc \
      FROM medex_prefs LIMIT 1;"
```

**Fix — re-seed the cache:**
```bash
kubectl exec -n openemr deployment/openemr-mysql -- \
  mysql -h localhost -u openemr -p'0p3nEmr!DbPw2026' openemr -e "
UPDATE medex_prefs
SET status = JSON_SET(
  COALESCE(status, '{}'),
  '$.last_services_check_ts', UNIX_TIMESTAMP(),
  '$.enabled_services', JSON_ARRAY(),
  '$.last_services_result', JSON_ARRAY()
) WHERE 1;"
```

**When this happens:** Any time the DB is reset, `medex_prefs` is wiped, or `status` JSON
is cleared. Always run the re-seed after restoring from backup or after any DB migration.

---

## ⚠️ K8s Pod Names Change — NEVER Hardcode Them

Pod names include a random hash (e.g., `openemr-6b4fccb8d9-pzwgr`) and **change on every
rollout restart or node reschedule**. If a previously-working kubectl command suddenly fails
with "pod not found", the pod was replaced.

**Always look up the current pod name dynamically:**
```bash
# OpenEMR pod:
kubectl get pods -n openemr -l app=openemr --no-headers \
  -o custom-columns=':metadata.name' | head -1

# MedEx API pod:
kubectl get pods -n medex -l app=medex-api --no-headers \
  -o custom-columns=':metadata.name' | head -1

# One-liner to set variable:
OE_POD=$(kubectl get pods -n openemr -l app=openemr --no-headers \
  -o custom-columns=':metadata.name' | head -1)
MX_POD=$(kubectl get pods -n medex -l app=medex-api --no-headers \
  -o custom-columns=':metadata.name' | head -1)
```

---

## ⚠️ `sed` in Pods Fails — Use PHP or Copy Locally

Running `kubectl exec -- sh -c "sed -i 's/foo/bar/' file.php"` on files with special
characters (dollar signs, backticks, slashes, quotes) will corrupt or silently fail.

**CORRECT approach for in-pod file edits:**
```bash
# Option A: Edit locally and copy back (always use deploy_to_pod.sh)
# Edit the file on Mac, then:
./deploy_to_pod.sh path/to/file.php /var/www/.../file.php

# Option B: PHP one-liner for simple substitutions
OE_POD=$(kubectl get pods -n openemr -l app=openemr --no-headers \
  -o custom-columns=':metadata.name' | head -1)
kubectl exec -n openemr "$OE_POD" -- php -r "
\$f = '/path/to/file.php';
file_put_contents(\$f, str_replace('old', 'new', file_get_contents(\$f)));
"
kubectl exec -n openemr "$OE_POD" -- chmod 644 /path/to/file.php
```

---

## ⚡ OpenEMR K8s One-Liners (Quick Reference)

```bash
# Current pod names
OE_POD=$(kubectl get pods -n openemr -l app=openemr --no-headers -o custom-columns=':metadata.name' | head -1)
MX_POD=$(kubectl get pods -n medex -l app=medex-api --no-headers -o custom-columns=':metadata.name' | head -1)

# Live PHP errors for OpenEMR
kubectl logs -n openemr -l app=openemr --tail=50 -f

# Live PHP errors for MedEx API
kubectl logs -n medex -l app=medex-api -c medex-api --tail=50 -f

# Deploy all module PHP files (with auto chmod)
cd /Users/ray/projects/openemr && ./deploy_to_pod.sh --module

# Time the login page (acceptance test for page-load fixes)
time curl -sk -o /dev/null -w "TTFB: %{time_starttransfer}s\n" \
  "https://emr.hipaabank.net/interface/login/login.php?site=default"
# Expected: < 2s. ~15s = chmod problem. ~5s = NULL timestamp problem.

# Check/fix module enabled
kubectl exec -n openemr deployment/openemr-mysql -- \
  mysql -h localhost -u openemr -p'0p3nEmr!DbPw2026' openemr \
  -e "SELECT mod_active FROM modules WHERE mod_directory='oe-module-medex';"
kubectl exec -n openemr deployment/openemr-mysql -- \
  mysql -h localhost -u openemr -p'0p3nEmr!DbPw2026' openemr \
  -e "UPDATE modules SET mod_active=1 WHERE mod_directory='oe-module-medex';"

# Re-seed services cache (prevents NULL timestamp hang)
kubectl exec -n openemr deployment/openemr-mysql -- \
  mysql -h localhost -u openemr -p'0p3nEmr!DbPw2026' openemr -e "
UPDATE medex_prefs SET status = JSON_SET(
  COALESCE(status,'{}'),
  '$.last_services_check_ts', UNIX_TIMESTAMP(),
  '$.enabled_services', JSON_ARRAY(),
  '$.last_services_result', JSON_ARRAY()
) WHERE 1;"
```
