import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { GET } from "@/app/api/fhir/[...path]/route";
import { signCookieValue } from "@/lib/auth/cookies";
import * as tokenStore from "@/lib/auth/token-store";
import * as panelCache from "@/lib/auth/panel-cache";
import type { NextRequest } from "next/server";

const ENV = {
  OPENEMR_OAUTH_BASE: "https://openemr.example.com",
  OPENEMR_FHIR_BASE: "https://openemr.example.com/apis/default/fhir",
  OPENEMR_DASHBOARD_CLIENT_ID: "test-client-id",
  OPENEMR_DASHBOARD_CLIENT_SECRET: "test-client-secret",
  DASHBOARD_PUBLIC_URL: "https://dashboard.example.com",
  SESSION_COOKIE_SECRET: "test-secret-32-bytes-or-longer-please",
  // Default: existing tests run as an admin user so the panel-scope gate
  // bypasses without firing an extra Patient/{id} fetch. Panel-scope-
  // specific tests below override this.
  COPILOT_ADMIN_USERS: "admin-user",
};
const SESSION_TTL_MS = 8 * 60 * 60 * 1000;

beforeEach(() => {
  tokenStore.__resetForTests();
  panelCache.__resetForTests();
  vi.restoreAllMocks();
  for (const [k, v] of Object.entries(ENV)) vi.stubEnv(k, v);
});
afterEach(() => {
  vi.unstubAllEnvs();
});

function buildReq(opts: {
  pathname: string;
  search?: string;
  cookie?: string;
}): NextRequest {
  const url = `https://dashboard.example.com${opts.pathname}${opts.search ?? ""}`;
  const headers: Record<string, string> = {};
  if (opts.cookie) headers.cookie = opts.cookie;
  // Build a mock NextRequest by augmenting Request with `nextUrl`.
  const req = new Request(url, { headers }) as Request & { nextUrl: URL };
  Object.defineProperty(req, "nextUrl", { value: new URL(url), writable: false });
  return req as unknown as NextRequest;
}

function sessionCookieFor(sessionId: string): string {
  const signed = signCookieValue({ sessionId }, ENV.SESSION_COOKIE_SECRET, SESSION_TTL_MS);
  return `dashboard_session=${signed}`;
}

function makeBundleResponse(): Response {
  return new Response(
    JSON.stringify({ resourceType: "Bundle", entry: [{ resource: { resourceType: "Patient", id: "123" } }] }),
    { status: 200, headers: { "content-type": "application/fhir+json" } },
  );
}

describe("GET /api/fhir/[...path]", () => {
  it("happy path: token-store populated, fetch returns Bundle, proxy returns 200 + no-store cache", async () => {
    tokenStore.set("sid-1", {
      access: "tok-A",
      refresh: "ref-1",
      expiresAt: Date.now() + 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(makeBundleResponse());
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.status).toBe(200);
    expect(res.headers.get("Cache-Control")).toBe("no-store, private");
    const body = (await res.json()) as { resourceType: string };
    expect(body.resourceType).toBe("Bundle");
    expect(fetchMock).toHaveBeenCalledTimes(1);
    const [url, init] = fetchMock.mock.calls[0];
    expect(url.toString()).toBe("https://openemr.example.com/apis/default/fhir/Patient/123");
    const initObj = init as RequestInit & { cache?: string };
    expect(initObj.cache).toBe("no-store");
    const headers = new Headers(initObj.headers as HeadersInit);
    expect(headers.get("Authorization")).toBe("Bearer tok-A");
  });

  it("preserves query string for resource searches", async () => {
    tokenStore.set("sid-1", {
      access: "tok-A",
      refresh: "ref-1",
      expiresAt: Date.now() + 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(makeBundleResponse());
    await GET(
      buildReq({
        pathname: "/api/fhir/MedicationRequest",
        search: "?patient=123&_count=10",
        cookie: sessionCookieFor("sid-1"),
      }),
      { params: Promise.resolve({ path: ["MedicationRequest"] }) },
    );
    const [url] = fetchMock.mock.calls[0];
    expect(url.toString()).toBe(
      "https://openemr.example.com/apis/default/fhir/MedicationRequest?patient=123&_count=10",
    );
  });

  it("401-then-refresh-success: exactly two FHIR fetches + one refresh fetch", async () => {
    tokenStore.set("sid-1", {
      access: "tok-OLD",
      refresh: "ref-1",
      expiresAt: Date.now() - 60_000, // expired access (refresh path will fire)
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    const fetchMock = vi.spyOn(globalThis, "fetch").mockImplementation(async (input, init) => {
      const url = typeof input === "string" ? input : (input as URL).toString();
      if (url.includes("/oauth2/default/token")) {
        return new Response(
          JSON.stringify({ access_token: "tok-NEW", refresh_token: "ref-2", expires_in: 3600 }),
          { status: 200 },
        );
      }
      // Distinguish first vs second FHIR fetch by checking the bearer header
      const auth = init ? new Headers(init.headers as HeadersInit).get("Authorization") : null;
      if (auth === "Bearer tok-OLD") return new Response("expired", { status: 401 });
      if (auth === "Bearer tok-NEW") return makeBundleResponse();
      return new Response("unexpected", { status: 500 });
    });
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.status).toBe(200);
    // 2 FHIR fetches + 1 refresh = 3 total
    expect(fetchMock).toHaveBeenCalledTimes(3);
  });

  it("401-then-refresh-fail: returns 401 with cookie cleared", async () => {
    tokenStore.set("sid-1", {
      access: "tok-OLD",
      refresh: "ref-1",
      expiresAt: Date.now() - 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    vi.spyOn(globalThis, "fetch").mockImplementation(async (input) => {
      const url = typeof input === "string" ? input : (input as URL).toString();
      if (url.includes("/oauth2/default/token")) {
        return new Response("denied", { status: 400 });
      }
      return new Response("expired", { status: 401 });
    });
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.status).toBe(401);
    expect(res.headers.get("Set-Cookie") ?? "").toContain("Max-Age=0");
  });

  it("missing session cookie → 401, no upstream fetch", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch");
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123" }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.status).toBe(401);
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it("invalid session cookie → 401, no upstream fetch", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch");
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: "dashboard_session=garbage" }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.status).toBe(401);
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it("session cookie OK but token-store empty (post-restart) → 401", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch");
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("never-stored") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.status).toBe(401);
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it("path traversal: /api/fhir/Patient/.. → 400, no upstream fetch", async () => {
    tokenStore.set("sid-1", {
      access: "tok-A",
      refresh: "ref-1",
      expiresAt: Date.now() + 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    const fetchMock = vi.spyOn(globalThis, "fetch");
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/..", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", ".."] }) },
    );
    expect(res.status).toBe(400);
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it("missing required env → 500", async () => {
    vi.stubEnv("OPENEMR_FHIR_BASE", "");
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.status).toBe(500);
  });

  it("forwards Prefer request header for FHIR Bulk operations", async () => {
    tokenStore.set("sid-1", {
      access: "tok-A",
      refresh: "ref-1",
      expiresAt: Date.now() + 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response("", { status: 202, headers: { "content-location": "https://openemr.example.com/apis/default/fhir/$bulkdata-status?job=abc" } }),
    );
    const url = "https://dashboard.example.com/api/fhir/$export";
    const req = new Request(url, {
      headers: { cookie: sessionCookieFor("sid-1"), prefer: "respond-async" },
    }) as Request & { nextUrl: URL };
    Object.defineProperty(req, "nextUrl", { value: new URL(url), writable: false });
    const res = await GET(req as unknown as NextRequest, { params: Promise.resolve({ path: ["$export"] }) });
    expect(res.status).toBe(202);
    // Content-Location should be REWRITTEN to point through the dashboard proxy,
    // so the browser keeps polling via /api/fhir (which has the session cookie),
    // not directly against OpenEMR (which would fail auth).
    expect(res.headers.get("Content-Location")).toBe("https://dashboard.example.com/api/fhir/$bulkdata-status?job=abc");
    const init = fetchMock.mock.calls[0][1] as RequestInit;
    const sentHeaders = new Headers(init.headers as HeadersInit);
    expect(sentHeaders.get("Prefer")).toBe("respond-async");
  });

  it("does NOT rewrite Content-Location URLs that point elsewhere", async () => {
    tokenStore.set("sid-1", {
      access: "tok-A",
      refresh: "ref-1",
      expiresAt: Date.now() + 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response("", { status: 202, headers: { "content-location": "https://other.example.com/some/path" } }),
    );
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.headers.get("Content-Location")).toBe("https://other.example.com/some/path");
  });

  it("strips Content-Encoding and Content-Length (undici decompresses upstream)", async () => {
    tokenStore.set("sid-1", {
      access: "tok-A",
      refresh: "ref-1",
      expiresAt: Date.now() + 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response("{}", {
        status: 200,
        headers: {
          "content-type": "application/fhir+json",
          "content-encoding": "gzip",
          "content-length": "999",
        },
      }),
    );
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.headers.get("Content-Encoding")).toBeNull();
    expect(res.headers.get("Content-Length")).toBeNull();
  });

  it("forces Cache-Control: no-store, private on responses (overrides upstream)", async () => {
    tokenStore.set("sid-1", {
      access: "tok-A",
      refresh: "ref-1",
      expiresAt: Date.now() + 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response("{}", {
        status: 200,
        headers: {
          "content-type": "application/fhir+json",
          "cache-control": "max-age=3600", // upstream tries to cache; we MUST override
          "set-cookie": "uppercase-evil=yes", // upstream cookie MUST be suppressed
        },
      }),
    );
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.headers.get("Cache-Control")).toBe("no-store, private");
    expect(res.headers.get("Set-Cookie")).toBeNull();
  });

  describe("panel-scope gate", () => {
    function makePatientResponse(generalPractitionerRefs: string[]): Response {
      const body = {
        resourceType: "Patient",
        id: "patient-X",
        generalPractitioner: generalPractitionerRefs.map((r) => ({ reference: r })),
      };
      return new Response(JSON.stringify(body), {
        status: 200,
        headers: { "content-type": "application/fhir+json" },
      });
    }
    function makeBundleResponseAlt(): Response {
      return new Response(
        JSON.stringify({ resourceType: "Bundle", entry: [] }),
        { status: 200, headers: { "content-type": "application/fhir+json" } },
      );
    }

    it("non-admin with matching GP → allow (proxies through)", async () => {
      tokenStore.set("sid-1", {
        access: "tok-A",
        refresh: "ref-1",
        expiresAt: Date.now() + 60_000,
        sessionExpiresAt: Date.now() + SESSION_TTL_MS,
        openemrUsername: "dr-smith",
      });
      let calls = 0;
      vi.spyOn(globalThis, "fetch").mockImplementation(async () => {
        calls += 1;
        // Call 1 = panel-scope GP lookup (returns Patient)
        // Call 2 = main proxy fetch (returns Bundle)
        return calls === 1
          ? makePatientResponse(["Practitioner/dr-smith"])
          : makeBundleResponseAlt();
      });
      const res = await GET(
        buildReq({ pathname: "/api/fhir/Encounter", search: "?patient=patient-X", cookie: sessionCookieFor("sid-1") }),
        { params: Promise.resolve({ path: ["Encounter"] }) },
      );
      expect(res.status).toBe(200);
    });

    it("non-admin with mismatching GP → 403, no upstream main fetch", async () => {
      tokenStore.set("sid-1", {
        access: "tok-A",
        refresh: "ref-1",
        expiresAt: Date.now() + 60_000,
        sessionExpiresAt: Date.now() + SESSION_TTL_MS,
        openemrUsername: "dr-smith",
      });
      let calls = 0;
      vi.spyOn(globalThis, "fetch").mockImplementation(async () => {
        calls += 1;
        return makePatientResponse(["Practitioner/dr-jones"]);
      });
      const res = await GET(
        buildReq({ pathname: "/api/fhir/Encounter", search: "?patient=patient-X", cookie: sessionCookieFor("sid-1") }),
        { params: Promise.resolve({ path: ["Encounter"] }) },
      );
      expect(res.status).toBe(403);
      // Exactly one fetch — the GP lookup. No main upstream fetch.
      expect(calls).toBe(1);
    });

    it("non-admin with empty GP → fallthrough (allow, default)", async () => {
      tokenStore.set("sid-1", {
        access: "tok-A",
        refresh: "ref-1",
        expiresAt: Date.now() + 60_000,
        sessionExpiresAt: Date.now() + SESSION_TTL_MS,
        openemrUsername: "dr-smith",
      });
      let calls = 0;
      vi.spyOn(globalThis, "fetch").mockImplementation(async () => {
        calls += 1;
        return calls === 1 ? makePatientResponse([]) : makeBundleResponseAlt();
      });
      const res = await GET(
        buildReq({ pathname: "/api/fhir/MedicationRequest", search: "?patient=patient-X", cookie: sessionCookieFor("sid-1") }),
        { params: Promise.resolve({ path: ["MedicationRequest"] }) },
      );
      expect(res.status).toBe(200);
    });

    it("STRICT_PANEL_SCOPE=true + empty GP → 403", async () => {
      vi.stubEnv("STRICT_PANEL_SCOPE", "true");
      tokenStore.set("sid-1", {
        access: "tok-A",
        refresh: "ref-1",
        expiresAt: Date.now() + 60_000,
        sessionExpiresAt: Date.now() + SESSION_TTL_MS,
        openemrUsername: "dr-smith",
      });
      let calls = 0;
      vi.spyOn(globalThis, "fetch").mockImplementation(async () => {
        calls += 1;
        return makePatientResponse([]);
      });
      const res = await GET(
        buildReq({ pathname: "/api/fhir/MedicationRequest", search: "?patient=patient-X", cookie: sessionCookieFor("sid-1") }),
        { params: Promise.resolve({ path: ["MedicationRequest"] }) },
      );
      expect(res.status).toBe(403);
      expect(calls).toBe(1); // GP lookup only; main not called
    });

    it("query subject=Patient/<id> is detected for panel-scope", async () => {
      tokenStore.set("sid-1", {
        access: "tok-A",
        refresh: "ref-1",
        expiresAt: Date.now() + 60_000,
        sessionExpiresAt: Date.now() + SESSION_TTL_MS,
        openemrUsername: "dr-smith",
      });
      let calls = 0;
      vi.spyOn(globalThis, "fetch").mockImplementation(async () => {
        calls += 1;
        return calls === 1 ? makePatientResponse(["Practitioner/dr-jones"]) : makeBundleResponseAlt();
      });
      const res = await GET(
        buildReq({ pathname: "/api/fhir/Observation", search: "?subject=Patient/patient-X", cookie: sessionCookieFor("sid-1") }),
        { params: Promise.resolve({ path: ["Observation"] }) },
      );
      expect(res.status).toBe(403);
    });
  });

  it("async params shape: params is awaited", async () => {
    tokenStore.set("sid-1", {
      access: "tok-A",
      refresh: "ref-1",
      expiresAt: Date.now() + 60_000,
      sessionExpiresAt: Date.now() + SESSION_TTL_MS,
      openemrUsername: "admin-user",
    });
    vi.spyOn(globalThis, "fetch").mockResolvedValue(makeBundleResponse());
    // Explicitly pass a Promise to verify the await works.
    const res = await GET(
      buildReq({ pathname: "/api/fhir/Patient/123", cookie: sessionCookieFor("sid-1") }),
      { params: Promise.resolve({ path: ["Patient", "123"] }) },
    );
    expect(res.status).toBe(200);
  });
});
