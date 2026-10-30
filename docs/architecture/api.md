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

## Services and endpoints must be exercised (the consumer question)

**The non-negotiable baseline: services, APIs, and CLI tools must have unit tests.** Tests are
what prove the logic works headlessly. Beyond tests, whether an endpoint also needs a *real
consumer* depends on what kind of endpoint it is.

**Standardized / FHIR APIs do not require a consumer.** The full conformant FHIR resource set
is a goal in itself — certification and interoperability mean we want the complete surface
even where the frontend never calls it. A new FHIR endpoint is justified by the standard; it
needs unit tests, not a consuming feature.

**Internal / non-FHIR APIs should have a consumer — and it is the consumer, not the tests,
that proves the *shape*.** Unit tests prove an endpoint runs; only an actual caller proves it
is shaped to serve its intended purpose. Endpoints without consumers tend to rot: types
drift, edge cases go unverified, mis-shaping is discovered late. So:

- **A frontend consumer** proves the endpoint serves the UI's real shape — the fields it
  needs, the errors it must present, the auth context it runs under.
- **A CLI consumer** (a `src/Console/` command) proves the service is usable headlessly:
  deterministic, injectable, free of request-only assumptions, and gives operators and tests a
  scriptable entry point.

It is acceptable to land an internal API **ahead** of the feature that consumes it — but do
it deliberately, ideally as a **PR stack** (API + consumer together) so it is not added
speculatively or in the wrong shape, rather than merged alone and left to rot. If you cannot
name who will consume a proposed internal endpoint, question whether it should exist yet, and
retire internal endpoints whose last consumer goes away.

This also means: **the REST front controller is the working proof that `interface/` can
reach the same end-state.** It already exists for the API;
[front-controller.md](front-controller.md) is about extending that model to the whole UI.
