import { describe, it, expect } from "vitest";
import { generateVerifier, verifierToChallenge } from "@/lib/auth/pkce";

describe("PKCE", () => {
  describe("generateVerifier", () => {
    it("produces base64url-charset only (43–128 chars)", () => {
      const v = generateVerifier();
      expect(v).toMatch(/^[A-Za-z0-9_-]+$/);
      expect(v.length).toBeGreaterThanOrEqual(43);
      expect(v.length).toBeLessThanOrEqual(128);
    });

    it("emits different output on consecutive calls (entropy sanity)", () => {
      const a = generateVerifier();
      const b = generateVerifier();
      expect(a).not.toBe(b);
    });
  });

  describe("verifierToChallenge", () => {
    it("is deterministic for a given verifier", () => {
      const v = "test_verifier_with_known_value_1234567890";
      expect(verifierToChallenge(v)).toBe(verifierToChallenge(v));
    });

    it("produces base64url charset, length 43 (256-bit SHA hash)", () => {
      const c = verifierToChallenge("some-verifier");
      expect(c).toMatch(/^[A-Za-z0-9_-]+$/);
      expect(c.length).toBe(43);
    });

    it("differs across different verifiers", () => {
      expect(verifierToChallenge("a")).not.toBe(verifierToChallenge("b"));
    });
  });
});
