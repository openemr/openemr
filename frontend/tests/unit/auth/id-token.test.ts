import { describe, it, expect } from "vitest";
import { Buffer } from "node:buffer";
import { decodeIdTokenClaims, extractPreferredUsername } from "@/lib/auth/id-token";

function makeIdToken(claims: object): string {
  // header.<base64url(claims)>.signature  — only middle segment matters
  const header = Buffer.from('{"alg":"RS256","typ":"JWT"}', "utf8").toString("base64url");
  const payload = Buffer.from(JSON.stringify(claims), "utf8").toString("base64url");
  return `${header}.${payload}.signature-not-checked`;
}

describe("decodeIdTokenClaims", () => {
  it("decodes the middle segment to a claims object", () => {
    const token = makeIdToken({ preferred_username: "rwang", iss: "https://example.com" });
    const claims = decodeIdTokenClaims(token);
    expect(claims).toMatchObject({ preferred_username: "rwang", iss: "https://example.com" });
  });

  it("returns null on malformed tokens", () => {
    expect(decodeIdTokenClaims("")).toBeNull();
    expect(decodeIdTokenClaims("only-one-segment")).toBeNull();
  });

  it("accepts a 2-part JWT shape (signature segment optional)", () => {
    // header.payload — middle base64url-encodes a JSON object
    const payload = Buffer.from('{"sub":"x"}', "utf8").toString("base64url");
    const token = `header.${payload}`;
    expect(decodeIdTokenClaims(token)).toMatchObject({ sub: "x" });
  });

  it("returns null when the middle segment isn't valid JSON", () => {
    const token = "header." + Buffer.from("not json", "utf8").toString("base64url") + ".sig";
    expect(decodeIdTokenClaims(token)).toBeNull();
  });

  it("returns null on non-string input", () => {
    // @ts-expect-error intentional bad input
    expect(decodeIdTokenClaims(undefined)).toBeNull();
    // @ts-expect-error intentional bad input
    expect(decodeIdTokenClaims(123)).toBeNull();
  });
});

describe("extractPreferredUsername", () => {
  it("returns the preferred_username claim when present", () => {
    const token = makeIdToken({ preferred_username: "physician-1" });
    expect(extractPreferredUsername(token)).toBe("physician-1");
  });

  it("returns undefined when the claim is missing", () => {
    const token = makeIdToken({ sub: "abc" });
    expect(extractPreferredUsername(token)).toBeUndefined();
  });

  it("returns undefined when the claim is not a string", () => {
    const token = makeIdToken({ preferred_username: 123 });
    expect(extractPreferredUsername(token)).toBeUndefined();
  });

  it("returns undefined for undefined / empty input", () => {
    expect(extractPreferredUsername(undefined)).toBeUndefined();
    expect(extractPreferredUsername("")).toBeUndefined();
  });

  it("returns undefined for malformed token", () => {
    expect(extractPreferredUsername("not-a-jwt")).toBeUndefined();
  });
});
