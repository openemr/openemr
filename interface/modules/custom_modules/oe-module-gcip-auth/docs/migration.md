# Migration Guide: Local Login to OIDC/SSO

This guide covers transitioning an existing OpenEMR installation from
local-only password authentication to OIDC-based SSO via the GCIP module.

## Migration Phases

The migration is designed to be gradual and reversible at every step:

```
Phase 1: Install & Configure    (local login still active)
Phase 2: Link User Identities   (local login still active)
Phase 3: Parallel Operation      (both login methods active)
Phase 4: Disable Local Login     (OIDC only)
```

### Phase 1: Install and Configure

1. Install and enable the GCIP module (see [README](../README.md))
2. Configure the core OIDC globals and module settings
3. Set `oidc_enabled = true`
4. Keep `oidc_local_login_disabled = false`

At this point, the login page shows the Firebase Authentication UI but
local login is still available as a fallback.

### Phase 2: Link User Identities

Every OpenEMR user who will log in via OIDC needs an entry in the
`oidc_external_identity` table linking their local account to their
external identity (issuer + subject ID).

#### Finding the external identity

Each user's external ID depends on the identity provider:

- **Google:** The `sub` claim is a numeric Google account ID
  (e.g., `104578293847561234567`)
- **SAML:** The `sub` claim is whatever the SAML IdP asserts as the
  NameID or subject
- **Email/Password (Firebase):** The `sub` claim is a Firebase UID
  (e.g., `a1B2c3D4e5F6g7H8i9J0`)

You can find a user's `sub` by:

1. Having them log in to Firebase (via a test page or the Firebase
   Console > Authentication > Users)
2. Looking up their UID in the Firebase Console > Authentication > Users
3. Using the Firebase Admin SDK to list users

The issuer for GCIP is always:
`https://securetoken.google.com/{your-project-id}`

#### Linking via SQL (until Admin API is available)

```sql
-- Link a single user
INSERT INTO `oidc_external_identity`
  (`user_id`, `issuer`, `external_id`, `email`)
VALUES
  (
    (SELECT `id` FROM `users` WHERE `username` = 'dr.smith'),
    'https://securetoken.google.com/my-emr-project',
    'a1B2c3D4e5F6g7H8i9J0',
    'dr.smith@example.com'
  );
```

```sql
-- Verify the link
SELECT u.username, e.issuer, e.external_id, e.email
FROM oidc_external_identity e
JOIN users u ON u.id = e.user_id
WHERE u.username = 'dr.smith';
```

```sql
-- Bulk link: if your users' emails match between OpenEMR and Firebase,
-- and you have exported Firebase UIDs to a CSV, you can bulk-insert:
INSERT INTO `oidc_external_identity`
  (`user_id`, `issuer`, `external_id`, `email`)
SELECT
  u.id,
  'https://securetoken.google.com/my-emr-project',
  t.firebase_uid,
  u.email
FROM `users` u
JOIN `temp_firebase_users` t ON t.email = u.email
WHERE u.active = 1;
```

#### Linking via Admin API (Phase 4, future)

Once the Admin API supports external identity fields, users can be
linked programmatically:

```
POST /admin/users/{id}/external-identity
{
    "issuer": "https://securetoken.google.com/my-emr-project",
    "external_id": "a1B2c3D4e5F6g7H8i9J0"
}
```

### Phase 3: Parallel Operation

With identities linked, both login methods work simultaneously:

- Users with linked identities see the Firebase login and can
  authenticate via SSO
- Users without linked identities (or in case of OIDC issues) can
  fall back to the standard username/password login
- The same user can use either method

**Recommended parallel period:** At least 1-2 weeks. During this time:

- Monitor the audit log for OIDC login successes and failures
- Ensure all users can log in via OIDC
- Identify and resolve any provisioning gaps (users without linked
  identities)
- Test session behavior (token expiry, re-authentication)

### Phase 4: Disable Local Login

Once all users are confirmed working with OIDC:

1. Set **Disable Local Password Login** to **Yes** in
   Administration > Config > Security
2. Communicate the change to all users

**Recovery plan:** If issues arise after disabling local login:

```sql
-- Re-enable local login via database (if locked out of admin UI)
UPDATE `globals`
SET `gl_value` = '0'
WHERE `gl_name` = 'oidc_local_login_disabled';
```

Or access the database via phpMyAdmin (available at port 8310 in the
Docker development environment).

## Rollback

The migration is fully reversible at any step:

| To rollback | Do this |
|-------------|---------|
| Disable OIDC entirely | Set `oidc_enabled = false` in globals |
| Re-enable local login | Set `oidc_local_login_disabled = false` |
| Unlink a user | `DELETE FROM oidc_external_identity WHERE user_id = ?` |
| Remove the module | Disable and uninstall via Modules > Manage Modules |

Existing `oidc_external_identity` records are preserved even if the module
is disabled — they are simply ignored when OIDC is off.

## Constraints

- **One external identity per user:** The current schema enforces a unique
  constraint on `user_id`. A user can only be linked to one external
  identity at a time. Multi-provider linking may be supported in a future
  release.
- **One local user per external identity:** The schema also enforces a
  unique constraint on `(issuer, external_id)`. An external identity can
  only map to one local user.
- **No auto-provisioning:** Users must exist in OpenEMR before they can be
  linked. The OIDC flow does not create new OpenEMR users — it only
  authenticates existing ones. Auto-provisioning may be added via the
  Admin API in the future.
