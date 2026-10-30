# The end-state: a single front controller for the whole application

Part of the [OpenEMR architecture docs](README.md).

> **This document is forward-looking synthesis, not current `AGENTS.md` doctrine.**
> `AGENTS.md` documents the pieces (Symfony `Request`, `CurrentRequest::get()`, constructor
> DI) but does not describe a unified front controller. The trajectory below is drawn from
> the code (`public/index.php`, PR #9943); confirm the current state before assuming a given
> request already flows through it in a given deployment.

The long-horizon goal is that the application stops being a directory of individually
addressable PHP scripts and becomes **a set of routes dispatched by one front
controller.** The REST API already proves one half of this with `apis/dispatch.php` (see
[api.md](api.md)). The other, broader half now exists too, and **it is the mechanism we
recommend building toward: `public/index.php`.**

---

## `public/index.php` is the emerging front controller — prefer it

A whole-application front controller landed in **PR #9943** (*"Initial front-controller
implementation"*) and is the route the frontend is expected to take, rather than growing
`apis/dispatch.php` (which is, and should stay, the REST/FHIR doorway). The pieces:

- **`public/index.php`** — the single web entry point. It builds a PSR-7 `ServerRequest`
  from globals, obtains a **PSR-11 container**, and asks a router what to do with the
  request.
- **`bootstrap.php`** — shared application setup for *every* SAPI (web **and** CLI),
  returning the DI container. It is deliberately narrow: autoloader, environment,
  error handling, DI tooling — and explicitly **no** database, sessions, HTTP-derived
  data, or `globals` config. This is the unified bootstrap a modern controller or CLI
  command is built on.
- **`src/BC/FallbackRouter.php`** — the strangler seam. `performLegacyRouting()` works out
  which legacy file a URL *would* have hit, normalizes the superglobals so that file can't
  tell the difference, and the front controller `require`s it. **Existing legacy pages run
  unchanged, through the front controller.** (`BC` = backward compatibility; treat
  everything there as transitional scaffolding, not a target API.)
- **`FRONT_CONTROLLER_USED`** — a constant the front controller defines so misconfigured
  deployments that bypass it can be detected (see the shutdown-function sketch in the file).

Why this is the one to back, over a bespoke UI router or more `apis/dispatch.php`:

1. **It is whole-application, not API-only.** `apis/dispatch.php` routes REST/FHIR;
   `public/index.php` is positioned to front *all* web traffic.
2. **It matches our DI target exactly** — a real PSR-11 container (`Firehed\Container`) and
   a PSR-7 request, the shape the [controller layer](layers.md#controller-layer) asks every
   controller to accept.
3. **It is already a working strangler fig.** Nothing has to be rewritten to adopt it:
   legacy files flow through `FallbackRouter` today, and modern routes get added *ahead* of
   the fallback incrementally. No big-bang cutover.
4. **It unifies web and CLI on one bootstrap** — which is precisely what makes the
   "[internal endpoints should have a frontend and/or a CLI consumer](api.md#services-and-endpoints-must-be-exercised-the-consumer-question)"
   recommendation cheap to honor: the CLI utility and the web request resolve the same
   services from the same container.

The file's own "future scope" note states the plan directly: put a modern `Router` ahead of
the fallback, so *well-formed new routes execute without touching the legacy systems* and
must rely only on modern conventions (DI, no globals). That is the seam new controllers
should aim to register against.

> **Prior art.** An earlier front-controller draft exists as PR #12643 (a Slim-based attempt).
> The router choice is not settled, and — per that author — the front-controller hook can land
> **independently of** the broader module/plugin work; the two need not be coupled.

---

## Trajectory

1. **Now:** individual `interface/*.php` files, most still procedural; `public/index.php`
   fronts them via `FallbackRouter` where deployed.
2. **Thin entry points:** each file bootstraps + delegates to a controller
   ([migration.md](migration.md)). The logic and markup are already gone; only the
   file-per-URL routing remains.
3. **Modern routes ahead of the fallback:** controllers register against the primary router
   `public/index.php` is designed to grow (the `$container->get(Router::class)` seam),
   reusing the API's central route-declaration model (`_rest_routes.inc.php` +
   `HttpRestRouteHandler`). A controller no longer needs its own entry-point file to be
   reachable; the `FallbackRouter` only handles what hasn't been migrated.
4. **Fallback shrinks to nothing:** as routes cover more of the surface, the pool of files
   flowing through `FallbackRouter` drains, and the thousands of individual entry-point
   shims fall away.

Each phase is reachable from the previous one without a big-bang cutover. Thinning an entry
point (phase 2) is valuable even if phases 3–4 are years out, because it is a prerequisite
for them and an improvement on its own. **Do phase-2 work whenever you touch an
`interface/` file; that is how the fallback eventually empties.**

Supporting infrastructure already exists alongside it: `src/Core/Kernel.php`,
`src/Core/OEHttpKernel.php`, the `src/Core/Routing/` classes (e.g. the Symfony-routing
strangler seam for zend_modules, PR #12481), and the module application/bootstrap system
are the beginnings of a real HTTP kernel the UI routes through.

> **Caveat — confirm before treating as settled.** `public/index.php` is the emerging
> direction, but as of this writing `bootstrap.php` notes it is still used only by an
> experimental CLI tool, and `FallbackRouter` lives under the `BC` (transitional)
> namespace. Treat this as a strong recommendation to build *toward*, and check the current
> state of these files (and any follow-up to PR #9943) before assuming a given request
> already flows through the front controller in a given deployment.
