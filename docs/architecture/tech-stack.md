# Technology stack and deprecation plan

Part of the [OpenEMR architecture docs](README.md). What the architecture runs on, and where
each piece is headed. The portal-specific additions (Phreez, Backbone, LABjs) are in
[portal.md → Portal technology stack](portal.md#12-portal-technology-stack-and-deprecation).

**Status legend:**

- **Keep** — strategic; invest here.
- **Upgrade** — keep, but track major-version upgrades.
- **Freeze** — legacy; no new usage, migrate out opportunistically.
- **Deprecate** — actively being removed; add no new usage, plan the replacement.
- **Evaluate** — replacement candidate; decision pending.

Versions below are drawn from `composer.json`, `package.json`, and the dev
`docker/development-easy/` stack at time of writing — verify against those files, which are
the source of truth.

---

## Language, runtime, database

| Technology | Version / where | Role | Status |
|---|---|---|---|
| PHP | `>= 8.3` (`composer.json`) | Application language | **Upgrade** — track 8.4+ (native `#[\Deprecated]` needs 8.4) |
| MariaDB | `mariadb:12.3.x` (docker dev/CI) | Primary RDBMS in dev/CI | **Keep** |
| MySQL | via `ext-mysqli` / `ext-pdo_mysql` | Supported alternative RDBMS | **Keep** — maintain parity |
| Doctrine DBAL | `^4.4` | DB abstraction under the ADODB-shaped surface | **Keep / grow** (see [layers.md](layers.md#data-access-layer)) |
| CouchDB | optional docker service | Offsite document store (see [subsystems.md](subsystems.md#document-management)) | **Freeze** — fold behind Flysystem adapters |

**MySQL vs MariaDB.** Support both. MariaDB is the containerized dev/CI default; MySQL
remains supported in deployments. Keep SQL **portable across both** — `utf8mb4` + consistent
collation (see [layers.md → schema](layers.md#database-schema-design-and-governance)), and
avoid engine-specific syntax that diverges between them. There is no plan to drop either.

---

## Backend frameworks & templating

| Technology | Version | Role | Status |
|---|---|---|---|
| Symfony components | 7.x | HTTP, DI, EventDispatcher, Console | **Keep / grow** |
| Laminas (Zend) MVC | — | Legacy module controller stack | **Freeze** — migrate modules toward `src/Controllers` |
| Twig | 3.x | Template engine | **Keep** (see [layers.md](layers.md#presentation-layer-twig)) |
| Smarty | 4.5 | Legacy template engine | **Deprecate** — no new templates |

---

## Build toolchain

| Technology | Version | Role | Status |
|---|---|---|---|
| Node.js | `>= 24` (`package.json` engines) | **Build toolchain only** (webpack/SASS) | **Keep** — *for the build* |
| Webpack | 5.x | Asset bundling | **Keep** |
| SASS | 1.x | Stylesheet compilation | **Keep** |

> **Node.js has two distinct roles — do not conflate them.** As the **build toolchain**
> (webpack, SASS) it is strategic and stays. As a **runtime service** for C-CDA it is a
> deprecation target (next table).

---

## Runtime services

| Technology | Where | Role | Status |
|---|---|---|---|
| C-CDA / CQM (now PHP) | `src/Services/Cda/` (incl. `Schematron/SchematronValidator.php`), `src/Cda/`, `src/Cqm/` | C-CDA generation + Schematron validation + CQM calculation, in PHP | **Largely done** — PHP has replaced the Node `oe-cda-schematron` / `oe-cqm-service` sidecars; the untracked `ccdaservice/` npm packages are legacy/vestigial, pending removal |

**Node.js for C-CDA/CQM → PHP (effectively done).** The Schematron validation and C-CDA/CQM
processing have been **reimplemented in PHP**: `src/Services/Cda/Schematron/SchematronValidator.php`
states outright that it *"replaces the Node.js oe-schematron-service / oe-cda-schematron
sidecar"*, `SchematronParser.php` is a port of `parseSchematron.js`, C-CDA generation is PHP
(`src/Cda/`, the Carecoordination module's `CcdaGenerator`), and CQM is PHP
(`src/Cqm/PhpCqmCalculation`). The `ccdaservice/` directory (Node packages, not git-tracked)
is legacy/vestigial — confirm no deployment path still invokes it, then remove it. (The
external ONC *reference* C-CDA validator, referenced by `CdaValidateDocuments`, is a separate
third-party service, not our sidecar.) Node remains only for the *build* (webpack/SASS,
above).

---

## Frontend libraries

| Technology | Version | Role | Status |
|---|---|---|---|
| Bootstrap | 4.6.2 | CSS framework | **Upgrade** — toward 5.x (which drops the jQuery dependency) |
| jQuery | 3.7.1 | DOM / AJAX | **Deprecate** — prefer vanilla JS for new code where feasible |
| Knockout.js | 3.5.3 | MVVM behind the tabbed shell | **Deprecate** — coupled to shell modernization |
| AngularJS | 1.x (listed in `AGENTS.md`; **not** a current npm dep) | Legacy pockets only | **Deprecate** — remove remaining usages |
| React | `15.1.0` (pinned GitHub zip, ~2016) | Isolated legacy usage | **Deprecate** — dangerously old; remove or modernize |
| select2 | 4.1.0 | jQuery-bound select widget | **Evaluate** (jQuery-coupled) |
| DataTables | 1.13.11 | Table widget | **Evaluate** |
| Moment.js | 2.31.0 | Date handling | **Deprecate** — upstream is maintenance-only; move to native `Intl` / a modern date lib |
| Backbone.js | 1.6.1 | MVC client for the portal's Phreez JSON API (`portal/patient/scripts/`) | **Retire with Phreez** — see [portal.md](portal.md#12-portal-technology-stack-and-deprecation) |
| Underscore.js | 1.13.8 | Backbone's utility/template dependency | **Retire with Phreez / Backbone** |
| DOMPurify | 3.4.x | HTML sanitization | **Keep** |

**Vanilla-JS-first.** Write new client code in modern vanilla JS (ESM), reaching for a
library only when it clearly earns its place. jQuery, Knockout, and AngularJS are the three
flagged for retirement: add no new usages and remove them opportunistically when you touch a
file — but do **not** rewrite working UI purely to drop a library. Knockout's removal is
coupled to modernizing the tabbed shell
([interface-and-forms.md](interface-and-forms.md#the-application-shell-mainphp)), not a
standalone task.

---

## Deprecation summary — what not to add in new code

- **Smarty** templates — and bare PHP/HTML (with inline CSS/JS) "polyglot" pages — → Twig
  presentation (with logic moved to a controller/service).
- **New Laminas-MVC** core controllers → `src/Controllers` + Symfony `Request`.
- **jQuery / Knockout / AngularJS / React** → vanilla JS.
- **Moment.js** → native `Intl`.
- **Node.js as a runtime** for C-CDA/CQM — **already replaced by PHP**; remove the vestigial
  `ccdaservice/`. Node stays only for the build.

See [portal.md § Portal technology stack](portal.md#12-portal-technology-stack-and-deprecation)
for the portal-specific additions.
