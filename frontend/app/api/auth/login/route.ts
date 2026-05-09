import { generateVerifier, verifierToChallenge } from "@/lib/auth/pkce";
import { signCookieValue, cookieAttrs } from "@/lib/auth/cookies";

export const runtime = "nodejs";

const PKCE_COOKIE = "oauth_state_pkce";
const PKCE_TTL_MS = 5 * 60 * 1000; // 5 minutes

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

export async function GET() {
  const oauthBase = process.env.OPENEMR_OAUTH_BASE;
  const fhirBase = process.env.OPENEMR_FHIR_BASE;
  const clientId = process.env.OPENEMR_DASHBOARD_CLIENT_ID;
  const publicUrl = process.env.DASHBOARD_PUBLIC_URL;
  const cookieSecret = process.env.SESSION_COOKIE_SECRET;

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
    { state, code_verifier: codeVerifier },
    cookieSecret,
    PKCE_TTL_MS,
  );

  const headers = new Headers({
    Location: authorizeUrl,
    "Set-Cookie": `${PKCE_COOKIE}=${signed}; ${cookieAttrs(PKCE_TTL_MS / 1000)}`,
  });
  return new Response(null, { status: 302, headers });
}
