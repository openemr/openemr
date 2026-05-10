import { Buffer } from "node:buffer";

/**
 * Decode the claims (middle segment) of a JWT-format ID token.
 *
 * Returns the parsed JSON payload or `null` if the input is malformed.
 *
 * **Does NOT verify the signature.** This helper is used to read the
 * `preferred_username` claim from an ID token that arrived in a TLS-protected
 * POST response from OpenEMR's token endpoint, so the trust comes from the
 * transport, not from JWT-signature verification. The returned claims must
 * not be used as a basis for authorization decisions in their raw form —
 * caller stores them in a server-controlled token-store and reads from
 * there.
 */
export function decodeIdTokenClaims(idToken: string): Record<string, unknown> | null {
  if (typeof idToken !== "string") return null;
  const parts = idToken.split(".");
  if (parts.length < 2) return null;
  const claimsB64 = parts[1];
  if (!claimsB64) return null;
  let decoded: Buffer;
  try {
    decoded = Buffer.from(claimsB64, "base64url");
  } catch {
    return null;
  }
  let parsed: unknown;
  try {
    parsed = JSON.parse(decoded.toString("utf8"));
  } catch {
    return null;
  }
  if (typeof parsed !== "object" || parsed === null) return null;
  return parsed as Record<string, unknown>;
}

/**
 * Convenience: extract `preferred_username` from an ID token (OpenEMR's
 * convention for the OpenEMR username). Returns `undefined` if the token
 * is malformed or the claim is absent.
 */
export function extractPreferredUsername(idToken: string | undefined): string | undefined {
  if (!idToken) return undefined;
  const claims = decodeIdTokenClaims(idToken);
  if (!claims) return undefined;
  const u = claims["preferred_username"];
  return typeof u === "string" ? u : undefined;
}
