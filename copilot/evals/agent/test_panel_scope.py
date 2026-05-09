"""A.7 — Per-physician patient scope enforcement.

Confirms the tool-layer panel gate denies a request when the requested
patient's `generalPractitioner` does not match the authenticated
physician's Practitioner UUID, and allows it when they match.

The gate is implemented in app/tools/_base.py:run_tool. It depends on
oauth.TokenSet.practitioner_uuid being populated from the id_token's
`fhirUser` claim by oauth._password_grant.
"""
from __future__ import annotations

import base64
import json

import pytest
import respx
from httpx import Response

from app.config import get_settings
from app.fhir.client import FhirClient
from app.phi.session import sessions
from app.tools.registry import dispatch


PATIENT_ID = "f47ac10b-58cc-4372-a567-0e02b2c3d479"
ALVAREZ_PRACTITIONER_UUID = "11111111-aaaa-4bbb-8ccc-111111111111"
CHEN_PRACTITIONER_UUID = "22222222-bbbb-4ccc-8ddd-222222222222"


def _id_token(practitioner_uuid: str) -> str:
    """Forge a minimal OIDC id_token with fhirUser=Practitioner/<uuid>.

    Signature is not verified by the agent — `_practitioner_uuid_from_id_token`
    only base64-decodes the payload segment.
    """
    header = base64.urlsafe_b64encode(b'{"alg":"none"}').rstrip(b"=").decode()
    payload_obj = {
        "preferred_username": "doc",
        "fhirUser": f"Practitioner/{practitioner_uuid}",
    }
    payload = (
        base64.urlsafe_b64encode(json.dumps(payload_obj).encode())
        .rstrip(b"=")
        .decode()
    )
    return f"{header}.{payload}.sig"


def _patient_resource(general_practitioner_uuid: str | None) -> dict:
    pt = {
        "resourceType": "Patient",
        "id": PATIENT_ID,
        "active": True,
        "gender": "female",
        "birthDate": "1979-04-12",
        "name": [{"given": ["Mariela"], "family": "Synthea"}],
    }
    if general_practitioner_uuid:
        pt["generalPractitioner"] = [
            {"reference": f"Practitioner/{general_practitioner_uuid}"}
        ]
    return pt


def _empty_condition_bundle() -> dict:
    return {"resourceType": "Bundle", "type": "searchset", "entry": []}


@pytest.fixture
def fhir():
    # Fresh FhirClient per test so the per-physician token cache starts empty.
    return FhirClient(get_settings())


def _make_session(physician_user_id: str = "dr_alvarez"):
    session_id = f"panel-test-{physician_user_id}"
    sessions.end(session_id)
    return sessions.create(session_id, physician_user_id, PATIENT_ID)


# Note: tool-layer A.7 panel check was removed once it became clear OpenEMR's
# FHIR Patient resource doesn't expose `generalPractitioner` in the response.
# The /v1/sessions gate (in app/main.py) now owns A.7 enforcement using the
# PHYSICIAN_PATIENT_PANEL env. Tests for that gate live in
# `test_env_panel_parses_and_filters` below; the tool-layer probe just
# confirms OpenEMR will let the physician read the patient at all (401/403),
# which is covered by `test_acl_denies_unknown_role` in test_tool_integration.py.


@respx.mock
async def test_panel_bypassed_when_practitioner_uuid_absent(fhir):
    """Admin (no fhirUser claim) bypasses panel — sees all patients."""
    settings = get_settings()
    # id_token without fhirUser → resolve_practitioner_uuid returns None
    header = base64.urlsafe_b64encode(b'{"alg":"none"}').rstrip(b"=").decode()
    payload = (
        base64.urlsafe_b64encode(b'{"preferred_username":"admin"}')
        .rstrip(b"=")
        .decode()
    )
    admin_id_token = f"{header}.{payload}.sig"

    respx.post(settings.openemr_oauth_base + "/token").mock(
        return_value=Response(
            200,
            json={
                "access_token": "tok-admin",
                "expires_in": 300,
                "id_token": admin_id_token,
            },
        )
    )
    # Patient owned by some other physician; admin should still see it
    respx.get(f"{settings.openemr_fhir_base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(
            200, json=_patient_resource(CHEN_PRACTITIONER_UUID)
        )
    )
    respx.get(f"{settings.openemr_fhir_base}/Condition").mock(
        return_value=Response(200, json=_empty_condition_bundle())
    )

    session = _make_session("admin")
    result = await dispatch("get_patient_summary", {}, fhir, session)

    assert result.acl_check.allowed is True
    assert result.error is None


def _id_token_with_sub_only(user_uuid: str) -> str:
    """Forge an id_token with `sub` (UUID) but no `fhirUser` claim."""
    header = base64.urlsafe_b64encode(b'{"alg":"none"}').rstrip(b"=").decode()
    payload_obj = {"preferred_username": "doc", "sub": user_uuid}
    payload = (
        base64.urlsafe_b64encode(json.dumps(payload_obj).encode())
        .rstrip(b"=")
        .decode()
    )
    return f"{header}.{payload}.sig"


# Sub-fallback tests were removed alongside the tool-layer A.7 check.
# resolve_practitioner_uuid's sub-fallback is exercised at the
# /v1/sessions gate (covered by test_env_panel_parses_and_filters and
# the live Railway smoke matrix in IMPLEMENTATION.md §6.5).


# --- env-driven panel (PHYSICIAN_PATIENT_PANEL) ---


def test_env_panel_parses_and_filters(monkeypatch):
    """The PHYSICIAN_PATIENT_PANEL env var → list lookup helper.

    Workaround for OpenEMR FHIR not exposing Patient.generalPractitioner.
    """
    from app.config import Settings
    from app.main import _env_panel_for

    s = Settings(
        physician_patient_panel=json.dumps(
            {"dr_alvarez": ["uuid-a", "uuid-b"], "dr_chen": ["uuid-c"]}
        )
    )
    assert _env_panel_for(s, "dr_alvarez") == ["uuid-a", "uuid-b"]
    assert _env_panel_for(s, "dr_chen") == ["uuid-c"]
    assert _env_panel_for(s, "dr_kumar") is None
    # Missing / invalid env → None (caller falls back to FHIR path)
    s2 = Settings(physician_patient_panel="{}")
    assert _env_panel_for(s2, "dr_alvarez") is None
    s3 = Settings(physician_patient_panel="not-json")
    assert _env_panel_for(s3, "dr_alvarez") is None
    s4 = Settings(physician_patient_panel=json.dumps({"dr_alvarez": "not-a-list"}))
    assert _env_panel_for(s4, "dr_alvarez") is None


@respx.mock
async def test_fhir_panel_allows_when_general_practitioner_empty(fhir, monkeypatch):
    """A clinician (has Practitioner UUID) accessing a patient whose
    ``Patient.generalPractitioner`` is empty must be ALLOWED — the
    OpenEMR R4 transformer never populates this field on Railway, so
    relying on it would 403 every clinician on every chart. The
    OpenEMR-side demographics gate carries scope enforcement.

    Regression test for the 2026-05-08 'doctors in scope can't see
    pending intakes' bug.
    """
    from app.config import Settings
    from app.main import _verify_patient_in_panel

    settings = get_settings()
    # Empty PHYSICIAN_PATIENT_PANEL → fall through to FHIR-derived path.
    monkeypatch.setattr(settings, "physician_patient_panel", "{}")

    # OAuth: id_token carries fhirUser=Practitioner/<uuid> for "dr_brown"
    respx.post(settings.openemr_oauth_base + "/token").mock(
        return_value=Response(
            200,
            json={
                "access_token": "tok-brown",
                "expires_in": 300,
                "id_token": _id_token(ALVAREZ_PRACTITIONER_UUID),
            },
        )
    )
    # Patient resource has NO generalPractitioner — Railway/Synthea reality.
    respx.get(f"{settings.openemr_fhir_base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(200, json=_patient_resource(None))
    )

    # Should NOT raise. Pre-fix this would 403 with 'patient_out_of_panel'.
    await _verify_patient_in_panel(fhir, "dr_brown", PATIENT_ID, settings)


@respx.mock
async def test_env_panel_miss_falls_through_to_fhir_check(fhir, monkeypatch):
    """2026-05-08 — env-panel is advisory: a physician listed in
    PHYSICIAN_PATIENT_PANEL who tries to access a patient NOT in their
    listed UUIDs no longer hard-403s. They fall through to the
    FHIR-derived check, which (on Railway / Synthea data) sees an empty
    ``generalPractitioner`` and allows.

    Regression test for the 2026-05-08 'physicians can't see the
    pending banner' bug — the env-panel had Mariela only in admin's
    list, so every other clinician was 403'd before reaching the FHIR
    fallback.
    """
    from app.main import _verify_patient_in_panel

    settings = get_settings()
    # dr_alvarez is listed but only with PATIENT_ID_OTHER, not PATIENT_ID
    monkeypatch.setattr(
        settings,
        "physician_patient_panel",
        json.dumps({"dr_alvarez": ["some-other-uuid-not-our-patient"]}),
    )

    respx.post(settings.openemr_oauth_base + "/token").mock(
        return_value=Response(
            200,
            json={
                "access_token": "tok-alvarez",
                "expires_in": 300,
                "id_token": _id_token(ALVAREZ_PRACTITIONER_UUID),
            },
        )
    )
    # Patient resource returned with NO generalPractitioner — the
    # Synthea/Railway reality the FHIR-fallback relax was built for.
    respx.get(f"{settings.openemr_fhir_base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(200, json=_patient_resource(None))
    )

    # Pre-fix: this would 403 on the env miss.
    # Post-fix: env miss logs a warning, falls through to FHIR, FHIR sees
    # empty owners, allows.
    await _verify_patient_in_panel(fhir, "dr_alvarez", PATIENT_ID, settings)


@respx.mock
async def test_fhir_panel_still_denies_when_general_practitioner_lists_others(
    fhir, monkeypatch
):
    """When generalPractitioner IS populated and excludes the requesting
    clinician, deny — preserves the original tight semantics for any
    OpenEMR install where the field is wired up.
    """
    from app.config import Settings
    from app.main import _verify_patient_in_panel
    from fastapi import HTTPException

    settings = get_settings()
    monkeypatch.setattr(settings, "physician_patient_panel", "{}")

    respx.post(settings.openemr_oauth_base + "/token").mock(
        return_value=Response(
            200,
            json={
                "access_token": "tok-brown",
                "expires_in": 300,
                "id_token": _id_token(ALVAREZ_PRACTITIONER_UUID),
            },
        )
    )
    # Patient owned by Chen — Alvarez should be denied.
    respx.get(f"{settings.openemr_fhir_base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(
            200, json=_patient_resource(CHEN_PRACTITIONER_UUID)
        )
    )

    with pytest.raises(HTTPException) as ei:
        await _verify_patient_in_panel(fhir, "dr_alvarez", PATIENT_ID, settings)
    assert ei.value.status_code == 403
    assert "out_of_panel" in ei.value.detail
