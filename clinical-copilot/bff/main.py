"""BFF FastAPI app.

Endpoints:

- ``GET  /oauth/start``    — begin OAuth2 authorization-code + PKCE
- ``GET  /oauth/callback`` — exchange code → access + refresh
- ``POST /chat``           — proxy to the sidecar with a 5-min task token
- ``GET  /health``         — liveness probe
"""

from __future__ import annotations

import logging
import secrets
from typing import Any

import httpx
from fastapi import Body, FastAPI, HTTPException, Query, Request, status
from fastapi.responses import JSONResponse, RedirectResponse

from sidecar.config import get_settings

from .oauth import (
    OpenEMROAuthClient,
    OpenEMRTokenResponse,
    make_pkce_pair,
    mint_task_token,
    verify_task_token,
)
from .policy import PolicyStore

logger = logging.getLogger(__name__)


# ─── In-memory PKCE & refresh-token stores (production: encrypted Postgres) ─


_PKCE_BY_STATE: dict[str, dict[str, str]] = {}
_REFRESH_BY_USER: dict[str, OpenEMRTokenResponse] = {}
_POLICY = PolicyStore()
_POLICY.grant(user_id="dr.m@example.org", patient_id="Patient/87413")
_POLICY.grant(user_id="dr.m@example.org", patient_id="Patient/87414")
_POLICY.grant(user_id="dr.m@example.org", patient_id="Patient/87415")


def create_app() -> FastAPI:
    settings = get_settings()
    app = FastAPI(title="Clinical Co-Pilot BFF", version="0.1.0")

    @app.get("/health")
    def health() -> dict[str, str]:
        return {"status": "ok"}

    @app.get("/oauth/start")
    async def oauth_start(
        request: Request,
        user_id: str = Query(...),
        purpose: str = Query("diagnostic_cross_check"),
    ) -> RedirectResponse:
        """Begin the authorization-code-with-PKCE flow."""
        verifier, challenge = make_pkce_pair()
        state = secrets.token_urlsafe(24)
        _PKCE_BY_STATE[state] = {
            "code_verifier": verifier,
            "user_id": user_id,
            "purpose": purpose,
        }
        redirect_uri = f"{request.base_url!s}oauth/callback".replace("//oauth", "/oauth")
        async with OpenEMROAuthClient(
            oauth_base=settings.openemr_oauth_base,
            client_id=settings.openemr_client_id,
            client_secret=settings.openemr_client_secret,
            verify_ssl=settings.fhir_verify_ssl,
        ) as client:
            url = client.authorize_url(
                redirect_uri=redirect_uri,
                state=state,
                code_challenge=challenge,
                scope=(
                    "openid offline_access "
                    "patient/Condition.r patient/MedicationRequest.r "
                    "patient/AllergyIntolerance.r patient/Observation.r "
                    "patient/Encounter.r patient/Patient.r"
                ),
                purpose_of_use=purpose,
            )
        return RedirectResponse(url)

    @app.get("/oauth/callback")
    async def oauth_callback(
        request: Request, code: str = Query(...), state: str = Query(...)
    ) -> JSONResponse:
        if state not in _PKCE_BY_STATE:
            raise HTTPException(status_code=400, detail="unknown state")
        record = _PKCE_BY_STATE.pop(state)
        redirect_uri = f"{request.base_url!s}oauth/callback".replace("//oauth", "/oauth")
        async with OpenEMROAuthClient(
            oauth_base=settings.openemr_oauth_base,
            client_id=settings.openemr_client_id,
            client_secret=settings.openemr_client_secret,
            verify_ssl=settings.fhir_verify_ssl,
        ) as client:
            tokens = await client.exchange_code(
                code=code, redirect_uri=redirect_uri, code_verifier=record["code_verifier"]
            )
        _REFRESH_BY_USER[record["user_id"]] = tokens
        return JSONResponse(
            {"status": "ok", "scope": tokens.scope, "expires_in": tokens.expires_in}
        )

    @app.post("/chat")
    async def chat(
        body: dict[str, Any] = Body(...),
    ) -> JSONResponse:
        """Mint a 5-minute task token and proxy to the sidecar.

        The body shape is the same as the sidecar's ``/chat`` body.
        """
        for required in ("user_id", "patient_id", "purpose"):
            if required not in body:
                raise HTTPException(
                    status_code=400, detail=f"missing field: {required}"
                )
        user_id = str(body["user_id"])
        patient_id = str(body["patient_id"])
        purpose = str(body["purpose"])

        # Second-layer policy check (independent of OpenEMR ACL).
        denial = _POLICY.check(user_id=user_id, patient_id=patient_id, purpose=purpose)
        if denial is not None:
            logger.warning("policy_denied", extra=denial.__dict__)
            return JSONResponse(
                {"detail": denial.reason},
                status_code=status.HTTP_403_FORBIDDEN,
            )

        # Mint the 5-minute downscoped task token.
        scopes = [
            "patient/Condition.r",
            "patient/MedicationRequest.r",
            "patient/AllergyIntolerance.r",
            "patient/Observation.r",
            "patient/Encounter.r",
        ]
        task_token = mint_task_token(
            signing_key=settings.bff_jwt_signing_key,
            user_id=user_id,
            patient_id=patient_id,
            purpose_of_use=purpose,
            scopes=scopes,
            lifetime_seconds=settings.task_token_lifetime_seconds,
        )

        # Proxy to the sidecar over mTLS in production. For local development
        # we use the configured COPILOT_SIDECAR_URL.
        async with httpx.AsyncClient(verify=settings.fhir_verify_ssl) as client:
            try:
                upstream = await client.post(
                    f"{settings.sidecar_url.rstrip('/')}/chat",
                    headers={"Authorization": f"Bearer {task_token}"},
                    json=body,
                    timeout=30.0,
                )
            except httpx.RequestError as exc:
                raise HTTPException(
                    status_code=502, detail=f"sidecar unreachable: {exc}"
                ) from exc
        return JSONResponse(
            content=upstream.json(), status_code=upstream.status_code
        )

    @app.get("/policy/check")
    def policy_check(
        user_id: str = Query(...),
        patient_id: str = Query(...),
        purpose: str = Query("diagnostic_cross_check"),
    ) -> dict[str, Any]:
        denial = _POLICY.check(user_id=user_id, patient_id=patient_id, purpose=purpose)
        if denial is None:
            return {"allowed": True}
        return {"allowed": False, "reason": denial.reason}

    @app.post("/internal/verify-token")
    def internal_verify_token(token: str = Body(..., embed=True)) -> dict[str, Any]:
        try:
            payload = verify_task_token(token, signing_key=settings.bff_jwt_signing_key)
        except ValueError as exc:
            raise HTTPException(status_code=401, detail=str(exc)) from exc
        return {"ok": True, "claims": payload}

    return app


app = create_app()
