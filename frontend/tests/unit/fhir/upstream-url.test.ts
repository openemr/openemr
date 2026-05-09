import { describe, it, expect } from "vitest";
import { buildUpstreamUrl } from "@/lib/fhir/upstream-url";

const BASE = "https://openemr.example.com/apis/default/fhir";

describe("buildUpstreamUrl", () => {
  describe("happy paths", () => {
    it("constructs a single-segment FHIR resource URL", () => {
      const r = buildUpstreamUrl(BASE, ["Patient"], "");
      expect(r.ok).toBe(true);
      if (r.ok) {
        expect(r.url.toString()).toBe("https://openemr.example.com/apis/default/fhir/Patient");
      }
    });

    it("constructs Patient/{id}", () => {
      const r = buildUpstreamUrl(BASE, ["Patient", "123"], "");
      expect(r.ok).toBe(true);
      if (r.ok) {
        expect(r.url.pathname).toBe("/apis/default/fhir/Patient/123");
      }
    });

    it("preserves the search string when provided with leading '?'", () => {
      const r = buildUpstreamUrl(BASE, ["MedicationRequest"], "?patient=123&_count=10");
      expect(r.ok).toBe(true);
      if (r.ok) {
        expect(r.url.search).toBe("?patient=123&_count=10");
      }
    });

    it("accepts empty search", () => {
      const r = buildUpstreamUrl(BASE, ["Encounter"], "");
      expect(r.ok).toBe(true);
    });

    it("handles a base URL with trailing slash", () => {
      const r = buildUpstreamUrl(BASE + "/", ["Patient"], "");
      expect(r.ok).toBe(true);
      if (r.ok) {
        expect(r.url.pathname).toBe("/apis/default/fhir/Patient");
      }
    });

    it("encodes safe URL chars correctly (e.g. id with hyphen)", () => {
      const r = buildUpstreamUrl(BASE, ["Patient", "abc-def-ghi"], "");
      expect(r.ok).toBe(true);
    });

    it("preserves '$' for FHIR operation paths like Patient/$everything", () => {
      const r = buildUpstreamUrl(BASE, ["Patient", "$everything"], "");
      expect(r.ok).toBe(true);
      if (r.ok) {
        expect(r.url.pathname).toBe("/apis/default/fhir/Patient/$everything");
      }
    });

    it("preserves '$' on a top-level FHIR operation like $export", () => {
      const r = buildUpstreamUrl(BASE, ["$export"], "");
      expect(r.ok).toBe(true);
      if (r.ok) {
        expect(r.url.pathname).toBe("/apis/default/fhir/$export");
      }
    });
  });

  describe("path-traversal rejection (six attack inputs)", () => {
    it("rejects literal '..' segment", () => {
      const r = buildUpstreamUrl(BASE, ["Patient", ".."], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("dot-dot");
    });

    it("rejects literal '.' segment (URL would normalize it away)", () => {
      const r = buildUpstreamUrl(BASE, ["Patient", "."], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("single-dot");
    });

    it("rejects URL-encoded '.' (%2e)", () => {
      const r = buildUpstreamUrl(BASE, ["Patient", "%2e"], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("single-dot");
    });

    it("rejects URL-encoded '..' (%2e%2e)", () => {
      const r = buildUpstreamUrl(BASE, ["Patient", "%2e%2e"], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("dot-dot");
    });

    it("rejects decoded '/' inside a segment", () => {
      const r = buildUpstreamUrl(BASE, ["foo%2Fbar"], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("slash");
    });

    it("rejects decoded backslash inside a segment", () => {
      const r = buildUpstreamUrl(BASE, ["foo%5Cbar"], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("backslash");
    });

    it("rejects empty segment (e.g. '//' in the URL → empty array entry)", () => {
      const r = buildUpstreamUrl(BASE, ["Patient", ""], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("empty");
    });

    it("rejects control char (NUL %00)", () => {
      const r = buildUpstreamUrl(BASE, ["foo%00bar"], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("control");
    });

    it("rejects malformed percent-encoding", () => {
      const r = buildUpstreamUrl(BASE, ["foo%ZZ"], "");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("percent-encoding");
    });
  });

  describe("search validation", () => {
    it("rejects search that doesn't start with '?'", () => {
      const r = buildUpstreamUrl(BASE, ["Patient"], "patient=123");
      expect(r.ok).toBe(false);
      if (!r.ok) expect(r.reason).toContain("search must");
    });

    it("accepts search === ''", () => {
      const r = buildUpstreamUrl(BASE, ["Patient"], "");
      expect(r.ok).toBe(true);
    });

    it("accepts search starting with '?'", () => {
      const r = buildUpstreamUrl(BASE, ["Patient"], "?patient=123");
      expect(r.ok).toBe(true);
    });
  });

  describe("invalid base", () => {
    it("rejects an unparseable base URL", () => {
      const r = buildUpstreamUrl("not-a-url", ["Patient"], "");
      expect(r.ok).toBe(false);
    });
  });
});
