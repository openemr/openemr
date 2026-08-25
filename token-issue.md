# Token issue

## Problem

Keycloak login works.
On first login, OpenEMR creates the local shadow user correctly.

The failure happens later when a token issued by Keycloak is sent to OpenEMR REST or FHIR APIs. That token is rejected.

## Is this an OpenEMR bug?

Not based on the current code.

OpenEMR uses external OpenID Connect login for browser authentication, but its API layer expects an access token issued and signed by the OpenEMR OAuth server.

Relevant code paths:

- `src/RestControllers/Subscriber/AuthorizationListener.php`
  - The REST API always installs `BearerTokenAuthorizationStrategy`.
- `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php`
  - API bearer tokens are validated with OpenEMR's configured public REST key.
  - The request is also rejected if the token is not tied to an OpenEMR trusted user session.
- `src/Common/Auth/OpenIDConnect/Repositories/AccessTokenRepository.php`
  - New access tokens are created by OpenEMR with OpenEMR's own issuer value.
- `src/Services/TrustedUserService.php`
  - Token use depends on an `oauth_trusted_user` record with active session state.

## What this means

A Keycloak token is not automatically usable as an OpenEMR API token.

There are two separate concerns here:

1. User login into OpenEMR using Keycloak.
2. API authorization for `/apis`, `/fhir`, or related endpoints.

The current implementation supports `1`.
It does not mean `2` will accept a raw Keycloak access token.

## Valid solutions

### Option 1: Use Keycloak only for user login, and use OpenEMR tokens for API calls

This is the safest path with the current codebase.

Flow:

1. User authenticates through Keycloak into OpenEMR.
2. Client obtains an OpenEMR-issued OAuth access token from OpenEMR.
3. MCP server or API client calls OpenEMR using that OpenEMR token.

This matches how the API authorization code currently works.

### Option 2: Token exchange

Use Keycloak as the identity provider, but exchange the Keycloak token for an OpenEMR token before calling OpenEMR APIs.

Conceptually:

1. Authenticate user with Keycloak.
2. Backend verifies the Keycloak token.
3. Backend requests or mints an OpenEMR access token for the mapped OpenEMR user.
4. Use the OpenEMR token against OpenEMR APIs.

This is usually the cleanest pattern if your MCP server already trusts Keycloak.

### Option 3: Modify OpenEMR to trust Keycloak-issued bearer tokens directly

This is possible, but it is a product/code change, not a configuration-only fix.

You would need to extend the bearer token authorization path to:

1. Validate Keycloak JWT signatures against Keycloak JWKS.
2. Validate issuer and audience against Keycloak settings.
3. Map token subject or claims to an OpenEMR local user.
4. Build the OpenEMR session and role context from that mapping.
5. Replace or adapt the current trusted-user checks that assume OpenEMR-issued tokens.

Without that work, a Keycloak token will keep failing.

#### Is direct Keycloak JWT validation industry standard?

Yes, in the general OAuth2 / OpenID Connect model, it is industry standard for a resource server to validate bearer JWTs issued by a trusted identity provider.

That said, there is an important distinction:

- It is standard for a resource server to validate external JWTs when the resource server is explicitly designed to trust that issuer.
- It is not enough to say "the JWT is valid" if the application also depends on local session state, local token revocation rules, local role mapping, or local scope semantics.

For OpenEMR specifically, direct Keycloak JWT acceptance is not just a standards question. It is an architecture change because the current API authorization code is designed around OpenEMR-issued access tokens plus `oauth_trusted_user` session state.

#### Risks and implementation concerns with Option 3

If OpenEMR is changed to accept Keycloak bearer tokens directly, the implementation must handle more than signature verification.

At minimum it must enforce:

1. JWT signature validation against Keycloak JWKS.
2. Strict issuer validation.
3. Strict audience validation for the intended OpenEMR API client.
4. Expiration and not-before checks.
5. Allowed signing algorithm restrictions.
6. Reliable mapping from Keycloak subject or claims to the correct OpenEMR local user.
7. Role and permission mapping into OpenEMR's local authorization model.
8. Scope translation into the scopes OpenEMR expects for REST and FHIR routes.

Additional concerns:

- Logout and revocation behavior may become weaker if OpenEMR accepts self-contained JWTs without introspection or a compensating revocation strategy.
- Key rotation must be handled correctly through JWKS refresh and cache behavior.
- Claim mapping mistakes can create privilege escalation bugs.
- In healthcare workflows, patient and provider context must be mapped carefully or a valid token could still authorize the wrong access.
- OpenEMR currently relies on trusted-user/session checks, so those checks would need to be replaced, bypassed, or redefined for externally issued tokens.

#### Practical assessment of Option 3

Option 3 is a legitimate pattern and not inherently non-standard.

The issue is that for OpenEMR it is the highest-risk and highest-effort option because it changes the trust model of the API layer.

If the goal is a stable integration with minimal platform change, token exchange is usually the cleaner design:

1. Authenticate with Keycloak.
2. Verify the Keycloak token in the integrating backend.
3. Obtain or mint an OpenEMR token for the mapped OpenEMR user.
4. Call OpenEMR APIs using the OpenEMR token.

This preserves OpenEMR's current authorization assumptions while still allowing Keycloak to remain the upstream identity provider.

## Recommendation

If the goal is to make an MCP server work, do not send the raw Keycloak access token directly to OpenEMR.

Use one of these patterns instead:

- Preferred short-term: obtain an OpenEMR OAuth token and use that for API calls.
- Preferred integration architecture: exchange the Keycloak token for an OpenEMR token in your backend.
- Only if you want a custom platform change: add direct Keycloak JWT validation inside OpenEMR API authorization.

## Bottom line

This looks like an expected limitation of the current architecture, not just a broken token.

If you want, the next step is to implement one of these:

- a token-exchange bridge for your MCP server, or
- direct Keycloak JWT acceptance inside OpenEMR's `BearerTokenAuthorizationStrategy`.
