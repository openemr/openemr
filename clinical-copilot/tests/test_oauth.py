"""Unit tests for BFF OAuth2 / PKCE / task token utilities."""

from __future__ import annotations

import base64
import hashlib
import time

import pytest

from bff.oauth import (
    make_pkce_pair,
    mint_task_token,
    verify_task_token,
)


def test_pkce_pair_round_trip() -> None:
    verifier, challenge = make_pkce_pair()
    expected = base64.urlsafe_b64encode(
        hashlib.sha256(verifier.encode("ascii")).digest()
    ).rstrip(b"=").decode("ascii")
    assert challenge == expected
    # Verifier length must satisfy RFC 7636: 43..128
    assert 43 <= len(verifier) <= 128


def test_task_token_round_trip() -> None:
    key = "this-is-a-32-byte-test-signing-key!!"
    token = mint_task_token(
        signing_key=key,
        user_id="dr.m@example.org",
        patient_id="Patient/87413",
        purpose_of_use="diagnostic_cross_check",
        scopes=["patient/Condition.r"],
        lifetime_seconds=300,
    )
    claims = verify_task_token(token, signing_key=key)
    assert claims["sub"] == "dr.m@example.org"
    assert claims["patient_id"] == "Patient/87413"
    assert claims["purpose_of_use"] == "diagnostic_cross_check"
    assert "patient/Condition.r" in claims["scope"]


def test_task_token_rejects_bad_signature() -> None:
    token = mint_task_token(
        signing_key="key-A",
        user_id="u", patient_id="Patient/1",
        purpose_of_use="diagnostic_cross_check", scopes=[],
    )
    with pytest.raises(ValueError, match="bad signature"):
        verify_task_token(token, signing_key="key-B")


def test_task_token_rejects_expired() -> None:
    token = mint_task_token(
        signing_key="k",
        user_id="u", patient_id="Patient/1",
        purpose_of_use="diagnostic_cross_check", scopes=[], lifetime_seconds=0,
    )
    time.sleep(0.1)
    with pytest.raises(ValueError, match="expired"):
        verify_task_token(token, signing_key="k")
