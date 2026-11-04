# Removing Phreeze from the patient portal

Status: plan. Branch `remove-phreeze`, based on master `3b309e05ac` (2026-11-03).

Phreeze is the LGPL PHP framework that serves everything under `/portal/patient/`. It provides the
routing (`Dispatcher`, `GenericRouter`), the controller base (`PortalController`), the ORM
(`Phreezer`, `Phreezable`, `Criteria`, `Reporter`, and a second MySQLi connection), and the Savant
template engine. This plan removes it in three pull requests that can merge in any order, each leaving the portal working.

## Rules for every PR

- **Keep every URL.** Callers outside `portal/patient/` hard-code them (listed in Inventory → Callers).
  `.htaccess`, the nginx configs and `src/BC/FallbackRouter.php` keep rewriting `/portal/patient/*`
  to `index.php?_REWRITE_COMMAND=...` throughout, so none of them change.
- **Keep the JSON contract** each Backbone view reads, field for field: camelCase keys, the paged
  shape `{rows, totalResults, totalPages, pageSize, currentPage, orderBy, orderDesc}`, and the error
  shape `401 {success:false, message, errors:{field:msg}}`. Before swapping an endpoint, add a test
  that records its current output, then check the replacement against it. The Backbone front end
  stays; replacing it is a separate project.
- **Keep every access rule** the router and guards enforce today (Inventory → Access rules), and
  carry the existing security tests over to the new code instead of deleting them.
- **New code goes in `src/`** and follows AGENTS.md: strict types, `QueryUtils`, services injected
  into the controller, Symfony `Request`, a typed principal for authorization, no new baseline
  entries.
- **Each PR removes the baseline entries for the files it deletes or fixes.**
- **The review-queue migration (#14969, `263b76d8a7`) is the model.** It replaced the
  OnsitePortalActivity and OnsiteActivityView stacks with `PortalReviewController` and services in
  `src/Services/PatientReview/`, on the same route keys, without touching the JavaScript.

## Inventory

### Size

| Part | Files | Lines | Baseline entries |
|---|---|---|---|
| `fwk/` (framework) | 79 PHP | — | 1,336 |
| `libs/` (controllers, models, DAOs, reporters) | 24 | ~5,600 | 2,910 |
| `templates/*.tpl.php` | 10 | ~1,780 | 99 |
| `_*_config.php`, `index.php` | 4 | ~700 | 34 |
| **Total** | | | **4,379** (6.5% of the baseline) |

`PatientCriteriaDAO.php` alone has 1,365 baseline entries, and `UserCriteriaDAO.php` (dead code) has
855.

### Routes (`_app_config.php`)

| Route | Controller method | Status |
|---|---|---|
| `GET:`, `GET:provider` | `Provider.Home`: the staff Portal Dashboard | live |
| `GET:patientdata` | `Patient.ListView`: the demographics form (portal profile, registration, staff review) | live |
| `GET:api/patientdata` | `Patient.Query`: `PatientReporter`; with pid 0 it runs `DESCRIBE patient_data` | live |
| `POST:api/patient` (`p_none`, `p_reg`) | `Patient.Create`: inserts the patient at registration | **blocked, see Bugs** |
| `GET:api/patient/(:num)` | `Patient.Read` | live |
| `PUT:api/patient/(:num)` | `Patient.Update`: staff "Commit to Chart", then `completeProfileChange` | live |
| `DELETE:api/patient/(:num)` (`p_limited`) | `Patient.Delete`: hard-deletes the `patient_data` row | **no caller; remove** |
| `GET/PUT:api/portalpatient/(:num)` | `PortalPatient.Read/Update`: pending profile change through the review queue | live |
| `GET:onsitedocuments` | `OnsiteDocument.ListView`: portal, dashboard and module modes | live |
| `GET:onsitedocument/(:num)` | `OnsiteDocument.SingleView`: its template does not exist | **dead** |
| `GET/POST/PUT/DELETE:api/onsitedocument(s)` | `OnsiteDocument.Query/Read/Create/Update/Delete` | live |
| `api/onsiteportalactivit*`, `onsiteactivityview*` | `PortalReview.*`: already on `src/Services/PatientReview` | live |
| `DELETE:api/onsiteportalactivity/(:num)` (`p_none`) | `PortalReview.DocumentCancel`: no session can reach it | **dead** |
| `*:api/(:any)` | `Provider.ErrorApi404`: has no `p_acl`, so it returns 401 before running | effectively dead |

### Access rules that must survive

- **Portal session**: the session has `pid` plus `patient_portal_onsite_two` (`index.php`). It may
  use only `p_all` routes, and `p_limited` only when the URL id equals `bootstrap_uri_id`.
- **Staff session**: everything else, which `_machine_config.php` admits only with `authUserID` and
  the `patientportal/portal` ACL. Staff may use `p_all` and `p_staff`. `p_none` and `p_limited` are
  refused.
- **Registration**: `register === true` restricts a session to routes marked `p_reg: true`.
- `PortalSessionPidGuard::assertRequestKeysMatchSession($_POST, $_GET, $_REQUEST)` runs on every
  portal request and rejects any pid-family key that differs from the session pid.
- `PortalPatientAccessGuard::assertCanRead/Write`: a portal session must own the pid. Staff need
  `patientportal/portal` plus `patients/demo`.
- `AppBasePortalController::ApplyRequestEqualsFilters` lets request keys set only `_Equals` filters,
  never the operator or the column. The bitwise criteria are cast to int
  (`OnsiteDocumentCriteriaSqlInjectionTest`).
- Tests that record these rules: `GenericRouterStaffAclTest`,
  `PortalBootstrapCoreSessionRejectionIsolatedTest` (it reads `GenericRouter.php` by path),
  `PatientControllerSecurityTest` (mostly placeholders), `PortalSessionPidGuardTest`,
  `OnsiteDocumentCriteriaSqlInjectionTest`, `FallbackRouterTest`,
  `UnserializeAllowedClassesTest` (it requires `ConnectionSetting.php`).

### Front end

- Backbone 1.6.1, underscore 1.13.8 and jQuery come from `public/assets` and are loaded through
  LAB.js. Nothing outside `portal/patient/` uses Backbone, underscore templates or `$LAB`.
- `scripts/model.js` defines the models and collections. The live ones are OnsiteDocument,
  OnsitePortalActivity, OnsiteActivityView (the collection only), Patient and PortalPatient. The
  `PortalPatientCollection`, User, InsuranceCompany and InsuranceData models have no server route.
- The page scripts are `scripts/app/patientdata.js`, `onsitedocuments.js`,
  `onsiteportalactivities.js` and `onsiteactivityviews.js`.
- `templates/portal/home.html.twig` calls `page.updateModel()` from the injected `patientdata.js`.
  The registration Twig template reads the `patientdata` iframe's DOM and posts
  `{submitForm:true}` to it.
- `OnsiteActivityViewListView` dispatches the CustomEvent
  `openemr:portal:provider:onsiteactivityview:click`. Nothing in core listens for it, but it is a
  module extension point, so keep it.

### Callers outside `portal/patient/`

- `onsitedocuments?pid=…`: `portal/home.php:243`, `templates/portal/home.html.twig:578`,
  `templates/documents/general_list.html:58` (module mode), and the faxsms onetime links
  (`NotificationEventListener.php:161`).
- `patientdata`: `home.html.twig:318` (the profile dialog), the registration iframe
  (`portal_register.html.twig:321`), and the staff review dialog.
- `provider`: `standard.json:2023`, `sites/default/documents/custom_menus/Custom.json:2017`,
  `portal/messaging/messages.php:187,222`, and `portal/import_template_ui.php:580`.
- `onsiteactivityviews[?activity=payment]`: `interface/main/tabs/js/user_data_view_model.js:88,102`,
  `portal/portal_payment.php:463`, and the E2E test `PortalPaymentStaffReviewTest`.
- Infrastructure: `composer.json` (classmap at :199-202, and `autoload.files` at :238-240, which loads
  `FieldMap`, `IObserver` and `KeyMap` on **every request**), `FallbackRouter.php:135-136,242`, the
  nginx `_REWRITE_COMMAND` rewrites (`ci/nginx`, `docker/library/dockers/dev-nginx`, `setup.php:1684`),
  and `.phpstan/rector-scan.neon`.
- No PHP outside `portal/patient/` uses a Phreeze class.

### Services to reuse

- `src/Services/PatientService.php`: `getFreshPid()` (to replace `max(pid)+1`), `databaseInsert`,
  `databaseUpdate` (which also records the history that Phreeze's raw save skips), and `getOne`.
- `src/Services/PatientReview/*`: the profile, document and payment review workflows, already used.
- `src/Services/DocumentTemplates/DocumentTemplateRender.php`: `fetchTemplateDocument` and
  `doRender`, already used by `OnsiteDocument.Create/Read`.
- `src/Common/Session/PortalPatientAccessGuard`, `PortalSessionPidGuard`.
- Missing today: a CRUD service for `onsite_documents`, and the DEM `layout_options` "exclude in
  portal" lookup.

## Bugs and gaps found

1. **Self-registration cannot create the chart.** `portal/account/register.php:46-47` sets `pid=true`
   and `register=true`, but never sets `patient_portal_onsite_two`. `bootstrap_pid` therefore stays
   empty, the router takes the staff branch, and the `p_none` route `POST:api/patient` is refused.
   The deny-by-default rule came in with `0e6dd527c0` (#13855). This has not been reproduced in a
   browser yet (that needs reCAPTCHA keys). If confirmed, it is fixed in PR 1.
2. **A portal patient can hard-delete their own `patient_data` row** through
   `DELETE:api/patient/(:num)` (`p_limited`). No UI calls it. PR 2 removes the route.
3. **The OnsiteDocument routes and `Patient.ListView` have no staff ACL of their own.** They rely on
   the bootstrap's `patientportal/portal` check alone. Staff can read or write any patient's
   documents through `patientId`. PR 3 adds `PortalPatientAccessGuard`.
4. **The Phreeze API endpoints have no CSRF check.** `PortalController::VerifyCSRFToken` is never
   called. PR 3 adds one.
5. **Unescaped output**: `OnsiteActivityViewListView.tpl.php:31` (`cuser`) and
   `PatientListView.tpl.php:93,290` (`$row[...]`). PR 3 fixes these when the templates move to Twig.
6. **Possible telehealth bug**: when `portal_onsite_two_basepath == 1`,
   `oe-module-comlink-telehealth` builds `/portal/patient/home.php`, which falls into the Phreeze
   rewrite. Verify, and fix in the module, outside this project.

## Pull requests

No PR waits on another to merge. Any steps that only work after another piece has landed are in the
same PR. That gives three PRs, and they can be reviewed and merged in any order.

| PR | What it does | Depends on |
|---|---|---|
| 1 | Fix: self-registration can create the chart | nothing |
| 2 | Delete dead Phreeze code and unreachable routes | nothing |
| 3 | Replace Phreeze: documents, profile, router, templates; delete `fwk/` | nothing |

PR 3 is the large one, because every step after the first needs the step before it. Once the ORM
is gone, the router can be replaced. Once the controller base is replaced, the templates can leave
Savant. And `fwk/` can only be deleted once the router and templates are off it. It is built as
one commit per step below, so it can be reviewed commit by commit, and the portal works after every
commit.

The PRs can touch the same files. If PR 2 merges first, PR 3 drops those files from its own
deletions when it rebases. If PR 1 merges first, PR 3 carries its registration rule into the new
router. Either order resolves with an ordinary rebase, without changing any code.

### PR 1: `fix(portal): let self-registration create the patient chart`

- Confirm the bug in a browser with reCAPTCHA test keys.
- Make the registering visitor reach `POST:api/patient`, and nothing else that is not `p_reg`.
  Either the router treats a `register === true` session as its own branch, ahead of the staff
  branch, or the route gets an access level meant for registration. Do not let it reach `p_none`
  routes in general.
- Extend `GenericRouterStaffAclTest` with registration cases: `POST:api/patient` is allowed, and
  every other non-`p_reg` route is refused.
- Small, and can ship on its own as a bug fix.

### PR 2: `refactor(portal): delete dead Phreeze code and unreachable routes`

Deletions only, with no change in behaviour.

- **Framework**: all of `fwk/libs/util/`, the 7 unused Savant plugins and filters under
  `savant/resources/`, `MockRouter`, `PHPRenderEngine`, `ObserveToFile`, `ExportUtility`,
  `MemCacheProxy`, `CacheMemCache` and `VsDateUtil`.
- **App**: the User stack (`User`, `UserCriteria`, `UserDAO`, `UserMap`, `UserCriteriaDAO`),
  `OnsiteDocumentReporter`, `templates/_Header.tpl.php`, and the commented-out User routes.
- **Routes**: `DELETE:api/patient/(:num)` (bug 2), `GET:onsitedocument/(:num)` and
  `DELETE:api/onsiteportalactivity/(:num)`, plus the controller methods behind them. Also the
  methods no route reaches (`PortalPatient.ListView/Query/Delete` and `Default/Provider` `Error*`
  except `Default.Error404` and `Provider.ErrorApi404`, which the `api/(:any)` catch-all routes
  still name).
- **JS**: the dead models in `model.js`, `pageAudit.destroy`, and `page.deleteModel`.
- **Baseline**: about 1,100+ fewer entries.
- **Checks**: `php -l`, PHPStan, the isolated tests, and a manual click through the profile,
  documents and review pages.

### PR 3: `refactor(portal): replace the Phreeze framework`

Before any code moves, the first commits add the tests every later commit is checked against.

**Commit 1: tests that record today's behaviour**
- E2E smoke tests: portal document fill and submit, staff review of a document, portal profile
  edit, and staff "Commit to Chart". None exist today.
- DB tests that record the JSON from `api/onsitedocuments`, `api/onsitedocument/{id}` (read,
  create, update, delete), `api/patientdata`, `api/patient/{id}` and `api/portalpatient/{id}`.

**Commit 2: documents off the ORM**
- Add `src/Services/PortalDocuments/` with an `OnsiteDocumentRepository` built on `QueryUtils`
  (find, page by pid with the `Locked` and `filter` rules, insert, update, delete), plus a readonly
  `OnsiteDocument` DTO that maps the 20 columns to the camelCase JSON.
- The search is a fixed method with whitelisted columns and sort order; no request key reaches SQL.
  Replace `OnsiteDocumentCriteriaSqlInjectionTest` with tests of that method, keeping its "request
  input cannot choose the operator" intent.
- `OnsiteDocumentController` keeps its routes and JSON and calls the repository. Add
  `PortalPatientAccessGuard` read and write checks for staff (bug 3), and add tests for document
  ownership (none exist today).
- Delete `OnsiteDocument`, `OnsiteDocumentDAO`, `OnsiteDocumentMap`, `OnsiteDocumentCriteria` and
  `OnsiteDocumentCriteriaDAO`.

**Commit 3: patient profile off the ORM**
- Add a portal profile service in `src/Services/` that maps `patient_data` rows to and from the
  camelCase field set (one mapper for both directions, replacing `PatientMap`, `PatientReporter`
  and the inline snake_case→camelCase code in `PortalPatient.Read`). It uses
  `PatientService::databaseUpdate`, `databaseInsert` and `getFreshPid`.
- Move the DEM `layout_options` exclude lookup and the portal-provider list out of
  `Patient.ListView` into that service.
- Registration (pid 0) gets explicit empty defaults, replacing the `DESCRIBE patient_data` trick.
- Replace the `PatientControllerSecurityTest` placeholders with real tests.
- Delete `Patient`, `PatientDAO`, `PatientMap`, `PatientCriteria`, `PatientCriteriaDAO` and
  `PatientReporter`.

**Commit 4: delete the ORM**
- Nothing uses it after commits 2 and 3. Delete `Phreezer`, `Phreezable`, `DataSet`, `DataPage`,
  `QueryBuilder`, `Criteria*`, `Reporter`, `FieldMap`, `KeyMap`, `IDaoMap*`, the caches,
  `DataAdapter`, `ConnectionSetting`, the MySQLi driver and `DatabaseConfig`.
- Remove the second database connection in `_machine_config.php`, the three `autoload.files`
  entries in `composer.json`, and `UnserializeAllowedClassesTest`'s use of `ConnectionSetting`.

**Commit 5: new router and controllers**
- Add `src/Portal/Routing/`: a route table where each route declares a typed access rule (an enum,
  e.g. `Any`, `Staff`, `OwnPatient`, `None`, plus whether registration may use it). Add a small
  dispatcher that builds a typed principal (portal patient, registering visitor, or staff user) from
  the session.
- The controllers become `src/` classes that take a Symfony `Request` and their services through
  the constructor, and return `JsonResponse` or `Response`.
- `index.php` stays the entry point and keeps reading `_REWRITE_COMMAND`, so `.htaccess`, nginx and
  `FallbackRouter` are unchanged. It keeps the `PortalSessionPidGuard` call and the error handling
  (401 JSON for `api/`, the fatal page otherwise). The `_machine_config.php` bootstrap becomes a class.
- Move `GenericRouterStaffAclTest` and `PortalBootstrapCoreSessionRejectionIsolatedTest` over to
  the new router, keeping every case, including the registration cases from PR 1.
- Delete `Dispatcher`, `GenericRouter`, `IRouter`, `ActionRouter`, `UrlWriter`, `PortalController`,
  `AppBasePortalController`, `RequestUtil`, `Context`, the Auth classes, `_global_config.php` and
  `_app_config.php`.

**Commit 6: CSRF on the API**
- Check a CSRF token on POST, PUT and DELETE (bug 4). The pages print the token, and `model.js`
  sends it as a header on every Backbone sync. It is a separate commit so it can be reviewed, or
  split out, on its own.

**Commit 7: templates to Twig**
- Move the four live pages (`ProviderHome`, `PatientListView`, `OnsiteDocumentListView` and
  `OnsiteActivityViewListView`) and the two error pages to `templates/portal/patient/*.html.twig`.
  `_FormsHeader`, `_modalFormHeader` and `_Footer` become Twig partials. Fix the unescaped output
  (bug 5).
- Keep the element ids, the underscore `<script type="text/template">` blocks and the inline globals
  the page scripts and `home.html.twig` depend on.
- Add Twig render tests and fixtures for each page (`openemr-cmd update-twig-fixtures`).
- Delete `SavantRenderEngine`, `IRenderEngine`, `ObserveTo*` and `fwk/libs/savant/`.

**Commit 8: final cleanup**
- Delete what is left of `fwk/` and its `.htaccess`.
- Remove the `composer.json` classmap entries, `FallbackRouter.php:135,242` and their test cases,
  and `.phpstan/rector-scan.neon`'s `fwk` paths.
- Replace the Phreeze LGPL headers on rewritten app files. Code still derived from Phreeze keeps
  its attribution.
- Add a CHANGELOG entry, and remove every baseline entry for the deleted files.

**Checks for PR 3**: the commit 1 E2E and DB tests pass after every commit. Also run PHPStan, the
isolated tests and the Twig fixtures, and click through by hand: portal profile and documents,
registration, staff review list, document review and "Commit to Chart", module mode from Patient
Documents, and the Portal Dashboard.

## Open decisions

1. CSRF (commit 6): keep it in PR 3, or move it to its own PR after PR 3 merges?
2. Templates: Twig (recommended, with render tests), or keep plain PHP templates rendered by a
   small helper?
3. Replacing the Backbone front end: out of scope for this project (recommended), or a later
   project?
