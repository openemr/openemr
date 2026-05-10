import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { GET } from "@/app/api/auth/login/route";
import { verifyCookieValue } from "@/lib/auth/cookies";

const ENV = {
  OPENEMR_OAUTH_BASE: "https://openemr.example.com",
  OPENEMR_FHIR_BASE: "https://openemr.example.com/apis/default/fhir",
  OPENEMR_DASHBOARD_CLIENT_ID: "test-client-id",
  DASHBOARD_PUBLIC_URL: "https://dashboard.example.com",
  SESSION_COOKIE_SECRET: "test-secret-32-bytes-or-longer-please",
};

describe("GET /api/auth/login", () => {
  beforeEach(() => {
    for (const [k, v] of Object.entries(ENV)) {
      vi.stubEnv(k, v);
    }
  });
  afterEach(() => {
    vi.unstubAllEnvs();
  });

  it("returns 500 when env is missing", async () => {
    vi.stubEnv("OPENEMR_OAUTH_BASE", "");
    const res = await GET(new Request("http://test/api/auth/login"));
    expect(res.status).toBe(500);
  });

  it("redirects 302 to OpenEMR authorize URL with all PKCE params", async () => {
    const res = await GET(new Request("http://test/api/auth/login"));
    expect(res.status).toBe(302);
    const loc = res.headers.get("Location");
    expect(loc).not.toBeNull();
    const url = new URL(loc!);
    expect(url.origin).toBe(ENV.OPENEMR_OAUTH_BASE);
    expect(url.pathname).toBe("/oauth2/default/authorize");
    expect(url.searchParams.get("response_type")).toBe("code");
    expect(url.searchParams.get("client_id")).toBe(ENV.OPENEMR_DASHBOARD_CLIENT_ID);
    expect(url.searchParams.get("redirect_uri")).toBe(`${ENV.DASHBOARD_PUBLIC_URL}/api/auth/callback`);
    expect(url.searchParams.get("code_challenge_method")).toBe("S256");
    expect(url.searchParams.get("code_challenge")).toMatch(/^[A-Za-z0-9_-]+$/);
    expect(url.searchParams.get("state")).toMatch(/^[A-Za-z0-9_-]+$/);
    const scope = url.searchParams.get("scope") ?? "";
    expect(scope).toContain("openid");
    expect(scope).toContain("offline_access");
    expect(scope).toContain("api:fhir");
    expect(scope).toContain("user/Patient.read");
    expect(scope).toContain("user/AllergyIntolerance.read");
    expect(scope).toContain("user/Condition.read");
    expect(scope).toContain("user/MedicationRequest.read");
    expect(scope).toContain("user/CareTeam.read");
    expect(scope).toContain("user/Encounter.read");
    // aud is required by OpenEMR SMART-on-FHIR for any FHIR-scoped request
    expect(url.searchParams.get("aud")).toBe(ENV.OPENEMR_FHIR_BASE);
  });

  it("strips trailing slashes from DASHBOARD_PUBLIC_URL when building redirect_uri", async () => {
    vi.stubEnv("DASHBOARD_PUBLIC_URL", "https://dashboard.example.com/");
    const res = await GET(new Request("http://test/api/auth/login"));
    const url = new URL(res.headers.get("Location")!);
    expect(url.searchParams.get("redirect_uri")).toBe(
      "https://dashboard.example.com/api/auth/callback",
    );
  });

  it("strips trailing slashes from OPENEMR_FHIR_BASE when building aud", async () => {
    vi.stubEnv("OPENEMR_FHIR_BASE", "https://openemr.example.com/apis/default/fhir/");
    const res = await GET(new Request("http://test/api/auth/login"));
    const url = new URL(res.headers.get("Location")!);
    expect(url.searchParams.get("aud")).toBe(
      "https://openemr.example.com/apis/default/fhir",
    );
  });

  it("sets the oauth_state_pkce cookie with HttpOnly + SameSite=Lax + Max-Age=300", async () => {
    vi.stubEnv("NODE_ENV", "development");
    const res = await GET(new Request("http://test/api/auth/login"));
    const setCookie = res.headers.get("Set-Cookie");
    expect(setCookie).not.toBeNull();
    expect(setCookie).toMatch(/^oauth_state_pkce=/);
    expect(setCookie).toContain("HttpOnly");
    expect(setCookie).toContain("SameSite=Lax");
    expect(setCookie).toContain("Max-Age=300");
    expect(setCookie).not.toContain("Secure");
  });

  it("includes Secure on the Set-Cookie when NODE_ENV=production", async () => {
    vi.stubEnv("NODE_ENV", "production");
    const res = await GET(new Request("http://test/api/auth/login"));
    const setCookie = res.headers.get("Set-Cookie") ?? "";
    expect(setCookie).toContain("Secure");
  });

  it("uses SameSite=None in production (PKCE cookie must survive iframe round trip)", async () => {
    vi.stubEnv("NODE_ENV", "production");
    const res = await GET(new Request("http://test/api/auth/login"));
    const setCookie = res.headers.get("Set-Cookie") ?? "";
    expect(setCookie).toContain("SameSite=None");
    expect(setCookie).not.toContain("SameSite=Lax");
  });

  it("the signed cookie verifies and contains state + code_verifier matching the redirect", async () => {
    const res = await GET(new Request("http://test/api/auth/login"));
    const setCookie = res.headers.get("Set-Cookie") ?? "";
    const cookieValue = setCookie.split(";")[0].split("=").slice(1).join("=");
    const decoded = verifyCookieValue<{ state: string; code_verifier: string; next?: string }>(cookieValue, ENV.SESSION_COOKIE_SECRET);
    expect(decoded).not.toBeNull();
    const url = new URL(res.headers.get("Location")!);
    expect(decoded!.state).toBe(url.searchParams.get("state"));
    expect(decoded!.code_verifier).toMatch(/^[A-Za-z0-9_-]+$/);
    // next defaults to "/" when no ?next= param is provided
    expect(decoded!.next).toBe("/");
  });

  it("preserves a same-origin ?next= path in the PKCE cookie", async () => {
    const res = await GET(new Request("http://test/api/auth/login?next=/patient/abc-123"));
    const setCookie = res.headers.get("Set-Cookie") ?? "";
    const cookieValue = setCookie.split(";")[0].split("=").slice(1).join("=");
    const decoded = verifyCookieValue<{ next?: string }>(cookieValue, ENV.SESSION_COOKIE_SECRET);
    expect(decoded?.next).toBe("/patient/abc-123");
  });

  it("rejects an open-redirect attempt (?next=https://evil.example) and falls back to /", async () => {
    const res = await GET(new Request("http://test/api/auth/login?next=https://evil.example/oops"));
    const setCookie = res.headers.get("Set-Cookie") ?? "";
    const cookieValue = setCookie.split(";")[0].split("=").slice(1).join("=");
    const decoded = verifyCookieValue<{ next?: string }>(cookieValue, ENV.SESSION_COOKIE_SECRET);
    expect(decoded?.next).toBe("/");
  });

  it("rejects a protocol-relative ?next=//evil.example (open-redirect class)", async () => {
    const res = await GET(new Request("http://test/api/auth/login?next=//evil.example/oops"));
    const setCookie = res.headers.get("Set-Cookie") ?? "";
    const cookieValue = setCookie.split(";")[0].split("=").slice(1).join("=");
    const decoded = verifyCookieValue<{ next?: string }>(cookieValue, ENV.SESSION_COOKIE_SECRET);
    expect(decoded?.next).toBe("/");
  });
});
