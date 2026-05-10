import { createHash, randomBytes } from "node:crypto";

/**
 * PKCE per RFC 7636.
 *
 * The code_verifier is a high-entropy 43-128 char base64url string; we use
 * 32 bytes of randomness which encodes to 43 chars (256 bits of entropy,
 * the spec's recommended minimum).
 *
 * The code_challenge is base64url(SHA-256(verifier)).
 */

export function generateVerifier(): string {
  return randomBytes(32).toString("base64url");
}

export function verifierToChallenge(verifier: string): string {
  return createHash("sha256").update(verifier).digest("base64url");
}
