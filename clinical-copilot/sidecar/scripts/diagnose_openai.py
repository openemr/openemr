"""Diagnose why the sidecar can't reach OpenAI.

Run from the clinical-copilot dir while the .venv is active::

    python -m sidecar.scripts.diagnose_openai

This is the single command to reach for when chat shows ``openai call
failed: APIConnectionError``. It runs the full pre-flight in isolation:

1. Loads ``.env`` via the same Settings class the sidecar uses, so any
   misconfig surfaces with the same value the sidecar would see.
2. Reports proxy env vars that would silently reroute outbound HTTPS.
3. Resolves DNS for the OpenAI host (or your custom OPENAI_BASE_URL).
4. Opens a TCP connection to port 443.
5. Performs a minimal real chat-completion call against ``gpt-4o-mini``
   — the cheapest model — and prints the result.

If any step fails it runs the same diagnoser the sidecar uses, so the
output mirrors what you'd see in the UI's "show reasons" section but
with a single, well-formatted block instead of 24 lines.

The script is read-only: no audit-log writes, no DB connection, no
network calls beyond the OpenAI ping. Costs at most one tiny token call
(< $0.0001).
"""

from __future__ import annotations

import asyncio
import os
import socket
import sys
import time
import traceback
from textwrap import indent
from typing import Any

from sidecar.agent.error_diagnostics import diagnose_openai_error
from sidecar.config import get_settings

# Shell-safe ANSI colour helpers — degrade to no-op when stdout isn't a TTY
# (e.g. piped to a file). Keeps the output plain when copy-pasted into a
# bug report.
_USE_COLOR = sys.stdout.isatty()


def _c(text: str, code: str) -> str:
    if not _USE_COLOR:
        return text
    return f"\x1b[{code}m{text}\x1b[0m"


def _ok(text: str) -> str:
    return _c(f"✓ {text}", "32")  # green


def _bad(text: str) -> str:
    return _c(f"✗ {text}", "31")  # red


def _warn(text: str) -> str:
    return _c(f"⚠ {text}", "33")  # yellow


def _hdr(text: str) -> str:
    return _c(text, "1;36")  # bold cyan


def _hostname_from_url(url: str | None) -> str:
    """Pull the host out of a URL without bothering with the full parser."""
    if not url:
        return "api.openai.com"
    after_scheme = url.split("://", 1)[-1]
    return after_scheme.split("/", 1)[0].split("@")[-1].split(":", 1)[0]


def _check_env() -> dict[str, Any]:
    """Inspect the loaded settings and return a structured fact-sheet.

    Prints inline so the user can read along; the return value is unused
    by callers but kept for testability.
    """
    print(_hdr("1. .env / settings"))
    settings = get_settings()
    api_key = settings.openai_api_key
    base_url = settings.openai_base_url
    if api_key:
        prefix = api_key[:7]
        print(_ok(f"OPENAI_API_KEY  set (prefix={prefix!r}, len={len(api_key)})"))
    else:
        print(_bad("OPENAI_API_KEY  MISSING — set it in clinical-copilot/.env"))
    print(
        _ok(f"OPENAI_BASE_URL {base_url!r} (custom)")
        if base_url else
        _ok("OPENAI_BASE_URL unset → using https://api.openai.com/v1 (default)")
    )
    print(_ok(f"COPILOT_OPENAI_MODEL {settings.openai_model!r}"))
    print(_ok(f"COPILOT_LLM_PROVIDER {settings.llm_provider.value!r}"))
    return {
        "api_key": api_key,
        "base_url": base_url,
        "model": settings.openai_model,
        "provider": settings.llm_provider.value,
    }


def _check_proxy_env() -> None:
    """Report any proxy env vars active in the current shell.

    Many users set HTTPS_PROXY years ago and forget; httpx honours these
    silently, which means the OpenAI SDK ends up routing through a stale
    proxy without any visible config in our .env.
    """
    print(_hdr("2. proxy env vars"))
    proxy_keys = (
        "HTTP_PROXY", "HTTPS_PROXY", "ALL_PROXY",
        "http_proxy", "https_proxy", "all_proxy",
        "NO_PROXY", "no_proxy",
    )
    found: dict[str, str] = {}
    for k in proxy_keys:
        v = os.environ.get(k)
        if v is not None and v != "":
            # Truncate so we never print embedded credentials.
            shown = v if len(v) <= 40 else f"{v[:40]}…<len={len(v)}>"
            found[k] = shown
    if not found:
        print(_ok("No proxy env vars set — outbound HTTPS will go direct."))
    else:
        for k, v in sorted(found.items()):
            print(_warn(f"{k}={v}"))
        print(_warn(
            "These will reroute outbound HTTPS through a proxy. "
            "If unintended, run `unset HTTPS_PROXY HTTP_PROXY ALL_PROXY` "
            "in this shell before relaunching."
        ))


def _check_dns(host: str) -> bool:
    """Resolve ``host`` and print every IP returned.

    DNS issues are the #1 cause of APIConnectionError when a VPN is
    active. Listing all addresses (IPv4 + IPv6) makes weirdness visible
    — e.g. only an IPv6 record on a v4-only network.
    """
    print(_hdr(f"3. DNS resolution: {host}"))
    try:
        infos = socket.getaddrinfo(host, 443, type=socket.SOCK_STREAM)
        seen: set[str] = set()
        for _fam, _type, _proto, _canon, sockaddr in infos:
            ip = sockaddr[0]
            seen.add(ip)
        for ip in sorted(seen):
            kind = "IPv6" if ":" in ip else "IPv4"
            print(_ok(f"resolved → {ip} ({kind})"))
        return True
    except socket.gaierror as exc:
        print(_bad(f"DNS lookup failed: {exc!r}"))
        print(_warn(
            "If you're on a VPN (Surfshark, WireGuard, corporate VPN), "
            "this is almost certainly the cause. Disconnect or change "
            "server and re-run this script."
        ))
        return False


def _check_tcp(host: str) -> bool:
    """Open a plain TCP connection to ``host:443`` to confirm reachability.

    Done before the TLS/HTTPS layer so an SSL error vs. a TCP-refused
    error is unambiguous in the output.
    """
    print(_hdr(f"4. TCP reachability: {host}:443"))
    start = time.perf_counter()
    try:
        sock = socket.create_connection((host, 443), timeout=8.0)
        sock.close()
        ms = (time.perf_counter() - start) * 1000.0
        print(_ok(f"TCP connect OK in {ms:.0f} ms"))
        return True
    except OSError as exc:
        print(_bad(f"TCP connect failed: {type(exc).__name__}: {exc}"))
        return False


async def _check_openai_call(api_key: str, base_url: str | None, model: str) -> None:
    """Issue a one-token chat-completion to confirm end-to-end works.

    Cost: a few prompt tokens + 1 completion token on gpt-4o-mini ≈ < $0.0001.
    On failure runs the diagnoser and prints the same block the UI would.
    """
    print(_hdr(f"5. OpenAI ping: {model}"))
    if not api_key:
        print(_bad("Skipped — no API key. Set OPENAI_API_KEY in .env first."))
        return
    try:
        from openai import AsyncOpenAI  # noqa: PLC0415 — gated import: extras may not be installed
    except ImportError:
        print(_bad(
            "openai package not installed. Run "
            "`pip install -e '.[openai]'` from the clinical-copilot dir."
        ))
        return
    kwargs: dict[str, Any] = {"api_key": api_key}
    if base_url:
        kwargs["base_url"] = base_url
    client = AsyncOpenAI(**kwargs)
    start = time.perf_counter()
    try:
        resp = await client.chat.completions.create(
            model=model,
            temperature=0.0,
            max_tokens=1,
            messages=[
                {"role": "system", "content": "You output one word: ok"},
                {"role": "user", "content": "ping"},
            ],
        )
        ms = (time.perf_counter() - start) * 1000.0
        content = resp.choices[0].message.content
        print(_ok(f"chat.completions.create OK in {ms:.0f} ms — content={content!r}"))
        usage = getattr(resp, "usage", None)
        if usage is not None:
            print(_ok(
                f"usage: prompt={usage.prompt_tokens} "
                f"completion={usage.completion_tokens}"
            ))
    except Exception as exc:
        ms = (time.perf_counter() - start) * 1000.0
        print(_bad(f"call failed in {ms:.0f} ms: {type(exc).__name__}: {exc}"))
        diag = diagnose_openai_error(
            exc, base_url=base_url, api_key=api_key, model=model,
        )
        print(_hdr("\n--- diagnosis ---"))
        print(_bad(f"category: {diag.category}"))
        print(_bad(f"summary:  {diag.summary}"))
        print(_warn(f"hint:     {diag.hint}"))
        if diag.log_context:
            print(_hdr("\n--- context ---"))
            for k, v in diag.log_context.items():
                print(f"  {k}: {v}")
        # Print the full chain repr so a bug report has everything.
        print(_hdr("\n--- traceback ---"))
        print(indent(
            "".join(traceback.format_exception(type(exc), exc, exc.__traceback__)),
            "  ",
        ))


async def _amain() -> int:
    print(_hdr("clinical-copilot OpenAI connectivity diagnose\n"))
    cfg = _check_env()
    print()
    _check_proxy_env()
    print()
    host = _hostname_from_url(cfg["base_url"])
    dns_ok = _check_dns(host)
    print()
    if dns_ok:
        _check_tcp(host)
        print()
    await _check_openai_call(cfg["api_key"], cfg["base_url"], cfg["model"])
    print()
    return 0


def main() -> int:
    return asyncio.run(_amain())


if __name__ == "__main__":  # pragma: no cover
    sys.exit(main())
