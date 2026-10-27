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
context. See [`docs/main_dashboard_patient_clear_architecture.md`](../main_dashboard_patient_clear_architecture.md)
for how patient context is set and cleared.

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

- **Locked tabs.** `tabLockToggle()` pins a tab (`locked(true)`); a locked tab is not reused
  or closed by subsequent navigation (the `closeTab`/replace paths guard on `locked()`), so
  opening another page spawns a *new* tab instead of replacing the pinned one. E-signed
  ("locked") **encounters** are a related but distinct concept — `isEncounterLocked()` stops a
  locked encounter from accepting new forms.
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
- A page that genuinely must *write* session state sets **`$sessionAllowWrite = true`**
  *before* including `globals.php`; otherwise session writes in that request are not
  persisted. This is the one legacy global whose presence is load-bearing — respect it.
- The mechanism works under both the native file handler and the predis/Redis-sentinel
  session handler.

**Access session state only through the session classes — never raw `$_SESSION`.** This
holds for *all* session access, reads and writes alike:

- Obtain the session from **`SessionWrapperFactory`** — `getActiveSession()`, or
  `getCoreSession()` / `getPortalSession()` for a specific realm — or have it injected as a
  Symfony **`SessionInterface`** (as `CarePlanController` does). Use the **session bag**'s
  accessors: `get()`, `set()`, `has()`, `remove()`.
- Writes go through the abstraction too. `WriteThroughSession` (which extends Symfony's
  `Session`, backed by an attribute bag) persists writes automatically.
- **Raw `$_SESSION` reads and writes are forbidden** (hub anti-patterns). Touching the
  superglobal bypasses realm separation (core vs portal vs setup), the write-through
  persistence, and the read-only/lock handling above — all of which the classes exist to
  manage.
- **Keep two concerns separate:** *how* you access session data (always the abstraction)
  versus *whether* a write persists (gated by `$sessionAllowWrite` + `read_and_close`). You
  need both — the session bag to touch the data, and `$sessionAllowWrite = true` set before
  `globals.php` for that write to stick.

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
  new forms (fee-sheet exceptions aside).

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
