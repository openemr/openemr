import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { GET } from "@/app/api/auth/logout/route";
import { signCookieValue } from "@/lib/auth/cookies";
import * as tokenStore from "@/lib/auth/token-store";

const SECRET = "test-secret-32-bytes-or-longer-please";
const SESSION_TTL_MS = 8 * 60 * 60 * 1000;

beforeEach(() => {
  tokenStore.__resetForTests();
  vi.stubEnv("SESSION_COOKIE_SECRET", SECRET);
});
afterEach(() => {
  vi.unstubAllEnvs();
});

describe("GET /api/auth/logout", () => {
  it("clears the session cookie and evicts the token-store entry", async () => {
    tokenStore.set("sid-1", { access: "a", refresh: "r", expiresAt: Date.now() + 60_000, sessionExpiresAt: Date.now() + SESSION_TTL_MS });
    const cookie = signCookieValue({ sessionId: "sid-1" }, SECRET, SESSION_TTL_MS);
    const req = new Request("https://dashboard.example.com/api/auth/logout", {
      headers: { cookie: `dashboard_session=${cookie}` },
    });
    const res = await GET(req);
    expect(res.status).toBe(302);
    expect(res.headers.get("Location")).toBe("/");
    const setCookie = res.headers.get("Set-Cookie") ?? "";
    expect(setCookie).toMatch(/^dashboard_session=/);
    expect(setCookie).toContain("Max-Age=0");
    expect(tokenStore.get("sid-1")).toBeUndefined();
  });

  it("with no session cookie still returns 302 (idempotent)", async () => {
    const req = new Request("https://dashboard.example.com/api/auth/logout");
    const res = await GET(req);
    expect(res.status).toBe(302);
    expect(res.headers.get("Set-Cookie")).toContain("Max-Age=0");
  });

  it("with garbage session cookie still returns 302", async () => {
    const req = new Request("https://dashboard.example.com/api/auth/logout", {
      headers: { cookie: "dashboard_session=not-a-valid-signed-cookie" },
    });
    const res = await GET(req);
    expect(res.status).toBe(302);
  });
});
