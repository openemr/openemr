"""OAuth2 client-credentials flow against OpenEMR.

For the demo, the agent runs as a registered SMART confidential client using
the `system/*.read` scopes. In production, this gets replaced by the SMART app
launch handshake — the user's session token is delegated, not a service account.
"""
from __future__ import annotations

import time
from dataclasses import dataclass

import httpx

from app.config import Settings


@dataclass
class TokenCache:
    access_token: str
    expires_at: float  # epoch seconds


class FhirOAuthClient:
    def __init__(self, settings: Settings):
        self._settings = settings
        self._cache: TokenCache | None = None

    async def get_token(self, client: httpx.AsyncClient) -> str:
        now = time.time()
        if self._cache and self._cache.expires_at > now + 30:
            return self._cache.access_token

        s = self._settings
        if s.oauth_grant_type == "password":
            data = {
                "grant_type": "password",
                "client_id": s.oauth_client_id,
                "client_secret": s.oauth_client_secret,
                "scope": s.oauth_scopes,
                "user_role": s.oauth_user_role,
                "username": s.oauth_username,
                "password": s.oauth_password,
            }
        else:
            data = {
                "grant_type": "client_credentials",
                "client_id": s.oauth_client_id,
                "client_secret": s.oauth_client_secret,
                "scope": s.oauth_scopes,
            }

        resp = await client.post(
            f"{s.openemr_oauth_base}/token",
            data=data,
            headers={"Content-Type": "application/x-www-form-urlencoded"},
            timeout=s.fhir_timeout_seconds,
        )
        if resp.status_code >= 400:
            raise RuntimeError(
                f"OAuth token error {resp.status_code}: {resp.text[:300]}"
            )
        body = resp.json()
        token = body["access_token"]
        ttl = int(body.get("expires_in", 300))
        self._cache = TokenCache(access_token=token, expires_at=now + ttl)
        return token
