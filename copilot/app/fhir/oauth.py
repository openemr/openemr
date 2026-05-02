"""OAuth2 client — per-physician token store with SMART auth-code + PKCE.

Supports three flows:

  1. **SMART app launch (auth-code + PKCE)** — production path. The user
     opens `/v1/oauth/launch` from inside OpenEMR's launcher (or directly
     for an "EHR-launch-then-redirect" flow). We build a code-challenge,
     redirect to OpenEMR's `/authorize`, exchange the returned code on
     `/v1/oauth/callback`, and store the resulting access/refresh-token
     pair keyed by `physician_user_id` (resolved from the `id_token`'s
     `fhirUser` / `preferred_username` claim, with a fallback to the
     physician hint passed to `/v1/oauth/launch`).

  2. **Dev-launch (per-physician password grant)** — demo-safe fallback
     enabled by `SMART_DEV_LAUNCH_ENABLED=true`. Reads per-physician
     credentials from `SMART_DEV_CREDENTIALS` (JSON map) and mints a
     password-grant token for that physician. Useful when the SMART
     launch handshake from OpenEMR is flaky during the demo recording.

  3. **Legacy single-physician password grant** — kept for backwards
     compatibility with the existing `.env`. If a physician has no token
     and dev-launch is disabled, we fall back to the global
     `OAUTH_USERNAME` / `OAUTH_PASSWORD` so the existing demo doesn't
     regress while multi-physician config is rolled out.

The cache is keyed by `physician_user_id` (not a global singleton),
fixing the concurrency hazard where two physicians' calls would share
the same token. All mutations are `asyncio.Lock`-guarded.
"""
from __future__ import annotations

import asyncio
import base64
import hashlib
import json
import logging
import secrets
import time
from dataclasses import dataclass
from urllib.parse import urlencode

import httpx

from app.config import Settings


logger = logging.getLogger("copilot.fhir.oauth")


@dataclass
class TokenSet:
    access_token: str
    refresh_token: str | None
    expires_at: float  # epoch seconds
    scope: str
    physician_user_id: str
    # Practitioner UUID from the id_token's `fhirUser` claim, when present
    # (production SMART launch sets this).
    practitioner_uuid: str | None = None
    # User UUID from the id_token's `sub` claim. For OpenEMR clinician
    # users this equals the Practitioner FHIR resource id, so it serves
    # as a fallback when `fhirUser` isn't issued (e.g. password-grant
    # demos where the OAuth client wasn't approved for `fhirUser` scope).
    user_uuid: str | None = None


@dataclass
class PendingAuth:
    state: str
    code_verifier: str
    physician_user_id: str | None  # may be None until callback resolves it
    created_at: float


class FhirOAuthClient:
    """Per-physician token cache.

    NOT a singleton across physicians — `_tokens[physician_user_id]` keeps
    each physician's auth state isolated. Concurrent /v1/chat requests
    from different physicians therefore use independent tokens.
    """

    def __init__(self, settings: Settings):
        self._settings = settings
        self._tokens: dict[str, TokenSet] = {}
        self._pending: dict[str, PendingAuth] = {}
        self._lock = asyncio.Lock()

    # -------- PKCE helpers --------

    @staticmethod
    def _pkce_pair() -> tuple[str, str]:
        verifier = secrets.token_urlsafe(64)
        challenge = (
            base64.urlsafe_b64encode(hashlib.sha256(verifier.encode()).digest())
            .rstrip(b"=")
            .decode()
        )
        return verifier, challenge

    # -------- SMART auth-code flow --------

    def build_authorize_url(
        self,
        physician_user_id: str | None,
        launch: str | None,
        iss: str | None,
    ) -> tuple[str, str]:
        """Construct the /authorize URL and store PKCE verifier + state.

        Returns (url, state). The caller redirects the browser to `url`;
        OpenEMR redirects back to `oauth_redirect_uri` with `code` + `state`.
        """
        verifier, challenge = self._pkce_pair()
        state = secrets.token_urlsafe(24)
        self._pending[state] = PendingAuth(
            state=state,
            code_verifier=verifier,
            physician_user_id=physician_user_id,
            created_at=time.time(),
        )
        s = self._settings
        params = {
            "response_type": "code",
            "client_id": s.oauth_client_id,
            "redirect_uri": s.oauth_redirect_uri,
            "scope": s.oauth_scopes,
            "state": state,
            "aud": s.openemr_fhir_base,
            "code_challenge": challenge,
            "code_challenge_method": "S256",
        }
        if launch:
            params["launch"] = launch
            params["scope"] = "launch " + s.oauth_scopes
        base = (iss or s.openemr_oauth_base).rstrip("/") + s.oauth_authorize_path
        return f"{base}?{urlencode(params)}", state

    async def exchange_code(
        self, http: httpx.AsyncClient, code: str, state: str
    ) -> TokenSet:
        """Exchange the auth-code for tokens, resolve physician identity."""
        pending = self._pending.pop(state, None)
        if not pending:
            raise RuntimeError("oauth_state_unknown_or_expired")
        s = self._settings
        data = {
            "grant_type": "authorization_code",
            "code": code,
            "redirect_uri": s.oauth_redirect_uri,
            "client_id": s.oauth_client_id,
            "client_secret": s.oauth_client_secret,
            "code_verifier": pending.code_verifier,
        }
        r = await http.post(
            f"{s.openemr_oauth_base}{s.oauth_token_path}",
            data=data,
            headers={"Content-Type": "application/x-www-form-urlencoded"},
            timeout=s.fhir_timeout_seconds,
        )
        if r.status_code >= 400:
            raise RuntimeError(
                f"OAuth code exchange {r.status_code}: {r.text[:300]}"
            )
        body = r.json()
        physician = (
            pending.physician_user_id
            or _claim_from_id_token(body.get("id_token"))
        )
        if not physician:
            raise RuntimeError("cannot_resolve_physician_user_id_from_id_token")
        ts = TokenSet(
            access_token=body["access_token"],
            refresh_token=body.get("refresh_token"),
            expires_at=time.time() + int(body.get("expires_in", 300)),
            scope=body.get("scope", s.oauth_scopes),
            physician_user_id=physician,
            practitioner_uuid=_practitioner_uuid_from_id_token(body.get("id_token")),
            user_uuid=_user_uuid_from_id_token(body.get("id_token")),
        )
        async with self._lock:
            self._tokens[physician] = ts
        logger.info(
            "oauth: stored token for physician=%s practitioner=%s user_uuid=%s",
            physician,
            ts.practitioner_uuid or "<none>",
            ts.user_uuid or "<none>",
        )
        return ts

    # -------- Token retrieval (per physician) --------

    async def get_token(
        self, http: httpx.AsyncClient, physician_user_id: str
    ) -> str:
        """Return a valid access_token for a physician.

        Priority:
          1. Cached unexpired token
          2. Refresh existing token via refresh_token
          3. Dev-launch (per-physician password grant) if enabled
          4. Legacy single-physician password grant (backwards compat)
        """
        s = self._settings
        ts = self._tokens.get(physician_user_id)
        now = time.time()
        if ts and ts.expires_at > now + s.oauth_refresh_skew_seconds:
            return ts.access_token

        if ts and ts.refresh_token:
            try:
                refreshed = await self._refresh(http, ts)
                return refreshed.access_token
            except Exception as e:  # noqa: BLE001
                logger.warning(
                    "oauth refresh failed for %s, falling back: %s",
                    physician_user_id,
                    e,
                )

        if s.smart_dev_launch_enabled:
            try:
                ts = await self._dev_launch(http, physician_user_id)
                return ts.access_token
            except Exception as e:  # noqa: BLE001
                logger.warning(
                    "dev-launch failed for %s, trying legacy fallback: %s",
                    physician_user_id,
                    e,
                )

        # Legacy single-physician path — keeps the existing demo working
        # while multi-physician config is rolled out.
        return (await self._legacy_password_grant(http, physician_user_id)).access_token

    async def _refresh(
        self, http: httpx.AsyncClient, ts: TokenSet
    ) -> TokenSet:
        s = self._settings
        r = await http.post(
            f"{s.openemr_oauth_base}{s.oauth_token_path}",
            data={
                "grant_type": "refresh_token",
                "refresh_token": ts.refresh_token,
                "client_id": s.oauth_client_id,
                "client_secret": s.oauth_client_secret,
            },
            headers={"Content-Type": "application/x-www-form-urlencoded"},
            timeout=s.fhir_timeout_seconds,
        )
        r.raise_for_status()
        body = r.json()
        new = TokenSet(
            access_token=body["access_token"],
            refresh_token=body.get("refresh_token", ts.refresh_token),
            expires_at=time.time() + int(body.get("expires_in", 300)),
            scope=ts.scope,
            physician_user_id=ts.physician_user_id,
        )
        async with self._lock:
            self._tokens[ts.physician_user_id] = new
        return new

    async def _dev_launch(
        self, http: httpx.AsyncClient, physician_user_id: str
    ) -> TokenSet:
        creds_map = json.loads(self._settings.smart_dev_credentials or "{}")
        creds = creds_map.get(physician_user_id)
        if not creds:
            raise RuntimeError(
                f"dev_launch_no_creds_for:{physician_user_id}"
            )
        ts = await self._password_grant(
            http,
            username=creds["username"],
            password=creds["password"],
            physician_user_id=physician_user_id,
        )
        async with self._lock:
            self._tokens[physician_user_id] = ts
        return ts

    async def _legacy_password_grant(
        self, http: httpx.AsyncClient, physician_user_id: str
    ) -> TokenSet:
        s = self._settings
        ts = await self._password_grant(
            http,
            username=s.oauth_username,
            password=s.oauth_password,
            physician_user_id=physician_user_id,
        )
        async with self._lock:
            self._tokens[physician_user_id] = ts
        logger.warning(
            "oauth: using legacy single-physician password grant for %s "
            "— set SMART_DEV_CREDENTIALS to enable per-physician auth",
            physician_user_id,
        )
        return ts

    async def _password_grant(
        self,
        http: httpx.AsyncClient,
        *,
        username: str,
        password: str,
        physician_user_id: str,
    ) -> TokenSet:
        s = self._settings
        data = {
            "grant_type": "password",
            "client_id": s.oauth_client_id,
            "client_secret": s.oauth_client_secret,
            "scope": s.oauth_scopes,
            "user_role": s.oauth_user_role,
            "username": username,
            "password": password,
        }
        r = await http.post(
            f"{s.openemr_oauth_base}{s.oauth_token_path}",
            data=data,
            headers={"Content-Type": "application/x-www-form-urlencoded"},
            timeout=s.fhir_timeout_seconds,
        )
        if r.status_code >= 400:
            raise RuntimeError(
                f"OAuth password grant {r.status_code} for {username}: {r.text[:200]}"
            )
        body = r.json()
        return TokenSet(
            access_token=body["access_token"],
            refresh_token=body.get("refresh_token"),
            expires_at=time.time() + int(body.get("expires_in", 300)),
            scope=body.get("scope", s.oauth_scopes),
            physician_user_id=physician_user_id,
            practitioner_uuid=_practitioner_uuid_from_id_token(body.get("id_token")),
            user_uuid=_user_uuid_from_id_token(body.get("id_token")),
        )

    async def resolve_practitioner_uuid(
        self, http: httpx.AsyncClient, physician_user_id: str
    ) -> str | None:
        """Return the FHIR Practitioner UUID for a physician.

        Used by A.7 patient-scope enforcement. Side-effect: ensures a
        token is minted for the physician.

        Resolution order:
          1. The configured demo physician (typically `admin`) returns
             None — admins bypass the panel gate, matching OpenEMR's UI
             behavior where they see all charts.
          2. `TokenSet.practitioner_uuid` from the id_token's `fhirUser`
             claim (production SMART path).
          3. `TokenSet.user_uuid` from the id_token's `sub` claim. For
             OpenEMR clinician users this equals the Practitioner FHIR
             id, so it works as a fallback when the OAuth client wasn't
             approved for the `fhirUser` scope.
          4. None — caller's panel check then fails closed for them.
        """
        if physician_user_id == self._settings.demo_physician_user_id:
            return None
        await self.get_token(http, physician_user_id)
        ts = self._tokens.get(physician_user_id)
        if not ts:
            return None
        return ts.practitioner_uuid or ts.user_uuid


def _claim_from_id_token(id_token: str | None) -> str | None:
    """Best-effort extraction of physician identity from an OIDC id_token.

    Tries `preferred_username`, `sub`, then the trailing path segment of
    `fhirUser` (which OpenEMR sets to `Practitioner/<uuid>`). No
    signature verification — we trust the immediately-preceding token
    endpoint over TLS to deliver a non-tampered id_token.
    """
    if not id_token:
        return None
    try:
        payload = id_token.split(".")[1] + "=="
        data = json.loads(base64.urlsafe_b64decode(payload))
        if data.get("preferred_username"):
            return str(data["preferred_username"])
        if data.get("sub"):
            return str(data["sub"])
        fhir_user = data.get("fhirUser") or ""
        if "/" in fhir_user:
            return fhir_user.rsplit("/", 1)[-1] or None
        return None
    except Exception:  # noqa: BLE001
        return None


def _practitioner_uuid_from_id_token(id_token: str | None) -> str | None:
    """Extract the Practitioner UUID from id_token's `fhirUser` claim.

    OpenEMR populates `fhirUser` as `Practitioner/<uuid>` for clinician
    users. Returns the bare UUID, or None if the claim is absent or
    points at a non-Practitioner resource (e.g. the admin user, who
    typically has no Practitioner link). Used by A.7 to verify
    Patient.generalPractitioner references.
    """
    if not id_token:
        return None
    try:
        payload = id_token.split(".")[1] + "=="
        data = json.loads(base64.urlsafe_b64decode(payload))
        fhir_user = data.get("fhirUser") or ""
        if not fhir_user.startswith("Practitioner/"):
            return None
        return fhir_user.rsplit("/", 1)[-1] or None
    except Exception:  # noqa: BLE001
        return None


def _user_uuid_from_id_token(id_token: str | None) -> str | None:
    """Extract the user's UUID from id_token's `sub` claim.

    For OpenEMR clinician users, `users.uuid` IS the Practitioner FHIR
    resource id — so when `fhirUser` is missing (e.g. when the OAuth
    client wasn't approved for the `fhirUser` scope), `sub` is a safe
    fallback for the practitioner UUID.

    Returns None if the claim is absent or doesn't look like a UUID.
    """
    if not id_token:
        return None
    try:
        payload = id_token.split(".")[1] + "=="
        data = json.loads(base64.urlsafe_b64decode(payload))
        sub = data.get("sub")
        if not sub or not isinstance(sub, str):
            return None
        # OpenEMR uses UUID-shaped subs for users; reject opaque ids
        # (eight-four-four-four-twelve hex chars + 4 hyphens = 36).
        if len(sub) != 36 or sub.count("-") != 4:
            return None
        return sub
    except Exception:  # noqa: BLE001
        return None
