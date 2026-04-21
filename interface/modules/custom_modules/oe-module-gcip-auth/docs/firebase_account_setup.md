# Firebase/GCIP Project Setup for OpenEMR OIDC Testing

Step-by-step guide to creating a Firebase project and configuring it for
use with the GCIP Auth module.

## 1. Create a Firebase Project

1. Go to [console.firebase.google.com](https://console.firebase.google.com/)
2. Click **Add project**
3. Name it something like `openemr-oidc-test`
4. Disable Google Analytics (not needed for this)
5. Click **Create project**

**Note your Project ID** — it's shown under the project name (e.g.,
`openemr-oidc-test`). You'll need this for both `gcip_firebase_project_id`
and `gcip_client_id`.

## 2. Enable Authentication

1. In the left sidebar, click **Authentication**
2. Click **Get started**
3. If prompted to upgrade to **Identity Platform**, do it — it's free tier
   and gives you the full OIDC features

## 3. Enable a Sign-in Provider

The simplest for testing is **Google**:

1. Go to **Authentication > Sign-in method**
2. Click **Google**
3. Toggle **Enable**
4. Set a **Project support email** (your email)
5. Click **Save**

**Common mistake:** Forgetting the support email — it won't save without
one.

## 4. Add Authorized Domains

1. Go to **Authentication > Settings > Authorized domains**
2. `localhost` should already be listed. If not, add it
3. If you're testing via `https://localhost:9300`, `localhost` covers it

**Common mistake:** If you access OpenEMR via an IP (e.g., `127.0.0.1`)
instead of `localhost`, add that too — Firebase is strict about domain
matching.

## 5. Get Your Configuration Values

Go to **Project Settings** (gear icon top-left > Project settings):

| What | Where | Example |
|------|-------|---------|
| **Project ID** | General tab, top | `openemr-oidc-test` |
| **Web API Key** | General tab, "Web API Key" row | `AIzaSyB1234...` |
| **Auth Domain** | Derive from project ID | `openemr-oidc-test.firebaseapp.com` |

The auth domain follows the pattern: `{project-id}.firebaseapp.com`

## 6. Find the Issuer URL

For GCIP/Firebase, the issuer is always:

```
https://securetoken.google.com/{project-id}
```

For example: `https://securetoken.google.com/openemr-oidc-test`

You can verify it works by opening this in your browser:

```
https://securetoken.google.com/{project-id}/.well-known/openid-configuration
```

This should return a JSON discovery document. **If it 404s, your project ID
is wrong.**

## 7. Create a Test User (to get a Firebase UID)

Two options:

**Option A: Sign in via Firebase and note the UID**

You'll do this when you first test the login — after configuring the
module, the Firebase UI will appear. Sign in with Google. It will fail
(no identity mapping yet), but the user will appear in Firebase Console.
Go to **Authentication > Users** — your account will be listed with its
**User UID**.

**Option B: Create a test user manually**

1. Go to **Authentication > Users**
2. Click **Add user**
3. Enter an email/password
4. Note the **User UID** shown after creation

## 8. Summary of Values for OpenEMR

Once done, you'll have:

```
gcip_firebase_project_id  = openemr-oidc-test
gcip_firebase_api_key     = AIzaSyB1234...
gcip_firebase_auth_domain = openemr-oidc-test.firebaseapp.com
gcip_issuer               = https://securetoken.google.com/openemr-oidc-test
gcip_client_id            = openemr-oidc-test
gcip_allowed_tenant_ids   = (leave empty — no tenant filtering for single-tenant testing)
```

Enter these in the OpenEMR GCIP module admin UI (Modules > GCIP Auth).

## 9. Link a Test User to OpenEMR

After your first login attempt (step 10), you'll have a Firebase UID.
Link it to the OpenEMR admin user:

```sql
INSERT INTO oidc_external_identity (user_id, issuer, external_id, email)
VALUES (
    (SELECT id FROM users WHERE username = 'admin'),
    'https://securetoken.google.com/openemr-oidc-test',
    '<the-firebase-uid>',
    'your@gmail.com'
);
```

## 10. Testing Flow

1. Visit the OpenEMR login page — you should see the Firebase UI
2. Sign in with Google — it will fail with "login failure" (expected — no
   identity mapping yet)
3. Check **Authentication > Users** in Firebase Console — note the User UID
4. Run the SQL from step 9 to link the user
5. Try logging in again — should succeed and redirect to the dashboard

## Common Pitfalls

- **API key restrictions:** If you have API key restrictions in GCP Console,
  the key may not work for Firebase Auth. For testing, leave it unrestricted.
- **Browser popups:** Google sign-in uses a popup — ensure your browser isn't
  blocking it.
- **Mixed content:** Firebase JS SDK is loaded from CDN over HTTPS. If your
  OpenEMR runs on HTTP (`localhost:8300`), some browsers block mixed content.
  Use the HTTPS URL (`localhost:9300`) instead.
- **Wrong audience:** The `aud` claim in GCIP tokens is the project ID, not
  the API key. Set `gcip_client_id` to the **project ID**.
- **Issuer typo:** The issuer must be exactly
  `https://securetoken.google.com/{project-id}` — no trailing slash.
- **Forgot to enable OIDC in globals:** The module won't activate unless
  `oidc_enabled` is set to `true` in Administration > Config > Security.
