# Migration strategy — the strangler fig

Part of the [OpenEMR architecture docs](README.md).

We do not rewrite. We strangle the legacy code gradually, and the Care Plan form is the
pattern to copy:

- `interface/forms/care_plan/new.php` — thin entry point (the shim)
- `src/Controllers/Interface/Forms/CarePlan/CarePlanController.php` — the controller
- `src/Services/Forms/CarePlanFormService.php` — the service
- `interface/forms/care_plan/templates/*.html.twig` — the presentation

---

## The thin entry point

A legacy `interface/.../new.php` becomes a shim that bootstraps the environment, builds
the controller with its dependencies, and delegates. Everything it used to do inline now
lives in typed classes. From `interface/forms/care_plan/new.php`:

```php
require_once(__DIR__ . "/../../globals.php");   // legacy bootstrap stays, for now

$formService  = new FormService();
$twigContainer = new TwigContainer(__DIR__ . '/../../', $globalsBag->getKernel());

$controller = new CarePlanController(
    new CarePlanFormService($formService),
    $formService,
    $twigContainer->getTwig(),
    SessionWrapperFactory::getInstance()->getActiveSession(),
    ServiceContainer::getLogger(),
    $globalsBag->getString('rootdir'),
    $globalsBag->getWebRoot(),
    $globalsBag->getString('v_js_includes'),
);

$controller->newAction(CurrentRequest::get())->send();
```

The file is still the URL target, but it holds no logic — it is wiring. `CurrentRequest::get()`
is the transitional seam that gives procedural entry points the one Symfony `Request`
object without a full router (`AGENTS.md` → Request Input).

---

## The migration recipe

For a legacy page or helper:

1. **Identify the seams.** Separate, in your head, the three concerns tangled in the file:
   input handling, business logic + SQL, and HTML output.
2. **Extract the logic + SQL into a service** under `src/Services/` (extend `BaseService`
   if table-backed). Add unit tests against it — this is the payoff that makes the rest
   safe.
3. **Extract the HTML into a Twig template.** Move derived-value computation up into the
   controller; the template only formats.
4. **Create a controller** under `src/Controllers/…` that parses the `Request`, calls the
   service, and renders the template to a `Response`.
5. **Reduce the original `.php` file to a thin entry point** that builds the controller and
   delegates (see above).
6. **Verify the seam both ways** where modules are involved — exercise with and without
   the module and diff (`AGENTS.md` → Module/Core Boundaries).

Each step is independently shippable. A half-migrated feature (service extracted, entry
point not yet thinned) is still an improvement and still passes CI.

---

## Where migrated code lands

| Moving out of         | Lands in                          | As |
|-----------------------|-----------------------------------|----|
| `library/*.inc.php` helper | `src/Services/…` or `src/Common/…` | A class with injected deps |
| `interface/.../x.php` logic | `src/Services/…`             | A service method, unit-tested |
| `interface/.../x.php` HTML  | `interface/.../templates/*.html.twig` or `templates/` | A Twig template |
| `interface/.../x.php` input/dispatch | `src/Controllers/…`      | A controller action returning `Response` |

`library/` is a *legacy* home, not a modern one — new helpers do not go there; they go in
`src/` as services. `library/dated_reminder_functions.php` (which imports a module class
directly) is explicitly called out in `AGENTS.md` as something to migrate, not to imitate.

---

## Deprecating and retiring legacy functions

Most of `library/` is procedural functions called from thousands of places. You cannot move
the logic into a service *and* fix every caller in one change — so you decouple the two
steps with a **facade** and a **deprecation clock.**

**Step 1 — convert the function into a facade over a service.** Move the real logic into a
`src/Services/` (or `src/Common/`) class, then leave the original procedural function in
place as a thin, behavior-preserving wrapper that delegates to the service and emits a
deprecation notice. The function keeps its exact signature and return shape, so every
existing caller keeps working unchanged:

```php
/**
 * @deprecated since 7.0.3; use OpenEMR\Services\Foo\FooService::doThing().
 *             Scheduled for removal no earlier than 7.2.0 — migrate callers.
 */
function legacy_do_thing(int $pid, string $type): array
{
    \OpenEMR\BC\Deprecation::emit('legacy_do_thing() is deprecated; use FooService::doThing().');
    return (new \OpenEMR\Services\Foo\FooService())->doThing($pid, $type);
}
```

- **Use the project's deprecation mechanism**, `OpenEMR\BC\Deprecation::emit()`, not a bare
  `trigger_error`. It is runtime-configurable: `DeprecationMode::Warning` logs an
  `E_USER_DEPRECATED` in production, while `DeprecationMode::Error` *throws* — which is how
  dev and test environments turn an overlooked deprecated call into an immediate, visible
  failure (`interface/globals.php` sets `Error` mode in development). On PHP 8.4+ the native
  `#[\Deprecated]` attribute is an option too, but `Deprecation::emit()` is the portable
  path today and can be placed on specific branches, not just whole symbols.
- **Always pair the runtime notice with a `@deprecated` docblock** that names the
  replacement *and* the earliest removal version, so the migration path and the clock are
  discoverable from the source and from IDE tooling.
- The facade must not add behavior — it exists only to redirect callers to the service and
  to announce its own obsolescence. All new work (new callers, bug fixes to the logic) goes
  through the service directly.

**Step 2 — migrate the callers off the facade.** With the logic now in a service, work
through the call sites (frequently other `library/` and `interface/` files) and repoint
them at the service, as part of the normal strangler work (the recipe above). Each migrated
caller is independently shippable.

**Step 3 — remove the facade, then the file.** Once the last caller of a deprecated function
is gone, the facade can be deleted. When every function in a legacy `library/` file has been
retired this way, the file itself is removed.

### Removal policy and versioning

Removal is gated on **both** conditions, not just one:

1. **Zero remaining callers** in the codebase (and no reasonable expectation of external
   consumers still depending on it — see below).
2. **A deprecation window of at least one to two `Y` releases** on our `X.Y.Z` version
   cycle has elapsed since the symbol was deprecated. A function deprecated in `X.Y` is not
   removed before `X.(Y+1)` at the earliest, and preferably `X.(Y+2)`.

> **This deliberately breaks strict semantic versioning.** Under SemVer, removing a public
> function is a breaking change that mandates a major (`X`) bump; we instead retire
> deprecated symbols across minor (`Y`) releases. We accept that trade-off for
> **certification reasons** — our release cadence is constrained by certification cycles
> rather than by SemVer. The one-to-two-`Y`-release window is the compensating guarantee: it
> gives downstream consumers a predictable, published runway to migrate.

**Consumers and module authors must plan accordingly.** Anyone writing against OpenEMR's
procedural library functions — in-tree modules, third-party modules, downstream forks —
should:

- Treat any symbol carrying a `@deprecated`/`Deprecation::emit()` marker as already on its
  way out, and migrate to the named service replacement at the next opportunity rather than
  waiting for the removal release.
- Run their test/dev environments in `DeprecationMode::Error` so deprecated calls surface
  immediately instead of silently logging.
- Track the removal version named in the `@deprecated` tag and budget the migration within
  that one-to-two-`Y`-release window.

Writers of *new* code should never introduce a caller of an already-deprecated function; a
new call site resets no clocks and simply creates more migration debt.
