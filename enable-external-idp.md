# External Identity Provider (IdP) / SSO implementation plan

## Goal

Allow OpenEMR staff users to sign in through an external OpenID Connect (OIDC)
provider (for example Keycloak, Entra ID, Okta, or Auth0) while retaining
OpenEMR's existing users, ACLs, session model, MFA policy, auditing, and local
login as an administrator break-glass path.

This is **not** the existing OpenEMR OAuth/OIDC server used for SMART-on-FHIR
clients. This work makes OpenEMR an OIDC *relying party* (client) to an
external IdP.

## What the current code shows

- The standard staff login is rendered by
  `interface/login/login.php` and posted to `interface/main/main_screen.php`.
- Login verification is currently hard-coded in `library/auth.inc.php` to
  local credentials or the Google sign-in path.
- `AuthUtils::setUserSessionVariables()` in
  `src/Common/Auth/AuthUtils.php` establishes the normal OpenEMR user session.
  The subsequent main-screen path rotates the session ID, creates CSRF state,
  and creates the `session_tracker` record.
- The login template already emits a `TemplatePageEvent`, so a module can
  contribute login-page UI. The authentication decision itself has no module
  extension point.

Therefore, a module can own the OIDC protocol, configuration, and identity
mapping, but a small core authentication extension is needed. A module should
not simulate a password POST or manually reproduce the main-screen login
sequence: either approach would be fragile and could bypass security controls.

## Recommended scope and decisions

1. Support OIDC Authorization Code flow with PKCE (S256), not implicit or
   resource-owner-password flows.
2. Start with one configured IdP per OpenEMR site. Design storage and APIs to
   permit multiple providers later.
3. Map an IdP identity to an **existing, active** OpenEMR staff user by a
   stable `(issuer, subject)` binding. Do not auto-provision users in the first
   release.
4. Keep the local username/password login enabled by default for recovery.
   Deployment policy may hide it only after a tested recovery procedure exists.
5. Let OpenEMR remain the authority for authorization: IdP groups must not
   overwrite OpenEMR ACLs in the first release.
6. Apply the current OpenEMR MFA and timeout policy after IdP authentication.
   The IdP's MFA is additive, not a reason to silently bypass local policy.

## Architecture

```
Browser -> OpenEMR login -> SSO module: start -> External IdP
        <- normal OpenEMR session <- SSO module: verified callback
```

The module owns the two OIDC endpoints and calls a narrow core login-completion
service only after token validation and local-user resolution. Core owns session
rotation, CSRF setup, session tracking, local MFA, password-expiration policy,
and the final redirect. This keeps the sensitive session lifecycle in one place.

### Small core changes required

Create a deliberately small authentication extension surface, preferably under
`src/Common/Auth/`:

- `ExternalAuthenticationProviderInterface`: provider ID, availability, login
  URL/label, and callback handling result.
- `ExternalAuthenticationResult`: the validated local OpenEMR user ID plus
  provider ID and audit-safe context; never raw tokens or claims.
- `LoginCompletionService`: a single service used by both local/Google and
  external authentication to validate the local user again, set session
  variables, rotate the session, create CSRF/session-tracker state, invoke MFA
  and password-expiration rules, and redirect.
- A login-page event or provider registry so enabled modules can add an SSO
  button without replacing the whole Twig login template.
- A callback-to-login continuation mechanism that preserves only an allowlisted
  return path, chosen facility/language, and site ID.

The initial change should avoid a general-purpose remote-code hook. Registered
providers should be loaded only from enabled OpenEMR modules and must return a
typed result. Core should log a failed or malformed provider result and fail
closed.

### Separate module/add-on

Create an optional module, for example `oe-module-external-idp`, with:

- module bootstrap and event subscriber;
- an admin configuration screen and encrypted secret storage;
- `/start` and `/callback` endpoints;
- OIDC discovery, authorization-request, token-exchange, JWKS, and claim
  validation services;
- a local identity-binding administration screen;
- database install/upgrade scripts, tests, documentation, and module-manager
  enable/disable behavior.

Suggested module tables:

- `external_idp_provider`: issuer URL, discovery metadata cache, client ID,
  encrypted client secret (where required), scopes, enabled state, and allowed
  claim configuration.
- `external_idp_identity`: provider ID, OIDC `sub`, OpenEMR `users.id`,
  creation/update timestamps, and a uniqueness constraint on
  `(provider_id, subject)`.

Store a secret through the existing protected configuration mechanism where
possible; never place it in the module's source, browser markup, logs, audit
details, or plain database configuration values.

## Detailed implementation phases

### Phase 0 — confirm requirements and threat model

Document the chosen IdP, OpenEMR version, single-site/multisite behavior,
redirect URL(s), whether IdP-initiated login is needed (recommend no for v1),
local MFA requirements, logout expectations, and who can bind identities.
Define recovery access: at least one tested local administrator account and the
ability to disable the module without IdP access.

### Phase 1 — core extension seam

1. Extract the post-authentication work from the main-screen path into the
   shared login-completion service without behavior changes for local or Google
   sign-in.
2. Add the typed provider registry and login-page button contribution event.
3. Add unit and integration coverage proving existing local, Google, MFA,
   timeout, relogin, CSRF, and facility-selection behavior remains unchanged.
4. Add audit events for external authentication start, success, failure, and
   logout, with provider ID and local username but no credentials or tokens.

Acceptance criterion: with no SSO module enabled, all existing login behavior
and tests are unchanged.

### Phase 2 — module foundation and admin configuration

1. Scaffold the module using the existing custom-module conventions in
   `interface/modules/custom_modules/`.
2. Add migrations, module enable/disable hooks, permission checks, and a
   privileged configuration screen.
3. Implement OIDC discovery from an HTTPS issuer URL, validate the issuer, and
   cache metadata/JWKS with controlled refresh.
4. Validate configuration before enabling SSO: exact redirect URI, client ID,
   discovery document, supported authorization-code/PKCE flow, and required
   signing algorithms.

Acceptance criterion: an administrator can configure a disabled provider and
see an actionable validation result without exposing its secret.

### Phase 3 — secure OIDC login flow

1. Add a login-page “Sign in with &lt;provider&gt;” button through the core event.
2. On start, generate and store one-time `state`, `nonce`, PKCE verifier,
   timestamp, selected site, language/facility choice, and allowlisted return
   target in the pre-auth core session.
3. On callback, require and consume `state` exactly once; exchange the code
   server-side using the PKCE verifier.
4. Verify ID-token signature against the issuer's JWKS and validate `iss`,
   `aud`, `azp` when applicable, `exp`, `iat`, nonce, and permitted signing
   algorithm. Treat token endpoint TLS/HTTP failures as login failures.
5. Resolve `(issuer, sub)` to a binding; recheck that its OpenEMR user is
   active and has a valid authorization/ACL group; pass the result to the
   core login-completion service.
6. Send generic user-facing failures, detailed server logs/audit events, and
   no token/claim values in either.

Acceptance criterion: a mapped active user reaches the normal OpenEMR main
screen with a rotated session, valid CSRF token, session-tracker row, expected
MFA, and correct ACLs. Unmapped, disabled, replayed, invalid, expired, or
wrong-issuer logins fail closed.

### Phase 4 — identity administration and operations

1. Add an administrator-only identity-binding workflow. Search for an existing
   OpenEMR user, record an externally verified issuer/subject, and show safe
   display metadata. Do not bind by mutable email alone.
2. Provide revocation/unbind, provider disable, configuration test, and a
   status view with last successful/failed attempt metadata.
3. Define logout: v1 must always clear the OpenEMR session. IdP single logout
   should be an optional later feature because provider behavior is not
   consistent; never depend on it to terminate the local session.
4. Document backup/recovery, secret rotation, JWKS key rotation, IdP outage
   behavior, browser cookie/SameSite requirements, and upgrade/rollback steps.

### Phase 5 — quality and release

Test at three layers:

- unit: state/nonce/PKCE lifecycle, discovery/JWKS validation, token claims,
  mapping, and failed-closed cases;
- integration: local login regression, external callback to OpenEMR session,
  MFA, inactive user, missing ACL group, timeout, logout, multisite, and
  facility/language continuation;
- manual interoperability: a development IdP (Keycloak is suitable), then the
  intended production IdP, including signing-key rotation and IdP outage.

Run the standard PHP lint, unit/integration test suites, dependency security
audit, and a focused security review before release. Pin and review the OIDC
client library rather than writing JWT/JOSE validation by hand.

## Delivery order and review gates

1. Approve the decisions in Phase 0 and the minimal core extension design.
2. Submit the core seam as a separately reviewable change with no SSO provider
   enabled by default.
3. Submit the module foundation/configuration change.
4. Submit the OIDC flow and identity-binding UI with automated tests.
5. Pilot with one non-production IdP tenant and a small mapped-user group.
6. Release the add-on with the core seam, rollback procedure, and security
   configuration guide.

## Open questions for approval

- Which IdP(s) must work in the first release?
- Should OpenEMR's own MFA always run after IdP authentication, or is there a
  documented assurance-level policy that permits an exception?
- Is staff-only SSO sufficient, or is patient-portal SSO also in scope? (The
  latter should be a separate project because it has a different login/session
  model.)
- Is one IdP per OpenEMR site acceptable for v1?
- Are users pre-created and manually bound, as recommended, or is just-in-time
  provisioning a required future feature?
