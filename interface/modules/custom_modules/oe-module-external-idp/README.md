# External Identity Provider module

This optional module makes OpenEMR a relying party for an external OpenID
Connect provider. It now includes the phase 3 authorization start and callback
flow, but the provider still must be explicitly enabled in module config before
the login button appears.

Install it through OpenEMR's Module Manager, open its configuration screen, and
enter the exact HTTPS issuer URL and OIDC client ID. Saving performs OIDC
discovery over TLS, validates the required authorization-code endpoints and
signing algorithms, and caches the metadata. The client secret is never
displayed after save and is encrypted using OpenEMR's database crypto service.

The login button only appears when the provider is enabled. The start endpoint
stores one-time state, nonce, and PKCE verifier in the pre-auth session;
callback verifies the id_token signature and claims, then hands control back to
the normal OpenEMR login completion flow.

For environments that already obtain a valid Keycloak/OIDC identity token
outside the browser redirect flow, the module also exposes
`token_login.php`. That endpoint accepts a direct JWT through either
`Authorization: Bearer <jwt>` or an `id_token`/`jwt` request parameter,
validates signature, issuer, audience, subject, and token times against the
configured provider, and then continues through the normal OpenEMR local login
flow. Because this is not the authorization-code callback flow, it does not
require or validate an OIDC `nonce`.

For stateless API calls, the REST bearer-token authorization layer also falls
back to the external IdP validator when an OpenEMR OAuth2 token is not
recognized. By default the external bearer token must use the configured
provider `client_id` as its audience. If forwarded tokens are minted for other
trusted Keycloak clients, add those values to `Accepted bearer audiences` in
the module configuration so API requests can present them directly in the
`Authorization: Bearer` header without creating an OpenEMR login session.

If those external bearer tokens do not carry OpenEMR FHIR/API scopes, enable
`Enable internal scope exchange for external bearer tokens` in the provider
configuration. When enabled, OpenEMR validates the external JWT, maps it to a
local user, and synthesizes request-specific internal scopes for the current
API call. Leave this disabled if you want OpenEMR to enforce upstream token
scopes strictly.

Phase 4 adds the operational controls around that flow:

- administrator-only binding of an external `sub` to an existing active OpenEMR
  user;
- binding revocation from the same admin screen;
- provider enable/disable support from the configuration form;
- a discovery test action that does not save settings;
- status tracking for the last login start, last success, and last failure;
- logout remains local-session first: OpenEMR always clears its own session, and
  single logout from the external IdP is intentionally deferred to a later
  release because provider behavior is inconsistent;
- safe recovery guidance: keep one tested local administrator login available
  and disable the module if the IdP is unavailable, keys rotate unexpectedly, or
  bindings need to be rebuilt.
