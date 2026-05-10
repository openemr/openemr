import { generateVerifier, verifierToChallenge } from "@/lib/auth/pkce";
import { signCookieValue, cookieAttrs } from "@/lib/auth/cookies";

export const runtime = "nodejs";

const PKCE_COOKIE = "oauth_state_pkce";
const PKCE_TTL_MS = 5 * 60 * 1000; // 5 minutes

/**
 * Sanitize a `next` query parameter for safe use as a post-login Location
 * header value. Rejects anything that isn't a same-origin absolute path
 * — defense against open-redirect ("?next=https://evil.com").
 */
function safeNextPath(raw: string | null): string {
  if (!raw) return "/";
  // Must start with a single slash and not be protocol-relative (`//`).
  if (!raw.startsWith("/") || raw.startsWith("//")) return "/";
  // Reject control chars / CRLF (header-injection guard).
  if (/[\x00-\x1f\x7f]/.test(raw)) return "/";
  return raw;
}

const SCOPES = [
  "openid",
  "offline_access",
  "api:fhir",
  "user/Patient.read",
  "user/AllergyIntolerance.read",
  "user/Condition.read",
  "user/MedicationRequest.read",
  "user/CareTeam.read",
  "user/Encounter.read",
].join(" ");

export async function GET(req: Request) {
  const oauthBase = process.env.OPENEMR_OAUTH_BASE;
  const fhirBase = process.env.OPENEMR_FHIR_BASE;
  const clientId = process.env.OPENEMR_DASHBOARD_CLIENT_ID;
  const publicUrl = process.env.DASHBOARD_PUBLIC_URL;
  const cookieSecret = process.env.SESSION_COOKIE_SECRET;

  const next = safeNextPath(new URL(req.url).searchParams.get("next"));

  if (!oauthBase || !fhirBase || !clientId || !publicUrl || !cookieSecret) {
    return new Response(
      "missing required env (OPENEMR_OAUTH_BASE, OPENEMR_FHIR_BASE, OPENEMR_DASHBOARD_CLIENT_ID, DASHBOARD_PUBLIC_URL, SESSION_COOKIE_SECRET)",
      { status: 500 },
    );
  }

  const state = generateVerifier();
  const codeVerifier = generateVerifier();
  const codeChallenge = verifierToChallenge(codeVerifier);
  // Strip trailing slashes so a configured `DASHBOARD_PUBLIC_URL=...com/`
  // doesn't produce a non-matching `//api/auth/callback` redirect URI
  // (OAuth registries match redirect URIs exactly).
  const redirectUri = `${publicUrl.replace(/\/+$/, "")}/api/auth/callback`;

  const params = new URLSearchParams({
    response_type: "code",
    client_id: clientId,
    redirect_uri: redirectUri,
    code_challenge: codeChallenge,
    code_challenge_method: "S256",
    state,
    scope: SCOPES,
    // OpenEMR SMART-on-FHIR requires `aud` for any authorize request that
    // wants FHIR-scoped tokens. Strip trailing slashes for the same reason.
    aud: fhirBase.replace(/\/+$/, ""),
  });
  const authorizeUrl = `${oauthBase.replace(/\/+$/, "")}/oauth2/default/authorize?${params.toString()}`;

  const signed = signCookieValue(
    { state, code_verifier: codeVerifier, next },
    cookieSecret,
    PKCE_TTL_MS,
  );

  const headers = new Headers({
    Location: authorizeUrl,
    "Set-Cookie": `${PKCE_COOKIE}=${signed}; ${cookieAttrs(PKCE_TTL_MS / 1000)}`,
  });
  return new Response(null, { status: 302, headers });
}
