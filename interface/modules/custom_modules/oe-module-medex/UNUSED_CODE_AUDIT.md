# MedEx Module Unused Code Audit
Updated: 2026-04-12

## Goal
Identify module files that are either:
- Safe to remove from customer builds, or
- Potentially valuable but currently unlinked in UI/routes.

## High-Confidence Remove Candidates
- `.DS_Store` files in module tree.
- `src/API/API_Original_Backup.php`
- `src/Listeners/MessagesPageListener.php.bak`
- `src/Services/MessageGenerationService.php.bak`
- `src/Services/MessageReceiveService.php.bak`
- `src/Services/PracticeService.php.bak`
- `src/UpdateManager.php.bak`

Reason: backup/editor artifacts, not runtime dependencies.

## Likely Valuable But Unlinked (Decide: Link or Remove)
- `admin/cleanup.php`
- `admin/fix_url.php`
- `admin/fix_calendar_subscription.php`
- `admin/manual_setup.php`
- `admin/whatsapp_settings.php`
- `public/status_test.php`
- `public/enable_first.php`
- `provider_checkbox_group.php`

Reason: admin/support utilities exist, but no active route/menu linkage found in module references.

## Linked/Active Utilities (Keep)
- `admin/manual_config.php` (referenced by `admin/api/get_overview.php`)
- `public/dashboard.php` (referenced in overview content)
- `public/ajax_handler.php` (used by recall board)
- `public/setup.php` (multiple references)

## Packaging Recommendation
For customer release ZIP:
- Exclude backup files (`*.bak`, `*_Backup.php`).
- Exclude local metadata (`.DS_Store`).
- Keep docs needed by support (`HELP.md`, install/update docs), but do not ship internal planning docs if not customer-facing.

## Next Step
Create a release-prune script to automatically exclude/remove the high-confidence remove set before packaging.

## Smarty Cache Note
- Manual UI reset was intentionally not kept.
- Module now performs an automatic Smarty compiled-cache reset once per module version on load.
