"""Async parallel FHIR client.

The snapshot service issues a parallel fan-out across OpenEMR's per-resource
FHIR R4 endpoints (ARCHITECTURE.md §2.4). OpenEMR does not implement
``Patient/{id}/$everything``; the closest single-call equivalents are
``$docref`` (per-patient C-CDA) and ``$export`` (asynchronous Bulk Data),
both of which are slower than the per-resource fan-out for the per-visit hot
path.

Every request goes out with a clinician-bound short-lived bearer token
minted by the BFF. The client refuses plaintext.
"""

from __future__ import annotations

import asyncio
from collections.abc import Mapping
from dataclasses import dataclass
from typing import Any

import httpx
from tenacity import AsyncRetrying, retry_if_exception_type, stop_after_attempt, wait_exponential

from sidecar.config import Settings


class FhirRequestError(RuntimeError):
    """Raised when a FHIR request fails after retries."""

    def __init__(self, endpoint: str, status: int, body: str) -> None:
        super().__init__(f"FHIR {endpoint} failed: HTTP {status}")
        self.endpoint = endpoint
        self.status = status
        self.body = body


@dataclass(frozen=True)
class FhirResult:
    """Outcome of a single FHIR fetch.

    ``ok`` is False when the resource type is unavailable on the server but
    the rest of the snapshot is still usable. Per ARCHITECTURE.md §8 ("Multi-
    tenant variance") the build degrades gracefully rather than aborting the
    whole snapshot.
    """

    endpoint: str
    ok: bool
    bundle: Mapping[str, Any] | None
    error: str | None = None


# Resources we always pull on the per-visit hot path.
DEFAULT_RESOURCE_QUERIES: tuple[tuple[str, str], ...] = (
    ("active_problems", "Condition?patient={pid}&category=problem-list-item&clinical-status=active"),
    ("encounter_diagnoses", "Condition?patient={pid}&category=encounter-diagnosis"),
    ("medications", "MedicationRequest?patient={pid}"),
    ("allergies", "AllergyIntolerance?patient={pid}"),
    ("vitals", "Observation?patient={pid}&category=vital-signs&_count=50"),
    ("labs", "Observation?patient={pid}&category=laboratory&_count=200"),
    ("encounters", "Encounter?patient={pid}&date=ge2024-01-01"),
    ("procedures", "Procedure?patient={pid}"),
    ("documents", "DocumentReference?patient={pid}&category=clinical-note"),
)


class FhirClient:
    """Thin async wrapper over OpenEMR's FHIR R4 surface.

    Holds a per-task token. The token is short-lived (5 min) and patient-
    scoped; if it expires mid-fan-out the BFF re-mints, not this client.
    """

    def __init__(self, settings: Settings, bearer_token: str) -> None:
        self._settings = settings
        self._token = bearer_token
        self._client = httpx.AsyncClient(
            base_url=settings.openemr_fhir_base.rstrip("/"),
            timeout=httpx.Timeout(connect=3.0, read=15.0, write=10.0, pool=5.0),
            verify=settings.fhir_verify_ssl,
            headers={
                "Authorization": f"Bearer {bearer_token}",
                "Accept": "application/fhir+json",
            },
        )

    async def __aenter__(self) -> "FhirClient":
        return self

    async def __aexit__(self, *_exc: object) -> None:
        await self._client.aclose()

    async def _get(self, endpoint: str) -> Mapping[str, Any]:
        async for attempt in AsyncRetrying(
            stop=stop_after_attempt(3),
            wait=wait_exponential(multiplier=0.25, max=2.0),
            retry=retry_if_exception_type(httpx.TransportError),
            reraise=True,
        ):
            with attempt:
                response = await self._client.get(endpoint)
                if response.status_code >= 400:
                    raise FhirRequestError(endpoint, response.status_code, response.text)
                return response.json()
        raise RuntimeError("unreachable")  # pragma: no cover

    async def fan_out(
        self, patient_uuid: str, queries: tuple[tuple[str, str], ...] = DEFAULT_RESOURCE_QUERIES
    ) -> dict[str, FhirResult]:
        """Issue every query in parallel, gather results without aborting on failure."""

        async def one(name: str, template: str) -> tuple[str, FhirResult]:
            endpoint = "/" + template.format(pid=patient_uuid)
            try:
                bundle = await self._get(endpoint)
                return name, FhirResult(endpoint=endpoint, ok=True, bundle=bundle)
            except FhirRequestError as exc:
                return name, FhirResult(
                    endpoint=endpoint,
                    ok=False,
                    bundle=None,
                    error=f"HTTP {exc.status}: {exc.body[:200]}",
                )
            except Exception as exc:  # noqa: BLE001
                return name, FhirResult(endpoint=endpoint, ok=False, bundle=None, error=str(exc))

        completions = await asyncio.gather(*(one(name, tpl) for name, tpl in queries))
        return dict(completions)

    async def docref(self, patient_uuid: str) -> Mapping[str, Any]:
        """Fall back to ``POST DocumentReference/$docref`` when the fan-out is incomplete.

        Returns the C-CDA Continuity-of-Care document the operation generates.
        Used only on cold-start ingest or when more than one resource type
        failed in the fan-out.
        """
        endpoint = "/DocumentReference/$docref"
        response = await self._client.post(endpoint, json={"patient": patient_uuid})
        if response.status_code >= 400:
            raise FhirRequestError(endpoint, response.status_code, response.text)
        return response.json()
