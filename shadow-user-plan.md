# Shadow user implementation plan for External IdP OIDC SSO

## Goal

Add optional just-in-time local user provisioning for the External Identity Provider module so that a successful Keycloak or other OIDC login can create and maintain a local OpenEMR user and binding when no binding already exists.

The design goal is:

- secure by default
- predictable for administrators
- minimal impact on existing login behavior
- reusable across different OIDC providers, not only Keycloak

## Current behavior

Today the module does this:

1. authenticate the user with the external OIDC provider
2. validate the returned tokens and claims
3. look up an existing binding from `(provider_id, subject)` to a local OpenEMR user
4. complete login only if that binding already exists

If no binding exists, login fails.

## Proposed target behavior

Add a configurable provisioning mode:

- `Manual binding only` (current behavior, default)
- `Auto-bind existing local user`
- `Auto-provision shadow user`
- `Auto-bind or provision`

Recommended meaning:

- `Manual binding only`
  - no automatic matching
  - no user creation
- `Auto-bind existing local user`
  - if no subject binding exists, attempt to match an existing OpenEMR user by configured claim
  - create the subject binding if one trusted match is found
- `Auto-provision shadow user`
  - if no subject binding exists, create a new OpenEMR local user using claims and bind it
  - do not attempt fuzzy matching to an existing user
- `Auto-bind or provision`
  - first try exact trusted match to an existing user
  - if no match is found, create a new shadow user

## Guiding rules

1. `sub` is the identity anchor
   - only the OIDC `sub` claim should be used as the stable external identity
   - usernames and emails may change and should not be the binding key

2. authorization must remain local to OpenEMR
   - authentication comes from OIDC
   - role/ACL decisions remain in OpenEMR

3. automatic user creation must be explicitly enabled
   - default remains manual binding

4. matching must be exact, never fuzzy
   - exact username match or exact email match only

5. shadow users must be identifiable
   - generated users should be marked so admins can review and manage them

## Required configuration additions

Add provider-level settings to `module_external_idp_provider` and the module UI:

- `provisioning_mode`
- `match_claim`
  - allowed values: `preferred_username`, `email`, `upn`
- `username_claim`
  - claim used when creating a new OpenEMR username
- `email_claim`
  - claim used for email population
- `first_name_claim`
  - default `given_name`
- `last_name_claim`
  - default `family_name`
- `default_facility_id` or empty
- `default_authorized`
  - recommended default: `0`
- `default_active`
  - recommended default: `1`
- `default_acl_group`
  - explicit OpenEMR group for provisioned users
- `username_prefix`
  - recommended default like `oidc_`
- `sync_claims_on_login`
  - yes/no
- `allow_email_match_if_unique`
  - yes/no

Optional later:

- `required_claim_name`
- `required_claim_value`
- `allowed_email_domains`

## Data model changes

### 1. Extend provider table

Add columns to `module_external_idp_provider` for the new provisioning settings.

### 2. Extend identity table

Keep current identity table as the authoritative external binding table.

Consider adding:

- `created_by_provisioning` tinyint
- `last_claim_snapshot` json or text

This is useful for audit and support but not strictly required for phase 1.

### 3. Mark local users created by OIDC

Do not overload the external subject into the main username if it is too long or unstable-looking.

Instead:

- generate a stable local username
- store the real external identity in the identity binding table

If needed later, add a dedicated marker column or use an existing note/custom field pattern. If schema changes to `users` are undesirable, keep the marker in the module table.

## Username strategy for shadow users

This must be deterministic and collision-safe.

Recommended algorithm:

1. start with configured `username_claim`
2. normalize to OpenEMR-safe username characters
3. prepend configured prefix, for example `oidc_`
4. if empty, fall back to `oidc_user`
5. if collision exists, append a short deterministic suffix derived from provider id plus subject hash

Examples:

- `preferred_username = jsmith` -> `oidc_jsmith`
- `email = jane.doe@example.com` -> `oidc_jane.doe`
- collision -> `oidc_jsmith_a13f9c`

Do not use raw `sub` as username unless no better option exists.

## Claim mapping strategy

Recommended default mappings:

- username: `preferred_username`
- email: `email`
- first name: `given_name`
- last name: `family_name`

Fallback behavior:

- if first/last name are missing, allow empty or derive from `name`
- if email is missing, continue if OpenEMR allows user without email
- if username claim is missing, fall back to generated hash-based username

## Provisioning flow

### Phase A: existing binding path

1. complete OIDC authentication
2. load `sub`
3. look up `(provider_id, sub)`
4. if found, log in existing bound user

### Phase B: auto-bind existing user path

If no binding exists and mode allows auto-bind:

1. read configured `match_claim`
2. extract exact claim value
3. search for a single active local OpenEMR user by exact username or exact email
4. if exactly one trusted match is found:
   - create binding `(provider_id, sub) -> user_id`
   - continue login
5. if zero or multiple matches:
   - fail with explicit message

### Phase C: auto-provision shadow user path

If no binding exists and mode allows provisioning:

1. extract mapped claims
2. build new local username
3. create local OpenEMR user with configured defaults
4. assign required auth group / ACL group
5. create binding `(provider_id, sub) -> new user_id`
6. continue login

## Local user creation details

This is the most sensitive part and should be explicit.

The new user should be:

- active = configured default
- authorized = configured default
- assigned to a configured ACL/auth group
- flagged as externally provisioned in module-owned metadata if possible

Important:

Provisioned users still need valid rows in the OpenEMR tables required by `ExternalAuthenticationService`.

That means the implementation must create the user in the same supported way OpenEMR normally creates a staff user, including any required companion rows such as:

- `users`
- `users_secure`
- group/auth linkage tables as required by OpenEMR

Implementation should reuse existing OpenEMR services for user creation if available, rather than hand-writing inserts.

## Password handling

Shadow users authenticated through OIDC should not require a locally known password for normal SSO use.

However, current `ExternalAuthenticationService::complete()` expects a `users_secure.password` row to exist for session setup.

So the implementation must do one of these:

### Preferred

Refactor external login completion so it does not depend on a reusable local password hash when the user has already been externally authenticated.

### Acceptable transitional approach

Create a strong random local password hash for provisioned users and never expose it to users.

This satisfies the existing session creation flow while keeping password ownership external.

Document clearly that:

- the local password is system-generated
- it is not communicated to the user
- local password login may be disabled later if desired

## Changes required in code

### 1. `OidcAuthenticationService`

Add provisioning branch after `resolveUserId()` returns null.

New responsibilities:

- load provisioning settings
- extract claims safely
- call a provisioning service
- record audit outcomes for:
  - auto-bound existing user
  - provisioned new user
  - provisioning failure

### 2. New service: `OidcProvisioningService`

Create a dedicated service to keep auth flow clean.

Suggested methods:

- `resolveOrProvisionUser(array $provider, object $claims): int`
- `tryAutoBindExistingUser(...)`
- `provisionShadowUser(...)`
- `buildLocalUsername(...)`
- `syncUserProfileClaims(...)`

### 3. `IdentityRepository`

Keep existing behavior and add helpers if useful:

- `findBindingByProviderAndSubject()`
- `createOrUpdateBinding()`

### 4. User creation integration

Find and use the OpenEMR-supported service/repository for staff user creation and group assignment.

Do not implement raw SQL user creation unless there is no supported service path.

### 5. `moduleConfig.php`

Add UI for:

- provisioning mode
- claim mapping
- default ACL/auth group
- username prefix
- sync-on-login toggle

Add admin help text that explains the security impact of each mode.

## Sync behavior on subsequent logins

Add optional claim synchronization on successful login.

Recommended sync scope:

- first name
- last name
- email
- possibly facility if explicitly configured

Do not automatically sync:

- ACL group
- authorization flags
- sensitive OpenEMR privileges

Those should remain local admin decisions.

## Audit and logging

Add structured audit events for:

- external login auto-bound existing user
- external login provisioned new user
- external login claim match failure
- external login duplicate match prevented auto-bind
- external login sync updated local profile

Add support logging that includes:

- provider id
- subject
- provisioning mode
- matched/created user id

Never log tokens or secrets.

## Error handling expectations

Admin-facing errors should be explicit:

- no local binding and provisioning disabled
- configured claim missing from token
- multiple local users matched the configured claim
- provisioning group configuration is missing
- local user creation failed

User-facing login failure text should stay generic where needed, but admin status pages should retain the specific reason.

## Security considerations

1. default mode must remain manual
2. require explicit default ACL/auth group for auto-provisioning
3. exact match only
4. never auto-link by display name
5. never auto-escalate privileges from external claims unless a future RBAC mapping design is approved
6. use `sub` as the persistent binding key
7. keep a clear audit trail for all automatic actions

## Testing plan

### Unit tests

- claim extraction
- username normalization
- collision handling
- exact-match auto-bind behavior
- provisioning-mode branching

### Integration tests

- manual binding still works unchanged
- auto-bind by username works
- auto-bind by email works when unique
- ambiguous email match fails
- auto-provision creates local user and binding
- repeat login reuses existing binding
- sync-on-login updates allowed fields only
- inactive local user blocks login

### Manual tests

With Keycloak:

1. login with manual binding only
2. login with auto-bind existing user
3. login with auto-provision
4. verify second login does not create duplicate user
5. verify disabled local user cannot log in even with valid Keycloak auth

## Recommended implementation phases

### Phase 1: foundation

- add schema/config fields
- add provisioning service skeleton
- keep default behavior unchanged

### Phase 2: auto-bind existing user

- support exact username/email match
- create binding automatically

### Phase 3: auto-provision shadow user

- create local user with default group
- generate local password hash if needed
- create binding

### Phase 4: profile sync and audit refinement

- optional safe claim sync
- stronger admin visibility

## Recommended default rollout

For a good operational rollout:

1. ship with `Manual binding only` as default
2. enable `Auto-bind existing local user` first in controlled environments
3. enable `Auto-provision shadow user` only after default group and local user lifecycle rules are agreed

## Open design decisions still needed

Before implementation, decide:

1. which exact OpenEMR auth/ACL group new users should receive
2. whether provisioned users are staff users, API-only users, or another controlled type
3. whether local password login for provisioned users should remain possible
4. whether email-based matching is acceptable in your environment
5. whether claim sync should be enabled by default

## Recommendation

The proper design is:

- keep `sub` as the only persistent identity key
- make provisioning explicitly configurable
- separate authentication from authorization
- prefer exact auto-bind before auto-create when safe
- create shadow users only with explicit local role defaults and full audit logging

That gives a solution that is practical for Keycloak, reusable for any OIDC provider, and controlled enough for production environments.
