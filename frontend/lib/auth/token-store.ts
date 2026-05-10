/**
 * In-memory OAuth token store with single-flight refresh.
 *
 * The store is module-scope state. This is intentional and acceptable for a
 * single-replica Railway deployment; if we ever scale horizontally, a
 * shared backing store (Redis) becomes necessary. Module-scope state means
 * route handlers MUST run in the Node.js runtime, not Edge — every route
 * that touches this store exports `runtime = 'nodejs'`.
 *
 * Tokens never leave this process; the browser only ever holds a signed
 * `dashboard_session=<sessionId>` cookie that maps into this map.
 *
 * Lifecycle invariants:
 * - Each entry carries a `sessionExpiresAt` mirroring the cookie TTL.
 *   `get()` evicts entries past `sessionExpiresAt`, bounding memory growth.
 * - `del()` during an in-flight refresh sets `entry.revoked = true` and
 *   leaves the entry in place; the refresh's success path detects this
 *   and removes the entry instead of resurrecting it. After the refresh
 *   settles, the revoked flag is no longer needed and the entry is gone.
 *   Without an in-flight refresh, `del()` removes the entry immediately.
 */

import { Buffer } from "node:buffer";

export interface TokenEntry {
  access: string;
  refresh: string;
  /** epoch ms — when the access token stops being usable. */
  expiresAt: number;
  /**
   * epoch ms — when the entire session is forcibly evicted regardless of
   * refresh token state. Mirrors the dashboard_session cookie TTL (8h).
   */
  sessionExpiresAt: number;
  /**
   * OpenEMR username extracted from the OIDC ID token's `preferred_username`
   * claim at OAuth callback. Used by the FHIR proxy's panel-scope gate to
   * compare against `Patient.generalPractitioner` references.
   */
  openemrUsername?: string;
  /**
   * In-flight refresh promise. Set when a refresh is underway; concurrent
   * callers `await` the same promise to avoid double-fetching the token
   * endpoint (single-flight refresh).
   */
  refreshing?: Promise<TokenEntry>;
  /**
   * Set by `del()` when called while a refresh is in flight. Prevents the
   * refresh's success path from resurrecting a logged-out session, and
   * makes `get()` treat the entry as already gone.
   */
  revoked?: boolean;
}

export interface RefreshOptions {
  /** Full URL to OpenEMR's token endpoint, e.g. `${OAUTH_BASE}/oauth2/default/token`. */
  tokenEndpoint: string;
  clientId: string;
  clientSecret: string;
}

const store = new Map<string, TokenEntry>();

function sweepExpired(): void {
  const now = Date.now();
  for (const [k, v] of store.entries()) {
    // Don't evict entries with an in-flight refresh — the refresh handler
    // owns lifecycle for those (it will settle and remove or update them).
    if (v.sessionExpiresAt <= now && !v.refreshing) {
      store.delete(k);
    }
  }
}

export function set(sessionId: string, entry: Omit<TokenEntry, "refreshing" | "revoked">): void {
  // Sweep on the write path so expired sessions are bounded even when no
  // later get() ever runs (e.g. user closes the browser and never returns).
  sweepExpired();
  store.set(sessionId, { ...entry });
}

export function get(sessionId: string): TokenEntry | undefined {
  const entry = store.get(sessionId);
  if (!entry) return undefined;
  // Treat revoked entries as gone — they're only kept around so an
  // in-flight refresh can detect the logout and finalize cleanup.
  if (entry.revoked) return undefined;
  // Bounded session lifetime — after this, the cookie is also expired
  // server-side (matching cookie TTL); evict to bound memory growth even
  // for users who never explicitly log out.
  if (entry.sessionExpiresAt <= Date.now()) {
    store.delete(sessionId);
    return undefined;
  }
  return entry;
}

export function del(sessionId: string): void {
  const entry = store.get(sessionId);
  if (!entry) return;
  if (entry.refreshing) {
    // Logout/refresh race: keep the entry in the map but mark it revoked.
    // The in-flight refresh's success path checks this flag and will
    // remove the entry instead of overwriting it with fresh tokens. This
    // is race-correct independent of how long the refresh takes — no
    // wall-clock TTL on tombstones to defeat.
    entry.revoked = true;
  } else {
    store.delete(sessionId);
  }
}

export function refresh(sessionId: string, opts: RefreshOptions): Promise<TokenEntry> {
  // Sweep on refresh too — refresh is a write-path event.
  sweepExpired();
  const entry = store.get(sessionId);
  if (!entry) {
    return Promise.reject(new Error("no session entry to refresh"));
  }
  if (entry.refreshing) {
    return entry.refreshing;
  }
  // Set entry.refreshing BEFORE calling doRefresh so any concurrent del()
  // (including ones triggered synchronously inside the fetch mock or the
  // real underlying transport) sees the in-flight state and can correctly
  // tombstone the session. If we assigned after doRefresh() returned, the
  // pre-await sync portion of doRefresh would already have run with
  // entry.refreshing still undefined.
  let resolveOuter!: (v: TokenEntry) => void;
  let rejectOuter!: (e: unknown) => void;
  const outerPromise = new Promise<TokenEntry>((res, rej) => {
    resolveOuter = res;
    rejectOuter = rej;
  });
  entry.refreshing = outerPromise;
  doRefresh(sessionId, entry, opts).then(resolveOuter, rejectOuter);
  return outerPromise;
}

async function doRefresh(
  sessionId: string,
  entry: TokenEntry,
  opts: RefreshOptions,
): Promise<TokenEntry> {
  try {
    const body = new URLSearchParams({
      grant_type: "refresh_token",
      refresh_token: entry.refresh,
    });
    const basic = Buffer.from(`${opts.clientId}:${opts.clientSecret}`).toString("base64");
    const res = await fetch(opts.tokenEndpoint, {
      method: "POST",
      cache: "no-store",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        Authorization: `Basic ${basic}`,
      },
      body,
    });
    if (!res.ok) {
      store.delete(sessionId);
      throw new Error(`refresh failed: ${res.status}`);
    }
    const json = (await res.json()) as {
      access_token: string;
      refresh_token?: string;
      expires_in: number;
    };
    // Logout/refresh race: if /api/auth/logout fired during the token POST,
    // del() set entry.revoked=true. Honor logout — finalize cleanup and
    // do NOT resurrect the session. This check is race-correct regardless
    // of how long the token POST took.
    if (entry.revoked) {
      store.delete(sessionId);
      throw new Error("session was revoked during refresh");
    }
    const next: TokenEntry = {
      access: json.access_token,
      refresh: json.refresh_token ?? entry.refresh,
      expiresAt: Date.now() + json.expires_in * 1000,
      sessionExpiresAt: entry.sessionExpiresAt, // preserve original session bound
      openemrUsername: entry.openemrUsername,    // preserve username across refresh
    };
    store.set(sessionId, next);
    return next;
  } catch (err) {
    store.delete(sessionId);
    throw err;
  } finally {
    // Whether success or failure, the in-flight promise is settled — don't
    // leave a stale reference that future callers would await forever.
    const cur = store.get(sessionId);
    if (cur) {
      delete cur.refreshing;
    }
  }
}

/**
 * Test-only helper: reset module-scope state between tests. Refuses to run
 * outside `NODE_ENV=test` so production code can't accidentally evict
 * everyone.
 */
export function __resetForTests(): void {
  if (process.env.NODE_ENV !== "test") {
    throw new Error("__resetForTests is test-only");
  }
  store.clear();
}
