"""Unit tests for the OpenAI / httpx error diagnoser.

These tests synthesise the exact exception chains the OpenAI SDK raises
under each failure mode and assert that ``diagnose_openai_error``
classifies them into the right category with an actionable hint. We
build the chains ourselves rather than monkey-patching the SDK so the
tests are deterministic and don't touch the network.
"""

from __future__ import annotations

import errno
import socket
import ssl

import httpx
import openai
import pytest

from sidecar.agent.error_diagnostics import diagnose_openai_error


def _chain(top: BaseException, *causes: BaseException) -> BaseException:
    """Wire up the ``__cause__`` chain top → causes[0] → causes[1] → … and return ``top``."""
    current: BaseException = top
    for c in causes:
        current.__cause__ = c
        current = c
    return top


def _make_api_connection_error(underlying: BaseException) -> openai.APIConnectionError:
    """Build an ``APIConnectionError`` whose ``__cause__`` is ``underlying``.

    The SDK accepts ``request=`` in its ctor; we stub it out with a tiny
    object since we only care about the chain walk, not the message.
    """
    request = httpx.Request("POST", "https://api.openai.com/v1/chat/completions")
    err = openai.APIConnectionError(request=request)
    err.__cause__ = underlying
    return err


# ─── Connection-error sub-cases ────────────────────────────────────────────


def test_dns_failure_classified_as_connect_dns_failure() -> None:
    gai = socket.gaierror(socket.EAI_NONAME, "nodename nor servname provided, or not known")
    httpx_err = httpx.ConnectError("DNS lookup failed")
    httpx_err.__cause__ = gai
    sdk_err = _make_api_connection_error(httpx_err)

    diag = diagnose_openai_error(sdk_err, base_url=None, api_key="sk-proj-x", model="gpt-4o-mini")

    assert diag.category == "connect_dns_failure"
    assert "DNS lookup" in diag.hint
    assert "VPN" in diag.hint  # VPN guidance is the most actionable thing here
    assert "api.openai.com" in diag.hint


def test_connection_refused_classified_as_connect_refused() -> None:
    refused = ConnectionRefusedError(errno.ECONNREFUSED, "Connection refused")
    httpx_err = httpx.ConnectError("connect failed")
    httpx_err.__cause__ = refused
    sdk_err = _make_api_connection_error(httpx_err)

    diag = diagnose_openai_error(
        sdk_err,
        base_url="http://localhost:11434/v1",
        api_key="sk-x",
        model="m",
    )

    assert diag.category == "connect_refused"
    # The hint should name the host derived from base_url.
    assert "localhost" in diag.hint


def test_unreachable_network_classified_as_connect_unreachable() -> None:
    unreach = OSError(errno.ENETUNREACH, "Network is unreachable")
    httpx_err = httpx.ConnectError("connect failed")
    httpx_err.__cause__ = unreach
    sdk_err = _make_api_connection_error(httpx_err)

    diag = diagnose_openai_error(sdk_err, base_url=None, api_key="sk-x", model="m")

    assert diag.category == "connect_unreachable"
    assert "captive-portal" in diag.hint or "VPN" in diag.hint


def test_tls_failure_classified_as_connect_tls_failure() -> None:
    tls = ssl.SSLError("CERTIFICATE_VERIFY_FAILED")
    httpx_err = httpx.ConnectError("ssl handshake failed")
    httpx_err.__cause__ = tls
    sdk_err = _make_api_connection_error(httpx_err)

    diag = diagnose_openai_error(sdk_err, base_url=None, api_key="sk-x", model="m")

    assert diag.category == "connect_tls_failure"
    assert "TLS" in diag.hint or "clock" in diag.hint


def test_proxy_error_classified_as_proxy_rejected() -> None:
    proxy_err = httpx.ProxyError("403 from proxy after CONNECT")
    sdk_err = _make_api_connection_error(proxy_err)

    diag = diagnose_openai_error(sdk_err, base_url=None, api_key="sk-x", model="m")

    assert diag.category == "connect_proxy_rejected"
    assert "proxy" in diag.hint.lower()


def test_timeout_classified_as_connect_timeout() -> None:
    timeout = httpx.ConnectTimeout("connect timeout")
    sdk_err = _make_api_connection_error(timeout)

    diag = diagnose_openai_error(sdk_err, base_url=None, api_key="sk-x", model="m")

    assert diag.category == "connect_timeout"
    assert "timed out" in diag.hint or "timeout" in diag.hint.lower()


def test_unknown_connect_failure_falls_back_to_other() -> None:
    # An OSError we don't have a specific case for.
    weird = OSError(99, "address not available")
    httpx_err = httpx.ConnectError("connect failed")
    httpx_err.__cause__ = weird
    sdk_err = _make_api_connection_error(httpx_err)

    diag = diagnose_openai_error(sdk_err, base_url=None, api_key="sk-x", model="m")

    assert diag.category == "connect_other"
    # Even the catch-all should mention VPN — it's the most common cause.
    assert "VPN" in diag.hint


# ─── HTTP-status error cases ──────────────────────────────────────────────


def _make_status_error(cls_name: str, status: int) -> openai.APIStatusError:
    """Build a real ``openai.APIStatusError`` subclass instance."""
    cls = getattr(openai, cls_name)
    request = httpx.Request("POST", "https://api.openai.com/v1/chat/completions")
    response = httpx.Response(status, request=request)
    return cls(message=f"HTTP {status}", response=response, body={"error": {"code": status}})


def test_authentication_error_classified_as_auth_invalid() -> None:
    err = _make_status_error("AuthenticationError", 401)
    diag = diagnose_openai_error(err, base_url=None, api_key="sk-proj-fake", model="gpt-4o-mini")

    assert diag.category == "auth_invalid"
    assert "401" in diag.summary
    assert "platform.openai.com" in diag.hint


def test_permission_denied_classified_as_auth_forbidden() -> None:
    err = _make_status_error("PermissionDeniedError", 403)
    diag = diagnose_openai_error(err, base_url=None, api_key="sk-x", model="gpt-4o-mini")

    assert diag.category == "auth_forbidden"
    assert "gpt-4o-mini" in diag.summary


def test_not_found_classified_as_model_not_found() -> None:
    err = _make_status_error("NotFoundError", 404)
    diag = diagnose_openai_error(err, base_url=None, api_key="sk-x", model="not-a-model")

    assert diag.category == "model_not_found"
    assert "not-a-model" in diag.summary


def test_rate_limit_classified_as_rate_limited() -> None:
    err = _make_status_error("RateLimitError", 429)
    diag = diagnose_openai_error(err, base_url=None, api_key="sk-x", model="m")

    assert diag.category == "rate_limited"
    assert "concurrency" in diag.hint.lower() or "quota" in diag.hint.lower()


def test_internal_server_error_classified_as_upstream_5xx() -> None:
    err = _make_status_error("InternalServerError", 500)
    diag = diagnose_openai_error(err, base_url=None, api_key="sk-x", model="m")

    assert diag.category == "upstream_5xx"
    assert "transient" in diag.hint or "retry" in diag.hint


def test_unknown_exception_falls_back_to_unknown() -> None:
    diag = diagnose_openai_error(
        ValueError("totally unrelated"),
        base_url=None,
        api_key="sk-x",
        model="m",
    )
    assert diag.category == "unknown"


# ─── Log-context redaction ────────────────────────────────────────────────


def test_log_context_redacts_api_key() -> None:
    """The diagnoser must never echo the full key into the log_context."""
    long_key = "sk-proj-" + "x" * 156  # 164 chars total
    err = _make_status_error("AuthenticationError", 401)
    diag = diagnose_openai_error(err, base_url=None, api_key=long_key, model="m")

    summary_str = str(diag.log_context.get("api_key_summary", ""))
    assert long_key not in summary_str
    assert "len=164" in summary_str


def test_proxy_env_snapshot_truncates_and_records_length(monkeypatch: pytest.MonkeyPatch) -> None:
    """A long HTTPS_PROXY value with embedded creds is truncated, never logged in full."""
    creds = "http://user:supersecretpassword@proxy.corp.example.com:8080"
    monkeypatch.setenv("HTTPS_PROXY", creds)
    err = _make_api_connection_error(httpx.ProxyError("403"))
    diag = diagnose_openai_error(err, base_url=None, api_key="sk-x", model="m")

    proxy_env = diag.log_context.get("proxy_env", {})
    assert "HTTPS_PROXY" in proxy_env
    # We truncate to 24 chars + length annotation when long.
    val = proxy_env["HTTPS_PROXY"]
    assert "supersecretpassword" not in val
    assert f"len={len(creds)}" in val
