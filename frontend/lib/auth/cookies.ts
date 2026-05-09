import { createHmac, timingSafeEqual } from "node:crypto";

/**
 * Signed-cookie helpers.
 *
 * Format: `<base64url(JSON({...payload, exp}))>.<base64url(HMAC-SHA-256(b64-payload, secret))>`.
 *
 * Server-side expiry is encoded into the payload as `exp` (epoch ms).
 * `verifyCookieValue` rejects expired cookies regardless of browser Max-Age.
 *
 * HMAC compare is constant-time (`crypto.timingSafeEqual`).
 */

type SignedPayload<T> = T & { exp: number };

function b64url(buf: Buffer | string): string {
  return Buffer.isBuffer(buf)
    ? buf.toString("base64url")
    : Buffer.from(buf, "utf8").toString("base64url");
}

function fromB64url(s: string): Buffer {
  return Buffer.from(s, "base64url");
}

export function signCookieValue(
  payload: object,
  secret: string,
  ttlMs: number,
): string {
  const body: SignedPayload<typeof payload> = {
    ...(payload as object),
    exp: Date.now() + ttlMs,
  };
  const encoded = b64url(JSON.stringify(body));
  const sig = createHmac("sha256", secret).update(encoded).digest("base64url");
  return `${encoded}.${sig}`;
}

export function verifyCookieValue<T extends object>(
  signed: string,
  secret: string,
): T | null {
  if (typeof signed !== "string" || !signed.includes(".")) return null;
  const lastDot = signed.lastIndexOf(".");
  const encoded = signed.slice(0, lastDot);
  const sig = signed.slice(lastDot + 1);
  if (!encoded || !sig) return null;

  const expected = createHmac("sha256", secret)
    .update(encoded)
    .digest();
  let provided: Buffer;
  try {
    provided = fromB64url(sig);
  } catch {
    return null;
  }
  if (provided.length !== expected.length) return null;
  if (!timingSafeEqual(provided, expected)) return null;

  let parsed: SignedPayload<T>;
  try {
    parsed = JSON.parse(fromB64url(encoded).toString("utf8"));
  } catch {
    return null;
  }
  if (typeof parsed !== "object" || parsed === null) return null;
  if (typeof parsed.exp !== "number" || parsed.exp <= Date.now()) return null;

  // Strip exp from the returned payload so callers don't have to
  // know the helper added it.
  const { exp: _exp, ...rest } = parsed;
  void _exp;
  return rest as T;
}

export function cookieAttrs(maxAgeSec: number): string {
  const isProd = process.env.NODE_ENV === "production";
  const parts = [
    "Path=/",
    "HttpOnly",
    "SameSite=Lax",
    `Max-Age=${maxAgeSec}`,
  ];
  if (isProd) parts.push("Secure");
  return parts.join("; ");
}

export function clearCookieAttrs(): string {
  // Match cookieAttrs's prod-vs-dev attribute set so browsers actually
  // evict the cookie (Set-Cookie clears must agree on Secure with the
  // original set call).
  return cookieAttrs(0);
}
