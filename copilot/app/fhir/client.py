"""Thin async FHIR client wrapping httpx.

Every tool goes through this client. It handles:
  - OAuth2 token (client-credentials) caching
  - Bearer header injection
  - Timeouts (5s default — failure modes are surfaced explicitly per AUDIT/ARCHITECTURE)
  - JSON parsing into dict (FHIR R4 resource shape)

Errors propagate as FhirError so tools can render them as graceful degradation
strings rather than 500s reaching the LLM.
"""
from __future__ import annotations

from typing import Any

import httpx

from app.config import Settings
from app.fhir.oauth import FhirOAuthClient


class FhirError(Exception):
    def __init__(self, message: str, status: int | None = None):
        super().__init__(message)
        self.status = status


class FhirClient:
    def __init__(self, settings: Settings):
        self._settings = settings
        self._oauth = FhirOAuthClient(settings)
        self._http = httpx.AsyncClient(
            timeout=settings.fhir_timeout_seconds,
            headers={"Accept": "application/fhir+json"},
            verify=settings.openemr_verify_tls,
        )

    async def aclose(self) -> None:
        await self._http.aclose()

    async def _headers(self, physician_user_id: str) -> dict[str, str]:
        token = await self._oauth.get_token(self._http, physician_user_id)
        return {"Authorization": f"Bearer {token}"}

    async def get_resource(
        self,
        resource_type: str,
        resource_id: str,
        *,
        physician_user_id: str,
    ) -> dict[str, Any]:
        url = f"{self._settings.openemr_fhir_base}/{resource_type}/{resource_id}"
        try:
            r = await self._http.get(url, headers=await self._headers(physician_user_id))
        except httpx.TimeoutException as e:
            raise FhirError(f"FHIR timeout on {resource_type}/{resource_id}") from e
        if r.status_code in (401, 403):
            raise FhirError(f"FHIR access denied on {resource_type}", status=r.status_code)
        if r.status_code == 404:
            raise FhirError(f"{resource_type}/{resource_id} not found", status=404)
        r.raise_for_status()
        return r.json()

    async def search(
        self,
        resource_type: str,
        params: dict[str, Any],
        *,
        physician_user_id: str,
    ) -> dict[str, Any]:
        url = f"{self._settings.openemr_fhir_base}/{resource_type}"
        try:
            r = await self._http.get(
                url,
                headers=await self._headers(physician_user_id),
                params=params,
            )
        except httpx.TimeoutException as e:
            raise FhirError(f"FHIR timeout searching {resource_type}") from e
        if r.status_code in (401, 403):
            raise FhirError(f"FHIR access denied on {resource_type}", status=r.status_code)
        r.raise_for_status()
        return r.json()


async def get_fhir_client(settings: Settings) -> FhirClient:
    return FhirClient(settings)
