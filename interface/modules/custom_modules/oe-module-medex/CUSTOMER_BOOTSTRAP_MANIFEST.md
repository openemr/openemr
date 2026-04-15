# MedEx Customer Bootstrap Manifest

Updated: 2026-04-15

## Goal

Define the smallest customer-facing MedEx package that should ship as the initial local OpenEMR module.

This manifest is intentionally stricter than the current `build_release.sh`.

It answers three packaging questions:

1. What must be present for MedEx to install and start onboarding?
2. What should be delivered later as subscription components?
3. What must never ship in the customer bootstrap ZIP?

## Package Name

Initial package:

- `oe-module-medex-bootstrap`

This is the only package a customer should upload initially.

## Bootstrap Package Contents

These files stay in the initial local package.

### Root files

- `openemr.bootstrap.php`
- `ModuleManagerListener.php`
- `moduleConfig.php`
- `show_help.php`
- `show_help_setup.php`
- `help.php`
- `composer.json`
- `composer.lock`
- `LICENSE.md`
- `README.md`
- `INSTALL.md`
- `HELP.md`

### Bootstrap admin pages

- `admin/index.php`
- `admin/cloud_dashboard.php`
- `admin/help_center.php`
- `admin/manual_config.php`
- `admin/reconnect.php`
- `admin/disconnect.php`
- `admin/reset_connection.php`
- `admin/save_preferences.php`
- `admin/sync_practice.php`

### Bootstrap public pages

- `public/index.php`
- `public/help.php`
- `public/status.php`
- `public/setup.php`
- `public/update.php`
- `public/callback.php`

### Bootstrap source files

- `src/ModuleManagerListener.php`
- `src/MedExAPI.php`
- `src/MedExConfig.php`
- `src/MedExDirectoryManager.php`
- `src/UpdateManager.php`
- `src/CriticalPatchNotifier.php`
- `src/API/Client/HttpClient.php`
- `src/API/Exceptions/InvalidDataException.php`
- `src/API/OEGlobalsBag_polyfill.php`

### Bootstrap migrations and SQL

- `migrations/001_create_migrations_table.php`
- `migrations/002_add_update_cache_columns.php`
- `fix_module_registration.sql`
- `sql/README.md`

### Runtime dependencies to review but currently allowed

- `vendor/autoload.php`

Note:
- Do not ship the full `vendor/` tree by default unless bootstrap runtime proves it is required.
- Preferred end state is either a minimal runtime subset or no bundled vendor code beyond what bootstrap strictly needs.

## Component Packages

These files should not be in the bootstrap ZIP. They should be installed only after entitlement/subscription is known.

### `component-secure-chat`

- `public/secure_chat.php`
- `public/quick_chat.php`
- `public/receive_chat_message.php`
- `public/portal_messages.php`
- `public/portal_redirect.php`
- `admin/api/secure_chat_audit.php`
- `src/Services/MessageReceiveService.php`
- `src/Services/MessageGenerationService.php`

### `component-reminders-campaigns`

- `public/sms_bot.php`
- `public/sms_bot_list.php`
- `public/sms_zone_module.php`
- `public/recall_board.php`
- `public/ajax.php`
- `public/ajax_handler.php`
- `public/ajax/get_campaign_status.php`
- `public/ajax/get_recall_campaign_status.php`
- `public/ajax/save_recall.php`
- `src/Services/RecallsBoardService.php`
- `src/API/Services/CampaignService.php`

### `component-calendar`

- `admin/api/calendar_feeds.php`
- `public/calendar_export_saas.php`
- `public/calendar_feed.php`
- `public/calendar_feeds.php`
- `public/assets/fullcalendar.php`
- `public/calendar/api/events.php`
- `public/calendar/api/templates.php`
- `public/calendar/calendar.js`
- `public/calendar/edit_event_wrapper.php`
- `public/calendar/get_events.php`
- `public/calendar/index.php`
- `public/calendar/license_check.php`
- `public/calendar/set_calendar_preference.php`
- `public/calendar/stream_events.php`
- `public/calendar/templates.php`
- `public/calendar/update_event.php`
- `src/Listeners/CalendarInjectionListener.php`
- `src/Listeners/CalendarInterceptListener.php`
- `src/Services/CalendarService.php`
- `src/API/Services/EventsService.php`
- `src/API/Services/EventsService_Full.php`

### `component-pdf-management`

- `admin/pdf/admin_pdf_advanced_mapping.js`
- `admin/pdf/api_db_schema.php`
- `admin/pdf/api_patient_data.php`
- `admin/pdf/editor.php`
- `admin/pdf/editor_old_standalone.php`
- `admin/pdf/fhir_helper.php`
- `public/api/pdf_data.php`
- `public/pdf.php`
- `admin/pdf/index.php`

### `component-telehealth`

- `public/telehealth.php`
- `migrations/003_create_telehealth_form.php`
- `sql/telehealth_form.sql`
- `src/Services/ModalityService.php`

### `component-openemr-ui-hooks`

- `public/messages_hook.php`
- `public/css/medex_tooltips.css`
- `public/css/patient_tracker_injection.css`
- `public/js/patient_tracker_injection.js`
- `src/Events/Event.php`
- `src/Events/MessagesPageRenderEvent.php`
- `src/Events/PatientTrackerPageRenderEvent.php`
- `src/Events/PatientTrackerRenderEvent.php`
- `src/Listeners/MessagesPageListener.php`
- `src/Listeners/PatientTrackerInjectionListener.php`
- `src/Listeners/PatientTrackerListener.php`
- `src/Listeners/TemplatePageListener.php`
- `recall_buttons_loader.php`
- `provider_checkbox_group.php`

## SaaS Wrapper Files

These may remain locally, but only as thin redirect/iframe/SSO bridge pages. Their current implementations are too large.

- `admin/splash.php`
- `admin/onboarding.php`
- `admin/register.php`
- `admin/register_process.php`
- `admin/onboarding_otp.php`
- `admin/onboarding_validate_url.php`
- `admin/create_cart.php`
- `admin/process_payment.php`
- `admin/process_subscription.php`
- `admin/agreement_sign.php`
- `admin/api/get_overview.php`
- `admin/api/get_subscriptions.php`
- `admin/api/get_subscriptions_content.php`
- `admin/api/get_settings.php`
- `admin/api/get_settings_content.php`
- `admin/api/get_backups.php`
- `admin/api/get_backups_content.php`
- `admin/backups.php`
- `admin/backup_actions.php`
- `admin/download_backup.php`
- `admin/settings.php`
- `public/dashboard.php`
- `public/campaigns.php`

Packaging rule:
- do not ship these as full local business logic in the bootstrap ZIP
- if temporarily needed, replace them with small wrappers and keep only the wrappers

## Never Ship In Customer Bootstrap ZIP

### Backup and editor artifacts

- `*.bak`
- `*.bak*`
- `*_Backup.php`
- `.DS_Store`

### Internal engineering and planning docs

- `ARCHITECTURE.md`
- `CALENDAR_README.md`
- `CLEAN_REMOVAL_PR.md`
- `CORRECTED_PR_PLAN.md`
- `DEPLOYMENT.md`
- `FINAL_PR_PLAN.md`
- `IMPLEMENTATION_GUIDE.md`
- `INTEGRATION_ACTION_PLAN.md`
- `LIBRARY_INTEGRATION_ANALYSIS.md`
- `MASTER_PROJECT_GUIDE.md`
- `MEDEXBANK_API_SPEC.md`
- `MENU_LINKS_ADDED.md`
- `MIGRATION_AUDIT.md`
- `MOCKUP_CAMPAIGN_CONTROLS.md`
- `MODERNIZATION_COMPLETE.md`
- `MODULE_REGISTRATION_FIX.md`
- `OPENEMR_PORTAL_INTEGRATION.md`
- `PHASE2_SUMMARY.md`
- `PROJECT-STATUS.md`
- `PR_SUBMISSION_PLAN.md`
- `SOCKET_CALENDAR_IMPLEMENTATION.md`
- `SSO_IMPLEMENTATION_SPEC.md`
- `SUBSCRIPTION_MODEL.md`
- `TABLE_RENAME_PLAN.md`
- `UNUSED_CODE_AUDIT.md`
- `UPDATE_SYSTEM.md`
- `WORK_LOG.md`
- `BOOTSTRAP_COMPONENT_CLASSIFICATION.md`
- `.AI-CONTEXT`
- `.AI-DISCOVERY`

### Test and standalone tooling

- `tests/`
- `test_integration.sh`
- `standalone/`
- `build_release.sh`
- `modernize_api.php`

### Legacy/support-only pages pending removal

- `admin/cleanup.php`
- `admin/debug_api.php`
- `admin/fix_calendar_subscription.php`
- `admin/fix_url.php`
- `admin/manual_setup.php`
- `admin/onboarding_blocklist.php`
- `admin/update_recharge.php`
- `admin/whatsapp_settings.php`
- `public/enable_first.php`
- `public/status_test.php`
- `backup_recall_board_assets/`

## Immediate Packaging Rule

Until the package builder is rewritten, a customer bootstrap build should be treated as valid only if:

1. it contains all files listed in `Bootstrap Package Contents`
2. it excludes all files listed in `Never Ship In Customer Bootstrap ZIP`
3. it does not include subscription feature files unless intentionally building a component package

## Next Step

Update `build_release.sh` to build:

1. bootstrap ZIP only
2. optional per-component ZIPs
3. explicit deny-list validation before packaging completes
