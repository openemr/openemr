# ClaimRev Connect for OpenEMR

Connects OpenEMR to the [Claim Revolution, LLC](https://www.claimrev.com)
clearinghouse and adds revenue-cycle dashboards on top of the data the
clearinghouse returns. Claim and ERA exchange runs over the ClaimRev REST
API; eligibility, reconciliation, denial analytics, AR aging, and a claim
status dashboard run on the local copy of that data plus OpenEMR's billing
tables.

Module version: see `Bootstrap::MODULE_VERSION`. Current: **2.1.2**.

---

## Features

### Billing flow

| Page                              | What it does                                                                                                              |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `public/x12Tracker.php`           | Tracks claim files sent to ClaimRev (status, retry).                                                                      |
| `public/era.php`                  | Lists ERA / 835 files received from ClaimRev with download.                                                               |
| `public/payment_advice.php`       | ERA-driven payment advice search + posting to OpenEMR's `ar_session` / `ar_activity`. Single-row + batch post supported.  |
| `public/claims.php`               | Claim search against the ClaimRev portal with detail expansion, sort, CSV export, requeue, mark-worked, and editor links. |
| `public/claim_status.php`         | Work queue: claims needing attention, with timeline of every status event we've seen.                                     |
| `public/reconciliation.php`       | Side-by-side OpenEMR claim status vs ClaimRev status with discrepancy classification.                                     |

### Eligibility

| Page                              | What it does                                                                                                              |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `public/appointments.php`         | Upcoming appointments with their primary-insurance eligibility status and "check now" / queue actions.                    |
| Eligibility card on patient chart | Per-product (Eligibility / Coverage Discovery / Demographics / MBI Finder) results with AI chat over the saved response.  |
| Calendar indicators               | Color codes events on the OpenEMR calendar by eligibility status — green / red / yellow / grey. Off by default.           |
| Eligibility sweep                 | Background service that proactively queues eligibility checks for the next N days of appointments on configured weekdays. |

### Reporting / dashboards

| Page                              | What it does                                                                                                              |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `public/aging_report.php`         | AR aging by payer with current / 30 / 60 / 90 / 120 / 120+ buckets and CSV export.                                        |
| `public/denial_analytics.php`     | Denial rollups by reason code, by payer, by month with date filters.                                                      |
| `public/recoupment_report.php`    | Identifies negative ERA adjustments (recoupments / takebacks).                                                             |
| `public/patient_balance.php`      | Patient-responsibility queue: encounters with outstanding balance after insurance closed, with statement tracking.        |
| Dashboard KPIs                    | `DashboardService` exposes claim throughput, AR, denial, collection, and patient-AR metrics for the home tile.            |

---

## Configuration

All settings live under **Admin → Globals → ClaimRev Connect**. Names are
the `oe_claimrev_*` keys defined in `src/GlobalConfig.php`.

### Required to function

| Setting                                       | Notes                                                                                              |
| --------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| `oe_claimrev_config_environment`              | `P` for production (default), `D` for a custom identity provider (Entra ID External, Zitadel, …). |
| `oe_claimrev_config_clientid`                 | OAuth client ID. Available in the ClaimRev Portal under **Client Connect**.                        |
| `oe_claimrev_config_clientsecret`             | OAuth client secret. Encrypted at rest. Contact ClaimRev support for this value.                   |

Without these three set, `GlobalConfig::isConfigured()` returns false and
the module skips event registration entirely (no menu item, no eligibility
events, no posting endpoints) — pages that try to load show
`ModuleNotConfiguredException`.

### Optional

| Setting                                          | What it does                                                                                                                |
| ------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------- |
| `oe_claimrev_x12_partner_name`                   | X12 partner record name. Default `ClaimRev`. Used by `ClaimRevModuleSetup::createPartnerRecord`.                            |
| `oe_claimrev_config_service_type_codes`          | Comma-separated 270 service-type codes. Empty asks for all benefits.                                                        |
| `oe_claimrev_benefit_code_filter`                | Comma-separated benefit codes (1, 6, A, B, C, …). Filters the eligibility detail view locally; not sent to ClaimRev.        |
| `oe_claimrev_config_auto_send_claim_files`       | Auto-send X12 claim files. When off, files queue but are sent manually.                                                     |
| `oe_claimrev_config_add_menu_button`             | Add the module's top-nav menu item (re-login required).                                                                     |
| `oe_claimrev_config_add_eligibility_card`        | Add the eligibility card to the patient dashboard.                                                                          |
| `oe_claimrev_config_use_facility_for_eligibility`| Use the facility (rather than the appointment provider) as the 270 information receiver.                                    |
| `oe_claimrev_enable_rte`                         | Real-time eligibility — kick off a check when an appointment is created.                                                    |
| `oe_claimrev_eligibility_results_age`            | Days before a stored eligibility result is considered stale. Used by the sweep + calendar indicators.                       |
| `oe_claimrev_send_eligibility`                   | Master switch for the background eligibility send service.                                                                  |
| `oe_claimrev_enable_watchdog`                    | Auto-resets ClaimRev background services that stay in `running=1` for >10 minutes (PHP crash, OOM kill). Default on.        |
| `oe_claimrev_enable_notifications`               | Poll ClaimRev for portal notifications and deliver them as OpenEMR pnotes. Default on.                                      |
| `oe_claimrev_notification_recipient`             | Semicolon-separated OpenEMR usernames to receive notifications. Default `admin`.                                            |
| `oe_claimrev_enable_test_mode`                   | Show a "Test Mode" toggle on the payment advice page. Generates simulated ERA data from local billing rows.                 |
| `oe_claimrev_enable_sweep`                       | Master switch for the eligibility sweep background service.                                                                 |
| `oe_claimrev_sweep_days`                         | Comma-separated day-of-week numbers (`0` = Sunday … `6` = Saturday). Default `1,4` (Mon + Thu).                             |
| `oe_claimrev_sweep_lookahead`                    | Days ahead to sweep. Default `7`.                                                                                            |
| `oe_claimrev_enable_calendar_indicators`         | Color-code OpenEMR calendar events by eligibility status. May impact calendar performance on busy schedules.                |

### Identity-provider overrides

Auto-configured for production. Only override when pointing at a custom
identity provider:

`oe_claimrev_config_portal_url`, `oe_claimrev_config_dev_api_url`,
`oe_claimrev_config_dev_scope`, `oe_claimrev_config_dev_authority`.

---

## Background services

Registered in `background_services` by `table.sql` and re-enabled on
module enable via `ModuleManagerListener::enable`. Each one has a
`@phpstan-ignore openemr.noGlobalNsFunctions` shim that delegates to
a namespaced service class so the cron table can address it by name.

| Service                       | Function                          | Cadence    | Delegate                              |
| ----------------------------- | --------------------------------- | ---------- | ------------------------------------- |
| `ClaimRev_Send`               | `start_X12_Claimrev_send_files`   | 1 minute   | `ClaimUpload::sendWaitingFiles`       |
| `ClaimRev_Receive`            | `start_X12_Claimrev_get_reports`  | 240 minutes | `ReportDownload::getWaitingFiles`    |
| `ClaimRev_Elig_Send_Receive`  | `start_send_eligibility`          | 1 minute   | `EligibilityTransfer::sendWaitingEligibility` |
| `ClaimRev_Notifications`      | `start_claimrev_notifications`    | 60 minutes | `NotificationPollService::run`        |
| `ClaimRev_Watchdog`           | `start_claimrev_watchdog`         | 20 minutes | `ClaimRevModuleSetup::resetStuckServices` |
| `ClaimRev_Elig_Sweep`         | `start_eligibility_sweep`         | 1440 minutes (daily) | `EligibilitySweepService::run` |

The watchdog only resets services other than itself; otherwise a watchdog
run that exceeds 10 minutes would clear its own running flag mid-execution.

---

## Database tables

All created by `table.sql`. Migrations run once on enable via
`ClaimRevModuleSetup::runMigrations`, which understands the standard
OpenEMR `#IfNotRow / #IfNotColumnType / #IfNotTable / #EndIf` directives.

| Table                                | Purpose                                                                              |
| ------------------------------------ | ------------------------------------------------------------------------------------ |
| `mod_claimrev_eligibility`           | One row per (patient, payer responsibility, request) eligibility check + JSON result. |
| `mod_claimrev_notifications`         | Tracks which portal notifications have already been delivered as pnotes.             |
| `mod_claimrev_claims`                | Per-claim mirror of ClaimRev status (object id, status name/id, ERA classification, paid amount, ar_session_id, last sync). |
| `mod_claimrev_claim_events`          | Append-only event log per claim (submitted, rejected, accepted, denied, status_check_276, era_received, payment_posted, requeued, corrected, manual_note, claimrev_sync). |
| `mod_claimrev_patient_statements`    | Statement history for the patient balance queue (date, method, amount, status, notes). |

---

## Development

### Code conventions

This module follows the OpenEMR developer conventions documented in the
top-level `CLAUDE.md`. In particular:

- Every PHP file starts with `declare(strict_types=1)`.
- Superglobal access goes through `ModuleInput` (which routes through
  `filter_input` so the `openemr.forbiddenRequestGlobals` PHPStan rule
  passes).
- Mixed cells from `QueryUtils::fetchRecords` are narrowed with
  `TypeCoerce::asString / asInt / asFloat / asBool / asNullableInt`
  rather than bare casts (`(int) $mixed` is rejected at PHPStan level
  10).
- `catch (\Throwable)` and `catch (\Exception)` are forbidden by
  `openemr.forbiddenCatchType`. Use
  `catch (\RuntimeException | \LogicException)` instead. `ClaimRevException`
  extends `RuntimeException` so this still catches our own throws.
- Database calls go through `QueryUtils`; legacy `sqlStatement` /
  `sqlInsert` / `sqlQuery` / `sqlStatementNoLog` are blocked by
  `openemr.deprecatedSqlFunction`.
- Error reporting uses the PSR-3 logger via `ServiceContainer::getLogger`,
  not `error_log()`.

### Tests

Pure helpers have isolated test coverage in
`tests/Tests/Isolated/Modules/ClaimRevConnector/`:

```
TypeCoerceTest                  asString / asInt / asFloat / asBool / asNullableInt
ValueMappingTest                mapPayerResponsibility (case-insensitive primary/secondary/tertiary → p/s/t)
AgingReportServiceTest          toCsv with RFC 4180 escape
ClaimTrackingServiceTest        parsePcn (mirrors PaymentAdvicePostingService)
PaymentAdvicePostingServiceTest buildIdempotencyReference, parsePatientControlNumber, getClaimStatusLabel, sumServiceAmounts
ReconciliationServiceTest       computeDiscrepancy classifier
```

Run them from the repo root:

```bash
composer phpunit-isolated -- --filter ClaimRevConnector
```

DB-bound and API-bound code paths (the `reconcile()` / `searchClaims()` /
`post()` entry points) need a real OpenEMR install; they are exercised
by manual QA through the UI.

### Static analysis

`composer phpstan` runs at level 10. Custom rules in `tests/PHPStan/Rules/`
enforce the conventions above. Avoid adding new baseline entries — fix
the underlying type error.

`composer rector-check` keeps the module on modern PHP idioms; run
`composer rector-fix` to apply suggestions.

### Branches

This module is maintained on two branches in the ClaimRev fork:

- **`master`** — targets upstream OpenEMR's master branch. No back-compat
  shims, latest PHP / Symfony / dependency versions.
- **`release/v7-compat`** — targets OpenEMR 7.x. Adds a thin
  `src/Compat/` shim layer plus a reflection-based `CsrfHelper` that
  detects the 7.x vs 8.x `CsrfUtils::collectCsrfToken` signature. The
  module-internal API is the same; only the OpenEMR-internal API hooks
  differ.

After merging changes from `master` into `release/v7-compat`, run
`tools/v7-overlay-restore.sh` to put the shim files back. The script is
idempotent.

---

## Troubleshooting

### `ModuleNotConfiguredException` on every module page

The required globals (`oe_claimrev_config_clientid`,
`oe_claimrev_config_clientsecret`, `oe_claimrev_config_environment`)
aren't set. Go to **Admin → Globals → ClaimRev Connect**, fill the
three values, save. Re-login (the module's menu item only renders
after a fresh login).

### Background services stuck in `running=1`

The watchdog should reset stuck services every 20 minutes. To reset
manually:

```sql
UPDATE background_services
SET running = 0
WHERE running = 1
  AND name LIKE '%ClaimRev%'
  AND name != 'ClaimRev_Watchdog';
```

The watchdog excludes itself for the reason noted above.

### Wrong build for the OpenEMR version

The `master` branch ships clean for OpenEMR 8.x. For 7.x, install from
the `release/v7-compat` branch — the shim layer is required there.
Symptoms of running the wrong build are typically `Class not found`
errors for `OpenEMR\Common\Csrf\CsrfUtils` (8.x build on 7.x) or
unexpected method-signature errors (7.x build on 8.x).

### "Already posted" warnings on a payment advice that wasn't

`PaymentAdvicePostingService::isAlreadyPosted` keys off the
`ar_session.reference` value, which uses a fixed
`ClaimRev-{paymentAdviceId}` prefix
(`PaymentAdvicePostingService::REFERENCE_PREFIX`). If the prefix or the
paymentAdviceId changes between runs, the dedup check stops matching
and the same advice can post twice. The shape is regression-tested
in `PaymentAdvicePostingServiceTest::testReferencePrefixHasExpectedShape`.

---

## License

GPL v3 — see [`LICENSE`](LICENSE) and the
[OpenEMR project license](https://github.com/openemr/openemr/blob/master/LICENSE).

## Contributing

Open an issue or send a pull request against
[claimrevolution/openemr_fork](https://github.com/claimrevolution/openemr_fork)
or the upstream module path
[`interface/modules/custom_modules/oe-module-claimrev-connect`](https://github.com/openemr/openemr/tree/master/interface/modules/custom_modules/oe-module-claimrev-connect)
in the main OpenEMR repo.
