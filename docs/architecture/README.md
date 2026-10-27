# OpenEMR Architecture & Target Patterns

**Status:** Living document — the direction we are steering toward, not a description of
where every file is today.
**Audience:** Anyone writing or reviewing OpenEMR code, human or AI agent.
**Relationship to [`AGENTS.md`](../../AGENTS.md):** `AGENTS.md` is the line-level coding
standard (types, null safety, PHPStan, DI rules, commit format). These documents are the
layer above it: *how the pieces fit together* and *how we move legacy code into that shape*.
When the two overlap, `AGENTS.md` wins on mechanics; these documents own the structural
"why" and the migration strategy.

This file is the **hub**: the shared mental model, the quick decision gate, and a routing
table to the detailed sub-documents. Read it first; open a sub-document only for the topic
you are touching.

---

## How these documents are organized (and why)

This is a set of small documents, not one large one, **deliberately — to fit AI-agent
context windows.** An AI agent (and the tools it uses) generally loads a referenced document
*whole*. A single monolith would force every agent, on every task, to pull ~15k tokens of
mostly-irrelevant prose into its context — crowding out the actual code it needs to reason
about, and raising cost and latency for no benefit. Splitting lets an agent load only the
slice that matters:

- **This hub** is small and cheap, and the thing an agent should reach for first. It carries
  the mental model (§Target architecture), the **decision gate** (the checklist and
  anti-patterns below), and the routing table — enough to place most changes correctly
  *without opening anything else*.
- **Each sub-document is self-contained** and covers one coherent topic, so loading it pulls
  in that topic's depth and nothing else.
- **The routing table** below is the index: match your task to one row, open that one file.

For humans this is a slight cost — no single-scroll read — so to read the whole architecture
in order: this hub → [layers](layers.md) → [subsystems](subsystems.md) →
[interface-and-forms](interface-and-forms.md) → [api](api.md) → [migration](migration.md) →
[front-controller](front-controller.md) → [tech-stack](tech-stack.md) → [portal](portal.md).
Keep each document honest against the code it describes; a split that drifts is worse than a
monolith.

---

## 1. The problem we are solving

OpenEMR began as procedural PHP: a URL maps directly to a `.php` file under `interface/`,
and that file does everything — reads `$_GET`/`$_POST`/`$_SESSION` directly, opens the
database inline, embeds business rules in the middle of HTML, and echoes markup as it
goes. There are still tens of thousands of such files:

| Tree          | Rough PHP file count | Role today                         | Direction |
|---------------|----------------------|------------------------------------|-----------|
| `interface/`  | ~27,000              | Procedural web UI + inline SQL     | Shrink → thin entry points, then routes |
| `library/`    | ~500                 | Legacy procedural helpers          | Migrate into `src/` services |
| `src/`        | ~2,200               | Modern `OpenEMR\` PSR-4 code       | Grow — the destination |

This architecture is hard to test, hard to reason about, impossible to type-check well,
and couples presentation, logic, and persistence so tightly that a change to one risks
all three. **The goal is not a rewrite.** It is a steady, file-by-file migration toward a
layered architecture, using the *strangler fig* pattern: wrap the old entry point, move
its guts into typed classes, and leave a thin shim behind until routing can replace it
([migration.md](migration.md)).

---

## 2. Target architecture at a glance

The architecture has **two axes**. The *vertical* axis is the request pipeline — layers
each depending only on the layer below. The *horizontal* axis is the core↔module event
seam — the orthogonal channel through which modules extend core without core depending on
them (`AGENTS.md` → Module/Core Boundaries). Both are shown together because reading only
the vertical one invites the mistake the event seam exists to prevent: hardcoding module
behavior into core.

```
                      HTTP request
                           │
               ┌───────────▼────────────┐
               │  Routing / front        │   today: REST/FHIR routes via apis/dispatch.php;
               │  controller             │   legacy pages reached directly or via
               │  (emerging)             │   CurrentRequest. A unified front controller
               └───────────┬────────────┘   (public/index.php) is the forward-looking goal.
                           │  Symfony Request
               ┌───────────▼────────────┐           ┌──────────────────────────┐
               │  Controller             │           │  Modules                 │
               │  (src/Controllers/…)    │◀─events──▶│  (interface/modules/…)   │
               └───────────┬────────────┘  dispatch  │                          │
                  ┌─────────┴─────────┐     /        │  subscribe to core       │
        ┌─────────▼────────┐ ┌────────▼─────────┐    │  events and contribute   │
        │ Presentation     │ │ Service / Domain │◀───│  menus, UI, data         │
        │ (Twig templates) │ │ (src/Services/…) │ ev │  (MenuEvent, RenderEvent, │
        └──────────────────┘ └────────┬─────────┘    │   StyleFilterEvent, …)    │
                                      │              └──────────────────────────┘
                            ┌─────────▼─────────┐     core dispatches an event; a
                            │ Data access       │     listener in a module fills it in.
                            │ (centralized SQL) │     No core → module dependency, ever.
                            └─────────┬─────────┘
                                      │             QueryUtils now → Doctrine DBAL/ORM.
                                  Database          No SQL above the data layer. Ever.
```

**The vertical rule that makes the layering work:** dependencies point *downward and
inward*. Templates never touch the database. Controllers never write SQL. Services never
read `$_GET`. Each layer talks only to the one beneath it, through typed values. Detail:
[layers.md](layers.md).

**The horizontal rule that keeps modules decoupled:** dependencies point *one way, core →
module, and only through events*. Core dispatches an event carrying its inputs; a module
subscribes and contributes; with no module installed the event goes unanswered and core
behaves exactly as before. Core source must never name a module class.

> **Two footnotes on the diagram.** (1) In-tree modules also run on a legacy **Laminas
> (Zend) MVC** controller stack — the established mechanism for *module* controllers, not a
> target for new core UI (see [layers.md → Controller layer](layers.md#controller-layer)).
> (2) The front-controller box is *forward-looking synthesis*, not current `AGENTS.md`
> doctrine — see [front-controller.md](front-controller.md).

The canonical in-tree example of the whole stack is the **Care Plan encounter form**:
`interface/forms/care_plan/new.php` (thin entry point) →
`src/Controllers/Interface/Forms/CarePlan/CarePlanController.php` →
`src/Services/Forms/CarePlanFormService.php` →
`interface/forms/care_plan/templates/*.html.twig`. Read those four together before a similar
migration.

---

## 3. Routing table — match your task to one document

| If your change touches… | Read |
|---|---|
| Presentation/Twig, controllers, services, queries, **DB schema/DDL** | [layers.md](layers.md) |
| Permissions/ACL, file I/O, documents, i18n, encryption/keys | [subsystems.md](subsystems.md) |
| The tabbed shell, menus, tabs, sessions, encounters, **forms / LBF** | [interface-and-forms.md](interface-and-forms.md) |
| A REST/FHIR endpoint, or reusing one instead of reimplementing | [api.md](api.md) |
| Modernizing legacy code; deprecating/retiring a function | [migration.md](migration.md) |
| Routing / the `public/index.php` front controller | [front-controller.md](front-controller.md) |
| Choosing or retiring a library/runtime; version questions | [tech-stack.md](tech-stack.md) |
| Anything in the patient portal | [portal.md](portal.md) |

---

## 4. Decision gate — "where does my code go?"

Place each piece before writing. (This gate is mirrored as a trigger list in `AGENTS.md`.)

- **Is it HTML?** → a Twig template. Not Smarty, not `echo`. ([layers.md](layers.md#presentation-layer-twig))
- **Does it read `$_GET`/`$_POST`/`$_SESSION`, or decide which view to show?** → a controller
  action reading the Symfony `Request`. ([layers.md](layers.md#controller-layer))
- **Is it a business rule, calculation, orchestration, or authorization?** → a service in
  `src/Services/`. ([layers.md](layers.md#service--domain-layer))
- **Is it SQL?** → inside a service via `QueryUtils`. Never in a controller or template.
  ([layers.md](layers.md#data-access-layer))
- **Is it a schema change (DDL)?** → *both* the versioned `sql/..._upgrade.sql` and
  `sql/database.sql`, with table/column comments; FKs on numeric columns only; non-FK
  relationships recorded in `openemr.meta.xml`. ([layers.md](layers.md#database-schema-design-and-governance))
- **Does the REST/FHIR API already do this?** → call/extend the endpoint or its service.
  ([api.md](api.md))
- **A helper you were about to drop in `library/`?** → it is a service; put it in `src/`.
  ([migration.md](migration.md#where-migrated-code-lands))
- **A config value?** → `OEGlobalsBag` typed getter, injected — not `$GLOBALS`.
  ([layers.md](layers.md#cross-cutting-concerns))
- **A permission check?** → `AclMain` (heading to an injected authz service), at the
  boundary — not ad-hoc role strings. ([subsystems.md](subsystems.md#permissions-and-authorization))
- **File I/O?** → a Flysystem `FilesystemOperator` from `ManagerInterface` — not raw paths.
  ([subsystems.md](subsystems.md#filesystem-abstraction-flysystem))
- **User-facing text?** → the context-correct `xl*()` variant, with a literal string.
  ([subsystems.md](subsystems.md#internationalization-i18n))
- **At-rest encryption?** → `CryptoInterface`/`CryptoGen` — never hand-rolled crypto.
  ([subsystems.md](subsystems.md#security-encryption-and-key-management))
- **A new clinical/encounter form?** → MVC shape in `src/` (controller + service + Twig),
  table in `sql/database.sql`, thin entry points — not a procedural `new/view/save/report`
  quartet. LBF only for admin-configurable forms. ([interface-and-forms.md](interface-and-forms.md))
- **A menu/tab item for a module?** → contribute via `MenuEvent`/`RenderEvent`, not by
  editing a core menu JSON or template. ([interface-and-forms.md](interface-and-forms.md#menus-and-how-they-are-overridden))
- **Patient-portal behavior?** → reuse the same services/API as the clinical UI, never a
  portal-only copy. ([portal.md](portal.md))

If a single file would answer "yes" to several of these, that is the smell the layering
exists to remove — split it.

---

## 5. Anti-patterns we are actively retiring

Add no new instances of these; remove them opportunistically when you touch a file
(`AGENTS.md` → "Legacy Code Is Not the Standard"):

- SQL inline in a page or template; business logic inside Twig/Smarty/HTML.
- Direct superglobal access (`$_GET`, `$_POST`, `$_REQUEST`, `$_SESSION`, `$GLOBALS`) in
  logic; `filter_input()` anywhere.
- `new` on a service inside business logic; static service locators; global state as a
  service locator.
- New Smarty templates; new `library/` procedural helpers.
- API endpoints / services with no consumer in the tree — a frontend UX or CLI utility
  (ideally both) must exercise them. ([api.md](api.md#every-endpoint-needs-a-real-consumer))
- New call sites of an already-`@deprecated` function, or deprecating a function without
  converting it to a service-backed facade first. ([migration.md](migration.md#deprecating-and-retiring-legacy-functions))
- catch-`\Exception` (misses `\Error`), catch-log-continue, leaking `$e->getMessage()` to
  users.
- Core code naming a module class (use events).
- FKs on text/char columns; a schema change applied to only one of
  `sql/..._upgrade.sql` / `sql/database.sql`; tables/columns shipped without comments.
- Ad-hoc role-string permission checks instead of `AclMain`/an authz service.
- Raw filesystem calls or hardcoded `sites/<site>/…` paths instead of Flysystem; new
  document code extending `Document.class.php` rather than `DocumentService`.
- Bare user-facing strings, the wrong-context `xl*` variant, or a dynamic value passed to
  `xl()`.
- Hand-rolled openssl/sodium instead of `CryptoInterface`/`CryptoGen`.
- **Any raw `$_SESSION` read or write.** Session state is accessed only through the OpenEMR
  session classes (`SessionWrapperFactory`) and the Symfony `SessionInterface` session bag
  they return — never the superglobal. (The `$sessionAllowWrite` legacy global still gates
  whether writes *persist*; see [interface-and-forms.md](interface-and-forms.md#cookie--session-management--the-read_and_close-mechanism).)
- New hand-coded forms as a procedural quartet instead of the MVC shape.

### Deprecated technologies — do not add new usage

Smarty · new Laminas-MVC core controllers · jQuery · Knockout.js · AngularJS · React ·
Moment.js · Node.js **as a runtime** (the C-CDA service; Node stays for the *build*). Full
inventory, versions, and rationale: [tech-stack.md](tech-stack.md).

---

## 6. How this is enforced

Parts of this architecture are checked in CI:

- **PHPStan level 10** (`tests/PHPStan/Rules/` holds custom project rules — forbidden
  globals, forbidden direct instantiations, namespace/boundary rules).
- **PSR-12 / PER-CS** via phpcs; **Rector** for modernization.
- **Twig compilation + render tests**, **isolated unit tests**, DB-backed snapshot tests.
- **`composer-require-checker`** catches undeclared dependencies (including module/core
  boundary leaks).

Run the full suite with `openemr-cmd code-quality` (container) or `composer code-quality`
(host). See `AGENTS.md` → Code Quality / Testing for the complete command set.

---

## 7. Reference implementations in-tree

When in doubt, read working code rather than this document:

| Pattern | Canonical example |
|---|---|
| Full stack (entry → controller → service → Twig) | `interface/forms/care_plan/` + `src/Controllers/Interface/Forms/CarePlan/` + `src/Services/Forms/CarePlanFormService.php` |
| Table-backed service | `src/Services/BaseService.php` and any `*Service.php` extending it |
| Centralized query surface | `src/Common/Database/QueryUtils.php` |
| Twig environment + extensions | `src/Common/Twig/TwigContainer.php` |
| Whole-app front controller (direction) | `public/index.php` + `bootstrap.php` + `src/BC/FallbackRouter.php` (PR #9943) |
| REST front controller (API doorway) | `apis/dispatch.php` + `_rest_routes.inc.php` + `src/Common/Http/HttpRestRouteHandler.php` |
| Typed config access | `src/Core/OEGlobalsBag.php` |
| Core↔module event seam | `src/Events/Core/` (e.g. `StyleFilterEvent`, `TwigEnvironmentEvent`) |
| Permissions / ACL | `src/Common/Acl/AclMain.php` (ACO catalog documented in-file) |
| Filesystem abstraction | `src/Services/Storage/ManagerInterface.php` + `Manager.php` (Flysystem) |
| Document management | `src/Services/DocumentService.php`; legacy `library/classes/Document.class.php` |
| Internationalization | `library/translation.inc.php` (`xl`/`xlt`/`xla`/`xlj`) + `src/Common/Translation/TranslationCache.php` |
| Encryption & key management | `src/Common/Crypto/CryptoGen.php` (`CryptoInterface`, `KeySource`/`KeyVersion`) |
| Tabbed UI shell | `interface/main/tabs/main.php` + `js/tabs_view_model.js` (Knockout) |
| Menu system + override seam | `src/Menu/MainMenuRole.php` + `MenuEvent`; JSON in `interface/main/tabs/menu/menus/` |
| Session no-lock mechanism | `interface/globals.php` (`$sessionAllowWrite`) + `src/Common/Session/` (`read_and_close`) |
| Encounter / form registry | `form_encounter` + `forms` + `registry` tables; `library/forms.inc.php`, `library/registry.inc.php` |
| Legacy PHP form (quartet) | `interface/forms/newpatient/` |
| MVC form (target) | `interface/forms/care_plan/` + `src/Controllers/Interface/Forms/CarePlan/` |
| Layout Based Forms (LBF/LBT) | `interface/forms/LBF/` + `library/options.inc.php` (`generate_form_field`); `sites/<site>/LBF/*.plugin.php` |
| Patient portal | [portal.md](portal.md) |

---

*Keep this document honest: when a new pattern becomes the agreed standard, update the hub
and the relevant sub-document, and point to the in-tree example that demonstrates it. An
architecture doc that drifts from the code it describes is worse than none.*
