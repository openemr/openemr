# Interface architecture: the tabbed shell, menus, sessions, encounters, and forms

Part of the [OpenEMR architecture docs](README.md). This subsystem is where the two-axis
model (see the hub's diagram) is most visible: the UI shell is a request pipeline, and its
menus/tabs are extended by modules through the event seam. It is also the largest reservoir
of legacy code and therefore the biggest migration target. The patient-facing portal is a
separate app documented in [portal.md](portal.md).

---

## The application shell (`main.php`)

After login the browser loads **`interface/main/tabs/main.php`**, a single-page shell built
on **Knockout.js** (`interface/main/tabs/js/tabs_view_model.js`, the `app_view_model`). The
shell is not a page that navigates; it is a persistent frame that hosts **a set of tabs, each
an `<iframe>` loading a legacy `interface/…` page**. This is how OpenEMR runs thousands of
procedural pages inside one modern-feeling application without rewriting them — the shell is
itself a strangler wrapper. The tab strip is rendered via
`OpenEMR\Events\Main\Tabs\RenderEvent` and filtered by `OpenEMR\Tabs\DefaultTabsFilter`, and
the shell holds the CSRF tokens (`csrf_token_js`, plus a separate `api_csrf_token_js` for the
REST/LocalApi stack) and site/session context that inner frames read via `top`.

## Patient header / dashboard

Above the tabs sits the **patient header** (`interface/main/tabs/templates/patient_data_template.php`)
— the demographic banner and dashboard shown whenever a patient is "in context" (a `pid` is
set in the session). It persists across tab changes so every inner frame shares one patient
context, and clearing the patient resets that context across the shell.

## Menus and how they are overridden

Menus are **role-based JSON documents** resolved by `src/Menu/MainMenuRole.php`:

- A role (`getMenuRole()`) maps to a menu file — a built-in one under
  `interface/main/tabs/menu/menus/*.json` (`standard.json`, `chart_review.json`,
  `front_office.json`, `answering_service.json`), **or** a site-custom override loaded from
  `sites/<site>/documents/custom_menus/<name>.json`. Dropping a custom JSON in that directory
  is the no-code way to override the menu for a site.
- The code-level override seam is the event axis: `MainMenuRole` dispatches
  **`OpenEMR\Menu\MenuEvent`** with `MENU_UPDATE` (listeners add or modify entries — this is
  how a module injects its menu items) and `MENU_RESTRICT` (listeners apply ACLs to decide
  what is shown). `PatientMenuEvent`/`PatientMenuRole` do the same for the patient-level menu.
  A module contributes menu items by subscribing to these events — it never edits a core menu
  file (the core↔module rule; `AGENTS.md` → Module/Core Boundaries).

## Tabs: locked tabs and side-by-side

Each tab is a `tabStatus(title, url, name, loading_label, closable, visible, locked)`
observable (`tabs_view_model.js`). Two behaviors worth knowing:

- **Locked tabs.** `tabLockToggle()` pins a tab (`locked(true)`). The reuse/replace paths
  guard on `locked()`, so a pinned tab is **not reused or replaced by navigation** — opening
  another page spawns a *new* tab instead. (A pinned tab can still be closed explicitly via
  `tabClose()`, and `openExistingTab()` updates an existing tab that matches the same name.)
  E-signed ("locked") **encounters** are a related but distinct concept. In the main menu,
  `isEncounterLocked()` (`tabs_view_model.js`) blocks adding a form to a locked encounter,
  except Fees > Fee Sheet; the encounter page's own form menu applies
  `LockedEncounterBillingForms` (see [Encounters as the clinical focus](#encounters-as-the-clinical-focus)).
- **Side-by-side.** The shell can show two frames at once; inter-frame coordination goes
  through `interface/main/tabs/js/frame_proxies.js` rather than direct cross-frame calls.

## Cookie / session management — the `read_and_close` mechanism

Because the shell fires **many concurrent requests from multiple frames/tabs against one
session**, PHP's default session file lock (which serializes requests sharing a session)
would stall the whole UI. OpenEMR's distinctive mechanism, driven from
`interface/globals.php` and `src/Common/Session/` (`SessionConfiguration`,
`SessionWrapperFactory`, `SessionUtil`):

- A page is **read-only by default**: the session is opened, read, and **immediately
  `session_write_close()`d** (PHP's `read_and_close`), releasing the lock so sibling frame
  requests proceed in parallel.
- **`$sessionAllowWrite = true`** set *before* including `globals.php` selects a **read-write
  session for the whole request** (instead of the read-only default). **Legacy** code that still
  writes raw `$_SESSION`, or code that must hold a writable session across the whole request,
  requires it. **New code uses the session abstraction (next section), never raw `$_SESSION`,**
  and ordinary abstraction-backed writes do **not** need the flag — they reopen read-and-close
  storage and save on their own. Setting it holds the session lock for the whole request
  (reducing frame concurrency), so use it only when a request genuinely needs a writable
  session throughout.
- The mechanism works under both the native file handler and the predis/Redis-sentinel
  session handler.

**Access session state only through the session classes — never raw `$_SESSION`.** This
holds for *all* session access, reads and writes alike:

- Obtain the session from **`SessionWrapperFactory`** — `getActiveSession()` for the active
  session, or `getCoreSession()` / `getPortalSession()` (which return the *existing* active
  session unless called with `reset: true`, so confirm you are in the intended realm before
  touching realm-specific state) — or have it injected as a Symfony **`SessionInterface`** (as
  `CarePlanController` does). Use the **session bag**'s accessors: `get()`, `set()`, `has()`,
  `remove()`.
- Writes go through the abstraction too. `WriteThroughSession` (which extends Symfony's
  `Session`, backed by an attribute bag) persists writes automatically.
- **Raw `$_SESSION` reads and writes are forbidden** (hub anti-patterns). Touching the
  superglobal bypasses realm separation (core vs portal vs setup), the write-through
  persistence, and the read-only/lock handling above — all of which the classes exist to
  manage.
- **Keep two concerns separate:** *how* you access session data — always the abstraction, which
  reopens read-and-close storage and saves each mutation on its own — versus *whether the whole
  request runs with a writable session*, which is what `$sessionAllowWrite` controls. Ordinary
  abstraction-backed writes persist **without** `$sessionAllowWrite`; reserve it for requests
  that truly need a writable session for their whole lifecycle.

See also [layers.md → Cross-cutting concerns](layers.md#cross-cutting-concerns).

## Encounters as the clinical focus

The clinical data model centers on the **encounter** (a patient visit):

- `form_encounter` is the encounter record itself; `forms` is the registry of **every form
  instance attached to an encounter** — one row per form carrying `formdir`, the per-form
  `form_id`, the `encounter`, and `pid`.
- Workflow state lives in the session: opening a patient sets `pid`; selecting or creating an
  encounter sets `encounter`; forms are then added to *that* encounter.
- The **encounter form itself is a form** — `interface/forms/newpatient/`
  (`C_EncounterVisitForm`) creates/edits the visit. A locked (e-signed) encounter accepts no
  new forms **except its billing forms — the Fee Sheet and Misc Billing Options**
  (`src/Forms/LockedEncounterBillingForms.php`): the encounter page's form menu offers only
  those two, and an existing one keeps its Edit button. (A Misc Billing Options form that was
  itself e-signed and locked stays locked.)

## PHP forms: structure, registration, management

A traditional OpenEMR form is a **directory under `interface/forms/<dir>/`** following a
script convention:

| File | Role |
|------|------|
| `new.php` | create / edit entry point |
| `view.php` | edit an existing instance |
| `save.php` | persist the submission |
| `report.php` | render the form in the encounter summary |
| `info.txt` | form metadata (name, category) |
| `table.sql` | the form's own `form_<dir>` table DDL |

Registration and lifecycle:

- **`registerForm()`** (`library/registry.inc.php`) inserts the form into the **`registry`**
  table (name, `directory`, `category`, `state`, etc.); `getRegistered()` lists active forms
  (filterable by encounter type) to build the "add form" menu; `state` toggles a form on/off.
- **`addForm()`** (`library/forms.inc.php`) writes the `forms` row linking a new instance to
  the current encounter; `getFormByEncounter()`, `authorizeForm()`, and `hasFormPermission()`
  round out the per-encounter form API.

## LBF / LBT: Layout Based Forms and Transactions

Not every form is hand-coded. **Layout Based Forms (LBF)** and **Layout Based Transactions
(LBT)** are *data-driven*: an administrator defines the fields in the
**`layout_options`** and **`layout_group_properties`** tables through
`interface/super/edit_layout.php`, and a single generic engine
(`interface/forms/LBF/`) renders them. The renderer is **`generate_form_field()`** in
`library/options.inc.php`, a large `switch` on `data_type` (text, long text, date, provider
list, list dropdown, checkbox, and dozens more). LBF instances are encounter forms; LBT are
non-encounter transactions (e.g. referrals).

- **LBF plugins.** A layout can attach custom PHP via a
  `sites/<site>/LBF/<formname>.plugin.php` file (loaded by `interface/forms/LBF/new.php`),
  letting a site inject logic into the generic renderer without a bespoke form directory —
  e.g. the bundled `LBFvbf`, `LBFgcac`, `LBFathv` plugins under `sites/default/LBF/`.
- LBF/LBT stay the right tool for **admin-configurable** forms; they are not the thing being
  retired.

## Direction: forms toward MVC

Hand-built forms are migrating off the `new.php`/`view.php`/`save.php`/`report.php` +
`table.sql` procedural quartet toward the **MVC shape in `src/`**, exactly as the Care Plan
form demonstrates (see the hub's reference table and [migration.md](migration.md)):

- **Controller + service + Twig in `src/`** — e.g.
  `src/Controllers/Interface/Forms/CarePlan/`, `src/Services/Forms/CarePlanFormService.php`,
  and `*.html.twig` templates — with the form directory's `new.php`/`view.php` reduced to thin
  entry points ([migration.md → the thin entry point](migration.md#the-thin-entry-point)).
- **Tables defined in `sql/database.sql`** (and the versioned upgrade script, see
  [layers.md → schema](layers.md#database-schema-design-and-governance)), not a standalone
  `table.sql` the installer runs in isolation.
- **Reuse the service through the API** where possible ([api.md](api.md)), so the same form
  logic backs the UI, the REST surface, and any CLI consumer.

Build new hand-coded forms this way; migrate existing ones opportunistically when you touch
them. LBF/LBT remain for configurable forms.
