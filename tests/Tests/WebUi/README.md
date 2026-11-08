# WebUi Test Suite

In-container HTTP integration tests that drive OpenEMR's web UI form
endpoints (`/interface/*`, `/portal/*`) via a real HTTP client
(Guzzle) + DOM crawler (Symfony DomCrawler), running against a
container built from PR source in CI.

## When to use this suite

Put a test here when it needs to assert end-to-end behavior of a web
UI flow — login form, MFA challenge, admin action, portal user
action, XSS/CSRF regression, etc. — that:

- exercises PHP code under `interface/` or `portal/` (procedural page
  entry points, not Symfony-routed controllers),
- needs the OpenEMR container to actually be running (session state,
  redirects, cookie handling), and
- asserts on HTTP response codes / body content or DOM structure.

## When NOT to use this suite

- **`tests/Tests/Unit/`** — pure PHP unit tests with no HTTP or DB
- **`tests/Tests/Services/`** — DB-backed service-layer tests that
  call PHP classes directly rather than driving HTTP endpoints
- **`tests/Tests/Api/`** — HTTP integration tests against OAuth2 /
  REST / FHIR **API** endpoints (`/oauth2/*`, `/apis/*`). If your
  test hits a Symfony-routed JSON API, use api-suite
- **`tests/Tests/E2e/`** — full-browser Panther/Selenium tests for
  JS-heavy flows or visual/interaction concerns
- **`tests/Acceptance/`** — tests against black-box Docker Hub
  artifact images. Only assertions that hold in **every supported
  from_tag / to_tag** belong there; PR-new behavior must not live
  here or every unrelated future PR sees red acceptance CI until the
  behavior ships in a released tag

## HTTP-only convention

**Setup and teardown MUST NOT touch the database directly.** No
`QueryUtils`, no `sqlStatement`, no `privQuery`. Drive admin-UI or
user-UI HTTP flows the way a real browser would.

Rationale:

- Tests mimic real user behavior — no direct DB shell access.
- Setup logic isn't coupled to schema internals, so schema evolution
  doesn't ripple into unrelated tests.
- Makes it obvious what user-facing surface each test depends on;
  breakage in the enrollment/admin UI shows up in setup, not in the
  assertion.

Example: `WebLoginTotpFlowTest` enrolls admin TOTP by driving the
`mfa_totp.php` reg1 → reg2 → reg3 form flow as admin, scraping the
server-generated secret from the reg2 response, then hitting `/token`
via the enrollment session — instead of `INSERT INTO login_mfa_registrations`.

## Infrastructure

- **HTTP client:** `GuzzleHttp\Client` with `CookieJar` for session
  persistence and configurable `allow_redirects`.
- **HTML parsing:** `Symfony\Component\DomCrawler\Crawler`.
- **Base URL:** `getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost'`.
- **Server probe pattern:** check the `Server` header in `setUp`
  and either skip or hard-fail per the `OPENEMR_ALLOW_OAUTH_HTTPS_SKIP`
  contract (see `AuthorizationLogoutFullFlowTest` for the reference
  implementation).

## CI

Runs in:

- **`test.yml`** matrix (main test workflow, apache + mariadb).
- **`test-frontcontroller.yml`** matrix (built-in PHP webserver
  variant, sanity check for front-controller routing).
