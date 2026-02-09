# OpenEMR OAuth2 Authentication

This document describes the OAuth2/OpenID Connect authentication system.

## Overview

OpenEMR implements **SMART on FHIR v2.2.0** using the League OAuth2 Server library.

| Component        | Implementation                              |
|------------------|---------------------------------------------|
| OAuth2 Server    | League OAuth2 Server                        |
| Token Format     | JWT (RS256 signed)                          |
| Key Storage      | RSA 2048-bit in database + files            |
| Session Storage  | Database (`oauth_trusted_user`)             |

## Token Types

### Access Token

| Property      | Value                                       |
|---------------|---------------------------------------------|
| Format        | JWT (header.payload.signature)              |
| TTL           | 1 hour (`PT1H`)                             |
| Storage       | `api_token` table                           |
| Algorithm     | RS256 (RSA SHA-256)                         |

**JWT Claims:**

| Claim    | Description                     |
|----------|---------------------------------|
| `sub`    | User ID (UUID)                  |
| `aud`    | Client ID                       |
| `iss`    | Server URL (issuer)             |
| `exp`    | Expiration timestamp            |
| `iat`    | Issued at timestamp             |
| `jti`    | Unique token identifier         |
| `scopes` | Array of granted scopes         |

### Refresh Token

| Property      | Value                                       |
|---------------|---------------------------------------------|
| Format        | Encrypted (not JWT)                         |
| TTL           | 3 months (`P3M`)                            |
| Storage       | `api_refresh_token` table                   |
| Purpose       | Issue new access tokens                     |

### Authorization Code

| Property      | Value                                       |
|---------------|---------------------------------------------|
| Format        | String (opaque)                             |
| TTL           | 5 minutes (`PT300S`)                        |
| Storage       | `oauth_trusted_user` table                  |
| Purpose       | Exchange for access token                   |

### ID Token (OpenID Connect)

| Property      | Value                                       |
|---------------|---------------------------------------------|
| Format        | JWT                                         |
| Purpose       | User identity assertion                     |
| Claims        | OIDC standard (sub, iss, aud, etc.)         |

## Grant Types

### Authorization Code Grant (Recommended)

**Flow:**

```
1. Client → /oauth2/{site}/authorize
   ├─ client_id, redirect_uri, scope, state
   ├─ code_challenge, code_challenge_method=S256 (PKCE)
   └─ response_type=code

2. User authenticates at /oauth2/{site}/login

3. User approves scopes at /oauth2/{site}/scope-authorize-confirm

4. Server → Client redirect_uri
   └─ code, state

5. Client → POST /oauth2/{site}/token
   ├─ grant_type=authorization_code
   ├─ code, redirect_uri
   ├─ client_id, client_secret (or JWT assertion)
   └─ code_verifier (PKCE)

6. Server → Client
   └─ access_token, refresh_token, expires_in, token_type=Bearer
```

**PKCE Requirement:** S256 only (no "plain" method)

### Password Grant (Legacy)

```
POST /oauth2/{site}/token
├─ grant_type=password
├─ username, password
├─ user_role (patient, users, system)
├─ client_id
└─ email (required for patient role)
```

### Client Credentials Grant (Service-to-Service)

```
POST /oauth2/{site}/token
├─ grant_type=client_credentials
├─ client_assertion_type=urn:ietf:params:oauth:client-assertion-type:jwt-bearer
├─ client_assertion=<JWT signed with client private key>
└─ scope=system/*

Automatic mapping to "oe-system" user
```

### Refresh Token Grant

```
POST /oauth2/{site}/token
├─ grant_type=refresh_token
├─ refresh_token=<token>
├─ client_id
└─ scope (optional, must be subset of original)
```

## Client Registration

### Dynamic Registration (RFC 7591)

```
POST /oauth2/{site}/registration
Content-Type: application/json

{
  "client_name": "My App",
  "redirect_uris": ["https://app.example.com/callback"],
  "scope": "openid patient/Patient.read",
  "grant_types": ["authorization_code", "refresh_token"],
  "application_type": "web"
}
```

**Response:**

```json
{
  "client_id": "generated-id",
  "client_secret": "generated-secret",
  "registration_access_token": "...",
  "registration_client_uri": "..."
}
```

### Client Types

| Type          | Has Secret | PKCE Required | Scopes Allowed           |
|---------------|------------|---------------|--------------------------|
| Confidential  | Yes        | No            | All (user/*, system/*)   |
| Public        | No         | Yes           | patient/*, openid        |

### Database Schema (`oauth_clients`)

| Column                              | Description                        |
|-------------------------------------|------------------------------------|
| `client_id`                         | Unique identifier                  |
| `client_secret`                     | Encrypted secret                   |
| `client_name`                       | Display name                       |
| `client_role`                       | 'user' or 'patient'                |
| `redirect_uri`                      | Callback URIs (pipe-separated)     |
| `scope`                             | Approved scopes                    |
| `grant_types`                       | Supported grant types              |
| `is_confidential`                   | 1=confidential, 0=public           |
| `is_enabled`                        | 0=disabled, 1=enabled              |
| `jwks_uri` / `jwks`                 | Public keys for JWT auth           |
| `skip_ehr_launch_authorization_flow`| SMART on FHIR flag                 |

## Token Validation

### Validation Flow

```
HTTP Request with Authorization: Bearer <token>
    ↓
BearerTokenAuthorizationStrategy::authorizeRequest()
    ├─ Extract token from header
    ├─ verifyAccessToken()
    │   ├─ Parse JWT
    │   ├─ Verify RS256 signature with public key
    │   ├─ Check expiration
    │   └─ Validate issuer/audience
    ├─ isAccessTokenRevokedInDatabase()
    │   └─ Check api_token.revoked = 0
    ├─ TrustedUserService::isTrustedUser()
    │   └─ Verify oauth_trusted_user record exists
    ├─ Resolve User (UuidUserAccount)
    │   └─ Convert UUID to user account
    └─ setupSessionForUserRole()
        └─ Populate session variables
```

### Session Variables Set

**For Users (role='users'):**

| Variable       | Value                    |
|----------------|--------------------------|
| `userId`       | User UUID                |
| `userRole`     | 'users'                  |
| `authUser`     | Username                 |
| `authUserID`   | User ID (numeric)        |
| `authProvider` | Auth group               |

**For Patients (role='patient'):**

| Variable       | Value                    |
|----------------|--------------------------|
| `userId`       | User UUID                |
| `userRole`     | 'patient'                |
| `pid`          | Patient ID               |

**For System (role='system'):**

| Variable       | Value                    |
|----------------|--------------------------|
| `userId`       | System user UUID         |
| `userRole`     | 'system'                 |
| `authUser`     | 'system'                 |

## Discovery Endpoints

### OpenID Configuration

```
GET /.well-known/openid-configuration

{
  "issuer": "https://server.example.com/oauth2/default",
  "authorization_endpoint": ".../authorize",
  "token_endpoint": ".../token",
  "jwks_uri": ".../jwk",
  "grant_types_supported": [...],
  "response_types_supported": [...],
  "scopes_supported": [...],
  "claims_supported": [...]
}
```

### SMART Configuration

```
GET /fhir/.well-known/smart-configuration

{
  "authorization_endpoint": "...",
  "token_endpoint": "...",
  "capabilities": ["launch-ehr", "launch-standalone", ...],
  "code_challenge_methods_supported": ["S256"]
}
```

### Public Keys (JWKS)

```
GET /oauth2/{site}/jwk

{
  "keys": [{
    "kty": "RSA",
    "alg": "RS256",
    "use": "sig",
    "n": "...",
    "e": "AQAB"
  }]
}
```

## Token Storage Tables

### api_token

| Column      | Description                    |
|-------------|--------------------------------|
| `id`        | Auto-increment PK              |
| `user_id`   | User UUID                      |
| `token`     | Token identifier (not full JWT)|
| `expiry`    | Expiration datetime            |
| `client_id` | Client that received token     |
| `scope`     | JSON-encoded scope array       |
| `revoked`   | 1=revoked, 0=active            |
| `context`   | SMART launch context (JSON)    |

### api_refresh_token

| Column      | Description                    |
|-------------|--------------------------------|
| `id`        | Auto-increment PK              |
| `user_id`   | User UUID                      |
| `token`     | Encrypted refresh token        |
| `expiry`    | Expiration datetime            |
| `client_id` | Client that received token     |
| `revoked`   | 1=revoked, 0=active            |

### oauth_trusted_user

| Column         | Description                    |
|----------------|--------------------------------|
| `id`           | PK                             |
| `user_id`      | User UUID                      |
| `client_id`    | Client ID                      |
| `scope`        | Authorized scopes              |
| `persist_login`| Remember me flag               |
| `code`         | Authorization code             |
| `session_cache`| Session data (JSON)            |
| `grant_type`   | Grant type used                |

## Security Features

### PKCE (Proof Key for Code Exchange)

- Required for public clients
- Only S256 method supported (no "plain")
- Prevents authorization code interception

### JWT Client Assertion (RFC 7523)

- Asymmetric authentication for confidential clients
- Client signs JWT with private key
- Server validates with client's public key (from jwks_uri)
- Replay prevention via `jwt_grant_history` table

### Token Revocation

```php
// Revoke access token
UPDATE api_token SET revoked = 1 WHERE token = ?

// Revoke refresh token
UPDATE api_refresh_token SET revoked = 1 WHERE token = ?

// Revoke trust relationship
DELETE FROM oauth_trusted_user WHERE user_id = ? AND client_id = ?
```

### Encryption

| Data                | Method                        |
|---------------------|-------------------------------|
| Private key         | OpenSSL AES-256-CBC           |
| Client secrets      | CryptoGen (AES-256)           |
| Refresh tokens      | Encrypted before storage      |

## Key Files Reference

| File                                                     | Purpose                     |
|----------------------------------------------------------|-----------------------------|
| `src/RestControllers/AuthorizationController.php`        | OAuth2 endpoints            |
| `src/Common/Auth/OpenIDConnect/Grant/*.php`              | Grant type implementations  |
| `src/Common/Auth/OpenIDConnect/Repositories/*.php`       | Token/client repositories   |
| `src/Common/Auth/OpenIDConnect/Entities/*.php`           | OAuth2 entities             |
| `src/Common/Auth/OpenIDConnect/JWT/JsonWebKeyParser.php` | JWT parsing                 |
| `src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php` | Token validation |
| `src/Common/Auth/OAuth2KeyConfig.php`                    | Key management              |
