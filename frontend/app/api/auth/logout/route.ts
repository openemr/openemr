import { verifyCookieValue, clearCookieAttrs } from "@/lib/auth/cookies";
import * as tokenStore from "@/lib/auth/token-store";

export const runtime = "nodejs";

const SESSION_COOKIE = "dashboard_session";

function readCookie(req: Request, name: string): string | null {
  const header = req.headers.get("cookie");
  if (!header) return null;
  const match = header.match(new RegExp(`(?:^|;\\s*)${name}=([^;]+)`));
  return match ? match[1] : null;
}

export async function GET(req: Request) {
  const cookieSecret = process.env.SESSION_COOKIE_SECRET;
  if (cookieSecret) {
    const raw = readCookie(req, SESSION_COOKIE);
    if (raw) {
      const decoded = verifyCookieValue<{ sessionId: string }>(raw, cookieSecret);
      if (decoded) {
        tokenStore.del(decoded.sessionId);
      }
    }
  }
  const headers = new Headers({
    Location: "/",
    "Set-Cookie": `${SESSION_COOKIE}=; ${clearCookieAttrs()}`,
  });
  return new Response(null, { status: 302, headers });
}
