import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import {
  signCookieValue,
  verifyCookieValue,
  cookieAttrs,
  clearCookieAttrs,
} from "@/lib/auth/cookies";

const SECRET = "test-secret-do-not-use-in-prod-32-bytes-min";
const ALT_SECRET = "wrong-secret-still-32-bytes-or-longer-please";
const TTL_5_MIN = 300_000;
const TTL_8_HOUR = 28_800_000;

describe("signed cookies", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date("2026-01-01T00:00:00Z"));
  });
  afterEach(() => {
    vi.useRealTimers();
  });

  it("round-trips with 5-min TTL", () => {
    const signed = signCookieValue({ state: "abc", code_verifier: "xyz" }, SECRET, TTL_5_MIN);
    const verified = verifyCookieValue<{ state: string; code_verifier: string }>(signed, SECRET);
    expect(verified).toEqual({ state: "abc", code_verifier: "xyz" });
  });

  it("round-trips with 8-hour TTL", () => {
    const signed = signCookieValue({ sessionId: "uuid-here" }, SECRET, TTL_8_HOUR);
    const verified = verifyCookieValue<{ sessionId: string }>(signed, SECRET);
    expect(verified).toEqual({ sessionId: "uuid-here" });
  });

  it("returns null on tampered HMAC", () => {
    const signed = signCookieValue({ x: 1 }, SECRET, TTL_5_MIN);
    // Flip a char in the signature half
    const idx = signed.lastIndexOf(".");
    const tampered = signed.slice(0, idx + 1) + "AAAAAAAA" + signed.slice(idx + 9);
    expect(verifyCookieValue(tampered, SECRET)).toBeNull();
  });

  it("returns null on tampered payload (HMAC won't match)", () => {
    const signed = signCookieValue({ x: 1 }, SECRET, TTL_5_MIN);
    // Replace first char of payload
    const tampered = "Z" + signed.slice(1);
    expect(verifyCookieValue(tampered, SECRET)).toBeNull();
  });

  it("returns null when verified with wrong secret", () => {
    const signed = signCookieValue({ x: 1 }, SECRET, TTL_5_MIN);
    expect(verifyCookieValue(signed, ALT_SECRET)).toBeNull();
  });

  it("returns null on expired payload (server-side enforcement)", () => {
    const signed = signCookieValue({ x: 1 }, SECRET, TTL_5_MIN);
    // Advance past TTL
    vi.setSystemTime(new Date("2026-01-01T00:06:00Z"));
    expect(verifyCookieValue(signed, SECRET)).toBeNull();
  });

  it("returns null on malformed input", () => {
    expect(verifyCookieValue("", SECRET)).toBeNull();
    expect(verifyCookieValue("nodot", SECRET)).toBeNull();
    expect(verifyCookieValue(".", SECRET)).toBeNull();
    expect(verifyCookieValue("a.", SECRET)).toBeNull();
    expect(verifyCookieValue(".b", SECRET)).toBeNull();
  });
});

describe("cookieAttrs", () => {
  afterEach(() => {
    vi.unstubAllEnvs();
  });

  it("includes Path, HttpOnly, SameSite, Max-Age in dev", () => {
    vi.stubEnv("NODE_ENV", "development");
    const a = cookieAttrs(300);
    expect(a).toContain("Path=/");
    expect(a).toContain("HttpOnly");
    expect(a).toContain("SameSite=Lax");
    expect(a).toContain("Max-Age=300");
    expect(a).not.toContain("Secure");
  });

  it("adds Secure in production", () => {
    vi.stubEnv("NODE_ENV", "production");
    const a = cookieAttrs(300);
    expect(a).toContain("Secure");
  });
});

describe("clearCookieAttrs", () => {
  afterEach(() => {
    vi.unstubAllEnvs();
  });

  it("returns Max-Age=0 with the same attribute set", () => {
    vi.stubEnv("NODE_ENV", "development");
    const a = clearCookieAttrs();
    expect(a).toContain("Max-Age=0");
    expect(a).toContain("HttpOnly");
    expect(a).toContain("SameSite=Lax");
    expect(a).not.toContain("Secure");
  });

  it("includes Secure in production", () => {
    vi.stubEnv("NODE_ENV", "production");
    expect(clearCookieAttrs()).toContain("Secure");
  });
});
