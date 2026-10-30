# Enforcement: turning the architecture into CI checks

Part of the [OpenEMR architecture docs](README.md). This document catalogs **which rules in
these docs are mechanically enforced today**, which are **candidates** for automation (with the
mechanism and rough effort), and which must stay **human review**. It is the bridge between the
prose rules and the CI that guards them.

> **Status: analysis + roadmap, not a work order.** Everything under "Candidate" below is a
> *proposal*. **Do not implement new PHPStan/lint rules from this document yet** — it is
> published so the architecture direction can be reviewed first; rules should be added only
> after the direction is ratified, so we don't automate a target that then shifts. The
> "Enforced today" section describes checks that *already exist*; it adds nothing new.

---

## How enforcement works in this repo

- **Custom PHPStan rules** live in [`tests/PHPStan/Rules/`](../../tests/PHPStan/Rules/) (with a
  [README](../../tests/PHPStan/Rules/README.md)) and are registered in
  `.phpstan/extension.neon`. PHPStan runs at **level 10**. This is the primary mechanism for
  structural rules and the easiest to extend.
- **The reusable pattern** is a *path-scoped forbidden-symbol* rule: forbid a function, method,
  class, or namespace, except under an allowed path. `ForbiddenClassesRule` (which forbids
  laminas-db outside `interface/modules/zend_modules/`) and the "no legacy `sql.inc.php`
  functions in `src/`" rule are the templates most new rules should copy.
- **phpcs** (`phpcs.xml.dist`) enforces PSR-12 / PER-CS and a set of Slevomat sniffs (style,
  type hints, import hygiene).
- **ESLint / Stylelint** (`npm run lint:js`, `npm run stylelint`) guard the front end — the
  right home for JS library-deprecation rules.
- **composer-require-checker** catches undeclared dependencies, including some core↔module
  boundary leaks.
- **codespell**, **conventional-commits**, **php -l**, and the **Twig compilation/render** and
  snapshot tests round out the suite. Run it all with `openemr-cmd code-quality` /
  `composer code-quality`.

## Status legend

- **Enforced** — a check already fails CI on violation.
- **Candidate** — automatable; not yet built. Effort is rough (S/M/L) and names the mechanism.
- **Heuristic** — partially automatable; a lint can catch blatant cases but cannot prove the
  rule. Pair with review.
- **Human** — a judgment call; keep it in the `AGENTS.md` gate and code review, not a rule.

---

## 1. Enforced today

These doc rules already fail CI. No new work — cited so the docs and the checks stay in sync.

| Rule (from the docs) | Enforced by |
|---|---|
| No `$_GET`/`$_POST`/`$_REQUEST`/`$_SERVER`/`$_COOKIE`/`$_FILES` in logic; **no `filter_input()`** | `ForbiddenRequestGlobalsRule`, `ForbiddenGlobalsAccessRule` |
| No raw `$_SESSION` read or write | `ForbiddenSessionSuperglobalRule`, `ForbidDirectSessionWriteRule` |
| No `global` keyword; no `$GLOBALS` | `ForbiddenGlobalKeywordRule`, `ForbiddenGlobalNamespaceRule` |
| Use `OEGlobalsBag` typed getters, not `get()` + cast | `OEGlobalsBagTypedGetterRule` |
| Catch `\Throwable`, not `\Exception`; no exit in catch/finally | `ForbiddenCatchTypeRule`, `ForbiddenExitInCatchFinallyRule` |
| No raw `sqlStatement()`/`sqlQuery()` in `src/` (use `QueryUtils`) | scoped forbidden-functions rule (`.phpstan/extension.neon`) |
| laminas-db only inside `zend_modules` (a boundary rule) | `ForbiddenClassesRule` |
| No `error_log`/`mail`/`call_user_func`/`eval`/raw `curl_*`/shell exec | `ForbiddenFunctionsRule`, `ForbiddenCurlFunctionsRule`, `ForbiddenEvalRule`, `ForbiddenShellExecutionRule`, `ForbiddenStaticMethodsRule` |
| Dynamic (non-literal) value passed to `xl()` | the `literal-string` parameter type (PHPStan native) |
| Undeclared dependencies / some core↔module leaks | `composer-require-checker` |
| PSR-12 / PER-CS style, import hygiene, file headers | phpcs + Slevomat (`phpcs.xml.dist`) |

## 2. Candidate — high value, low effort (reuse an existing pattern)

| Rule | Mechanism | Effort | Notes |
|---|---|---|---|
| **Core must not name a module class** (`OpenEMR\Modules\*` / vendor-prefixed) outside `interface/modules/` | new PHPStan rule modeled on `ForbiddenClassesRule` (namespace-prefix + path scope) | **S** | The most-repeated boundary in the docs; near-identical precedent exists. `class_exists()` string probes are the sanctioned exception. |
| **No hand-rolled crypto** (`openssl_*`, `sodium_*`, `hash_hmac`) outside `Common\Crypto` | extend `ForbiddenFunctionsRule` with a path allowlist | **S** | Pushes code to `CryptoInterface`/`CryptoGen`. |
| **No new Smarty templates** | CI diff gate failing on an added `*.tpl` / new Smarty usage | **S** | Simple check on the PR diff; Smarty is frozen. |
| **Deprecated front-end libs** (jQuery `$`/`jQuery`, `ko.`, `angular.`, `moment(`) in new JS | ESLint `no-restricted-globals` / `no-restricted-imports` | **S** | ESLint is already wired (`npm run lint:js`); the natural home. |
| **Calls to `@deprecated` symbols** flagged | enable/confirm `phpstan/phpstan-deprecation-rules` | **S** | Makes the deprecation clock ([migration.md](migration.md)) self-enforcing. Config, not code. |

## 3. Candidate — medium effort (custom tooling, high value)

| Rule | Mechanism | Effort | Notes |
|---|---|---|---|
| **Schema governance** over `sql/database.sql` + upgrade scripts: (a) no FK on text/char columns, (b) every table/column has a `COMMENT`, (c) upgrade ↔ `database.sql` parity | custom SQL linter, building on the existing `Sql/` rules (`SqlReservedWordRule`, `SchemaColumnRegistry`) | **M–L** | The biggest net-new capability; directly guards [layers.md → schema](layers.md#database-schema-design-and-governance). |
| **No raw filesystem I/O in `src/`** (`fopen`/`file_get_contents`/`file_put_contents`) → Flysystem | extend `ForbiddenFunctionsRule`, tightly scoped | **M** | Noisy without a careful allowlist; scope narrowly. |
| **PSR-3: no string interpolation in log messages** | custom PHPStan rule on `LoggerInterface::*()` first arg | **M** | Well-defined; pairs with the existing logging rules. |
| **No `new` of a `*Service` in controllers/business logic** | extend `ForbiddenInstantiationsRule` | **M** | Needs a precise "service" definition (namespace/suffix) to avoid false positives. |
| **New document code must not extend `Document.class.php`** | `ForbiddenClassesRule`-style (forbid `extends`/reference in new code) | **M** | Steers to `DocumentService` ([subsystems.md](subsystems.md#document-management)). |

## 4. Heuristic — lint can nudge, not prove

| Rule | Why only partial |
|---|---|
| No SQL / no business logic in Twig templates | Needs a Twig-level lint for forbidden functions/filters in `.twig`; catches blatant cases, not "logic" in general. |
| Context-correct Twig escaping (autoescape is off) | True enforcement needs taint analysis; practically, lint for un-filtered output and forbid re-enabling autoescape. |
| Module menu/tab items contributed via events, not hardcoded in core templates | The module-namespace rule (§2) catches code references, not hardcoded markup. |

## 5. Human review — do not try to automate

These are judgment calls; they live in the `AGENTS.md` gate and code review, not in a rule.

- **Internal (non-FHIR) endpoints have a real consumer** (UI and/or CLI) — cross-cutting, not
  statically decidable; unit-test presence *is* enforceable, the consumer judgment is not
  ([api.md](api.md#services-and-endpoints-must-be-exercised-the-consumer-question)).
- **New forms use the MVC shape** rather than the procedural quartet
  ([interface-and-forms.md](interface-and-forms.md#direction-forms-toward-mvc)).
- **Authorization via `AclMain` / a scoped service**, not ad-hoc role strings
  ([subsystems.md](subsystems.md#permissions-and-authorization)).
- **Compliance-governed output re-validated against the standard, not just CI**
  ([compliance.md](compliance.md)).
- **Deprecation lifecycle judgment** — when to deprecate, the removal-window call
  ([migration.md](migration.md#deprecating-and-retiring-legacy-functions)).

---

## Priority, when implementation is approved

Once the architecture direction is ratified, the suggested order is: the **module/core boundary
rule** (§2, most-repeated principle, direct precedent) → **hand-rolled-crypto** and
**new-Smarty** gates (§2, trivial) → **ESLint deprecated-libs** (§2) → the **schema linter**
(§3, largest new capability). Each should be run across the whole codebase first to size the
existing violation count and decide between a hard failure and a baseline-then-ratchet rollout
(`AGENTS.md` discourages growing the PHPStan baseline — prefer fixing, or scope a new rule to
`src/` where the tree is clean).

*Keep this document honest: when a candidate becomes an implemented check, move its row up to
§1 and name the rule file.*
