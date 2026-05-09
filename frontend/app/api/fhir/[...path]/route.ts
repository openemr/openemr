import { NextRequest } from "next/server";
import { verifyCookieValue, clearCookieAttrs } from "@/lib/auth/cookies";
import * as tokenStore from "@/lib/auth/token-store";
import { buildUpstreamUrl } from "@/lib/fhir/upstream-url";

export const runtime = "nodejs";

const SESSION_COOKIE = "dashboard_session";

function readCookie(req: Request, name: string): string | null {
  const header = req.headers.get("cookie");
  if (!header) return null;
  const match = header.match(new RegExp(`(?:^|;\\s*)${name}=([^;]+)`));
  return match ? match[1] : null;
}

function unauthorized(): Response {
  // Clear the cookie alongside 401 so the browser drops the dead session.
  return new Response("unauthorized", {
    status: 401,
    headers: {
      "Cache-Control": "no-store, private",
      "Set-Cookie": `${SESSION_COOKIE}=; ${clearCookieAttrs()}`,
    },
  });
}

async function handle(
  req: NextRequest,
  ctx: { params: Promise<{ path: string[] }> },
  method: "GET" | "HEAD",
): Promise<Response> {
  const oauthBase = process.env.OPENEMR_OAUTH_BASE;
  const fhirBase = process.env.OPENEMR_FHIR_BASE;
  const clientId = process.env.OPENEMR_DASHBOARD_CLIENT_ID;
  const clientSecret = process.env.OPENEMR_DASHBOARD_CLIENT_SECRET;
  const cookieSecret = process.env.SESSION_COOKIE_SECRET;
  if (!oauthBase || !fhirBase || !clientId || !clientSecret || !cookieSecret) {
    return new Response("missing required env", { status: 500 });
  }

  const { path } = await ctx.params;
  const built = buildUpstreamUrl(fhirBase, path, req.nextUrl.search);
  if (!built.ok) {
    return new Response(`bad request: ${built.reason}`, { status: 400 });
  }
  // Extract to a local so the discriminated-union narrowing survives into
  // the inner async function's closure (TS doesn't carry the narrowing
  // across nested function boundaries).
  const upstreamUrl = built.url;

  const sessionRaw = readCookie(req, SESSION_COOKIE);
  if (!sessionRaw) {
    return unauthorized();
  }
  const session = verifyCookieValue<{ sessionId: string }>(sessionRaw, cookieSecret);
  if (!session) {
    return unauthorized();
  }
  const entry = tokenStore.get(session.sessionId);
  if (!entry) {
    return unauthorized();
  }

  const accept = req.headers.get("accept") ?? "application/fhir+json";
  // Forward the small set of FHIR-relevant request headers the proxy
  // needs to remain protocol-correct. `Prefer: respond-async` is required
  // by Bulk FHIR `$export`; conditional headers are useful for caching.
  const preferHeader = req.headers.get("prefer");
  const ifMatchHeader = req.headers.get("if-match");
  const ifNoneMatchHeader = req.headers.get("if-none-match");
  const tokenEndpoint = `${oauthBase.replace(/\/+$/, "")}/oauth2/default/token`;

  async function fetchUpstream(accessToken: string): Promise<Response> {
    const headers: Record<string, string> = {
      Authorization: `Bearer ${accessToken}`,
      Accept: accept,
    };
    if (preferHeader) headers.Prefer = preferHeader;
    if (ifMatchHeader) headers["If-Match"] = ifMatchHeader;
    if (ifNoneMatchHeader) headers["If-None-Match"] = ifNoneMatchHeader;
    return fetch(upstreamUrl, {
      method,
      cache: "no-store",
      headers,
    });
  }

  let upstream = await fetchUpstream(entry.access);
  if (upstream.status === 401) {
    let refreshed;
    try {
      refreshed = await tokenStore.refresh(session.sessionId, {
        tokenEndpoint,
        clientId,
        clientSecret,
      });
    } catch {
      return unauthorized();
    }
    upstream = await fetchUpstream(refreshed.access);
    if (upstream.status === 401) {
      return unauthorized();
    }
  }

  // Stream the upstream body back. Forward most upstream response
  // headers — Bulk FHIR `$export` returns Content-Location for the
  // job-poll URL, ETag for resource versioning, etc. — but suppress
  // cookies/security headers and force no-store so PHI never lands in
  // any intermediary cache.
  const responseHeaders = new Headers();
  const HEADER_DENYLIST = new Set([
    "set-cookie",
    "set-cookie2",
    "transfer-encoding",
    "connection",
    "keep-alive",
    "trailer",
    "cache-control",
    "pragma",
    // Node's undici fetch transparently decompresses gzip/br upstream
    // bodies but leaves these headers in place. Forwarding them tells
    // the browser to decode an already-decoded body (or trust a stale
    // length), which corrupts the response.
    "content-encoding",
    "content-length",
  ]);
  // For headers carrying upstream URLs (Content-Location, Location), rewrite
  // any URL that points back at OPENEMR_FHIR_BASE so the browser keeps
  // following them via this proxy instead of trying to hit OpenEMR
  // directly (which would fail auth — the browser only has our session
  // cookie, not a bearer token).
  const dashboardPublic = process.env.DASHBOARD_PUBLIC_URL;
  const fhirBaseUrl = new URL(fhirBase);
  const fhirBasePath = fhirBaseUrl.pathname.replace(/\/+$/, "");
  const URL_HEADERS = new Set(["content-location", "location"]);
  function rewriteUpstreamUrl(value: string): string {
    if (!dashboardPublic) return value;
    try {
      const u = new URL(value);
      if (u.origin === fhirBaseUrl.origin && u.pathname.startsWith(`${fhirBasePath}/`)) {
        const sub = u.pathname.slice(fhirBasePath.length);
        return `${dashboardPublic.replace(/\/+$/, "")}/api/fhir${sub}${u.search}`;
      }
    } catch {
      /* header value isn't a parseable URL — leave it alone */
    }
    return value;
  }
  upstream.headers.forEach((value, key) => {
    const lk = key.toLowerCase();
    if (HEADER_DENYLIST.has(lk)) return;
    if (URL_HEADERS.has(lk)) {
      responseHeaders.set(key, rewriteUpstreamUrl(value));
    } else {
      responseHeaders.set(key, value);
    }
  });
  responseHeaders.set("Cache-Control", "no-store, private");
  return new Response(method === "HEAD" ? null : upstream.body, {
    status: upstream.status,
    headers: responseHeaders,
  });
}

export async function GET(req: NextRequest, ctx: { params: Promise<{ path: string[] }> }) {
  return handle(req, ctx, "GET");
}
export async function HEAD(req: NextRequest, ctx: { params: Promise<{ path: string[] }> }) {
  return handle(req, ctx, "HEAD");
}
