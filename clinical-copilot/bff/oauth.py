"""OAuth2 authorization-code with PKCE against OpenEMR.

Implements the per-task token-exchange flow from ARCHITECTURE.md §3.1.

PKCE generation:

- ``code_verifier``: 43–128 chars from the unreserved URL set (RFC 7636).
- ``code_challenge``: BASE64URL(SHA256(code_verifier)), no padding.

Refresh tokens are stored in the BFF's encrypted Postgres column. The
sidecar never sees one.
"""

from __future__ import annotations

import base64
import hashlib
import hmac
import json
import os
import secrets
import time
from dataclasses import dataclass
from typing import Any

import httpx


def make_pkce_pair() -> tuple[str, str]:
    """Return ``(code_verifier, code_challenge)`` per RFC 7636 §4."""
    verifier = secrets.token_urlsafe(64)[:96]  # ~96 chars, > min 43
    digest = hashlib.sha256(verifier.encode("ascii")).digest()
    challenge = base64.urlsafe_b64encode(digest).rstrip(b"=").decode("ascii")
    return verifier, challenge


@dataclass(frozen=True)
class OpenEMRTokenResponse:
    access_token: str
    refresh_token: str | None
    expires_in: int
    scope: str
    id_token: str | None = None


class OpenEMROAuthClient:
    """Thin async client over OpenEMR's OAuth2 endpoints.

    The OpenEMR routes are:

    - ``GET  {oauth_base}/authorize``  — authorization-code endpoint
    - ``POST {oauth_base}/token``      — token endpoint

    See ``openemr/Documentation/api/AUTHORIZATION.md``.
    """

    def __init__(
        self,
        oauth_base: str,
        client_id: str,
        client_secret: str | None,
        verify_ssl: bool = True,
    ) -> None:
        self._oauth_base = oauth_base.rstrip("/")
        self._client_id = client_id
        self._client_secret = client_secret
        self._http = httpx.AsyncClient(
            timeout=httpx.Timeout(connect=3.0, read=10.0, write=10.0, pool=5.0),
            verify=verify_ssl,
        )

    async def __aenter__(self) -> "OpenEMROAuthClient":
        return self

    async def __aexit__(self, *exc: object) -> None:
        await self._http.aclose()

    def authorize_url(
        self,
        *,
        redirect_uri: str,
        state: str,
        code_challenge: str,
        scope: str,
        purpose_of_use: str | None = None,
    ) -> str:
        from urllib.parse import urlencode
        params: dict[str, str] = {
            "response_type": "code",
            "client_id": self._client_id,
            "redirect_uri": redirect_uri,
            "scope": scope,
            "state": state,
            "code_challenge": code_challenge,
            "code_challenge_method": "S256",
        }
        if purpose_of_use:
            params["purpose_of_use"] = purpose_of_use
        return f"{self._oauth_base}/authorize?{urlencode(params)}"

    async def exchange_code(
        self, *, code: str, redirect_uri: str, code_verifier: str
    ) -> OpenEMRTokenResponse:
        data: dict[str, str] = {
            "grant_type": "authorization_code",
            "code": code,
            "redirect_uri": redirect_uri,
            "client_id": self._client_id,
            "code_verifier": code_verifier,
        }
        if self._client_secret:
            data["client_secret"] = self._client_secret
        resp = await self._http.post(f"{self._oauth_base}/token", data=data)
        resp.raise_for_status()
        body = resp.json()
        return OpenEMRTokenResponse(
            access_token=body["access_token"],
            refresh_token=body.get("refresh_token"),
            expires_in=int(body.get("expires_in", 3600)),
            scope=body.get("scope", ""),
            id_token=body.get("id_token"),
        )

    async def refresh(self, refresh_token: str) -> OpenEMRTokenResponse:
        data: dict[str, str] = {
            "grant_type": "refresh_token",
            "refresh_token": refresh_token,
            "client_id": self._client_id,
        }
        if self._client_secret:
            data["client_secret"] = self._client_secret
        resp = await self._http.post(f"{self._oauth_base}/token", data=data)
        resp.raise_for_status()
        body = resp.json()
        return OpenEMRTokenResponse(
            access_token=body["access_token"],
            refresh_token=body.get("refresh_token", refresh_token),
            expires_in=int(body.get("expires_in", 3600)),
            scope=body.get("scope", ""),
            id_token=body.get("id_token"),
        )


# ─── 5-minute task token (BFF-internal HMAC JWT) ───────────────────────────


def _b64url(data: bytes) -> str:
    return base64.urlsafe_b64encode(data).rstrip(b"=").decode("ascii")


def _b64url_decode(data: str) -> bytes:
    pad = "=" * (-len(data) % 4)
    return base64.urlsafe_b64decode(data + pad)


def mint_task_token(
    *,
    signing_key: str,
    user_id: str,
    patient_id: str,
    purpose_of_use: str,
    scopes: list[str],
    lifetime_seconds: int = 300,
) -> str:
    """Mint a JWT (HS256) representing one downscoped task.

    The sidecar verifies this token with the same signing key. Real
    deployments swap to RS256 with rotating keys; HS256 with a 32-byte
    key is acceptable for an internal trust boundary that is also fronted
    by mTLS.
    """
    header = {"alg": "HS256", "typ": "JWT"}
    now = int(time.time())
    payload: dict[str, Any] = {
        "iss": "clinical-copilot-bff",
        "sub": user_id,
        "patient_id": patient_id,
        "purpose_of_use": purpose_of_use,
        "scope": " ".join(scopes),
        "iat": now,
        "nbf": now,
        "exp": now + lifetime_seconds,
        "jti": secrets.token_urlsafe(8),
    }
    h = _b64url(json.dumps(header, separators=(",", ":")).encode())
    p = _b64url(json.dumps(payload, separators=(",", ":")).encode())
    signing_input = f"{h}.{p}".encode("ascii")
    sig = hmac.new(signing_key.encode("utf-8"), signing_input, hashlib.sha256).digest()
    return f"{h}.{p}.{_b64url(sig)}"


def verify_task_token(token: str, *, signing_key: str) -> dict[str, Any]:
    """Verify and decode a token. Raises ``ValueError`` on any failure."""
    try:
        header_b64, payload_b64, sig_b64 = token.split(".")
    except ValueError as exc:
        raise ValueError("malformed token") from exc
    signing_input = f"{header_b64}.{payload_b64}".encode("ascii")
    expected = hmac.new(signing_key.encode("utf-8"), signing_input, hashlib.sha256).digest()
    if not hmac.compare_digest(expected, _b64url_decode(sig_b64)):
        raise ValueError("bad signature")
    payload = json.loads(_b64url_decode(payload_b64))
    if int(payload.get("exp", 0)) <= int(time.time()):
        raise ValueError("expired")
    if int(payload.get("nbf", 0)) > int(time.time()):
        raise ValueError("not yet valid")
    return payload
