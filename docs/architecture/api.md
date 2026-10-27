# API-first: reuse endpoints instead of reimplementing

Part of the [OpenEMR architecture docs](README.md).

When functionality already exists as a REST or FHIR endpoint — or could reasonably be
expressed as one — **prefer driving that endpoint over writing a parallel implementation.**

- The API layer is already the furthest-along example of the target architecture:
  `apis/dispatch.php` is a real front controller, routes are declared centrally in
  `_rest_routes.inc.php`, and `src/Common/Http/HttpRestRouteHandler.php` dispatches to
  controllers under `src/RestControllers/` that delegate to the *same* `src/Services/`
  classes the web UI should use.
- Because REST controllers and web controllers call the same services, "use the API" and
  "use the service" converge: the service is the single source of truth, and the API is
  one typed, tested, documented doorway into it.
- When you build a new feature, ask: *does the API already expose this? Could this feature
  be a thin client of an endpoint instead of new inline logic?* If the API supports it,
  lean on it. If it almost supports it, extend the endpoint rather than forking the logic
  into the UI.
- See `API_README.md` and `FHIR_README.md`. New endpoints follow the existing
  route-declaration + RestController + Service pattern.
- **Protected API requests are authorized by OAuth2 scope** (SMART-on-FHIR, granular
  `.cruds`), layered over the ACL model, with their own OAuth2 key material. A few **public
  discovery endpoints are unauthenticated by design** — e.g. `GET /fhir/metadata`
  (the FHIR `CapabilityStatement`) and the SMART `.well-known/smart-configuration`. The
  architecture of scopes, keys, and client auth is in
  [subsystems.md → OAuth2 scopes and API authorization](subsystems.md#oauth2-scopes-and-api-authorization);
  the operational detail is in [`API_README.md`](../../API_README.md) and
  `Documentation/api/AUTHENTICATION.md` / `AUTHORIZATION.md`.

---

## Every endpoint needs a real consumer

An API endpoint — and the service class behind it — **must have at least one consumer in
the codebase: a frontend UX, a CLI utility, or both.** Prefer both where it makes sense.

The reason is exercise. Code with no caller is code no test, no reviewer, and no user ever
runs against a real request. It rots silently: its types drift, its edge cases go
unverified, its failures surface only when someone finally wires it up months later. An
endpoint that ships with a consumer is an endpoint that is proven to work end-to-end, kept
honest by an actual call site, and covered by whatever tests exercise that consumer.

- **A frontend consumer** proves the endpoint serves the UI's real shape — the fields it
  needs, the errors it must present, the auth context it runs under.
- **A CLI consumer** (a `src/Console/` command) proves the service is usable headlessly:
  deterministic, injectable, free of request-only assumptions. It also gives operators and
  tests a scriptable entry point, and often surfaces hidden coupling to `$_SESSION`/globals
  that a browser request would have masked.
- **Both** is the recommendation: the two consumers pull the service toward genuine
  reusability — the same logic driven from two independent contexts is logic that has
  earned its place as a service rather than a one-off.

This is a design-time gate, not a nice-to-have: if you cannot name a consumer for a
proposed endpoint, question whether the endpoint should exist yet. Build the service for
the consumer that needs it first, expose the endpoint when a second context genuinely
needs the same logic, and retire endpoints whose last consumer goes away.

This also means: **the REST front controller is the working proof that `interface/` can
reach the same end-state.** It already exists for the API;
[front-controller.md](front-controller.md) is about extending that model to the whole UI.
