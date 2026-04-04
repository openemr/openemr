# Troubleshooting GCIP OIDC Authentication

## Checking the Audit Log

All OIDC authentication events are recorded in the OpenEMR audit log.
Navigate to **Reports > Audit Log** and filter by event type `login`.

Each failed OIDC attempt produces a specific audit message that identifies
the failure point. The table below maps messages to causes and fixes.

| Audit Log Message | Cause | Fix |
|-------------------|-------|-----|
| `GCIP module not configured (missing issuer or client ID)` | Module settings are incomplete | Go to Modules > GCIP Auth and fill in Issuer and Client ID |
| `GCIP OIDC discovery failed` | Cannot reach the provider's `.well-known/openid-configuration` | Check issuer URL is correct; verify network connectivity from server to `securetoken.google.com`; check firewall/proxy settings |
| `GCIP OIDC token validation failed` | JWT signature, issuer, audience, or expiry check failed | See "Token Validation Failures" below |
| `GCIP OIDC account not provisioned for iss=... sub=...` | No `oidc_external_identity` row for this external user | Link the user — see [Migration Guide](migration.md) |
| `GCIP OIDC mapped user not found in users table` | Identity mapping exists but points to a deleted user | Check `oidc_external_identity.user_id` matches an existing `users.id` |
| `GCIP OIDC user account is disabled` | The local user's `active` flag is 0 | Re-activate the user in Administration > Users |
| `GCIP OIDC user has no ACL group` | User exists but has no gACL group assignment | Assign the user to an ACL group in Administration > ACL |
| `OIDC token expired, so force logout` | Session re-validation detected an expired token and no silent refresh occurred | Normal if user was idle; see "Session Expiry" below |
| `OIDC failure: {ip}. IP address has been manually blocked` | IP rate limiting triggered — manual block | Check Administration > Reports > IP Tracker |
| `OIDC failure: {ip}. IP address exceeded maximum number of failed logins` | IP rate limiting triggered — too many failures | Wait for auto-reset or manually reset in IP Tracker |

## Token Validation Failures

When the audit log shows `GCIP OIDC token validation failed`, the token
itself was rejected. Common causes:

### Wrong issuer

**Symptom:** Login fails immediately after Firebase authentication.

**Check:** Verify the issuer URL in module config matches the token's `iss`
claim. For GCIP, the issuer is always:
```
https://securetoken.google.com/{project-id}
```

**Debug:** Decode the token (without trusting it) to inspect claims:
```bash
# Decode the payload (second part of the JWT, base64url-encoded)
echo "<token-payload-part>" | base64 -d 2>/dev/null | jq .
```
Compare the `iss` field with your configured issuer.

### Wrong audience

**Symptom:** Same as wrong issuer — immediate failure.

**Check:** The `aud` claim in GCIP tokens is the Firebase project ID.
Verify the Client ID in module config matches your Firebase project ID.

### Expired token

**Symptom:** Login fails if the user takes too long between Firebase
authentication and the POST back to OpenEMR.

**Check:** GCIP ID tokens are valid for 1 hour. The clock skew setting
(`oidc_clock_skew_seconds`, default 30) adds tolerance. If your server's
clock is significantly off, token validation may fail.

**Fix:** Sync server time via NTP. Increase clock skew tolerance if needed
(but keep it under 60 seconds).

### JWKS fetch failure

**Symptom:** Discovery succeeds but token validation fails because keys
cannot be fetched.

**Check:** The server needs outbound HTTPS access to:
- `https://securetoken.google.com/{project-id}/.well-known/openid-configuration`
- The `jwks_uri` from the discovery document (typically
  `https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com`)

**Fix:** Allow outbound HTTPS in your firewall. If behind a proxy, configure
PHP's stream context or Guzzle's proxy settings.

## Session Expiry

GCIP/Firebase ID tokens have a fixed 1-hour lifetime. After login, the
token's expiry is stored in the session. The server checks this on each
request. If the token has expired and no silent refresh has occurred, the
session is destroyed and the user is redirected to login.

**Expected behavior:** Users who are actively using OpenEMR should not
notice this — the client-side silent refresh (when implemented) will keep
the session alive. Users who are idle for more than 1 hour will be
redirected to login.

**If sessions expire too quickly:** Check that the
`oidc_session_revalidation_minutes` global is not set too low. The default
(5 minutes) means the server rechecks token validity every 5 minutes.

## Login Page Not Showing Firebase UI

**Symptom:** The standard OpenEMR login form appears instead of the
Firebase Authentication UI.

**Checklist:**

1. Is the module enabled? Check Modules > Manage Modules
2. Is `oidc_enabled` set to `true`? Check Administration > Config > Security
3. Is the Firebase API key configured? Check Modules > GCIP Auth
4. Check the browser console for JavaScript errors (the Firebase JS SDK
   may fail to load if the auth domain is not configured correctly or the
   domain is not in Firebase's authorized domains list)

## User Locked Out After Disabling Local Login

If `oidc_local_login_disabled` is set to `true` and OIDC login is broken,
you can recover via the database:

```sql
UPDATE `globals`
SET `gl_value` = '0'
WHERE `gl_name` = 'oidc_local_login_disabled';
```

Access MySQL via:
- phpMyAdmin at `http://localhost:8310/` (Docker dev environment)
- Direct MySQL connection: `mysql -u openemr -p openemr`
- Docker: `docker compose exec mysql mysql -u openemr -popenemr openemr`

## IP Rate Limiting

The OIDC login endpoint uses the same IP-based rate limiting as the
standard login. If an IP address exceeds the configured maximum failed
login attempts (`ip_max_failed_logins` global), it will be blocked.

**To check:** Administration > Reports > IP Tracker

**To unblock:** Use the IP Tracker report to reset the counter or remove
the manual block for the affected IP.

**To adjust thresholds:** Administration > Config > Security:
- `ip_max_failed_logins` — maximum failures per IP (0 = disabled)
- `ip_time_reset_password_max_failed_logins` — auto-reset timeout in
  seconds (0 = no auto-reset)

## Diagnostic SQL Queries

```sql
-- List all external identity mappings
SELECT u.username, u.fname, u.lname, u.active,
       e.issuer, e.external_id, e.email, e.created_at
FROM oidc_external_identity e
JOIN users u ON u.id = e.user_id
ORDER BY u.username;

-- Find users WITHOUT external identity (not yet linked)
SELECT u.id, u.username, u.fname, u.lname, u.email
FROM users u
LEFT JOIN oidc_external_identity e ON e.user_id = u.id
WHERE e.id IS NULL AND u.active = 1
ORDER BY u.username;

-- Check revoked tokens
SELECT * FROM oidc_token_revocation
ORDER BY revoked_at DESC;

-- Clean up expired revocation entries
DELETE FROM oidc_token_revocation
WHERE token_expiry < NOW();

-- Check OIDC-related globals
SELECT gl_name, gl_value
FROM globals
WHERE gl_name LIKE 'oidc_%'
ORDER BY gl_name;

-- Check module config
SELECT * FROM module_gcip_config;
```
