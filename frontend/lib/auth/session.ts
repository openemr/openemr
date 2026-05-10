/**
 * Server-only helpers for reading the current dashboard session.
 *
 * Importing this file from a "use client" component will fail at build
 * time — `next/headers` cookies() and the in-memory token-store both
 * require the Node runtime.
 */

import { cookies } from "next/headers";
import { verifyCookieValue } from "./cookies";
import * as tokenStore from "./token-store";

const SESSION_COOKIE = "dashboard_session";

export interface SessionUser {
  openemrUsername: string | null;
}

/**
 * Returns the OpenEMR username for the current session, or null if not
 * signed in / cookie invalid / token-store entry missing.
 */
export async function getSessionUser(): Promise<SessionUser> {
  const cookieSecret = process.env.SESSION_COOKIE_SECRET;
  if (!cookieSecret) return { openemrUsername: null };
  const store = await cookies();
  const raw = store.get(SESSION_COOKIE);
  if (!raw) return { openemrUsername: null };
  const decoded = verifyCookieValue<{ sessionId: string }>(raw.value, cookieSecret);
  if (!decoded) return { openemrUsername: null };
  const entry = tokenStore.get(decoded.sessionId);
  if (!entry) return { openemrUsername: null };
  return { openemrUsername: entry.openemrUsername ?? null };
}
