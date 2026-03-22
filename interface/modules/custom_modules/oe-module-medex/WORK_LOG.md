# MedEx Module Work Log

**Purpose:** Track every change made to prevent data loss and provide memory for future work

**Date Started:** 2026-01-29 17:30

---

## Session: 2026-01-29 Recall Board Rebuild (STARTING)

### Context
- Lost 9 hours of Recall Board finalized work
- Lost Flow Board finalized work
- Previous work was NOT properly saved/committed
- Starting complete rebuild of Recall Board UX to match medexbank.com

### Critical Rules Going Forward
1. ✅ **TEST every change immediately** - verify it works before moving on
2. ✅ **DOCUMENT every change in this log** - what was changed, why, result
3. ✅ **ASK before deleting anything** - never assume something should be removed
4. ✅ **USE medex_icons table** - fetch from database, DO NOT create own icons
5. ✅ **MATCH medexbank.com exactly** - borders, shadows, spacing, colors
6. ✅ **COMMIT frequently** - small commits with clear messages
7. ✅ **CHECK what exists before modifying** - read file first, understand what's there

### Recall Board Requirements (User Specified)

#### Header Changes
- [ ] Two header rows (main headers + checkboxes/print icons row)
- [ ] "Office" column header

#### Office Column
- [ ] MedEx phone icon (from medex_icons table)
- [ ] Calendar icon (MedEx styled)
- [ ] 10px spacing between icons
- [ ] Vertically aligned and centered
- [ ] Checkbox click → Notes textarea focus/highlight

#### Patient Column
- [ ] Age moved AFTER name
- [ ] `white-space: nowrap` on all 3 rows

#### Next Column (TBD which)
- [ ] 2 lines
- [ ] `white-space: nowrap`

#### Contact Info Column
- [ ] Made wider
- [ ] `white-space: nowrap`
- [ ] **First row**: Communication possibility icons (SMS, Email, Phone)
- [ ] Icons centered, 10px padding, vertically aligned
- [ ] Use medex_icons table for icons
- [ ] If not possible: circle with fa-ban (Font Awesome)

#### Status Column
- [ ] Limited height to 4 rows
- [ ] Icons first (top)
- [ ] Events below icons in reverse chronological order (most recent top)
- [ ] Scrollable
- [ ] Scroll to top on load

#### Actions Column
- [ ] Use medex_icons table icons
- [ ] Font Awesome with borders/shadows per MedEx style

### Database Info
- **medex_icons table exists** with 76 icons
- Table structure: i_UID, msg_type, msg_status, i_description, i_html, i_blob
- Icons for: SMS, AVM, EMAIL, POSTCARD
- Statuses: ALLOWED, NotAllowed, SCHEDULED, SENT, READ, CONFIRMED, FAILED, CALL, CALLED, STOP, EXTRA, Other

### Files Involved (Current State)
- `/Users/ray/github/openemr/interface/modules/custom_modules/oe-module-medex/public/js/recall_board_injection.js` - 35,934 bytes, last modified Jan 29 10:20
- `/Users/ray/github/openemr/interface/modules/custom_modules/oe-module-medex/public/ajax.php` - 8,962 bytes, last modified Jan 29 10:20
- `/Users/ray/github/openemr/interface/main/messages/messages.php` - core OpenEMR file

### Current Status
- **WAITING** for user approval to proceed
- **NOT STARTING** any work until supervised
- User needs to be present to prevent mistakes

---

## Session: 2026-02-14 PDF Template Manager - iframe Navigation Fix

### Context
- PDF Template Manager pages (index.html, marketplace.html) in MedEx SaaS
- Loaded via iframe from OpenEMR wrapper (admin/pdf/index.php)
- Navigation links caused "Site ID missing" errors when clicked inside iframe

### Problem Analysis
When MedEx pages were loaded inside OpenEMR iframe:
1. Links navigated directly within iframe, losing OpenEMR session context
2. postMessage used wrong format (`type` instead of `action`)
3. `template_id` was nested in `params` but wrapper expected it at top level
4. Missing `return false` on onclick handlers caused default navigation

### Changes Made

#### MedEx SaaS Side (web_full/pdf/)
- **marketplace.html**:
  - Added `return` to onclick handlers
  - Fixed postMessage: `{action: 'navigate', page, template_id}` format
  - After install, uses `navigateTo()` instead of direct `window.location.href`
  
- **index.html**:
  - Added `return` to onclick handler
  - Fixed postMessage format (was using `type` instead of `action`)
  - `createNewTemplate()` and `editTemplate()` now use `navigateTo()`

#### Both pages:
- Detect iframe context: `const inIframe = window.self !== window.top`
- Hide standalone nav when in iframe (parent wrapper provides nav tabs)
- `navigateTo()` returns false to prevent default link behavior

### OpenEMR Module Side (verified, no changes needed)
- `admin/pdf/index.php` already correctly:
  - Listens for `{action: 'navigate'}` messages
  - Handles `?page=xxx` routing
  - Passes `customer_id` from authenticated session

### User Journey Verified
| Action | Before | After |
|--------|--------|-------|
| Dashboard → Marketplace | Broken | ✓ postMessage |
| Dashboard → Edit template | Broken | ✓ postMessage with template_id |
| Marketplace → My Templates | Broken | ✓ postMessage |
| Install → Edit | Direct href (broken) | ✓ navigateTo() |

### Files Changed
- `medex-local/web_full/pdf/marketplace.html`
- `medex-local/web_full/pdf/index.html`
- `medex-local/medex-core/AI_MAINTENANCE_LOG.md` (documentation)

### Testing Notes
- Works in standalone mode (direct URL access)
- Works in iframe mode (OpenEMR wrapper)
- Customer ID preserved across all navigation
- CSRF not needed for SaaS iframe pages (auth is session-based)

---

## Change Log (will be updated with each change)

### [TIMESTAMP] - [File] - [Description]
(Changes will be logged here as they happen)

---

## Testing Checklist (to be completed for each change)
- [ ] Change made
- [ ] File saved
- [ ] Browser refreshed
- [ ] Functionality verified
- [ ] No console errors
- [ ] Icons match medexbank.com
- [ ] Spacing/alignment correct
- [ ] Change documented in this log
- [ ] Change committed to git

---

## Mistakes to Avoid (Lessons Learned)
1. ❌ Creating own icon HTML instead of using medex_icons table
2. ❌ Making up icon styles instead of matching MedEx
3. ❌ Not testing changes immediately
4. ❌ Overwriting code without understanding what's there
5. ❌ Not documenting what was done
6. ❌ Not committing frequently
7. ❌ Deleting code without asking first
8. ❌ Assuming something should work instead of verifying

---

## Questions to Ask Before Each Change
1. What file am I about to modify?
2. What does it currently contain?
3. What exactly am I changing?
4. Why am I changing it?
5. What could this break?
6. Do I need to ask the user first?
7. How will I test this?
8. Where will I document this?
