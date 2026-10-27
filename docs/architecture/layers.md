# The layers in detail

Part of the [OpenEMR architecture docs](README.md) — start at the hub for the big picture,
the decision checklist, and the routing table. This document covers the **request-pipeline
layers** (presentation → controller → service → data) and the database schema that sits
beneath them. The cross-cutting subsystems (permissions, filesystem, documents, i18n,
security, the interface shell) are in [subsystems.md](subsystems.md) and
[interface-and-forms.md](interface-and-forms.md).

**The rule for everything here:** dependencies point *downward and inward*. Templates never
touch the database. Controllers never write SQL. Services never read `$_GET`. Each layer
talks only to the one beneath it, through typed values.

---

## Presentation layer (Twig)

**Target:** All new HTML is rendered by **Twig 3.x**. Smarty (legacy) and raw
`echo`/`?>…<?php` interleaving are frozen — do not add to them.

Rules:

- **No business logic in templates.** A template receives a fully-prepared view model
  (plain arrays/DTOs) and formats it. No DB calls, no service lookups, no `$GLOBALS`,
  no computing derived data. If a template needs a value, the controller computes it and
  passes it in. (See `CarePlanController::newAction()` — every value the template needs
  is assembled first, then handed to `$this->twig->render(...)`.)
- **Escaping is the template's job, by default.** Twig autoescaping replaces the manual
  `text()`/`attr()` scattering of legacy pages. Reach for `|raw` only with a documented
  reason.
- **Rendering goes through `TwigContainer`** (`src/Common/Twig/TwigContainer.php`), which
  builds the `Twig\Environment`, registers OpenEMR extensions/filters, and dispatches
  `TwigEnvironmentEvent` so modules can extend it. Do not instantiate `Twig\Environment`
  by hand.
- **Templates have tests.** Compilation tests cover every `.twig` file; some have render
  fixtures. When you change a covered template, regenerate fixtures
  (`openemr-cmd update-twig-fixtures`) and review the diff. See
  `tests/Tests/Isolated/Common/Twig/`.

Why Twig over Smarty: a single actively-maintained engine, real autoescaping, testable
compilation, sandboxing for module-supplied templates, and first-class PHPStan/IDE support
for the code that feeds it. Every Smarty template retired is one fewer engine to secure
and teach.

---

## Controller layer

Thin, HTTP-only, constructor-injected. A controller's entire job is: **parse the HTTP
request into typed values, call a service, choose a view, and return a `Response`.** No
business rules, no SQL, no HTML assembly beyond handing a view model to Twig.

The shape we want (abbreviated from `CarePlanController`):

```php
final class CarePlanController
{
    public function __construct(
        private readonly CarePlanFormService $carePlanFormService,
        private readonly FormService $formService,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
        private readonly LoggerInterface $logger,
        // ...config values injected as typed scalars, not read from $GLOBALS
    ) {}

    public function newAction(Request $request): Response
    {
        if (!$this->formService->hasFormPermission(self::FORM_DIR)) {
            return $this->denyAccess('render');
        }
        $formId = $request->query->getInt('id');          // typed input, no $_GET
        $rows   = $this->carePlanFormService->getCarePlanRows(/* ... */);  // logic lives in the service
        return $this->createResponse($this->twig->render($template, [/* view model */]));
    }
}
```

Non-negotiables:

- **All dependencies through the constructor** (`AGENTS.md` → Dependency Injection). No
  `new SomeService()` inside an action, no static service locators, no reaching into
  `$GLOBALS`/`$_SESSION`/`$_GET` from within the controller body.
- **Input via the Symfony `Request`** (`$request->query`, `$request->request`, …), never
  the superglobals and never `filter_input()`. See `AGENTS.md` → Request Input for the
  full mapping and the `$_REQUEST` caveat.
- **Return a `Response`;** let the caller `->send()` it. This is what lets the same
  controller back a route later without rewriting it.
- **One action per meaningful operation** (`newAction`, `saveAction`), authorization
  checked at the top, CSRF enforced on writes.
- Controllers live under `src/Controllers/` (web UI) or `src/RestControllers/` (API),
  namespaced to mirror the path.

> In-tree modules also run on a legacy **Laminas (Zend) MVC** controller stack
> (`src/Core/Routing/Zend*`, `src/Core/ModulesApplication.php`). That is the established
> mechanism for *module* controllers, not a target for new core UI — new core controllers
> follow the shape above.

---

## Service / domain layer

Business rules, orchestration, validation, and authorization belong in services under
`src/Services/`. This is the layer that should grow as `library/` and `interface/`
shrink.

- **Extend `BaseService`** for table-backed services; it centralizes field metadata,
  UUID handling, search, and query plumbing (`src/Services/BaseService.php`):

  ```php
  class ExampleService extends BaseService
  {
      public const TABLE_NAME = "example";
      public function __construct() { parent::__construct(self::TABLE_NAME); }
  }
  ```

- **Services own the queries.** A controller or template that needs data asks a service;
  the service is the *only* place that knows the table shape. This is what "centralize all
  queries into service classes" means in practice.
- **Services are unit-testable in isolation** — constructor injection, injected clock
  (PSR-20) instead of `time()`, injected logger (PSR-3) instead of `error_log`. A service
  should not know whether it was called from the web UI, the API, a CLI command, or a
  test.
- **Parse at the boundary, trust types within.** Controllers hand services typed values
  (or domain primitives like `PatientId`); services do not re-validate raw strings on
  every call. See `AGENTS.md` → Parse, Don't Validate and Domain Primitives.
- **Logic is written once and reused across entry points.** If the web UI and the API
  both need the same operation, they call the same service method — they do not
  reimplement it. See [api.md](api.md).

---

## Data access layer

Centralized, heading to Doctrine. **No SQL above this layer.** Controllers and templates
never contain a query; services do, and eventually only a repository/Doctrine layer does.

- **Today:** use `QueryUtils` (`src/Common/Database/QueryUtils.php`) for queries — it is
  the typed, escaped surface over the legacy ADODB API. New code uses `QueryUtils`, not
  raw `sqlStatement()`/`sqlQuery()` sprinkled inline.
- **Direction:** consolidate persistence behind **Doctrine DBAL 4.x**, then the ORM, so
  the SQL surface becomes a small set of repositories rather than ad-hoc queries spread
  through services. Doctrine DBAL is already the real driver beneath the ADODB-shaped API.
- **Schema changes** go through the `sql_upgrade.php` system (see below). **Do not** use
  Doctrine Migrations yet — it is experimental and not adopted (`AGENTS.md` → Database and
  Global Settings).
- The end-state: a service depends on a repository *interface*; the Doctrine
  implementation is wired in configuration, not in business logic. That boundary is what
  will eventually let persistence change without touching the domain.

---

## Cross-cutting concerns

| Concern        | Target mechanism | Don't |
|----------------|------------------|-------|
| Configuration  | `OEGlobalsBag` typed getters (`getString`, `getInt`, `getBoolean`) | Read `$GLOBALS` directly |
| Logging        | Injected PSR-3 `LoggerInterface`, context arrays | `error_log`, string interpolation into messages |
| Time           | Injected PSR-20 `ClockInterface` | `new DateTime()`, `time()` in logic |
| Session        | OpenEMR session classes (`SessionWrapperFactory`) → the Symfony `SessionInterface` session bag they return | Any raw `$_SESSION` read or write |
| Errors         | Catch `\Throwable`, let it propagate unless you can recover | catch-log-continue; expose `getMessage()` to users |

See `AGENTS.md` for the detailed rules behind each of these.

---

## Database schema design and governance

The data-access layer governs how code reaches the database; this section governs the
database *itself* — the physical schema, its relationships, and how changes to it are
delivered.

### Foreign keys: numeric only

- **Model relationships with foreign keys on numeric columns** — integer surrogate keys
  (`id`, `*_id`). This is the default for any new relationship between tables.
- **Do not place foreign keys on text/character columns.** Char/varchar/text foreign keys
  are a persistent source of breakage in this codebase: collation mismatches between the two
  columns silently defeat the constraint or the join, character-set differences compound it,
  and OpenEMR's long history of joining on textual values (list option ids, codes, free
  text) means those columns are not clean enough to carry referential integrity. Relate
  tables by their numeric surrogate keys and keep the text as descriptive data, not as a
  join key.
- A relationship that genuinely can only be expressed textually (legacy list ids,
  pipe-delimited id lists like `patient_data.care_team_provider`, cross-table ids with no
  enforceable constraint) does **not** get a database foreign key — it gets *documented* as
  one instead (see the meta.xml rule below).

### Every schema change goes in two places (soon three)

A DDL change is not complete until it lands in **both** canonical locations, which must never
drift apart:

1. **The versioned upgrade script** — `sql/<X_Y_Z>-to-<A_B_C>_upgrade.sql` (the latest is
   `sql/8_5_0-to-8_6_0_upgrade.sql`), run by `sql_upgrade.php`. This migrates **existing**
   installations from one release to the next.
2. **The full baseline schema** — `sql/database.sql`. This is what a **fresh** install
   builds from. A change applied only to the upgrade script leaves new installs missing it
   (and vice versa).

Make upgrade-script statements **idempotent and conditional** using the directives
`sql_upgrade.php` understands — `#IfMissingColumn`, `#IfNotTable`, `#IfNotRow`,
`#IfNotIndex`, `#IfColumn`, `#IfNotColumnType`, … each closed by `#EndIf` — so re-running an
upgrade (or upgrading from an intermediate point) is safe. Mind the parser's constraints:
per the `AGENTS.md`/module gotchas, keep comments on their own full lines — a trailing
`-- comment` after a DDL statement can break the installer; only full-line comments are
safe.

> **Soon: Doctrine Migrations.** The plan is to move schema change management onto Doctrine
> Migrations. It is **not adopted yet** — `AGENTS.md` is explicit that Doctrine Migrations
> are experimental and must not be used for schema changes today. Until that switch is made
> deliberately and documented here, the two-file (`upgrade` + `database.sql`) process is the
> required path, and new work should be written so it will be straightforward to express as
> a migration later (discrete, ordered, reversible-in-spirit changes).

### Document logical relationships that have no physical foreign key

When a relationship is real but, by the rule above, is **not** enforced with a database
foreign key, record it as metadata in
`Documentation/EHI_Export/schemaspy/schemas/openemr.meta.xml`. That file carries the logical
foreign-key graph that the physical schema cannot, and it feeds the EHI Export and SchemaSpy
documentation. Each such column gets a `<foreignKey table="…" column="…" />` entry and a
`comments="…"` description, exactly as `patient_data` already documents its
`providerID`/`ref_providerID`/`created_by` links to `users.id`. Keeping this file current is
how a text-keyed or list-encoded relationship stays discoverable even though no constraint
expresses it.

### Comment every table and every column

- **Every table** carries a `COMMENT='…'` stating its purpose (`sql/database.sql` already
  does this widely, e.g. *"Links person records to patient_data records when person becomes
  patient"*).
- **Every column** carries a comment describing what it holds and why — **even when the
  author is sure the intent is obvious.** "Obvious" does not survive contact with a schema
  this large, this old, and this heavily integrated; the next reader, the EHI export, and
  the FHIR mapping all depend on a stated intent rather than an inferred one. Mirror the same
  description into the column's `comments="…"` in `openemr.meta.xml` so the documentation and
  the live schema agree.

### Additional schema best practices

- **Surrogate primary key on every table:** an auto-increment integer `id` as the PK.
  Natural/business keys (codes, MRNs) are columns with their own unique index, not the PK.
- **Add a `uuid` column** for any record exposed externally (API, FHIR, EHI export), managed
  through `UuidRegistry` — external consumers reference the UUID, internal joins use the
  numeric id.
- **InnoDB, always,** for new tables — transactions and real foreign-key support. Do not
  create MyISAM tables.
- **`utf8mb4` with a single consistent collation** across related columns. Consistent
  collation is not optional housekeeping: it is the same root cause behind the "no text
  foreign keys" rule, so uniformity here keeps even non-FK text joins predictable.
- **Index foreign-key columns and frequent query predicates.** An unindexed `*_id` join
  column is a performance bug waiting to happen.
- **Prefer a join table over a delimited list.** Pipe-delimited id lists (as in some legacy
  `care_team_*` columns) are a documented legacy shape, not a pattern to repeat — new
  many-to-many relationships get a proper junction table with numeric foreign keys.
- **Explicit types and intent:** `DECIMAL` for money (never float), `DATETIME`/`DATE` with
  explicit nullability and defaults, `tinyint(1)` for booleans. State `NOT NULL` and a
  default deliberately rather than by accident.
- **Keep logic out of the database.** No business rules in triggers or stored procedures —
  that logic belongs in the service layer (above), where it is typed, tested, and reusable.
- **All access through the service/`QueryUtils` layer** — the schema rules here and the
  access rules there are two halves of one data architecture.
