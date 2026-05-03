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


@respx.mock
async def test_panel_allows_when_general_practitioner_matches(fhir):
    """Patient.generalPractitioner matches dr_alvarez → tool runs."""
    settings = get_settings()
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
    respx.get(f"{settings.openemr_fhir_base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(
            200, json=_patient_resource(ALVAREZ_PRACTITIONER_UUID)
        )
    )
    respx.get(f"{settings.openemr_fhir_base}/Condition").mock(
        return_value=Response(200, json=_empty_condition_bundle())
    )

    session = _make_session("dr_alvarez")
    result = await dispatch("get_patient_summary", {}, fhir, session)

    assert result.acl_check.allowed is True
    assert result.error is None
    assert f"Patient/{PATIENT_ID}" in result.record_ids


@respx.mock
async def test_panel_denies_when_general_practitioner_mismatches(fhir):
    """Patient is dr_chen's; dr_alvarez attempts → patient_out_of_panel."""
    settings = get_settings()
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
    respx.get(f"{settings.openemr_fhir_base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(
            200, json=_patient_resource(CHEN_PRACTITIONER_UUID)
        )
    )

    session = _make_session("dr_alvarez")
    result = await dispatch("get_patient_summary", {}, fhir, session)

    assert result.acl_check.allowed is False
    assert result.acl_check.reason == "patient_out_of_panel"
    assert result.error and "patient_out_of_panel" in result.error
    assert result.record_ids == []


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


@respx.mock
async def test_panel_falls_back_to_sub_when_fhir_user_absent(fhir):
    """Real demo path: OAuth client wasn't approved for `fhirUser` scope,
    so the id_token only carries `sub`. For OpenEMR clinicians,
    `users.uuid` IS the Practitioner FHIR id, so `sub` resolves the
    panel correctly. Patient owned by dr_alvarez → allowed.
    """
    settings = get_settings()
    respx.post(settings.openemr_oauth_base + "/token").mock(
        return_value=Response(
            200,
            json={
                "access_token": "tok-alvarez-no-fhiruser",
                "expires_in": 300,
                "id_token": _id_token_with_sub_only(ALVAREZ_PRACTITIONER_UUID),
            },
        )
    )
    respx.get(f"{settings.openemr_fhir_base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(
            200, json=_patient_resource(ALVAREZ_PRACTITIONER_UUID)
        )
    )
    respx.get(f"{settings.openemr_fhir_base}/Condition").mock(
        return_value=Response(200, json=_empty_condition_bundle())
    )

    session = _make_session("dr_alvarez")
    result = await dispatch("get_patient_summary", {}, fhir, session)

    assert result.acl_check.allowed is True
    assert result.error is None


@respx.mock
async def test_panel_denies_via_sub_fallback_on_mismatch(fhir):
    """Same fallback path — but Patient owned by dr_chen → dr_alvarez denied."""
    settings = get_settings()
    respx.post(settings.openemr_oauth_base + "/token").mock(
        return_value=Response(
            200,
            json={
                "access_token": "tok-alvarez-no-fhiruser",
                "expires_in": 300,
                "id_token": _id_token_with_sub_only(ALVAREZ_PRACTITIONER_UUID),
            },
        )
    )
    respx.get(f"{settings.openemr_fhir_base}/Patient/{PATIENT_ID}").mock(
        return_value=Response(
            200, json=_patient_resource(CHEN_PRACTITIONER_UUID)
        )
    )

    session = _make_session("dr_alvarez")
    result = await dispatch("get_patient_summary", {}, fhir, session)

    assert result.acl_check.allowed is False
    assert result.acl_check.reason == "patient_out_of_panel"


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
