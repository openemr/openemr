# OpenEMR Patient Portal — Architecture

**Status:** Living document — current state plus the direction we are steering toward.
**Audience:** Anyone working on the patient portal, human or AI agent.
**Companion:** [`README.md`](README.md) is the overall architecture and target patterns;
this document is the portal-specific view. The layering rules, the strangler-fig migration
strategy, and the subsystem conventions in `README.md` all apply here — this file covers
what is *different* about the portal and where its biggest gaps are.

> **One-line orientation.** The portal is the **"onsite" portal** rooted at `/portal`. It
> authenticates *patients* (not staff), runs on its own session, and — unlike the rest of
> the app — reaches core data through its **own JSON API under `/portal/patient/api/*`
> (now `src/` controllers and services) and the procedural `get_*.php` endpoints, rather
> than the REST/FHIR API**. Closing that gap is the portal's defining architectural goal.

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

The portal is still a **two-era codebase**, but the dividing line has moved. The
`portal/patient/` app — once the oldest stratum in the project (the vendored **Phreeze**
MVC/ORM framework) — has been rebuilt on `src/` controllers, a typed router, and Twig
(#15020, #15021, #15058, #15071); Phreeze is gone. The oldest stratum left is the
procedural `get_*.php` data endpoints (and the shared `portal/lib/`). Both the layering
rules and the migration strategy in `README.md` apply.

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
| `portal/get_*.php` | AJAX data endpoints (`get_patient_documents`, `get_allergies`, `get_lab_results`, `get_medications`, `get_prescriptions`, `get_problems`, `get_profile`, …) — each includes `verify_session.php`, mostly direct `sqlQuery`/`sqlStatement`. | Legacy procedural |
| `portal/patient/` | Staff Portal Dashboard, portal documents, profile/registration pages, and the **portal JSON API**. `index.php` is a thin entry point → `src/Controllers/Portal/Patient/PortalFrontController` → `src/Portal/Routing/PortalRouter` (see §5). | **Modern** (thin entry point → `src/` controllers, Twig) |

Supporting subdirs: `portal/account/` (registration + reset), `portal/messaging/` (secure
messaging), `portal/report/` (patient reports, ledger), `portal/sign/` (e-signature),
`portal/lib/` (shared procedural libs), `portal_payment.php` + gateway JS
(Stripe/Authorize.net/Rainforest), and `portal/import_template*.php` (the document-template
editor/backend). Questionnaire import/rendering is no longer in `portal/`
(`questionnaire_render.php` was removed in #15178) — it now lives under `interface/super/`
(see §5).

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
and `sessionUser = '-patient-'`. **The authoritative discriminator is the positive marker
`session['patient_portal_onsite_two']`** — that is what `PortalPatientAccessGuard` branches on
before verifying ownership with `PortalSessionPidGuard`. (`authUserID` also happens to be unset
for portal sessions, but that is an implementation detail; new portal paths must key off the
positive marker, not treat a missing `authUserID` as proof of a patient session.)
Session plumbing is in `src/Common/Session/` (`SessionUtil`, `SessionWrapperFactory`,
`WriteThroughSession`, `PatientSessionUtil`), and the `read_and_close` no-lock mechanism from
[interface-and-forms.md](interface-and-forms.md#cookie--session-management--the-read_and_close-mechanism)
applies here too. As everywhere, access the portal session only through these classes — obtain
it via `SessionWrapperFactory::getPortalSession()` and use the Symfony `SessionInterface`
session bag — never raw `$_SESSION`.

**The modern login flow** (`src/Controllers/Portal/`) — extracted from the old
`get_patient_info.php` precisely so it could be unit-tested
(`tests/Tests/Unit/Portal/PatientPortalLoginControllerTest.php`):

- **`PatientPortalLoginController::login()`** orchestrates the whole flow: anti-CSRF `itsme`
  flag, username/password, an optional **email-confirmation** check (`enforce_signin_email` —
  the legacy "passaddon" email match; the controller labels it explicitly as *not* MFA),
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
controller, with bit-for-bit legacy behavior preserved.** Copy it when modernizing the
remaining `get_*.php` endpoints.

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

The portal's security-critical access control lives in `src/Common/Session/` and
`src/Common/Csrf/`:

- **`PortalPatientAccessGuard`** (`src/Common/Session/`) — demographics access via
  `assertCanRead(pid)` / `assertCanWrite(pid)`, and document access via
  `assertCanReadDocuments(pid)` / `assertCanAddDocument(pid)` / `assertCanWriteDocuments(pid)`
  (#15023). For a portal session (`patient_portal_onsite_two` present) it enforces
  session-pid ownership; for a **staff** caller it falls back to ACLs — `patientportal/portal`
  **plus** `patients/demo` for demographics, or `patients/docs` for documents
  (read / write-or-addonly / write).
- **`PortalSessionPidGuard`** (`src/Common/Session/`) — binds every request to one patient:
  `requireBootstrapPid()`, `assertMatchesSession()`, `assertOwnedBySession()`, and
  `assertRequestKeysMatchSession()`, which scans the arrays it is handed for any key matching
  `/^p(id|atientid)(_|$)/i` and rejects one that disagrees with the session pid. It is called
  **once, in `PortalFrontController`**, with `$request->request->all()` and
  `$request->query->all()` — it reads no superglobals itself, in keeping with the
  no-superglobals rule in [layers.md](layers.md#controller-layer). The authoritative patient is
  read from `OEGlobalsBag` `bootstrap_pid`.
- **`PortalApiCsrfGuard`** (`src/Common/Csrf/`) — requires the session's CSRF token in the
  `X-CSRF-Token` header on every method other than `GET`/`HEAD`/`OPTIONS`
  (`PortalFrontController` calls `assertValid()` before dispatch). The router no longer
  accepts `_method` or `X-HTTP-Method-Override` overrides — the HTTP method is taken at face
  value.

These guards are defense in depth, not a patch over a leaky ORM: the portal's document-list
query is fixed, with whitelisted sort columns and bound filters, so no client-supplied query
criteria reach SQL. **Any new portal data path must still go through these guards.**

---

## 5. Portal ↔ core data flow — the central architectural issue

Unlike the clinical UI, **the portal does not use the public `/apis` REST or FHIR
controllers.** Core data is reached two ways, both outside the API-first model that
[api.md](api.md) sets for the rest of the app:

**(a) The portal JSON API (`/portal/patient/api/*`).** Formerly the Phreeze framework,
replaced in #15058/#15071 with the URLs and JSON contract unchanged.
`portal/patient/index.php` hands off to `src/Controllers/Portal/Patient/PortalFrontController`,
which checks CSRF (`PortalApiCsrfGuard`) and request PID keys (`PortalSessionPidGuard`), then
dispatches through `src/Portal/Routing/PortalRouteTable`: one `PortalRoute` per method + path,
each with a typed access rule (`RouteAccess`) and a typed caller (`PortalPrincipal`: portal
patient, registering patient, or staff).

- Controllers (`PatientController`, `PortalPatientController`, `OnsiteDocumentController`,
  `PortalReviewController`, `ProviderController`) take a Symfony `Request` and return a
  `Response`.
- Data goes through `QueryUtils`-backed services: `OnsiteDocumentRepository`,
  `PortalProfileService` (Commit to Chart and self-registration save through `PatientService`),
  and the review queue (`src/Services/PatientReview/`) for everything held for staff review.
- Pages are Twig (`templates/portal/patient/`); the client is `scripts/portal-api.js` (fetch)
  and `scripts/portal-page.js` plus the per-screen `scripts/app/*.js` (see §6).
- Contract tests: `PortalPatientApiContractTest` (status codes, response shapes, record keys)
  and `PortalRouterTest` (access rules).

This is still a portal-private API, not `/apis` REST/FHIR, which is why the direction below
stands.

**(b) Direct procedural queries + selective `src/` services.** The `get_*.php` endpoints
mostly issue `sqlQuery`/`sqlStatement` directly, but newer features reuse services: secure
messaging via `src/Services/PortalMessagingSender.php` (resolves sender identity, prevents
impersonation) from `portal/messaging/handle_note.php`; C-CDA/document exchange via
`src/Services/CDADocumentService.php`; appointment requests reuse core
`add_edit_event_user.php` / `find_appt_popup_user.php` + `library/appointments.inc.php`.

Intake/documents/questionnaires: `document_templates` + `portal/import_template*.php` drive
intake/consent templates. Questionnaire import now runs through
`interface/super/manage_questionnaire_availability.php?view=import`
(`QuestionnaireImportController`, `src/Services/Questionnaire/Import/`), reached from the
patient dashboard's Questionnaire Assessments tabs; signed/uploaded docs land in
`onsite_documents` + `onsite_signatures`.

> **Direction (the big one).** Converge the portal onto the **same services, and where
> possible the same REST/FHIR endpoints, as the clinical UI.** Retiring the Phreeze framework
> (done) was the first chore; what remains is that the portal JSON API, though now on `src/`
> services, is still a **private** API next to `/apis`, and the `get_*.php` endpoints still
> issue direct `sqlQuery`. Converge both onto core services and REST/FHIR, following the
> `get_patient_info.php` → `PatientPortalLoginController` pattern (strategy:
> [migration.md](migration.md)). Patient-submitted data goes through the **review queue**
> (`src/Services/PatientReview/`), which is already shared with the FHIR API (#15162).

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
- **Portal patient pages (`portal/patient/`): Twig** (`templates/portal/patient/`) with
  vanilla-JS page scripts (`scripts/app/*.js`) over a small fetch-based API client
  (`scripts/portal-api.js`, which sends the `X-CSRF-Token` header on writes) and shared page
  helpers (`scripts/portal-page.js`). Rows and forms are `<template>` elements filled with
  `textContent`. Jest: `tests/js/portal-patient-api.test.js`. (The old Backbone/Underscore
  client and the LAB.js loader were removed with Phreeze in #15071.)
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
| `patient_review_request`, `patient_review_event`, `patient_review_secret` | Review queue: one row per patient submission (portal or API app), append-only history, and sensitive data (card details) kept apart and deleted when the request closes. See `src/Services/PatientReview/`. |
| `onsite_portal_activity` | **Retired.** Migrated to the review queue (#14969); kept unused for one release, then dropped. Do not read or write it. |
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
   headline goal — eliminate the parallel portal-private API and direct-query stack so portal
   and clinical UI share one source of truth and one security surface.
2. **Move the `get_*.php` endpoints to `src/` controllers and services**, following the
   `portal/patient/` front controller (#15058) or the `get_patient_info.php` →
   `PatientPortalLoginController` pattern. These procedural endpoints are now the oldest
   stratum left in the portal.
3. **Patient-submitted data is held in the review queue** (`src/Services/PatientReview/`,
   `ReviewQueueService`) for staff approval, never written straight to the chart, and never
   to `onsite_portal_activity`. Portal submissions and SMART patient-app writes (#15162)
   already share it.
4. **Keep all patient-scoped access behind the §4 guards** (`PortalPatientAccessGuard` /
   `PortalSessionPidGuard` / `PortalApiCsrfGuard`) until typed, scoped repositories
   ([subsystems.md → Permissions](subsystems.md#permissions-and-authorization)) make the scope
   structural rather than a runtime check.
5. **New portal UI in Twig**, new portal logic in injected services, new portal tables in
   `sql/database.sql` ([layers.md → schema](layers.md#database-schema-design-and-governance)).
6. **Extend via events**, never by editing core portal files (§7).

> **Under discussion (not settled):** a larger proposed step is to re-home the whole portal as
> a **core module** once the REST/FHIR APIs are mature enough to back it — with **Phreeze
> removal as the first chore** (done: #15020, #15021, #15058, #15071). That would change the
> end-state (a module rather than `portal/` in the tree). It is recorded here as a direction
> under review by the maintainers, not a committed plan.

---

## 11. Reference implementations in-tree

| Area | Canonical example | Era |
|------|-------------------|-----|
| Transitional login (thin shim → tested controller) | `portal/get_patient_info.php` → `src/Controllers/Portal/PatientPortalLoginController.php` | Modern — copy this |
| Credential repository | `src/Controllers/Portal/SqlPortalLoginCredentialsRepository.php` (interface + impl) | Modern |
| Credential provisioning service | `src/Services/PatientAccessOnsiteService.php` | Modern |
| Portal JSON API routing (typed per-route access) | `src/Portal/Routing/PortalRouteTable.php`, `src/Controllers/Portal/Patient/PortalFrontController.php` | Modern |
| Review queue (patient-submitted data) | `src/Services/PatientReview/ReviewQueueService.php` | Modern |
| Patient-scope guards | `src/Common/Session/PortalPatientAccessGuard.php`, `PortalSessionPidGuard.php`; `src/Common/Csrf/PortalApiCsrfGuard.php` | Modern |
| Secure messaging sender | `src/Services/PortalMessagingSender.php` | Modern |
| Dashboard presentation | `portal/home.php` + `templates/portal/*.html.twig` | Mixed |
| Legacy AJAX data endpoints (to migrate) | `portal/get_*.php` | Legacy |

---

## 12. Portal technology stack and deprecation

The portal shares the core stack and its direction — see
[tech-stack.md](tech-stack.md) for PHP, MariaDB/MySQL, Twig/Smarty, the C-CDA/CQM move to PHP,
and the jQuery/Knockout/vanilla-JS plan. The table below is the **portal-specific** surface on
top of that. (Status legend as in [`tech-stack.md`](tech-stack.md).)

| Technology | Where | Role | Status |
|---|---|---|---|
| Twig | `templates/portal/*.html.twig`, `templates/portal/patient/` | Dashboard / login / registration / portal-patient presentation | **Keep** (§6) |
| Vanilla-JS fetch client | `portal/patient/scripts/portal-api.js`, `portal-page.js`, `scripts/app/*.js` | Client for the portal JSON API (document review, signing, profile) | **Keep** (§6) |
| Bootstrap / jQuery (dashboard) | `setupHeader([...])` bundles | Dashboard CSS/JS, shared with core | **Upgrade / Deprecate** — tracks core |
| i18next | dashboard asset bundle | **Client-side lookup over the shared `xl()` translations** — `portal/home.php` feeds `TranslationService::getLanguageDefinitionsForSession()` into `i18next.init()`, exactly as core does in `interface/main/tabs/main.php`; the JS `xl()` (`library/js/utility.js`) is `top.i18next.t()` | **Keep** |
| signature_pad (UMD) | `portal/sign/assets/signature_pad.umd.js` | E-signature capture | **Keep** (vanilla) |
| reCAPTCHA (Google) | `portal/account/register.php` | Registration bot defense | **Keep** |
| Portal JSON API | `src/Portal/Routing/PortalRouteTable.php` | Portal's private data API | **Converge** → core services + REST/FHIR (§5, §10) |

**Portal deprecation priorities.**

1. **The portal JSON API → core services/REST** (§5) — the single most important portal move;
   it collapses the parallel data stack onto one source of truth. (The Phreeze framework,
   Backbone client, and plain-PHP views that used to top this list were removed in #15020,
   #15021, #15058, #15071.)
2. **The `get_*.php` endpoints → `src/` controllers and services** — now the oldest stratum
   left in the portal (§10, item 2).
3. **jQuery/Knockout/AngularJS/Moment** — same vanilla-JS-first direction as core.

Add no new usage of anything marked **Deprecate**/**Converge** here; follow the strangler
strategy ([migration.md](migration.md)) to remove existing usage.

---

*Keep this document honest: the portal is modernizing fast in `src/Controllers/Portal/` and
`src/Services/`, so verify a named class still exists and still works as described before
relying on it, and update this file as the remaining `get_*.php` endpoints are retired.*
