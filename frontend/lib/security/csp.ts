/**
 * Content Security Policy and related security response headers.
 *
 * Pure functions so they're unit-testable independent of next.config.ts's
 * `headers()` builder.
 */

export interface SecurityHeader {
  key: string;
  value: string;
}

export function buildCsp(opts: { copilotOrigin?: string; openemrOrigin?: string }): string {
  // Allow same-origin everything by default. The Co-Pilot iframe lives at
  // a separate origin (COPILOT_URL) and needs to load via frame-src.
  const frameSrc = ["'self'"];
  if (opts.copilotOrigin) frameSrc.push(opts.copilotOrigin);

  // The dashboard is embedded inside OpenEMR's main UI iframe (RTop slot)
  // so that clicking a patient name in OpenEMR's finder lands a clinician
  // on the modern dashboard *without leaving OpenEMR's chrome*. Allow
  // OpenEMR's origin in frame-ancestors when configured; otherwise the
  // dashboard remains un-iframable for direct-URL access.
  const frameAncestors = ["'self'"];
  if (opts.openemrOrigin) frameAncestors.push(opts.openemrOrigin);

  const directives: Record<string, string[]> = {
    "default-src": ["'self'"],
    // Next.js 15 + React 19 stream the RSC payload via inline <script> tags
    // (self.__next_f.push(...)). Strict 'self' breaks hydration and blanks
    // the page. Future: nonce-based CSP via Next.js middleware. See
    // PATIENT_DASHBOARD_MIGRATION.md §5 deferred list.
    "script-src": ["'self'", "'unsafe-inline'"],
    "style-src": ["'self'", "'unsafe-inline'"],
    "img-src": ["'self'", "data:", "blob:"],
    "font-src": ["'self'", "data:"],
    "connect-src": ["'self'"],
    "frame-src": frameSrc,
    "frame-ancestors": frameAncestors,
    "base-uri": ["'self'"],
    "form-action": ["'self'"],
    "object-src": ["'none'"],
  };

  return Object.entries(directives)
    .map(([k, v]) => `${k} ${v.join(" ")}`)
    .join("; ");
}

/** Try to parse the COPILOT_URL env into its origin component. */
export function originFromEnv(envValue: string | undefined): string | undefined {
  if (!envValue) return undefined;
  try {
    return new URL(envValue).origin;
  } catch {
    return undefined;
  }
}

// Production fallback for OPENEMR_OAUTH_BASE. next.config.ts headers() runs
// once during `next build` (in the Docker build stage), so runtime env vars
// set on Railway never reach buildSecurityHeaders. Baking the prod URL in as
// a fallback lets the iframe embed work without declaring a Dockerfile ARG
// + Railway "Build Variable" — acceptable because this URL is stable for
// the demo. Override at build time by setting OPENEMR_OAUTH_BASE in scope
// for `npm run build`.
const PROD_OPENEMR_ORIGIN_FALLBACK = "https://openemr-production-0c8c.up.railway.app";

export function buildSecurityHeaders(env: Record<string, string | undefined>): SecurityHeader[] {
  const copilotOrigin = originFromEnv(env.COPILOT_URL);
  const openemrOrigin = originFromEnv(env.OPENEMR_OAUTH_BASE) ?? PROD_OPENEMR_ORIGIN_FALLBACK;
  const headers: SecurityHeader[] = [
    { key: "Content-Security-Policy", value: buildCsp({ copilotOrigin, openemrOrigin }) },
    { key: "X-Content-Type-Options", value: "nosniff" },
    { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
    { key: "Permissions-Policy", value: "camera=(), microphone=(), geolocation=()" },
  ];
  // X-Frame-Options is deprecated and only supports a single origin; modern
  // browsers prefer CSP frame-ancestors which we set above. Send DENY only
  // when no OpenEMR origin is configured, so we don't conflict with the
  // CSP-allowed embed.
  headers.push({
    key: "X-Frame-Options",
    value: openemrOrigin ? "SAMEORIGIN" : "DENY",
  });
  return headers;
}
