import { describe, it, expect } from "vitest";
import { buildCsp, buildSecurityHeaders, originFromEnv } from "@/lib/security/csp";

describe("buildCsp", () => {
  it("includes default-src 'self' and forbids object-src", () => {
    const csp = buildCsp({});
    expect(csp).toContain("default-src 'self'");
    expect(csp).toContain("object-src 'none'");
  });

  it("frame-ancestors defaults to 'self' (dashboard can iframe itself, no third party)", () => {
    expect(buildCsp({})).toContain("frame-ancestors 'self'");
    expect(buildCsp({})).not.toContain("https://openemr.example.com");
  });

  it("frame-ancestors includes the OpenEMR origin when provided (embed-in-OpenEMR)", () => {
    const csp = buildCsp({ openemrOrigin: "https://openemr.example.com" });
    expect(csp).toContain("frame-ancestors 'self' https://openemr.example.com");
  });

  it("frame-src allows 'self' only when no copilot origin", () => {
    const csp = buildCsp({});
    expect(csp).toContain("frame-src 'self'");
    expect(csp).not.toContain("https://copilot.example.com");
  });

  it("frame-src includes the copilot origin when provided", () => {
    const csp = buildCsp({ copilotOrigin: "https://copilot.example.com" });
    expect(csp).toContain("frame-src 'self' https://copilot.example.com");
  });

  it("style-src allows 'unsafe-inline' (Tailwind v4 + Next critical styles)", () => {
    expect(buildCsp({})).toContain("style-src 'self' 'unsafe-inline'");
  });

  it("script-src allows 'unsafe-inline' (Next.js 15 RSC streaming inline scripts)", () => {
    expect(buildCsp({})).toContain("script-src 'self' 'unsafe-inline'");
  });
});

describe("originFromEnv", () => {
  it("parses a URL to its origin", () => {
    expect(originFromEnv("https://copilot.example.com/iframe")).toBe("https://copilot.example.com");
  });

  it("returns undefined for invalid URL or missing", () => {
    expect(originFromEnv(undefined)).toBeUndefined();
    expect(originFromEnv("")).toBeUndefined();
    expect(originFromEnv("not-a-url")).toBeUndefined();
  });
});

describe("buildSecurityHeaders", () => {
  it("returns the canonical 5 headers", () => {
    const headers = buildSecurityHeaders({});
    const keys = headers.map((h) => h.key).sort();
    expect(keys).toEqual([
      "Content-Security-Policy",
      "Permissions-Policy",
      "Referrer-Policy",
      "X-Content-Type-Options",
      "X-Frame-Options",
    ]);
  });

  it("X-Content-Type-Options is nosniff", () => {
    const v = buildSecurityHeaders({}).find((h) => h.key === "X-Content-Type-Options")?.value;
    expect(v).toBe("nosniff");
  });

  it("X-Frame-Options is DENY when no OpenEMR origin configured", () => {
    const v = buildSecurityHeaders({}).find((h) => h.key === "X-Frame-Options")?.value;
    expect(v).toBe("DENY");
  });

  it("X-Frame-Options is SAMEORIGIN when OpenEMR origin is configured (defers to CSP frame-ancestors)", () => {
    const v = buildSecurityHeaders({ OPENEMR_OAUTH_BASE: "https://openemr.example.com" })
      .find((h) => h.key === "X-Frame-Options")?.value;
    expect(v).toBe("SAMEORIGIN");
  });

  it("Permissions-Policy disables camera/microphone/geolocation", () => {
    const v = buildSecurityHeaders({}).find((h) => h.key === "Permissions-Policy")?.value ?? "";
    expect(v).toContain("camera=()");
    expect(v).toContain("microphone=()");
    expect(v).toContain("geolocation=()");
  });

  it("CSP frame-src reflects COPILOT_URL env", () => {
    const v = buildSecurityHeaders({ COPILOT_URL: "https://copilot.example.com" }).find((h) => h.key === "Content-Security-Policy")?.value ?? "";
    expect(v).toContain("https://copilot.example.com");
  });

  it("CSP frame-ancestors reflects OPENEMR_OAUTH_BASE env (allows embedding from OpenEMR)", () => {
    const v = buildSecurityHeaders({ OPENEMR_OAUTH_BASE: "https://openemr.example.com" })
      .find((h) => h.key === "Content-Security-Policy")?.value ?? "";
    expect(v).toContain("frame-ancestors 'self' https://openemr.example.com");
  });
});
