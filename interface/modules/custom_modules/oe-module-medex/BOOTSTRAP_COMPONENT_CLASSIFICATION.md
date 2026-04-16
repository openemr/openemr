# MedEx Bootstrap / Component Classification

Updated: 2026-04-15

## Purpose

This is a first-pass file classification for reducing `oe-module-medex` to:

- a small bootstrap module that must exist locally in OpenEMR
- subscription-specific components that should be delivered only when needed
- SaaS-hosted functionality that should remain remote with thin local wrappers
- legacy/dead/support files that should stop shipping and then be deleted

This is not a final deletion list. It is a packaging and refactor map.

## Classification Rules

### Default Bias

- If a file can run on `medex-api`, move it there.
- Keep code in the local OpenEMR module only when OpenEMR itself requires local execution.
- Assume all proprietary/business logic should be removed from the customer server unless proven otherwise.

### `KEEP_IN_BOOTSTRAP`

Files required for module discovery, install, enable, ACL/session-aware routing, onboarding handoff, local entitlement checks, and component lifecycle management.

### `MOVE_TO_COMPONENT`

Files still needed in OpenEMR, but only for customers with a specific subscribed service.

### `MOVE_TO_SAAS_THIN_WRAPPER`

Files that should stay as a very small local launcher, iframe host, callback adapter, or SSO bridge while the real business logic/UI lives on MedEx/HIPAABank.

### `DELETE_AS_LEGACY`

Files that look obsolete, duplicated, backup-only, deprecated, support-only, or internal-planning-only. Safer rollout:

1. Stop shipping them in customer builds.
2. Confirm no active route/hook/button still depends on them.
3. Delete them from source.

## Keep In Bootstrap

These are the minimum local files the module still needs even in a SaaS-first model.

### Module registration and setup

- `openemr.bootstrap.php`
- `ModuleManagerListener.php`
- `src/ModuleManagerListener.php`
- `moduleConfig.php`
- `show_help_setup.php`
- `show_help.php`
- `help.php`
- `composer.json`

Reason:
- These are the files OpenEMR needs to discover, load, configure, help, and route the module.
- `openemr.bootstrap.php` currently defines top-level menu exposure and gates local pages by entitlement/session state.
- `ModuleManagerListener.php` and `show_help_setup.php` are part of the install/help flow.

### Core local services to keep, but slim down

- `src/MedExConfig.php`
- `src/MedExAPI.php`
- `src/MedExDirectoryManager.php`
- `src/UpdateManager.php`
- `src/CriticalPatchNotifier.php`
- `src/API/Client/HttpClient.php`
- `src/API/Exceptions/InvalidDataException.php`
- `src/API/OEGlobalsBag_polyfill.php`

Reason:
- These should become the local control plane for SSO, entitlement sync, manifest fetch, component install/remove, and health checks.
- They should be reduced over time to bootstrap duties only.

### Local status / health / handoff pages

- `admin/index.php`
- `admin/cloud_dashboard.php`
- `admin/help_center.php`
- `admin/manual_config.php`
- `public/status.php`
- `public/help.php`
- `public/index.php`
- `public/setup.php`
- `public/update.php`

Reason:
- These pages are good bootstrap candidates because they either hand off to SaaS or expose local support state.
- `admin/index.php` already performs cloud dashboard SSO handoff.

### Callback and sync adapters that likely remain local

- `public/callback.php`
- `admin/sync_practice.php`
- `admin/reconnect.php`
- `admin/disconnect.php`
- `admin/reset_connection.php`
- `admin/save_preferences.php`

Reason:
- These interact with the local OpenEMR session/config/database and likely remain as thin adapters even if most logic moves remote.

### DB and migration scaffolding

- `migrations/001_create_migrations_table.php`
- `migrations/002_add_update_cache_columns.php`
- `fix_module_registration.sql`
- `sql/README.md`

Reason:
- Bootstrap still needs a place to track installed component versions and local module state.
- Future component install/remove should likely move to manifest-driven migrations rather than one large shared SQL set.

## Move To SaaS Thin Wrapper

These should be reduced to launchers or status bridges, not full local feature implementations.

### Admin onboarding / account pages already marked SaaS-first

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

Reason:
- The module already contains explicit documentation saying `splash.php`, `onboarding.php`, and `register.php` are deprecated in favor of SaaS.
- These flows should not remain large local UIs. Keep only enough local code to launch SaaS and persist returned tokens/state.
- `admin/onboarding_otp.php`, `admin/register_process.php`, and `admin/agreement_sign.php` are currently still local for flow reliability, but architecturally they should be collapsed into API calls and remote pages.
- The target state is:
  - local wrapper page
  - local callback endpoint if needed
  - remote MedEx-owned workflow

### Dashboard tab content that should become remote

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

Reason:
- These are heavy dashboard/account/subscription UIs.
- Subscription catalog, pricing, backups, billing, and settings fit the SaaS control plane better than local OpenEMR rendering.
- Local bootstrap should show state and open the remote management UI.

### Remote embedded tools

- `public/dashboard.php`
- `public/pdf.php`
- `public/campaigns.php`
- `admin/pdf/index.php`

Reason:
- These already behave like wrappers around remote MedEx/HIPAABank interfaces.
- Keep a thin frame/redirect model locally rather than shipping full local business logic.

## Move To Component

These belong in subscription-specific packages that are installed only when the customer buys that service.

### Secure chat / portal messaging component

- `public/secure_chat.php`
- `public/quick_chat.php`
- `public/receive_chat_message.php`
- `public/portal_messages.php`
- `public/portal_redirect.php`
- `admin/api/secure_chat_audit.php`
- `src/Services/MessageReceiveService.php`
- `src/Services/MessageGenerationService.php`

Reason:
- These are tied to chat/messaging subscriptions, not bootstrap.

### SMS bot / reminders / campaigns component

- `public/sms_bot.php`
- `public/sms_bot_list.php`
- `public/sms_zone_module.php`
- `public/calendar_feed.php`
- `public/calendar_feeds.php`
- `public/campaigns.php`
- `public/recall_board.php`
- `public/ajax.php`
- `public/ajax_handler.php`
- `public/ajax/get_campaign_status.php`
- `public/ajax/get_recall_campaign_status.php`
- `public/ajax/save_recall.php`
- `src/Services/RecallsBoardService.php`
- `src/API/Services/CampaignService.php`
- `src/API/Services/EventsService.php`
- `src/API/Services/EventsService_Full.php`

Reason:
- Reminder and recall workflows are subscription-driven.
- They should not ship to customers without reminder/campaign products enabled.

### Calendar export / calendar sync component

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

## Immediate Cut Order

This is the practical next sequence for reducing the customer module.

1. `admin/onboarding_otp.php`
   - move OTP issuance, verification, expiry policy, and persistence to `medex-api`
   - keep only local AJAX bridge/session keepalive if OpenEMR requires it

2. `admin/agreement_sign.php`
   - move agreement body/source/rendering and signing workflow to `medex-api`
   - keep only iframe/modal launcher locally

3. `admin/register_process.php`
   - move registration/reconnect/account decision logic to `medex-api`
   - keep only local request forwarding plus returned-setting persistence

4. `admin/onboarding.php`
   - reduce to a launcher/container for MedEx-hosted onboarding
   - remove local business rules and pricing/service assembly

5. `admin/create_cart.php`, `admin/process_payment.php`, `admin/process_subscription.php`
   - replace with SaaS checkout/subscription orchestration

## Non-Negotiable Local-Only Set

These are the files that still justify being on the customer server.

- module registration/listener files
- local OpenEMR callback receivers
- local settings persistence needed by OpenEMR
- entitlement/component install adapters
- minimal help/install launchers

Everything else should be presumed removable or remote.
- `public/calendar/update_event.php`
- `src/Listeners/CalendarInjectionListener.php`
- `src/Listeners/CalendarInterceptListener.php`
- `src/Services/CalendarService.php`
- `src/API/Services/EventsService.php`
- `src/API/Services/EventsService_Full.php`

Reason:
- Calendar feed/export behavior is optional and should be delivered only when subscribed.

### PDF management component

- `admin/pdf/admin_pdf_advanced_mapping.js`
- `admin/pdf/api_db_schema.php`
- `admin/pdf/api_patient_data.php`
- `admin/pdf/editor.php`
- `admin/pdf/editor_old_standalone.php`
- `admin/pdf/fhir_helper.php`
- `public/api/pdf_data.php`

Reason:
- PDF management is clearly a separate paid/local integration surface.

### Telehealth component

- `public/telehealth.php`
- `migrations/003_create_telehealth_form.php`
- `sql/telehealth_form.sql`
- `src/Services/ModalityService.php`

Reason:
- Telehealth is entitlement-driven and should not ship with the bootstrap.

### OpenEMR UI injection component

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

Reason:
- These are feature-level UI augmentations, not bootstrap.
- They should ship only when the relevant subscription needs to alter local OpenEMR screens.

## Delete As Legacy

High-confidence delete or stop-ship candidates.

### Backup, temp, and editor artifacts

- `.DS_Store`
- `admin/.DS_Store`
- `public/.DS_Store`
- `public/assets/.DS_Store`
- `src/.DS_Store`
- `src/API/API_Original_Backup.php`
- `src/Listeners/MessagesPageListener.php.bak`
- `src/Services/MessageGenerationService.php.bak`
- `src/Services/MessageReceiveService.php.bak`
- `src/Services/PracticeService.php.bak`
- `src/UpdateManager.php.bak`

Reason:
- These are not customer runtime assets.

### Deprecated local pages already identified in module docs

- `admin/splash.php`
- `admin/onboarding.php`
- `admin/register.php`

Reason:
- Current module docs already mark these as deprecated in a SaaS-first architecture.
- Short-term: keep as wrappers only.
- End-state: remove full local implementations.

### Support / repair / manual-only pages to review for removal

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
- `modernize_api.php`

Reason:
- These look like support utilities, repair scripts, one-off migration helpers, or partial/abandoned flows.
- Default stance should be to stop shipping them unless a current route depends on them.

### Local dev / standalone packaging artifacts

- `standalone/compat.php`
- `standalone/server.php`
- `build_release.sh`
- `test_integration.sh`
- `tests/functional_test.php`
- `tests/integration_test.php`
- `tests/simple_test.php`
- `tests/unit_test.php`

Reason:
- Useful for development, not customer deployment.
- Keep in source if still used internally, but exclude from customer bundles.

### Internal planning and migration docs that should not ship to customers

- `ARCHITECTURE.md`
- `CALENDAR_README.md`
- `CHANGELOG.md`
- `CLAUDE.md`
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
- `docs/MEDEx_CORE_REMOVAL_GUIDE.md`
- `docs/MEDEx_MIGRATION_GUIDE.md`
- `tests/MEDEXBANK_LOG_MONITORING.md`
- `tests/TEST_RESULTS.md`
- `tests/USER_TESTING_GUIDE.md`
- `.AI-CONTEXT`
- `.AI-DISCOVERY`

Reason:
- These are internal engineering/support artifacts, not production module assets.

### Vendor packaging review

- `vendor/`

Reason:
- Re-evaluate whether this entire vendor tree belongs in the customer module package.
- If the bootstrap is minimized, only runtime dependencies needed by that bootstrap should ship.

## Recommended Refactor Order

1. Freeze bootstrap surface:
   - `openemr.bootstrap.php`
   - module manager/help/config files
   - local config + health + SSO bridge

2. Turn onboarding/account pages into wrappers:
   - replace heavy local forms with SaaS redirects or iframe hosts

3. Split by subscription:
   - secure chat
   - reminders/campaigns
   - calendar export
   - PDF management
   - telehealth

4. Stop shipping legacy/support files:
   - backup files
   - standalone tools
   - deprecated local pages
   - internal docs

5. Add a local component registry:
   - installed components
   - installed file manifests
   - component version table
   - uninstall/remove hooks by subscription

## Immediate Packaging Rule

For the next customer-facing package, do not ship:

- backup files
- `.DS_Store`
- internal planning docs
- standalone tooling
- tests
- deprecated pages unless still required as wrappers

That alone reduces footprint before deeper code extraction begins.
