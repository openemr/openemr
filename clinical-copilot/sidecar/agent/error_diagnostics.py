"""Diagnose OpenAI / httpx / network exceptions into actionable messages.

The OpenAI Python SDK raises a small set of opaque exceptions whose default
``str(exc)`` is famously useless (``APIConnectionError: Connection error.``).
The real cause sits one or two levels deeper on the ``__cause__`` chain
(``httpx.ConnectError`` → ``socket.gaierror``, ``ssl.SSLError``,
``ConnectionRefusedError``, etc.) — and that's what an operator needs to see.

This module walks the chain, classifies the failure, and produces:

* a single-line, human-readable summary suitable for surfacing in the chat UI
  next to a dropped pair, and
* a structured ``dict`` of fields to log so the launcher's ``.launch.log``
  carries everything we'd ever want to grep.

It is deliberately defensive about importing the OpenAI SDK and ``httpx`` —
both are optional at the module level so the eval suite (mock provider) and
CI without ``openai`` extras still import this file. We branch on
``isinstance`` against ``Exception`` subclasses we look up by name, falling
back gracefully if a class is absent.
"""

from __future__ import annotations

import errno
import os
import socket
import ssl
from dataclasses import dataclass, field
from typing import Any

# Optional imports. ``openai`` ships our exception hierarchy; ``httpx`` ships
# the transport-level exceptions the SDK wraps. Both are listed in the
# optional ``[openai]`` extra, so we tolerate their absence.
try:
    import openai as _openai
    _HAS_OPENAI = True
except ImportError:  # pragma: no cover — only when extras unset
    _openai = None  # type: ignore[assignment]
    _HAS_OPENAI = False

try:
    import httpx as _httpx
    _HAS_HTTPX = True
except ImportError:  # pragma: no cover — httpx is a hard dep, but be defensive
    _httpx = None  # type: ignore[assignment]
    _HAS_HTTPX = False


# Env vars that silently reroute outbound HTTP — common cause of mystery
# connection errors when a user has dotfiles set for a corporate proxy.
_PROXY_ENV_VARS = (
    "HTTP_PROXY",
    "HTTPS_PROXY",
    "ALL_PROXY",
    "NO_PROXY",
    "http_proxy",
    "https_proxy",
    "all_proxy",
    "no_proxy",
)


@dataclass(frozen=True)
class ErrorDiagnosis:
    """Result of classifying one exception.

    Attributes
    ----------
    summary
        A single-line message of the form
        ``"<sdk-class> → <root-cause>: <hint>"``. Safe to surface in the UI.
    category
        Stable identifier — useful for fingerprinting many errors that
        share the same root cause (e.g. all 24 dropped pairs share
        ``"connect_dns_failure"`` so the aggregator can collapse them into
        one banner). Always lowercase ``snake_case``.
    hint
        Actionable next step in plain English, suitable to display alone
        if you only have room for one line.
    log_context
        Structured fields to emit alongside the traceback at log time.
    """

    summary: str
    category: str
    hint: str
    log_context: dict[str, Any] = field(default_factory=dict)


# ─── Internal helpers ─────────────────────────────────────────────────────


def _walk_cause_chain(exc: BaseException, *, max_depth: int = 8) -> list[BaseException]:
    """Return the cause chain starting with ``exc``.

    Walks both ``__cause__`` (explicit ``raise X from Y``) and
    ``__context__`` (implicit chain when one exception triggers another).
    Stops at ``max_depth`` to defend against pathological self-referential
    chains we'd otherwise loop on.
    """
    seen: set[int] = set()
    chain: list[BaseException] = []
    current: BaseException | None = exc
    while current is not None and id(current) not in seen and len(chain) < max_depth:
        seen.add(id(current))
        chain.append(current)
        current = current.__cause__ or current.__context__
    return chain


def _qualname(exc: BaseException) -> str:
    """Return ``module.ClassName`` for an exception — better than just the class name.

    We don't trim the leading ``builtins.`` since ``socket.gaierror`` and
    ``builtins.OSError`` both look the same at the class level otherwise.
    """
    module = exc.__class__.__module__
    name = exc.__class__.__qualname__
    if module in ("builtins", "__main__"):
        return name
    return f"{module}.{name}"


def _is_openai_class(exc: BaseException, name: str) -> bool:
    """Check ``isinstance(exc, openai.<name>)`` defensively.

    The SDK class hierarchy occasionally renames things between minor
    versions; this helper degrades gracefully when the class is missing.
    """
    if not _HAS_OPENAI:
        return False
    cls = getattr(_openai, name, None)
    return isinstance(cls, type) and isinstance(exc, cls)


def _is_httpx_class(exc: BaseException, name: str) -> bool:
    if not _HAS_HTTPX:
        return False
    cls = getattr(_httpx, name, None)
    return isinstance(cls, type) and isinstance(exc, cls)


def _proxy_env_snapshot() -> dict[str, str]:
    """Return any proxy-related env vars currently set (values truncated).

    We never log the full value because corporate proxy URLs can contain
    embedded credentials. First 24 chars + length is enough to diagnose.
    """
    seen: dict[str, str] = {}
    for key in _PROXY_ENV_VARS:
        val = os.environ.get(key)
        if val is None or val == "":
            continue
        seen[key] = f"{val[:24]}…<len={len(val)}>" if len(val) > 24 else val
    return seen


def _hostname_from_url(url: str | None) -> str:
    """Extract the host out of a URL without pulling in urllib.parse for one call."""
    if not url:
        return "api.openai.com"
    # crude but adequate: scheme://host[:port]/path
    after_scheme = url.split("://", 1)[-1]
    return after_scheme.split("/", 1)[0].split("@")[-1].split(":", 1)[0]


def _api_key_summary(api_key: str | None) -> str:
    """Describe an API key without leaking it.

    ``sk-proj-`` keys are project-scoped (≥ 100 chars); ``sk-`` keys are
    classic. Empty strings are normalised to ``None`` upstream but we
    handle the literal ``""`` defensively.
    """
    if not api_key:
        return "MISSING"
    return f"{api_key[:7]}…<len={len(api_key)}>"


# ─── Connection-error sub-classifier ──────────────────────────────────────


def _classify_connection_error(  # noqa: PLR0911 — dispatcher with one return per category
    sdk_exc: BaseException,
    chain: list[BaseException],
    *,
    base_url: str | None,
) -> tuple[str, str, str]:
    """Drill into the chain to figure out why a connect attempt failed.

    Returns ``(category, root_repr, hint)``. Anything we can't recognise
    falls through to a generic "(VPN?) check connectivity" message — that
    catches the long tail of weird transports without lying about specifics.
    """
    host = _hostname_from_url(base_url)
    # The "deepest" exception is almost always the one we want to talk
    # about, but we'll mention any httpx-layer wrapper too in the repr.
    deepest = chain[-1] if chain else sdk_exc
    deepest_repr = _qualname(deepest)
    deepest_msg = str(deepest).strip()

    # 1. DNS failures — socket.gaierror is the canonical signal. errno -2
    #    and -3 (EAI_NONAME / EAI_AGAIN) are the most common.
    if isinstance(deepest, socket.gaierror):
        return (
            "connect_dns_failure",
            f"{deepest_repr}({deepest.errno}): {deepest_msg}",
            (
                f"DNS lookup of {host!r} failed. If a VPN is active "
                "(Surfshark, WireGuard, corporate VPN, etc.), disconnect "
                "or switch server and retry — VPNs that override system "
                "DNS are the most common cause."
            ),
        )

    # 2. TCP connect refused / unreachable.
    if isinstance(deepest, ConnectionRefusedError):
        return (
            "connect_refused",
            f"{deepest_repr}: {deepest_msg}",
            (
                f"Nothing is listening at {host!r}. If OPENAI_BASE_URL is "
                "set, double-check the host/port; otherwise the most likely "
                "cause is a stale local proxy on a port you no longer run."
            ),
        )
    if isinstance(deepest, OSError) and deepest.errno in (
        errno.ENETUNREACH,
        errno.EHOSTUNREACH,
        errno.ENETDOWN,
    ):
        return (
            "connect_unreachable",
            f"{deepest_repr}({deepest.errno}): {deepest_msg}",
            (
                "The OS reports the destination network is unreachable. "
                "This is usually a downed network interface, an offline VPN "
                "tunnel that's still capturing routes, or being on a "
                "captive-portal Wi-Fi that hasn't been signed into yet."
            ),
        )

    # 3. TLS / SSL handshake.
    if isinstance(deepest, ssl.SSLError) or (
        _is_httpx_class(deepest, "ConnectError") and "SSL" in deepest_msg.upper()
    ):
        # Common subcases we can speak to directly.
        msg_low = deepest_msg.lower()
        if "certificate" in msg_low and ("expired" in msg_low or "verify" in msg_low):
            hint = (
                "TLS certificate verification failed. Check the system clock "
                "(a clock skew of a few minutes will reject any cert) and "
                "any MITM proxy/VPN that re-signs HTTPS traffic."
            )
        else:
            hint = (
                "TLS handshake failed. Most often a corporate MITM proxy or "
                "an out-of-date system trust store; less often a system "
                "clock issue."
            )
        return ("connect_tls_failure", f"{deepest_repr}: {deepest_msg}", hint)

    # 4. Proxy-layer errors — httpx surfaces these as ProxyError.
    if _is_httpx_class(deepest, "ProxyError"):
        proxy_env = _proxy_env_snapshot()
        env_hint = (
            f" Detected proxy env vars: {sorted(proxy_env)}." if proxy_env else
            " No proxy env vars set, but httpx still routed via a proxy — "
            "check ~/.netrc or system network settings."
        )
        return (
            "connect_proxy_rejected",
            f"{deepest_repr}: {deepest_msg}",
            f"Outbound HTTPS proxy rejected the request.{env_hint}",
        )

    # 5. Timeouts (the SDK has APITimeoutError but a raw httpx timeout can
    #    also bubble through).
    if (
        _is_openai_class(sdk_exc, "APITimeoutError")
        or _is_httpx_class(deepest, "ConnectTimeout")
        or _is_httpx_class(deepest, "ReadTimeout")
        or _is_httpx_class(deepest, "WriteTimeout")
        or _is_httpx_class(deepest, "PoolTimeout")
    ):
        return (
            "connect_timeout",
            f"{deepest_repr}: {deepest_msg}",
            (
                "The request timed out before a response came back. Often a "
                "VPN that drops idle connections, a slow corporate proxy, or "
                "an OpenAI server hiccup. Retry once before assuming a "
                "config problem."
            ),
        )

    # 6. Remote protocol / mid-stream disconnects.
    if _is_httpx_class(deepest, "RemoteProtocolError") or _is_httpx_class(deepest, "ReadError"):
        return (
            "connect_protocol_error",
            f"{deepest_repr}: {deepest_msg}",
            (
                "The server closed the connection mid-response. Usually "
                "transient — retry once. If persistent, a load balancer or "
                "VPN inline-inspecting traffic is the prime suspect."
            ),
        )

    # 7. Catch-all for the rest of the httpx.ConnectError tree (no clear
    #    sub-cause). We at least surface the deepest repr so an operator
    #    can search for it.
    return (
        "connect_other",
        f"{deepest_repr}: {deepest_msg}" if deepest_msg else deepest_repr,
        (
            f"Could not establish a connection to {host!r}. The OpenAI SDK "
            "doesn't know why. Most common causes (in order): VPN routing, "
            "OS-level firewall, captive-portal Wi-Fi, or corporate MITM "
            "proxy that doesn't allow api.openai.com."
        ),
    )


# ─── Top-level dispatch ────────────────────────────────────────────────────


def diagnose_openai_error(  # noqa: PLR0911 — dispatcher with one return per HTTP-status / connect category
    exc: BaseException,
    *,
    base_url: str | None = None,
    api_key: str | None = None,
    model: str | None = None,
) -> ErrorDiagnosis:
    """Classify an exception raised from an OpenAI SDK call.

    Pass the raw exception you caught — the function does the chain walk
    itself. ``base_url``, ``api_key``, and ``model`` are optional context
    that lets the diagnosis name the actual host/model that failed and
    redact the key safely.
    """
    chain = _walk_cause_chain(exc)
    sdk_class = _qualname(exc)
    chain_repr = " → ".join(_qualname(e) for e in chain)
    base_log_context: dict[str, Any] = {
        "sdk_class": sdk_class,
        "cause_chain": chain_repr,
        "host": _hostname_from_url(base_url),
        "base_url_set": bool(base_url),
        "model": model,
        "api_key_summary": _api_key_summary(api_key),
        "proxy_env": _proxy_env_snapshot(),
    }

    # ─── HTTP-status errors (we got a response, just a bad one) ─────────
    # APIStatusError carries .status_code and .response; subclasses
    # (AuthenticationError, RateLimitError, …) tell us which HTTP code.
    if _is_openai_class(exc, "AuthenticationError"):
        body = _safe_response_body(exc)
        return ErrorDiagnosis(
            summary=(
                f"{sdk_class} (HTTP 401): API key rejected. "
                f"key={_api_key_summary(api_key)}"
            ),
            category="auth_invalid",
            hint=(
                "OpenAI rejected the API key. Confirm OPENAI_API_KEY in .env "
                "matches the key shown in platform.openai.com/api-keys, "
                "and that the key hasn't been revoked. For project-scoped "
                "keys (sk-proj-…) the project must also have access to the "
                f"requested model ({model or 'see config'})."
            ),
            log_context={**base_log_context, "response_body": body},
        )

    if _is_openai_class(exc, "PermissionDeniedError"):
        body = _safe_response_body(exc)
        return ErrorDiagnosis(
            summary=f"{sdk_class} (HTTP 403): project lacks permission for {model!r}.",
            category="auth_forbidden",
            hint=(
                "The key authenticated but the project is not allowed to "
                f"call model {model!r}. Either pick a model the project has "
                "access to, or add it under Project → Limits in the OpenAI "
                "dashboard."
            ),
            log_context={**base_log_context, "response_body": body},
        )

    if _is_openai_class(exc, "NotFoundError"):
        body = _safe_response_body(exc)
        return ErrorDiagnosis(
            summary=f"{sdk_class} (HTTP 404): model {model!r} not found.",
            category="model_not_found",
            hint=(
                f"OpenAI does not recognise model {model!r}. Check the "
                "spelling in COPILOT_OPENAI_MODEL (current default: "
                "gpt-4o-mini) and that your project has access."
            ),
            log_context={**base_log_context, "response_body": body},
        )

    if _is_openai_class(exc, "RateLimitError"):
        body = _safe_response_body(exc)
        return ErrorDiagnosis(
            summary=f"{sdk_class} (HTTP 429): rate-limited or quota exhausted.",
            category="rate_limited",
            hint=(
                "Either the per-minute rate limit was hit (lower "
                "COPILOT_PAIR_JUDGE_MAX_CONCURRENCY in .env) or the project "
                "has run out of paid credit (check platform.openai.com/usage)."
            ),
            log_context={**base_log_context, "response_body": body},
        )

    if _is_openai_class(exc, "BadRequestError"):
        body = _safe_response_body(exc)
        return ErrorDiagnosis(
            summary=f"{sdk_class} (HTTP 400): request rejected by OpenAI.",
            category="bad_request",
            hint=(
                "OpenAI says the request itself is malformed. Most often a "
                "schema/temperature combination the model doesn't accept, "
                "or a content-too-long error. The full response body is in "
                "the launcher log."
            ),
            log_context={**base_log_context, "response_body": body},
        )

    if _is_openai_class(exc, "UnprocessableEntityError"):
        body = _safe_response_body(exc)
        return ErrorDiagnosis(
            summary=f"{sdk_class} (HTTP 422): request semantically invalid.",
            category="unprocessable",
            hint=(
                "OpenAI accepted the request shape but rejected the "
                "contents — usually a structured-output schema mismatch."
            ),
            log_context={**base_log_context, "response_body": body},
        )

    if _is_openai_class(exc, "ConflictError"):
        body = _safe_response_body(exc)
        return ErrorDiagnosis(
            summary=f"{sdk_class} (HTTP 409): conflicting state.",
            category="conflict",
            hint=("OpenAI rejected the request as conflicting with current "
                  "resource state. Usually transient; retry once."),
            log_context={**base_log_context, "response_body": body},
        )

    if _is_openai_class(exc, "InternalServerError"):
        body = _safe_response_body(exc)
        status = getattr(exc, "status_code", "5xx")
        return ErrorDiagnosis(
            summary=f"{sdk_class} (HTTP {status}): OpenAI server error.",
            category="upstream_5xx",
            hint=(
                "Server-side error on OpenAI's end. Almost always transient "
                "— retry once after a few seconds. If it persists, check "
                "status.openai.com."
            ),
            log_context={**base_log_context, "response_body": body},
        )

    # ─── Connection-layer errors (we never got a response) ──────────────
    if _is_openai_class(exc, "APIConnectionError") or _is_httpx_class(exc, "TransportError"):
        category, root_repr, hint = _classify_connection_error(
            exc, chain, base_url=base_url,
        )
        return ErrorDiagnosis(
            summary=f"{sdk_class} → {root_repr}",
            category=category,
            hint=hint,
            log_context=base_log_context,
        )

    # ─── Generic fallback — a non-OpenAI-classified exception escaped ──
    return ErrorDiagnosis(
        summary=f"{sdk_class}: {str(exc).strip() or '(no message)'}",
        category="unknown",
        hint=(
            "The OpenAI call raised an exception we don't have a classifier "
            "for. Full chain + traceback are in the launcher log; please "
            "open an issue with the chain and the model name."
        ),
        log_context=base_log_context,
    )


def _safe_response_body(exc: BaseException) -> str | None:
    """Pull a short snippet out of an APIStatusError's response body if present.

    OpenAI sometimes returns a detailed JSON error body (``{"error": {...}}``)
    that's far more informative than the SDK's str(exc). We slice it short
    so we never log a megabyte of payload.
    """
    body: object | None = getattr(exc, "body", None)
    if body is not None:
        text = str(body)
        return text[:512] + (" …" if len(text) > 512 else "")
    response = getattr(exc, "response", None)
    if response is not None:
        text_attr = getattr(response, "text", None)
        if isinstance(text_attr, str):
            return text_attr[:512] + (" …" if len(text_attr) > 512 else "")
    return None


__all__ = ["ErrorDiagnosis", "diagnose_openai_error"]
