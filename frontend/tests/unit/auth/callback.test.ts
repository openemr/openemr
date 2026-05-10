import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { GET } from "@/app/api/auth/callback/route";
import { signCookieValue, verifyCookieValue } from "@/lib/auth/cookies";
import * as tokenStore from "@/lib/auth/token-store";

const ENV = {
  OPENEMR_OAUTH_BASE: "https://openemr.example.com",
  OPENEMR_FHIR_BASE: "https://openemr.example.com/apis/default/fhir",
  OPENEMR_DASHBOARD_CLIENT_ID: "test-client-id",
  OPENEMR_DASHBOARD_CLIENT_SECRET: "test-client-secret",
  DASHBOARD_PUBLIC_URL: "https://dashboard.example.com",
  SESSION_COOKIE_SECRET: "test-secret-32-bytes-or-longer-please",
};

const PKCE_TTL_MS = 5 * 60 * 1000;

beforeEach(() => {
  tokenStore.__resetForTests();
  vi.restoreAllMocks();
  for (const [k, v] of Object.entries(ENV)) {
    vi.stubEnv(k, v);
  }
});
afterEach(() => {
  vi.unstubAllEnvs();
  vi.useRealTimers();
});

function buildPkceCookie(state: string, codeVerifier: string, ttlMs = PKCE_TTL_MS, next?: string): string {
  return signCookieValue(
    { state, code_verifier: codeVerifier, ...(next !== undefined ? { next } : {}) },
    ENV.SESSION_COOKIE_SECRET,
    ttlMs,
  );
}

function buildReq(opts: { code?: string; state?: string; cookie?: string }): Request {
  const url = new URL("https://dashboard.example.com/api/auth/callback");
  if (opts.code) url.searchParams.set("code", opts.code);
  if (opts.state) url.searchParams.set("state", opts.state);
  const headers: Record<string, string> = {};
  if (opts.cookie) headers.cookie = opts.cookie;
  return new Request(url.toString(), { headers });
}

const STATE = "happy-state-value";
const VERIFIER = "happy-verifier-value";
const PKCE_COOKIE_HEADER = (signed: string) => `oauth_state_pkce=${signed}`;

describe("GET /api/auth/callback", () => {
  it("happy path: 302 to /, two Set-Cookie headers, token-store populated", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(
        JSON.stringify({
          access_token: "access-tok",
          refresh_token: "refresh-tok",
          expires_in: 3600,
        }),
        { status: 200 },
      ),
    );
    const cookie = buildPkceCookie(STATE, VERIFIER);
    const res = await GET(buildReq({ code: "auth-code", state: STATE, cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.status).toBe(302);
    expect(res.headers.get("Location")).toBe("/");

    const setCookies = res.headers.getSetCookie();
    expect(setCookies.length).toBe(2);
    const sessionCookie = setCookies.find((s) => s.startsWith("dashboard_session="))!;
    const clearedPkce = setCookies.find((s) => s.startsWith("oauth_state_pkce="))!;
    expect(sessionCookie).toBeDefined();
    expect(clearedPkce).toContain("Max-Age=0");

    const sessionValue = sessionCookie.split(";")[0].slice("dashboard_session=".length);
    const decoded = verifyCookieValue<{ sessionId: string }>(sessionValue, ENV.SESSION_COOKIE_SECRET);
    expect(decoded).not.toBeNull();
    const stored = tokenStore.get(decoded!.sessionId);
    expect(stored?.access).toBe("access-tok");
    expect(stored?.refresh).toBe("refresh-tok");

    expect(fetchMock).toHaveBeenCalledTimes(1);
    const [tokenUrl, init] = fetchMock.mock.calls[0];
    expect(tokenUrl).toBe(`${ENV.OPENEMR_OAUTH_BASE}/oauth2/default/token`);
    const initObj = init as RequestInit & { cache?: string };
    expect(initObj.method).toBe("POST");
    expect(initObj.cache).toBe("no-store");
    const reqHeaders = new Headers(initObj.headers as HeadersInit);
    expect(reqHeaders.get("Content-Type")).toBe("application/x-www-form-urlencoded");
    const expectedBasic = "Basic " + Buffer.from(`${ENV.OPENEMR_DASHBOARD_CLIENT_ID}:${ENV.OPENEMR_DASHBOARD_CLIENT_SECRET}`).toString("base64");
    expect(reqHeaders.get("Authorization")).toBe(expectedBasic);
    const bodyStr = (initObj.body as URLSearchParams).toString();
    expect(bodyStr).toContain("grant_type=authorization_code");
    expect(bodyStr).toContain("code=auth-code");
    expect(bodyStr).toContain(`code_verifier=${VERIFIER}`);
    expect(bodyStr).toContain(encodeURIComponent("https://dashboard.example.com/api/auth/callback"));
  });

  it("redirects to ?next= path when present in PKCE cookie", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(
        JSON.stringify({ access_token: "a", refresh_token: "r", expires_in: 3600 }),
        { status: 200 },
      ),
    );
    const cookie = buildPkceCookie(STATE, VERIFIER, PKCE_TTL_MS, "/patient/abc-123");
    const res = await GET(buildReq({ code: "auth-code", state: STATE, cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.status).toBe(302);
    expect(res.headers.get("Location")).toBe("/patient/abc-123");
  });

  it("falls back to / when next is an open-redirect attempt", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(
        JSON.stringify({ access_token: "a", refresh_token: "r", expires_in: 3600 }),
        { status: 200 },
      ),
    );
    const cookie = buildPkceCookie(STATE, VERIFIER, PKCE_TTL_MS, "https://evil.example/oops");
    const res = await GET(buildReq({ code: "auth-code", state: STATE, cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.headers.get("Location")).toBe("/");
  });

  it("state mismatch → 400", async () => {
    const cookie = buildPkceCookie(STATE, VERIFIER);
    const res = await GET(buildReq({ code: "c", state: "different-state", cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.status).toBe(400);
  });

  it("missing PKCE cookie → 400", async () => {
    const res = await GET(buildReq({ code: "c", state: STATE }));
    expect(res.status).toBe(400);
  });

  it("missing code/state → 400", async () => {
    const cookie = buildPkceCookie(STATE, VERIFIER);
    const res = await GET(buildReq({ cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.status).toBe(400);
  });

  it("expired PKCE cookie → 400 (server-side TTL enforcement)", async () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date("2026-01-01T00:00:00Z"));
    const cookie = buildPkceCookie(STATE, VERIFIER, 60_000);
    vi.setSystemTime(new Date("2026-01-01T00:05:00Z")); // past 60s TTL
    const res = await GET(buildReq({ code: "c", state: STATE, cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.status).toBe(400);
  });

  it("token endpoint returns 400 → 500", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response("bad", { status: 400 }));
    const cookie = buildPkceCookie(STATE, VERIFIER);
    const res = await GET(buildReq({ code: "c", state: STATE, cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.status).toBe(500);
  });

  it("token endpoint throws → 500", async () => {
    vi.spyOn(globalThis, "fetch").mockRejectedValue(new Error("network"));
    const cookie = buildPkceCookie(STATE, VERIFIER);
    const res = await GET(buildReq({ code: "c", state: STATE, cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.status).toBe(500);
  });

  it("missing required env → 500", async () => {
    vi.stubEnv("OPENEMR_OAUTH_BASE", "");
    const cookie = buildPkceCookie(STATE, VERIFIER);
    const res = await GET(buildReq({ code: "c", state: STATE, cookie: PKCE_COOKIE_HEADER(cookie) }));
    expect(res.status).toBe(500);
  });
});
