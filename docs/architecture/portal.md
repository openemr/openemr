# OpenEMR Patient Portal — Architecture

**Status:** Living document — current state plus the direction we are steering toward.
**Audience:** Anyone working on the patient portal, human or AI agent.
**Companion:** [`README.md`](README.md) is the overall architecture and target patterns;
this document is the portal-specific view. The layering rules, the strangler-fig migration
strategy, and the subsystem conventions in `README.md` all apply here — this file covers
what is *different* about the portal and where its biggest gaps are.

> **One-line orientation.** The portal is the **"onsite" portal** rooted at `/portal`. It
> authenticates *patients* (not staff), runs on its own session, and — unlike the rest of
> the app — reaches core data through its **own legacy Phreez JSON API and direct queries
> rather than the REST/FHIR API**. Closing that gap is the portal's defining architectural
> goal.

---

## 1. What the portal is (and isn't)

- **Onsite portal only.** The entire patient-facing app lives under `/portal`. There is **no
  separate "offsite" portal application** in this tree. "Offsite" survives only as a config
  concept and the **C-CDA alt-service** bridge (`ccda_alt_service_enable`,
  `portal/ccda_gateway.php` → `src/Services/CDADocumentService.php`) — the vestige of the
  historical offsite portal.
- **"Onsite two"** is the second-generation onsite portal (hence the `*_onsite_two_*` config
  vocabulary) that replaced the original; it is the only generation present.
- **Patients, not staff.** Portal users authenticate against `patient_access_onsite`, keyed
  per `pid` — a completely separate credential store and session from the staff `users`
  auth.

The portal is also a **two-era codebase**: a modernizing outer shell (Twig dashboard, a
unit-tested `src/Controllers/Portal/` login flow, `src/Services` for credentials/messaging)
wrapped around the **oldest stratum in the whole project** — the Phreez/Savant MVC app at
`portal/patient/`. Both the layering rules and the migration strategy in `README.md` apply.

---

## 2. Entry points and structure

Root: `portal/`.

| Entry point | Role | Era |
|-------------|------|-----|
| `portal/index.php` | Patient **login page** — also one-time-token autologin, registration bootstrap, password reset. Hard-gates on `portal_onsite_two_enable`. Bootstraps the portal session (`SessionUtil::PORTAL_SESSION_ID`) *before* including `interface/globals.php` with `$ignoreAuth_onsite_portal = true`. | Legacy procedural, but delegates session/logo/one-time work to `src/` |
| `portal/get_patient_info.php` | Login form **POST target** — the **model transitional shim**: ~180 lines that wire dependencies and delegate to `PatientPortalLoginController`. Regenerates the session id (`$session->migrate(true)`) against fixation. | **Modern** (thin entry point → tested controller) |
| `portal/home.php` | Main **dashboard** after login. `require_once('verify_session.php')`, then renders Twig `templates/portal/home.html.twig`; defines the card-navigation map (ClinicalDocuments, Appointments, MakePayment, SecureMessaging, HealthSnapshot, Profile, BillingSummary, MedicalReports, PROAssessment). | Mixed (procedural data-gathering + Twig) |
| `portal/verify_session.php` | Shared **auth gate** include for the `get_*` endpoints — requires `session['pid']` **and** `session['patient_portal_onsite_two']`, else destroys the portal session and redirects to `index.php`. | Legacy |
| `portal/logout.php` | `portalLog('logout')` then `SessionWrapperFactory::destroyPortalSession()`. | Legacy |
| `portal/get_*.php` | AJAX data endpoints (`get_patient_documents`, `get_allergies`, `get_lab_results`, `get_medications`, `get_prescriptions`, `get_problems`, `get_profile`, `get_pro`, …) — each includes `verify_session.php`, mostly direct `sqlQuery`/`sqlStatement`. | Legacy procedural |
| `portal/patient/` | The **Phreez MVC app + its own JSON API** (see §5). | Oldest stratum |

Supporting subdirs: `portal/account/` (registration + reset), `portal/messaging/` (secure
messaging), `portal/report/` (patient reports, ledger), `portal/sign/` (e-signature),
`portal/lib/` (shared procedural libs), `portal_payment.php` + gateway JS
(Stripe/Authorize.net/Rainforest), `questionnaire_render.php`, `import_template*.php`
(intake/consent template editor).

**Staff-side portal tools** live under `interface/`, not `portal/`:
`interface/patient_file/summary/create_portallogin.php` (staff provisions portal
credentials, ACL `patientportal/portal`), `interface/reports/portal_lockout_tracker.php`,
and portal intake forms (`interface/forms/questionnaire_assessments/patient_portal.php`,
`interface/forms/sdoh/patient_portal.php`).

---

## 3. Authentication and session

**Credential store.** Patients authenticate against **`patient_access_onsite`** (one row per
`pid`): `portal_username`, `portal_login_username`, `portal_pwd` (hashed via the *same*
`OpenEMR\Common\Auth\AuthHash` primitive as staff, but stored here), `portal_pwd_status`
(0 = must-change, 1 = current), `portal_onetime` (one-time token), and brute-force fields
`portal_fail_counter` / `portal_last_fail`. The `patient_data.allow_patient_portal` column
gates whether a patient may log in at all.

**Session separation.** The portal runs on a **distinct session** — `SessionUtil::PORTAL_SESSION_ID`,
obtained via `SessionWrapperFactory::getInstance()->getPortalSession()` (staff use
`getCoreSession()`). A logged-in portal patient is marked by the pair
`session['pid']` + `session['patient_portal_onsite_two'] = 1`, with `authUser = 'portal-user'`
and `sessionUser = '-patient-'`. Crucially, **`authUserID` is intentionally not set** for
portal sessions — that absence is what distinguishes a patient session from a staff session.
Session plumbing is in `src/Common/Session/` (`SessionUtil`, `SessionWrapperFactory`,
`WriteThroughSession`, `PatientSessionUtil`), and the `read_and_close` no-lock mechanism from
[interface-and-forms.md](interface-and-forms.md#cookie--session-management--the-read_and_close-mechanism)
applies here too. As everywhere, access the portal session only through these classes — obtain
it via `SessionWrapperFactory::getPortalSession()` and use the Symfony `SessionInterface`
session bag — never raw `$_SESSION`.

**The modern login flow** (`src/Controllers/Portal/`) — the portal's most modernized area,
extracted from the old `get_patient_info.php` precisely so it could be unit-tested
(`tests/Tests/Unit/Portal/PatientPortalLoginControllerTest.php`):

- **`PatientPortalLoginController::login()`** orchestrates the whole flow: anti-CSRF `itsme`
  flag, username/password, optional second factor (`enforce_signin_email` "passaddon"),
  language choice, the three password-update modes (`NORMAL=0`, `REQUIRED=1`,
  `ONE_TIME_RESET=2`), one-time PIN token login, rehash-on-login, and final session
  establishment + redirect (to `home.php` or a safelisted module file via
  `ModulesApplication::filterSafeLocalModuleFiles`).
- **`PortalLoginCredentialsRepository`** (interface) + **`SqlPortalLoginCredentialsRepository`**
  (impl) own all SQL against `patient_access_onsite`/`patient_data`, routed through
  `QueryUtils` with `noLog` so password queries stay out of the SQL audit log.
- Supporting pieces: `PortalLoginRowNormalizer`, `PortalPasswordHasher` +
  `AuthHashPortalPasswordHasher`, `PatientPortalLoginResult` (value object), `PortalAuditLogger`
  (delegates to legacy `ApplicationTable::portalLog`), `PortalSessionAccessor` +
  `SessionUtilPortalSessionAccessor`.

This flow is the reference example of the strangler pattern for the portal: **a thin
procedural entry point (`get_patient_info.php`) delegating to a fully-tested `src/`
controller, with bit-for-bit legacy behavior preserved.** Copy it when modernizing other
portal endpoints.

**Credential provisioning & registration.**
`src/Services/PatientAccessOnsiteService.php` generates portal credentials and one-time
tokens and sends the credentials email (Twig
`templates/emails/patient/portal_login/message.html.twig`), emitting
`PortalCredentialsUpdatedEvent` / `PortalCredentialsTemplateDataFilterEvent`. One-time auth
uses `OpenEMR\Common\Auth\OneTimeAuth`. Self-registration is `portal/account/register.php`
(Twig `templates/portal/registration/portal_register.html.twig`), gated by
`portal_onsite_two_register` **and** Google reCAPTCHA keys.

---

## 4. Authorization guards

Two guards in `src/Common/Session/` enforce that a portal request can only ever touch its
own patient — this is the portal's most security-critical modern code:

- **`PortalPatientAccessGuard`** — `assertCanRead()` / `assertCanWrite(pid)`. For a portal
  session (`patient_portal_onsite_two` present) it enforces session-pid ownership; for a
  staff caller it falls back to ACLs `patientportal/portal` + `patients/demo`.
- **`PortalSessionPidGuard`** — binds every request to one patient: `requireBootstrapPid()`,
  `assertMatchesSession()`, `assertOwnedBySession()`, and `assertRequestKeysMatchSession()`,
  which scans `$_POST`/`$_GET`/`$_REQUEST` for any key matching `/^p(id|atientid)(_|$)/i` to
  defend against Phreez ORM criteria-bypass tricks (e.g. `Pid_Equals`). It reads the
  authoritative patient from `OEGlobalsBag` `bootstrap_pid`.

The second guard exists specifically because the legacy Phreez layer (§5) exposes ORM query
criteria to the client; the guard is the compensating control. **Any new portal data path
must go through these guards.**

---

## 5. Portal ↔ core data flow — the central architectural issue

Unlike the clinical UI, **the portal does not use the public `/apis` REST or FHIR
controllers.** Core data is reached two ways, both outside the API-first model that
[api.md](api.md) sets for
the rest of the app:

**(a) The Phreez MVC + its own JSON API (`portal/patient/`) — legacy framework.**
`portal/patient/index.php` bootstraps `_global_config.php` / `_app_config.php` /
`_machine_config.php` and runs `Dispatcher::Dispatch(...)`. The framework is **Phreez** (an
old PHP ORM/MVC) with the **Savant** template engine, vendored under
`portal/patient/fwk/libs/{savant,util,verysimple}`.

- A REST-ish JSON route map in `_app_config.php` (`api/patient[/:num]`,
  `api/portalpatient/:num`, `api/onsitedocuments`, `api/onsiteportalactivities`,
  `api/onsiteactivityviews`, `api/users`) is consumed by the dashboard JS.
- Controllers in `portal/patient/libs/Controller/` (`PortalPatientController`,
  `OnsiteDocumentController`, `OnsitePortalActivityController`, …) call the guards from §4.
- Models/DAOs in `portal/patient/libs/Model/` + `.../DAO/` are **Phreez-generated** with
  their own `*Criteria`/`*Map` classes — the portal's **own ORM and queries**, not the
  `src/Services` layer.

**(b) Direct procedural queries + selective `src/` services.** The `get_*.php` endpoints
mostly issue `sqlQuery`/`sqlStatement` directly, but newer features reuse services: secure
messaging via `src/Services/PortalMessagingSender.php` (resolves sender identity, prevents
impersonation) from `portal/messaging/handle_note.php`; C-CDA/document exchange via
`src/Services/CDADocumentService.php`; appointment requests reuse core
`add_edit_event_user.php` / `find_appt_popup_user.php` + `library/appointments.inc.php`.

Intake/documents/questionnaires: `document_templates` + `portal/import_template*.php` drive
intake/consent templates; `portal/questionnaire_render.php` renders FHIR-style
questionnaires (`questionnaire_repository` / `questionnaire_response`); signed/uploaded docs
land in `onsite_documents` + `onsite_signatures`.

> **Direction (the big one).** Converge the portal onto the **same services, and where
> possible the same REST/FHIR endpoints, as the clinical UI.** The Phreez app and the
> `get_*.php` direct queries are a parallel data stack that re-implements logic the core
> services already own — exactly the duplication the API-first principle exists to prevent,
> and a doubled security surface (hence the §4 guards). Migrate Phreez controllers and
> `get_*.php` endpoints to thin shims over `src/Services`/REST, following the
> `get_patient_info.php` → `PatientPortalLoginController` pattern. Retire the vendored
> Phreez/Savant framework as its last controller is migrated (strategy:
> [migration.md](migration.md)).

---

## 6. Frontend stack

Two eras here too:

- **Dashboard (`portal/home.php`): Twig + Bootstrap/jQuery**, sharing the main app's theming.
  Templates in `templates/portal/` (`base.html.twig`, `home.html.twig`, `header.html.twig`,
  `partial/cards/*`, `login/autologin.html.twig`, `registration/portal_register.html.twig`,
  `portal-credentials-settings.html.twig`). `base.html.twig` pulls assets through the shared
  `setupHeader([...])` pipeline (bundles `no_main-theme`, `portal-theme`,
  `datetime-picker`, `i18next`, `i18formatting`) — i.e. the main app's `Core\Header` asset
  system, themed via `portal_css_header`, rendered through `ServiceContainer::getTwig()`.
  i18n via i18next. Modern-ish (Twig), still Bootstrap/jQuery, not a SPA.
- **Phreez app (`portal/patient/`): Savant `*.tpl.php` server templates** + jQuery/AJAX in
  `portal/patient/scripts/` loaded by the ancient **LABjs** loader. Legacy — not
  Knockout/Angular/React/Vue.
- **E-signature:** `portal/sign/assets/signature_pad.umd.js` + `signer_api.js` (vanilla).

New portal UI should follow the main app's Presentation-layer rules
([layers.md → Presentation layer](layers.md#presentation-layer-twig)): Twig,
no logic in templates, view models assembled by a controller.

---

## 7. Extensibility and branding

No bundled CMS. Public surface = the login page, self-registration (reCAPTCHA-gated), and a
configurable nav driven by the **`patient_portal_menu`** table. Branding via `LogoService`
(`src/Services/LogoService.php`, logos keyed `portal/login/primary` &
`portal/login/secondary`) and the `portal_css_header` theme global.

Extend the portal through **events**, per the core↔module rule
([`README.md` §2](README.md#2-target-architecture-at-a-glance)): `src/Events/PatientPortal/`
(`RenderEvent`, `AppointmentFilterEvent`, `PatientReportFilterEvent`) plus
`ModulesApplication` safelisted redirects. Modules already do this — e.g.
`oe-openplanit-module` and `oe-module-comlink-telehealth` ship their own
`templates/portal/*.twig` and portal controllers
(`PatientPortalReportController`, `TeleHealthPatientPortalController`). A module must never
edit core portal files to inject behavior.

---

## 8. Configuration globals

Defined in `library/globals.inc.php`; the master switch is checked in `portal/index.php` and
`interface/globals.php`:

| Global | Purpose |
|--------|---------|
| `portal_onsite_two_enable` | Master on/off ("Enable Patient Portal") |
| `portal_onsite_two_address` | Public URL of the portal |
| `portal_onsite_two_basepath` | Use server protocol/host in internal URLs |
| `portal_onsite_two_register` | Allow self-registration (with reCAPTCHA) |
| `portal_onsite_document_download` | Allow document downloads |
| `portal_two_pass_reset` / `portal_two_ledger` / `portal_two_payments` | Feature toggles (reset / ledger / payments cards) |
| `portal_force_credential_reset`, `enforce_signin_email`, `use_email_for_portal_username` | Credential/login policy |
| `portal_timeout`, `portal_onetime_max_pin_attempts` | Session timeout, one-time PIN attempt cap |
| `portal_css_header`, `portal_primary_menu_logo_height` | Theme / branding |
| `ccda_alt_service_enable` | The C-CDA "offsite" alt-service bridge (the only "offsite" vestige) |

Read these through `OEGlobalsBag` typed getters, never `$GLOBALS`
([layers.md → Cross-cutting concerns](layers.md#cross-cutting-concerns)).

---

## 9. Key database tables

| Table | Role |
|-------|------|
| `patient_access_onsite` | Portal credentials (one row per `pid`); see §3 |
| `onsite_documents` | Patient documents / filled intake templates |
| `onsite_signatures` | E-signature records |
| `onsite_mail`, `onsite_messages` | Secure messaging |
| `onsite_portal_activity` | Portal activity queue (requests/tasks surfaced to staff) |
| `onsite_online` | Online/session presence |
| `patient_portal_menu` | Configurable portal navigation |
| `document_templates` | Intake/consent template definitions |
| `questionnaire_repository`, `questionnaire_response`, `form_questionnaire_assessments` | PRO / questionnaire intake |
| `patient_data` (`allow_patient_portal`) | Demographics; gates portal access |

---

## 10. Target architecture for the portal

The portal inherits every target from [`README.md`](README.md); the portal-specific
priorities, in rough order:

1. **Converge data access onto core services and the REST/FHIR API** (§5). This is the
   headline goal — eliminate the parallel Phreez/direct-query stack so portal and clinical
   UI share one source of truth and one security surface.
2. **Migrate Phreez/Savant controllers to `src/` + Twig**, strangler-style, using
   `get_patient_info.php` → `PatientPortalLoginController` as the template; retire the
   vendored framework when its last controller is gone.
3. **Keep all patient-scoped access behind `PortalPatientAccessGuard` /
   `PortalSessionPidGuard`** (§4) until typed, scoped repositories
   ([subsystems.md → Permissions](subsystems.md#permissions-and-authorization)) make the scope
   structural rather than a runtime check.
4. **New portal UI in Twig**, new portal logic in injected services, new portal tables in
   `sql/database.sql` ([layers.md → schema](layers.md#database-schema-design-and-governance)).
5. **Extend via events**, never by editing core portal files (§7).

---

## 11. Reference implementations in-tree

| Area | Canonical example | Era |
|------|-------------------|-----|
| Transitional login (thin shim → tested controller) | `portal/get_patient_info.php` → `src/Controllers/Portal/PatientPortalLoginController.php` | Modern — copy this |
| Credential repository | `src/Controllers/Portal/SqlPortalLoginCredentialsRepository.php` (interface + impl) | Modern |
| Credential provisioning service | `src/Services/PatientAccessOnsiteService.php` | Modern |
| Patient-scope guards | `src/Common/Session/PortalPatientAccessGuard.php`, `PortalSessionPidGuard.php` | Modern |
| Secure messaging sender | `src/Services/PortalMessagingSender.php` | Modern |
| Dashboard presentation | `portal/home.php` + `templates/portal/*.html.twig` | Mixed |
| Legacy MVC + own JSON API (to retire) | `portal/patient/` (Phreez + Savant + DAOs) | Legacy |
| Legacy AJAX data endpoints (to migrate) | `portal/get_*.php` | Legacy |

---

## 12. Portal technology stack and deprecation

The portal shares the core stack and its direction — see
[tech-stack.md](tech-stack.md) for PHP, MariaDB/MySQL,
Twig/Smarty, Node-for-C-CDA, and the jQuery/Knockout/vanilla-JS plan. The table below is the
**portal-specific** surface on top of that. (Status legend as in `README.md` §12.)

| Technology | Where | Role | Status |
|---|---|---|---|
| Phreez (PHP MVC/ORM) | `portal/patient/fwk/`, `libs/` | The `portal/patient/` app + its own JSON API & DAOs (§5) | **Deprecate** — migrate controllers to `src/`, retire the framework |
| Savant | `portal/patient/fwk/libs/savant`, `*.tpl.php` | Phreez template engine | **Deprecate** → Twig |
| LABjs | `portal/patient/scripts/libs/LAB.min.js` | Ancient async script loader | **Deprecate** → ESM / webpack bundles |
| jQuery + AJAX (Phreez pattern) | `portal/patient/scripts/` | Dashboard client logic | **Deprecate** → vanilla JS (tracks core; see [tech-stack.md](tech-stack.md)) |
| Twig | `templates/portal/*.html.twig` | Dashboard / login / registration presentation | **Keep** (§6) |
| Bootstrap / jQuery (dashboard) | `setupHeader([...])` bundles | Dashboard CSS/JS, shared with core | **Upgrade / Deprecate** — tracks core |
| i18next | dashboard asset bundle | **Client-side** i18n in the portal UI | **Evaluate** — distinct from server-side `xl()` ([subsystems.md → i18n](subsystems.md#internationalization-i18n)); keep one story |
| signature_pad (UMD) | `portal/sign/assets/signature_pad.umd.js` | E-signature capture | **Keep** (vanilla) |
| reCAPTCHA (Google) | `portal/account/register.php` | Registration bot defense | **Keep** |
| Phreez JSON API | `portal/patient/_app_config.php` routes | Portal's private data API | **Deprecate** → core `src/Services` + REST/FHIR (§5, §10) |

**Portal deprecation priorities.** The portal carries the project's **oldest technology
stratum**, so its deprecation list is longer than core's:

1. **Phreez + Savant + LABjs** — the whole `portal/patient/` framework is the top retirement
   target. Migrate its controllers to `src/` (Twig + injected services) strangler-style and
   remove the vendored framework when its last route is gone.
2. **The Phreez JSON API → core services/REST** (§5) — the single most important portal move;
   it collapses the parallel data stack onto one source of truth.
3. **jQuery/Knockout/AngularJS/Moment** — same vanilla-JS-first direction as core.
4. **Reconcile i18n** — the portal dashboard uses client-side **i18next** while the rest of
   the app translates server-side with `xl()` ([subsystems.md → i18n](subsystems.md#internationalization-i18n)). Pick one story rather than
   maintaining two translation pipelines.

Add no new usage of anything marked **Deprecate** here; follow the strangler strategy
([migration.md](migration.md)) to remove existing
usage.

---

*Keep this document honest: the portal is modernizing fast in `src/Controllers/Portal/` and
`src/Services/`, so verify a named class still exists and still works as described before
relying on it, and update this file as Phreez endpoints are retired.*
