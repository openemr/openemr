# MedEx SaaS Migration Plan (Execution + Recovery)

Last updated: 2026-03-25
Owner: Ray / MedEx
Execution repo: `openemr`
Production web target: `medexbank.com` (`/var/www/clients/client5/web89/web/cart/upload`)

## Non-Negotiable Production Rules
- `MEDEXBANK.COM` is live production for legacy customers. Do not break it.
- `medexbank.com` and `api.hipaabank.net` share the same production MySQL/MariaDB.
- Any schema/data change is treated as shared production impact.

## Migration Goal
- Keep business-critical feature logic and entitlement authority server-side.
- Keep OpenEMR module as a thin connector/UI shell with strict server-verified gating.
- Remove user-facing `api.hipaabank.net` links from module UX.

## Phase Sequence
1. URL/Brand Hardening
- Replace browser-facing `api.hipaabank.net` links with branded host URLs.
- Keep API calls functional server-side.

2. Entitlement Hardening
- For each premium surface, enforce server-verified entitlements (forced refresh path).
- Deny access immediately on cancel/disable (no stale local grants).

3. Service-by-Service Lockdown
- Secure Chat
- SMS Bot
- PDF workflows
- Calendar full/export surfaces
- TeleHealth

4. Ops Safety and Rollback
- Commit frequently with small, scoped units.
- Keep production snapshot history on `medexbank.com` using out-of-web-root git metadata.
- Maintain simple restore commands.

## Commit Cadence (Required)
- Commit after each completed sub-step (target every 15-45 minutes).
- Commit immediately before and after live pod sync.
- Commit message format:
  - `medex: <area> <action>`
  - Example: `medex: enforce server entitlement for sms bot page`

## Production Snapshot Repo (medexbank.com)
Use Git metadata outside public web files:
- Git metadata dir:
  - `/var/www/clients/client5/web89/home/rmagauran/git-meta/medexbank-upload.git`
- Work tree:
  - `/var/www/clients/client5/web89/web/cart/upload`

### Initial Setup
```bash
GIT_DIR="/var/www/clients/client5/web89/home/rmagauran/git-meta/medexbank-upload.git"
WORK_TREE="/var/www/clients/client5/web89/web/cart/upload"

mkdir -p "$(dirname "$GIT_DIR")"
git --git-dir="$GIT_DIR" --work-tree="$WORK_TREE" init
git --git-dir="$GIT_DIR" config user.name "MedEx Production Snapshot"
git --git-dir="$GIT_DIR" config user.email "ops@medexbank.com"
```

### Baseline Snapshot
```bash
git --git-dir="$GIT_DIR" --work-tree="$WORK_TREE" add -A
git --git-dir="$GIT_DIR" --work-tree="$WORK_TREE" commit -m "baseline: production state 2026-03-25"
```

### Snapshot Before/After Any Production Change
```bash
git --git-dir="$GIT_DIR" --work-tree="$WORK_TREE" add -A
git --git-dir="$GIT_DIR" --work-tree="$WORK_TREE" commit -m "checkpoint: <what changed>"
```

### Restore a File Quickly
```bash
git --git-dir="$GIT_DIR" --work-tree="$WORK_TREE" checkout <commit_sha> -- <relative/path/from/upload>
```

## Current Completed Checkpoints
- Branded tutorial/help URL routing in OpenEMR module.
- Server-verified entitlement enforcement added for Secure Chat + manual sync path.
- Admin-facing API hostname references removed in key configuration views.
- Server-verified entitlement enforcement extended to:
  - SMS Bot (`sms_bot.php`, `sms_bot_list.php`)
  - PDF surfaces (`pdf.php`, `public/api/pdf_data.php`)
  - Calendar surfaces (`calendar/index.php`, `calendar/get_events.php`, `assets/fullcalendar.php`, `calendar_export_saas.php`)
  - TeleHealth (`telehealth.php`)

## Next Implementation Steps
1. Re-run targeted smoke tests for all newly gated routes in live OpenEMR UI.
2. Sweep remaining module entry points for stale-cache entitlement checks.
3. Start migration of any high-value workflow logic still local-only toward server authority.
4. Keep production snapshot checkpoints before/after each production-side change.
