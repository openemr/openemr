/**
 * Server-component-callable FHIR client.
 *
 * Inside server components and Route Handlers, we issue an absolute-URL
 * fetch back through the dashboard's own /api/fhir proxy. We forward the
 * caller's dashboard_session cookie so the proxy can look up the bearer
 * token in its module-scope token-store.
 *
 * Why an absolute URL: server-component fetches use the global undici
 * fetch which has no concept of "current origin"; relative URLs throw
 * `Invalid URL`. The DASHBOARD_PUBLIC_URL env (added in KR4) is exactly
 * the right value to prefix.
 */

import { cookies } from "next/headers";

export class FhirError extends Error {
  constructor(public status: number, message: string) {
    super(message);
    this.name = "FhirError";
  }
}

export interface FhirGetOptions {
  /** Override the absolute-URL prefix (mainly for tests). */
  baseUrl?: string;
  /** Override cookie forwarding (mainly for tests). */
  cookieHeader?: string;
  /** Optional Accept header override (defaults to application/fhir+json). */
  accept?: string;
}

export async function fhirGet<T>(path: string, options: FhirGetOptions = {}): Promise<T> {
  const baseUrl = options.baseUrl ?? process.env.DASHBOARD_PUBLIC_URL;
  if (!baseUrl) {
    throw new FhirError(500, "DASHBOARD_PUBLIC_URL not set");
  }
  // Forward the dashboard_session cookie so the /api/fhir proxy can map
  // it to a bearer token via its token-store.
  let cookieHeader = options.cookieHeader;
  if (cookieHeader === undefined) {
    const store = await cookies();
    const sessionCookie = store.get("dashboard_session");
    cookieHeader = sessionCookie ? `dashboard_session=${sessionCookie.value}` : "";
  }
  const url = `${baseUrl.replace(/\/+$/, "")}/api/fhir${path.startsWith("/") ? path : `/${path}`}`;
  const res = await fetch(url, {
    cache: "no-store",
    headers: {
      Accept: options.accept ?? "application/fhir+json",
      ...(cookieHeader ? { Cookie: cookieHeader } : {}),
    },
  });
  if (!res.ok) {
    throw new FhirError(res.status, `FHIR upstream ${res.status} for ${path}`);
  }
  return (await res.json()) as T;
}
