/**
 * Pure helper for the FHIR proxy: validate the catch-all path segments and
 * the query string, then construct an upstream URL that cannot escape
 * `OPENEMR_FHIR_BASE`.
 *
 * Kept separate from the route handler so the URL-construction logic can
 * be tested exhaustively against path-traversal attack inputs without
 * spinning up a Next request context.
 */

export type BuildResult =
  | { ok: true; url: URL }
  | { ok: false; reason: string };

/**
 * Reject any segment that, after URL-decoding, contains characters that
 * could change path semantics (separators, control chars, dot-dot).
 */
/**
 * Re-encode a validated path segment for use in a URL while preserving
 * RFC 3986 `pchar` sub-delims that FHIR uses meaningfully — most
 * importantly `$` for FHIR operation paths like `Patient/$everything`.
 *
 * `encodeURIComponent` is over-strict (RFC 2396): it percent-encodes
 * every sub-delim. We unescape the ones that are valid in path segments
 * after encoding, so the upstream sees a syntactically valid path that
 * preserves operation markers.
 */
function encodePathSegment(decoded: string): string {
  const encoded = encodeURIComponent(decoded);
  // Map of safe pchar sub-delims to restore (per RFC 3986 §3.3):
  //   sub-delims = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
  //   plus ":" and "@" which are valid pchars too.
  return encoded
    .replace(/%21/g, "!")
    .replace(/%24/g, "$")
    .replace(/%26/g, "&")
    .replace(/%27/g, "'")
    .replace(/%28/g, "(")
    .replace(/%29/g, ")")
    .replace(/%2A/g, "*")
    .replace(/%2B/g, "+")
    .replace(/%2C/g, ",")
    .replace(/%3B/g, ";")
    .replace(/%3D/g, "=")
    .replace(/%3A/g, ":")
    .replace(/%40/g, "@");
}

function isUnsafeSegment(decoded: string): string | null {
  if (decoded === "") return "empty segment";
  if (decoded === ".") return "single-dot segment";
  if (decoded === "..") return "dot-dot segment";
  if (decoded.includes("/")) return "decoded slash in segment";
  if (decoded.includes("\\")) return "decoded backslash in segment";
  for (let i = 0; i < decoded.length; i++) {
    const code = decoded.charCodeAt(i);
    if (code < 0x20 || code === 0x7f) {
      return `control char (0x${code.toString(16)}) in segment`;
    }
  }
  return null;
}

export function buildUpstreamUrl(
  base: string,
  segments: string[],
  search: string,
): BuildResult {
  // Validate search before doing anything else — pure helpers shouldn't
  // silently accept callers who hand it a non-query string.
  if (search !== "" && !search.startsWith("?")) {
    return { ok: false, reason: "search must be empty or start with '?'" };
  }

  let baseUrl: URL;
  try {
    baseUrl = new URL(base);
  } catch {
    return { ok: false, reason: "invalid base URL" };
  }
  const basePath = baseUrl.pathname.replace(/\/+$/, "");

  const encodedSegments: string[] = [];
  for (const segment of segments) {
    let decoded: string;
    try {
      decoded = decodeURIComponent(segment);
    } catch {
      return { ok: false, reason: "segment is not valid percent-encoding" };
    }
    const reason = isUnsafeSegment(decoded);
    if (reason !== null) {
      return { ok: false, reason };
    }
    encodedSegments.push(encodePathSegment(decoded));
  }

  const pathname = `${basePath}/${encodedSegments.join("/")}`;
  let url: URL;
  try {
    url = new URL(pathname + search, baseUrl.origin);
  } catch {
    return { ok: false, reason: "could not construct URL" };
  }
  if (url.origin !== baseUrl.origin) {
    return { ok: false, reason: "constructed URL escaped base origin" };
  }
  if (!url.pathname.startsWith(`${basePath}/`)) {
    return { ok: false, reason: "constructed URL escaped base path" };
  }
  return { ok: true, url };
}
