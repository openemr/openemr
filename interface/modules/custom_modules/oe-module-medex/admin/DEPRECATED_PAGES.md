# Deprecated Local Pages

As of the SaaS-first architecture refactor, the following pages are **deprecated** and should not be used:

## Deprecated Pages

### `splash.php`
**Reason:** Marketing/sales page now hosted on MedExBank.com
**Replacement:** SaaS registration page at `https://medexbank.com/cart/upload/index.php?route=account/register`

### `onboarding.php`
**Reason:** Multi-step onboarding wizard now hosted on MedExBank.com
**Replacement:** SaaS dashboard handles service configuration

### `register.php`
**Reason:** Account registration now handled by MedExBank.com
**Replacement:** `MedExAPI::getSaaSUrl('register')` with redirect to SaaS

### `settings.php` (partial deprecation)
**Reason:** Full settings UI now on MedExBank.com dashboard
**Keep:** Only local connection settings (API key, Practice ID) if needed for troubleshooting
**Replacement:** `MedExAPI::getSaaSUrl('settings')` opens SaaS settings with SSO

## Migration Path

All buttons that previously linked to these pages now use:
- `MedExAPI::getSaaSUrl('dashboard')` - For registered users
- `MedExAPI::getSaaSUrl('register')` - For new users

## Files Safe to Delete

After testing confirms SaaS flow works:
```
admin/splash.php
admin/onboarding.php
admin/register.php
admin/register_process.php (if registration is SaaS-only)
```

## What Stays Local

These pages remain in the module:
- `public/status.php` - Connection status display in modal
- `public/help.php` - Quick help popup
- `public/callback.php` - API callbacks from SaaS
- `public/recall_board.php` - Recall Board integration
- `public/ajax*.php` - AJAX handlers for local data
- `src/` - API and service classes
