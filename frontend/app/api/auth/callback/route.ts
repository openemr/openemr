import { Buffer } from "node:buffer";
import { randomUUID, timingSafeEqual } from "node:crypto";
import { signCookieValue, verifyCookieValue, cookieAttrs, clearCookieAttrs } from "@/lib/auth/cookies";
import * as tokenStore from "@/lib/auth/token-store";
import { extractPreferredUsername } from "@/lib/auth/id-token";

export const runtime = "nodejs";

const PKCE_COOKIE = "oauth_state_pkce";
const SESSION_COOKIE = "dashboard_session";
const SESSION_TTL_MS = 8 * 60 * 60 * 1000; // 8 hours

interface PkceCookie {
  state: string;
  code_verifier: string;
  /**
   * Same-origin absolute path (e.g. "/patient/<uuid>") to redirect to
   * after a successful token exchange. Sanitized at /api/auth/login;
   * we still re-validate here as defense in depth before using it as a
   * Location header value.
   */
  next?: string;
}

function safeNextPath(raw: string | undefined): string {
  if (!raw) return "/";
  if (!raw.startsWith("/") || raw.startsWith("//")) return "/";
  if (/[\x00-\x1f\x7f]/.test(raw)) return "/";
  return raw;
}

function readCookie(req: Request, name: string): string | null {
  const header = req.headers.get("cookie");
  if (!header) return null;
  const match = header.match(new RegExp(`(?:^|;\\s*)${name}=([^;]+)`));
  return match ? match[1] : null;
}

function constantTimeStringEqual(a: string, b: string): boolean {
  const ab = Buffer.from(a);
  const bb = Buffer.from(b);
  if (ab.length !== bb.length) return false;
  return timingSafeEqual(ab, bb);
}

export async function GET(req: Request) {
  const oauthBase = process.env.OPENEMR_OAUTH_BASE;
  const clientId = process.env.OPENEMR_DASHBOARD_CLIENT_ID;
  const clientSecret = process.env.OPENEMR_DASHBOARD_CLIENT_SECRET;
  const publicUrl = process.env.DASHBOARD_PUBLIC_URL;
  const cookieSecret = process.env.SESSION_COOKIE_SECRET;

  if (!oauthBase || !clientId || !clientSecret || !publicUrl || !cookieSecret) {
    return new Response("missing required env", { status: 500 });
  }

  const url = new URL(req.url);
  const code = url.searchParams.get("code");
  const state = url.searchParams.get("state");
  if (!code || !state) {
    return new Response("missing code or state", { status: 400 });
  }

  const pkceRaw = readCookie(req, PKCE_COOKIE);
  if (!pkceRaw) {
    return new Response("missing pkce cookie", { status: 400 });
  }
  const pkce = verifyCookieValue<PkceCookie>(pkceRaw, cookieSecret);
  if (!pkce) {
    return new Response("invalid or expired pkce cookie", { status: 400 });
  }
  if (!constantTimeStringEqual(pkce.state, state)) {
    return new Response("state mismatch", { status: 400 });
  }

  const redirectUri = `${publicUrl.replace(/\/+$/, "")}/api/auth/callback`;
  const tokenEndpoint = `${oauthBase.replace(/\/+$/, "")}/oauth2/default/token`;
  const basic = Buffer.from(`${clientId}:${clientSecret}`).toString("base64");

  const body = new URLSearchParams({
    grant_type: "authorization_code",
    code,
    code_verifier: pkce.code_verifier,
    redirect_uri: redirectUri,
  });

  let tokenRes: Response;
  try {
    tokenRes = await fetch(tokenEndpoint, {
      method: "POST",
      cache: "no-store",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        Authorization: `Basic ${basic}`,
      },
      body,
    });
  } catch {
    return new Response("token endpoint unreachable", { status: 500 });
  }

  if (!tokenRes.ok) {
    return new Response("token exchange failed", { status: 500 });
  }

  const tokenJson = (await tokenRes.json()) as {
    access_token: string;
    refresh_token?: string;
    expires_in: number;
    id_token?: string;
  };

  const sessionId = randomUUID();
  const now = Date.now();
  tokenStore.set(sessionId, {
    access: tokenJson.access_token,
    refresh: tokenJson.refresh_token ?? "",
    expiresAt: now + tokenJson.expires_in * 1000,
    // Session-bound eviction matching the cookie TTL — prevents indefinite
    // token retention for users who never explicitly log out.
    sessionExpiresAt: now + SESSION_TTL_MS,
    // OIDC ID token (when scope includes `openid`, which we always request)
    // carries the OpenEMR username in `preferred_username`. We extract and
    // store it so the FHIR proxy's panel-scope gate can match it against
    // Patient.generalPractitioner references. Trust comes from the TLS POST
    // to the token endpoint, not from JWT signature verification.
    openemrUsername: extractPreferredUsername(tokenJson.id_token),
  });

  const sessionCookie = signCookieValue({ sessionId }, cookieSecret, SESSION_TTL_MS);
  const headers = new Headers();
  headers.set("Location", safeNextPath(pkce.next));
  // TWO Set-Cookie headers: set the new session AND clear the now-spent
  // PKCE state cookie. headers.append (not .set) is required so both
  // survive — .set would silently drop one.
  headers.append("Set-Cookie", `${SESSION_COOKIE}=${sessionCookie}; ${cookieAttrs(SESSION_TTL_MS / 1000)}`);
  headers.append("Set-Cookie", `${PKCE_COOKIE}=; ${clearCookieAttrs()}`);
  return new Response(null, { status: 302, headers });
}
