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

export function buildCsp(opts: { copilotOrigin?: string }): string {
  // Co-Pilot iframe lives at a separate origin (COPILOT_URL); allow it
  // via frame-src.
  const frameSrc = ["'self'"];
  if (opts.copilotOrigin) frameSrc.push(opts.copilotOrigin);

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
    // Same-origin embed only. The dashboard is co-hosted inside OpenEMR's
    // Apache container under /modern/*, so OpenEMR (which iframes the
    // chooser → modern dashboard) is already same-origin and covered by
    // 'self'. No cross-origin allowlist needed.
    "frame-ancestors": ["'self'"],
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

export function buildSecurityHeaders(env: Record<string, string | undefined>): SecurityHeader[] {
  const copilotOrigin = originFromEnv(env.COPILOT_URL);
  return [
    { key: "Content-Security-Policy", value: buildCsp({ copilotOrigin }) },
    { key: "X-Content-Type-Options", value: "nosniff" },
    { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
    { key: "Permissions-Policy", value: "camera=(), microphone=(), geolocation=()" },
    // Same-origin embed: SAMEORIGIN matches CSP frame-ancestors 'self'.
    { key: "X-Frame-Options", value: "SAMEORIGIN" },
  ];
}
