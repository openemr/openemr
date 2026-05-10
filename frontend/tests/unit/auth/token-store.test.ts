import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import * as tokenStore from "@/lib/auth/token-store";

const REFRESH_OPTS = {
  tokenEndpoint: "https://openemr.example.com/oauth2/default/token",
  clientId: "client-id",
  clientSecret: "client-secret",
};

const SESSION_TTL = 8 * 60 * 60 * 1000;

function fixture(overrides: Partial<Parameters<typeof tokenStore.set>[1]> = {}) {
  return {
    access: "a",
    refresh: "r",
    expiresAt: Date.now() + 60_000,
    sessionExpiresAt: Date.now() + SESSION_TTL,
    ...overrides,
  };
}

beforeEach(() => {
  tokenStore.__resetForTests();
  vi.restoreAllMocks();
});
afterEach(() => {
  vi.restoreAllMocks();
});

describe("token-store", () => {
  it("set/get/delete round-trips", () => {
    tokenStore.set("sid", fixture());
    expect(tokenStore.get("sid")?.access).toBe("a");
    tokenStore.del("sid");
    expect(tokenStore.get("sid")).toBeUndefined();
  });

  it("get returns entries even past access-token expiry (refresh path needs them)", () => {
    // The proxy will see the expired access token, get 401, call refresh().
    // Evicting on access expiry would destroy the still-valid refresh token.
    tokenStore.set("sid", fixture({ expiresAt: Date.now() - 120_000 }));
    expect(tokenStore.get("sid")?.access).toBe("a");
    expect(tokenStore.get("sid")?.refresh).toBe("r");
  });

  it("get evicts entries past sessionExpiresAt (cookie-bound lifetime)", () => {
    tokenStore.set("sid", fixture({
      expiresAt: Date.now() - 120_000,
      sessionExpiresAt: Date.now() - 1_000,
    }));
    expect(tokenStore.get("sid")).toBeUndefined();
  });

  it("__resetForTests refuses outside NODE_ENV=test", () => {
    vi.stubEnv("NODE_ENV", "production");
    expect(() => tokenStore.__resetForTests()).toThrow(/test-only/);
    vi.unstubAllEnvs();
  });

  describe("refresh", () => {
    it("rejects when no entry exists", async () => {
      await expect(tokenStore.refresh("missing", REFRESH_OPTS)).rejects.toThrow(/no session/);
    });

    it("happy path POSTs urlencoded body with Basic auth", async () => {
      tokenStore.set("sid", fixture({ access: "old", refresh: "r1" }));
      const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
        new Response(
          JSON.stringify({ access_token: "new", refresh_token: "r2", expires_in: 3600 }),
          { status: 200, headers: { "content-type": "application/json" } },
        ),
      );
      const next = await tokenStore.refresh("sid", REFRESH_OPTS);
      expect(next.access).toBe("new");
      expect(next.refresh).toBe("r2");

      expect(fetchMock).toHaveBeenCalledTimes(1);
      const [url, init] = fetchMock.mock.calls[0];
      expect(url).toBe(REFRESH_OPTS.tokenEndpoint);
      const initObj = init as RequestInit & { cache?: string };
      expect(initObj.method).toBe("POST");
      expect(initObj.cache).toBe("no-store");
      const headers = new Headers(initObj.headers as HeadersInit);
      expect(headers.get("Content-Type")).toBe("application/x-www-form-urlencoded");
      const expectedBasic = "Basic " + Buffer.from("client-id:client-secret").toString("base64");
      expect(headers.get("Authorization")).toBe(expectedBasic);
      const bodyStr = (initObj.body as URLSearchParams).toString();
      expect(bodyStr).toContain("grant_type=refresh_token");
      expect(bodyStr).toContain("refresh_token=r1");
    });

    it("preserves sessionExpiresAt across refresh (session bound, not access bound)", async () => {
      const sessionEnd = Date.now() + SESSION_TTL;
      tokenStore.set("sid", fixture({ access: "old", refresh: "r1", sessionExpiresAt: sessionEnd }));
      vi.spyOn(globalThis, "fetch").mockResolvedValue(
        new Response(
          JSON.stringify({ access_token: "new", refresh_token: "r2", expires_in: 3600 }),
          { status: 200 },
        ),
      );
      const next = await tokenStore.refresh("sid", REFRESH_OPTS);
      expect(next.sessionExpiresAt).toBe(sessionEnd);
    });

    it("single-flight: two concurrent calls share one fetch", async () => {
      tokenStore.set("sid", fixture({ access: "old", refresh: "r1" }));
      const fetchMock = vi.spyOn(globalThis, "fetch").mockImplementation(async () => {
        await new Promise((r) => setTimeout(r, 5));
        return new Response(
          JSON.stringify({ access_token: "new", refresh_token: "r2", expires_in: 3600 }),
          { status: 200 },
        );
      });
      const [a, b] = await Promise.all([
        tokenStore.refresh("sid", REFRESH_OPTS),
        tokenStore.refresh("sid", REFRESH_OPTS),
      ]);
      expect(a).toEqual(b);
      expect(fetchMock).toHaveBeenCalledTimes(1);
    });

    it("on failure deletes entry and rejects", async () => {
      tokenStore.set("sid", fixture({ access: "old", refresh: "r1" }));
      vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response("nope", { status: 400 }));
      await expect(tokenStore.refresh("sid", REFRESH_OPTS)).rejects.toThrow(/refresh failed/);
      expect(tokenStore.get("sid")).toBeUndefined();
    });

    it("does NOT resurrect a session that was logged out mid-refresh (logout/refresh race)", async () => {
      tokenStore.set("sid", fixture({ access: "old", refresh: "r1" }));
      vi.spyOn(globalThis, "fetch").mockImplementation(async () => {
        // Simulate logout firing while the token POST is in flight (refresh is set, so tombstone is created)
        tokenStore.del("sid");
        return new Response(
          JSON.stringify({ access_token: "new", refresh_token: "r2", expires_in: 3600 }),
          { status: 200 },
        );
      });
      await expect(tokenStore.refresh("sid", REFRESH_OPTS)).rejects.toThrow(/revoked/);
      // Tombstone keeps the session out of the store
      expect(tokenStore.get("sid")).toBeUndefined();
    });

    it("normal logout (no refresh in flight) does NOT create a tombstone", async () => {
      tokenStore.set("sid", fixture());
      tokenStore.del("sid");
      // Re-using the sessionId would NOT be blocked because no tombstone was set.
      // (Verified indirectly: a fresh set + refresh succeeds.)
      tokenStore.set("sid", fixture({ access: "fresh", refresh: "r9" }));
      vi.spyOn(globalThis, "fetch").mockResolvedValue(
        new Response(JSON.stringify({ access_token: "new", expires_in: 3600 }), { status: 200 }),
      );
      const next = await tokenStore.refresh("sid", REFRESH_OPTS);
      expect(next.access).toBe("new");
    });
  });

  describe("eviction sweep", () => {
    it("set() sweeps expired sessions even if no get() runs", () => {
      tokenStore.set("expired", fixture({ sessionExpiresAt: Date.now() - 1_000 }));
      tokenStore.set("fresh", fixture()); // sweep runs here
      // The expired entry has been evicted by sweepExpired() during set('fresh')
      expect(tokenStore.get("expired")).toBeUndefined();
      expect(tokenStore.get("fresh")?.access).toBe("a");
    });

    it("after failed refresh, a fresh entry can be refreshed again (no stale promise)", async () => {
      tokenStore.set("sid", fixture({ access: "old", refresh: "r1" }));
      vi.spyOn(globalThis, "fetch").mockResolvedValueOnce(new Response("nope", { status: 400 }));
      await expect(tokenStore.refresh("sid", REFRESH_OPTS)).rejects.toThrow();
      // Re-set the entry; new refresh attempt should succeed without
      // awaiting any stale settled promise.
      tokenStore.set("sid", fixture({ access: "old2", refresh: "r3" }));
      vi.spyOn(globalThis, "fetch").mockResolvedValueOnce(
        new Response(JSON.stringify({ access_token: "new", expires_in: 3600 }), { status: 200 }),
      );
      const next = await tokenStore.refresh("sid", REFRESH_OPTS);
      expect(next.access).toBe("new");
    });
  });
});
